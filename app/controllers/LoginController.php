<?php
// app/controllers/LoginController.php

require_once __DIR__ . '/../../config/sessao.php';

$baseUrl = BASE_URL . '/app/controllers/LoginController.php';
$acao = $_GET['acao'] ?? 'login';

/* 1. LOGIN */
if ($acao === 'login') {

    // Redireciona se o usuário já estiver logado
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

                    $stmtToken = $pdo->prepare(
                        "UPDATE users SET remember_token = ? WHERE id = ?"
                    );
                    $stmtToken->execute([$token, $usuario['id']]);


                    $https = !empty($_SERVER['HTTPS'])
                        && $_SERVER['HTTPS'] !== 'off';

                    setcookie('gymflow_remember', $token, [
                        'expires' => time() + (86400 * 30),
                        'path' => '/',
                        'secure' => $https,
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
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

    /* 2. LOGOUT */
} elseif ($acao === 'logout') {

    efetuarLogout();

    /* 3. ESQUECI A SENHA */
} elseif ($acao === 'esqueci_senha') {

    $erro = '';
    $sucesso = '';

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $email = trim($_POST['email'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um e-mail válido.';
        } else {
            // A mensagem é igual para e-mails cadastrados ou não.
            $sucesso = 'Se o e-mail estiver cadastrado, você receberá um link de recuperação em breve.';

            try {
                require_once __DIR__ . '/../../config/conexao.php';

                $stmt = $pdo->prepare(
                    "SELECT id, email FROM users WHERE email = ?"
                );
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Cria um token aleatório válido por uma hora.
                    $token = bin2hex(random_bytes(32));
                    $expires = date(
                        'Y-m-d H:i:s',
                        strtotime('+1 hour')
                    );

                    $stmtToken = $pdo->prepare(
                        "UPDATE users
                         SET reset_token = ?, reset_token_expires_at = ?
                         WHERE id = ?"
                    );
                    $stmtToken->execute([
                        $token,
                        $expires,
                        $user['id']
                    ]);

                    try {
                        // Carrega o PHPMailer instalado no projeto.
                        require_once __DIR__ . '/../../vendor/PHPMailer/PHPMailer/src/Exception.php';
                        require_once __DIR__ . '/../../vendor/PHPMailer/PHPMailer/src/PHPMailer.php';
                        require_once __DIR__ . '/../../vendor/PHPMailer/PHPMailer/src/SMTP.php';

                        // Carrega as configurações privadas do Gmail.
                        $emailConfig = require __DIR__ . '/../../config/email.local.php';

                        // Monta o link de recuperação.
                        $link = rtrim($emailConfig['app_url'], '/')
                            . '/app/controllers/LoginController.php?acao=redefinir_senha&token='
                            . urlencode($token);

                        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

                        $mail->isSMTP();
                        $mail->Host = $emailConfig['host'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $emailConfig['username'];
                        $mail->Password = $emailConfig['password'];
                        $mail->SMTPSecure =
                            \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = (int) $emailConfig['port'];

                        $mail->CharSet = 'UTF-8';
                        $mail->setFrom(
                            $emailConfig['username'],
                            'GymFlow'
                        );
                        $mail->addAddress($user['email']);

                        $mail->isHTML(true);
                        $mail->Subject = 'Redefinição de senha do GymFlow';

                        $linkSeguro = htmlspecialchars(
                            $link,
                            ENT_QUOTES,
                            'UTF-8'
                        );

                        $mail->Body = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de senha - GymFlow</title>
</head>
<body style="margin:0; padding:0; background-color:#0b0b0b; font-family:Arial,Helvetica,sans-serif; color:#f5f5f5;">

    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        Recupere seu acesso ao GymFlow de forma segura.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="width:100%; background-color:#0b0b0b;">
        <tr>
            <td align="center" style="padding:36px 14px;">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                    style="width:100%; max-width:600px; background-color:#151515; border:1px solid #303030; border-radius:14px;">

                    <tr>
                        <td style="height:5px; background-color:#ffb000; font-size:0; line-height:0;">
                            &nbsp;
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:34px 24px 22px;">
                            <p style="margin:0; font-size:30px; font-weight:bold; letter-spacing:2px; color:#ffffff;">
                                GYM<span style="color:#ffb000;">FLOW</span>
                            </p>
                            <p style="margin:10px 0 0; font-size:11px; letter-spacing:3px; color:#a3a3a3;">
                                SEU TREINO, SEU RITMO
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:12px 30px 34px;">

                            <h1 style="margin:0 0 18px; font-size:25px; line-height:1.3; color:#ffffff; text-align:center;">
                                Vamos recuperar seu acesso?
                            </h1>

                            <p style="margin:0 0 16px; font-size:15px; line-height:1.8; color:#d1d1d1;">
                                Olá!
                            </p>

                            <p style="margin:0; font-size:15px; line-height:1.8; color:#d1d1d1;">
                                Recebemos uma solicitação para redefinir sua senha do GymFlow.
                                Clique no botão abaixo para criar uma nova senha e voltar a acessar sua conta.
                            </p>

                            <!-- Espaço antes do botão -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td height="18" style="height:18px; font-size:0; line-height:18px;">
                                        &nbsp;
                                    </td>
                                </tr>
                            </table>

                            <!-- Botão de recuperação -->
                            <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" bgcolor="#ffb000" style="border-radius:8px;">
                                        <a href="{$linkSeguro}"
                                            style="display:inline-block; padding:16px 28px; font-size:15px; font-weight:bold; color:#151515; text-decoration:none; border-radius:8px;">
                                            REDEFINIR MINHA SENHA
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Espaço entre o botão e o aviso -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td height="18" style="height:18px; font-size:0; line-height:18px;">
                                        &nbsp;
                                    </td>
                                </tr>
                            </table>

                            <!-- Aviso de segurança -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background-color:#202020; border:1px solid #353535; border-radius:8px;">
                                <tr>
                                    <td style="padding:16px;">
                                        <p style="margin:0; font-size:13px; line-height:1.8; color:#f0f0f0;">
                                            <strong style="color:#ffb000;">ATENÇÃO</strong><br>
                                            Este link expira em 1 hora por segurança.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0; font-size:13px; line-height:1.8; color:#a3a3a3;">
                                Se você não solicitou a recuperação de senha, basta ignorar este e-mail.
                                Sua senha atual não será alterada por esta mensagem.
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td align="center"
                            style="padding:22px 20px; border-top:1px solid #303030; background-color:#111111; border-radius:0 0 14px 14px;">

                            <p style="margin:0 0 8px; font-size:13px; font-weight:bold; color:#ffb000;">
                                GYMFLOW
                            </p>

                            <p style="margin:0; font-size:11px; line-height:1.8; color:#888888;">
                                Este é um e-mail automático. Por favor, não responda.<br>
                                Sua segurança é importante para nós.
                            </p>

                        </td>
                    </tr>

                </table>

                <p style="margin:18px 0 0; font-size:11px; color:#777777; text-align:center;">
                    GymFlow &copy; Todos os direitos reservados.
                </p>

            </td>
        </tr>
    </table>

</body>
</html>
HTML;

                        $mail->AltBody = "Olá!\n\n"
                            . "Recebemos uma solicitação para redefinir sua senha do GymFlow.\n\n"
                            . "Para criar uma nova senha, acesse o link abaixo:\n"
                            . $link . "\n\n"
                            . "Este link expira em 1 hora por segurança.\n"
                            . "Se você não solicitou a recuperação, ignore este e-mail.\n\n"
                            . "Equipe GymFlow";

                        $mail->send();
                    } catch (\Throwable $e) {
                        // Se o envio falhar, invalida o token que não foi entregue.
                        try {
                            $stmtInvalidar = $pdo->prepare(
                                "UPDATE users
                                 SET reset_token = NULL,
                                     reset_token_expires_at = NULL
                                 WHERE id = ?"
                            );
                            $stmtInvalidar->execute([$user['id']]);
                        } catch (\Throwable $erroInvalidacao) {
                            error_log(
                                'GymFlow: falha ao invalidar token de recuperação.'
                            );
                        }

                        error_log(
                            'GymFlow: falha no envio do e-mail de recuperação: '
                                . $e->getMessage()
                        );
                    }
                }
            } catch (\Throwable $e) {
                // Registra o erro no log, sem expor detalhes técnicos na página.
                error_log(
                    'GymFlow: falha ao processar a recuperação de senha: '
                        . $e->getMessage()
                );
            }
        }
    }

    require __DIR__ . '/../views/login/esqueci_senha.php';

    /* 4. REDEFINIR SENHA */
} elseif ($acao === 'redefinir_senha') {

    $token = $_GET['token'] ?? '';

    if ($token === '') {
        header("Location: " . $baseUrl . "?acao=login");
        exit;
    }

    require_once __DIR__ . '/../../config/conexao.php';

    // Confere o token e sua validade
    $stmt = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE reset_token = ?
         AND reset_token_expires_at > NOW()"
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $erro = "Link de recuperação inválido ou expirado.";
        require __DIR__ . '/../views/login/esqueci_senha.php';
        exit;
    }

    $erro = '';
    $sucesso = '';

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $nova_senha = $_POST['nova_senha'] ?? '';

        if (strlen($nova_senha) >= 6) {
            $hash = password_hash($nova_senha, PASSWORD_DEFAULT);

            // Salva a nova senha e invalida o token usado
            $stmtUpdate = $pdo->prepare(
                "UPDATE users
                 SET password = ?,
                     reset_token = NULL,
                     reset_token_expires_at = NULL
                 WHERE id = ?"
            );
            $stmtUpdate->execute([
                $hash,
                $user['id']
            ]);

            $sucesso = "Senha redefinida com sucesso! "
                . "<a href='" . BASE_URL
                . "/app/controllers/LoginController.php?acao=login'>"
                . "Fazer Login</a>";
        } else {
            $erro = "A senha deve ter pelo menos 6 caracteres.";
        }
    }

    require __DIR__ . '/../views/login/redefinir_senha.php';
} else {
    header("Location: " . $baseUrl . "?acao=login");
    exit;
}
