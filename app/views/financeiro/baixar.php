<?php
if (!isset($conta)) {
    header("Location: /Gymflow/app/controllers/FinanceiroController.php");
    exit;
}

$tituloPagina = "Baixar pagamento";
$cssEspecifico = '/Gymflow/assets/css/css/jorge-financeiro.css';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link rel="stylesheet" href="/Gymflow/assets/css/css/jorge-financeiro.css">

<main class="conteudo financeiro-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Baixar Pagamento</h1>
            <p>Confirme o recebimento e selecione o método de liquidação da fatura.</p>
        </div>
        <div class="fin-header-actions">
            <a href="/Gymflow/app/controllers/FinanceiroController.php" class="fin-btn fin-btn-secondary fin-btn-sm">
                ← Voltar às contas
            </a>
        </div>
    </div>

    <section class="fin-form-card">
        <h2>Confirmar Recebimento</h2>
        <p class="subtitle">Confira os dados da cobrança antes de registrar a baixa.</p>

        <div class="fin-info-box">
            <?php if (!empty($conta['aluno_nome'])): ?>
                <div class="fin-info-box-item" style="flex: 1 1 100%;">
                    <span class="label">Aluno</span>
                    <span class="value"><?= htmlspecialchars($conta['aluno_nome']) ?></span>
                </div>
            <?php endif; ?>

            <div class="fin-info-box-item">
                <span class="label">Valor a Receber</span>
                <span class="value" style="color: var(--fin-primary-dark);">
                    R$ <?= number_format($conta['valor'], 2, ',', '.') ?>
                </span>
            </div>

            <div class="fin-info-box-item">
                <span class="label">Vencimento</span>
                <span class="value">
                    <?= date('d/m/Y', strtotime($conta['vencimento'])) ?>
                </span>
            </div>

            <?php if (!empty($conta['matricula_id'])): ?>
                <div class="fin-info-box-item">
                    <span class="label">Matrícula</span>
                    <span class="value">#<?= htmlspecialchars($conta['matricula_id']) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <form
            method="POST"
            action="/Gymflow/app/controllers/FinanceiroController.php?acao=confirmar_pagamento"
        >
            <input
                type="hidden"
                name="conta_id"
                value="<?= (int) $conta['id'] ?>"
            >

            <div class="fin-form-group">
                <label for="forma_pagamento" class="fin-form-label">
                    Forma de Pagamento <span class="required">*</span>
                </label>

                <select
                    name="forma_pagamento"
                    id="forma_pagamento"
                    class="fin-form-select"
                    required
                >
                    <option value="Dinheiro">Dinheiro</option>
                    <option value="Pix">Pix</option>
                    <option value="Cartão de Crédito">Cartão de Crédito</option>
                    <option value="Cartão de Débito">Cartão de Débito</option>
                    <option value="Boleto">Boleto</option>
                </select>
            </div>

            <div class="fin-form-actions">
                <button type="submit" class="fin-btn fin-btn-primary">
                    ✓ Confirmar Pagamento
                </button>

                <a href="/Gymflow/app/controllers/FinanceiroController.php" class="fin-btn fin-btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>

    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>