<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?? "GymCore"; ?></title>

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/portifolio.css?v=11">

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        defer
    ></script>

    <style>
        :root {
            --portfolio-primary: <?= htmlspecialchars($config["primary_color"] ?? "#C9A227") ?>;
            --portfolio-secondary: <?= htmlspecialchars($config["secondary_color"] ?? "#000000") ?>;
        }
    </style>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/portfolio-mapa.css">
</head>

<body>

<header class="portfolio-header">

    <div class="portfolio-logo">
        <h1><?= htmlspecialchars($tituloPagina ?? "GymCore") ?></h1>
    </div>

    <nav class="portfolio-nav">
        <ul>
            <li><a href="<?= BASE_URL ?>/index.php#inicio">Início</a></li>
            <li><a href="<?= BASE_URL ?>/index.php#planos">Planos</a></li>
            <li><a href="<?= BASE_URL ?>/index.php#unidades">Unidades</a></li>
            <li><a href="<?= BASE_URL ?>/index.php#modalidades">Modalidades</a></li>
            <li><a href="<?= BASE_URL ?>/index.php#sobre">Sobre Nós</a></li>
            <li><a href="<?= BASE_URL ?>/index.php#contato">Contato</a></li>
        </ul>
    </nav>

    <div class="portfolio-acoes">
        <?php if ($usuarioLogado): ?>
            <?php if ($roleUsuario === 'Aluno'): ?>
                <a href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=aluno" style="background-color: var(--portfolio-primary); color: #fff; border-radius: 4px; padding: 8px 16px;">
                    Área do Aluno
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/app/controllers/DashboardController.php" style="background-color: var(--portfolio-primary); color: #fff; border-radius: 4px; padding: 8px 16px;">
                    Painel GymFlow
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=logout" style="margin-left: 10px;">Sair</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=login">Entrar</a>
            <a href="<?= BASE_URL ?>/app/controllers/MatriculaController.php" style="background-color: var(--portfolio-primary); color: #fff; border-radius: 4px; padding: 8px 16px;">
                Matricule-se
            </a>
        <?php endif; ?>
    </div>

</header>
