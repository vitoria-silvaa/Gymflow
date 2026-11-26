<?php
// app/controllers/CrmController.php

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin', 'Recepcao']);

$tituloPagina = 'CRM Leads';
$baseUrl = BASE_URL . '/app/controllers/CrmController.php';

$acao = $_GET['acao'] ?? 'listar';
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$admin = ($_SESSION['usuario_role'] ?? '') === 'Admin';

$_SESSION['crm_csrf'] ??= bin2hex(random_bytes(32));

$csrf = $_SESSION['crm_csrf'];

$flash = $_SESSION['crm_flash'] ?? [];

unset($_SESSION['crm_flash']);

$erro = $flash['erro'] ?? '';
$sucesso = $flash['sucesso'] ?? '';

$statusManuais = [
    'Novo',
    'Contato Agendado',
    'Experimental',
    'Perdido'
];

$redirecionar = static function (string $url): void {
    header('Location: ' . $url, true, 303);
    exit;
};

$texto = static fn($v) =>
    is_string($v) ? trim($v) : '';

$inteiro = static fn($v) =>
    is_scalar($v)
        ? (int) filter_var(
            $v,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        )
        : 0;

if ($metodo === 'POST') {

    $token = $_POST['csrf'] ?? null;

    if (
        !is_string($token) ||
        !hash_equals($csrf, $token)
    ) {
        $_SESSION['crm_flash'] = [
            'erro' => 'A sessão do formulário expirou. Tente novamente.'
        ];

        $redirecionar($baseUrl);
    }
}

if ($acao === 'listar' && $metodo === 'GET') {

    $operacao = 'listar';

    require __DIR__ . '/../models/Crm.php';

    if ($erroModel !== '') {
        $erro = $erroModel;
    }

    require __DIR__ . '/../views/crm/index.php';

    exit;
}

if (
    in_array($acao, ['cadastrar', 'editar'], true) &&
    in_array($metodo, ['GET', 'POST'], true)
) {

    $isEdicao = $acao === 'editar';

    $tituloPagina = $isEdicao
        ? 'Editar lead'
        : 'Novo lead';

    $id = $isEdicao
        ? $inteiro($_GET['id'] ?? $_POST['id'] ?? 0)
        : 0;

    $dados = ['status' => 'Novo'];

    if ($isEdicao) {

        $operacao = 'buscar';

        require __DIR__ . '/../models/Crm.php';

        if (!$lead) {

            $_SESSION['crm_flash'] = [
                'erro' => 'Lead não encontrado ou não autorizado.'
            ];

            $redirecionar($baseUrl);
        }

        if (
            !empty($lead['matricula_id']) ||
            in_array(
                $lead['status'],
                ['Convertido', 'Aguardando pagamento'],
                true
            )
        ) {

            $_SESSION['crm_flash'] = [
                'erro' => 'Este lead possui seu histórico protegido.'
            ];

            $redirecionar($baseUrl);
        }

        $dados = $lead;
    }

    if ($metodo === 'POST') {

        foreach ([
            'nome',
            'telefone',
            'objetivo',
            'campanha'
        ] as $campo) {

            $dados[$campo] = $texto($_POST[$campo] ?? '');
        }

        $dados['filial_id'] = $inteiro(
            $_POST['filial_id'] ?? 0
        );

        $dados['status'] = $isEdicao
            ? $texto($_POST['status'] ?? '')
            : 'Novo';

        $operacao = $isEdicao
            ? 'atualizar'
            : 'cadastrar';

        require __DIR__ . '/../models/Crm.php';

        if ($erroModel === '') {

            $_SESSION['crm_flash'] = [
                'sucesso' => $isEdicao
                    ? 'Lead atualizado com sucesso.'
                    : 'Lead cadastrado com sucesso.'
            ];

            $redirecionar($baseUrl);
        }

        $erro = $erroModel;
    }

    $operacao = 'listar_filiais';

    require __DIR__ . '/../models/Crm.php';

    if ($erro === '' && $erroModel !== '') {
        $erro = $erroModel;
    }

    require __DIR__ . '/../views/crm/cadastrar.php';

    exit;
}

if (
    in_array($acao, ['atualizar_status', 'excluir'], true) &&
    $metodo === 'POST'
) {

    $id = $inteiro($_POST['id'] ?? 0);
    $status = $texto($_POST['status'] ?? '');

    $operacao = $acao;

    require __DIR__ . '/../models/Crm.php';

    $_SESSION['crm_flash'] = $erroModel === ''
        ? [
            'sucesso' => $acao === 'excluir'
                ? 'Lead excluído.'
                : 'Etapa atualizada.'
        ]
        : ['erro' => $erroModel];

    $redirecionar($baseUrl);
}

http_response_code(405);

exit('Operação não permitida.');