<?php
// app/models/Financeiro.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';
$company_id = $_SESSION['company_id'] ?? 0;

// listar contas

if ($operacao === 'listar_contas') {

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
        INNER JOIN alunos a ON a.id = c.aluno_id
        INNER JOIN filiais f ON f.id = a.filial_id
        WHERE f.company_id = :company_id
        ORDER BY c.vencimento ASC
    ");
    $stmt->execute([':company_id' => $company_id]);
    $contas = $stmt->fetchAll();


    // total em aberto
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(c.valor), 0) AS total
        FROM contas c
        INNER JOIN alunos a ON a.id = c.aluno_id
        INNER JOIN filiais f ON f.id = a.filial_id
        WHERE c.status != 'Pago' AND f.company_id = ?
    ");
    $stmt->execute([$company_id]);
    $totalAberto = $stmt->fetch()['total'];


    // total recebido
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(c.valor), 0) AS total
        FROM contas c
        INNER JOIN alunos a ON a.id = c.aluno_id
        INNER JOIN filiais f ON f.id = a.filial_id
        WHERE c.status = 'Pago' AND f.company_id = ?
    ");
    $stmt->execute([$company_id]);
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
        INNER JOIN alunos a ON a.id = c.aluno_id
        INNER JOIN filiais f ON f.id = a.filial_id
        WHERE c.id = :id AND f.company_id = :company_id
        LIMIT 1
    ");

    $stmt->execute([
        ':id'         => $conta_id,
        ':company_id' => $company_id
    ]);

    $conta = $stmt->fetch() ?: null;
}


// confirmar pagamento

elseif ($operacao === 'baixar_pagamento') {

    $conta_id = (int) ($conta_id ?? 0);
    $forma_pagamento = trim($forma_pagamento ?? '');

    if ($conta_id > 0 && $forma_pagamento !== '') {

        // Verifica se a conta pertence a um aluno desta empresa antes de atualizar
        $stmt = $pdo->prepare("
            UPDATE contas c
            INNER JOIN alunos a ON a.id = c.aluno_id
            INNER JOIN filiais f ON f.id = a.filial_id
            SET
                c.status = 'Pago',
                c.forma_pagamento = :forma_pagamento,
                c.pago_em = CURRENT_TIMESTAMP
            WHERE c.id = :id
              AND c.status != 'Pago'
              AND f.company_id = :company_id
        ");

        $stmt->execute([
            ':forma_pagamento' => $forma_pagamento,
            ':id'              => $conta_id,
            ':company_id'      => $company_id
        ]);
    }
}


// listar faturas do aluno

elseif ($operacao === 'listar_faturas_aluno') {

    $aluno_id = (int) ($aluno_id ?? $alunoId ?? $_SESSION['aluno_id'] ?? 0);

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