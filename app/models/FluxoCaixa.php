<?php
// app/models/FluxoCaixa.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';
$company_id = $_SESSION['company_id'] ?? 0;


// listar fluxo de caixa
if ($operacao === 'listar_fluxo') {

    // total de receitas recebidas (apenas desta empresa)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(c.valor), 0) AS total
        FROM contas c
        INNER JOIN alunos a ON a.id = c.aluno_id
        INNER JOIN filiais f ON f.id = a.filial_id
        WHERE c.status = 'Pago' AND f.company_id = ?
    ");
    $stmt->execute([$company_id]);
    $totalReceitas = $stmt->fetch()['total'];


    // total de custos (apenas desta empresa via filiais)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(cu.valor), 0) AS total
        FROM custos cu
        INNER JOIN filiais f ON f.id = cu.filial_id
        WHERE f.company_id = ?
    ");
    $stmt->execute([$company_id]);
    $totalCustos = $stmt->fetch()['total'];


    // resultado
    $resultado = $totalReceitas - $totalCustos;


    // quantidade de pagamentos
    $stmt = $pdo->prepare("
        SELECT COUNT(c.id) AS total
        FROM contas c
        INNER JOIN alunos a ON a.id = c.aluno_id
        INNER JOIN filiais f ON f.id = a.filial_id
        WHERE c.status = 'Pago' AND f.company_id = ?
    ");
    $stmt->execute([$company_id]);
    $quantidadeReceitas = $stmt->fetch()['total'];


    // quantidade de custos
    $stmt = $pdo->prepare("
        SELECT COUNT(cu.id) AS total
        FROM custos cu
        INNER JOIN filiais f ON f.id = cu.filial_id
        WHERE f.company_id = ?
    ");
    $stmt->execute([$company_id]);
    $quantidadeCustos = $stmt->fetch()['total'];


    // listar custos
    $stmt = $pdo->prepare("
        SELECT
            cu.id,
            cu.descricao,
            cu.categoria,
            cu.valor,
            cu.data
        FROM custos cu
        INNER JOIN filiais f ON f.id = cu.filial_id
        WHERE f.company_id = ?
        ORDER BY cu.data DESC, cu.id DESC
    ");
    $stmt->execute([$company_id]);
    $custos = $stmt->fetchAll();
}


// criar custo
elseif ($operacao === 'criar_custo') {

    $descricao = trim($descricao ?? '');
    $categoria = trim($categoria ?? '');
    $valor     = (float) ($valor ?? 0);
    $data      = trim($data ?? '');
    // Usa a primeira filial ativa da empresa logada como referência do custo
    $filial_id_custo = (int) ($filial_id ?? 0);

    // Se não veio filial, busca a primeira filial ativa da empresa
    if ($filial_id_custo <= 0) {
        $stmtF = $pdo->prepare("SELECT id FROM filiais WHERE company_id = ? AND ativo = 1 LIMIT 1");
        $stmtF->execute([$company_id]);
        $filial_id_custo = (int) ($stmtF->fetchColumn() ?: 0);
    }

    if (
        $descricao !== '' &&
        $categoria !== '' &&
        $valor > 0 &&
        $data !== '' &&
        $filial_id_custo > 0
    ) {

        $stmt = $pdo->prepare("
            INSERT INTO custos (
                filial_id,
                descricao,
                categoria,
                valor,
                data
            )
            VALUES (
                :filial_id,
                :descricao,
                :categoria,
                :valor,
                :data
            )
        ");

        $stmt->execute([
            ':filial_id'  => $filial_id_custo,
            ':descricao'  => $descricao,
            ':categoria'  => $categoria,
            ':valor'      => $valor,
            ':data'       => $data
        ]);
    }
}