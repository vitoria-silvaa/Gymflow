<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/FluxoCaixaController.php");
    exit;
}

$tituloPagina = "Lançar custo";
$cssEspecifico = '/Gymflow/assets/css/css/jorge-financeiro.css';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link rel="stylesheet" href="/Gymflow/assets/css/css/jorge-financeiro.css">

<main class="conteudo fluxo-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Lançar Despesa / Custo</h1>
            <p>Registre saídas e custos operacionais da academia no fluxo de caixa.</p>
        </div>

        <div class="fin-header-actions">
            <a href="/Gymflow/app/controllers/FluxoCaixaController.php" class="fin-btn fin-btn-secondary fin-btn-sm">
                ← Voltar ao Fluxo
            </a>
        </div>
    </div>

    <section class="fin-form-card">
        <h2>Novo Lançamento</h2>
        <p class="subtitle">Preencha as informações detalhadas da despesa operacional.</p>

        <form
            method="POST"
            action="/Gymflow/app/controllers/FluxoCaixaController.php?acao=confirmar_lancamento"
        >
            <div class="fin-form-grid">

                <div class="fin-form-group col-span-2">
                    <label for="descricao" class="fin-form-label">
                        Descrição da Despesa <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        name="descricao"
                        id="descricao"
                        class="fin-form-input"
                        placeholder="Ex: Manutenção de esteiras, Compra de anilhas, Aluguel"
                        required
                    >
                </div>

                <div class="fin-form-group">
                    <label for="categoria" class="fin-form-label">
                        Categoria <span class="required">*</span>
                    </label>
                    <select
                        name="categoria"
                        id="categoria"
                        class="fin-form-select"
                        required
                    >
                        <option value="Infraestrutura">Infraestrutura</option>
                        <option value="Equipamentos">Equipamentos</option>
                        <option value="Contas de Consumo">Contas de Consumo (Água/Luz/Net)</option>
                        <option value="Pessoal">Pessoal / Folha</option>
                        <option value="Marketing">Marketing & Divulgação</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>

                <div class="fin-form-group">
                    <label for="valor" class="fin-form-label">
                        Valor (R$) <span class="required">*</span>
                    </label>
                    <input
                        type="number"
                        name="valor"
                        id="valor"
                        class="fin-form-input"
                        step="0.01"
                        min="0.01"
                        placeholder="0,00"
                        required
                    >
                </div>

                <div class="fin-form-group col-span-2">
                    <label for="data" class="fin-form-label">
                        Data do Pagamento / Lançamento <span class="required">*</span>
                    </label>
                    <input
                        type="date"
                        name="data"
                        id="data"
                        class="fin-form-input"
                        value="<?= date('Y-m-d') ?>"
                        required
                    >
                </div>

            </div>

            <div class="fin-form-actions">
                <button type="submit" class="fin-btn fin-btn-primary">
                    ✓ Registrar Custo
                </button>

                <a href="/Gymflow/app/controllers/FluxoCaixaController.php" class="fin-btn fin-btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>

    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>