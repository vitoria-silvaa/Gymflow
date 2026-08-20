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
                $user_id = $usuario['id'];
                $operacao = 'buscar';
                $preferencias = null;

                require __DIR__ . '/../models/MinhaMarca.php';

                if ($preferencias) {
                    $_SESSION['nome_painel'] = $preferencias['nome_painel'];
                    $_SESSION['tema'] = $preferencias['tema'];
                    $_SESSION['cor_primaria'] = $preferencias['cor_primaria'];
                    $_SESSION['cor_secundaria'] = $preferencias['cor_secundaria'];
                    $_SESSION['tema_predefinido'] = $preferencias['tema_predefinido'];
                    $_SESSION['logo_url'] = $preferencias['logo_url'];
                } else {
                    $_SESSION['nome_painel'] = 'Gymflow';
                    $_SESSION['tema'] = 'dark';
                    $_SESSION['cor_primaria'] = '#ffb000';
                    $_SESSION['cor_secundaria'] = '#000000';
                    $_SESSION['tema_predefinido'] = 'padrao';
                    $_SESSION['logo_url'] = null;
                }

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

/* 2. LOGOUT */ elseif ($acao === 'logout') {
    efetuarLogout();
} else {
    header("Location: $baseUrl?acao=login");
    exit;
}
