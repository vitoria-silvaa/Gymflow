<?php
// app/controllers/LoginController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

$baseUrl = BASE_URL . '/app/controllers/LoginController.php';
$acao = $_GET['acao'] ?? 'login';

/* 1. LOGIN */
if ($acao === 'login') {
    // Redireciona estrategicamente se já estiver logado
    if (isset($_SESSION['usuario_id'])) {
        if (($_SESSION['usuario_role'] ?? '') === 'Aluno') {
            header("Location: " . BASE_URL . "/app/controllers/PortalAlunoController.php?acao=aluno");
        } else {
            header("Location: " . BASE_URL . "/app/controllers/DashboardController.php");
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

                // Lógica de "Lembrar-me"
                if (!empty($_POST['lembrar'])) {
                    $token = bin2hex(random_bytes(32));
                    $stmtToken = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $stmtToken->execute([$token, $usuario['id']]);
                    setcookie('gymflow_remember', $token, time() + (86400 * 30), "/"); // 30 dias
                }

                if ($usuario['role'] === 'Aluno') {
                    header("Location: " . BASE_URL . "/app/controllers/PortalAlunoController.php?acao=aluno");
                } else {
                    header("Location: " . BASE_URL . "/app/controllers/DashboardController.php");
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
}

/* 3. ESQUECI A SENHA */
elseif ($acao === 'esqueci_senha') {
    $erro = '';
    $sucesso = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        if ($email) {
            require_once __DIR__ . '/../../config/conexao.php';
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Gera token temporário
                $token = bin2hex(random_bytes(16));
                $stmtToken = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmtToken->execute([$token, $user['id']]);

                // Simulação de envio de e-mail (Exibe na tela para testes locais)
                $link = BASE_URL . "/app/controllers/LoginController.php?acao=redefinir_senha&token=" . $token;
                $sucesso = "Se este fosse um servidor real, um e-mail seria enviado. <br>Para fins de teste, clique aqui para redefinir: <a href='$link'>$link</a>";
            } else {
                // Por segurança, não dizemos se o e-mail existe ou não
                $sucesso = "Se o e-mail existir, você receberá um link de recuperação.";
            }
        }
    }
    require __DIR__ . '/../views/login/esqueci_senha.php';
}

/* 4. REDEFINIR SENHA */
elseif ($acao === 'redefinir_senha') {
    $token = $_GET['token'] ?? '';
    if (!$token) {
        die("Token inválido.");
    }
    require_once __DIR__ . '/../../config/conexao.php';
    $stmt = $pdo->prepare("SELECT id FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Token expirado ou inválido.");
    }

    $erro = '';
    $sucesso = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nova_senha = $_POST['nova_senha'] ?? '';
        if (strlen($nova_senha) >= 6) {
            $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmtUpdate = $pdo->prepare("UPDATE users SET password = ?, remember_token = NULL WHERE id = ?");
            $stmtUpdate->execute([$hash, $user['id']]);
            $sucesso = "Senha redefinida com sucesso! <a href='" . BASE_URL . "/app/controllers/LoginController.php?acao=login'>Fazer Login</a>";
        } else {
            $erro = "A senha deve ter pelo menos 6 caracteres.";
        }
    }
    require __DIR__ . '/../views/login/redefinir_senha.php';
}

else {
    header("Location: $baseUrl?acao=login");
    exit;
}
