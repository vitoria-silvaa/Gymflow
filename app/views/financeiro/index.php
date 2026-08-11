<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/FinanceiroController.php");
    exit;
}
$tituloPagina = "Financeiro";
include __DIR__ . '/../shared/header.php';
?>

<?php include __DIR__ . '/../shared/sidebar.php'; ?>

<h1>Financeiro Geral</h1>
<p>Módulo de fluxo de caixa e custos em desenvolvimento.</p>

<?php include __DIR__ . '/../shared/footer.php'; ?>