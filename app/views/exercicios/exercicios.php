<?php
// app/views/exercicios/exercicios.php
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Novo Exercício - Gymflow</title>
</head>

<body>

    <main>

        <h1>Novo Exercício</h1>

        <p>
            Cadastre um novo exercício na biblioteca.
        </p>


        <form
            action="/Gymflow/app/controllers/ExercicioController.php"
            method="POST"
            enctype="multipart/form-data">


            <!-- NOME -->

            <div>

                <label for="nome">
                    Nome do exercício
                </label>

                <input
                    type="text"
                    id="nome"
                    name="nome"
                    placeholder="Ex: Agachamento Livre"
                    required>

            </div>


            <!-- GRUPO MUSCULAR -->

            <div>

                <label for="grupo_muscular">
                    Grupo muscular
                </label>

                <select
                    id="grupo_muscular"
                    name="grupo_muscular"
                    required>

                    <option value="">
                        Selecione o grupo muscular
                    </option>

                    <option value="Bíceps">
                        Bíceps
                    </option>

                    <option value="Tríceps">
                        Tríceps
                    </option>

                    <option value="Costas">
                        Costas
                    </option>

                    <option value="Glúteos">
                        Glúteos
                    </option>

                    <option value="Ombros">
                        Ombros
                    </option>

                    <option value="Peito">
                        Peito
                    </option>

                    <option value="Posterior de coxa">
                        Posterior de coxa
                    </option>

                    <option value="Quadríceps">
                        Quadríceps
                    </option>

                </select>

            </div>


            <!-- TIPO DE MÍDIA -->

            <div>

                <label for="tipo_midia">
                    Tipo de mídia
                </label>

                <select
                    id="tipo_midia"
                    name="tipo_midia"
                    required>

                    <option value="">
                        Selecione o tipo
                    </option>

                    <option value="imagem">
                        Imagem
                    </option>

                    <option value="video">
                        Vídeo
                    </option>

                </select>

            </div>


            <!-- ARQUIVO -->

            <div>

                <label for="arquivo">
                    Arquivo
                </label>

                <input
                    type="file"
                    id="arquivo"
                    name="arquivo"
                    accept=".jpg,.jpeg,.png,.webp,.mp4,.webm"
                    required>

                <p>
                    Imagens: JPG, JPEG, PNG ou WEBP.
                    Vídeos: MP4 ou WEBM.
                </p>

            </div>


            <!-- BOTÕES -->

            <div>

                <a
                    href="/Gymflow/app/controllers/ExercicioController.php"
                    class="btn">
                    Cancelar
                </a>

                <button type="submit">
                    Adicionar exercício
                </button>

            </div>

        </form>

    </main>

</body>

</html>