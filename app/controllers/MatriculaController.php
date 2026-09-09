<?php
// app/controllers/MatriculaController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';
require_once __DIR__ . '/../services/MercadoPagoService.php';

$baseUrl = '/Gymflow/app/controllers/MatriculaController.php';
$acao    = $_GET['acao'] ?? 'identificacao';

// =====================================================================
// ETAPA 1: IDENTIFICAÇÃO DO ALUNO E SELEÇÃO DE PLANO
// =====================================================================
if ($acao === 'identificacao') {
    $erro = '';
    $dados = $_SESSION['matricula'] ?? [];

    // Se receber plano_id via GET, atualiza no estado temporário
    if (!empty($_GET['plano_id'])) {
        $dados['plano_id'] = (int) $_GET['plano_id'];
    }

    // Carrega todos os planos para o select
    $operacao = 'listar_planos';
    require __DIR__ . '/../models/Matricula.php';
    /** @var array $planos */

    // Se já houver um plano selecionado, busca seus dados no banco
    $planoSelecionado = null;
    $planoIdBusca = (int) ($dados['plano_id'] ?? 0);
    if ($planoIdBusca > 0) {
        $operacao = 'buscar_plano';
        $plano_id = $planoIdBusca;
        require __DIR__ . '/../models/Matricula.php';
        /** @var array|null $plano */
        $planoSelecionado = $plano;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $dados['plano_id']   = (int) ($_POST['plano_id'] ?? 0);
        $dados['nome']       = trim($_POST['nome'] ?? '');
        $dados['cpf']        = trim($_POST['cpf'] ?? '');
        $dados['rg']         = trim($_POST['rg'] ?? '');
        $dados['sexo']       = trim($_POST['sexo'] ?? '');
        $dados['nascimento'] = trim($_POST['nascimento'] ?? '');
        $dados['email']      = trim($_POST['email'] ?? '');
        $dados['telefone']   = trim($_POST['telefone'] ?? '');
        $dados['endereco']   = trim($_POST['endereco'] ?? '');
        $dados['senha']      = $_POST['senha'] ?? '';

        // Validações obrigatórias
        if (
            $dados['plano_id'] <= 0 ||
            empty($dados['nome']) ||
            empty($dados['cpf']) ||
            empty($dados['sexo']) ||
            empty($dados['nascimento']) ||
            empty($dados['email']) ||
            empty($dados['telefone']) ||
            empty($dados['senha'])
        ) {
            $erro = 'Por favor, preencha todos os campos obrigatórios marcados com (*).';
        } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erro = 'Por favor, informe um endereço de e-mail válido.';
        } elseif (strlen($dados['senha']) < 6) {
            $erro = 'A senha para o Portal do Aluno deve conter no mínimo 6 caracteres.';
        } else {
            // Validação de existência do plano no banco
            $operacao = 'buscar_plano';
            $plano_id = $dados['plano_id'];
            require __DIR__ . '/../models/Matricula.php';

            if (empty($plano)) {
                $erro = 'O plano selecionado não foi encontrado ou está inativo.';
            } else {
                // Validação de unicidade no banco (CPF em alunos e E-mail em users)
                $operacao = 'validar_dados_cadastrais';
                $cpf   = $dados['cpf'];
                $email = $dados['email'];
                require __DIR__ . '/../models/Matricula.php';

                if (!empty($erroModel)) {
                    $erro = $erroModel;
                } else {
                    // Guarda os dados validados temporariamente na sessão
                    $_SESSION['matricula'] = $dados;
                    header("Location: $baseUrl?acao=filial");
                    exit;
                }
            }
        }

        // Atualiza o plano selecionado caso o formulário tenha mudado
        if ($dados['plano_id'] > 0) {
            $operacao = 'buscar_plano';
            $plano_id = $dados['plano_id'];
            require __DIR__ . '/../models/Matricula.php';
            $planoSelecionado = $plano;
        }
    }

    require __DIR__ . '/../views/matricula/identificacao.php';
}

// =====================================================================
// ETAPA 2: ESCOLHA DA FILIAL / UNIDADE
// =====================================================================
elseif ($acao === 'filial') {
    $matriculaTemp = $_SESSION['matricula'] ?? [];

    if (empty($matriculaTemp['plano_id']) || empty($matriculaTemp['nome'])) {
        header("Location: $baseUrl?acao=identificacao");
        exit;
    }

    $erro = '';

    // Busca detalhes do plano selecionado
    $operacao = 'buscar_plano';
    $plano_id = (int) $matriculaTemp['plano_id'];
    require __DIR__ . '/../models/Matricula.php';
    /** @var array $plano */

    // Busca filiais ativas cadastradas no banco
    $operacao = 'listar_filiais';
    require __DIR__ . '/../models/Matricula.php';
    /** @var array $filiais */

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $filialId = (int) ($_POST['filial_id'] ?? 0);

        if ($filialId <= 0) {
            $erro = 'Por favor, selecione uma filial para treinar.';
        } else {
            // Confirma existência da filial ativa
            $operacao = 'buscar_filial';
            $filial_id = $filialId;
            require __DIR__ . '/../models/Matricula.php';
            /** @var array|null $filial */

            if (empty($filial)) {
                $erro = 'A filial selecionada é inválida ou não está ativa.';
            } else {
                $_SESSION['matricula']['filial_id'] = $filialId;
                header("Location: $baseUrl?acao=pagamento");
                exit;
            }
        }
    }

    require __DIR__ . '/../views/matricula/filial.php';
}

// =====================================================================
// ETAPA 3: FORMA DE PAGAMENTO (MERCADO PAGO)
// =====================================================================
elseif ($acao === 'pagamento') {
    $matriculaTemp = $_SESSION['matricula'] ?? [];

    if (empty($matriculaTemp['plano_id']) || empty($matriculaTemp['filial_id'])) {
        header("Location: $baseUrl?acao=filial");
        exit;
    }

    $erro = '';

    // Recupera dados do plano e da filial do banco
    $operacao = 'buscar_plano';
    $plano_id = (int) $matriculaTemp['plano_id'];
    require __DIR__ . '/../models/Matricula.php';
    /** @var array $plano */

    $operacao = 'buscar_filial';
    $filial_id = (int) $matriculaTemp['filial_id'];
    require __DIR__ . '/../models/Matricula.php';
    /** @var array $filial */

    $mpService = new MercadoPagoService();
    $publicKeyMP = $mpService->getPublicKey();
    $isConfiguredMP = $mpService->isConfigured();

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $metodo = strtolower(trim($_POST['metodo'] ?? 'pix'));
        if (!in_array($metodo, ['pix', 'cartao'])) {
            $metodo = 'pix';
        }

        // Processa a cobrança via serviço do Mercado Pago (com proteção de dados sensíveis)
        $respPagamento = $mpService->processarPagamento([
            'matricula_id' => time(), // identificador temporário para requisição
            'metodo'       => $metodo,
            'valor'        => (float) $plano['valor'],
            'aluno'        => [
                'nome'  => $matriculaTemp['nome'],
                'email' => $matriculaTemp['email'],
                'cpf'   => $matriculaTemp['cpf']
            ],
            'token_cartao' => $_POST['token_cartao'] ?? null,
            'parcelas'     => (int) ($_POST['parcelas'] ?? 1)
        ]);

        // Executa a concretização atômica da matrícula no banco
        $operacao = 'concretizar_matricula';
        $dados = [
            'aluno' => [
                'filial_id'  => $filial['id'],
                'nome'       => $matriculaTemp['nome'],
                'cpf'        => $matriculaTemp['cpf'],
                'rg'         => $matriculaTemp['rg'] ?? null,
                'sexo'       => $matriculaTemp['sexo'],
                'nascimento' => $matriculaTemp['nascimento'],
                'email'      => $matriculaTemp['email'],
                'telefone'   => $matriculaTemp['telefone'],
                'endereco'   => $matriculaTemp['endereco'] ?? null,
                'senha'      => $matriculaTemp['senha']
            ],
            'plano' => [
                'id'      => $plano['id'],
                'nome'    => $plano['nome'],
                'valor'   => $plano['valor'],
                'duracao' => $plano['duracao']
            ],
            'pagamento' => [
                'metodo'          => $metodo,
                'status'          => $respPagamento['status'] ?? 'aprovado',
                'mercado_pago_id' => $respPagamento['mercado_pago_id'] ?? null
            ]
        ];

        require __DIR__ . '/../models/Matricula.php';

        if (!empty($erroModel)) {
            $erro = "Falha ao concretizar a matrícula: " . $erroModel;
        } else {
            // Guarda dados finais para a tela de sucesso e limpa o fluxo temporário
            $_SESSION['matricula_sucesso'] = array_merge($matriculaConcretizada, [
                'pix_copia_cola' => $respPagamento['pix_copia_cola'] ?? null
            ]);
            unset($_SESSION['matricula']);

            header("Location: $baseUrl?acao=sucesso");
            exit;
        }
    }

    require __DIR__ . '/../views/matricula/pagamento.php';
}

// =====================================================================
// ETAPA 4: MATRÍCULA REALIZADA COM SUCESSO
// =====================================================================
elseif ($acao === 'sucesso') {
    $resultado = $_SESSION['matricula_sucesso'] ?? null;

    if (empty($resultado)) {
        header("Location: /Gymflow/index.php");
        exit;
    }

    require __DIR__ . '/../views/matricula/sucesso.php';
}

// =====================================================================
// REDIRECIONAMENTO PADRÃO
// =====================================================================
else {
    header("Location: $baseUrl?acao=identificacao");
    exit;
}
