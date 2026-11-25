<?php
// =============================================
// GYMFLOW - COMPONENTE DE FREQUÊNCIA
// =============================================

$freqHoje = (
    new DateTimeImmutable(
        'today',
        new DateTimeZone('America/Sao_Paulo')
    )
)->format('Y-m-d');

$freqUrl = BASE_URL .
    '/app/controllers/FrequenciaTreinoController.php';
?>

<section
    id="gf-frequencia"
    data-hoje="<?= htmlspecialchars(
                    $freqHoje,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
    data-url="<?= htmlspecialchars(
                    $freqUrl,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>">

    <!-- INDICADORES MENSAIS -->

    <div class="gf-stats">

        <article class="gf-stat gf-stat-completo">

            <div class="gf-stat-icon">
                <i data-lucide="check-circle"></i>
            </div>

            <span class="gf-muted">
                Dias concluídos
            </span>

            <strong data-total="completo">
                —
            </strong>

        </article>

        <article class="gf-stat gf-stat-parcial">

            <div class="gf-stat-icon">
                <i data-lucide="clock"></i>
            </div>

            <span class="gf-muted">
                Dias parciais
            </span>

            <strong data-total="parcial">
                —
            </strong>

        </article>

        <article class="gf-stat gf-stat-ativos">

            <div class="gf-stat-icon">
                <i data-lucide="activity"></i>
            </div>

            <span class="gf-muted">
                Dias ativos
            </span>

            <strong data-total="ativos">
                —
            </strong>

        </article>

    </div>

    <!-- CALENDÁRIO E DETALHES -->

    <div class="gf-body">

        <article class="gf-calendar">

            <div class="gf-section-heading">

                <h2>Calendário de treinos</h2>

                <p class="gf-muted">
                    Selecione uma data para consultar
                    seus exercícios.
                </p>

            </div>

            <!-- NAVEGAÇÃO -->

            <div class="gf-nav">

                <button
                    type="button"
                    data-nav="-1"
                    aria-label="Mês anterior">

                    <i data-lucide="chevron-left"></i>

                </button>

                <strong data-mes></strong>

                <button
                    type="button"
                    data-nav="1"
                    aria-label="Próximo mês">

                    <i data-lucide="chevron-right"></i>

                </button>

            </div>

            <!-- DIAS -->

            <div
                class="gf-grid"
                data-calendario>
            </div>

            <!-- LEGENDA -->

            <div class="gf-legend">

                <span>
                    <i class="gf-dot gf-completo"></i>
                    Concluído
                </span>

                <span>
                    <i class="gf-dot gf-parcial"></i>
                    Parcial
                </span>

                <span>
                    <i class="gf-dot"></i>
                    Sem treino
                </span>

            </div>

            <p
                class="gf-muted gf-notice"
                data-aviso
                role="status">
            </p>

            <button
                type="button"
                class="gf-retry"
                data-retry
                hidden>

                Tentar novamente

            </button>

        </article>

        <!-- DETALHES DA DATA -->

        <article
            class="gf-detail"
            data-detalhes
            hidden>

            <div class="gf-section-heading">

                <h2>Resumo do dia</h2>

                <span
                    class="gf-muted"
                    data-data>
                </span>

            </div>

            <div class="gf-daily-summary">

                <span
                    class="gf-status"
                    data-status>
                </span>

                <strong
                    class="gf-daily-count"
                    data-contagem>
                    0/4
                </strong>

                <p
                    class="gf-muted"
                    data-quantidade>
                </p>

                <progress
                    class="gf-progress"
                    max="4"
                    value="0"
                    aria-label="Meta diária"
                    data-progresso>
                </progress>

            </div>

            <div class="gf-exercise-section">

                <h3>Exercícios realizados</h3>

                <p
                    class="gf-muted"
                    data-vazio
                    hidden>

                    Nenhum exercício registrado
                    nesta data.

                </p>

                <ul
                    class="gf-list"
                    data-exercicios>
                </ul>

            </div>

        </article>

    </div>

</section>