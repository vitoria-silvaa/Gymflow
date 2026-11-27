<?php
// GYMFLOW - TELA DE LOGIN
// app/views/login/index.php

if (!isset($erro)) {
    header(
        'Location: ' . BASE_URL .
        '/app/controllers/LoginController.php?acao=login'
    );
    exit;
}

$erro = $erro ?? '';
$email = $email ?? '';

$loginCorPrimaria = $loginCorPrimaria ?? '#ffb000';
$loginCorSecundaria = $loginCorSecundaria ?? '#0b0b0b';
$loginNomeAcademia = $loginNomeAcademia ?? '';

$validarCorLogin = static function ($cor, $padrao) {
    return is_string($cor)
        && preg_match('/^#[0-9a-fA-F]{6}$/', $cor)
        ? $cor
        : $padrao;
};

$loginCorPrimaria = $validarCorLogin(
    $loginCorPrimaria,
    '#ffb000'
);

$loginCorSecundaria = $validarCorLogin(
    $loginCorSecundaria,
    '#0b0b0b'
);

$contrasteLogin = static function ($hex) {
    $hex = ltrim($hex, '#');

    $r = hexdec(substr($hex, 0, 2)) / 255;
    $g = hexdec(substr($hex, 2, 2)) / 255;
    $b = hexdec(substr($hex, 4, 2)) / 255;

    $linearizar = static function ($canal) {
        return $canal <= 0.04045
            ? $canal / 12.92
            : (($canal + 0.055) / 1.055) ** 2.4;
    };

    $luminancia =
        0.2126 * $linearizar($r) +
        0.7152 * $linearizar($g) +
        0.0722 * $linearizar($b);

    $contrasteEscuro = ($luminancia + 0.05) / 0.05;
    $contrasteBranco = 1.05 / ($luminancia + 0.05);

    return $contrasteEscuro >= $contrasteBranco
        ? '#000000'
        : '#ffffff';
};

$loginTextoPrimaria = $contrasteLogin($loginCorPrimaria);

// Identificação do portfólio de origem.

$empresaInformada =
    $_GET['empresa']
    ?? $_POST['empresa']
    ?? null;

$empresaId = filter_var(
    $empresaInformada,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($empresaId === false || $empresaId === null) {
    $empresaId = filter_var(
        $_SESSION['company_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
}

if ($empresaId === false || $empresaId === null) {

    $referencia = $_SERVER['HTTP_REFERER'] ?? '';

    if ($referencia !== '') {

        $partes = parse_url($referencia);

        $hostAtual = $_SERVER['HTTP_HOST'] ?? '';
        $hostReferencia = $partes['host'] ?? '';

        $hostAtual = strtolower(
            explode(':', $hostAtual)[0]
        );

        $hostReferencia = strtolower($hostReferencia);

        $caminhoReferencia = $partes['path'] ?? '';
        $caminhoEsperado = BASE_URL . '/index.php';

        if (
            $hostAtual !== '' &&
            $hostReferencia === $hostAtual &&
            $caminhoReferencia === $caminhoEsperado
        ) {

            parse_str(
                $partes['query'] ?? '',
                $parametrosReferencia
            );

            $empresaId = filter_var(
                $parametrosReferencia['id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );
        }
    }
}

$siteUrl = BASE_URL . '/index.php';

if ($empresaId !== false && $empresaId !== null) {
    $siteUrl .= '?id=' . (int) $empresaId;
}

$escaparLogin = static function ($valor) {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

$loginCssPath = __DIR__ . '/../../../assets/css/login.css';

$loginCssVersao = is_file($loginCssPath)
    ? filemtime($loginCssPath)
    : '1';

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Entrar | GymFlow</title>

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/global.css">

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/login.css?v=<?= $loginCssVersao ?>">

    <style>
        .login-page {
            --portfolio-primary: <?= $loginCorPrimaria ?>;
            --portfolio-secondary: <?= $loginCorSecundaria ?>;
            --portfolio-on-primary: <?= $loginTextoPrimaria ?>;
        }
    </style>

</head>

<body>

    <main class="login-page">

        <div class="login-container">

            <!-- COLUNA ESQUERDA -->

            <section class="login-first-column">

                <div class="login-brand">
                    GYM<span>FLOW</span>
                </div>

                <div class="login-left-content">

                    <a
                        href="<?= $escaparLogin($siteUrl) ?>"
                        class="login-back-link">

                        Voltar ao site

                    </a>

                    <div class="login-title-line"></div>

                    <div class="login-introduction">

                        <?php if ($loginNomeAcademia !== ''): ?>

                            <h1 class="login-title">

                                Bem-vindo à

                                <span>
                                    <?= $escaparLogin($loginNomeAcademia) ?>
                                </span>

                            </h1>

                            <p class="login-description">

                                Entre na sua conta para acompanhar
                                suas atividades e acessar os serviços
                                disponíveis na sua academia.

                            </p>

                        <?php else: ?>

                            <h1 class="login-title">

                                Seu espaço de

                                <span>gestão e evolução.</span>

                            </h1>

                            <p class="login-description">

                                Uma plataforma integrada para
                                administradores, colaboradores e alunos.
                                Acesse sua conta para continuar.

                            </p>

                        <?php endif; ?>

                    </div>

                    <div class="login-features">

                        <div class="login-feature">

                            <div>
                                <strong>Organização</strong>
                                <span>Informações centralizadas</span>
                            </div>

                        </div>

                        <div class="login-feature">

                            <div>
                                <strong>Segurança</strong>
                                <span>Acesso individual</span>
                            </div>

                        </div>

                        <div class="login-feature">

                            <div>
                                <strong>Praticidade</strong>
                                <span>Tudo em um só lugar</span>
                            </div>

                        </div>

                    </div>

                </div>

            </section>

            <!-- COLUNA DIREITA -->

            <section class="login-second-column">

                <div
                    class="login-background-glow"
                    aria-hidden="true"></div>

                <div class="login-form-container">

                    <div
                        class="login-card-glow"
                        aria-hidden="true"></div>

                    <div class="form-brand">
                        GYM<span>FLOW</span>
                    </div>

                    <header class="login-header">

                        <h2 class="login-form-title">
                            Entrar na sua conta
                        </h2>

                        <p class="login-form-description">
                            Informe suas credenciais para
                            acessar seu painel.
                        </p>

                    </header>

                    <?php if (!empty($erro)): ?>

                        <div
                            class="login-alert login-alert-error"
                            role="alert">

                            <?= $escaparLogin($erro) ?>

                        </div>

                    <?php endif; ?>

                    <!-- FORMULÁRIO -->

                    <form
                        action=""
                        method="POST"
                        class="login-form">

                        <?php if (
                            $empresaId !== false &&
                            $empresaId !== null
                        ): ?>

                            <input
                                type="hidden"
                                name="empresa"
                                value="<?= (int) $empresaId ?>">

                        <?php endif; ?>

                        <!-- E-MAIL -->

                        <div class="form-group">

                            <label
                                for="email"
                                class="form-label">

                                E-mail

                            </label>

                            <div class="input-wrapper">

                                <input
                                    type="email"
                                    class="form-input"
                                    id="email"
                                    name="email"
                                    value="<?= $escaparLogin($email) ?>"
                                    placeholder="Digite seu e-mail"
                                    autocomplete="username"
                                    maxlength="255"
                                    required>

                            </div>

                        </div>

                        <!-- SENHA -->

                        <div class="form-group">

                            <label
                                for="senha"
                                class="form-label">

                                Senha

                            </label>

                            <div class="input-wrapper">

                                <input
                                    type="password"
                                    class="form-input password-input"
                                    id="senha"
                                    name="senha"
                                    placeholder="Digite sua senha"
                                    autocomplete="current-password"
                                    required>

                                <button
                                    type="button"
                                    class="password-toggle"
                                    id="passwordToggle"
                                    aria-label="Mostrar senha"
                                    aria-pressed="false">

                                    <span
                                        id="password-icon"
                                        aria-hidden="true">
                                        ◉
                                    </span>

                                </button>

                            </div>

                        </div>

                        <!-- OPÇÕES -->

                        <div class="login-options">

                            <label class="remember-me">

                                <input
                                    type="checkbox"
                                    name="lembrar"
                                    value="1">

                                <span>Lembrar de mim</span>

                            </label>

                            <a
                                href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=esqueci_senha"
                                class="forgot-password">

                                Esqueceu sua senha?

                            </a>

                        </div>

                        <!-- ENTRAR -->

                        <button
                            type="submit"
                            class="login-submit-button">

                            <span>Entrar</span>

                        </button>

                    </form>

                    <div class="login-footer">

                        <span>
                            Precisa de ajuda com seu acesso?
                        </span>

                        <a href="<?= $escaparLogin($siteUrl) ?>#contato">
                            Entre em contato
                        </a>

                    </div>

                </div>

            </section>

        </div>

    </main>

    <script src="<?= BASE_URL ?>/assets/js/login.js" defer></script>

</body>

</html>