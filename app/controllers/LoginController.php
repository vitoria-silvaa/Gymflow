<?php
// app/controllers/LoginController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

$baseUrl = '/Gymflow/app/controllers/LoginController.php';
$acao = $_GET['acao'] ?? 'login';

/* 1. LOGIN */
if ($acao === 'login') {
    // Redireciona estrategicamente se já estiver logado
    if (isset($_SESSION['usuario_id'])) {
        if (($_SESSION['usuario_role'] ?? '') === 'Aluno') {
            header("Location: /Gymflow/app/controllers/PortalAlunoController.php?acao=aluno");
        } else {
            header("Location: /Gymflow/app/controllers/DashboardController.php");
        }
        exit;
    }

    $erro = '';
    $email = '';

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if ($email === '' || $senha === '') {
            $erro = "Por favor, preencha todos os campos.";
        } else {
            $operacao = 'buscar_por_email';
            require __DIR__ . '/../models/Funcionario.php';

            if (!empty($usuario) && password_verify($senha, $usuario['password'])) {
                iniciarSessao($usuario);

                if ($usuario['role'] === 'Aluno') {
                    header("Location: /Gymflow/app/controllers/PortalAlunoController.php?acao=aluno");
                } else {
                    header("Location: /Gymflow/app/controllers/DashboardController.php");
                }
                exit;
            } else {
                $erro = "E-mail ou senha incorretos.";
            }
        }
    }

    require __DIR__ . '/../views/login/index.php';
}

/* 2. LOGOUT */
elseif ($acao === 'logout') {
    efetuarLogout();
}

else {
    header("Location: $baseUrl?acao=login");
    exit;
}
