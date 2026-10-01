<?php
// app/views/exercicios/editar.php

require __DIR__ . '/../shared/header.php';
?>

<main class="fin-main">

    <div class="fin-header">
        <div>
            <h1 class="fin-title">Editar Exercício</h1>
            <p class="fin-subtitle">Altere as informações do exercício "<?= htmlspecialchars($exercicio['nome']) ?>".</p>
        </div>
        <a href="<?= BASE_URL ?>/app/controllers/ExercicioController.php" class="fin-btn fin-btn-secondary">Voltar</a>
    </div>

    <div class="fin-card">
        <form method="POST" enctype="multipart/form-data" action="<?= BASE_URL ?>/app/controllers/ExercicioController.php" class="fin-form-grid">

            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" value="<?= (int) $exercicio['id'] ?>">

            <div class="fin-form-group">
                <label class="fin-label" for="nome">Nome do exercício</label>
                <input class="fin-input" type="text" id="nome" name="nome" value="<?= htmlspecialchars($exercicio['nome']) ?>" required>
            </div>

            <div class="fin-form-group">
                <label class="fin-label" for="grupo_muscular">Grupo muscular</label>
                <select class="fin-input" id="grupo_muscular" name="grupo_muscular" required>
                    <option value="">Selecione</option>
                    <option value="Peito" <?= $exercicio['grupo'] === 'Peito' ? 'selected' : '' ?>>Peito</option>
                    <option value="Costas" <?= $exercicio['grupo'] === 'Costas' ? 'selected' : '' ?>>Costas</option>
                    <option value="Pernas" <?= $exercicio['grupo'] === 'Pernas' ? 'selected' : '' ?>>Pernas</option>
                    <option value="Ombros" <?= $exercicio['grupo'] === 'Ombros' ? 'selected' : '' ?>>Ombros</option>
                    <option value="Bíceps" <?= $exercicio['grupo'] === 'Bíceps' ? 'selected' : '' ?>>Bíceps</option>
                    <option value="Tríceps" <?= $exercicio['grupo'] === 'Tríceps' ? 'selected' : '' ?>>Tríceps</option>
                    <option value="Abdômen" <?= $exercicio['grupo'] === 'Abdômen' ? 'selected' : '' ?>>Abdômen</option>
                    <option value="Glúteos" <?= $exercicio['grupo'] === 'Glúteos' ? 'selected' : '' ?>>Glúteos</option>
                    <option value="Posterior de coxa" <?= $exercicio['grupo'] === 'Posterior de coxa' ? 'selected' : '' ?>>Posterior de coxa</option>
                    <option value="Quadríceps" <?= $exercicio['grupo'] === 'Quadríceps' ? 'selected' : '' ?>>Quadríceps</option>
                </select>
            </div>

            <div class="fin-form-group">
                <label class="fin-label" for="tipo_midia">Tipo de mídia</label>
                <select class="fin-input" id="tipo_midia" name="tipo_midia" required>
                    <option value="imagem" <?= $exercicio['tipo_midia'] === 'imagem' ? 'selected' : '' ?>>Imagem</option>
                    <option value="video" <?= $exercicio['tipo_midia'] === 'video' ? 'selected' : '' ?>>Vídeo</option>
                </select>
            </div>

            <div class="fin-form-group">
                <label class="fin-label" for="arquivo">Substituir Mídia Atual</label>
                <input class="fin-input" type="file" id="arquivo" name="arquivo" accept=".jpg,.jpeg,.png,.webp,.mp4,.webm">
                <small style="color: #666; margin-top: 5px; display: block;">Deixe em branco para manter a mídia atual. Imagens: JPG, JPEG, PNG, WEBP. Vídeos: MP4, WEBM.</small>
            </div>

            <div class="fin-form-group" style="grid-column: 1 / -1;">
                <label class="fin-label">Mídia Atual</label>
                <div style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; display: inline-block;">
                    <?php if ($exercicio['tipo_midia'] === 'imagem'): ?>
                        <img src="<?= htmlspecialchars($exercicio['midia']) ?>" alt="Mídia do Exercício" style="max-width: 200px; max-height: 200px; display: block;">
                    <?php elseif ($exercicio['tipo_midia'] === 'video'): ?>
                        <video src="<?= htmlspecialchars($exercicio['midia']) ?>" style="max-width: 200px; max-height: 200px; display: block;" controls></video>
                    <?php else: ?>
                        <p style="margin: 0; color: #666;">Sem mídia válida</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="fin-form-actions" style="grid-column: 1 / -1; margin-top: 20px;">
                <button type="submit" class="fin-btn fin-btn-primary">Salvar Alterações</button>
                <a href="<?= BASE_URL ?>/app/controllers/ExercicioController.php" class="fin-btn fin-btn-secondary">Cancelar</a>
            </div>

        </form>
    </div>

</main>

<?php require __DIR__ . '/../shared/footer.php'; ?>