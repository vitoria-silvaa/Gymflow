<?php
if (!isset($tituloPagina)) {
    header("Location: " . BASE_URL . "/app/controllers/CrmController.php");
    exit;
}

$cssEspecifico = BASE_URL . '/assets/css/jorge-financeiro.css';

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

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

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
            <div class="crm-kanban-column <?= $colClass ?>" ondragover="permitirDrop(event)" ondrop="soltar(event, '<?= $coluna ?>')">
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
                            <div class="crm-lead-card" draggable="true" ondragstart="iniciarDrag(event, <?= $lead['id'] ?>)" style="cursor: grab;">
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

                                <div class="crm-lead-actions" style="display: flex; flex-direction: column; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 12px;">
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap; width: 100%;">
                                        <?php 
                                            $whatsappNumber = preg_replace('/\D/', '', $lead['telefone'] ?? '');
                                            if (!empty($whatsappNumber)): 
                                                $wppMsg = '';
                                                $nomeLead = htmlspecialchars(explode(' ', trim($lead['nome'] ?? ''))[0]);
                                                switch (strtolower($coluna)) {
                                                    case 'novo':
                                                        $wppMsg = "Olá {$nomeLead}, tudo bem? Vimos o seu interesse pela GymFlow! Gostaria de saber mais sobre nossos planos e estrutura?";
                                                        break;
                                                    case 'contato agendado':
                                                        $wppMsg = "Olá {$nomeLead}, tudo bem? Estou entrando em contato para confirmarmos nossa conversa. Qual o melhor horário para você?";
                                                        break;
                                                    case 'experimental':
                                                        $wppMsg = "Olá {$nomeLead}, tudo pronto para sua aula experimental na GymFlow? Estamos te aguardando, não esqueça a garrafinha!";
                                                        break;
                                                    case 'convertido':
                                                        $wppMsg = "Olá {$nomeLead}, seja muito bem-vindo(a) à família GymFlow! Qualquer dúvida sobre o app ou treinos, estamos à disposição por aqui!";
                                                        break;
                                                    case 'perdido':
                                                        $wppMsg = "Olá {$nomeLead}, faz um tempo que não nos falamos. Sentimos sua falta! Temos uma condição especial caso decida treinar conosco, topa conversar?";
                                                        break;
                                                }
                                                $wppUrl = "https://wa.me/55{$whatsappNumber}?text=" . urlencode($wppMsg);
                                        ?>
                                            <a href="<?= $wppUrl ?>" target="_blank" style="display:inline-flex; align-items:center; gap:4px; background: #25D366; color: white; text-decoration: none; padding: 6px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; white-space: nowrap; flex: 1; justify-content: center;">
                                                <span>💬</span> WhatsApp
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($lead['email'])): ?>
                                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($lead['email']) ?>" target="_blank" style="display:inline-flex; align-items:center; gap:4px; background: #EA4335; color: white; text-decoration: none; padding: 6px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; white-space: nowrap; flex: 1; justify-content: center;">
                                                <span>✉️</span> E-mail
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="crm-lead-links" style="display: flex; justify-content: center; gap: 8px; width: 100%; font-size: 13px;">
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

    <!-- Form oculto para atualizar via Drag & Drop -->
    <form id="form-drag-drop" method="POST" action="<?= $baseUrl ?>?acao=atualizar_status" style="display: none;">
        <input type="hidden" name="id" id="dd_lead_id">
        <input type="hidden" name="status" id="dd_lead_status">
    </form>

</main>

<script>
    function permitirDrop(ev) {
        ev.preventDefault(); // Necessário para permitir o drop
    }

    function iniciarDrag(ev, leadId) {
        ev.dataTransfer.setData("leadId", leadId);
        ev.target.style.opacity = "0.5";
    }

    function soltar(ev, novoStatus) {
        ev.preventDefault();
        var leadId = ev.dataTransfer.getData("leadId");
        
        if (leadId) {
            document.getElementById('dd_lead_id').value = leadId;
            document.getElementById('dd_lead_status').value = novoStatus;
            document.getElementById('form-drag-drop').submit();
        }
    }

    document.addEventListener("dragend", function(ev) {
        ev.target.style.opacity = "1";
    });
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>