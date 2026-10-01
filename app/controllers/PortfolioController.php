<?php
// app/controllers/PortfolioController.php


require_once __DIR__ . '/../../config/sessao.php';

// Apenas Admin pode configurar o portfólio
verificarRole(['Admin']);

$tituloPagina = "Portfólio";
$acao = $_GET['acao'] ?? 'index';
$company_id = $_SESSION['company_id'] ?? (int)($_GET['id'] ?? 1);

// ======================================================
// 1. SALVAR ALTERAÇÕES
// ======================================================
if ($acao === 'salvar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $operacao = 'salvar';
    $dadosPost = $_POST;
    $dadosFiles = $_FILES;

    require __DIR__ . '/../models/Portfolio.php';

    if (!empty($erroModel)) {
        header("Location: " . BASE_URL . "/app/controllers/PortfolioController.php?status=erro");
        exit;
    }

    header("Location: " . BASE_URL . "/app/controllers/PortfolioController.php?status=sucesso");
    exit;
}

// ======================================================
// 2. EXIBIR PAINEL DE CONFIGURAÇÕES (Admin)
// ======================================================
$operacao = 'buscar_admin';
require __DIR__ . '/../models/Portfolio.php';

require __DIR__ . '/../views/portfolio/index.php';
