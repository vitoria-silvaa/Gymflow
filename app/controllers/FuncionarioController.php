<?php
// app/controllers/FuncionarioController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';
verificarRole(['Admin', 'Professor', 'Recepcao']);

$baseUrl = '/Gymflow/app/controllers/FuncionarioController.php';
$acao = $_GET['acao'] ?? 'listar';

/* 1. LISTAR FUNCIONÁRIOS */
if ($acao === 'listar') {
    $nome_busca = trim($_GET['nome_busca'] ?? '');
    $role_busca = trim($_GET['role_busca'] ?? '');

    $operacao = 'listar';
    require __DIR__ . '/../models/Funcionario.php';

    require __DIR__ . '/../views/funcionarios/index.php';
}

/* 2. CADASTRAR FUNCIONÁRIO */
elseif ($acao === 'cadastrar') {
    $erro = '';
    $dados = [];

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $dados = [
            'nome'      => trim($_POST['nome'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'senha'     => $_POST['senha'] ?? '',
            'role'      => $_POST['role'] ?? '',
            'id_filial' => (int) ($_POST['id_filial'] ?? 0)
        ];

        if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha']) || empty($dados['role']) || $dados['id_filial'] <= 0) {
            $erro = "Por favor, preencha todos os campos obrigatórios (*).";
        } else {
            $operacao = 'cadastrar';
            require __DIR__ . '/../models/Funcionario.php';

            if (empty($erroModel)) {
                header("Location: $baseUrl?acao=listar");
                exit;
            }
            $erro = $erroModel;
        }
    }

    // Busca filiais ativas para preencher o select
    $statusFiltro = 'Ativa';
    $operacao = 'listar';
    require __DIR__ . '/../models/Filial.php';

    require __DIR__ . '/../views/funcionarios/cadastrar.php';
}

/* 3. EDITAR FUNCIONÁRIO */
elseif ($acao === 'editar') {
    $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

    if ($id <= 0) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $operacao = 'buscar';
    require __DIR__ . '/../models/Funcionario.php';

    if (!$funcionario) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $erro = '';

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $dados = [
            'nome'      => trim($_POST['nome'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'senha'     => $_POST['senha'] ?? '',
            'role'      => $_POST['role'] ?? '',
            'id_filial' => (int) ($_POST['id_filial'] ?? 0)
        ];

        if (empty($dados['nome']) || empty($dados['email']) || empty($dados['role']) || $dados['id_filial'] <= 0) {
            $erro = "Por favor, preencha todos os campos obrigatórios (*).";
        } else {
            $operacao = 'atualizar';
            require __DIR__ . '/../models/Funcionario.php';

            if (empty($erroModel)) {
                header("Location: $baseUrl?acao=visualizar&id=$id");
                exit;
            }
            $erro = $erroModel;
        }
    }

    // Recarrega dados atualizados do funcionário
    $operacao = 'buscar';
    require __DIR__ . '/../models/Funcionario.php';

    // Busca filiais ativas para o select
    $statusFiltro = 'Ativa';
    $operacao = 'listar';
    require __DIR__ . '/../models/Filial.php';

    require __DIR__ . '/../views/funcionarios/editar.php';
}

/* 4. VISUALIZAR FUNCIONÁRIO */
elseif ($acao === 'visualizar') {
    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $operacao = 'buscar';
    require __DIR__ . '/../models/Funcionario.php';

    if (!$funcionario) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    require __DIR__ . '/../views/funcionarios/visualizar.php';
}

else {
    header("Location: $baseUrl?acao=listar");
    exit;
}
