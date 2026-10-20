
<?php
// app/controllers/MatriculaController.php

require_once __DIR__ . '/../../config/sessao.php';
require_once __DIR__ . '/../services/MercadoPagoService.php';

$baseUrl = BASE_URL . '/app/controllers/MatriculaController.php';
$acao = $_GET['acao'] ?? 'identificacao';

$acoesPermitidas = [
    'identificacao',
    'filial',
    'pagamento',
    'sucesso'
];

if (!in_array($acao, $acoesPermitidas, true)) {
    header("Location: $baseUrl?acao=identificacao");
    exit;
}

// ==================================================
// CONTEXTO MULTI-TENANT DA MATRÍCULA
// ==================================================
//
// O company_id chega pelo link do site público.
// Depois, permanece em $_SESSION['matricula'].
//
// Este contexto é independente da sessão do
// usuário autenticado: visitantes também podem
// iniciar uma matrícula.
//
// O model verifica empresa, plano e filial no banco.

if ($acao === 'identificacao' && isset($_GET['company_id'])) {

    $empresaRecebida = filter_var(
        $_GET['company_id'],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($empresaRecebida === false) {
        http_response_code(400);
        exit('Academia inválida.');
    }

    $empresaRecebida = (int) $empresaRecebida;

    $empresaAnterior = (int) (
        $_SESSION['matricula']['company_id'] ?? 0
    );

    // Impede reutilizar dados de outra academia.
    if (
        $empresaAnterior > 0 &&
        $empresaAnterior !== $empresaRecebida
    ) {
        unset($_SESSION['matricula']);
    }

    $_SESSION['matricula']['company_id'] = $empresaRecebida;
}

$company_id = (int) (
    $_SESSION['matricula']['company_id'] ?? 0
);

// A tela de sucesso utiliza um resultado separado,
// já gravado após a concretização da matrícula.
if ($acao !== 'sucesso' && $company_id <= 0) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// ==================================================
// ETAPA 1 - IDENTIFICAÇÃO E PLANO
// ==================================================

if ($acao === 'identificacao') {

    $erro = '';
    $dados = $_SESSION['matricula'] ?? [];

    if (!empty($_GET['plano_id'])) {
        $dados['plano_id'] = (int) $_GET['plano_id'];
    }

    // Lista somente planos da empresa.
    $operacao = 'listar_planos';
    require __DIR__ . '/../models/Matricula.php';

    if (!empty($erroModel)) {
        $erro = $erroModel;
    }

    $planoSelecionado = null;
    $planoIdBusca = (int) ($dados['plano_id'] ?? 0);

    if ($planoIdBusca > 0) {
        $operacao = 'buscar_plano';
        $plano_id = $planoIdBusca;
        require __DIR__ . '/../models/Matricula.php';

        $planoSelecionado = $plano;

        if (!$planoSelecionado) {
            $dados['plano_id'] = 0;
        }
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

        $dados['company_id'] = $company_id;
        $dados['plano_id'] = (int) ($_POST['plano_id'] ?? 0);
        $dados['nome'] = trim($_POST['nome'] ?? '');
        $dados['cpf'] = trim($_POST['cpf'] ?? '');
        $dados['rg'] = trim($_POST['rg'] ?? '');
        $dados['sexo'] = trim($_POST['sexo'] ?? '');
        $dados['nascimento'] = trim($_POST['nascimento'] ?? '');
        $dados['email'] = trim($_POST['email'] ?? '');
        $dados['telefone'] = trim($_POST['telefone'] ?? '');
        $dados['endereco'] = trim($_POST['endereco'] ?? '');
        $dados['senha'] = $_POST['senha'] ?? '';

        if (
            $dados['plano_id'] <= 0 ||
            $dados['nome'] === '' ||
            $dados['cpf'] === '' ||
            $dados['sexo'] === '' ||
            $dados['nascimento'] === '' ||
            $dados['email'] === '' ||
            $dados['telefone'] === '' ||
            $dados['senha'] === ''
        ) {
            $erro = 'Preencha todos os campos obrigatórios.';
        } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um endereço de e-mail válido.';
        } elseif (strlen($dados['senha']) < 6) {
            $erro = 'A senha deve conter no mínimo 6 caracteres.';
        } else {

            // Confirma que o plano pertence à empresa.
            $operacao = 'buscar_plano';
            $plano_id = $dados['plano_id'];
            require __DIR__ . '/../models/Matricula.php';

            if (empty($plano)) {

                $erro = 'O plano selecionado não está disponível.';
            } else {

                $operacao = 'validar_dados_cadastrais';
                $cpf = $dados['cpf'];
                $email = $dados['email'];

                require __DIR__ . '/../models/Matricula.php';

                if (!empty($erroModel)) {

                    $erro = $erroModel;
                } else {

                    $_SESSION['matricula'] = $dados;

                    header("Location: $baseUrl?acao=filial");
                    exit;
                }
            }
        }

        if ($dados['plano_id'] > 0) {
            $operacao = 'buscar_plano';
            $plano_id = $dados['plano_id'];

            require __DIR__ . '/../models/Matricula.php';

            $planoSelecionado = $plano;
        }
    }

    require __DIR__ . '/../views/matricula/identificacao.php';
}

// ==================================================
// ETAPA 2 - SELEÇÃO DE FILIAL
// ==================================================

elseif ($acao === 'filial') {

    $matriculaTemp = $_SESSION['matricula'] ?? [];

    if (
        empty($matriculaTemp['plano_id']) ||
        empty($matriculaTemp['nome'])
    ) {
        header("Location: $baseUrl?acao=identificacao");
        exit;
    }

    $erro = '';

    $operacao = 'buscar_plano';
    $plano_id = (int) $matriculaTemp['plano_id'];

    require __DIR__ . '/../models/Matricula.php';

    if (empty($plano)) {
        header("Location: $baseUrl?acao=identificacao");
        exit;
    }

    $operacao = 'listar_filiais';

    require __DIR__ . '/../models/Matricula.php';

    if (!empty($erroModel)) {
        $erro = $erroModel;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

        $filialId = (int) ($_POST['filial_id'] ?? 0);

        if ($filialId <= 0) {

            $erro = 'Selecione uma filial para treinar.';
        } else {

            // Verifica empresa e situação da filial.
            $operacao = 'buscar_filial';
            $filial_id = $filialId;

            require __DIR__ . '/../models/Matricula.php';

            if (empty($filial)) {

                $erro = 'Filial inválida ou indisponível.';
            } else {

                $_SESSION['matricula']['filial_id'] = $filialId;

                header("Location: $baseUrl?acao=pagamento");
                exit;
            }
        }
    }

    require __DIR__ . '/../views/matricula/filial.php';
}

// ==================================================
// ETAPA 3 - PAGAMENTO
// ==================================================

elseif ($acao === 'pagamento') {

    $matriculaTemp = $_SESSION['matricula'] ?? [];

    if (
        empty($matriculaTemp['plano_id']) ||
        empty($matriculaTemp['filial_id'])
    ) {
        header("Location: $baseUrl?acao=filial");
        exit;
    }

    $erro = '';

    // Revalida plano dentro da empresa.
    $operacao = 'buscar_plano';
    $plano_id = (int) $matriculaTemp['plano_id'];

    require __DIR__ . '/../models/Matricula.php';

    if (empty($plano)) {
        header("Location: $baseUrl?acao=identificacao");
        exit;
    }

    // Revalida filial dentro da empresa.
    $operacao = 'buscar_filial';
    $filial_id = (int) $matriculaTemp['filial_id'];

    require __DIR__ . '/../models/Matricula.php';

    if (empty($filial)) {
        header("Location: $baseUrl?acao=filial");
        exit;
    }

    // Mantém o serviço já utilizado pelo projeto.
    $mpService = new MercadoPagoService();

    $publicKeyMP = $mpService->getPublicKey();
    $isConfiguredMP = $mpService->isConfigured();

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

        $metodo = strtolower(
            trim($_POST['metodo'] ?? 'pix')
        );

        if (!in_array($metodo, ['pix', 'cartao'], true)) {
            $metodo = 'pix';
        }

        // Atenção:
        // A integração financeira ainda precisará
        // de uma auditoria própria para garantir
        // confirmação confiável e idempotência.
        //
        // Este código mantém a chamada existente.

        try {

            $respPagamento = $mpService->processarPagamento([
                'matricula_id' => time(),
                'metodo' => $metodo,
                'valor' => (float) $plano['valor'],
                'aluno' => [
                    'nome' => $matriculaTemp['nome'],
                    'email' => $matriculaTemp['email'],
                    'cpf' => $matriculaTemp['cpf']
                ],
                'token_cartao' => $_POST['token_cartao'] ?? null,
                'parcelas' => (int) ($_POST['parcelas'] ?? 1)
            ]);

            if (!is_array($respPagamento)) {
                throw new RuntimeException(
                    'Resposta de pagamento inválida.'
                );
            }

            // Nunca presumir aprovação por ausência
            // de status na resposta do serviço.
            $statusPagamento =
                $respPagamento['status'] ?? 'pendente';

            // Concretização: o model volta a validar
            // a propriedade da filial e do plano.

            $operacao = 'concretizar_matricula';

            $dados = [
                'aluno' => [
                    'filial_id' => $filial['id'],
                    'company_id' => $company_id,
                    'nome' => $matriculaTemp['nome'],
                    'cpf' => $matriculaTemp['cpf'],
                    'rg' => $matriculaTemp['rg'] ?? null,
                    'sexo' => $matriculaTemp['sexo'],
                    'nascimento' => $matriculaTemp['nascimento'],
                    'email' => $matriculaTemp['email'],
                    'telefone' => $matriculaTemp['telefone'],
                    'endereco' => $matriculaTemp['endereco'] ?? null,
                    'senha' => $matriculaTemp['senha']
                ],
                'plano' => [
                    'id' => $plano['id'],
                    'nome' => $plano['nome'],
                    'valor' => $plano['valor'],
                    'duracao' => $plano['duracao']
                ],
                'pagamento' => [
                    'metodo' => $metodo,
                    'status' => $statusPagamento,
                    'mercado_pago_id' =>
                    $respPagamento['mercado_pago_id'] ?? null
                ]
            ];

            require __DIR__ . '/../models/Matricula.php';

            if (!empty($erroModel)) {

                $erro = 'Falha ao concretizar a matrícula: ' .
                    $erroModel;
            } elseif (!empty($matriculaConcretizada)) {

                $_SESSION['matricula_sucesso'] = array_merge(
                    $matriculaConcretizada,
                    [
                        'pix_copia_cola' =>
                        $respPagamento['pix_copia_cola'] ?? null
                    ]
                );

                unset($_SESSION['matricula']);

                header("Location: $baseUrl?acao=sucesso");
                exit;
            } else {

                $erro = 'Não foi possível concluir a matrícula.';
            }
        } catch (Throwable $e) {

            error_log(
                '[GymFlow][MatriculaController] ' .
                    $e->getMessage()
            );

            $erro = 'Não foi possível processar sua solicitação. ' .
                'Tente novamente mais tarde.';
        }
    }

    require __DIR__ . '/../views/matricula/pagamento.php';
}

// ==================================================
// ETAPA 4 - RESULTADO
// ==================================================

elseif ($acao === 'sucesso') {

    $resultado = $_SESSION['matricula_sucesso'] ?? null;

    if (empty($resultado)) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }

    require __DIR__ . '/../views/matricula/sucesso.php';
}
