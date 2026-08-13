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

<?php if (!empty($leads)): ?>

    <?php foreach ($leads as $lead): ?>

        <p>
            <strong>Nome:</strong>
            <?= htmlspecialchars($lead['nome']) ?>
        </p>

        <p>
            <strong>Objetivo:</strong>
            <?= htmlspecialchars($lead['objetivo']) ?>
        </p>

        <p>
            <strong>Campanha:</strong>
            <?= htmlspecialchars($lead['campanha']) ?>
        </p>

        <hr>

    <?php endforeach; ?>

<?php else: ?>

    <p>Nenhum lead encontrado.</p>

<?php endif; ?>

<p>Módulo de prospecção e novos clientes</p>

<a href="/Gymflow/app/controllers/CrmController.php?acao=cadastrar">Novo Lead</a>
<?php include __DIR__ . '/../shared/footer.php'; ?>