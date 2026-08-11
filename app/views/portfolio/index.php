<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/PortfolioController.php");
    exit;
}
$tituloPagina = "Site / Portfólio";
include __DIR__ . '/../shared/header.php';
?>

<?php include __DIR__ . '/../shared/sidebar.php'; ?>

<h1>Configuração do Portfólio / Site</h1>
<p>Módulo de personalização do site público (white-label) em desenvolvimento.</p>

<?php include __DIR__ . '/../shared/footer.php'; ?>