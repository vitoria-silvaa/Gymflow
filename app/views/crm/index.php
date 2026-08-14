<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/CrmController.php");
    exit;
}

/** @var array $leads */
/** @var string $sucesso */
/** @var string $erro */
/** @var string $baseUrl */

$tituloPagina = "CRM";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

$colunasStatus = ['Novo', 'Contato Agendado', 'Experimental', 'Convertido', 'Perdido'];

$leadsPorStatus = [
    'Novo' => [],
    'Contato Agendado' => [],
    'Experimental' => [],
    'Convertido' => [],
    'Perdido' => []
];

if (!empty($leads)) {
    foreach ($leads as $lead) {
        $st = $lead['status'] ?? 'Novo';
        if (isset($leadsPorStatus[$st])) {
            $leadsPorStatus[$st][] = $lead;
        } else {
            $leadsPorStatus['Novo'][] = $lead;
        }
    }
}
?>

<h1>CRM / Quadro de Leads</h1>

<?php if (!empty($sucesso)): ?>
    <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold;">
        <?= htmlspecialchars($sucesso) ?>
    </div>
<?php endif; ?>

<?php if (!empty($erro)): ?>
    <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold;">
        <?= htmlspecialchars($erro) ?>
    </div>
<?php endif; ?>

<p>
    <a href="<?= htmlspecialchars($baseUrl ?? '/Gymflow/app/controllers/CrmController.php') ?>?acao=cadastrar" style="padding: 8px 12px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">
        + Novo Lead
    </a>
</p>

<br>

<div style="display: flex; gap: 15px; align-items: flex-start; overflow-x: auto;">
    <?php foreach ($colunasStatus as $coluna): ?>
        <div style="flex: 1; min-width: 200px; border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9; border-radius: 6px;">
            <h3 style="margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 8px;">
                <?= htmlspecialchars($coluna) ?> (<?= count($leadsPorStatus[$coluna]) ?>)
            </h3>

            <?php if (!empty($leadsPorStatus[$coluna])): ?>
                <?php foreach ($leadsPorStatus[$coluna] as $lead): ?>
                    <div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; background-color: #fff; border-radius: 4px;">
                        <p style="margin: 0 0 5px 0;"><strong>Nome:</strong> <?= htmlspecialchars($lead['nome']) ?></p>
                        <p style="margin: 0 0 5px 0;"><strong>Telefone:</strong> <?= htmlspecialchars($lead['telefone'] ?? '-') ?></p>
                        <p style="margin: 0 0 5px 0;"><strong>Objetivo:</strong> <?= htmlspecialchars($lead['objetivo'] ?? '-') ?></p>
                        <p style="margin: 0 0 10px 0;"><strong>Campanha:</strong> <?= htmlspecialchars($lead['campanha'] ?? '-') ?></p>

                        <form method="POST" action="<?= htmlspecialchars($baseUrl ?? '/Gymflow/app/controllers/CrmController.php') ?>?acao=atualizar_status" style="margin: 0;">
                            <input type="hidden" name="id" value="<?= (int) $lead['id'] ?>">
                            <label style="font-size: 12px;">Mudar Status:</label>
                            <br>
                            <select name="status" style="padding: 4px; margin-top: 4px;" onchange="this.form.submit()">
                                <?php foreach ($colunasStatus as $st): ?>
                                    <option value="<?= $st ?>" <?= ($lead['status'] === $st) ? 'selected' : '' ?>>
                                        <?= $st ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" style="padding: 4px 8px; font-size: 12px;">Alterar</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="font-size: 13px; color: #777;">Nenhum lead nesta área.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<br>
<p>Módulo de prospecção e novos clientes</p>

<?php include __DIR__ . '/../shared/footer.php'; ?>