<?php
// app/models/FluxoCaixa.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';


// listar fluxo de caixa
if ($operacao === 'listar_fluxo') {

    // total de receitas recebidas
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM contas
        WHERE status = 'Pago'
    ");

    $totalReceitas = $stmt->fetch()['total'];


    // total de custos
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM custos
    ");

    $totalCustos = $stmt->fetch()['total'];


    // resultado
    $resultado = $totalReceitas - $totalCustos;


    // quantidade de pagamentos
    $stmt = $pdo->query("
        SELECT COUNT(id) AS total
        FROM contas
        WHERE status = 'Pago'
    ");

    $quantidadeReceitas = $stmt->fetch()['total'];


    // quantidade de custos
    $stmt = $pdo->query("
        SELECT COUNT(id) AS total
        FROM custos
    ");

    $quantidadeCustos = $stmt->fetch()['total'];


    // listar custos
    $stmt = $pdo->query("
        SELECT
            id,
            descricao,
            categoria,
            valor,
            data
        FROM custos
        ORDER BY data DESC, id DESC
    ");

    $custos = $stmt->fetchAll();
}


// criar custo
elseif ($operacao === 'criar_custo') {

    $descricao = trim($descricao ?? '');
    $categoria = trim($categoria ?? '');
    $valor = (float) ($valor ?? 0);
    $data = trim($data ?? '');


    if (
        $descricao !== '' &&
        $categoria !== '' &&
        $valor > 0 &&
        $data !== ''
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
                1,
                :descricao,
                :categoria,
                :valor,
                :data
            )
        ");

        $stmt->execute([
            ':descricao' => $descricao,
            ':categoria' => $categoria,
            ':valor' => $valor,
            ':data' => $data
        ]);
    }
}