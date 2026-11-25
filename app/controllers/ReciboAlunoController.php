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

    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.aluno_id,
            c.matricula_id,
            c.vencimento,
            c.valor,
            c.status,
            c.forma_pagamento,
            c.pago_em,

            a.nome AS nome_aluno,

            p.nome AS nome_plano,

            f.nome AS nome_filial,
            f.cnpj AS cnpj_filial,

            CONCAT_WS(
                ', ',
                NULLIF(TRIM(f.endereco), ''),
                NULLIF(TRIM(f.numero), ''),
                NULLIF(TRIM(f.complemento), '')
            ) AS endereco_filial

        FROM contas c

        INNER JOIN alunos a
            ON a.id = c.aluno_id

        INNER JOIN filiais f
            ON f.id = a.filial_id

        INNER JOIN matriculas m
            ON m.id = c.matricula_id
            AND m.aluno_id = a.id

        INNER JOIN planos p
            ON p.id = m.plano_id
            AND p.company_id = f.company_id

        INNER JOIN users u
            ON u.aluno_id = a.id
            AND u.company_id = f.company_id

        WHERE c.id = :conta_id
          AND c.status = 'Pago'
          AND a.id = :aluno_id
          AND u.id = :usuario_id
          AND u.role = 'Aluno'
          AND f.company_id = :company_id

        LIMIT 1
    ");

    $stmt->execute([

        ':conta_id' => $contaId,

        ':aluno_id' => $alunoId,

        ':usuario_id' => $usuarioId,

        ':company_id' => $empresaId
    ]);

    $recibo = $stmt->fetch(
        PDO::FETCH_ASSOC
    ) ?: null;

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
