<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/CrmController.php");
    exit;
}

$cssEspecifico = '/Gymflow/assets/css/css/jorge-financeiro.css';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

$colunasStatus = ['Novo', 'Contato Agendado', 'Experimental', 'Convertido', 'Perdido'];
$leadsPorStatus = array_fill_keys($colunasStatus, []);

foreach ($leads ?? [] as $lead) {
    $st = $lead['status'] ?? 'Novo';
    $leadsPorStatus[$st][] = $lead;
}

$columnClassMap = [
    'Novo' => 'crm-col-novo',
    'Contato Agendado' => 'crm-col-contato',
    'Experimental' => 'crm-col-experimental',
    'Convertido' => 'crm-col-convertido',
    'Perdido' => 'crm-col-perdido',
];

$totalLeads = count($leads ?? []);
?>

<link rel="stylesheet" href="/Gymflow/assets/css/css/jorge-financeiro.css">

<main class="conteudo crm-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>CRM / Quadro de Leads</h1>
            <p>Gerencie o funil de prospecção e conversão de potenciais alunos (<?= $totalLeads ?> lead(s) ativos).</p>
        </div>

        <div class="fin-header-actions">
            <a href="<?= $baseUrl ?>?acao=cadastrar" class="fin-btn fin-btn-primary">
                + Novo Lead
            </a>
        </div>
    </div>

    <!-- Quadro Kanban de Leads -->
    <div class="crm-kanban-board">
        <?php foreach ($colunasStatus as $coluna): ?>
            <?php 
                $colClass = $columnClassMap[$coluna] ?? 'crm-col-novo'; 
                $quantidade = count($leadsPorStatus[$coluna]);
            ?>
            <div class="crm-kanban-column <?= $colClass ?>">
                <div class="crm-column-header">
                    <h3 class="crm-column-title">
                        <?= htmlspecialchars($coluna) ?>
                    </h3>
                    <span class="crm-column-count"><?= $quantidade ?></span>
                </div>

                <div class="crm-column-cards">
                    <?php if ($quantidade === 0): ?>
                        <div class="crm-empty-state">
                            Nenhum lead nesta etapa
                        </div>
                    <?php else: ?>
                        <?php foreach ($leadsPorStatus[$coluna] as $lead): ?>
                            <div class="crm-lead-card">
                                <h4 class="crm-lead-name">
                                    <?= htmlspecialchars($lead['nome']) ?>
                                </h4>

                                <div class="crm-lead-details">
                                    <div class="crm-lead-detail-item">
                                        <span>📞</span>
                                        <strong><?= htmlspecialchars($lead['telefone'] ?? '-') ?></strong>
                                    </div>

                                    <?php if (!empty($lead['objetivo'])): ?>
                                        <div class="crm-lead-detail-item">
                                            <span class="crm-lead-tag">
                                                🎯 <?= htmlspecialchars($lead['objetivo']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($lead['campanha'])): ?>
                                        <div class="crm-lead-detail-item">
                                            <span class="crm-lead-tag">
                                                📢 <?= htmlspecialchars($lead['campanha']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="crm-lead-actions">
                                    <div class="crm-lead-links">
                                        <a href="<?= $baseUrl ?>?acao=editar&id=<?= $lead['id'] ?>">Editar</a>
                                        <span>•</span>
                                        <a
                                            href="<?= $baseUrl ?>?acao=excluir&id=<?= $lead['id'] ?>"
                                            class="crm-delete-link"
                                            onclick="return confirm('Deseja realmente excluir este lead?');"
                                        >
                                            Excluir
                                        </a>
                                    </div>
                                </div>

                                <form method="POST" action="<?= $baseUrl ?>?acao=atualizar_status" class="crm-status-form">
                                    <input type="hidden" name="id" value="<?= (int) $lead['id'] ?>">
                                    <select name="status" class="crm-status-select" onchange="this.form.submit()">
                                        <?php foreach ($colunasStatus as $st): ?>
                                            <option value="<?= $st ?>" <?= $lead['status'] === $st ? 'selected' : '' ?>>
                                                Mover: <?= $st ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>