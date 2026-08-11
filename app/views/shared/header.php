<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymCore | <?= $tituloPagina ?? 'Dashboard'; ?></title>
</head>

<body>

<header>
    <h1>GymCore</h1>

    <h2><?= $tituloPagina ?? 'Dashboard'; ?></h2>

    <?php if (($_SESSION['usuario_role'] ?? '') === 'Aluno'): ?>
        <a href="/Gymflow/app/controllers/PortalAlunoController.php?acao=aluno">Portal do Aluno</a>
    <?php endif; ?>

    <span><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?> (<?= htmlspecialchars($_SESSION['usuario_role'] ?? 'Admin'); ?>)</span>
</header>