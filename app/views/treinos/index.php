<?php

/** @var array $alunos */
/** @var array $exercicios */
/** @var array $professores */
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/TreinoController.php");
    exit;
}

$tituloPagina = "Fichas de Treino";

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main>

    <?php if (!empty($mensagemSucesso)): ?>
        <div class="mensagem-sucesso">
            <?= htmlspecialchars($mensagemSucesso) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensagemErro)): ?>
        <div class="mensagem-erro">
            <?= htmlspecialchars($mensagemErro) ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="formFicha">
        <div>
            <h1>Fichas de Treino</h1>

            <p>
                Monte e versione o treino do aluno.
            </p>
        </div>

        <section>

            <div>
                <label for="professor">Professor responsável</label>

                <select id="professor" name="professor_id">
                    <option value="">Selecione um professor</option>

                    <?php foreach ($professores as $professor): ?>
                        <option value="<?= (int) $professor['id'] ?>">
                            <?= htmlspecialchars($professor['name']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div>
                <label for="aluno">Aluno</label>

                <select id="aluno" name="aluno">
                    <option value="">Selecione um aluno</option>

                    <?php foreach ($alunos as $aluno): ?>
                        <option value="<?= (int) $aluno['id'] ?>">
                            <?= htmlspecialchars($aluno['nome']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div>
                <label for="objetivo">Objetivo</label>

                <select id="objetivo" name="objetivo">
                    <option value="">Selecione um objetivo</option>

                    <option value="Emagrecimento">Emagrecimento</option>
                    <option value="Definição">Definição</option>
                    <option value="Hipertrofia">Hipertrofia</option>
                    <option value="Ganho de Massa Muscular">Ganho de Massa Muscular</option>
                    <option value="Condicionamento">Condicionamento</option>
                    <option value="Resistência Muscular">Resistência Muscular</option>
                    <option value="Força">Força</option>
                    <option value="Mobilidade e Flexibilidade">Mobilidade e Flexibilidade</option>
                    <option value="Reabilitação">Reabilitação</option>
                    <option value="Saúde e Qualidade de Vida">Saúde e Qualidade de Vida</option>
                </select>
            </div>

            <div>
                <label for="exercicio">Exercício</label>

                <select id="exercicio" name="exercicio">
                    <option value="">Selecione um exercício</option>

                    <?php foreach ($exercicios as $exercicio): ?>
                        <option value="<?= (int) $exercicio['id'] ?>">
                            <?= htmlspecialchars($exercicio['nome']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>

                <button type="button" id="btnAdicionar">
                    Adicionar
                </button>

            </div>

        </section>

        <section>

            <h2>Exercícios da ficha</h2>

            <table>
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

                <tbody>
                    <!-- Os exercícios adicionados aparecerão aqui -->
                </tbody>
            </table>

        </section>

        <section>

            <button type="submit" id="btnSalvarFicha" disabled>
                Salvar ficha
            </button>

        </section>

    </form>

    <section id="historicoTreinos" style="display: none;">

        <h2>Histórico de Treinos</h2>

        <div id="listaHistorico"></div>

    </section>

</main>


<script>
    function atualizarBotaoSalvar() {

        const professor = document.getElementById('professor').value;
        const aluno = document.getElementById('aluno').value;
        const objetivo = document.getElementById('objetivo').value;

        const tabelaExercicios = document.querySelector('#formFicha section:nth-of-type(2) tbody');
        const quantidadeExercicios = tabelaExercicios.querySelectorAll('tr').length;

        const btnSalvar = document.getElementById('btnSalvarFicha');

        if (
            professor !== '' &&
            aluno !== '' &&
            objetivo !== '' &&
            quantidadeExercicios > 0
        ) {
            btnSalvar.disabled = false;
        } else {
            btnSalvar.disabled = true;
        }
    }

    document.getElementById('btnAdicionar').addEventListener('click', function() {

        const exercicio = document.getElementById('exercicio');
        const tabela = document.querySelector('tbody');

        if (exercicio.value === '') {
            alert('Selecione um exercício.');
            return;
        }

        const option = exercicio.options[exercicio.selectedIndex];

        const linha = document.createElement('tr');
        const indice = document.querySelectorAll('tbody tr').length;

        linha.innerHTML = `
        <td>
            ${option.text}
            <input type="hidden" name="itens[${indice}][exercicio_id]" value="${exercicio.value}">
        </td>

        <td>
            <input type="number"
                   name="itens[${indice}][series]"
                   min="1"
                   required
                   placeholder="Séries">
        </td>

        <td>
            <input type="number"
                   name="itens[${indice}][repeticoes]"
                   min="1"
                   required
                   placeholder="Repetições">
        </td>

        <td>
            <input type="number"
                   name="itens[${indice}][carga]"
                   min="0"
                   step="0.5"
                   placeholder="Opcional">
        </td>

        <td>
            <input type="text"
                   name="itens[${indice}][intervalo]"
                   placeholder="Opcional">
        </td>

        <td>
            <button type="button" onclick="this.closest('tr').remove(); atualizarBotaoSalvar();">
                🗑️
            </button>
        </td>
    `;

        tabela.appendChild(linha);

        exercicio.value = '';

        atualizarBotaoSalvar();
    });


    document.getElementById('professor').addEventListener('change', atualizarBotaoSalvar);

    document.getElementById('aluno').addEventListener('change', atualizarBotaoSalvar);

    document.getElementById('objetivo').addEventListener('change', atualizarBotaoSalvar);


    document.getElementById('aluno').addEventListener('change', function() {

        const alunoId = this.value;

        const historico = document.getElementById('historicoTreinos');
        const lista = document.getElementById('listaHistorico');

        if (alunoId === '') {
            historico.style.display = 'none';
            lista.innerHTML = '';
            return;
        }

        lista.innerHTML = '<p>Carregando histórico...</p>';
        historico.style.display = 'block';

        fetch('/Gymflow/app/controllers/TreinoController.php?acao=historico&aluno_id=' + alunoId)
            .then(response => response.json())
            .then(fichas => {

                if (fichas.length === 0) {
                    lista.innerHTML = '<p>Este aluno ainda não possui fichas de treino.</p>';
                    return;
                }

                lista.innerHTML = '';

                fichas.forEach(ficha => {

                    let exercicios = '';

                    ficha.itens.forEach(item => {

                        const carga = item.carga !== null && item.carga !== '' ?
                            item.carga + ' kg' :
                            '—';

                        const intervalo = item.intervalo !== null && item.intervalo !== '' ?
                            item.intervalo :
                            '—';

                        exercicios += `
                        <tr>
                            <td>${item.nome_exercicio}</td>
                            <td>${item.series}</td>
                            <td>${item.repeticoes}</td>
                            <td>${carga}</td>
                            <td>${intervalo}</td>
                        </tr>
                    `;
                    });

                    lista.innerHTML += `
                    <div class="ficha-historico">

                        <div>
                            <strong>v${ficha.versao}</strong>
                            <strong>${ficha.objetivo}</strong>
                        </div>

                        <p>
                            Professor: ${ficha.nome_professor}
                        </p>

                        <p>
                            Criada em: ${ficha.criada_em}
                        </p>

                        <table>
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
                                ${exercicios}
                            </tbody>
                        </table>

                    </div>
                `;
                });
            })
            .catch(error => {

                console.error(error);

                lista.innerHTML = `
                <p>
                    Não foi possível carregar o histórico de treinos.
                </p>
            `;
            });
    });
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>