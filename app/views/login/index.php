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
</head>

<body>

    <main>

        <div>

            <!-- Lado esquerdo-->
            <div>

                <a href="/Gymflow/index.php">
                    ← Voltar para o site
                </a>

                <div>
                    <h1>
                        Gestão completa para sua rede de academias.
                    </h1>

                    <p>
                        Multi-tenant, multi-filial, multi-cargo.
                        Tudo em um só lugar.
                    </p>
                </div>

            </div>

            <!-- Lado direito -->
            <div>

                <div>

                    <h2>
                        Entrar
                    </h2>

                    <p>
                        Acesse o painel da sua rede.
                    </p>

                    <!-- Exibição de Erros de Autenticação -->
                    <?php if (!empty($erro)): ?>
                        <div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold;">
                            <?php echo htmlspecialchars($erro); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">

                        <div>

                            <label for="email">
                                E-mail
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                required>

                        </div>

                        <div>

                            <label for="senha">
                                Senha
                            </label>

                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                required>

                        </div>

                        <button
                            type="submit">

                            Entrar

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </main>

</body>

</html>