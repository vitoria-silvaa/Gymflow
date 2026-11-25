<?php
// =============================================
// GYMFLOW - PORTAL DO ALUNO
// app/views/portal_aluno/treinos.php
// =============================================

$tituloPagina = 'Meus Treinos';

$cssEspecifico = BASE_URL .
    '/assets/css/treino-guiado.css';

$fichas = $fichas ?? [];
$itens = $itens ?? [];
$ficha_atual = $ficha_atual ?? null;

$url = BASE_URL .
    '/app/controllers/PortalAlunoController.php?acao=treinos';

$esc = static fn($v) => htmlspecialchars(
    (string) $v,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

// =============================================
// PREPARAR EXERCÍCIOS
// =============================================

$exercicios = [];

foreach ($itens as $i) {

    $midia = trim((string) ($i['midia'] ?? ''));

    if (str_starts_with($midia, 'assets/uploads/exercicios/')) {
        $midia = BASE_URL . '/' . $midia;
    }

    if (str_starts_with($midia, '/assets/uploads/exercicios/')) {
        $midia = BASE_URL . $midia;
    }

    $prefixo = BASE_URL . '/assets/uploads/exercicios/';

    if (
        !str_starts_with($midia, $prefixo) ||
        !preg_match(
            '/^[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp|gif|mp4|webm)$/i',
            substr($midia, strlen($prefixo))
        )
    ) {
        $midia = '';
    }

    $exercicios[] = [

        'item_id' => (int) (
            $i['item_id'] ?? 0
        ),

        'nome' => (string) (
            $i['nome_exercicio'] ?? 'Exercício'
        ),

        'grupo' => (string) (
            $i['grupo'] ??
            $i['grupo_muscular'] ??
            'Não informado'
        ),

        'series' => max(1, (int) ($i['series'] ?? 1)),

        'reps' => (string) ($i['repeticoes'] ?? ''),

        'carga' => (string) ($i['carga'] ?? ''),

        'intervalo' => (string) ($i['intervalo'] ?? ''),

        'midia' => $midia,

        'video' => in_array(
            strtolower(pathinfo($midia, PATHINFO_EXTENSION)),
            ['mp4', 'webm'],
            true
        )

    ];
}

$idFicha = (int) ($ficha_atual['id'] ?? 0);

$dataFicha = (
    !empty($ficha_atual['criada_em']) &&
    strtotime($ficha_atual['criada_em']) !== false
)
    ? date('d/m/Y', strtotime($ficha_atual['criada_em']))
    : 'Não informada';

$totalSeries = array_sum(
    array_column($exercicios, 'series')
);

// =============================================
// IDENTIFICAR SESSÃO TEMPORÁRIA
// =============================================

$chave = 'gymflow:guiado:v2:' . hash(
    'sha256',
    session_id() . '|' .
        ($_SESSION['company_id'] ?? 0) . '|' .
        ($_SESSION['aluno_id'] ?? 0) . '|' .
        $idFicha . '|' .
        serialize($exercicios)
);

if (
    empty($_SESSION['historico_treino_csrf']) ||
    !is_string($_SESSION['historico_treino_csrf'])
) {

    $_SESSION['historico_treino_csrf'] =
        bin2hex(random_bytes(32));
}

$json = json_encode(
    [
        'exercicios' => $exercicios,

        'chave' => $chave,

        'ficha_id' => $idFicha,

        'versao' => (int) (
            $ficha_atual['versao'] ?? 1
        ),

        'csrf' =>
        $_SESSION['historico_treino_csrf'],

        'url_historico' => BASE_URL .
            '/app/controllers/HistoricoTreinoController.php'
    ],
    JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_INVALID_UTF8_SUBSTITUTE |
        JSON_UNESCAPED_UNICODE
);

require __DIR__ . '/../shared/header.php';
require __DIR__ . '/../shared/sidebar.php';
?>

<main class="tg-page" id="tgPage">
    <section
        class="tg-ficha-summary"
        id="tgFrequencyCard"
        hidden>

        <div class="tg-summary-top">

            <div>

                <span class="tg-eyebrow">
                    MINHA FREQUÊNCIA
                </span>

                <h2 id="tgFrequencyTitle">
                    Meta diária
                </h2>

                <p id="tgFrequencyText">
                    Carregando frequência...
                </p>

            </div>

            <strong
                id="tgFrequencyCount"
                style="font-size: 24px;">

                0/4

            </strong>

        </div>

        <progress
            id="tgFrequencyProgress"
            value="0"
            max="4"
            aria-label="Meta diária de exercícios"
            style="
            width: 100%;
            height: 8px;
            accent-color: #d97706;
        ">
        </progress>

        <p
            id="tgFrequencyMessage"
            style="font-size: 12px; margin-bottom: 0;">
        </p>

    </section>

    <p id="tgBoot" role="status">
        Carregando controles do treino...
    </p>

    <!-- =====================================
         FICHA DE TREINO
    ====================================== -->

    <div id="tgOverview">

        <header class="tg-page-header">

            <h1>Meus treinos</h1>

            <p>
                Acompanhe e execute sua ficha de treino.
            </p>

        </header>

        <?php if (!$fichas): ?>

            <section class="tg-empty">

                <h2>Nenhuma ficha disponível</h2>

                <p>
                    Aguarde seu professor cadastrar sua ficha.
                </p>

            </section>

        <?php else: ?>

            <!-- FICHAS -->

            <section class="tg-fichas">

                <h2>Suas fichas</h2>

                <nav
                    class="tg-ficha-tabs"
                    aria-label="Fichas de treino">

                    <?php foreach ($fichas as $f): ?>

                        <a
                            class="tg-ficha-tab <?= (int) $f['id'] === $idFicha ? 'is-active' : '' ?>"
                            href="<?= $esc($url . '&ficha=' . (int) $f['id']) ?>">

                            <span>

                                <strong>
                                    <?= $esc($f['objetivo'] ?? 'Treino') ?>
                                </strong>

                                <small>
                                    Versão <?= $esc($f['versao'] ?? 1) ?>
                                </small>

                            </span>

                        </a>

                    <?php endforeach; ?>

                </nav>

            </section>

            <?php if ($ficha_atual): ?>

                <!-- RESUMO DA FICHA -->

                <section class="tg-ficha-summary">

                    <div class="tg-summary-top">

                        <div>

                            <h2>
                                <?= $esc($ficha_atual['objetivo'] ?? 'Treino') ?>
                            </h2>

                            <div class="tg-meta">

                                <span>
                                    Prof. <?= $esc($ficha_atual['nome_professor'] ?? 'Não informado') ?>
                                </span>

                                <span>
                                    <?= $esc($dataFicha) ?>
                                </span>

                            </div>

                        </div>

                        <button
                            type="button"
                            class="tg-btn tg-btn-secondary"
                            data-tg-action="print">

                            Imprimir / salvar PDF

                        </button>

                    </div>

                    <div class="tg-summary-stats">

                        <div>

                            <span>Exercícios</span>

                            <strong>
                                <?= count($exercicios) ?>
                            </strong>

                        </div>

                        <div>

                            <span>Total de séries</span>

                            <strong>
                                <?= $totalSeries ?>
                            </strong>

                        </div>

                        <div>

                            <span>Versão</span>

                            <strong>
                                <?= $esc($ficha_atual['versao'] ?? 1) ?>
                            </strong>

                        </div>

                    </div>

                    <?php if ($exercicios): ?>

                        <div class="tg-summary-actions">

                            <button
                                type="button"
                                id="tgStartAll"
                                class="tg-btn tg-btn-primary"
                                data-tg-action="all">

                                Iniciar treino

                            </button>

                            <button
                                type="button"
                                id="tgResume"
                                class="tg-btn tg-btn-primary"
                                data-tg-action="resume"
                                hidden>

                                Continuar treino

                            </button>

                            <p id="tgSessionNotice">
                                Execute os exercícios na ordem prescrita.
                            </p>

                        </div>

                    <?php endif; ?>

                </section>

                <!-- EXERCÍCIOS -->

                <section class="tg-exercise-section">

                    <div class="tg-section-heading">

                        <h2>Exercícios da ficha</h2>

                        <span class="tg-count">

                            <?= count($exercicios) ?> exercícios

                        </span>

                    </div>

                    <div class="tg-exercise-grid">

                        <?php foreach ($exercicios as $n => $e): ?>

                            <article class="tg-exercise-card">

                                <!-- MÍDIA -->

                                <div class="tg-media">

                                    <?php if ($e['midia'] && $e['video']): ?>

                                        <video
                                            src="<?= $esc($e['midia']) ?>"
                                            controls
                                            playsinline
                                            preload="metadata">
                                        </video>

                                    <?php elseif ($e['midia']): ?>

                                        <img
                                            src="<?= $esc($e['midia']) ?>"
                                            alt="<?= $esc($e['nome']) ?>"
                                            loading="lazy">

                                    <?php endif; ?>

                                    <div
                                        class="tg-media-empty"
                                        <?= $e['midia'] ? 'hidden' : '' ?>>

                                        Sem mídia disponível

                                    </div>

                                </div>

                                <!-- INFORMAÇÕES -->

                                <div class="tg-exercise-content">

                                    <span class="tg-muscle">

                                        <?= $esc($e['grupo']) ?>

                                    </span>

                                    <h3>
                                        <?= $esc($e['nome']) ?>
                                    </h3>

                                    <div class="tg-exercise-data">

                                        <?php foreach (
                                            [
                                                'Séries' => $e['series'],
                                                'Repetições' => $e['reps'],
                                                'Carga' => $e['carga'],
                                                'Descanso' => $e['intervalo']
                                            ] as $label => $valor
                                        ): ?>

                                            <div>

                                                <span>
                                                    <?= $label ?>
                                                </span>

                                                <strong>
                                                    <?= $esc($valor === '' ? '—' : $valor) ?>
                                                </strong>

                                            </div>

                                        <?php endforeach; ?>

                                    </div>

                                    <button
                                        type="button"
                                        class="tg-btn tg-btn-primary tg-start-exercise"
                                        data-tg-action="one"
                                        data-index="<?= $n ?>">

                                        Iniciar exercício

                                    </button>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                    <?php if (!$exercicios): ?>

                        <div class="tg-empty">
                            Ficha sem exercícios.
                        </div>

                    <?php endif; ?>

                </section>

            <?php endif; ?>

        <?php endif; ?>

    </div>

    <!-- =====================================
         TREINO GUIADO
    ====================================== -->

    <section
        class="tg-guide"
        id="tgGuide"
        hidden>

        <header class="tg-guide-header">

            <button
                type="button"
                class="tg-btn tg-btn-secondary"
                data-tg-action="back">

                Voltar à ficha

            </button>

            <button
                type="button"
                id="tgEndRequest"
                class="tg-btn tg-btn-danger"
                data-tg-action="askEnd">

                Encerrar treino

            </button>

        </header>

        <!-- CONFIRMAÇÃO -->

        <div
            class="tg-end-confirm"
            id="tgEndConfirm"
            hidden>

            <strong>
                Descartar a sessão atual?
            </strong>

            <p>
                Os exercícios concluídos continuarão no histórico.
                Apenas o progresso dos exercícios não concluídos
                será descartado.
            </p>

            <div class="tg-button-row">

                <button
                    type="button"
                    class="tg-btn tg-btn-secondary"
                    data-tg-action="cancelEnd">

                    Continuar

                </button>

                <button
                    type="button"
                    class="tg-btn tg-btn-danger"
                    data-tg-action="end">

                    Encerrar e descartar

                </button>

            </div>

        </div>

        <div id="tgRunning">

            <!-- PROGRESSO -->

            <section class="tg-progress-card">

                <div class="tg-progress-heading">

                    <h2>Treino em andamento</h2>

                    <strong id="tgPercent">0%</strong>

                </div>

                <progress
                    id="tgProgress"
                    max="100"
                    value="0">
                </progress>

                <div class="tg-progress-labels">

                    <span id="tgPosition"></span>

                    <span id="tgCount"></span>

                </div>

            </section>

            <div class="tg-guide-grid">

                <!-- EXERCÍCIO ATUAL -->

                <section class="tg-guide-card">

                    <div
                        class="tg-media tg-guide-media"
                        id="tgGuideMedia">
                    </div>

                    <div class="tg-guide-content">

                        <span
                            id="tgGroup"
                            class="tg-muscle">
                        </span>

                        <h2 id="tgName"></h2>

                        <div class="tg-prescription">

                            <div>
                                <span>Séries</span>
                                <strong id="tgSets"></strong>
                            </div>

                            <div>
                                <span>Repetições</span>
                                <strong id="tgReps"></strong>
                            </div>

                            <div>
                                <span>Carga prescrita</span>
                                <strong id="tgLoad"></strong>
                            </div>

                            <div>
                                <span>Descanso</span>
                                <strong id="tgRest"></strong>
                            </div>

                        </div>

                    </div>

                </section>

                <!-- PAINEL DE EXECUÇÃO -->

                <section class="tg-guide-card tg-execution">

                    <div class="tg-execution-heading">

                        <h2 id="tgPhase"></h2>

                        <p id="tgHint"></p>

                    </div>

                    <div
                        class="tg-set-indicators"
                        id="tgIndicators">
                    </div>

                    <!-- CRONÔMETRO -->

                    <div id="tgRestPanel">

                        <div class="tg-timer">

                            <svg
                                viewBox="0 0 140 140"
                                aria-hidden="true">

                                <circle
                                    class="tg-timer-track"
                                    cx="70"
                                    cy="70"
                                    r="57">
                                </circle>

                                <circle
                                    class="tg-timer-ring"
                                    id="tgRing"
                                    cx="70"
                                    cy="70"
                                    r="57"
                                    transform="rotate(-90 70 70)">
                                </circle>

                            </svg>

                            <div class="tg-timer-label">

                                <strong id="tgTime">
                                    00:00
                                </strong>

                                <span id="tgTimerStatus"></span>

                            </div>

                        </div>

                        <div
                            class="tg-timer-actions"
                            id="tgControls"
                            hidden>

                            <button
                                type="button"
                                id="tgPause"
                                class="tg-btn tg-btn-secondary"
                                data-tg-action="pause">

                                Pausar

                            </button>

                            <button
                                type="button"
                                id="tgAdd"
                                class="tg-btn tg-btn-secondary"
                                data-tg-action="add">

                                +15 s

                            </button>

                            <button
                                type="button"
                                class="tg-btn tg-btn-secondary"
                                data-tg-action="skip">

                                Pular

                            </button>

                        </div>

                    </div>

                    <!-- REGISTRO DA SÉRIE -->

                    <div id="tgWorkPanel">

                        <div class="tg-fields">

                            <div>

                                <label for="tgActualReps">
                                    Repetições realizadas
                                </label>

                                <input
                                    type="number"
                                    id="tgActualReps"
                                    min="1"
                                    max="999"
                                    step="1"
                                    inputmode="numeric">

                            </div>

                            <div>

                                <label for="tgActualLoad">
                                    Carga utilizada
                                </label>

                                <input
                                    type="text"
                                    id="tgActualLoad"
                                    maxlength="40">

                            </div>

                        </div>

                        <p
                            id="tgError"
                            class="tg-error"
                            role="alert"
                            hidden>
                        </p>

                        <button
                            type="button"
                            class="tg-btn tg-btn-primary tg-full"
                            data-tg-action="complete">

                            Concluir série

                        </button>

                    </div>

                    <!-- AVANÇAR -->

                    <div id="tgNextPanel" hidden>

                        <button
                            type="button"
                            class="tg-btn tg-btn-primary tg-full"
                            id="tgNext"
                            data-tg-action="next">

                            Continuar

                        </button>

                    </div>

                </section>

            </div>

        </div>

        <!-- RESUMO FINAL -->

        <section
            class="tg-finish"
            id="tgFinishPanel"
            hidden>

            <h2>Execução concluída!</h2>

            <p id="tgFinishStats"></p>

            <div
                class="tg-finish-details"
                id="tgFinishDetails">
            </div>

            <p>
                Os exercícios concluídos foram registrados
                no seu histórico de treinos.
            </p>

            <button
                type="button"
                class="tg-btn tg-btn-primary"
                data-tg-action="end">

                Voltar à ficha

            </button>

        </section>

    </section>

    <!-- =====================================
         IMPRESSÃO DA FICHA
    ====================================== -->

    <?php if ($ficha_atual): ?>

        <article class="tg-print">

            <header>

                <strong>
                    <?= $esc($_SESSION['nome_painel'] ?? 'GymFlow') ?>
                </strong>

                <h1>Ficha de treinamento</h1>

            </header>

            <dl>

                <?php foreach (
                    [
                        'Aluno' => $ficha_atual['nome_aluno'] ?? $_SESSION['usuario_nome'] ?? 'Aluno',
                        'Professor' => $ficha_atual['nome_professor'] ?? 'Não informado',
                        'Unidade' => $ficha_atual['nome_filial'] ?? 'Não informada',
                        'Objetivo' => $ficha_atual['objetivo'] ?? 'Treino',
                        'Versão' => $ficha_atual['versao'] ?? 1,
                        'Data' => $dataFicha
                    ] as $k => $v
                ): ?>

                    <dt><?= $esc($k) ?></dt>

                    <dd><?= $esc($v) ?></dd>

                <?php endforeach; ?>

            </dl>

            <table>

                <thead>

                    <tr>

                        <th>Exercício</th>
                        <th>Grupo</th>
                        <th>Séries</th>
                        <th>Repetições</th>
                        <th>Carga</th>
                        <th>Descanso</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($exercicios as $e): ?>

                        <tr>

                            <?php foreach (
                                [
                                    'nome',
                                    'grupo',
                                    'series',
                                    'reps',
                                    'carga',
                                    'intervalo'
                                ] as $k
                            ): ?>

                                <td>

                                    <?= $esc($e[$k] === '' ? '—' : $e[$k]) ?>

                                </td>

                            <?php endforeach; ?>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </article>

    <?php endif; ?>

</main>

<!-- =========================================
     JAVASCRIPT INTEGRADO
========================================== -->

<script>
    (() => {

        'use strict';

        const dados = <?= $json ?: '{"exercicios":[],"chave":""}' ?>;

        const lista = dados.exercicios;

        const $ = id => document.getElementById(id);

        const show = (id, visivel) => {

            const elemento = $(id);

            if (elemento) {
                elemento.hidden = !visivel;
            }
        };

        const text = (id, valor) => {

            const elemento = $(id);

            if (elemento) {
                elemento.textContent = valor;
            }
        };

        const page = $('tgPage');

        let s = null;
        let mediaIndex = -1;
        let storageOk = true;
        let salvandoHistorico = false;
        let revisaoFrequencia = 0;

        const limite = 3600000;

        const circulo = 2 * Math.PI * 57;

        const atual = () => lista[s.fila[s.pos]];

        const feitas = () => s.registros[s.pos].length;

        const total = () => s.fila.reduce(
            (a, i) => a + lista[i].series,
            0
        );

        const realizadas = () => s.registros.reduce(
            (a, r) => a + r.length,
            0
        );

        // =====================================
        // ARMAZENAMENTO TEMPORÁRIO
        // =====================================

        function salvar() {

            try {

                if (s) {

                    sessionStorage.setItem(
                        dados.chave,
                        JSON.stringify({
                            s,
                            atualizado: Date.now()
                        })
                    );

                } else {

                    sessionStorage.removeItem(dados.chave);
                }

            } catch {

                storageOk = false;
            }
        }

        function restaurar() {

            try {

                const r = JSON.parse(
                    sessionStorage.getItem(dados.chave)
                );

                if (
                    !r ||
                    Date.now() - r.atualizado > 43200000
                ) {
                    return;
                }

                const x = r.s;

                if (
                    !x ||
                    !Array.isArray(x.fila) ||
                    !x.fila.length ||

                    !x.fila.every(i =>
                        Number.isInteger(i) &&
                        lista[i]
                    ) ||

                    !Number.isInteger(x.pos) ||
                    !lista[x.fila[x.pos]] ||

                    !Array.isArray(x.registros) ||

                    x.registros.length !== x.fila.length ||

                    !x.registros.every((a, i) =>
                        Array.isArray(a) &&
                        a.length <= lista[x.fila[i]].series &&
                        a.every(v =>
                            Number.isInteger(v.reps) &&
                            v.reps > 0 &&
                            v.reps <= 999 &&
                            typeof v.carga === 'string'
                        )
                    ) ||

                    ![
                        'work',
                        'rest',
                        'nextSet',
                        'nextExercise',
                        'done'
                    ].includes(x.fase) ||

                    !Number.isFinite(x.inicio)
                ) {
                    return;
                }

                if (
                    x.fase === 'rest' &&
                    (
                        !x.timer ||
                        !Number.isFinite(x.timer.duracao) ||
                        x.timer.duracao <= 0 ||
                        x.timer.duracao > limite ||
                        !Number.isFinite(x.timer.restante) ||
                        (
                            x.timer.fim !== null &&
                            !Number.isFinite(x.timer.fim)
                        )
                    )
                ) {
                    return;
                }

                s = x;

            } catch {

                // Continua funcionando sem rascunho.
            }
        }

        // =====================================
        // INTERPRETAR INTERVALO DO PROFESSOR
        // =====================================

        function intervalo(v) {

            v = String(v ?? '')
                .trim()
                .toLowerCase()
                .replace(',', '.');

            let m;
            let n;

            // Exemplo: 01:30

            if ((m = v.match(/^(\d+):([0-5]\d)$/))) {

                n = +m[1] * 60 + +m[2];

                // Exemplo: 1 min 30 s

            } else if (
                (m = v.match(
                    /^(\d+)\s*(?:m|min|minuto|minutos)\s*(\d+)\s*(?:s|seg|segundo|segundos)$/
                ))
            ) {

                n = +m[1] * 60 + +m[2];

                // Exemplo: 1.5 min

            } else if (
                (m = v.match(
                    /^(\d+(?:\.\d+)?)\s*(?:m|min|minuto|minutos)$/
                ))
            ) {

                n = +m[1] * 60;

                // Exemplos: 60, 60s, 60 segundos

            } else if (
                (m = v.match(
                    /^(\d+(?:\.\d+)?)\s*(?:s|seg|segs|segundo|segundos)?$/
                ))
            ) {

                n = +m[1];
            }

            return (
                    Number.isFinite(n) &&
                    n >= 0 &&
                    n <= 3600
                ) ?
                Math.ceil(n) :
                null;
        }

        function tempo(ms) {

            const t = Math.max(
                0,
                Math.ceil(ms / 1000)
            );

            return (
                String(Math.floor(t / 60)).padStart(2, '0') +
                ':' +
                String(t % 60).padStart(2, '0')
            );
        }

        // =====================================
        // MÍDIAS
        // =====================================

        function pausaVideos() {

            page.querySelectorAll('video')
                .forEach(v => v.pause());
        }

        function fallback(el, vazio) {

            const falha = () => {

                el.hidden = true;
                vazio.hidden = false;
            };

            el.addEventListener('error', falha);

            if (
                (
                    el.tagName === 'IMG' &&
                    el.complete &&
                    !el.naturalWidth
                ) ||
                el.error
            ) {

                falha();
            }
        }

        page.querySelectorAll('#tgOverview .tg-media')
            .forEach(c => {

                const el = c.querySelector('img,video');

                if (el) {

                    fallback(
                        el,
                        c.querySelector('.tg-media-empty')
                    );
                }
            });

        function midia() {

            const indice = s.fila[s.pos];

            if (mediaIndex === indice) return;

            mediaIndex = indice;

            const c = $('tgGuideMedia');

            const e = atual();

            c.querySelectorAll('video')
                .forEach(v => v.pause());

            c.replaceChildren();

            const vazio = document.createElement('div');

            vazio.className = 'tg-media-empty';

            vazio.textContent = 'Sem mídia disponível';

            c.append(vazio);

            if (!e.midia) return;

            const el = document.createElement(
                e.video ? 'video' : 'img'
            );

            if (e.video) {

                el.controls = true;
                el.playsInline = true;
                el.preload = 'metadata';

            } else {

                el.alt = e.nome;
            }

            vazio.hidden = true;

            el.addEventListener('error', () => {

                el.hidden = true;
                vazio.hidden = false;
            });

            c.prepend(el);

            el.src = e.midia;
        }

        // =====================================
        // TELA PRINCIPAL
        // =====================================

        function overview() {

            show('tgStartAll', !s);

            show('tgResume', !!s);

            text(
                'tgResume',
                s?.fase === 'done' ?
                'Ver resumo' :
                'Continuar treino'
            );

            text(
                'tgSessionNotice',
                s ?
                `${realizadas()}/${total()} séries realizadas.${storageOk ? '' : ' Progresso apenas nesta página.'}` :
                'Execute os exercícios na ordem prescrita.'
            );

            page.querySelectorAll('.tg-start-exercise')
                .forEach(b => {

                    b.disabled = !!s;
                });
        }

        // =====================================
        // INICIAR TREINO
        // =====================================

        function abrir() {

            pausaVideos();

            show('tgOverview', false);
            show('tgGuide', true);
            show('tgEndConfirm', false);

            render();
            tick();

            window.scrollTo(0, 0);
        }

        function iniciar(fila, tipo = 'individual') {

            if (s || !fila.length) return;

            s = {

                fila,

                tipo,

                pos: 0,

                registros: fila.map(() => []),

                fase: 'work',

                inicio: Date.now(),

                timer: null
            };

            mediaIndex = -1;

            salvar();

            abrir();
        }

        function voltar() {

            pausaVideos();

            show('tgGuide', false);
            show('tgOverview', true);
            show('tgEndConfirm', false);

            salvar();
            overview();

            window.scrollTo(0, 0);
        }

        function encerrar() {

            pausaVideos();

            s = null;

            mediaIndex = -1;

            $('tgGuideMedia').replaceChildren();

            salvar();

            voltar();
        }

        // =====================================
        // CRONÔMETRO
        // =====================================

        function restante() {

            return Math.max(
                0,
                s.timer.fim === null ?
                s.timer.restante :
                s.timer.fim - Date.now()
            );
        }

        function desenhar(ms, max, status) {

            text('tgTime', tempo(ms));

            text('tgTimerStatus', status);

            $('tgRing').style.strokeDasharray =
                String(circulo);

            $('tgRing').style.strokeDashoffset =
                String(
                    circulo * (
                        1 - (
                            max > 0 ?
                            Math.min(1, Math.max(0, ms / max)) :
                            0
                        )
                    )
                );
        }

        function fimDescanso() {

            if (s?.fase !== 'rest') return;

            s.timer = null;

            s.fase = 'nextSet';

            salvar();

            render();
        }

        function tick() {

            if (s?.fase !== 'rest') return;

            const ms = restante();

            if (ms <= 0) {

                fimDescanso();

                return;
            }

            if (!$('tgGuide').hidden) {

                desenhar(
                    ms,
                    s.timer.duracao,
                    s.timer.fim === null ?
                    'Pausado' :
                    'Descansando'
                );

                text(
                    'tgPause',
                    s.timer.fim === null ?
                    'Retomar' :
                    'Pausar'
                );

                $('tgAdd').disabled =
                    s.timer.duracao >= limite;
            }
        }

        // =====================================
        // RESUMO FINAL
        // =====================================

        function resumo() {

            const c = $('tgFinishDetails');

            c.replaceChildren();

            text(
                'tgFinishStats',
                `${realizadas()}/${total()} séries · Duração ${tempo((s.fim || Date.now()) - s.inicio)}`
            );

            s.fila.forEach((i, p) => {

                const card = document.createElement('article');

                card.className = 'tg-result-card';

                const h = document.createElement('h3');

                h.textContent = lista[i].nome;

                card.append(h);

                s.registros[p].forEach((r, n) => {

                    const row = document.createElement('div');

                    row.className = 'tg-result-row';

                    [
                        `Série ${n + 1}`,
                        `${r.reps} repetições`,
                        r.carga || 'Sem carga informada'
                    ].forEach(v => {

                        const span =
                            document.createElement('span');

                        span.textContent = v;

                        row.append(span);
                    });

                    card.append(row);
                });

                c.append(card);
            });
        }

        // =====================================
        // RENDERIZAÇÃO DA INTERFACE
        // =====================================

        function render() {

            overview();

            if (!s || $('tgGuide').hidden) return;

            const done = s.fase === 'done';

            show('tgRunning', !done);
            show('tgFinishPanel', done);
            show('tgEndRequest', !done);

            if (done) {

                pausaVideos();

                show('tgEndConfirm', false);

                resumo();

                return;
            }

            const e = atual();

            const n = feitas();

            const pct = Math.round(
                realizadas() / total() * 100
            );

            midia();

            text('tgPercent', pct + '%');

            $('tgProgress').value = pct;

            text(
                'tgPosition',
                `Exercício ${s.pos + 1} de ${s.fila.length}`
            );

            text(
                'tgCount',
                `${realizadas()}/${total()} séries`
            );

            text('tgName', e.nome);
            text('tgGroup', e.grupo);
            text('tgSets', e.series);
            text('tgReps', e.reps || '—');
            text('tgLoad', e.carga || '—');
            text('tgRest', e.intervalo || '—');

            // Indicadores das séries.

            const indicadores = $('tgIndicators');

            indicadores.replaceChildren();

            for (let j = 0; j < e.series; j++) {

                const span = document.createElement('span');

                span.className =
                    'tg-set-indicator' +
                    (
                        j < n ?
                        ' is-complete' :
                        j === n ?
                        ' is-current' :
                        ''
                    );

                span.textContent =
                    j < n ? '✓' : j + 1;

                indicadores.append(span);
            }

            show('tgError', false);

            show(
                'tgWorkPanel',
                s.fase === 'work'
            );

            show(
                'tgRestPanel',
                s.fase === 'work' ||
                s.fase === 'rest'
            );

            show(
                'tgControls',
                s.fase === 'rest'
            );

            show(
                'tgNextPanel',
                s.fase === 'nextSet' ||
                s.fase === 'nextExercise'
            );

            // EXECUÇÃO

            if (s.fase === 'work') {

                text(
                    'tgPhase',
                    `Série ${n + 1} de ${e.series}`
                );

                text(
                    'tgHint',
                    'Realize a série e registre sua execução.'
                );

                const sec = intervalo(e.intervalo);

                desenhar(
                    n + 1 === e.series ?
                    0 :
                    (sec ?? 0) * 1000,

                    (sec ?? 0) * 1000,

                    n + 1 === e.series ?
                    'Última série' :
                    sec === null ?
                    'Intervalo não definido' :
                    'Aguardando conclusão'
                );

                const ant = s.registros[s.pos][n - 1];

                $('tgActualReps').value =
                    ant?.reps ??
                    (
                        /^\d+$/.test(e.reps) ?
                        e.reps :
                        ''
                    );

                $('tgActualLoad').value =
                    ant?.carga ?? e.carga;

                // DESCANSO

            } else if (s.fase === 'rest') {

                text(
                    'tgPhase',
                    'Tempo de descanso'
                );

                text(
                    'tgHint',
                    `Série ${n} concluída.`
                );

                desenhar(
                    restante(),
                    s.timer.duracao,
                    s.timer.fim === null ?
                    'Pausado' :
                    'Descansando'
                );

                text(
                    'tgPause',
                    s.timer.fim === null ?
                    'Retomar' :
                    'Pausar'
                );

                $('tgAdd').disabled =
                    s.timer.duracao >= limite;

                // AVANÇAR

            } else {

                const prox =
                    s.fase === 'nextExercise';

                text(
                    'tgPhase',
                    prox ?
                    'Exercício concluído' :
                    'Próxima série'
                );

                text(
                    'tgHint',
                    prox ?
                    'Todas as séries foram realizadas.' :
                    'Continue quando estiver pronto.'
                );

                text(
                    'tgNext',
                    prox ?
                    'Próximo exercício' :
                    `Iniciar série ${n + 1}`
                );
            }
        }

        // =============================================
        // HISTÓRICO E FREQUÊNCIA
        // =============================================

        function gerarTokenHistorico() {

            const bytes = new Uint8Array(16);

            window.crypto.getRandomValues(bytes);

            return Array.from(
                bytes,
                b => b.toString(16).padStart(2, '0')
            ).join('');
        }

        // =============================================
        // ATUALIZAR INDICADOR DIÁRIO
        // =============================================

        function mostrarFrequencia(frequencia) {

            if (!frequencia) return;

            revisaoFrequencia++;

            const quantidade = Math.max(
                0,
                Number(frequencia.exercicios) || 0
            );

            const meta = 4;

            const concluido = quantidade >= meta;

            const cor = concluido ?
                'var(--tg-success)' :
                quantidade > 0 ?
                '#d97706' :
                'var(--tg-muted)';

            text(
                'tgFrequencyTitle',
                'Meta diária'
            );

            text(
                'tgFrequencyCount',
                `${quantidade}/${meta}`
            );

            text(
                'tgFrequencyText',

                concluido ?
                'Treino concluído' :
                quantidade > 0 ?
                'Treino parcialmente concluído' :
                'Sem treino registrado'
            );

            text(
                'tgFrequencyMessage',

                concluido ?
                'Você atingiu sua meta diária!' :
                `Faltam ${meta - quantidade} exercícios distintos para atingir sua meta.`
            );

            const titulo = $('tgFrequencyText');

            if (titulo) {

                titulo.style.color = cor;
            }

            const contador = $('tgFrequencyCount');

            if (contador) {

                contador.style.color = cor;
            }

            const barra = $('tgFrequencyProgress');

            if (barra) {

                barra.value = Math.min(
                    quantidade,
                    meta
                );

                barra.style.accentColor = cor;
            }

            show('tgFrequencyCard', true);
        }

        // =============================================
        // CONSULTAR A FREQUÊNCIA ATUAL
        // =============================================

        async function carregarFrequencia() {

            const revisao = revisaoFrequencia;

            try {

                const resposta = await fetch(

                    dados.url_historico,

                    {
                        method: 'GET',

                        credentials: 'same-origin',

                        cache: 'no-store',

                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );

                const resultado = await resposta.json();

                if (
                    !resposta.ok ||
                    !resultado.sucesso
                ) {

                    throw new Error(
                        resultado.mensagem ||
                        'Frequência indisponível.'
                    );
                }

                if (revisao === revisaoFrequencia) {

                    mostrarFrequencia(
                        resultado.frequencia
                    );
                }

            } catch {

                if (revisao !== revisaoFrequencia) {
                    return;
                }

                text(
                    'tgFrequencyText',
                    'Frequência temporariamente indisponível.'
                );

                text(
                    'tgFrequencyCount',
                    '—'
                );

                text(
                    'tgFrequencyMessage',
                    'Não foi possível consultar os registros.'
                );

                show('tgFrequencyProgress', false);

                show('tgFrequencyCard', true);
            }
        }

        // =============================================
        // SALVAR EXERCÍCIO NO BANCO
        // =============================================

        async function salvarExercicioConcluido(
            exercicio,
            registros
        ) {

            if (!exercicio.item_id) {

                throw new Error(
                    'Não foi possível identificar o exercício da ficha.'
                );
            }

            if (!s.token) {

                s.token = gerarTokenHistorico();

                salvar();
            }

            const payload = {

                ficha_id: Number(dados.ficha_id),

                item_id: Number(exercicio.item_id),

                versao: Number(dados.versao),

                token: s.token,

                tipo: s.tipo || 'individual',

                inicio: s.inicio,

                series: registros
            };

            const resposta = await fetch(

                dados.url_historico,

                {
                    method: 'POST',

                    credentials: 'same-origin',

                    headers: {

                        'Content-Type': 'application/json',

                        'Accept': 'application/json',

                        'X-CSRF-Token': dados.csrf
                    },

                    body: JSON.stringify(payload)
                }
            );

            let resultado;

            try {

                resultado = await resposta.json();

            } catch {

                throw new Error(
                    'Resposta inválida do servidor. Verifique se sua sessão está ativa e tente novamente.'
                );
            }

            if (
                !resposta.ok ||
                !resultado.sucesso ||
                !Array.isArray(
                    resultado.registro?.series
                )
            ) {

                throw new Error(
                    resultado.mensagem ||
                    'Não foi possível salvar o exercício.'
                );
            }

            show('tgFrequencyProgress', true);

            mostrarFrequencia(
                resultado.frequencia
            );

            return resultado.registro.series;
        }

        // =====================================
        // CONCLUIR SÉRIE
        // =====================================

        async function concluir() {

            if (
                s?.fase !== 'work' ||
                salvandoHistorico
            ) {

                return;
            }

            const e = atual();

            const reps = Number(
                $('tgActualReps').value
            );

            const carga = $('tgActualLoad')
                .value
                .trim()
                .slice(0, 40);

            if (
                !Number.isInteger(reps) ||
                reps < 1 ||
                reps > 999
            ) {

                text(
                    'tgError',
                    'Informe as repetições realizadas (1 a 999).'
                );

                show('tgError', true);

                return;
            }

            show('tgError', false);

            const novosRegistros = [

                ...s.registros[s.pos],

                {
                    reps,
                    carga
                }
            ];

            const ultimaSerie =
                novosRegistros.length === e.series;

            // =====================================
            // SALVAR NO BANCO APÓS A ÚLTIMA SÉRIE
            // =====================================

            if (ultimaSerie) {

                const botao = page.querySelector(
                    '[data-tg-action="complete"]'
                );

                salvandoHistorico = true;

                if (botao) {

                    botao.disabled = true;

                    botao.textContent =
                        'Salvando exercício...';
                }

                try {

                    const registrosSalvos =
                        await salvarExercicioConcluido(
                            e,
                            novosRegistros
                        );

                    s.registros[s.pos] =
                        registrosSalvos;

                } catch (erro) {

                    text(
                        'tgError',

                        erro.message ||
                        'Erro ao salvar exercício. Tente novamente.'
                    );

                    show('tgError', true);

                    // Permanece na série atual.
                    // Não perde as séries anteriores.

                    return;

                } finally {

                    salvandoHistorico = false;

                    if (botao) {

                        botao.disabled = false;

                        botao.textContent =
                            'Concluir série';
                    }
                }

            } else {

                // As séries intermediárias continuam
                // sendo armazenadas temporariamente.

                s.registros[s.pos] =
                    novosRegistros;
            }

            // =====================================
            // PRESERVAR A LÓGICA DO CRONÔMETRO
            // =====================================

            if (feitas() < e.series) {

                const sec = intervalo(e.intervalo);

                if (sec > 0) {

                    const ms = sec * 1000;

                    s.timer = {

                        duracao: ms,

                        restante: ms,

                        fim: Date.now() + ms

                    };

                    s.fase = 'rest';

                } else {

                    s.fase = 'nextSet';
                }

            } else if (

                s.pos + 1 < s.fila.length

            ) {

                s.fase = 'nextExercise';

            } else {

                s.fase = 'done';

                s.fim = Date.now();
            }

            salvar();

            render();
        }

        // =====================================
        // AÇÕES DOS BOTÕES
        // =====================================

        const acoes = {

            all: () => {

                iniciar(
                    lista.map((_, i) => i),
                    'completo'
                );
            },

            one: b => {

                const i = Number(b.dataset.index);

                if (
                    Number.isInteger(i) &&
                    lista[i]
                ) {

                    iniciar([i]);
                }
            },

            resume: () => {

                if (s) abrir();
            },

            back: voltar,

            askEnd: () => {

                show('tgEndConfirm', true);
            },

            cancelEnd: () => {

                show('tgEndConfirm', false);
            },

            end: encerrar,

            complete: concluir,

            skip: fimDescanso,

            next: () => {

                if (
                    !s ||
                    !['nextSet', 'nextExercise'].includes(s.fase)
                ) {
                    return;
                }

                if (s.fase === 'nextExercise') {

                    s.pos++;
                }

                s.fase = 'work';

                salvar();

                render();
            },

            pause: () => {

                if (s?.fase !== 'rest') return;

                const ms = restante();

                if (ms <= 0) {

                    return fimDescanso();
                }

                if (s.timer.fim === null) {

                    s.timer.fim = Date.now() + ms;

                } else {

                    s.timer.restante = ms;

                    s.timer.fim = null;
                }

                salvar();

                tick();
            },

            add: () => {

                if (s?.fase !== 'rest') return;

                const ms = restante();

                if (ms <= 0) {

                    return fimDescanso();
                }

                const extra = Math.min(
                    15000,
                    limite - s.timer.duracao
                );

                s.timer.duracao += extra;

                s.timer.restante = ms + extra;

                if (s.timer.fim !== null) {

                    s.timer.fim =
                        Date.now() + s.timer.restante;
                }

                salvar();

                tick();
            },

            print: () => {

                pausaVideos();

                window.print();
            }

        };

        // =====================================
        // GERENCIADOR CENTRAL DE CLIQUES
        // =====================================

        page.addEventListener('click', ev => {

            const b = ev.target.closest(
                'button[data-tg-action]'
            );

            if (
                !b ||
                b.disabled ||
                salvandoHistorico
            ) {
                return;
            }

            try {

                acoes[b.dataset.tgAction]?.(b);

            } catch (err) {

                text(
                    'tgBoot',
                    'Não foi possível executar esta ação: ' +
                    err.message
                );

                show('tgBoot', true);
            }
        });

        // =====================================
        // INICIALIZAÇÃO
        // =====================================

        // Mantém o indicador de frequência na listagem,
        // sem ocupar espaço durante o treino guiado.

        const frequencyCard = $('tgFrequencyCard');

        const overviewHeader = $('tgOverview')
            .querySelector('.tg-page-header');

        if (frequencyCard && overviewHeader) {

            overviewHeader.insertAdjacentElement(
                'afterend',
                frequencyCard
            );
        }

        restaurar();

        overview();

        tick();

        carregarFrequencia();

        setInterval(tick, 250);

        window.addEventListener(
            'pagehide',
            salvar
        );

        document.addEventListener(
            'visibilitychange',
            tick
        );

        show('tgBoot', false);

    })();
</script>

<?php require __DIR__ . '/../shared/footer.php'; ?>