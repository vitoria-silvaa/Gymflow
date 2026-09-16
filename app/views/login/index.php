<?php
if (!isset($erro)) {
    header("Location: /Gymflow/app/controllers/LoginController.php?acao=login");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - GymCore</title>

    <link rel="stylesheet" href="/Gymflow/assets/css/css/global.css">
    <link rel="stylesheet" href="/Gymflow/assets/css/css/login.css">

</head>

<body>

    <main class="login-page">

        <div class="login-container">

            <!-- =========================
                 LADO ESQUERDO
            ========================== -->
            <section class="login-first-column">

                <a
                    href="/Gymflow/index.php"
                    class="login-back-link">
                    ← Voltar para o site
                </a>

                <div class="login-introduction">

                    <h1 class="login-title">
                        Gestão completa para sua rede de academias.
                    </h1>

                    <p class="login-description">
                        Multi-tenant, multi-filial, multi-cargo.
                        Tudo em um só lugar.
                    </p>

                </div>

            </section>


            <!-- =========================
                 LADO DIREITO
            ========================== -->
            <section class="login-second-column">

                <div class="login-form-container">

                    <header class="login-header">

                        <h2 class="login-form-title">
                            Entrar
                        </h2>

                        <p class="login-form-description">
                            Acesse o painel da sua rede.
                        </p>

                    </header>


                    <!-- =========================
                         MENSAGEM DE ERRO
                    ========================== -->
                    <?php if (!empty($erro)): ?>

                        <div
                            class="login-alert login-alert-error"
                            role="alert">

                            <?php echo htmlspecialchars($erro); ?>

                        </div>

                    <?php endif; ?>


                    <!-- =========================
                         FORMULÁRIO
                    ========================== -->
                    <form
                        action=""
                        method="POST"
                        class="login-form">

                        <!-- E-mail -->
                        <div class="form-group">

                            <label
                                for="email"
                                class="form-label">
                                E-mail
                            </label>

                            <input
                                type="email"
                                class="form-input"
                                id="email"
                                name="email"
                                value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                placeholder="Digite seu e-mail"
                                autocomplete="email"
                                required>

                        </div>


                        <!-- Senha -->
                        <div class="form-group">

                            <label
                                for="senha"
                                class="form-label">
                                Senha
                            </label>

                            <input
                                type="password"
                                class="form-input"
                                id="senha"
                                name="senha"
                                placeholder="Digite sua senha"
                                autocomplete="current-password"
                                required>

                        </div>


                        <!-- Botão -->
                        <button
                            type="submit"
                            class="login-submit-button">

                            Entrar

                        </button>

                    </form>

                </div>

            </section>

        </div>

    </main>

</body>

</html>