<?php
if (!isset($erro)) {
    header("Location: " . BASE_URL . "/app/controllers/PlanoController.php?acao=cadastrar");
    exit;
}
/** @var string $erro */

$tituloPagina = "Cadastrar Plano";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<main class="conteudo financeiro-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Cadastrar Novo Plano</h1>
            <p>Configure um novo plano de mensalidade ou pacote para os alunos.</p>
        </div>
        <div class="fin-header-actions">
            <a href="<?= BASE_URL ?>/app/controllers/PlanoController.php?acao=listar" class="fin-btn fin-btn-secondary">
                Voltar para Lista
            </a>
        </div>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="ui-alerta-erro-box">
            ⚠️ <?= htmlspecialchars($erro); ?>
        </div>
    <?php endif; ?>

    <!-- CARD DO FORMULÁRIO -->
    <section class="fin-table-card ui-card-pad-28">

        <form action="<?= BASE_URL ?>/app/controllers/PlanoController.php?acao=cadastrar" method="POST">

            <div class="ui-grid-auto">

                <div class="fin-form-group">
                    <label>Nome do Plano *</label>
                    <input 
                        type="text" 
                        name="nome" 
                        class="fin-input" 
                        placeholder="Ex: Plano Trimestral Black" 
                        value="<?= htmlspecialchars($_POST['nome'] ?? ''); ?>" 
                        required>
                </div>

                <div class="fin-form-group">
                    <label>Categoria (Modalidade Principal) *</label>
                    <input 
                        type="text" 
                        name="categoria" 
                        class="fin-input" 
                        placeholder="Ex: Musculação / Pilates" 
                        value="<?= htmlspecialchars($_POST['categoria'] ?? ''); ?>" 
                        required>
                </div>

                <div class="fin-form-group">
                    <label>Valor Mensal/Total (R$) *</label>
                    <input 
                        type="text" 
                        name="valor" 
                        class="fin-input" 
                        placeholder="99.90" 
                        value="<?= htmlspecialchars($_POST['valor'] ?? ''); ?>" 
                        required>
                </div>

                <div class="fin-form-group">
                    <label>Duração *</label>
                    <select name="duracao" class="fin-select" required>
                        <option value="">Selecione...</option>
                        <option value="1 Mês" <?= (($_POST['duracao'] ?? '') === '1 Mês') ? 'selected' : ''; ?>>1 Mês</option>
                        <option value="3 Meses" <?= (($_POST['duracao'] ?? '') === '3 Meses') ? 'selected' : ''; ?>>3 Meses</option>
                        <option value="6 Meses" <?= (($_POST['duracao'] ?? '') === '6 Meses') ? 'selected' : ''; ?>>6 Meses</option>
                        <option value="1 Ano" <?= (($_POST['duracao'] ?? '') === '1 Ano') ? 'selected' : ''; ?>>1 Ano</option>
                    </select>
                </div>

            </div>

            <div class="ui-acoes-direita-borda">
                <a href="<?= BASE_URL ?>/app/controllers/PlanoController.php?acao=listar" class="fin-btn fin-btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="fin-btn fin-btn-primary">
                    Salvar Plano
                </button>
            </div>

        </form>

    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>