<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?? "GymCore"; ?></title>
 <link rel="stylesheet" href="/Gymflow/assets/css/css/global.css">
<link rel="stylesheet" href="/Gymflow/assets/css/css/portifolio.css?v=2">
<style>
    :root {
        --portfolio-primary: <?= htmlspecialchars($config['primary_color'] ?? '#C9A227') ?>;
        --portfolio-secondary: <?= htmlspecialchars($config['secondary_color'] ?? '#000000') ?>;
    }
</style>
</head>

<body>

<header class="portfolio-header">

    <div class="portfolio-logo">
        <h1><?= htmlspecialchars($tituloPagina ?? "GymCore") ?></h1>
    </div>

    <nav class="portfolio-nav">
        <ul>
            <li><a href="/Gymflow/index.php#inicio">Início</a></li>
            <li><a href="/Gymflow/index.php#planos">Planos</a></li>
            <li><a href="/Gymflow/index.php#unidades">Unidades</a></li>
            <li><a href="/Gymflow/index.php#modalidades">Modalidades</a></li>
            <li><a href="/Gymflow/index.php#sobre">Sobre Nós</a></li>
            <li><a href="/Gymflow/index.php#contato">Contato</a></li>
        </ul>
    </nav>

    <div class="portfolio-acoes">
        <a href="/Gymflow/app/controllers/LoginController.php?acao=login">Entrar</a>
        <a href="/Gymflow/app/controllers/MatriculaController.php">Matricule-se</a>
    </div>

</header>