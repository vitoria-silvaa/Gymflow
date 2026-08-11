<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/DashboardController.php");
    exit;
}
$tituloPagina = "Dashboard";
include __DIR__ . '/../shared/header.php';
?>

<?php include __DIR__ . '/../shared/sidebar.php'; ?>

<h1>Painel Geral (Dashboard)</h1>
<p>Seja bem-vindo ao GymCore! Utilize o menu lateral para gerenciar o sistema.</p>

<?php include __DIR__ . '/../shared/footer.php'; ?>