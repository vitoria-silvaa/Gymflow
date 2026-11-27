<?php
// =============================================
// GYMFLOW - MODEL DE RECIBO DO ALUNO
// app/models/ReciboAluno.php
// =============================================

function consultarReciboAluno(
    PDO $pdo,
    int $contaId,
    int $alunoId,
    int $usuarioId,
    int $empresaId
): ?array {
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
        ':conta_id'   => $contaId,
        ':aluno_id'   => $alunoId,
        ':usuario_id' => $usuarioId,
        ':company_id' => $empresaId,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
