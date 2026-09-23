<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/FinanceiroController.php");
    exit;
}

$tituloPagina = "Financeiro";
$cssEspecifico = '/Gymflow/assets/css/css/jorge-financeiro.css';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

$totalGeral = ($totalAberto ?? 0) + ($totalRecebido ?? 0);
?>

<link rel="stylesheet" href="/Gymflow/assets/css/css/jorge-financeiro.css">

<main class="conteudo financeiro-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Contas a Receber</h1>
            <p>Acompanhe e gerencie as mensalidades e pagamentos dos alunos da academia.</p>
        </div>
    </div>

    <!-- Cards de Resumo Financeiro -->
    <section class="fin-metrics-grid">

        <div class="fin-metric-card warning">
            <div class="fin-metric-top">
                <span class="fin-metric-label">Total em Aberto</span>
                <div class="fin-metric-icon">⏳</div>
            </div>
            <div class="fin-metric-value">
                R$ <?= number_format($totalAberto ?? 0, 2, ',', '.') ?>
            </div>
            <p class="fin-metric-subtext">
                Pendências a receber
            </p>
        </div>

        <div class="fin-metric-card success">
            <div class="fin-metric-top">
                <span class="fin-metric-label">Total Recebido</span>
                <div class="fin-metric-icon">✓</div>
            </div>
            <div class="fin-metric-value">
                R$ <?= number_format($totalRecebido ?? 0, 2, ',', '.') ?>
            </div>
            <p class="fin-metric-subtext">
                Mensalidades liquidadas
            </p>
        </div>

        <div class="fin-metric-card info">
            <div class="fin-metric-top">
                <span class="fin-metric-label">Total Previsto</span>
                <div class="fin-metric-icon">📊</div>
            </div>
            <div class="fin-metric-value">
                R$ <?= number_format($totalGeral, 2, ',', '.') ?>
            </div>
            <p class="fin-metric-subtext">
                Receita total projetada
            </p>
        </div>

    </section>

    <!-- Tabela de Contas -->
    <section class="fin-table-card">
        <div class="fin-table-header">
            <h2>Lançamentos de Matrículas</h2>
        </div>

        <div class="fin-table-responsive">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Matrícula</th>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($contas)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 32px; color: var(--fin-text-muted);">
                                Nenhuma conta a receber encontrada no momento.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contas as $conta): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($conta['aluno_nome']) ?></strong>
                                </td>

                                <td>
                                    <span class="fin-badge-categoria">#<?= htmlspecialchars($conta['matricula_id']) ?></span>
                                </td>

                                <td>
                                    <?= date('d/m/Y', strtotime($conta['vencimento'])) ?>
                                </td>

                                <td>
                                    <strong>R$ <?= number_format($conta['valor'], 2, ',', '.') ?></strong>
                                </td>

                                <td>
                                    <?php if ($conta['status'] === 'Pago'): ?>
                                        <span class="fin-badge fin-badge-pago">Pago</span>
                                    <?php else: ?>
                                        <span class="fin-badge fin-badge-aberto">Aberto</span>
                                    <?php endif; ?>
                                </td>

                                <td style="text-align: right;">
                                    <?php if ($conta['status'] !== 'Pago'): ?>
                                        <a
                                            href="/Gymflow/app/controllers/FinanceiroController.php?acao=baixar&id=<?= $conta['id'] ?>"
                                            class="fin-btn fin-btn-primary fin-btn-sm"
                                        >
                                            Baixar
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--fin-text-light);">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>