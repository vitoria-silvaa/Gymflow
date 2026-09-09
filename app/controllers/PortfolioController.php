<?php
// app/controllers/PortfolioController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

// Apenas Admin pode configurar o portfólio
verificarRole(['Admin']);

$tituloPagina = "Portfólio";
$acao = $_GET['acao'] ?? 'index';
$company_id = 1;

// ======================================================
// 1. SALVAR ALTERAÇÕES
// ======================================================
if ($acao === 'salvar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $operacao = 'salvar';
    $dadosPost = $_POST;
    $dadosFiles = $_FILES;

    require __DIR__ . '/../models/Portfolio.php';

    if (!empty($erroModel)) {
        header("Location: /Gymflow/app/controllers/PortfolioController.php?status=erro");
        exit;
    }

    header("Location: /Gymflow/app/controllers/PortfolioController.php?status=sucesso");
    exit;
}

// ======================================================
// 2. EXIBIR PAINEL DE CONFIGURAÇÕES (Admin)
// ======================================================
$operacao = 'buscar_admin';
require __DIR__ . '/../models/Portfolio.php';

require __DIR__ . '/../views/portfolio/index.php';
