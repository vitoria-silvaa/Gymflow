<?php
// =============================================
// GYMFLOW - ALTERAÇÃO DE TEMA DO ALUNO
// app/controllers/TemaAlunoController.php
// =============================================

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Aluno']);

// =============================================
// 1. VALIDAR MÉTODO
// =============================================

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {

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
    !hash_equals($tokenSessao, $tokenFormulario)
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
// 4. VALIDAR PÁGINA DE RETORNO
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

$acaoRetorno =
    $_POST['acao_retorno'] ?? 'aluno';

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

// =============================================
// 5. PRESERVAR ABA DAS FATURAS
// =============================================

if ($acaoRetorno === 'faturas') {

    $aba = $_POST['aba'] ?? '';

    if (in_array(
        $aba,
        ['vencer', 'atraso', 'pagas'],
        true
    )) {

        $parametros['aba'] = $aba;
    }
}

// =============================================
// 6. PRESERVAR TREINOS E FREQUÊNCIA
// =============================================

if ($acaoRetorno === 'treinos') {

    if (
        ($_POST['visualizacao'] ?? '')
        === 'frequencia'
    ) {

        $parametros['visualizacao'] =
            'frequencia';
    }

    $ficha = filter_var(
        $_POST['ficha'] ?? null,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ]
        ]
    );

    if ($ficha) {

        $parametros['ficha'] = $ficha;
    }
}

// =============================================
// 7. MONTAR URL DE RETORNO
// =============================================

$urlRetorno = BASE_URL .
    '/app/controllers/PortalAlunoController.php?' .
    http_build_query($parametros);

// =============================================
// 8. SALVAR PREFERÊNCIA
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

    exit('Não foi possível alterar o tema. Tente novamente.');
}

// =============================================
// 9. RETORNAR À PÁGINA ORIGINAL
// =============================================

header(
    'Location: ' . $urlRetorno,
    true,
    303
);

exit;
