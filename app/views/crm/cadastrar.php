<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/CrmController.php");
    exit;
}

$cssEspecifico = '/Gymflow/assets/css/css/jorge-financeiro.css';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

$isEdicao = !empty($dados['id']);
$colunasStatus = ['Novo', 'Contato Agendado', 'Experimental', 'Convertido', 'Perdido'];
?>

<link rel="stylesheet" href="/Gymflow/assets/css/css/jorge-financeiro.css">

<main class="conteudo crm-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1><?= $isEdicao ? 'Editar Lead' : 'Cadastrar Novo Lead' ?></h1>
            <p><?= $isEdicao ? 'Atualize as informações de contato e interesse do lead.' : 'Adicione um novo lead interessado em treinar na Gymflow.' ?></p>
        </div>

        <div class="fin-header-actions">
            <a href="<?= $baseUrl ?>" class="fin-btn fin-btn-secondary fin-btn-sm">
                ← Voltar ao Quadro
            </a>
        </div>
    </div>

    <section class="fin-form-card">
        <h2><?= $isEdicao ? 'Ficha do Lead' : 'Dados do Lead' ?></h2>
        <p class="subtitle">Campos marcados com <span style="color: var(--fin-danger); font-weight: bold;">*</span> são obrigatórios.</p>

        <?php if (!empty($erro)): ?>
            <div class="fin-alert fin-alert-danger">
                <span>⚠️</span>
                <span><?= htmlspecialchars($erro) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= $baseUrl ?>?acao=<?= $isEdicao ? 'editar' : 'cadastrar' ?>">
            <?php if ($isEdicao): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($dados['id'] ?? '')) ?>">
            <?php endif; ?>

            <div class="fin-form-grid">

                <div class="fin-form-group col-span-2">
                    <label class="fin-form-label">Filial <span class="required">*</span></label>
                    <select name="filial_id" class="fin-form-select" required>
                        <option value="">Selecione uma filial</option>
                        <?php foreach ($filiais ?? [] as $filial): ?>
                            <option
                                value="<?= $filial['id'] ?>"
                                <?= ($dados['filial_id'] ?? 0) == $filial['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($filial['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="fin-form-group">
                    <label class="fin-form-label">Nome Completo <span class="required">*</span></label>
                    <input
                        type="text"
                        name="nome"
                        class="fin-form-input"
                        placeholder="Ex: Carlos Oliveira"
                        value="<?= htmlspecialchars($dados['nome'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="fin-form-group">
                    <label class="fin-form-label">Telefone / WhatsApp <span class="required">*</span></label>
                    <input
                        type="text"
                        name="telefone"
                        class="fin-form-input"
                        placeholder="(00) 00000-0000"
                        value="<?= htmlspecialchars($dados['telefone'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="fin-form-group">
                    <label class="fin-form-label">Objetivo Principal</label>
                    <input
                        type="text"
                        name="objetivo"
                        class="fin-form-input"
                        placeholder="Ex: Emagrecimento, Hipertrofia, Saúde"
                        value="<?= htmlspecialchars($dados['objetivo'] ?? '') ?>"
                    >
                </div>

                <div class="fin-form-group">
                    <label class="fin-form-label">Origem / Campanha</label>
                    <input
                        type="text"
                        name="campanha"
                        class="fin-form-input"
                        placeholder="Ex: Instagram Ads, Indicação, Fachada"
                        value="<?= htmlspecialchars($dados['campanha'] ?? '') ?>"
                    >
                </div>

                <?php if ($isEdicao): ?>
                    <div class="fin-form-group col-span-2">
                        <label class="fin-form-label">Status no Funil</label>
                        <select name="status" class="fin-form-select">
                            <?php foreach ($colunasStatus as $st): ?>
                                <option
                                    value="<?= $st ?>"
                                    <?= ($dados['status'] ?? 'Novo') === $st ? 'selected' : '' ?>
                                >
                                    <?= $st ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

            </div>

            <div class="fin-form-actions">
                <button type="submit" class="fin-btn fin-btn-primary">
                    <?= $isEdicao ? '✓ Salvar Alterações' : '+ Cadastrar Lead' ?>
                </button>

                <a href="<?= $baseUrl ?>" class="fin-btn fin-btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>

    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>