<?php
// app/views/dashboard/index.php

if (!isset($tituloPagina, $cards, $baseUrl)) {
    http_response_code(403);
    exit;
}

$tituloPagina = 'Dashboard';

$cssEspecifico =
    BASE_URL . '/assets/css/dashboard-v2.css';

$role = $_SESSION['usuario_role'] ?? '';

$h = static fn($valor) => htmlspecialchars(
    (string) $valor,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$subtitulo = match ($role) {

    'Admin' =>
        'Acompanhe o desempenho da sua rede.',

    'Recepcao' =>
        'Acompanhe a rotina das suas unidades.',

    'Professor' =>
        'Acompanhe seus alunos e atividades.',

    default =>
        'Acompanhe os indicadores da academia.'
};

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main class="conteudo dashboard-page">

    <header class="dashboard-topo">

        <div class="dashboard-titulo">

            <h1>Dashboard</h1>

            <p><?= $h($subtitulo) ?></p>

        </div>

        <div class="dashboard-controles">

            <form
                method="GET"
                action="<?= $h($baseUrl) ?>"
                class="dashboard-filtro"
                id="dashboardFiltro">

                <div class="dashboard-campo">

                    <label for="periodo">
                        Período
                    </label>

                    <select
                        name="periodo"
                        id="periodo">

                        <?php foreach ($periodos as $valor => $rotulo): ?>

                            <option
                                value="<?= $h($valor) ?>"
                                <?= $periodo === $valor
                                    ? 'selected'
                                    : '' ?>>

                                <?= $h($rotulo) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div
                    class="dashboard-datas"
                    id="dashboardDatas"
                    <?= $periodo !== 'personalizado'
                        ? 'hidden'
                        : '' ?>>

                    <div class="dashboard-campo">

                        <label for="data_inicio">
                            De
                        </label>

                        <input
                            type="date"
                            name="data_inicio"
                            id="data_inicio"
                            value="<?= $h($dataInicio) ?>"
                            <?= $periodo === 'personalizado'
                                ? 'required'
                                : 'disabled' ?>>

                    </div>

                    <div class="dashboard-campo">

                        <label for="data_fim">
                            Até
                        </label>

                        <input
                            type="date"
                            name="data_fim"
                            id="data_fim"
                            value="<?= $h($dataFim) ?>"
                            min="<?= $h($dataInicio) ?>"
                            <?= $periodo === 'personalizado'
                                ? 'required'
                                : 'disabled' ?>>

                    </div>

                </div>

                <button
                    type="submit"
                    class="dashboard-aplicar">

                    Aplicar

                </button>

            </form>

            <div class="dashboard-data">

                <span>Atualizado em</span>

                <strong>
                    <?= $h($hoje->format('d/m/Y')) ?>
                </strong>

            </div>

        </div>

    </header>

    <?php if ($erroFiltro !== ''): ?>

        <div
            class="dashboard-alerta"
            role="alert">

            <?= $h($erroFiltro) ?>

        </div>

    <?php endif; ?>

    <p class="dashboard-periodo-atual">

        Período de consulta:
        <strong><?= $h($periodoDescricao) ?></strong>

    </p>

    <section
        class="dashboard-indicadores"
        data-quantidade="<?= count($cards) ?>"
        aria-label="Indicadores principais">

        <?php foreach ($cards as $card): ?>

            <article class="dashboard-indicador">

                <div class="dashboard-indicador-topo">

                    <span class="dashboard-indicador-label">

                        <?= $h($card['titulo']) ?>

                    </span>

                </div>

                <div class="dashboard-indicador-centro">

                    <strong class="dashboard-indicador-valor">

                        <?= $h($card['valor']) ?>

                    </strong>

                    <span
                        class="dashboard-indicador-icone"
                        aria-hidden="true">

                        <i data-lucide="<?= $h($card['icone']) ?>"></i>

                    </span>

                </div>

                <p class="dashboard-indicador-info">

                    <?= $h($card['info']) ?>

                </p>

            </article>

        <?php endforeach; ?>

    </section>

    <section class="dashboard-grafico-card">

        <div class="dashboard-grafico-topo">

            <div>

                <h2>Crescimento de alunos</h2>

                <p>
                    Novos alunos cadastrados no período selecionado.
                </p>

            </div>

            <div class="dashboard-grafico-total">

                <div class="dashboard-total-icone" aria-hidden="true">

                    <i data-lucide="users"></i>

                </div>

                <div class="dashboard-total-info">

                    <span>Total no período</span>

                    <strong>

                        <?= number_format(
                            $totalNovosPeriodo,
                            0,
                            ',',
                            '.'
                        ) ?>

                    </strong>

                    <small>novos cadastros</small>

                </div>

            </div>

        </div>

        <div class="dashboard-grafico-area">

            <canvas
                id="graficoAlunos"
                role="img"
                aria-label="Gráfico de novos alunos no período selecionado">
            </canvas>

        </div>

        <p
            id="dashboardGraficoErro"
            class="dashboard-grafico-rodape"
            role="status"
            hidden>

            Não foi possível carregar o gráfico.

        </p>

        <p class="dashboard-grafico-rodape">

            Os indicadores respeitam as permissões do seu perfil.

        </p>

    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Controle de período personalizado.

    const periodo = document.getElementById('periodo');

    const datas = document.getElementById('dashboardDatas');

    const inicio = document.getElementById('data_inicio');

    const fim = document.getElementById('data_fim');

    function atualizarPeriodo() {

        const personalizado =
            periodo.value === 'personalizado';

        datas.hidden = !personalizado;

        inicio.disabled = !personalizado;
        fim.disabled = !personalizado;

        inicio.required = personalizado;
        fim.required = personalizado;
    }

    function atualizarLimites() {

        fim.min = inicio.value;

        if (
            inicio.value &&
            fim.value &&
            fim.value < inicio.value
        ) {
            fim.value = inicio.value;
        }
    }

    periodo.addEventListener(
        'change',
        atualizarPeriodo
    );

    inicio.addEventListener(
        'change',
        atualizarLimites
    );

    atualizarPeriodo();
    atualizarLimites();

    // Gráfico.

    const canvas =
        document.getElementById('graficoAlunos');

    const pagina =
        document.querySelector('.dashboard-page');

    if (!canvas || !pagina) {
        return;
    }

    if (typeof Chart === 'undefined') {

        document.getElementById(
            'dashboardGraficoErro'
        ).hidden = false;

        return;
    }

    const labels = <?= json_encode(
        $labelsGrafico,
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT
    ) ?>;

    const valores = <?= json_encode(
        $valoresGrafico,
        JSON_NUMERIC_CHECK
    ) ?>;

    const estilos = getComputedStyle(pagina);

    const obterCor = (variavel, padrao) =>
        estilos.getPropertyValue(variavel).trim() ||
        padrao;

    const primaria = obterCor(
        '--cor-primaria',
        '#ffb000'
    );

    const texto = obterCor(
        '--cor-texto',
        '#111827'
    );

    const textoSuave = obterCor(
        '--cor-texto-secundario',
        '#64748b'
    );

    const borda = obterCor(
        '--cor-borda',
        '#e2e8f0'
    );

    const superficie = obterCor(
        '--cor-superficie',
        '#ffffff'
    );

    const fundoGrafico =
        /^#[0-9a-f]{6}$/i.test(primaria)
            ? primaria + '18'
            : 'rgba(100, 116, 139, 0.10)';

    new Chart(canvas, {

        type: 'line',

        data: {

            labels: labels,

            datasets: [{
                label: 'Novos alunos',
                data: valores,

                borderColor: primaria,
                backgroundColor: fundoGrafico,

                fill: true,
                borderWidth: 2,
                tension: 0,

                pointRadius: valores.length > 31 ? 2 : 3,
                pointHoverRadius: 5,

                pointBackgroundColor: superficie,
                pointBorderColor: primaria,
                pointBorderWidth: 2
            }]
        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            animation: false,

            interaction: {
                mode: 'index',
                intersect: false
            },

            plugins: {

                legend: {
                    display: false
                },

                tooltip: {

                    backgroundColor: superficie,

                    titleColor: texto,
                    bodyColor: texto,

                    borderColor: borda,
                    borderWidth: 1,

                    padding: 10,
                    cornerRadius: 6
                }
            },

            scales: {

                x: {

                    border: {
                        color: borda
                    },

                    grid: {
                        display: false
                    },

                    ticks: {
                        color: textoSuave,
                        autoSkip: true,
                        maxRotation: 0,
                        maxTicksLimit: 12
                    }
                },

                y: {

                    beginAtZero: true,
                    suggestedMax: 1,

                    border: {
                        color: borda
                    },

                    ticks: {
                        precision: 0,
                        color: textoSuave
                    },

                    grid: {
                        color: borda
                    }
                }
            }
        }
    });

});
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>