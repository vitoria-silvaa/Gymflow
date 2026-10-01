<?php
// app/views/exercicios/exercicios.php

require __DIR__ . '/../shared/header.php';
?>

<main class="fin-main">

    <div class="fin-header">
        <div>
            <h1 class="fin-title">Novo Exercício</h1>
            <p class="fin-subtitle">Cadastre um novo exercício na biblioteca.</p>
        </div>
        <a href="<?= BASE_URL ?>/app/controllers/ExercicioController.php" class="fin-btn fin-btn-secondary">Voltar</a>
    </div>

    <div class="fin-card">
        <form action="<?= BASE_URL ?>/app/controllers/ExercicioController.php" method="POST" enctype="multipart/form-data" class="fin-form-grid">

            <div class="fin-form-group">
                <label class="fin-label" for="nome">Nome do exercício</label>
                <input class="fin-input" type="text" id="nome" name="nome" placeholder="Ex: Agachamento Livre" required>
            </div>

            <div class="fin-form-group">
                <label class="fin-label" for="grupo_muscular">Grupo muscular</label>
                <select class="fin-input" id="grupo_muscular" name="grupo_muscular" required>
                    <option value="">Selecione o grupo muscular</option>
                    <option value="Bíceps">Bíceps</option>
                    <option value="Costas">Costas</option>
                    <option value="Glúteo">Glúteo</option>
                    <option value="Ombro">Ombro</option>
                    <option value="Peito">Peito</option>
                    <option value="Posterior de coxa">Posterior de coxa</option>
                    <option value="Quadríceps">Quadríceps</option>
                    <option value="Tríceps">Tríceps</option>
                </select>
            </div>

            <div class="fin-form-group">
                <label class="fin-label" for="tipo_midia">Tipo de mídia</label>
                <select class="fin-input" id="tipo_midia" name="tipo_midia" required>
                    <option value="">Selecione o tipo</option>
                    <option value="imagem">Imagem</option>
                    <option value="video">Vídeo</option>
                </select>
            </div>

            <div class="fin-form-group">
                <label class="fin-label" for="arquivo">Arquivo (Mídia)</label>
                <input class="fin-input" type="file" id="arquivo" name="arquivo" accept=".jpg,.jpeg,.png,.webp,.mp4,.webm" required>
                <small style="color: #666; margin-top: 5px; display: block;">Imagens: JPG, JPEG, PNG, WEBP. Vídeos: MP4, WEBM.</small>
            </div>

            <div class="fin-form-actions" style="grid-column: 1 / -1; margin-top: 20px;">
                <button type="submit" class="fin-btn fin-btn-primary">Adicionar Exercício</button>
                <a href="<?= BASE_URL ?>/app/controllers/ExercicioController.php" class="fin-btn fin-btn-secondary">Cancelar</a>
            </div>

        </form>
    </div>

</main>

<?php require __DIR__ . '/../shared/footer.php'; ?>