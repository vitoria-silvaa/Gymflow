<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/DashboardController.php");
    exit;
}
/** @var array $cards */

$tituloPagina = "Dashboard";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<h1>Painel Geral (Dashboard)</h1>
<p>Seja bem-vindo ao GymCore! Utilize o menu lateral para gerenciar o sistema.</p>

<section>
    <h2>Painel Executivo</h2>

    <div>
        <?php foreach ($cards as $card): ?>
            <div>
                <h3><?= htmlspecialchars($card['titulo']); ?></h3>
                <p><strong><?= htmlspecialchars((string) $card['valor']); ?></strong></p>
                <small><?= htmlspecialchars($card['info']); ?></small>
            </div>
            <br>
        <?php endforeach; ?>
    </div>
</section>

<?php include __DIR__ . '/../shared/footer.php'; ?>