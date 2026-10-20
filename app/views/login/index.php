<?php
// =============================================
// GYMFLOW - TELA DE LOGIN
// app/views/login/index.php
// =============================================

if (!isset($erro)) {
    header(
        'Location: ' . BASE_URL .
            '/app/controllers/LoginController.php?acao=login'
    );
    exit;
}

$erro = $erro ?? '';
$email = $email ?? '';

// Identidade visual padrão do login.
//
// Futuramente, o controller poderá fornecer
// as cores da academia identificada pela URL.
// A view não deve escolher uma empresa por conta própria.

$loginCorPrimaria = $loginCorPrimaria ?? '#ffb000';
$loginCorSecundaria = $loginCorSecundaria ?? '#0b0b0b';
$loginNomeAcademia = $loginNomeAcademia ?? '';

// Validação das cores recebidas.
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

// Contraste entre a cor da marca e o texto.
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

$loginTextoPrimaria = $contrasteLogin(
    $loginCorPrimaria
);

// Evita escolher automaticamente um tenant.
//
// O endereço público padrão continua disponível,
// mas o vínculo real com uma academia será tratado
// no controller posteriormente.

$siteUrl = BASE_URL . '/index.php';

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
        /*
         * Identidade visual do login.
         * Preparada para receber os dados da
         * academia antes da autenticação.
         */

        .login-page {
            --portfolio-primary: <?= $loginCorPrimaria ?>;
            --portfolio-secondary: <?= $loginCorSecundaria ?>;
            --portfolio-on-primary: <?= $loginTextoPrimaria ?>;
        }

        /*
         * Corrige o conflito com global.css.
         * Os inputs do login precisam permanecer
         * escuros e não devem conter ícones.
         */

        .login-page .login-form input.form-input {
            width: 100%;
            min-height: 48px;

            padding: 12px 15px;

            color: #ffffff;
            background-color: #080808;

            border: 1px solid rgba(255, 255, 255, 0.20);
            border-radius: 7px;

            font-family: inherit;
            font-size: 14px;

            box-shadow: none;
        }

        .login-page .login-form input.form-input::placeholder {
            color: #929292;
            opacity: 1;
        }

        .login-page .login-form input.form-input:focus,
        .login-page .login-form input.form-input:focus-visible {
            outline: none;

            background-color: #101010;
            border-color: var(--login-primaria);

            box-shadow: 0 0 0 3px var(--login-primaria-suave);
        }

        /*
         * Somente o campo de senha reserva
         * espaço para o botão de visualização.
         */

        .login-page .login-form input.password-input {
            padding-right: 48px;
        }

        /*
         * Remove definitivamente a imagem
         * fixa da academia.
         */

        .login-page .login-first-column {
            background-color: var(--login-secundaria);

            background-image:
                radial-gradient(circle at 15% 85%,
                    color-mix(in srgb,
                        var(--login-primaria) 9%,
                        transparent),
                    transparent 55%);
        }

        /*
         * Mantém o botão de visualizar senha
         * sem herdar o estilo de botão global.
         */

        .login-page .password-toggle {
            background-color: transparent;
            color: #a3a3a3;

            border: 0;
            padding: 0;
        }

        .login-page .password-toggle:hover {
            background-color: var(--login-primaria-suave);
            color: var(--login-primaria);
        }
    </style>

</head>

<body>

    <main class="login-page">

        <div class="login-container">

            <!-- =====================================
             COLUNA ESQUERDA
             ===================================== -->

            <section class="login-first-column">

                <!-- Marca do sistema -->

                <div class="login-brand">
                    GYM<span>FLOW</span>
                </div>

                <div class="login-left-content">

                    <!-- Voltar ao site -->

                    <a
                        href="<?= $escaparLogin($siteUrl) ?>"
                        class="login-back-link">
                        <span class="back-arrow">←</span>
                        Voltar para o site
                    </a>

                    <div class="login-title-line"></div>

                    <!-- Apresentação -->

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

                    <!-- Benefícios -->

                    <div class="login-features">

                        <div class="login-feature">

                            <div class="feature-icon">
                                ◈
                            </div>

                            <div>
                                <strong>Organização</strong>
                                <span>Informações centralizadas</span>
                            </div>

                        </div>

                        <div class="login-feature">

                            <div class="feature-icon">
                                ◇
                            </div>

                            <div>
                                <strong>Segurança</strong>
                                <span>Acesso individual</span>
                            </div>

                        </div>

                        <div class="login-feature">

                            <div class="feature-icon">
                                ✓
                            </div>

                            <div>
                                <strong>Praticidade</strong>
                                <span>Tudo em um só lugar</span>
                            </div>

                        </div>

                    </div>

                </div>

            </section>

            <!-- =====================================
             COLUNA DIREITA
             ===================================== -->

            <section class="login-second-column">

                <div
                    class="login-background-glow"
                    aria-hidden="true"></div>

                <div class="login-form-container">

                    <div
                        class="login-card-glow"
                        aria-hidden="true"></div>

                    <!-- Marca -->

                    <div class="form-brand">
                        GYM<span>FLOW</span>
                    </div>

                    <!-- Cabeçalho -->

                    <header class="login-header">

                        <h2 class="login-form-title">
                            Entrar na sua conta
                        </h2>

                        <p class="login-form-description">
                            Informe suas credenciais para
                            acessar seu painel.
                        </p>

                    </header>

                    <!-- Erro de autenticação -->

                    <?php if (!empty($erro)): ?>

                        <div
                            class="login-alert login-alert-error"
                            role="alert">
                            <?= $escaparLogin($erro) ?>
                        </div>

                    <?php endif; ?>

                    <!-- =================================
                     FORMULÁRIO
                     ================================= -->

                    <form
                        action=""
                        method="POST"
                        class="login-form">

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

                                <span>
                                    Lembrar de mim
                                </span>

                            </label>

                            <a
                                href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=esqueci_senha"
                                class="forgot-password">
                                Esqueceu sua senha?
                            </a>

                        </div>

                        <!-- BOTÃO ENTRAR -->

                        <button
                            type="submit"
                            class="login-submit-button">
                            <span>Entrar</span>
                            <span
                                class="button-arrow"
                                aria-hidden="true">
                                →
                            </span>
                        </button>

                    </form>

                    <!-- RODAPÉ -->

                    <div class="login-footer">

                        <span>
                            Precisa de ajuda com seu acesso?
                        </span>

                        <a
                            href="<?= $escaparLogin($siteUrl) ?>#contato">
                            Entre em contato
                        </a>

                    </div>

                </div>

            </section>

        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const campoSenha = document.getElementById('senha');
            const botaoSenha = document.getElementById('passwordToggle');
            const iconeSenha = document.getElementById('password-icon');

            if (!campoSenha || !botaoSenha || !iconeSenha) {
                return;
            }

            botaoSenha.addEventListener('click', function() {

                const mostrando = campoSenha.type === 'text';

                campoSenha.type = mostrando ?
                    'password' :
                    'text';

                iconeSenha.textContent = mostrando ?
                    '◉' :
                    '○';

                botaoSenha.setAttribute(
                    'aria-label',
                    mostrando ? 'Mostrar senha' : 'Ocultar senha'
                );

                botaoSenha.setAttribute(
                    'aria-pressed',
                    String(!mostrando)
                );

            });

        });
    </script>

</body>

</html>