<?php
// =============================================
// GYMFLOW - ALTERAÇÃO DE TEMA DO ALUNO
// app/controllers/TemaAlunoController.php
// =============================================

require_once __DIR__ . '/../../config/sessao.php';

// Exclusivo para alunos.

verificarRole(['Aluno']);

// =============================================
// 1. ACEITAR SOMENTE POST
// =============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);
    header('Allow: POST');

    exit('Método não permitido.');
}

// =============================================
// 2. PROTEÇÃO CSRF
// =============================================

$tokenSessao =
    $_SESSION['tema_aluno_csrf'] ?? '';

$tokenFormulario =
    $_POST['csrf_token'] ?? '';

if (
    !is_string($tokenSessao) ||
    !is_string($tokenFormulario) ||
    $tokenSessao === '' ||
    $tokenFormulario === '' ||
    !hash_equals(
        $tokenSessao,
        $tokenFormulario
    )
) {

    http_response_code(403);

    exit('Solicitação inválida.');
}

// =============================================
// 3. VALIDAR TEMA
// =============================================

$tema = $_POST['tema'] ?? '';

if (!in_array(
    $tema,
    ['light', 'dark'],
    true
)) {

    http_response_code(400);

    exit('Tema inválido.');
}

// =============================================
// 4. DEFINIR PÁGINA DE RETORNO
// =============================================

$acoesPermitidas = [
    'aluno',
    'treinos',
    'faturas',
    'contratos',
    'avaliacao',
    'meus_dados',
    'minhas_medidas'
];

$acaoRetorno = $_POST['acao_retorno'] ?? 'aluno';

if (!in_array(
    $acaoRetorno,
    $acoesPermitidas,
    true
)) {
    $acaoRetorno = 'aluno';
}

$parametros = [
    'acao' => $acaoRetorno
];

// Preserva a aba das faturas.

if ($acaoRetorno === 'faturas') {

    $aba = $_POST['aba'] ?? '';

    if (
        is_string($aba) &&
        preg_match('/^[a-z_-]{1,30}$/', $aba)
    ) {
        $parametros['aba'] = $aba;
    }
}

// Preserva a ficha selecionada.

if ($acaoRetorno === 'treinos') {

    $ficha = filter_var(
        $_POST['ficha'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($ficha && $ficha > 0) {
        $parametros['ficha'] = $ficha;
    }
}

$urlRetorno = BASE_URL .
    '/app/controllers/PortalAlunoController.php?' .
    http_build_query($parametros);

// =============================================
// 5. SALVAR PREFERÊNCIA
// =============================================

try {

    require_once __DIR__ .
        '/../models/Database.php';

    require_once __DIR__ .
        '/../models/IdentidadePortalAluno.php';

    salvarTemaPortalAluno(
        $pdo,
        $tema
    );
} catch (Throwable $e) {

    error_log(
        '[GymFlow][TemaAluno] ' .
            $e->getMessage()
    );

    http_response_code(500);

    exit('Não foi possível alterar o tema. ' .
        'Tente novamente.');
}

// =============================================
// 6. RETORNAR AO PORTAL
// =============================================

header(
    'Location: ' . $urlRetorno,
    true,
    303
);

exit;
