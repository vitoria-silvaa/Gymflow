<?php
// app/views/treinos/index.php

if (!isset($tituloPagina)) {
    header(
        "Location: " . BASE_URL .
            "/app/controllers/TreinoController.php"
    );
    exit;
}

/** @var array $alunos */
/** @var array $professores */
/** @var array $exercicios */

$tituloPagina = "Fichas de Treino";

// O header já acrescenta a versão do CSS.
$cssEspecifico = BASE_URL . '/assets/css/fichatreino.css';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main class="conteudo fichatreino-page">

    <!-- CABEÇALHO -->

    <header class="fichatreino-header">
        <div>
            <span class="fichatreino-etiqueta">
                GESTÃO DE TREINOS
            </span>

            <h1>Fichas de Treino</h1>

            <p>
                Monte e versione os treinos dos alunos,
                de forma organizada.
            </p>
        </div>
    </header>

    <!-- MENSAGENS -->

    <?php if (!empty($mensagemSucesso)): ?>
        <div class="ficha-alerta ficha-alerta-sucesso"
            role="status">
            <?= htmlspecialchars(
                $mensagemSucesso,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensagemErro)): ?>
        <div class="ficha-alerta ficha-alerta-erro"
            role="alert">
            <?= htmlspecialchars(
                $mensagemErro,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>
    <?php endif; ?>

    <!-- FORMULÁRIO -->

    <form method="POST" id="formFicha">

        <!-- CONFIGURAÇÃO DA FICHA -->

        <section class="ficha-card">

            <div class="ficha-card-header">
                <div>
                    <h2>Informações da ficha</h2>
                    <p>
                        Defina o responsável, o aluno
                        e o objetivo do treinamento.
                    </p>
                </div>
            </div>

            <div class="ficha-form-grid">

                <div class="ficha-campo">
                    <label for="professor">
                        Professor responsável
                        <span class="ficha-obrigatorio">*</span>
                    </label>

                    <select
                        id="professor"
                        name="professor_id"
                        required>
                        <option value="">
                            Selecione um professor
                        </option>

                        <?php foreach ($professores as $professor): ?>
                            <option value="<?= (int) $professor['id'] ?>">
                                <?= htmlspecialchars(
                                    $professor['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ficha-campo">
                    <label for="aluno">
                        Aluno
                        <span class="ficha-obrigatorio">*</span>
                    </label>

                    <select
                        id="aluno"
                        name="aluno"
                        required>
                        <option value="">
                            Selecione um aluno
                        </option>

                        <?php foreach ($alunos as $aluno): ?>
                            <option value="<?= (int) $aluno['id'] ?>">
                                <?= htmlspecialchars(
                                    $aluno['nome'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ficha-campo ficha-campo-largo">
                    <label for="objetivo">
                        Objetivo do treinamento
                        <span class="ficha-obrigatorio">*</span>
                    </label>

                    <select
                        id="objetivo"
                        name="objetivo"
                        required>
                        <option value="">
                            Selecione um objetivo
                        </option>

                        <?php
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
                        ?>

                        <?php foreach ($objetivos as $objetivo): ?>
                            <option value="<?= htmlspecialchars(
                                                $objetivo,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>">
                                <?= htmlspecialchars(
                                    $objetivo,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
        </section>

        <!-- EXERCÍCIOS -->

        <section class="ficha-card">

            <div class="ficha-card-header">
                <div>
                    <h2>Exercícios da ficha</h2>
                    <p>
                        Escolha os exercícios e defina
                        séries, repetições e carga.
                    </p>
                </div>

                <span id="contadorExercicios"
                    class="ficha-contador">
                    0 exercícios
                </span>
            </div>

            <div class="ficha-adicionar">

                <div class="ficha-campo">
                    <label for="exercicio">
                        Adicionar exercício
                    </label>

                    <select id="exercicio" name="exercicio">
                        <option value="">
                            Selecione um exercício
                        </option>

                        <?php foreach ($exercicios as $exercicio): ?>
                            <option value="<?= (int) $exercicio['id'] ?>">
                                <?= htmlspecialchars(
                                    $exercicio['nome'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button
                    type="button"
                    id="btnAdicionar"
                    class="ficha-btn ficha-btn-secundario">
                    + Adicionar
                </button>

            </div>

            <p
                id="mensagemExercicio"
                class="ficha-mensagem-campo"
                role="alert"
                hidden></p>

            <div class="ficha-tabela-container">

                <table class="ficha-tabela"
                    id="tabelaExercicios">

                    <thead>
                        <tr>
                            <th>Exercício</th>
                            <th>Séries</th>
                            <th>Repetições</th>
                            <th>Carga (kg)</th>
                            <th>Intervalo</th>
                            <th>Ação</th>
                        </tr>
                    </thead>

                    <tbody id="corpoExercicios">
                        <!-- Inserção dinâmica via JavaScript -->
                    </tbody>
                </table>

                <div
                    id="fichaEstadoVazio"
                    class="ficha-estado-vazio">
                    <strong>Nenhum exercício adicionado</strong>
                    <p>
                        Selecione um exercício acima
                        e clique em Adicionar.
                    </p>
                </div>

            </div>

        </section>

        <!-- SALVAR -->

        <section class="ficha-acoes">

            <div class="ficha-acoes-info">
                <strong>Pronto para salvar?</strong>

                <p id="mensagemSalvar">
                    Preencha os campos obrigatórios
                    e adicione pelo menos um exercício.
                </p>
            </div>

            <button
                type="submit"
                id="btnSalvarFicha"
                class="ficha-btn ficha-btn-primario"
                disabled>
                Salvar ficha
            </button>

        </section>

    </form>

    <!-- HISTÓRICO -->

    <section
        id="historicoTreinos"
        class="ficha-card ficha-historico-secao"
        hidden>
        <div class="ficha-card-header">
            <div>
                <h2>Histórico de Treinos</h2>
                <p>
                    Consulte as versões anteriores
                    das fichas do aluno selecionado.
                </p>
            </div>
        </div>

        <div id="listaHistorico"></div>
    </section>

</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const form = document.getElementById('formFicha');
        const professor = document.getElementById('professor');
        const aluno = document.getElementById('aluno');
        const objetivo = document.getElementById('objetivo');
        const exercicio = document.getElementById('exercicio');

        const btnAdicionar = document.getElementById('btnAdicionar');
        const btnSalvar = document.getElementById('btnSalvarFicha');

        const corpoExercicios =
            document.getElementById('corpoExercicios');

        const contador =
            document.getElementById('contadorExercicios');

        const estadoVazio =
            document.getElementById('fichaEstadoVazio');

        const mensagemSalvar =
            document.getElementById('mensagemSalvar');

        const mensagemExercicio =
            document.getElementById('mensagemExercicio');

        const historico =
            document.getElementById('historicoTreinos');

        const listaHistorico =
            document.getElementById('listaHistorico');

        let proximoIndice = 0;
        let requisicaoHistorico = null;

        // Evita inserir texto retornado pelo banco
        // como HTML sem tratamento.
        function escaparHTML(valor) {
            return String(valor ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function atualizarBotaoSalvar() {

            const quantidade =
                corpoExercicios.querySelectorAll('tr').length;

            const fichaValida =
                professor.value !== '' &&
                aluno.value !== '' &&
                objetivo.value !== '' &&
                quantidade > 0;

            btnSalvar.disabled = !fichaValida;

            contador.textContent =
                quantidade + (
                    quantidade === 1 ?
                    ' exercício' :
                    ' exercícios'
                );

            estadoVazio.hidden = quantidade > 0;

            mensagemSalvar.textContent = fichaValida ?
                'Confira os dados antes de salvar a ficha.' :
                'Preencha os campos obrigatórios e adicione pelo menos um exercício.';
        }

        function mostrarMensagemExercicio(texto) {
            mensagemExercicio.textContent = texto;
            mensagemExercicio.hidden = !texto;
        }

        function adicionarExercicio() {

            if (exercicio.value === '') {
                mostrarMensagemExercicio(
                    'Selecione um exercício para adicionar.'
                );
                exercicio.focus();
                return;
            }

            mostrarMensagemExercicio('');

            const option =
                exercicio.options[exercicio.selectedIndex];

            const idExercicio = exercicio.value;
            const nomeExercicio = option.textContent.trim();

            const indice = proximoIndice++;

            const linha = document.createElement('tr');

            linha.innerHTML = `
            <td class="ficha-nome-exercicio">
                <span class="nome-exercicio"></span>

                <input
                    type="hidden"
                    name="itens[${indice}][exercicio_id]"
                    value="${escaparHTML(idExercicio)}"
                >
            </td>

            <td>
                <input
                    type="number"
                    name="itens[${indice}][series]"
                    min="1"
                    required
                    placeholder="3"
                    aria-label="Séries"
                >
            </td>

            <td>
                <input
                    type="number"
                    name="itens[${indice}][repeticoes]"
                    min="1"
                    required
                    placeholder="12"
                    aria-label="Repetições"
                >
            </td>

            <td>
                <input
                    type="number"
                    name="itens[${indice}][carga]"
                    min="0"
                    step="0.5"
                    placeholder="Opcional"
                    aria-label="Carga em quilogramas"
                >
            </td>

            <td>
                <input
                    type="text"
                    name="itens[${indice}][intervalo]"
                    placeholder="Ex.: 60s"
                    aria-label="Intervalo"
                >
            </td>

            <td>
                <button
                    type="button"
                    class="ficha-btn-remover"
                    aria-label="Remover exercício"
                    title="Remover exercício"
                >
                    Remover
                </button>
            </td>
        `;

            linha.querySelector('.nome-exercicio').textContent =
                nomeExercicio;

            linha.querySelector('.ficha-btn-remover')
                .addEventListener('click', function() {

                    linha.remove();
                    atualizarBotaoSalvar();
                });

            corpoExercicios.appendChild(linha);

            exercicio.value = '';

            atualizarBotaoSalvar();
        }

        btnAdicionar.addEventListener(
            'click',
            adicionarExercicio
        );

        exercicio.addEventListener('change', function() {
            mostrarMensagemExercicio('');
        });

        professor.addEventListener(
            'change',
            atualizarBotaoSalvar
        );

        aluno.addEventListener(
            'change',
            atualizarBotaoSalvar
        );

        objetivo.addEventListener(
            'change',
            atualizarBotaoSalvar
        );

        form.addEventListener('submit', function(event) {
            atualizarBotaoSalvar();

            if (btnSalvar.disabled) {
                event.preventDefault();
            }
        });

        // ======================================
        // HISTÓRICO DE TREINOS
        // ======================================

        aluno.addEventListener('change', async function() {

            const alunoId = this.value;

            if (requisicaoHistorico) {
                requisicaoHistorico.abort();
            }

            listaHistorico.innerHTML = '';

            if (alunoId === '') {
                historico.hidden = true;
                return;
            }

            historico.hidden = false;
            listaHistorico.innerHTML =
                '<p class="ficha-historico-mensagem">Carregando histórico...</p>';

            requisicaoHistorico = new AbortController();

            try {
                const url =
                    <?= json_encode(
                        BASE_URL . '/app/controllers/TreinoController.php',
                        JSON_HEX_TAG |
                            JSON_HEX_AMP |
                            JSON_HEX_APOS |
                            JSON_HEX_QUOT
                    ) ?> +
                    '?acao=historico&aluno_id=' +
                    encodeURIComponent(alunoId);

                const resposta = await fetch(url, {
                    signal: requisicaoHistorico.signal
                });

                if (!resposta.ok) {
                    throw new Error(
                        'Falha ao consultar histórico.'
                    );
                }

                const fichas = await resposta.json();

                if (!Array.isArray(fichas)) {
                    throw new Error(
                        'Resposta inesperada do servidor.'
                    );
                }

                if (fichas.length === 0) {
                    listaHistorico.innerHTML = `
                    <div class="ficha-historico-vazio">
                        Este aluno ainda não possui fichas de treino.
                    </div>
                `;
                    return;
                }

                listaHistorico.innerHTML = '';

                fichas.forEach(function(ficha) {

                    const itens = Array.isArray(ficha.itens) ?
                        ficha.itens :
                        [];

                    const linhas = itens.map(function(item) {

                        const carga =
                            item.carga !== null &&
                            item.carga !== '' ?
                            item.carga + ' kg' :
                            '—';

                        const intervalo =
                            item.intervalo !== null &&
                            item.intervalo !== '' ?
                            item.intervalo :
                            '—';

                        return `
                        <tr>
                            <td>${escaparHTML(item.nome_exercicio)}</td>
                            <td>${escaparHTML(item.series)}</td>
                            <td>${escaparHTML(item.repeticoes)}</td>
                            <td>${escaparHTML(carga)}</td>
                            <td>${escaparHTML(intervalo)}</td>
                        </tr>
                    `;
                    }).join('');

                    const card = document.createElement('article');

                    card.className = 'ficha-historico-item';

                    card.innerHTML = `
                    <div class="ficha-historico-topo">
                        <span class="ficha-versao">
                            Versão ${escaparHTML(ficha.versao)}
                        </span>

                        <strong>
                            ${escaparHTML(ficha.objetivo)}
                        </strong>
                    </div>

                    <div class="ficha-historico-meta">
                        <span>
                            Professor:
                            ${escaparHTML(ficha.nome_professor)}
                        </span>

                        <span>
                            Criada em:
                            ${escaparHTML(ficha.criada_em)}
                        </span>
                    </div>

                    <div class="ficha-tabela-container">
                        <table class="ficha-tabela">
                            <thead>
                                <tr>
                                    <th>Exercício</th>
                                    <th>Séries</th>
                                    <th>Reps</th>
                                    <th>Carga</th>
                                    <th>Intervalo</th>
                                </tr>
                            </thead>

                            <tbody>
                                ${linhas}
                            </tbody>
                        </table>
                    </div>
                `;

                    listaHistorico.appendChild(card);
                });

            } catch (erro) {

                if (erro.name === 'AbortError') {
                    return;
                }

                console.error(erro);

                listaHistorico.innerHTML = `
                <p class="ficha-historico-erro">
                    Não foi possível carregar o histórico de treinos.
                </p>
            `;
            }
        });

        // Sincroniza o estado inicial dos elementos.
        atualizarBotaoSalvar();
    });
</script>

<?php
include __DIR__ . '/../shared/footer.php';
?>