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
    <link rel="stylesheet"
        href="/Gymflow/assets/css/css/login.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'] . '/Gymflow/assets/css/css/login.css'); ?>">
</head>

<body>

    <main class="login-page">

        <div class="login-container">

            <!-- =========================
                 LADO ESQUERDO
            ========================== -->

            <section class="login-first-column">

                <!-- Marca -->
                <div class="login-brand">
                    GYM<span>FLOW</span>
                </div>


                <!-- Conteúdo -->
                <div class="login-left-content">

                    <!-- Voltar -->
                    <a
                        href="/Gymflow/index.php"
                        class="login-back-link">

                        <span class="back-arrow">←</span>

                        Voltar para o site

                    </a>


                    <!-- Linha decorativa -->
                    <div class="login-title-line"></div>


                    <!-- Apresentação -->
                    <div class="login-introduction">

                        <h1 class="login-title">
                            Gestão completa para sua
                            <span>rede de academias.</span>
                        </h1>

                        <p class="login-description">
                            Multi-tenant, multi-filial e multi-cargo.
                            Tudo o que você precisa para administrar,
                            acompanhar e fazer sua rede crescer.
                        </p>

                    </div>


                    <!-- Benefícios -->
                    <div class="login-features">

                        <div class="login-feature">

                            <div class="feature-icon">
                                ◈
                            </div>

                            <div>
                                <strong>Mais controle</strong>
                                <span>Gestão centralizada</span>
                            </div>

                        </div>


                        <div class="login-feature">

                            <div class="feature-icon">
                                ◇
                            </div>

                            <div>
                                <strong>Mais segurança</strong>
                                <span>Acesso protegido</span>
                            </div>

                        </div>


                        <div class="login-feature">

                            <div class="feature-icon">
                                ⚡
                            </div>

                            <div>
                                <strong>Mais resultados</strong>
                                <span>Decisões inteligentes</span>
                            </div>

                        </div>

                    </div>

                </div>

            </section>


            <!-- =========================
                 LADO DIREITO
            ========================== -->

            <section class="login-second-column">

                <!-- Luz decorativa -->
                <div class="login-background-glow"></div>


                <!-- =========================
                     CARD DE LOGIN
                ========================== -->

                <div class="login-form-container">

                    <!-- Linha luminosa -->
                    <div class="login-card-glow"></div>


                    <!-- Marca dentro do card -->
                    <div class="form-brand">
                        GYM<span>FLOW</span>
                    </div>


                    <!-- Cabeçalho -->
                    <header class="login-header">

                        <h2 class="login-form-title">
                            Entrar na sua conta
                        </h2>

                        <p class="login-form-description">
                            Acesse o sistema e continue gerenciando
                            sua rede de academias.
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


                        <!-- E-MAIL -->
                        <div class="form-group">

                            <label
                                for="email"
                                class="form-label">

                                E-mail ou usuário

                            </label>


                            <div class="input-wrapper">

                                <span class="input-icon">
                                    @
                                </span>

                                <input
                                    type="email"
                                    class="form-input"
                                    id="email"
                                    name="email"
                                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                    placeholder="Digite seu e-mail ou usuário"
                                    autocomplete="email"
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

                                <span class="input-icon">
                                    •
                                </span>

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
                                    onclick="togglePassword()"
                                    aria-label="Mostrar senha">

                                    <span id="password-icon">
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


                            <!--
                                ALTERE O HREF QUANDO CRIAR
                                A PÁGINA DE RECUPERAÇÃO.
                            -->
                            <a
                                href="#"
                                class="forgot-password">

                                Esqueceu sua senha?

                            </a>

                        </div>


                        <!-- BOTÃO -->
                        <button
                            type="submit"
                            class="login-submit-button">

                            <span>
                                Entrar
                            </span>

                            <span class="button-arrow">
                                →
                            </span>

                        </button>

                    </form>


                    <!-- =========================
                         RODAPÉ DO CARD
                    ========================== -->

                    <div class="login-footer">

                        <span>
                            Novo por aqui?
                        </span>

                        <a href="/Gymflow/index.php#contato">
                            Entre em contato
                        </a>

                    </div>

                </div>

            </section>

        </div>

    </main>


    <!-- =========================
         MOSTRAR / OCULTAR SENHA
    ========================== -->

    <script>
        function togglePassword() {

            const senha = document.getElementById("senha");
            const icon = document.getElementById("password-icon");

            if (senha.type === "password") {

                senha.type = "text";
                icon.textContent = "○";

            } else {

                senha.type = "password";
                icon.textContent = "◉";

            }

        }
    </script>

</body>

</html>