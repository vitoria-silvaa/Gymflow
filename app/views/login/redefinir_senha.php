<?php
// app/views/login/redefinir_senha.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - GymCore</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css?v=<?= time() ?>">
    <style>
        .login-page { justify-content: center; align-items: center; }
        .login-container { max-width: 500px; width: 100%; min-height: auto; border-radius: 12px; }
        .login-second-column { width: 100% !important; min-height: auto !important; padding: 40px; }
        .success-box { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .error-box { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .success-box a { color: #047857; font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <main class="login-page">
        <div class="login-container">
            <section class="login-second-column">
                <div class="login-form-container">
                    <header class="login-header">
                        <h2 class="login-form-title">Criar Nova Senha</h2>
                        <p class="login-form-description">Digite sua nova senha abaixo.</p>
                    </header>
                    
                    <?php if (!empty($sucesso)): ?>
                        <div class="success-box"><?= $sucesso ?></div>
                    <?php else: ?>
                        <?php if (!empty($erro)): ?>
                            <div class="error-box"><?= $erro ?></div>
                        <?php endif; ?>

                        <form action="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=redefinir_senha&token=<?= htmlspecialchars($token) ?>" method="POST" class="login-form">
                            <div class="form-group">
                                <label for="nova_senha" class="form-label">Nova Senha</label>
                                <div class="input-wrapper">
                                    <span class="input-icon">•</span>
                                    <input type="password" class="form-input" id="nova_senha" name="nova_senha" required minlength="6" placeholder="Mínimo de 6 caracteres">
                                </div>
                            </div>
                            <button type="submit" class="login-submit-button">
                                <span>Salvar Senha</span>
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <div class="login-footer" style="margin-top: 20px; text-align: center;">
                        <a href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=login">← Voltar para o Login</a>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
