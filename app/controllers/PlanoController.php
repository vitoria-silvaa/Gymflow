<?php
// app/controllers/PlanoController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

// Apenas funcionários autorizados gerenciam planos
verificarRole(['Admin', 'Professor', 'Recepcao']);

$baseUrl = '/Gymflow/app/controllers/PlanoController.php';
$acao = $_GET['acao'] ?? 'listar';

/* LISTAR PLANOS */
if ($acao === 'listar') {
    $operacao = 'listar';
    require __DIR__ . '/../models/Plano.php';

    /** @var array $planos */
    require __DIR__ . '/../views/planos/index.php';
}

/* CADASTRAR PLANO */
elseif ($acao === 'cadastrar') {
    $erro = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dados = [];
        $dados['nome'] = trim($_POST['nome'] ?? '');
        $dados['categoria'] = trim($_POST['categoria'] ?? '');
        $dados['valor'] = trim($_POST['valor'] ?? '');
        $dados['duracao'] = $_POST['duracao'] ?? '';
        $dados['company_id'] = 1; // MVP default

        $dados['valor'] = str_replace(',', '.', $dados['valor']);

        if ($dados['nome'] === '' || $dados['categoria'] === '' || $dados['valor'] === '' || $dados['duracao'] === '') {
            $erro = "Por favor, preencha todos os campos obrigatórios (*).";
        } elseif (!is_numeric($dados['valor']) || $dados['valor'] < 0) {
            $erro = "Por favor, insira um valor numérico válido maior ou igual a zero.";
        } else {
            $operacao = 'cadastrar';
            require __DIR__ . '/../models/Plano.php';

            if (empty($erroModel)) {
                header("Location: $baseUrl?acao=listar");
                exit;
            }
            $erro = $erroModel;
        }
    }

    require __DIR__ . '/../views/planos/cadastrar.php';
}

/* EDITAR PLANO */
elseif ($acao === 'editar') {
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

    if ($id <= 0) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $operacao = 'buscar';
    require __DIR__ . '/../models/Plano.php';

    /** @var array|false $plano */
    if (!$plano) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $erro = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dados = [];
        $dados['nome'] = trim($_POST['nome'] ?? '');
        $dados['categoria'] = trim($_POST['categoria'] ?? '');
        $dados['valor'] = trim($_POST['valor'] ?? '');
        $dados['duracao'] = $_POST['duracao'] ?? '';

        $dados['valor'] = str_replace(',', '.', $dados['valor']);

        if ($dados['nome'] === '' || $dados['categoria'] === '' || $dados['valor'] === '' || $dados['duracao'] === '') {
            $erro = "Por favor, preencha todos os campos obrigatórios (*).";
        } elseif (!is_numeric($dados['valor']) || $dados['valor'] < 0) {
            $erro = "Por favor, insira um valor numérico válido maior ou igual a zero.";
        } else {
            $operacao = 'atualizar';
            require __DIR__ . '/../models/Plano.php';

            if (empty($erroModel)) {
                header("Location: $baseUrl?acao=listar");
                exit;
            }
            $erro = $erroModel;
        }

        // Recarrega dados atuais do plano
        $operacao = 'buscar';
        require __DIR__ . '/../models/Plano.php';
    }

    require __DIR__ . '/../views/planos/editar.php';
}

else {
    header("Location: $baseUrl?acao=listar");
    exit;
}
