<?php
// app/views/exercicios/editar.php

require __DIR__ . '/../shared/header.php';
?>

<main>

    <h1>Editar Exercício</h1>

    <p>
        Altere as informações do exercício.
    </p>

    <form
        method="POST"
        enctype="multipart/form-data"
        action="/Gymflow/app/controllers/ExercicioController.php"
    >

        <!-- INFORMAÇÕES DA EDIÇÃO -->

        <input
            type="hidden"
            name="acao"
            value="editar"
        >

        <input
            type="hidden"
            name="id"
            value="<?= (int) $exercicio['id'] ?>"
        >


        <!-- NOME -->

        <div>

            <label for="nome">
                Nome do exercício
            </label>

            <input
                type="text"
                id="nome"
                name="nome"
                value="<?= htmlspecialchars($exercicio['nome']) ?>"
                required
            >

        </div>


        <br>


        <!-- GRUPO MUSCULAR -->

        <div>

            <label for="grupo_muscular">
                Grupo muscular
            </label>

            <select
                id="grupo_muscular"
                name="grupo_muscular"
                required
            >

                <option value="">
                    Selecione
                </option>

                <option
                    value="Peito"
                    <?= $exercicio['grupo'] === 'Peito' ? 'selected' : '' ?>
                >
                    Peito
                </option>

                <option
                    value="Costas"
                    <?= $exercicio['grupo'] === 'Costas' ? 'selected' : '' ?>
                >
                    Costas
                </option>

                <option
                    value="Pernas"
                    <?= $exercicio['grupo'] === 'Pernas' ? 'selected' : '' ?>
                >
                    Pernas
                </option>

                <option
                    value="Ombros"
                    <?= $exercicio['grupo'] === 'Ombros' ? 'selected' : '' ?>
                >
                    Ombros
                </option>

                <option
                    value="Bíceps"
                    <?= $exercicio['grupo'] === 'Bíceps' ? 'selected' : '' ?>
                >
                    Bíceps
                </option>

                <option
                    value="Tríceps"
                    <?= $exercicio['grupo'] === 'Tríceps' ? 'selected' : '' ?>
                >
                    Tríceps
                </option>

                <option
                    value="Abdômen"
                    <?= $exercicio['grupo'] === 'Abdômen' ? 'selected' : '' ?>
                >
                    Abdômen
                </option>

                <option
                    value="Glúteos"
                    <?= $exercicio['grupo'] === 'Glúteos' ? 'selected' : '' ?>
                >
                    Glúteos
                </option>

            </select>

        </div>


        <br>


        <!-- TIPO DE MÍDIA -->

        <div>

            <label for="tipo_midia">
                Tipo de mídia
            </label>

            <select
                id="tipo_midia"
                name="tipo_midia"
                required
            >

                <option
                    value="imagem"
                    <?= $exercicio['tipo_midia'] === 'imagem' ? 'selected' : '' ?>
                >
                    Imagem
                </option>

                <option
                    value="video"
                    <?= $exercicio['tipo_midia'] === 'video' ? 'selected' : '' ?>
                >
                    Vídeo
                </option>

            </select>

        </div>


        <br>


        <!-- MÍDIA ATUAL -->

        <div>

            <label>
                Mídia atual
            </label>

            <?php if (!empty($exercicio['midia'])): ?>

                <?php if ($exercicio['tipo_midia'] === 'video'): ?>

                    <video
                        controls
                        width="300"
                    >

                        <source
                            src="<?= htmlspecialchars($exercicio['midia']) ?>"
                        >

                        Seu navegador não suporta vídeo.

                    </video>

                <?php else: ?>

                    <img
                        src="<?= htmlspecialchars($exercicio['midia']) ?>"
                        alt="<?= htmlspecialchars($exercicio['nome']) ?>"
                        width="300"
                    >

                <?php endif; ?>

            <?php else: ?>

                <p>
                    Nenhuma mídia cadastrada.
                </p>

            <?php endif; ?>

        </div>


        <br>


        <!-- NOVA MÍDIA -->

        <div>

            <label for="arquivo">
                Nova mídia
            </label>

            <input
                type="file"
                id="arquivo"
                name="arquivo"
            >

            <p>
                Deixe vazio para manter a mídia atual.
            </p>

        </div>


        <br>


        <!-- BOTÕES -->

        <div>

            <button type="submit">
                Salvar alterações
            </button>

            <a
                href="/Gymflow/app/controllers/ExercicioController.php"
            >
                Cancelar
            </a>

        </div>

    </form>

</main>

<?php require __DIR__ . '/../shared/footer.php'; ?>