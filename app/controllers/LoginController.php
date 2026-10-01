<?php
// app/controllers/LoginController.php


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
                // Token dedicado para reset (não contamina o remember_token)
                $token   = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $stmtToken = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
                $stmtToken->execute([$token, $expires, $user['id']]);

                $link = BASE_URL . "/app/controllers/LoginController.php?acao=redefinir_senha&token=" . $token;

                // Em desenvolvimento: exibir link. Em produção: integrar PHPMailer/SendGrid.
                if (defined('BASE_URL') && str_contains(BASE_URL, 'localhost')) {
                    $sucesso = "Link de recuperação (ambiente local):<br><a href='$link'>$link</a>";
                } else {
                    // TODO: enviar e-mail real com o $link
                    $sucesso = "Se o e-mail existir, você receberá um link de recuperação em breve.";
                }
            } else {
                // Por segurança, não revelamos se o e-mail existe
                $sucesso = "Se o e-mail existir, você receberá um link de recuperação em breve.";
            }
        }
    }
    require __DIR__ . '/../views/login/esqueci_senha.php';
}

/* 4. REDEFINIR SENHA */
elseif ($acao === 'redefinir_senha') {
    $token = $_GET['token'] ?? '';
    if (!$token) {
        header("Location: $baseUrl?acao=login");
        exit;
    }
    require_once __DIR__ . '/../../config/conexao.php';
    // Valida token dedicado de reset com expiração
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $erro = "Link de recuperação inválido ou expirado.";
        require __DIR__ . '/../views/login/esqueci_senha.php';
        exit;
    }

    $erro = '';
    $sucesso = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nova_senha = $_POST['nova_senha'] ?? '';
        if (strlen($nova_senha) >= 6) {
            $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            // Limpa o token de reset após uso
            $stmtUpdate = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
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
