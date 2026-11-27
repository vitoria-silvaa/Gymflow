<?php
// ============================================
// GYMFLOW - CONTROLLER DE RECIBOS
// app/controllers/ReciboAlunoController.php
// ============================================

// Evita que avisos HTML contaminem o JSON.
// Os erros continuam sendo registrados no log.

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Aluno']);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

// ============================================
// FUNÇÃO DE RESPOSTA
// ============================================

function responderRecibo(
    int $status,
    array $dados
): void {

    http_response_code($status);

    echo json_encode(
        $dados,
        JSON_UNESCAPED_UNICODE |
            JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

// ============================================
// VALIDAR MÉTODO HTTP
// ============================================

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {

    header('Allow: GET');

    responderRecibo(405, [
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ]);
}

// ============================================
// VALIDAR SESSÃO
// ============================================

$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);

$alunoId = (int) ($_SESSION['aluno_id'] ?? 0);

$empresaId = (int) ($_SESSION['company_id'] ?? 0);

if (
    $usuarioId <= 0 ||
    $alunoId <= 0 ||
    $empresaId <= 0
) {

    responderRecibo(403, [
        'sucesso' => false,
        'mensagem' => 'Sessão inválida.'
    ]);
}

// ============================================
// VALIDAR ID DA FATURA
// ============================================

$contaId = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1
        ]
    ]
);

if (!$contaId) {

    responderRecibo(400, [
        'sucesso' => false,
        'mensagem' => 'Fatura inválida.'
    ]);
}

// ============================================
// CONSULTAR RECIBO
// ============================================

try {

    require_once __DIR__ . '/../models/Database.php';
    require_once __DIR__ . '/../models/ReciboAluno.php';

    $recibo = consultarReciboAluno(
        $pdo,
        (int) $contaId,
        $alunoId,
        $usuarioId,
        $empresaId
    );

    // ========================================
    // VALIDAR RESULTADO
    // ========================================

    if (
        !$recibo ||
        $recibo['status'] !== 'Pago'
    ) {

        responderRecibo(404, [

            'sucesso' => false,

            'mensagem' =>
            'Recibo não encontrado ou indisponível.'
        ]);
    }

    // ========================================
    // DATA DE GERAÇÃO
    // ========================================

    $dataGeracao = new DateTimeImmutable(
        'now',
        new DateTimeZone('America/Sao_Paulo')
    );

    $recibo['gerado_em'] = $dataGeracao->format(
        'd/m/Y H:i:s'
    );

    // ========================================
    // RETORNAR DADOS DO RECIBO
    // ========================================

    responderRecibo(200, [

        'sucesso' => true,

        'recibo' => $recibo
    ]);
} catch (Throwable $e) {

    error_log(
        '[GymFlow][ReciboAluno] ' .
            $e->getMessage()
    );

    responderRecibo(500, [

        'sucesso' => false,

        'mensagem' =>
        'Não foi possível consultar o recibo.'
    ]);
}
