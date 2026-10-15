<?php
// app/views/dashboard/index.php

if (!isset($tituloPagina)) {
    header(
        "Location: " . BASE_URL .
            "/app/controllers/DashboardController.php"
    );
    exit;
}

$tituloPagina = "Dashboard";

$cssEspecifico =
    BASE_URL . '/assets/css/dashboard-v2.css';

$role = $_SESSION['usuario_role'] ?? '';
$anoAtual = date('Y');

$subtitulo = match ($role) {
    'Admin' => 'Acompanhe o desempenho da sua rede.',
    'Recepcao' => 'Acompanhe a rotina das suas unidades.',
    'Professor' => 'Acompanhe seus alunos e atividades.',
    default => 'Acompanhe os indicadores da academia.'
};

// Prepara os 12 meses do gráfico.
$valoresGrafico = array_fill(0, 12, 0);

foreach ($dadosGraficoNovosAlunos ?? [] as $item) {

    $mes = (int) ($item['mes'] ?? 0);

    if ($mes >= 1 && $mes <= 12) {
        $valoresGrafico[$mes - 1] =
            (int) ($item['total'] ?? 0);
    }
}

$totalNovosAno = array_sum($valoresGrafico);

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main class="conteudo dashboard-page">

    <!-- CABEÇALHO -->
    <header class="dashboard-topo">

        <div>
            <span class="dashboard-etiqueta">
                VISÃO GERAL
            </span>

            <h1>Dashboard</h1>

            <p>
                <?= htmlspecialchars(
                    $subtitulo,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>
        </div>

        <div class="dashboard-data">
            <span>Atualizado em</span>
            <strong><?= date('d/m/Y') ?></strong>
        </div>

    </header>

    <!-- CARDS -->
    <section class="dashboard-indicadores"
        aria-label="Indicadores principais">

        <?php foreach ($cards ?? [] as $indice => $card): ?>

            <article class="dashboard-indicador">

                <div class="dashboard-indicador-topo">

                    <span class="dashboard-indicador-label">
                        <?= htmlspecialchars(
                            $card['titulo'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>

                    <span class="dashboard-indicador-icone"
                        aria-hidden="true">
                        <?= ['↗', '▤', '◈', '%'][$indice % 4] ?>
                    </span>

                </div>

                <strong class="dashboard-indicador-valor">
                    <?= htmlspecialchars(
                        (string) ($card['valor'] ?? '0'),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </strong>

                <p class="dashboard-indicador-info">
                    <?= htmlspecialchars(
                        $card['info'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>

            </article>

        <?php endforeach; ?>

    </section>

    <!-- GRÁFICO -->
    <section class="dashboard-grafico-card">

        <div class="dashboard-grafico-topo">

            <div>
                <span class="dashboard-etiqueta">
                    ACOMPANHAMENTO
                </span>

                <h2>Crescimento de alunos</h2>

                <p>
                    Novos alunos cadastrados ao longo
                    de <?= $anoAtual ?>.
                </p>
            </div>

            <div class="dashboard-grafico-total">
                <span>Total no ano</span>

                <strong>
                    <?= number_format(
                        $totalNovosAno,
                        0,
                        ',',
                        '.'
                    ) ?>
                </strong>

                <small>novos cadastros</small>
            </div>

        </div>

        <div class="dashboard-grafico-area">
            <canvas id="graficoAlunos"></canvas>
        </div>

        <p class="dashboard-grafico-rodape">
            Os indicadores respeitam as permissões
            do seu perfil.
        </p>

    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const canvas = document.getElementById('graficoAlunos');

        if (!canvas || typeof Chart === 'undefined') {
            return;
        }

        const meses = [
            'Jan', 'Fev', 'Mar', 'Abr',
            'Mai', 'Jun', 'Jul', 'Ago',
            'Set', 'Out', 'Nov', 'Dez'
        ];

        const valores = <?= json_encode(
                            $valoresGrafico,
                            JSON_NUMERIC_CHECK
                        ) ?>;

        const contexto = canvas.getContext('2d');

        const corPrimaria = getComputedStyle(
            document.documentElement
        ).getPropertyValue('--cor-primaria').trim() || '#10b981';


        const gradiente = contexto.createLinearGradient(
            0, 0, 0, 340
        );

        gradiente.addColorStop(0, corPrimaria + '38');
        gradiente.addColorStop(1, corPrimaria + '00');


        new Chart(contexto, {
            type: 'line',

            data: {
                labels: meses,

                datasets: [{
                    label: 'Novos alunos',
                    data: valores,
                    borderColor: corPrimaria,
                    backgroundColor: gradiente,
                    fill: true,
                    borderWidth: 3,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: corPrimaria,
                    pointBorderWidth: 2
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        display: false
                    },

                    tooltip: {
                        backgroundColor: '#181818',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 14,
                        cornerRadius: 10
                    }
                },

                scales: {
                    x: {
                        grid: {
                            display: false
                        },

                        ticks: {
                            color: '#858585'
                        }
                    },

                    y: {
                        beginAtZero: true,

                        ticks: {
                            precision: 0,
                            color: '#858585'
                        },

                        grid: {
                            color: '#eeeeee'
                        }
                    }
                }
            }
        });
    });
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>