<?php
// app/controllers/FinanceiroController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin', 'Professor', 'Recepcao']);

$acao = $_GET['acao'] ?? 'listar';

// listar contas 
if ($acao === 'listar') {

    $operacao = 'listar_contas';
    require __DIR__ . '/../models/Financeiro.php';

    $tituloPagina = "Financeiro";
    require __DIR__ . '/../views/financeiro/index.php';
    exit;
}

// baixar pagamento 
if ($acao === 'baixar') {

    $conta_id = (int) ($_GET['id'] ?? 0);

    if ($conta_id <= 0) {
        header("Location: /Gymflow/app/controllers/FinanceiroController.php");
        exit;
    }

    $operacao = 'buscar_conta';
    require __DIR__ . '/../models/Financeiro.php';

    if (!$conta) {
        header("Location: /Gymflow/app/controllers/FinanceiroController.php");
        exit;
    }

    require __DIR__ . '/../views/financeiro/baixar.php';
    exit;
}

// confirmar pagamento 
if ($acao === 'confirmar_pagamento') {

    $conta_id = (int) ($_POST['conta_id'] ?? 0);
    $forma_pagamento = trim($_POST['forma_pagamento'] ?? '');

    if ($conta_id <= 0 || $forma_pagamento === '') {
        header("Location: /Gymflow/app/controllers/FinanceiroController.php");
        exit;
    }

    $operacao = 'baixar_pagamento';
    require __DIR__ . '/../models/Financeiro.php';

    header("Location: /Gymflow/app/controllers/FinanceiroController.php");
    exit;
}