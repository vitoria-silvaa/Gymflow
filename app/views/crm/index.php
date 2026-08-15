<?php
if (!isset($tituloPagina)) { header("Location: /Gymflow/app/controllers/CrmController.php"); exit; }

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

$colunasStatus = ['Novo', 'Contato Agendado', 'Experimental', 'Convertido', 'Perdido'];
$leadsPorStatus = array_fill_keys($colunasStatus, []);

foreach ($leads ?? [] as $lead) {
    $st = $lead['status'] ?? 'Novo';
    $leadsPorStatus[$st][] = $lead;
}
?>

<h1>CRM / Quadro de Leads</h1>

<p><a href="<?= $baseUrl ?>?acao=cadastrar">+ Novo Lead</a></p>

<div style="display: flex; gap: 15px; overflow-x: auto;">
    <?php foreach ($colunasStatus as $coluna): ?>
        <div style="flex: 1; min-width: 200px; border: 1px solid #ccc; padding: 10px; background: #f9f9f9; border-radius: 4px;">
            <h3 style="margin: 0 0 10px 0; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                <?= $coluna ?> (<?= count($leadsPorStatus[$coluna]) ?>)
            </h3>

            <?php foreach ($leadsPorStatus[$coluna] as $lead): ?>
                <div style="border: 1px solid #ddd; padding: 8px; margin-bottom: 8px; background: #fff; border-radius: 4px;">
                    <p style="margin: 0 0 4px 0;"><strong>Nome:</strong> <?= htmlspecialchars($lead['nome']) ?></p>
                    <p style="margin: 0 0 4px 0;"><strong>Tel:</strong> <?= htmlspecialchars($lead['telefone'] ?? '-') ?></p>
                    <?php if (!empty($lead['objetivo'])): ?><p style="margin: 0 0 4px 0;"><strong>Objetivo:</strong> <?= htmlspecialchars($lead['objetivo']) ?></p><?php endif; ?>
                    <?php if (!empty($lead['campanha'])): ?><p style="margin: 0 0 4px 0;"><strong>Campanha:</strong> <?= htmlspecialchars($lead['campanha']) ?></p><?php endif; ?>

                    <p style="margin: 6px 0;">
                        <a href="<?= $baseUrl ?>?acao=editar&id=<?= $lead['id'] ?>">Editar</a> |
                        <a href="<?= $baseUrl ?>?acao=excluir&id=<?= $lead['id'] ?>" onclick="return confirm('Excluir este lead?');">Excluir</a>
                    </p>

                    <form method="POST" action="<?= $baseUrl ?>?acao=atualizar_status" style="margin: 0;">
                        <input type="hidden" name="id" value="<?= $lead['id'] ?>">
                        <select name="status" onchange="this.form.submit()">
                            <?php foreach ($colunasStatus as $st): ?>
                                <option value="<?= $st ?>" <?= $lead['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>