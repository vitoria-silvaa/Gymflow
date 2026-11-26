<?php
// ================================================
// GYMFLOW - AVALIAÇÕES FÍSICAS
// app/views/avaliacoes_fisicas/index.php
// ================================================

// VARIÁVEIS RECEBIDAS DO CONTROLLER

/** @var array $alunosAvaliacao */
/** @var array $camposAvaliacao */
/** @var string $csrfAvaliacaoFisica */
/** @var string $urlAvaliacaoFisica */
/** @var string $hoje */
/** @var int $alunoSelecionadoId */
/** @var array|null $alunoSelecionado */
/** @var array $historicoAvaliacoes */
/** @var array $rascunhoAvaliacao */
/** @var array|null $mensagemAvaliacaoFisica */

// ================================================
// 1. VALIDAR ACESSO À VIEW
// ================================================

if (!isset(
    $alunosAvaliacao,
    $camposAvaliacao,
    $csrfAvaliacaoFisica,
    $urlAvaliacaoFisica,
    $hoje
)) {
    http_response_code(403);
    exit('Acesso negado.');
}

// ================================================
// 2. INICIALIZAR VARIÁVEIS OPCIONAIS
// ================================================

$alunoSelecionadoId = (int) (
    $alunoSelecionadoId ?? 0
);

$alunoSelecionado =
    $alunoSelecionado ?? null;

$historicoAvaliacoes =
    $historicoAvaliacoes ?? [];

$rascunhoAvaliacao =
    $rascunhoAvaliacao ?? [];

$mensagemAvaliacaoFisica =
    $mensagemAvaliacaoFisica ?? null;

// ================================================
// 3. CONFIGURAÇÕES DA PÁGINA
// ================================================

$tituloPagina = 'Avaliações Físicas';

$cssEspecifico = BASE_URL .
    '/assets/css/avaliacao-fisica.css';

$escAF = static function ($valor): string {

    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};

require __DIR__ . '/../shared/header.php';
require __DIR__ . '/../shared/sidebar.php';

?>

<main class="conteudo avaliacao-fisica-page">

    <!-- CABEÇALHO -->

    <header class="af-header">

        <span class="af-etiqueta">
            GESTÃO DE ALUNOS
        </span>

        <h1>Avaliações Físicas</h1>

        <p>
            Registre e acompanhe as avaliações
            físicas dos seus alunos.
        </p>

    </header>

    <!-- MENSAGENS -->

    <?php if ($mensagemAvaliacaoFisica !== null): ?>

        <?php
        $sucessoAF =
            ($mensagemAvaliacaoFisica['tipo'] ?? '')
            === 'sucesso';
        ?>

        <div
            class="af-alerta af-alerta-<?= $sucessoAF
                                            ? 'sucesso'
                                            : 'erro' ?>"
            role="<?= $sucessoAF
                        ? 'status'
                        : 'alert' ?>">

            <i data-lucide="<?= $sucessoAF
                                ? 'circle-check'
                                : 'circle-alert' ?>"></i>

            <?= $escAF(
                $mensagemAvaliacaoFisica['texto'] ?? ''
            ) ?>

        </div>

    <?php endif; ?>

    <!-- SELEÇÃO DO ALUNO -->

    <section class="af-card">

        <div class="af-card-header">

            <div class="af-icone">
                <i data-lucide="users"></i>
            </div>

            <div>

                <h2>Selecionar aluno</h2>

                <p>
                    Escolha um aluno vinculado a você.
                </p>

            </div>

        </div>

        <?php if (empty($alunosAvaliacao)): ?>

            <div class="af-vazio">

                <i data-lucide="user-round"></i>

                <h3>Nenhum aluno vinculado</h3>

                <p>
                    Solicite à administração ou à recepção
                    o vínculo de alunos e filiais ao seu cadastro.
                </p>

            </div>

        <?php else: ?>

            <form
                method="GET"
                action="<?= $escAF($urlAvaliacaoFisica) ?>"
                class="af-selecao">

                <div class="af-campo">

                    <label for="aluno_id">
                        Aluno
                    </label>

                    <select
                        name="aluno_id"
                        id="aluno_id"
                        required>

                        <option value="">
                            Selecione um aluno
                        </option>

                        <?php foreach ($alunosAvaliacao as $alunoAF): ?>

                            <option
                                value="<?= (int) $alunoAF['id'] ?>"
                                <?= (int) $alunoAF['id'] ===
                                    $alunoSelecionadoId
                                    ? 'selected'
                                    : '' ?>>

                                <?= $escAF($alunoAF['nome']) ?>

                                —

                                <?= $escAF($alunoAF['nome_filial']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <button
                    type="submit"
                    class="af-btn">

                    <i data-lucide="search"></i>

                    Consultar aluno

                </button>

            </form>

        <?php endif; ?>

    </section>

    <?php if ($alunoSelecionado !== null): ?>

        <!-- IDENTIFICAÇÃO DO ALUNO -->

        <section class="af-card af-aluno">

            <div class="af-avatar">

                <i data-lucide="user-round"></i>

            </div>

            <div class="af-aluno-info">

                <h2>
                    <?= $escAF($alunoSelecionado['nome']) ?>
                </h2>

                <p>
                    <?= $escAF($alunoSelecionado['nome_filial']) ?>
                </p>

            </div>

            <div class="af-total">

                <strong>
                    <?= count($historicoAvaliacoes) ?>
                </strong>

                <span>Avaliações registradas</span>

            </div>

        </section>

        <!-- NOVA AVALIAÇÃO -->

        <section class="af-card">

            <div class="af-card-header">

                <div class="af-icone">

                    <i data-lucide="clipboard-plus"></i>

                </div>

                <div>

                    <h2>Nova avaliação física</h2>

                    <p>
                        Preencha os valores obtidos na avaliação.
                    </p>

                </div>

            </div>

            <form
                method="POST"
                action="<?= $escAF($urlAvaliacaoFisica) ?>"
                class="af-form">

                <!-- SEGURANÇA -->

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= $escAF($csrfAvaliacaoFisica) ?>">

                <input
                    type="hidden"
                    name="aluno_id"
                    value="<?= $alunoSelecionadoId ?>">

                <!-- DATA -->

                <div class="af-campo af-campo-data">

                    <label for="data_avaliacao">
                        Data da avaliação *
                    </label>

                    <input
                        type="date"
                        name="data"
                        id="data_avaliacao"
                        max="<?= $escAF($hoje) ?>"
                        value="<?= $escAF(
                                    $rascunhoAvaliacao['data'] ?? $hoje
                                ) ?>"
                        required>

                </div>

                <!-- COMPOSIÇÃO E MEDIDAS CORPORAIS -->

                <?php foreach ([true, false] as $obrigatoriosAF): ?>

                    <div class="af-secao-titulo">

                        <i data-lucide="<?= $obrigatoriosAF
                                            ? 'activity'
                                            : 'ruler' ?>"></i>

                        <h3>

                            <?= $obrigatoriosAF
                                ? 'Composição corporal'
                                : 'Medidas corporais' ?>

                        </h3>

                    </div>

                    <div class="af-grid">

                        <?php foreach ($camposAvaliacao as $campoAF => $configAF): ?>

                            <?php

                            if (
                                $configAF['obrigatorio']
                                !== $obrigatoriosAF
                            ) {
                                continue;
                            }

                            $valorAF = str_replace(
                                ',',
                                '.',
                                (string) (
                                    $rascunhoAvaliacao[$campoAF] ?? ''
                                )
                            );

                            ?>

                            <div class="af-campo">

                                <label for="af_<?= $escAF($campoAF) ?>">

                                    <?= $escAF($configAF['rotulo']) ?>

                                    <?= $obrigatoriosAF
                                        ? ' *'
                                        : '' ?>

                                </label>

                                <input
                                    type="number"
                                    inputmode="decimal"

                                    name="<?= $escAF($campoAF) ?>"

                                    id="af_<?= $escAF($campoAF) ?>"

                                    min="<?= $escAF($configAF['min']) ?>"

                                    max="<?= $escAF($configAF['max']) ?>"

                                    step="<?= $configAF['casas'] === 2
                                                ? '0.01'
                                                : '0.1' ?>"

                                    value="<?= $escAF($valorAF) ?>"

                                    placeholder="<?= $campoAF === 'altura'
                                                        ? '1.68'
                                                        : ($obrigatoriosAF
                                                            ? '0.00'
                                                            : '0.0') ?>"

                                    <?= $obrigatoriosAF
                                        ? 'required'
                                        : '' ?>>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endforeach; ?>

                <!-- AVISO -->

                <p class="af-aviso">

                    <i data-lucide="info"></i>

                    Os campos com asterisco são obrigatórios.
                    Informe somente valores efetivamente medidos.

                </p>

                <!-- SALVAR -->

                <div class="af-acoes">

                    <button
                        type="submit"
                        class="af-btn">

                        <i data-lucide="save"></i>

                        Registrar avaliação

                    </button>

                </div>

            </form>

        </section>

        <!-- HISTÓRICO DE AVALIAÇÕES -->

        <section class="af-card">

            <div class="af-card-header">

                <div class="af-icone">

                    <i data-lucide="history"></i>

                </div>

                <div>

                    <h2>Avaliações anteriores</h2>

                    <p>
                        Consulte as avaliações já registradas.
                    </p>

                </div>

            </div>

            <?php if (empty($historicoAvaliacoes)): ?>

                <div class="af-vazio">

                    <i data-lucide="clipboard-list"></i>

                    <h3>Nenhuma avaliação registrada</h3>

                    <p>
                        Utilize o formulário acima
                        para registrar a primeira avaliação.
                    </p>

                </div>

            <?php else: ?>

                <div class="af-historico">

                    <?php foreach ($historicoAvaliacoes as $avaliacaoAF): ?>

                        <details class="af-registro">

                            <summary>

                                <div class="af-registro-data">

                                    <i data-lucide="calendar-days"></i>

                                    <div>

                                        <strong>

                                            <?= date(
                                                'd/m/Y',
                                                strtotime($avaliacaoAF['data'])
                                            ) ?>

                                        </strong>

                                        <span>

                                            Avaliação física
                                            #<?= (int) $avaliacaoAF['id'] ?>

                                        </span>

                                    </div>

                                </div>

                                <div class="af-registro-resumo">

                                    <span>

                                        <?= number_format(
                                            (float) $avaliacaoAF['peso'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?>

                                        kg

                                    </span>

                                    <i data-lucide="chevron-down"></i>

                                </div>

                            </summary>

                            <!-- MEDIDAS DA AVALIAÇÃO -->

                            <div class="af-registro-conteudo">

                                <div class="af-grid">

                                    <?php foreach ($camposAvaliacao as $campoAF => $configAF): ?>

                                        <?php

                                        $medidaAF =
                                            $avaliacaoAF[$campoAF] ?? null;

                                        ?>

                                        <div class="af-medida">

                                            <span>

                                                <?= $escAF(
                                                    $configAF['rotulo']
                                                ) ?>

                                            </span>

                                            <strong>

                                                <?= $medidaAF !== null
                                                    ? number_format(
                                                        (float) $medidaAF,
                                                        $configAF['casas'],
                                                        ',',
                                                        '.'
                                                    )
                                                    : 'Não informado' ?>

                                            </strong>

                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            </div>

                        </details>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    <?php endif; ?>

</main>

<?php require __DIR__ . '/../shared/footer.php'; ?>