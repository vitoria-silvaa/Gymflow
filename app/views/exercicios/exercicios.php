<?php
// app/views/exercicios/exercicios.php

$tituloPagina = "Novo Exercício";

$cssEspecifico =
    BASE_URL . '/assets/css/exercicios.css';

require __DIR__ . '/../shared/header.php';
require __DIR__ . '/../shared/sidebar.php';
?>

<main class="conteudo exercicios-cadastro-page">

    <!-- CABEÇALHO -->

    <div class="exercicios-cadastro-header">

        <div class="exercicios-cadastro-titulos">

            <span class="exercicios-cadastro-etiqueta">
                BIBLIOTECA DE EXERCÍCIOS
            </span>

            <h1>Novo Exercício</h1>

            <p>
                Cadastre um exercício e adicione
                uma imagem ou vídeo demonstrativo.
            </p>

        </div>

        <a
            href="<?= BASE_URL ?>/app/controllers/ExercicioController.php"
            class="exercicios-btn exercicios-btn-secundario">
            ← Voltar
        </a>

    </div>

    <!-- CARD DO FORMULÁRIO -->

    <section class="exercicios-cadastro-card">

        <div class="exercicios-card-header">

            <h2>Informações do exercício</h2>

            <p>
                Preencha os dados abaixo para
                adicionar o exercício à biblioteca.
            </p>

        </div>

        <form
            action="<?= BASE_URL ?>/app/controllers/ExercicioController.php"
            method="POST"
            enctype="multipart/form-data"
            class="exercicios-form">

            <div class="exercicios-form-grid">

                <!-- NOME -->

                <div class="exercicios-campo">

                    <label for="nome">
                        Nome do exercício
                        <span class="campo-obrigatorio">*</span>
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        placeholder="Ex.: Agachamento Livre"
                        maxlength="150"
                        required>

                </div>

                <!-- GRUPO MUSCULAR -->

                <div class="exercicios-campo">

                    <label for="grupo_muscular">
                        Grupo muscular
                        <span class="campo-obrigatorio">*</span>
                    </label>

                    <select
                        id="grupo_muscular"
                        name="grupo_muscular"
                        required>
                        <option value="">
                            Selecione o grupo muscular
                        </option>

                        <option value="Bíceps">Bíceps</option>
                        <option value="Costas">Costas</option>
                        <option value="Glúteo">Glúteo</option>
                        <option value="Ombro">Ombro</option>
                        <option value="Peito">Peito</option>
                        <option value="Posterior de coxa">
                            Posterior de coxa
                        </option>
                        <option value="Quadríceps">Quadríceps</option>
                        <option value="Tríceps">Tríceps</option>
                    </select>

                </div>

                <!-- MÍDIA -->

                <div class="exercicios-campo">

                    <label for="tipo_midia">
                        Tipo de mídia
                        <span class="campo-obrigatorio">*</span>
                    </label>

                    <select
                        id="tipo_midia"
                        name="tipo_midia"
                        required>
                        <option value="">
                            Selecione o tipo
                        </option>

                        <option value="imagem">Imagem</option>
                        <option value="video">Vídeo</option>
                    </select>

                </div>

                <!-- ARQUIVO -->

                <div class="exercicios-campo">

                    <label for="arquivo">
                        Arquivo de mídia
                        <span class="campo-obrigatorio">*</span>
                    </label>

                    <input
                        type="file"
                        id="arquivo"
                        name="arquivo"
                        accept=".jpg,.jpeg,.png,.webp,.mp4,.webm"
                        required>

                    <small class="exercicios-ajuda">
                        Imagens: JPG, JPEG, PNG ou WEBP.
                        Vídeos: MP4 ou WEBM.
                    </small>

                </div>

            </div>

            <!-- AÇÕES -->

            <div class="exercicios-form-actions">

                <a
                    href="<?= BASE_URL ?>/app/controllers/ExercicioController.php"
                    class="exercicios-btn exercicios-btn-secundario">
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="exercicios-btn exercicios-btn-primario">
                    Adicionar Exercício
                </button>

            </div>

        </form>

    </section>

</main>

<?php
require __DIR__ . '/../shared/footer.php';
?>