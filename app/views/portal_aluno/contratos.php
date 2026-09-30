<?php
if (!isset($contratos)) {
    header("Location: " . BASE_URL . "/app/controllers/PortalAlunoController.php?acao=contratos");
    exit;
}

$tituloPagina = "Meus Contratos";
?>

<?php 
include __DIR__ . '/../shared/header.php'; 
include __DIR__ . '/../shared/sidebar.php'; 
?>

<!-- CSS para a página de contratos -->
<style>
.contratos-page {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.contratos-header {
    margin-bottom: 2rem;
}

.portal-eyebrow {
    font-size: 0.85rem;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 1px;
    text-transform: uppercase;
    display: block;
    margin-bottom: 0.5rem;
}

.contratos-header h1 {
    font-size: 2rem;
    font-weight: 800;
    color: var(--cor-secundaria);
    margin: 0;
}

.contratos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.contrato-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    border: 1px solid #e2e8f0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
}

.contrato-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.contrato-status {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 1rem;
    align-self: flex-start;
}

.status-ativo {
    background-color: #dcfce7;
    color: #166534;
}

.status-inativo {
    background-color: #f1f5f9;
    color: #475569;
}

.contrato-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--cor-secundaria);
    margin: 0 0 0.5rem 0;
}

.contrato-category {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0 0 1.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.contrato-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #f1f5f9;
    flex-grow: 1;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.875rem;
}

.detail-label {
    color: #64748b;
    font-weight: 500;
}

.detail-value {
    color: #1e293b;
    font-weight: 600;
}

.contrato-price {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--cor-primaria);
    margin-bottom: 1.5rem;
}

.btn-contrato {
    background-color: #f8fafc;
    color: var(--cor-secundaria);
    border: 1px solid #e2e8f0;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-contrato:hover {
    background-color: var(--cor-primaria);
    color: #ffffff;
    border-color: var(--cor-primaria);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #ffffff;
    border-radius: 16px;
    border: 1px dashed #cbd5e1;
    color: #64748b;
}

.empty-state i {
    font-size: 3rem;
    color: #94a3b8;
    margin-bottom: 1rem;
}
</style>

<main class="contratos-page">

    <header class="contratos-header">
        <span class="portal-eyebrow">PORTAL DO ALUNO</span>
        <h1>Meus Contratos</h1>
    </header>

    <?php if (empty($contratos)): ?>
        <div class="empty-state">
            <i class="ph ph-file-dashed"></i>
            <h2>Nenhum contrato encontrado</h2>
            <p>Você ainda não possui matrículas ou contratos registrados no sistema.</p>
        </div>
    <?php else: ?>
        <div class="contratos-grid">
            <?php foreach ($contratos as $contrato): ?>
                <div class="contrato-card">
                    
                    <?php if ($contrato['ativa']): ?>
                        <span class="contrato-status status-ativo">
                            <i class="ph-fill ph-check-circle" style="margin-right: 4px;"></i> Ativo
                        </span>
                    <?php else: ?>
                        <span class="contrato-status status-inativo">
                            <i class="ph-fill ph-x-circle" style="margin-right: 4px;"></i> Inativo
                        </span>
                    <?php endif; ?>

                    <h3 class="contrato-title"><?= htmlspecialchars($contrato['nome_plano']) ?></h3>
                    
                    <div class="contrato-category">
                        <i class="ph ph-barbell"></i>
                        <?= htmlspecialchars($contrato['categoria']) ?>
                    </div>

                    <div class="contrato-price">
                        R$ <?= number_format($contrato['valor'], 2, ',', '.') ?>
                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">/ <?= htmlspecialchars($contrato['duracao']) ?></span>
                    </div>

                    <div class="contrato-details">
                        <div class="detail-row">
                            <span class="detail-label">Data de Início</span>
                            <span class="detail-value"><?= date('d/m/Y', strtotime($contrato['inicio'])) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Data de Fim</span>
                            <span class="detail-value"><?= date('d/m/Y', strtotime($contrato['fim'])) ?></span>
                        </div>
                        <?php if ($contrato['desconto'] > 0): ?>
                        <div class="detail-row">
                            <span class="detail-label">Desconto Aplicado</span>
                            <span class="detail-value" style="color: #10b981;">- R$ <?= number_format($contrato['desconto'], 2, ',', '.') ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <a href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=imprimir_contrato&id=<?= $contrato['id'] ?>" target="_blank" class="btn-contrato" style="text-decoration: none;">
                        <i class="ph ph-file-pdf"></i>
                        Baixar Contrato (PDF)
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<script>
// Atualiza o sidebar ativo
document.addEventListener('DOMContentLoaded', () => {
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
    sidebarLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href').includes('acao=contratos')) {
            link.classList.add('active');
        }
    });
});
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>
