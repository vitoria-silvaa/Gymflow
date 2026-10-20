<?php
// ==========================================
// GYMFLOW - ESQUECI MINHA SENHA
// app/views/login/esqueci_senha.php
// ==========================================

if (!defined('BASE_URL')) {
    http_response_code(403);
    exit('Acesso não permitido.');
}

$erro = $erro ?? '';
$sucesso = $sucesso ?? '';

$loginUrl = BASE_URL .
    '/app/controllers/LoginController.php?acao=login';

$actionUrl = BASE_URL .
    '/app/controllers/LoginController.php?acao=esqueci_senha';

$cssPath = __DIR__ .
    '/../../../assets/css/auth-recovery.css';

$cssVersion = is_file($cssPath)
    ? filemtime($cssPath)
    : '1';

$escapar = static function ($valor) {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Recuperar acesso | GymFlow</title>

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/global.css">

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/auth-recovery.css?v=<?= $cssVersion ?>">
</head>

<body>

    <main class="auth-recovery">

        <section
            class="auth-card"
            aria-labelledby="auth-title">

            <div class="auth-brand">
                GYM<span>FLOW</span>
            </div>

            <header class="auth-header">

                <h1 id="auth-title">
                    Recuperar acesso
                </h1>

                <p>
                    Informe o e-mail cadastrado na sua conta.
                    Se ele estiver registrado, enviaremos
                    um link para redefinir sua senha.
                </p>

            </header>

            <!-- ERRO -->

            <?php if ($erro !== ''): ?>

                <div
                    class="auth-message auth-message-error"
                    role="alert">
                    <p>
                        <?= $escapar($erro) ?>
                    </p>
                </div>

            <?php endif; ?>

            <!-- SUCESSO -->

            <?php if ($sucesso !== ''): ?>

                <div
                    class="auth-message auth-message-success"
                    role="status">
                    <p>
                        <?= $escapar($sucesso) ?>
                    </p>
                </div>

            <?php endif; ?>

            <!-- FORMULÁRIO -->

            <form
                action="<?= $escapar($actionUrl) ?>"
                method="POST"
                class="auth-form">

                <div class="auth-field">

                    <label for="email">
                        E-mail
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Digite seu e-mail cadastrado"
                        autocomplete="email"
                        inputmode="email"
                        maxlength="255"
                        required>

                </div>

                <button
                    type="submit"
                    class="auth-action">
                    Enviar link de recuperação
                </button>

            </form>

            <footer class="auth-footer">

                <a href="<?= $escapar($loginUrl) ?>">
                    ← Voltar para o login
                </a>

            </footer>

        </section>

    </main>

</body>

</html>