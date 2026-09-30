<?php

$nomePainel = $_SESSION['nome_painel'] ?? 'Gymflow';
$titulo = $tituloPagina ?? 'Dashboard';
$usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$role = $_SESSION['usuario_role'] ?? 'Admin';

$corPrimaria = $_SESSION['cor_primaria'] ?? '#10b981';
$corSecundaria = $_SESSION['cor_secundaria'] ?? '#000000';
$tema = $_SESSION['tema'] ?? 'light';

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($nomePainel) ?> |
        <?= htmlspecialchars($titulo) ?>
    </title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/layout.css?v=<?= time() ?>">
    <?php if ($role === 'Aluno'): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/portal.css?v=<?= time() ?>">
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/treinos.css?v=<?= time() ?>">
    <?php endif; ?>

    <?php if (!empty($cssEspecifico)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($cssEspecifico) ?>?v=<?= time() ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/cadastros.css?v=<?= time() ?>">

    <style>
        :root {
            --cor-primaria: <?= htmlspecialchars($corPrimaria) ?> !important;
            --cor-primaria-hover: <?= htmlspecialchars($corPrimaria) ?> !important;
            --cor-secundaria: <?= htmlspecialchars($corSecundaria) ?> !important;
            --fin-primary: <?= htmlspecialchars($corPrimaria) ?> !important;
            --fin-primary-dark: <?= htmlspecialchars($corPrimaria) ?> !important;
        }

        header h1 {
            color: var(--cor-primaria) !important;
        }

        <?php if ($tema === 'dark'): ?>
        body {
            background-color: #f8fafc !important;
            color: #111827 !important;
        }
        .header {
            background-color: var(--cor-secundaria) !important;
            color: #ffffff !important;
            border-bottom: 1px solid rgba(0,0,0,0.1) !important;
        }
        .header h1 {
            color: #ffffff !important;
        }
        <?php else: ?>
        body {
            background-color: #f8fafc !important;
            color: #111827 !important;
        }
        .header {
            background-color: #ffffff !important;
            color: #111827 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        <?php endif; ?>
    </style>
</head>

<body>

    <div class="mobile-overlay" id="mobileOverlay" onclick="document.getElementById('appSidebar').classList.remove('open'); this.classList.remove('active');"></div>

    <header class="header">

        <div class="header-esquerda">
            <button class="mobile-toggle" onclick="document.getElementById('appSidebar').classList.toggle('open'); document.getElementById('mobileOverlay').classList.toggle('active');">
                ☰
            </button>
            <h1><?= htmlspecialchars($titulo) ?></h1>
        </div>

        <div class="header-direita">



            <span>
                <?= htmlspecialchars($usuario) ?>
                (<?= htmlspecialchars($role) ?>)
            </span>

        </div>

    </header>