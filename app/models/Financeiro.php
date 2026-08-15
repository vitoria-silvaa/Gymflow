<?php
// app/models/Financeiro.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';

// listar contas

if ($operacao === 'listar_contas') {

    $stmt = $pdo->query("
        SELECT 
            c.id,
            c.aluno_id,
            c.matricula_id,
            c.vencimento,
            c.valor,
            c.status,
            c.forma_pagamento,
            c.pago_em,
            a.nome AS aluno_nome
        FROM contas c
        INNER JOIN alunos a
            ON a.id = c.aluno_id
        ORDER BY c.vencimento ASC
    ");

    $contas = $stmt->fetchAll();


    // total em aberto
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM contas
        WHERE status != 'Pago'
    ");

    $totalAberto = $stmt->fetch()['total'];


    // total recebido
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM contas
        WHERE status = 'Pago'
    ");

    $totalRecebido = $stmt->fetch()['total'];
}


// buscar uma conta

elseif ($operacao === 'buscar_conta') {

    $conta_id = (int) ($conta_id ?? 0);

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
            a.nome AS aluno_nome
        FROM contas c
        INNER JOIN alunos a
            ON a.id = c.aluno_id
        WHERE c.id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $conta_id
    ]);

    $conta = $stmt->fetch() ?: null;
}


// confirmar pagamento

elseif ($operacao === 'baixar_pagamento') {

    $conta_id = (int) ($conta_id ?? 0);
    $forma_pagamento = trim($forma_pagamento ?? '');

    if ($conta_id > 0 && $forma_pagamento !== '') {

        $stmt = $pdo->prepare("
            UPDATE contas
            SET
                status = 'Pago',
                forma_pagamento = :forma_pagamento,
                pago_em = CURRENT_TIMESTAMP
            WHERE id = :id
              AND status != 'Pago'
        ");

        $stmt->execute([
            ':forma_pagamento' => $forma_pagamento,
            ':id' => $conta_id
        ]);
    }
}


// listar faturas do aluno

elseif ($operacao === 'listar_faturas_aluno') {

    $stmt = $pdo->prepare("
        SELECT
            id,
            vencimento,
            valor,
            status,
            forma_pagamento,
            pago_em
        FROM contas
        WHERE aluno_id = :aluno_id
        ORDER BY vencimento ASC
    ");

    $stmt->execute([
        ':aluno_id' => $aluno_id
    ]);

    $todas_contas = $stmt->fetchAll();
}