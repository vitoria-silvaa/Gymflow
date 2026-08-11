<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/CrmController.php");
    exit;
}
$tituloPagina = "CRM";
include __DIR__ . '/../shared/header.php';
?>

<?php include __DIR__ . '/../shared/sidebar.php'; ?>

<h1>CRM / Leads</h1>
<p>Módulo de prospecção e novos clientes em desenvolvimento.</p>

<?php include __DIR__ . '/../shared/footer.php'; ?>