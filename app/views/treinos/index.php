<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/TreinoController.php");
    exit;
}
$tituloPagina = "Treinos";
include __DIR__ . '/../shared/header.php';
?>

<?php include __DIR__ . '/../shared/sidebar.php'; ?>

<h1>Gestão de Fichas de Treino</h1>
<p>Módulo de prescrição de treinos e biblioteca de exercícios em desenvolvimento.</p>

<?php include __DIR__ . '/../shared/footer.php'; ?>