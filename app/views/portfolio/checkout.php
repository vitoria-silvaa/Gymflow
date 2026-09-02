<?php
// app/views/portfolio/checkout.php
// Redireciona de forma compatível e transparente para o fluxo de matrícula oficial

$planoId = isset($_GET['plano_id']) ? (int) $_GET['plano_id'] : 0;
$destino = '/Gymflow/app/controllers/MatriculaController.php' . ($planoId > 0 ? '?plano_id=' . $planoId : '');

header("Location: $destino");
exit;
