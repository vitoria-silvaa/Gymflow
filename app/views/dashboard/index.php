<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/DashboardController.php");
    exit;
}
/** @var array $cards */
/** @var array $dadosGraficoNovosAlunos */

$tituloPagina = "Dashboard";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main class="conteudo">
    <h1>Painel Geral (Dashboard)</h1>
    <p>Seja bem-vindo ao GymCore! Utilize o menu lateral para gerenciar o sistema.</p>

    <section class="painel-executivo">
        <h2>Painel Executivo</h2>

        <div class="dashboard-cards">
            <?php foreach ($cards as $card): ?>
                <div class="dashboard-card">
                    <h3><?= htmlspecialchars($card['titulo']); ?></h3>
                    <p class="dashboard-card-valor"><strong><?= htmlspecialchars((string) $card['valor']); ?></strong></p>
                    <small><?= htmlspecialchars($card['info']); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="painel-grafico">
        <h2>Novos Alunos</h2>
        <div class="grafico-container" style="max-width: 800px; margin-top: 15px;">
            <canvas id="graficoAlunos"></canvas>
        </div>
    </section>
</main>

<!-- Inclusão do Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const dadosAlunos = <?= json_encode($dadosGraficoNovosAlunos ?? []) ?>;

    const meses = [
        'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
        'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'
    ];

    const valores = Array(12).fill(0);

    dadosAlunos.forEach(item => {
        valores[item.mes - 1] = Number(item.total);
    });

    new Chart(document.getElementById('graficoAlunos'), {
        type: 'line',

        data: {
            labels: meses,
            datasets: [{
                label: 'Novos Alunos',
                data: valores
            }]
        },

        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
<?php include __DIR__ . '/../shared/footer.php'; ?>