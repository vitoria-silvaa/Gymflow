<?php
// app/controllers/FluxoCaixaController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin']);

$acao = $_GET['acao'] ?? 'listar';


// listar fluxo de caixa
if ($acao === 'listar') {

    $operacao = 'listar_fluxo';
    require __DIR__ . '/../models/FluxoCaixa.php';

    $tituloPagina = "Fluxo de Caixa";
    require __DIR__ . '/../views/fluxo_caixa/index.php';

    exit;
}


// lançar custo
if ($acao === 'lancar') {

    $tituloPagina = "Lançar custo";

    require __DIR__ . '/../views/fluxo_caixa/lancar.php';

    exit;
}


// confirmar lançamento
if ($acao === 'confirmar_lancamento') {

    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $valor = $_POST['valor'] ?? '';
    $data = trim($_POST['data'] ?? '');

    if (
        $descricao === '' ||
        $categoria === '' ||
        $valor === '' ||
        $data === ''
    ) {
        header("Location: /Gymflow/app/controllers/FluxoCaixaController.php");
        exit;
    }

    $operacao = 'criar_custo';

    require __DIR__ . '/../models/FluxoCaixa.php';

    header("Location: /Gymflow/app/controllers/FluxoCaixaController.php");
    exit;
}