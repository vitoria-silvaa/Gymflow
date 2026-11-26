<?php
// ================================================
// GYMFLOW - CONTROLLER DE MEDIDAS DO ALUNO
// app/controllers/MedidasAlunoController.php
// ================================================

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Aluno']);

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/MedidasAluno.php';

header('Cache-Control: no-store, private');

// ================================================
// 1. ACEITAR SOMENTE CONSULTAS
// ================================================

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {

    http_response_code(405);

    header('Allow: GET');

    exit('Método não permitido.');
}

// ================================================
// 2. IDENTIFICAR ALUNO LOGADO
// ================================================

$medidasUsuarioId = (int) (
    $_SESSION['usuario_id'] ?? 0
);

$medidasAlunoId = (int) (
    $_SESSION['aluno_id'] ?? 0
);

$medidasEmpresaId = (int) (
    $_SESSION['company_id'] ?? 0
);

if (
    $medidasUsuarioId <= 0 ||
    $medidasAlunoId <= 0 ||
    $medidasEmpresaId <= 0
) {

    http_response_code(403);

    exit('Sessão inválida.');
}

// ================================================
// 3. CONSULTAR AVALIAÇÕES
// ================================================

try {

    if (!medidasAlunoAutorizar(
        $pdo,
        $medidasUsuarioId,
        $medidasAlunoId,
        $medidasEmpresaId
    )) {

        http_response_code(403);

        exit('Não foi possível validar seu cadastro.');
    }

    $avaliacoesAluno = consultarAvaliacoesMedidasAluno(
        $pdo,
        $medidasUsuarioId,
        $medidasAlunoId,
        $medidasEmpresaId
    );

    // ============================================
    // 4. PREPARAR DADOS DA VIEW
    // ============================================

    $dadosMedidasPortal = [

        'avaliacoes' => $avaliacoesAluno,

        'campos' => camposAvaliacaoFisica()

    ];

    // ============================================
    // 5. EXIBIR PÁGINA
    // ============================================

    require __DIR__ .
        '/../views/portal_aluno/minhas_medidas.php';

    exit;
} catch (Throwable $e) {

    error_log(
        '[GymFlow][MedidasAluno] ' .
            $e->getMessage()
    );

    http_response_code(500);

    exit('Não foi possível carregar suas avaliações.');
}
