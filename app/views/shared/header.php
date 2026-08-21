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

    <link rel="stylesheet" href="/Gymflow/assets/css/css/global.css">
    <link rel="stylesheet" href="/Gymflow/assets/css/css/layout.css">
</head>

<body>

    <header class="header">

        <div class="header-esquerda">
            <h1><?= htmlspecialchars($titulo) ?></h1>

        </div>

        <div class="header-direita">

            <?php if ($role === 'Aluno'): ?>
                <a href="/Gymflow/app/controllers/PortalAlunoController.php?acao=aluno">
                    Portal do Aluno
                </a>
            <?php endif; ?>

            <span>
                <?= htmlspecialchars($usuario) ?>
                (<?= htmlspecialchars($role) ?>)
            </span>

        </div>

    </header>