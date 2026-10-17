
<?php
// app/controllers/LoginController.php

require_once __DIR__ . '/../../config/sessao.php';

$baseUrl = BASE_URL . '/app/controllers/LoginController.php';
$acao = $_GET['acao'] ?? 'login';

/* 1. LOGIN */
if ($acao === 'login') {

    // Redireciona se o usuário já estiver logado.
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

                // Lógica de "Lembrar-me".
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

                // Obtém a empresa associada à conta, sem depender da sessão.
                $stmt = $pdo->prepare(
                    "SELECT id, email, company_id
                    FROM users
                    WHERE email = ?
                    LIMIT 1"
                );
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {

                    /*
                     * CORES DO E-MAIL
                     * Os valores padrão são usados se não houver configuração.
                     */
                    $coresEmail = [
                        'fundo' => '#0b0b0b',
                        'cartao' => '#151515',
                        'destaque' => '#ffb000',
                        'botao' => '#ffb000',
                        'texto_botao' => '#151515',
                    ];

                    $empresaId = (int) ($user['company_id'] ?? 0);

                    if ($empresaId > 0) {
                        try {
                            $stmtCores = $pdo->prepare("
                                SELECT
                                    ce.modo_cores_email,
                                    ce.cor_email_fundo,
                                    ce.cor_email_cartao,
                                    ce.cor_email_destaque,
                                    ce.cor_email_botao,
                                    ce.cor_email_texto_botao,
                                    p.cor_primaria,
                                    p.cor_secundaria
                                FROM configuracoes_email ce
                                LEFT JOIN preferencias_usuario p
                                    ON p.user_id = ce.atualizado_por
                                WHERE ce.company_id = ?
                                LIMIT 1
                            ");

                            $stmtCores->execute([$empresaId]);
                            $configCores = $stmtCores->fetch(PDO::FETCH_ASSOC);

                            if ($configCores) {
                                $modoCores = $configCores['modo_cores_email'] ?? 'personalizado';

                                if ($modoCores === 'personalizado') {
                                    $camposCores = [
                                        'fundo' => 'cor_email_fundo',
                                        'cartao' => 'cor_email_cartao',
                                        'destaque' => 'cor_email_destaque',
                                        'botao' => 'cor_email_botao',
                                        'texto_botao' => 'cor_email_texto_botao',
                                    ];

                                    foreach ($camposCores as $destino => $origem) {
                                        $cor = $configCores[$origem] ?? '';

                                        if (
                                            is_string($cor) &&
                                            preg_match('/^#[0-9a-fA-F]{6}$/', $cor)
                                        ) {
                                            $coresEmail[$destino] = strtolower($cor);
                                        }
                                    }
                                } elseif ($modoCores === 'marca') {
                                    $corPrimaria = $configCores['cor_primaria'] ?? '';
                                    $corSecundaria = $configCores['cor_secundaria'] ?? '';

                                    // Cor secundária da marca no fundo externo.
                                    if (
                                        is_string($corSecundaria) &&
                                        preg_match('/^#[0-9a-fA-F]{6}$/', $corSecundaria)
                                    ) {
                                        $coresEmail['fundo'] = strtolower($corSecundaria);
                                    }

                                    // Cor primária da marca nos destaques e no botão.
                                    if (
                                        is_string($corPrimaria) &&
                                        preg_match('/^#[0-9a-fA-F]{6}$/', $corPrimaria)
                                    ) {
                                        $coresEmail['destaque'] = strtolower($corPrimaria);
                                        $coresEmail['botao'] = strtolower($corPrimaria);

                                        // Escolhe uma cor de texto com contraste básico.
                                        $hex = ltrim($coresEmail['botao'], '#');
                                        $r = hexdec(substr($hex, 0, 2));
                                        $g = hexdec(substr($hex, 2, 2));
                                        $b = hexdec(substr($hex, 4, 2));

                                        $brilho = (299 * $r + 587 * $g + 114 * $b) / 1000;

                                        $coresEmail['texto_botao'] =
                                            $brilho >= 150 ? '#151515' : '#ffffff';
                                    }
                                }
                            }
                        } catch (\Throwable $e) {
                            // Se a configuração falhar, conserva as cores padrão.
                            error_log('GymFlow: falha ao carregar as cores do e-mail.');
                        }
                    }

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

                        /*
                         * CONFIGURAÇÃO SMTP
                         *
                         * O arquivo local continua sendo a alternativa.
                         * Se a empresa tiver SMTP personalizado ativado,
                         * seus dados serão usados no lugar dos dados locais.
                         */
                        $emailConfig = require __DIR__ . '/../../config/email.local.php';

                        if (!is_array($emailConfig)) {
                            throw new \RuntimeException(
                                'Configuração local de e-mail inválida.'
                            );
                        }

                        // Configuração padrão: SMTP local existente.
                        $smtpHost = trim((string) ($emailConfig['host'] ?? ''));
                        $smtpUsuario = trim((string) ($emailConfig['username'] ?? ''));
                        $smtpSenha = (string) ($emailConfig['password'] ?? '');
                        $smtpPorta = (int) ($emailConfig['port'] ?? 587);
                        $smtpSeguranca = 'tls';

                        $remetenteEmail = $smtpUsuario;
                        $remetenteNome = 'GymFlow';

                        // Carrega as funções de criptografia e configuração SMTP.
                        require_once __DIR__ . '/../models/ConfiguracaoEmail.php';

                        $configEmpresa = null;

                        if ($empresaId > 0) {
                            $configEmpresa = buscarConfiguracaoEmail(
                                $pdo,
                                $empresaId
                            );
                        }

                        // Usa o SMTP personalizado se estiver ativado para a empresa.
                        if (
                            $configEmpresa &&
                            (int) ($configEmpresa['usar_personalizado'] ?? 0) === 1
                        ) {
                            $stmtSenhaSmtp = $pdo->prepare(
                                'SELECT smtp_senha_criptografada
                                 FROM configuracoes_email
                                 WHERE company_id = ?
                                 LIMIT 1'
                            );
                            $stmtSenhaSmtp->execute([$empresaId]);
                            $senhaCriptografada = $stmtSenhaSmtp->fetchColumn();

                            if (
                                !is_string($senhaCriptografada) ||
                                $senhaCriptografada === ''
                            ) {
                                throw new \RuntimeException(
                                    'A senha SMTP da empresa não está configurada.'
                                );
                            }

                            // Descriptografa a senha somente durante o envio.
                            $smtpSenha = gymflowDescriptografarSenhaSmtp(
                                $senhaCriptografada
                            );

                            $smtpHost = trim(
                                (string) ($configEmpresa['smtp_host'] ?? '')
                            );
                            $smtpUsuario = trim(
                                (string) ($configEmpresa['smtp_usuario'] ?? '')
                            );
                            $smtpPorta = (int) (
                                $configEmpresa['smtp_porta'] ?? 0
                            );
                            $smtpSeguranca = (string) (
                                $configEmpresa['smtp_seguranca'] ?? 'tls'
                            );

                            $remetenteEmail = trim(
                                (string) ($configEmpresa['email_remetente'] ?? '')
                            );
                            $remetenteNome = trim(
                                (string) ($configEmpresa['nome_remetente'] ?? '')
                            );
                        }

                        // Valida os dados antes de iniciar a conexão SMTP.
                        if (
                            $smtpHost === '' ||
                            $smtpUsuario === '' ||
                            $smtpSenha === '' ||
                            $smtpPorta < 1 ||
                            $smtpPorta > 65535 ||
                            !in_array($smtpSeguranca, ['tls', 'ssl', 'none'], true) ||
                            !filter_var($remetenteEmail, FILTER_VALIDATE_EMAIL) ||
                            $remetenteNome === ''
                        ) {
                            throw new \RuntimeException(
                                'Os dados SMTP estão incompletos ou são inválidos.'
                            );
                        }

                        // Evita inserir texto arbitrário como nome no cabeçalho do e-mail.
                        $remetenteNome = trim(
                            preg_replace('/[\r\n]+/', ' ', $remetenteNome)
                        );

                        // Nome seguro para exibição dentro do HTML.
                        $nomeAcademiaHtml = htmlspecialchars(
                            $remetenteNome,
                            ENT_QUOTES,
                            'UTF-8'
                        );

                        // A URL da aplicação permanece na configuração local.
                        $appUrl = trim((string) ($emailConfig['app_url'] ?? ''));

                        if ($appUrl === '') {
                            throw new \RuntimeException(
                                'A URL da aplicação não está configurada.'
                            );
                        }

                        // Monta o link de recuperação.
                        $link = rtrim($appUrl, '/')
                            . '/app/controllers/LoginController.php?acao=redefinir_senha&token='
                            . urlencode($token);

                        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

                        $mail->isSMTP();
                        $mail->Host = $smtpHost;
                        $mail->SMTPAuth = true;
                        $mail->Username = $smtpUsuario;
                        $mail->Password = $smtpSenha;
                        $mail->Port = $smtpPorta;
                        $mail->CharSet = 'UTF-8';

                        // Aplica a segurança SMTP escolhida pela empresa.
                        if ($smtpSeguranca === 'ssl') {
                            $mail->SMTPSecure =
                                \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                        } elseif ($smtpSeguranca === 'tls') {
                            $mail->SMTPSecure =
                                \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        } else {
                            $mail->SMTPSecure = '';
                            $mail->SMTPAutoTLS = false;
                        }

                        $mail->setFrom($remetenteEmail, $remetenteNome);
                        $mail->addAddress($user['email']);

                        $mail->isHTML(true);
                        $mail->Subject = 'Redefinição de senha - ' . $remetenteNome;

                        $linkSeguro = htmlspecialchars(
                            $link,
                            ENT_QUOTES,
                            'UTF-8'
                        );

                        // Prepara as cores para inserção segura no HTML.
                        $corFundo = htmlspecialchars($coresEmail['fundo'], ENT_QUOTES, 'UTF-8');
                        $corCartao = htmlspecialchars($coresEmail['cartao'], ENT_QUOTES, 'UTF-8');
                        $corDestaque = htmlspecialchars($coresEmail['destaque'], ENT_QUOTES, 'UTF-8');
                        $corBotao = htmlspecialchars($coresEmail['botao'], ENT_QUOTES, 'UTF-8');
                        $corTextoBotao = htmlspecialchars($coresEmail['texto_botao'], ENT_QUOTES, 'UTF-8');

                        $mail->Body = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de senha - {$nomeAcademiaHtml}</title>
</head>
<body style="margin:0; padding:0; background-color:{$corFundo}; font-family:Arial,Helvetica,sans-serif; color:#f5f5f5;">

    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        Recupere seu acesso à {$nomeAcademiaHtml} de forma segura.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="width:100%; background-color:{$corFundo};">
        <tr>
            <td align="center" style="padding:36px 14px;">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                    style="width:100%; max-width:600px; background-color:{$corCartao}; border:1px solid #303030; border-radius:14px;">

                    <tr>
                        <td style="height:5px; background-color:{$corDestaque}; font-size:0; line-height:0;">
                            &nbsp;
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:34px 24px 22px;">
                            <p style="margin:0; font-size:30px; font-weight:bold; letter-spacing:1px; color:#ffffff;">
                                {$nomeAcademiaHtml}
                            </p>
                            <p style="margin:10px 0 0; font-size:11px; letter-spacing:2px; color:#a3a3a3;">
                                RECUPERAÇÃO DE ACESSO
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
                                Recebemos uma solicitação para redefinir sua senha na
                                {$nomeAcademiaHtml}. Clique no botão abaixo para criar
                                uma nova senha e voltar a acessar sua conta.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td height="18" style="height:18px; font-size:0; line-height:18px;">
                                        &nbsp;
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" bgcolor="{$corBotao}" style="border-radius:8px;">
                                        <a href="{$linkSeguro}"
                                            style="display:inline-block; padding:16px 28px; font-size:15px; font-weight:bold; color:{$corTextoBotao}; text-decoration:none; border-radius:8px;">
                                            REDEFINIR MINHA SENHA
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td height="18" style="height:18px; font-size:0; line-height:18px;">
                                        &nbsp;
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background-color:{$corCartao}; border:1px solid #353535; border-radius:8px;">
                                <tr>
                                    <td style="padding:16px;">
                                        <p style="margin:0; font-size:13px; line-height:1.8; color:#f0f0f0;">
                                            <strong style="color:{$corDestaque};">ATENÇÃO</strong><br>
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
                            style="padding:22px 20px; border-top:1px solid #303030; background-color:{$corFundo}; border-radius:0 0 14px 14px;">

                            <p style="margin:0 0 8px; font-size:13px; font-weight:bold; color:{$corDestaque};">
                                {$nomeAcademiaHtml}
                            </p>

                            <p style="margin:0; font-size:11px; line-height:1.8; color:#888888;">
                                Este é um e-mail automático. Por favor, não responda.<br>
                                Sua segurança é importante para nós.
                            </p>

                        </td>
                    </tr>

                </table>

                <p style="margin:18px 0 0; font-size:11px; color:#777777; text-align:center;">
                    {$nomeAcademiaHtml} &copy; Todos os direitos reservados.
                </p>

            </td>
        </tr>
    </table>

</body>
</html>
HTML;

                        // Versão em texto simples para clientes que não exibem HTML.
                        $mail->AltBody = "Olá!\n\n"
                            . "Recebemos uma solicitação para redefinir sua senha na "
                            . $remetenteNome . ".\n\n"
                            . "Para criar uma nova senha, acesse o link abaixo:\n"
                            . $link . "\n\n"
                            . "Este link expira em 1 hora por segurança.\n"
                            . "Se você não solicitou a recuperação, ignore este e-mail.\n\n"
                            . "Equipe " . $remetenteNome;

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
                // Registra o erro sem expor detalhes técnicos na página.
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

    // Confere o token e sua validade.
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

            // Salva a nova senha e invalida o token usado.
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
