<?php
// app/controllers/AlunoController.php

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin', 'Recepcao']);

$baseUrl = BASE_URL . '/app/controllers/AlunoController.php';

$tituloPagina = 'Alunos';

$acao = $_GET['acao'] ?? 'listar';

$texto = static fn($valor) =>
    is_string($valor) ? trim($valor) : '';

$inteiro = static fn($valor) =>
    (int) filter_var(
        $valor,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

// Padronização visual do CPF.
$formatarCpf = static function ($valor): string {

    $original = trim((string) $valor);

    if (!preg_match('/^[0-9.\- ]+$/', $original)) {
        return $original;
    }

    $cpf = preg_replace('/\D/', '', $original);

    if (strlen($cpf) !== 11) {
        return $original;
    }

    return substr($cpf, 0, 3) . '.' .
           substr($cpf, 3, 3) . '.' .
           substr($cpf, 6, 3) . '-' .
           substr($cpf, 9, 2);
};

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$erro = '';
$sucesso = '';

if (
    empty($_SESSION['aluno_csrf']) ||
    !is_string($_SESSION['aluno_csrf'])
) {
    $_SESSION['aluno_csrf'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['aluno_csrf'];

$redirecionar = static function (
    string $destino
): void {

    header('Location: ' . $destino, true, 303);
    exit;
};

$flash = $_SESSION['aluno_flash'] ?? [];

unset($_SESSION['aluno_flash']);

$erro = $flash['erro'] ?? '';
$sucesso = $flash['sucesso'] ?? '';

try {

    // Listagem de alunos.

    if ($acao === 'listar' && $metodo === 'GET') {

        $cpf = $texto($_GET['cpf'] ?? '');
        $status = $texto($_GET['status'] ?? '');

        $filial_id_filter = $inteiro(
            $_GET['filial_id'] ?? 0
        );

        if (!in_array(
            $status,
            ['', 'Ativo', 'Inativo', 'Pendente'],
            true
        )) {
            $status = '';
        }

        $operacao = 'listar_filiais';

        require __DIR__ . '/../models/Aluno.php';

        $idsAutorizados = array_map(
            'intval',
            array_column($filiais, 'id')
        );

        if (
            $filial_id_filter > 0 &&
            !in_array(
                $filial_id_filter,
                $idsAutorizados,
                true
            )
        ) {
            $_SESSION['aluno_flash'] = [
                'erro' => 'Você não possui acesso à filial selecionada.'
            ];

            $redirecionar($baseUrl . '?acao=listar');
        }

        $operacao = 'listar';

        require __DIR__ . '/../models/Aluno.php';

        if (!empty($erroModel)) {
            $erro = $erroModel;
        }

        // Padroniza os CPFs apresentados na tabela.

        foreach ($alunos as $indice => $registro) {

            $alunos[$indice]['cpf'] = $formatarCpf(
                $registro['cpf'] ?? ''
            );
        }

        $cpf = $formatarCpf($cpf);

        require __DIR__ . '/../views/alunos/index.php';

        exit;
    }

    // Visualização individual.

    if ($acao === 'visualizar' && $metodo === 'GET') {

        $tituloPagina = 'Dados do aluno';

        $id = $inteiro($_GET['id'] ?? 0);

        $operacao = 'visualizar';

        require __DIR__ . '/../models/Aluno.php';

        if (!$aluno) {

            $_SESSION['aluno_flash'] = [
                'erro' => $erroModel ?:
                    'Aluno não encontrado ou não autorizado.'
            ];

            $redirecionar($baseUrl . '?acao=listar');
        }

        // Padroniza o CPF nos dados cadastrais.

        $aluno['cpf'] = $formatarCpf(
            $aluno['cpf'] ?? ''
        );

        require __DIR__ . '/../views/alunos/visualizar.php';

        exit;
    }

    // Edição cadastral.

    if (
        $acao === 'editar' &&
        in_array($metodo, ['GET', 'POST'], true)
    ) {

        $tituloPagina = 'Editar aluno';

        $id = $inteiro($_GET['id'] ?? $_POST['id'] ?? 0);

        $operacao = 'buscar';

        require __DIR__ . '/../models/Aluno.php';

        if (!$aluno) {

            $_SESSION['aluno_flash'] = [
                'erro' => 'Aluno não encontrado ou não autorizado.'
            ];

            $redirecionar($baseUrl . '?acao=listar');
        }

        $dados = $aluno;

        if ($metodo === 'POST') {

            $token = $_POST['csrf'] ?? null;

            if (
                !is_string($token) ||
                !hash_equals($csrf, $token)
            ) {

                $erro =
                    'A sessão do formulário expirou. Tente novamente.';

            } else {

                foreach ([
                    'nome',
                    'rg',
                    'telefone',
                    'endereco'
                ] as $campo) {

                    $dados[$campo] = $texto(
                        $_POST[$campo] ?? ''
                    );
                }

                $operacao = 'atualizar';

                require __DIR__ . '/../models/Aluno.php';

                if ($erroModel === '') {

                    $_SESSION['aluno_flash'] = [
                        'sucesso' =>
                            'Dados cadastrais atualizados com sucesso.'
                    ];

                    $redirecionar(
                        $baseUrl .
                        '?acao=visualizar&id=' .
                        $id
                    );
                }

                $erro = $erroModel;
            }
        }

        // Padroniza o CPF somente para exibição.

        $aluno['cpf'] = $formatarCpf(
            $aluno['cpf'] ?? ''
        );

        $dados['cpf'] = $aluno['cpf'];

        require __DIR__ . '/../views/alunos/editar.php';

        exit;
    }

    // Bloquear os fluxos antigos de cadastro e matrícula.

    if (in_array(
        $acao,
        ['cadastrar', 'matricular'],
        true
    )) {

        $_SESSION['aluno_flash'] = [
            'erro' =>
                'A matrícula interna está sendo integrada ao novo fluxo.'
        ];

        $redirecionar($baseUrl . '?acao=listar');
    }

    // A baixa antiga não pode mais atualizar contas.

    if ($acao === 'pagar') {

        $_SESSION['aluno_flash'] = [
            'erro' =>
                'Os pagamentos devem ser confirmados no módulo Recebimentos.'
        ];

        $redirecionar($baseUrl . '?acao=listar');
    }

    $redirecionar($baseUrl . '?acao=listar');

} catch (Throwable $e) {

    error_log(
        '[GymFlow][AlunoController] ' .
        get_class($e) .
        ' | código ' .
        $e->getCode() .
        ' | linha ' .
        $e->getLine()
    );

    http_response_code(500);

    exit(
        'Não foi possível carregar os dados dos alunos. ' .
        'Tente novamente.'
    );
}