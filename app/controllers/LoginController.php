
<?php
// app/controllers/LoginController.php

require_once __DIR__ . '/../../config/sessao.php';
require_once __DIR__ . '/../models/Login.php';

$baseUrl = BASE_URL . '/app/controllers/LoginController.php';
$acao = $_GET['acao'] ?? 'login';


/*
 * Destino seguro após o login.
 *
 * Aceitamos somente a intenção "avaliacao".
 * Nunca redirecionamos para uma URL
 * informada livremente pelo visitante.
 */
$destinoAvaliacao = BASE_URL .
    '/app/controllers/PortalAlunoController.php?acao=avaliacao';

$destinoPortal = BASE_URL .
    '/app/controllers/PortalAlunoController.php?acao=aluno';

$destinoDashboard = BASE_URL .
    '/app/controllers/DashboardController.php';

if ($acao === 'login' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (($_GET['destino'] ?? '') === 'avaliacao') {
        $_SESSION['login_destino'] = 'avaliacao';
    } else {
        // Login comum não reutiliza uma intenção antiga.
        unset($_SESSION['login_destino']);
    }
}



/* 1. LOGIN */
if ($acao === 'login') {

    // Usuário já autenticado.
    if (isset($_SESSION['usuario_id'])) {

        $role = $_SESSION['usuario_role'] ?? '';

        if ($role === 'Aluno') {

            $destino = (
                ($_SESSION['login_destino'] ?? '') === 'avaliacao'
            )
                ? $destinoAvaliacao
                : $destinoPortal;
        } else {
            $destino = $destinoDashboard;
        }

        unset($_SESSION['login_destino']);

        header("Location: " . $destino);
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

            if (
                !empty($usuario) &&
                password_verify($senha, $usuario['password'])
            ) {

                // Captura o destino antes de iniciar uma
                // nova sessão de usuário.
                $querAvaliar = (
                    ($_SESSION['login_destino'] ?? '') === 'avaliacao'
                );

                iniciarSessao($usuario);

                $user_id = $usuario['id'];
                $operacao = 'buscar';
                $preferencias = null;

                require __DIR__ . '/../models/MinhaMarca.php';

                if ($preferencias) {

                    $_SESSION['nome_painel'] =
                        $preferencias['nome_painel'];

                    $_SESSION['tema'] =
                        $preferencias['tema'];

                    $_SESSION['cor_primaria'] =
                        $preferencias['cor_primaria'];

                    $_SESSION['cor_secundaria'] =
                        $preferencias['cor_secundaria'];

                    $_SESSION['tema_predefinido'] =
                        $preferencias['tema_predefinido'];

                    $_SESSION['logo_url'] =
                        $preferencias['logo_url'];
                } else {

                    $_SESSION['nome_painel'] = 'Gymflow';
                    $_SESSION['tema'] = 'dark';
                    $_SESSION['cor_primaria'] = '#ffb000';
                    $_SESSION['cor_secundaria'] = '#000000';
                    $_SESSION['tema_predefinido'] = 'padrao';
                    $_SESSION['logo_url'] = null;
                }


                // Preserva a funcionalidade Lembrar-me.

                if (!empty($_POST['lembrar'])) {

                    $token = bin2hex(random_bytes(32));

                    loginSalvarRememberToken(
                        $pdo,
                        (int) $usuario['id'],
                        $token
                    );


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

                // Limpa a intenção após autenticar.
                unset($_SESSION['login_destino']);

                if ($usuario['role'] === 'Aluno') {

                    $destino = $querAvaliar
                        ? $destinoAvaliacao
                        : $destinoPortal;
                } else {

                    // Nunca enviamos administradores ou
                    // funcionários ao portal do aluno.
                    $destino = $destinoDashboard;
                }

                header("Location: " . $destino);
                exit;
            } else {

                $erro = "E-mail ou senha incorretos.";
            }
        }
    }


    /*
     * IDENTIDADE VISUAL DO LOGIN
     *
     * Busca as configurações públicas da empresa
     * no Portfólio, sem utilizar o Minha Marca.
     */

    $loginCorPrimaria = '#ffb000';
    $loginCorSecundaria = '#0b0b0b';
    $loginNomeAcademia = '';
    $siteUrl = BASE_URL . '/index.php';

    // Identifica a empresa informada pelo Portfólio
    // ou preservada pelo redirecionamento do logout.
    $empresaInformada = $_GET['empresa']
        ?? $_POST['empresa']
        ?? null;

    $loginEmpresaId = filter_var(
        $empresaInformada,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ]
        ]
    );

    // Enquanto houver sessão autenticada, o ID
    // da empresa também pode vir dela.
    if (
        $loginEmpresaId === false ||
        $loginEmpresaId === null
    ) {
        $loginEmpresaId = filter_var(
            $_SESSION['company_id'] ?? null,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1
                ]
            ]
        );
    }

    // Consulta apenas as informações públicas
    // da empresa identificada.
    if (
        $loginEmpresaId !== false &&
        $loginEmpresaId !== null
    ) {
        try {
            require_once __DIR__ .
                '/../../config/conexao.php';

            $marca = loginBuscarMarcaEmpresa(
                $pdo,
                (int) $loginEmpresaId
            );

            if ($marca) {

                $loginEmpresaId = (int) $marca['id'];

                // Voltar ao Portfólio correto.
                $siteUrl = BASE_URL .
                    '/index.php?id=' .
                    $loginEmpresaId;

                $loginNomeAcademia = trim(
                    (string) ($marca['app_name'] ?? '')
                );

                // Cor primária configurada no Portfólio.
                $corPrimaria = $marca['primary_color']
                    ?? null;

                if (
                    is_string($corPrimaria) &&
                    preg_match(
                        '/^#[0-9a-fA-F]{6}$/',
                        $corPrimaria
                    )
                ) {
                    $loginCorPrimaria = $corPrimaria;
                }

                // Cor secundária configurada no Portfólio.
                $corSecundaria = $marca['secondary_color']
                    ?? null;

                if (
                    is_string($corSecundaria) &&
                    preg_match(
                        '/^#[0-9a-fA-F]{6}$/',
                        $corSecundaria
                    )
                ) {
                    $loginCorSecundaria = $corSecundaria;
                }
            } else {
                // Empresa inexistente: não assume outra.
                $loginEmpresaId = null;
            }
        } catch (Throwable $e) {

            error_log(
                'GymFlow: erro ao carregar identidade do login: ' .
                    $e->getMessage()
            );

            // Fallback visual seguro.
            $loginEmpresaId = null;
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
                $user = loginBuscarUsuarioPorEmailRecuperacao($pdo, $email);

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
                            $configCores = loginBuscarCoresEmail($pdo, $empresaId);

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

                    loginSalvarResetToken(
                        $pdo,
                        (int) $user['id'],
                        $token,
                        $expires
                    );

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
                            $senhaCriptografada = loginBuscarSenhaSmtpCriptografada(
                                $pdo,
                                $empresaId
                            );

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

                        ob_start();
                        require __DIR__ . '/../views/emails/recuperar_senha.php';
                        $mail->Body = (string) ob_get_clean();

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
                            loginInvalidarResetToken($pdo, (int) $user['id']);
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
    $user = loginBuscarUsuarioPorResetToken($pdo, $token);

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
            loginAtualizarSenhaReset($pdo, (int) $user['id'], $hash);

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
