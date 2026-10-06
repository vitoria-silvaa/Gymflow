<?php
if (!isset($plano)) {
    $id = $_GET['id'] ?? null;
    header("Location: " . BASE_URL . "/app/controllers/PlanoController.php?acao=editar" . ($id ? "&id=" . $id : ""));
    exit;
}
/** @var array $plano */
/** @var string $erro */

$tituloPagina = "Editar Plano";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<main class="conteudo financeiro-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Editar Plano #<?= htmlspecialchars((string)$plano['id']) ?></h1>
            <p>Atualize as informações do plano abaixo.</p>
        </div>
        <div class="fin-header-actions">
            <a href="<?= BASE_URL ?>/app/controllers/PlanoController.php?acao=listar" class="fin-btn fin-btn-secondary">
                ← Voltar para Lista
            </a>
        </div>
    </div>

    <?php if (!empty($erro)): ?>
        <div style="background-color: #fee2e2; color: #b91c1c; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; font-weight: 600; border: 1px solid #fecaca;">
            ⚠️ <?= htmlspecialchars($erro); ?>
        </div>
    <?php endif; ?>

    <!-- CARD DO FORMULÁRIO -->
    <section class="fin-table-card" style="padding: 28px;">

        <form action="<?= BASE_URL ?>/app/controllers/PlanoController.php?acao=editar&id=<?= $plano['id'] ?>" method="POST">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom: 24px;">

                <div class="fin-form-group">
                    <label>Nome do Plano *</label>
                    <input 
                        type="text" 
                        name="nome" 
                        class="fin-input" 
                        value="<?= htmlspecialchars($plano['nome']); ?>" 
                        required>
                </div>

                <div class="fin-form-group">
                    <label>Categoria *</label>
                    <input 
                        type="text" 
                        name="categoria" 
                        class="fin-input" 
                        value="<?= htmlspecialchars($plano['categoria']); ?>" 
                        required>
                </div>

                <div class="fin-form-group">
                    <label>Valor Mensal/Total (R$) *</label>
                    <input 
                        type="text" 
                        name="valor" 
                        class="fin-input" 
                        value="<?= htmlspecialchars($plano['valor']); ?>" 
                        required>
                </div>

                <div class="fin-form-group">
                    <label>Duração *</label>
                    <select name="duracao" class="fin-select" required>
                        <option value="">Selecione...</option>
                        <option value="1 Mês" <?= ($plano['duracao'] === '1 Mês') ? 'selected' : ''; ?>>1 Mês</option>
                        <option value="3 Meses" <?= ($plano['duracao'] === '3 Meses') ? 'selected' : ''; ?>>3 Meses</option>
                        <option value="6 Meses" <?= ($plano['duracao'] === '6 Meses') ? 'selected' : ''; ?>>6 Meses</option>
                        <option value="1 Ano" <?= ($plano['duracao'] === '1 Ano') ? 'selected' : ''; ?>>1 Ano</option>
                    </select>
                </div>

            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                <a href="<?= BASE_URL ?>/app/controllers/PlanoController.php?acao=listar" class="fin-btn fin-btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="fin-btn fin-btn-primary">
                    ✓ Salvar Alterações
                </button>
            </div>

        </form>

    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
