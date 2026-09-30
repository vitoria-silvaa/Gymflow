<?php
// app/views/login/esqueci_senha.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci a Senha - GymCore</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css?v=<?= time() ?>">
    <style>
        .login-page { justify-content: center; align-items: center; }
        .login-container { max-width: 500px; width: 100%; min-height: auto; border-radius: 12px; }
        .login-second-column { width: 100% !important; min-height: auto !important; padding: 40px; }
        .success-box { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .success-box a { color: #047857; font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <main class="login-page">
        <div class="login-container">
            <section class="login-second-column">
                <div class="login-form-container">
                    <header class="login-header">
                        <h2 class="login-form-title">Recuperar Senha</h2>
                        <p class="login-form-description">Digite seu e-mail para receber o link de recuperação.</p>
                    </header>
                    
                    <?php if (!empty($sucesso)): ?>
                        <div class="success-box"><?= $sucesso ?></div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=esqueci_senha" method="POST" class="login-form">
                        <div class="form-group">
                            <label for="email" class="form-label">E-mail</label>
                            <div class="input-wrapper">
                                <span class="input-icon">@</span>
                                <input type="email" class="form-input" id="email" name="email" required placeholder="Seu e-mail cadastrado">
                            </div>
                        </div>
                        <button type="submit" class="login-submit-button">
                            <span>Enviar Link</span>
                        </button>
                    </form>
                    <div class="login-footer" style="margin-top: 20px; text-align: center;">
                        <a href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=login">← Voltar para o Login</a>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
