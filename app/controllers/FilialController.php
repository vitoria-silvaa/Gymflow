<?php
// app/controllers/FilialController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';
verificarRole(['Admin']);

$baseUrl = BASE_URL . '/app/controllers/FilialController.php';
$acao = $_GET['acao'] ?? 'listar';

/* 1. LISTAR FILIAIS */
if ($acao === 'listar') {
    $statusFiltro = $_GET['status'] ?? '';
    $mensagem = $_GET['mensagem'] ?? '';

    $operacao = 'listar';
    require __DIR__ . '/../models/Filial.php';

    require __DIR__ . '/../views/filiais/index.php';
}

/* 2. CADASTRAR FILIAL */ elseif ($acao === 'cadastrar') {
    $erro = '';
    $dados = [];

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $dados['nome']        = trim($_POST['nome'] ?? '');
        $dados['cnpj']        = trim($_POST['cnpj'] ?? '');
        $dados['telefone']    = trim($_POST['telefone'] ?? '');
        $dados['responsavel'] = trim($_POST['responsavel'] ?? '');
        $dados['company_id']  = $_SESSION['company_id'] ?? 1;

        if ($dados['nome'] === '' || $dados['cnpj'] === '' || $dados['responsavel'] === '') {
            $erro = "Por favor, preencha todos os campos obrigatórios (*).";
        } else {
            $operacao = 'cadastrar';
            require __DIR__ . '/../models/Filial.php';

            if (empty($erroModel)) {
                header("Location: $baseUrl?acao=listar");
                exit;
            }
            $erro = $erroModel;
        }
    }

    require __DIR__ . '/../views/filiais/cadastrar.php';
}

/* 3. EDITAR FILIAL */ elseif ($acao === 'editar') {
    $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

    if ($id <= 0) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $operacao = 'buscar';
    require __DIR__ . '/../models/Filial.php';

    if (!$filial) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $erro = '';

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $dados = [
            'nome'        => trim($_POST['nome'] ?? ''),
            'cnpj'        => trim($_POST['cnpj'] ?? ''),
            'telefone'    => trim($_POST['telefone'] ?? ''),
            'responsavel' => trim($_POST['responsavel'] ?? ''),
            'company_id'  => $filial['company_id'] ?? $_SESSION['company_id'] ?? 1
        ];

        if ($dados['nome'] === '' || $dados['cnpj'] === '' || $dados['responsavel'] === '') {
            $erro = "Por favor, preencha todos os campos obrigatórios (*).";
        } else {
            $operacao = 'atualizar';
            require __DIR__ . '/../models/Filial.php';

            if (empty($erroModel)) {
                header("Location: $baseUrl?acao=listar");
                exit;
            }
            $erro = $erroModel;
        }

        // Recarrega os dados atualizados para exibição no formulário
        $operacao = 'buscar';
        require __DIR__ . '/../models/Filial.php';
    }

    require __DIR__ . '/../views/filiais/editar.php';
}

/* 4. ATIVAR / INATIVAR FILIAL */ elseif ($acao === 'alternar_status') {
    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    // Busca o status atual antes de alterar
    $operacao = 'buscar';
    require __DIR__ . '/../models/Filial.php';

    if (!$filial) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $filialAtiva = (int) $filial['ativo'] === 1;

    // Altera o status
    $operacao = 'alternar_status';
    require __DIR__ . '/../models/Filial.php';

    if (empty($erroModel)) {

        $mensagem = $filialAtiva
            ? 'Filial inativada'
            : 'Filial reativada';

        header(
            "Location: $baseUrl?acao=listar&mensagem=" . urlencode($mensagem)
        );
        exit;
    }

    header("Location: $baseUrl?acao=listar");
    exit;
}
