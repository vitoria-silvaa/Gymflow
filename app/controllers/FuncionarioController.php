<?php
// app/controllers/FuncionarioController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

// Apenas administradores podem gerenciar funcionários
verificarRole(['Admin']);

$baseUrl = '/Gymflow/app/controllers/FuncionarioController.php';
$acao = $_GET['acao'] ?? 'listar';

/* LISTAR FUNCIONÁRIOS */
if ($acao === 'listar') {
    $nome_busca = trim($_GET['nome_busca'] ?? '');
    $role_busca = trim($_GET['role_busca'] ?? '');

    $operacao = 'listar';
    require __DIR__ . '/../models/Funcionario.php';

    /** @var array $funcionarios */
    require __DIR__ . '/../views/funcionarios/index.php';
}

/* CADASTRAR FUNCIONÁRIO */
elseif ($acao === 'cadastrar') {
    $erro = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $role = $_POST['role'] ?? '';
        $id_filial = (int)($_POST['id_filial'] ?? 0);

        if ($nome === '' || $email === '' || $senha === '' || $role === '' || $id_filial <= 0) {
            $erro = "Por favor, preencha todos os campos obrigatórios (*).";
        } else {
            // Verificar email
            $operacao = 'verificar_email';
            require __DIR__ . '/../models/Funcionario.php';

            /** @var bool $emailExiste */
            if ($emailExiste) {
                $erro = "Este e-mail já está cadastrado.";
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
    }

    // Buscar filiais ativas para o select
    $statusFiltro = 'Ativa';
    $operacao = 'listar';
    require __DIR__ . '/../models/Filial.php';

    /** @var array $filiais */
    require __DIR__ . '/../views/funcionarios/cadastrar.php';
}

/* EDITAR FUNCIONÁRIO */
elseif ($acao === 'editar') {
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

    if ($id <= 0) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $operacao = 'buscar';
    require __DIR__ . '/../models/Funcionario.php';

    /** @var array|false $funcionario */
    /** @var array|false $filial_vinculada */
    if (!$funcionario) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $erro = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? ''; // Nova senha opcional
        $role = $_POST['role'] ?? '';
        $id_filial = (int)($_POST['id_filial'] ?? 0);

        if ($nome === '' || $email === '' || $role === '' || $id_filial <= 0) {
            $erro = "Por favor, preencha todos os campos obrigatórios (*).";
        } else {
            // Verificar email ignorando o próprio ID
            $operacao = 'verificar_email';
            require __DIR__ . '/../models/Funcionario.php';

            if ($emailExiste) {
                $erro = "Este e-mail pertence a outro usuário.";
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
    }

    // Recarregar dados atuais
    $operacao = 'buscar';
    require __DIR__ . '/../models/Funcionario.php';

    // Buscar filiais ativas para o select
    $statusFiltro = 'Ativa';
    $operacao = 'listar';
    require __DIR__ . '/../models/Filial.php';

    require __DIR__ . '/../views/funcionarios/editar.php';
}

/* VISUALIZAR FUNCIONÁRIO */
elseif ($acao === 'visualizar') {
    $id = (int)($_GET['id'] ?? 0);

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
