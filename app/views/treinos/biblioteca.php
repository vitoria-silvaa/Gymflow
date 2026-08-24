<?php
// app/views/treinos/biblioteca.php
?>

<?php require __DIR__ . '/../shared/header.php'; ?>

<main>

    <div class="pagina-cabecalho">

        <div>
            <h1>Biblioteca de Exercícios</h1>

            <p>
                Consulte e gerencie os exercícios disponíveis para os treinos.
            </p>
        </div>

        <a
            href="/Gymflow/app/views/exercicios/exercicios.php"
            class="btn"
        >
            + Novo exercício
        </a>

    </div>


    <!-- PESQUISA E FILTRO -->

    <div class="filtros-exercicios">

        <form method="GET">

            <div class="campo-pesquisa">

                <div>

                    <label for="pesquisa">
                        Pesquisar exercício
                    </label>

                    <input
                        type="text"
                        id="pesquisa"
                        name="pesquisa"
                        placeholder="Digite o nome do exercício..."
                        value="<?= htmlspecialchars($pesquisa ?? '') ?>"
                    >

                </div>


                <div>

                    <label for="grupo">
                        Grupo muscular
                    </label>

                    <select id="grupo" name="grupo">

                        <option value="">
                            Todos
                        </option>

                        <option value="Peito"
                            <?= ($grupo ?? '') === 'Peito' ? 'selected' : '' ?>>
                            Peito
                        </option>

                        <option value="Costas"
                            <?= ($grupo ?? '') === 'Costas' ? 'selected' : '' ?>>
                            Costas
                        </option>

                        <option value="Pernas"
                            <?= ($grupo ?? '') === 'Pernas' ? 'selected' : '' ?>>
                            Pernas
                        </option>

                        <option value="Ombros"
                            <?= ($grupo ?? '') === 'Ombros' ? 'selected' : '' ?>>
                            Ombros
                        </option>

                        <option value="Bíceps"
                            <?= ($grupo ?? '') === 'Bíceps' ? 'selected' : '' ?>>
                            Bíceps
                        </option>

                        <option value="Tríceps"
                            <?= ($grupo ?? '') === 'Tríceps' ? 'selected' : '' ?>>
                            Tríceps
                        </option>

                        <option value="Abdômen"
                            <?= ($grupo ?? '') === 'Abdômen' ? 'selected' : '' ?>>
                            Abdômen
                        </option>

                        <option value="Glúteos"
                            <?= ($grupo ?? '') === 'Glúteos' ? 'selected' : '' ?>>
                            Glúteos
                        </option>

                        <option value="Quadríceps"
                            <?= ($grupo ?? '') === 'Quadríceps' ? 'selected' : '' ?>>
                            Quadríceps
                        </option>

                        <option value="Posterior de coxa"
                            <?= ($grupo ?? '') === 'Posterior de coxa' ? 'selected' : '' ?>>
                            Posterior de coxa
                        </option>

                    </select>

                </div>


                <div class="botao-pesquisa">

                    <button type="submit">
                        Buscar
                    </button>

                </div>

            </div>

        </form>

    </div>


    <!-- LISTA DE EXERCÍCIOS -->

    <div class="exercicios-grid">

        <?php if (!empty($exercicios)): ?>

            <?php foreach ($exercicios as $exercicio): ?>

                <div class="exercicio-card">


                    <!-- MÍDIA -->

                    <div class="exercicio-midia">

                        <?php if (!empty($exercicio['midia'])): ?>


                            <?php if ($exercicio['tipo_midia'] === 'video'): ?>

                                <video
                                    controls
                                    style="
                                        width: 100%;
                                        height: 200px;
                                        object-fit: cover;
                                        border-radius: 8px;
                                    "
                                >

                                    <source
                                        src="<?= htmlspecialchars($exercicio['midia']) ?>"
                                        type="video/mp4"
                                    >

                                    Seu navegador não suporta vídeo.

                                </video>


                            <?php else: ?>

                                <img
                                    src="<?= htmlspecialchars($exercicio['midia']) ?>"
                                    alt="<?= htmlspecialchars($exercicio['nome']) ?>"
                                    style="
                                        width: 100%;
                                        height: 200px;
                                        object-fit: cover;
                                        border-radius: 8px;
                                    "
                                >

                            <?php endif; ?>


                        <?php else: ?>

                            <span>
                                Sem mídia
                            </span>

                        <?php endif; ?>

                    </div>


                    <!-- INFORMAÇÕES -->

                    <div class="exercicio-info">

                        <h3>
                            <?= htmlspecialchars($exercicio['nome']) ?>
                        </h3>

                        <span class="exercicio-grupo">
                            <?= htmlspecialchars($exercicio['grupo']) ?>
                        </span>

                    </div>


                   <!-- AÇÕES -->

                    <div class="exercicio-acoes">

                      <a
                        href="/Gymflow/app/controllers/ExercicioController.php?acao=editar&id=<?= (int) $exercicio['id'] ?>"
                      >
                        Editar
                      </a>

                      <a
                        href="/Gymflow/app/controllers/ExercicioController.php?acao=excluir&id=<?= (int) $exercicio['id'] ?>"
                        class="excluir"
                        onclick="return confirm('Tem certeza que deseja excluir este exercício?');"
                      >
                        Excluir
                      </a>

                    </div>


                </div>

            <?php endforeach; ?>


        <?php else: ?>

            <div class="nenhum-exercicio">

                <h3>
                    Nenhum exercício encontrado
                </h3>

                <p>
                    Não existem exercícios cadastrados ou nenhum exercício
                    corresponde à sua pesquisa.
                </p>

            </div>

        <?php endif; ?>

    </div>

</main>


<?php require __DIR__ . '/../shared/footer.php'; ?>