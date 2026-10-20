<?php
// ==========================================
// GYMFLOW - REDEFINIR SENHA
// app/views/login/redefinir_senha.php
// ==========================================

if (!defined('BASE_URL')) {
    http_response_code(403);
    exit('Acesso não permitido.');
}

$erro = $erro ?? '';
$sucesso = $sucesso ?? '';
$token = $token ?? '';

$loginUrl = BASE_URL .
    '/app/controllers/LoginController.php?acao=login';

$actionUrl = BASE_URL .
    '/app/controllers/LoginController.php?acao=redefinir_senha'
    . '&token=' . rawurlencode((string) $token);

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

    <title>Redefinir senha | GymFlow</title>

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
                    Criar nova senha
                </h1>

                <p>
                    Escolha uma nova senha para acessar
                    sua conta com segurança.
                </p>

            </header>

            <!-- MENSAGEM DE SUCESSO -->

            <?php if ($sucesso !== ''): ?>

                <div
                    class="auth-message auth-message-success"
                    role="status">
                    <p>
                        Sua senha foi redefinida com sucesso!
                        Você já pode entrar na sua conta.
                    </p>
                </div>

            <?php else: ?>

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

                <!-- FORMULÁRIO -->

                <form
                    action="<?= $escapar($actionUrl) ?>"
                    method="POST"
                    class="auth-form">

                    <div class="auth-field">

                        <label for="nova_senha">
                            Nova senha
                        </label>

                        <input
                            type="password"
                            id="nova_senha"
                            name="nova_senha"
                            placeholder="Digite sua nova senha"
                            autocomplete="new-password"
                            minlength="6"
                            required>

                        <p class="auth-hint">
                            Utilize pelo menos 6 caracteres.
                        </p>

                    </div>

                    <button
                        type="submit"
                        class="auth-action">
                        Salvar nova senha
                    </button>

                </form>

            <?php endif; ?>

            <footer class="auth-footer">

                <a href="<?= $escapar($loginUrl) ?>">
                    ← Voltar para o login
                </a>

            </footer>

        </section>

    </main>

</body>

</html>