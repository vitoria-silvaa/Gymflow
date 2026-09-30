<?php

$nomePainel = $_SESSION['nome_painel'] ?? 'Gymflow';
$titulo = $tituloPagina ?? 'Dashboard';
$usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$role = $_SESSION['usuario_role'] ?? 'Admin';

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
        <link rel="stylesheet" href="<?= htmlspecialchars($cssEspecifico) ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/cadastros.css">
</head>

<body>

    <header class="header">

        <div class="header-esquerda">
            <h1><?= htmlspecialchars($titulo) ?></h1>

        </div>

        <div class="header-direita">



            <span>
                <?= htmlspecialchars($usuario) ?>
                (<?= htmlspecialchars($role) ?>)
            </span>

        </div>

    </header>