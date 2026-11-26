<?php

if (!isset($tituloPagina)) {

    header('../../controllers/TreinoController.php' !== ''
        ? 'Location: ../../controllers/TreinoController.php'
        : 'Location: /');

    exit;
}

$tituloPagina = 'Fichas de Treino';

$cssEspecifico = BASE_URL . '/assets/css/fichatreino.css';

$esc = static function ($valor) {
    return htmlspecialchars(
        is_scalar($valor) ? (string) $valor : '',
        ENT_QUOTES,
        'UTF-8'
    );
};

$objetivos = [
    'Emagrecimento',
    'Definição',
    'Hipertrofia',
    'Ganho de Massa Muscular',
    'Condicionamento',
    'Resistência Muscular',
    'Força',
    'Mobilidade e Flexibilidade',
    'Reabilitação',
    'Saúde e Qualidade de Vida'
];

$objetivoAtual = $fichaForm['objetivo'] ?? '';

if (
    $objetivoAtual !== '' &&
    !in_array($objetivoAtual, $objetivos, true)
) {
    $objetivos[] = $objetivoAtual;
}

// Ícones SVG no padrão Lucide

$icone = static function ($nome) {

    $paths = [
        'plus' => '<path d="M12 5v14M5 12h14"/>',

        'edit' => '
            <path d="m16 3 5 5"/>
            <path d="M4 20h4L21 7l-4-4L4 16z"/>
        ',

        'trash' => '
            <path d="M3 6h18"/>
            <path d="M19 6v14H5V6"/>
            <path d="M8 6V3h8v3"/>
            <path d="M10 10v7M14 10v7"/>
        ',

        'back' => '
            <path d="m12 19-7-7 7-7"/>
            <path d="M5 12h14"/>
        ',

        'grip' => '
            <circle cx="9" cy="5" r="1"/>
            <circle cx="9" cy="12" r="1"/>
            <circle cx="9" cy="19" r="1"/>
            <circle cx="15" cy="5" r="1"/>
            <circle cx="15" cy="12" r="1"/>
            <circle cx="15" cy="19" r="1"/>
        ',

        'close' => '
            <path d="m18 6-12 12M6 6l12 12"/>
        '
    ];

    return '<svg
        xmlns="http://www.w3.org/2000/svg"
        width="18"
        height="18"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        focusable="false">'
        . ($paths[$nome] ?? '') .
        '</svg>';
};

$urlLista = $urlFicha .
    '?aluno_id=' . $alunoSelecionado;

$editando = $modoEditor &&
    (int) ($fichaForm['id'] ?? 0) > 0;

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

?>

<main class="conteudo fichatreino-page">

    <?php if (!empty($mensagemSucesso)): ?>

        <div class="ft-alert ft-alert-success" role="status">

            <?= $esc($mensagemSucesso) ?>

        </div>

    <?php endif; ?>

    <?php if (!empty($mensagemErro)): ?>

        <div class="ft-alert ft-alert-error" role="alert">

            <?= $esc($mensagemErro) ?>

        </div>

    <?php endif; ?>

    <?php if (!$modoEditor): ?>

        <!-- LISTAGEM DE FICHAS -->

        <header class="ft-header">

            <h1>Fichas de Treino</h1>

            <?php if ($alunoSelecionado > 0): ?>

                <a
                    href="<?= $esc($urlLista . '&novo=1') ?>"
                    class="ft-btn ft-btn-primary">

                    <?= $icone('plus') ?>

                    Nova ficha

                </a>

            <?php endif; ?>

        </header>

        <form
            method="GET"
            action="<?= $esc($urlFicha) ?>"
            class="ft-filter">

            <div class="ft-field">

                <label for="filtroAluno">Aluno</label>

                <select id="filtroAluno" name="aluno_id">

                    <option value="">
                        Selecione um aluno
                    </option>

                    <?php foreach ($alunos as $aluno): ?>

                        <option
                            value="<?= (int) $aluno['id'] ?>"
                            <?= $alunoSelecionado ===
                                (int) $aluno['id']
                                ? 'selected'
                                : '' ?>>

                            <?= $esc($aluno['nome']) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <button type="submit" class="ft-btn ft-btn-secondary">

                Consultar

            </button>

        </form>

        <section class="ft-panel">

            <?php if ($alunoSelecionado <= 0): ?>

                <div class="ft-empty">

                    Selecione um aluno para consultar suas fichas.

                </div>

            <?php elseif (empty($fichas)): ?>

                <div class="ft-empty">

                    Nenhuma ficha cadastrada para este aluno.

                    <a
                        href="<?= $esc($urlLista . '&novo=1') ?>"
                        class="ft-btn ft-btn-primary">

                        <?= $icone('plus') ?>

                        Criar ficha

                    </a>

                </div>

            <?php else: ?>

                <div class="ft-table-wrap">

                    <table class="ft-table">

                        <thead>
                            <tr>
                                <th>Nome da ficha</th>
                                <th>Objetivo</th>
                                <th>Professor</th>
                                <th>Exercícios</th>
                                <th>Criação</th>
                                <th class="ft-actions-column">Ações</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($fichas as $ficha): ?>

                                <?php
                                $dataFicha = strtotime(
                                    $ficha['criada_em'] ?? ''
                                );
                                ?>

                                <tr>

                                    <td class="ft-name">

                                        <?= $esc($ficha['nome_ficha']) ?>

                                    </td>

                                    <td>

                                        <?= $esc($ficha['objetivo']) ?>

                                    </td>

                                    <td>

                                        <?= $esc($ficha['nome_professor']) ?>

                                    </td>

                                    <td>

                                        <?= (int) $ficha['total_exercicios'] ?>

                                    </td>

                                    <td>

                                        <?= $dataFicha
                                            ? date('d/m/Y', $dataFicha)
                                            : '—' ?>

                                    </td>

                                    <td>

                                        <div class="ft-row-actions">

                                            <a
                                                href="<?= $esc(
                                                    $urlLista .
                                                    '&editar=' .
                                                    (int) $ficha['id']
                                                ) ?>"
                                                class="ft-icon-btn"
                                                title="Editar ficha"
                                                aria-label="Editar ficha">

                                                <?= $icone('edit') ?>

                                            </a>

                                            <button
                                                type="button"
                                                class="ft-icon-btn ft-icon-danger"
                                                data-excluir-id="<?= (int) $ficha['id'] ?>"
                                                data-excluir-nome="<?= $esc($ficha['nome_ficha']) ?>"
                                                title="Excluir ficha"
                                                aria-label="Excluir ficha">

                                                <?= $icone('trash') ?>

                                            </button>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </section>

        <!-- CONFIRMAÇÃO DE EXCLUSÃO -->

        <dialog id="dialogExcluir" class="ft-dialog">

            <form method="POST" action="<?= $esc($urlFicha) ?>">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= $esc($csrfTreino) ?>">

                <input type="hidden" name="acao" value="excluir">

                <input
                    type="hidden"
                    name="aluno"
                    value="<?= (int) $alunoSelecionado ?>">

                <input
                    type="hidden"
                    name="ficha_origem_id"
                    id="fichaExcluir">

                <div class="ft-dialog-header">

                    <h2>Excluir ficha</h2>

                    <button
                        type="button"
                        id="fecharDialog"
                        class="ft-icon-btn"
                        aria-label="Fechar">

                        <?= $icone('close') ?>

                    </button>

                </div>

                <p>
                    Deseja excluir a ficha
                    <strong id="nomeFichaExcluir"></strong>?
                </p>

                <p class="ft-muted">

                    Os exercícios continuarão disponíveis na biblioteca.

                    Fichas vinculadas a registros de frequência
                    não poderão ser excluídas permanentemente.

                </p>

                <div class="ft-dialog-actions">

                    <button
                        type="button"
                        id="cancelarExclusao"
                        class="ft-btn ft-btn-secondary">

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="ft-btn ft-btn-danger">

                        <?= $icone('trash') ?>

                        Excluir ficha

                    </button>

                </div>

            </form>

        </dialog>

    <?php else: ?>

        <!-- EDITOR DE FICHAS -->

        <header class="ft-header">

            <div class="ft-header-title">

                <a
                    href="<?= $esc($urlLista) ?>"
                    class="ft-icon-btn"
                    title="Voltar"
                    aria-label="Voltar para fichas">

                    <?= $icone('back') ?>

                </a>

                <div>

                    <h1>
                        <?= $editando ? 'Editar ficha' : 'Nova ficha' ?>
                    </h1>

                    <p><?= $esc($nomeAlunoSelecionado) ?></p>

                </div>

            </div>

        </header>

        <form
            method="POST"
            action="<?= $esc($urlFicha) ?>"
            id="formFicha">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= $esc($csrfTreino) ?>">

            <input type="hidden" name="acao" value="salvar">

            <input
                type="hidden"
                name="aluno"
                value="<?= (int) $alunoSelecionado ?>">

            <input
                type="hidden"
                name="ficha_origem_id"
                value="<?= (int) ($fichaForm['id'] ?? 0) ?>">

            <section class="ft-panel ft-editor-info">

                <div class="ft-form-grid">

                    <div class="ft-field ft-field-full">

                        <label for="nomeFicha">
                            Nome da ficha
                        </label>

                        <input
                            type="text"
                            id="nomeFicha"
                            name="nome_ficha"
                            maxlength="100"
                            placeholder="Ex.: Treino A - Peito e tríceps"
                            value="<?= $esc($fichaForm['nome_ficha'] ?? '') ?>"
                            required>

                    </div>

                    <div class="ft-field">

                        <label for="professor">
                            Professor responsável
                        </label>

                        <select
                            id="professor"
                            name="professor_id"
                            required>

                            <option value="">
                                Selecione
                            </option>

                            <?php foreach ($professores as $professor): ?>

                                <option
                                    value="<?= (int) $professor['id'] ?>"
                                    <?= (int) ($fichaForm['professor_id'] ?? 0)
                                        === (int) $professor['id']
                                        ? 'selected'
                                        : '' ?>>

                                    <?= $esc($professor['name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="ft-field">

                        <label for="objetivo">Objetivo</label>

                        <select
                            id="objetivo"
                            name="objetivo"
                            required>

                            <option value="">
                                Selecione
                            </option>

                            <?php foreach ($objetivos as $obj): ?>

                                <option
                                    value="<?= $esc($obj) ?>"
                                    <?= $objetivoAtual === $obj
                                        ? 'selected'
                                        : '' ?>>

                                    <?= $esc($obj) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

            </section>

            <section class="ft-panel ft-editor-exercises">

                <div class="ft-section-header">

                    <h2>Exercícios</h2>

                    <span id="contadorExercicios" class="ft-muted">

                        0 exercícios

                    </span>

                </div>

                <div class="ft-add">

                    <div class="ft-field">

                        <label for="exercicio">
                            Adicionar exercício
                        </label>

                        <select id="exercicio">

                            <option value="">
                                Selecione um exercício
                            </option>

                            <?php foreach ($exercicios as $exercicio): ?>

                                <option value="<?= (int) $exercicio['id'] ?>">

                                    <?= $esc($exercicio['nome']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <button
                        type="button"
                        id="btnAdicionar"
                        class="ft-btn ft-btn-secondary">

                        <?= $icone('plus') ?>

                        Adicionar

                    </button>

                </div>

                <p
                    id="mensagemExercicio"
                    class="ft-field-error"
                    role="alert"
                    hidden>
                </p>

                <div class="ft-table-wrap">

                    <table class="ft-table ft-edit-table">

                        <thead>

                            <tr>

                                <th class="ft-grip-column"></th>
                                <th>Exercício</th>
                                <th>Séries</th>
                                <th>Repetições</th>
                                <th>Carga (kg)</th>
                                <th>Intervalo (s)</th>
                                <th>Ação</th>

                            </tr>

                        </thead>

                        <tbody id="corpoExercicios"></tbody>

                    </table>

                    <div id="fichaEstadoVazio" class="ft-empty">

                        Nenhum exercício adicionado.

                    </div>

                </div>

            </section>

            <div class="ft-form-actions">

                <a
                    href="<?= $esc($urlLista) ?>"
                    class="ft-btn ft-btn-secondary">

                    Cancelar

                </a>

                <button
                    type="submit"
                    id="btnSalvarFicha"
                    class="ft-btn ft-btn-primary">

                    <?= $editando
                        ? 'Salvar alterações'
                        : 'Salvar ficha' ?>

                </button>

            </div>

        </form>

        <!-- MODELO DE LINHA DOS EXERCÍCIOS -->

        <template id="templateExercicio">

            <tr>

                <td class="ft-grip-column">

                    <button
                        type="button"
                        class="ft-grip"
                        title="Arraste para ordenar ou use as setas do teclado"
                        aria-label="Reordenar exercício">

                        <?= $icone('grip') ?>

                    </button>

                </td>

                <td class="ft-exercise-name">

                    <input type="hidden" data-field="item_id">

                    <select
                        data-field="exercicio_id"
                        aria-label="Exercício"
                        required>

                        <option value="">Selecione</option>

                        <?php foreach ($exercicios as $exercicio): ?>

                            <option value="<?= (int) $exercicio['id'] ?>">

                                <?= $esc($exercicio['nome']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </td>

                <td>

                    <input
                        type="number"
                        data-field="series"
                        min="1"
                        max="2147483647"
                        step="1"
                        aria-label="Séries"
                        required>

                </td>

                <td>

                    <input
                        type="text"
                        data-field="repeticoes"
                        maxlength="30"
                        aria-label="Repetições"
                        required>

                </td>

                <td>

                    <input
                        type="text"
                        data-field="carga"
                        maxlength="20"
                        placeholder="Opcional"
                        aria-label="Carga">

                </td>

                <td>

                    <input
                        type="number"
                        data-field="intervalo"
                        min="0"
                        max="1000"
                        step="1"
                        aria-label="Intervalo em segundos">

                </td>

                <td>

                    <button
                        type="button"
                        class="ft-icon-btn ft-icon-danger ft-remove"
                        title="Remover exercício"
                        aria-label="Remover exercício">

                        <?= $icone('trash') ?>

                    </button>

                </td>

            </tr>

        </template>

    <?php endif; ?>

</main>

<script>

document.addEventListener('DOMContentLoaded', function () {

    // CONFIRMAÇÃO DE EXCLUSÃO

    const dialog = document.getElementById('dialogExcluir');

    if (dialog) {

        const campoId = document.getElementById('fichaExcluir');

        const nome = document.getElementById('nomeFichaExcluir');

        document.querySelectorAll('[data-excluir-id]')
            .forEach(botao => {

                botao.addEventListener('click', function () {

                    campoId.value = this.dataset.excluirId;

                    nome.textContent = this.dataset.excluirNome;

                    dialog.showModal();
                });
            });

        document.getElementById('fecharDialog')
            .addEventListener('click', () => dialog.close());

        document.getElementById('cancelarExclusao')
            .addEventListener('click', () => dialog.close());

        return;
    }

    // EDITOR DE FICHAS

    const form = document.getElementById('formFicha');

    if (!form) {
        return;
    }

    const corpo = document.getElementById('corpoExercicios');

    const template = document.getElementById('templateExercicio');

    const exercicio = document.getElementById('exercicio');

    const contador = document.getElementById('contadorExercicios');

    const vazio = document.getElementById('fichaEstadoVazio');

    const mensagem = document.getElementById('mensagemExercicio');

    const btnSalvar = document.getElementById('btnSalvarFicha');

    const itensIniciais = <?= json_encode(
        array_values(
            is_array($fichaForm['itens'] ?? null)
                ? $fichaForm['itens']
                : []
        ),
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_INVALID_UTF8_SUBSTITUTE
    ) ?>;

    function mostrarMensagem(texto = '') {

        mensagem.textContent = texto;

        mensagem.hidden = !texto;
    }

    function atualizarLinhas() {

        const linhas = Array.from(corpo.rows);

        linhas.forEach((linha, indice) => {

            linha.querySelectorAll('[data-field]')
                .forEach(campo => {

                    campo.name =
                        `itens[${indice}][${campo.dataset.field}]`;
                });
        });

        contador.textContent =
            linhas.length +
            (linhas.length === 1 ? ' exercício' : ' exercícios');

        vazio.hidden = linhas.length > 0;

        btnSalvar.disabled = linhas.length === 0;
    }

    // Converter 60s, 90s e outros valores antigos

    function converterIntervalo(valor) {

        const texto = String(valor ?? '').trim();

        if (!texto) {
            return '';
        }

        const resultado = texto.match(/^(\d+)\s*s?$/i);

        return resultado ? resultado[1] : null;
    }

    // Ordenação pelo ícone de arrastar

    function configurarOrdenacao(linha, grip) {

        let arrastando = false;

        grip.addEventListener('pointerdown', evento => {

            if (evento.button !== 0) {
                return;
            }

            evento.preventDefault();

            grip.focus();

            arrastando = true;

            grip.setPointerCapture(evento.pointerId);

            linha.classList.add('ft-dragging');
        });

        grip.addEventListener('pointermove', evento => {

            if (!arrastando) {
                return;
            }

            const elemento = document.elementFromPoint(
                evento.clientX,
                evento.clientY
            );

            const alvo = elemento?.closest('tr');

            if (
                !alvo ||
                alvo === linha ||
                alvo.parentElement !== corpo
            ) {
                return;
            }

            const posicao = alvo.getBoundingClientRect();

            const depois =
                evento.clientY >
                posicao.top + posicao.height / 2;

            corpo.insertBefore(
                linha,
                depois ? alvo.nextElementSibling : alvo
            );

            atualizarLinhas();
        });

        function finalizar() {

            arrastando = false;

            linha.classList.remove('ft-dragging');

            atualizarLinhas();
        }

        grip.addEventListener('pointerup', finalizar);
        grip.addEventListener('pointercancel', finalizar);
        grip.addEventListener('lostpointercapture', finalizar);

        // Alternativa de ordenação pelo teclado

        grip.addEventListener('keydown', evento => {

            if (evento.key === 'ArrowUp') {

                evento.preventDefault();

                const anterior = linha.previousElementSibling;

                if (anterior) {

                    corpo.insertBefore(linha, anterior);
                }

            } else if (evento.key === 'ArrowDown') {

                evento.preventDefault();

                const proxima = linha.nextElementSibling;

                if (proxima) {

                    corpo.insertBefore(proxima, linha);
                }

            } else {

                return;
            }

            atualizarLinhas();

            grip.focus();
        });
    }

    // Adicionar ou preencher exercício

    function adicionarLinha(item) {

        if (!item || typeof item !== 'object') {
            return;
        }

        const linha =
            template.content.firstElementChild.cloneNode(true);

        const campo = nome =>
            linha.querySelector(`[data-field="${nome}"]`);

        campo('item_id').value = item.item_id ?? 0;

        const selectExercicio = campo('exercicio_id');

        const idExercicio = String(item.exercicio_id ?? '');

        const disponivel = Array.from(selectExercicio.options)
            .some(option =>
                option.value !== '' &&
                option.value === idExercicio
            );

        if (!disponivel && idExercicio !== '') {

            const option = document.createElement('option');

            option.value = idExercicio;

            option.textContent =
                (item.nome_exercicio || 'Exercício') +
                ' (indisponível)';

            selectExercicio.appendChild(option);
        }

        selectExercicio.value = idExercicio;

        if (!disponivel) {

            selectExercicio.setCustomValidity(
                'Selecione um exercício disponível.'
            );
        }

        selectExercicio.addEventListener('change', function () {

            const valido = Array.from(exercicio.options)
                .some(option =>
                    option.value !== '' &&
                    option.value === this.value
                );

            this.setCustomValidity(
                valido ? '' : 'Selecione um exercício disponível.'
            );
        });

        campo('series').value = item.series ?? 3;

        campo('repeticoes').value = item.repeticoes ?? 12;

        campo('carga').value = item.carga ?? '';

        const intervalo = converterIntervalo(item.intervalo);

        campo('intervalo').value = intervalo ?? '';

        if (intervalo === null) {

            campo('intervalo').setCustomValidity(
                'Revise o intervalo em segundos.'
            );

            campo('intervalo').placeholder = 'Revisar';
        }

        campo('intervalo').addEventListener('input', function () {

            const valor = Number(this.value);

            this.setCustomValidity(
                this.value !== '' &&
                (
                    !Number.isInteger(valor) ||
                    valor < 0 ||
                    valor > 1000
                )
                    ? 'Informe um intervalo de 0 a 1000 segundos.'
                    : ''
            );
        });

        const repeticoes = campo('repeticoes');

        function validarRepeticoes() {

            const valor = repeticoes.value.trim();

            const valido =
                /^[1-9]\d*(?:\s*(?:a|-)\s*[1-9]\d*)?$/i
                    .test(valor);

            repeticoes.setCustomValidity(
                valor === '' || valido
                    ? ''
                    : 'Informe repetições válidas.'
            );
        }

        repeticoes.addEventListener('input', validarRepeticoes);

        validarRepeticoes();

        linha.querySelector('.ft-remove')
            .addEventListener('click', function () {

                linha.remove();

                atualizarLinhas();
            });

        configurarOrdenacao(
            linha,
            linha.querySelector('.ft-grip')
        );

        corpo.appendChild(linha);

        atualizarLinhas();
    }

    document.getElementById('btnAdicionar')
        .addEventListener('click', function () {

            if (!exercicio.value) {

                mostrarMensagem('Selecione um exercício.');

                exercicio.focus();

                return;
            }

            mostrarMensagem();

            adicionarLinha({
                item_id: 0,
                exercicio_id: exercicio.value,
                series: 3,
                repeticoes: '12',
                carga: '',
                intervalo: '60'
            });

            exercicio.value = '';
        });

    exercicio.addEventListener('change', () => mostrarMensagem());

    // Carregar dados existentes

    if (Array.isArray(itensIniciais)) {

        itensIniciais.forEach(adicionarLinha);
    }

    atualizarLinhas();

    // Salvar formulário

    form.addEventListener('submit', evento => {

        atualizarLinhas();

        if (corpo.rows.length === 0) {

            evento.preventDefault();

            mostrarMensagem(
                'Adicione pelo menos um exercício.'
            );

            return;
        }

        if (!form.checkValidity()) {

            evento.preventDefault();

            form.reportValidity();

            return;
        }

        corpo.querySelectorAll('[data-field="intervalo"]')
            .forEach(input => {

                if (input.value !== '') {
                    input.value = String(Number(input.value));
                }
            });

        btnSalvar.disabled = true;

        btnSalvar.textContent = 'Salvando...';
    });

});

</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>