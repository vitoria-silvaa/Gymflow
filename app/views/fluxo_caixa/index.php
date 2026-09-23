<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/FluxoCaixaController.php");
    exit;
}

$tituloPagina = "Fluxo de Caixa";
$cssEspecifico = '/Gymflow/assets/css/css/jorge-financeiro.css';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

$periodoAtual = $_GET['periodo'] ?? 'mensal';
$isResultadoPositivo = ($resultado ?? 0) >= 0;
?>

<link rel="stylesheet" href="/Gymflow/assets/css/css/jorge-financeiro.css">

<main class="conteudo fluxo-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Fluxo de Caixa & DRE</h1>
            <p>Controle financeiro consolidado com demonstrativo de receitas e despesas operacionais.</p>
        </div>

        <div class="fin-header-actions">
            <!-- Seletor de Período Diário/Mensal -->
            <div class="fin-tabs-container">
                <a
                    href="/Gymflow/app/controllers/FluxoCaixaController.php?periodo=diario"
                    class="fin-tab-item <?= $periodoAtual === 'diario' ? 'active' : '' ?>"
                >
                    Diário
                </a>

                <a
                    href="/Gymflow/app/controllers/FluxoCaixaController.php?periodo=mensal"
                    class="fin-tab-item <?= $periodoAtual !== 'diario' ? 'active' : '' ?>"
                >
                    Mensal
                </a>
            </div>

            <!-- Botão de Lançar Custo -->
            <a
                href="/Gymflow/app/controllers/FluxoCaixaController.php?acao=lancar"
                class="fin-btn fin-btn-primary"
            >
                + Lançar Custo
            </a>
        </div>
    </div>

    <!-- Cards de Métricas do Fluxo -->
    <section class="fin-metrics-grid">

        <div class="fin-metric-card success">
            <div class="fin-metric-top">
                <span class="fin-metric-label">Receitas</span>
                <div class="fin-metric-icon">↑</div>
            </div>
            <div class="fin-metric-value">
                R$ <?= number_format($totalReceitas ?? 0, 2, ',', '.') ?>
            </div>
            <p class="fin-metric-subtext">
                <?= (int) ($quantidadeReceitas ?? 0) ?> pagamentos recebidos
            </p>
        </div>

        <div class="fin-metric-card danger">
            <div class="fin-metric-top">
                <span class="fin-metric-label">Custos</span>
                <div class="fin-metric-icon">↓</div>
            </div>
            <div class="fin-metric-value">
                R$ <?= number_format($totalCustos ?? 0, 2, ',', '.') ?>
            </div>
            <p class="fin-metric-subtext">
                <?= (int) ($quantidadeCustos ?? 0) ?> despesas lançadas
            </p>
        </div>

        <div class="fin-metric-card <?= $isResultadoPositivo ? 'success' : 'danger' ?>">
            <div class="fin-metric-top">
                <span class="fin-metric-label">Resultado Líquido</span>
                <div class="fin-metric-icon">⚖</div>
            </div>
            <div class="fin-metric-value">
                R$ <?= number_format($resultado ?? 0, 2, ',', '.') ?>
            </div>
            <p class="fin-metric-subtext">
                Saldo: Receitas - Custos
            </p>
        </div>

    </section>

    <!-- DRE Simplificada -->
    <section class="fin-dre-card">
        <div class="fin-dre-header">
            <h2>Demonstração do Resultado do Exercício (DRE)</h2>
            <p>Resumo executivo do período selecionado (<?= htmlspecialchars(ucfirst($periodoAtual)) ?>)</p>
        </div>

        <div class="fin-dre-list">
            <div class="fin-dre-item receita">
                <div class="fin-dre-item-label">
                    <span class="fin-dre-item-tag">(+)</span>
                    <span>Receita Bruta de Mensalidades</span>
                </div>
                <div class="fin-dre-item-valor">
                    R$ <?= number_format($totalReceitas ?? 0, 2, ',', '.') ?>
                </div>
            </div>

            <div class="fin-dre-item custo">
                <div class="fin-dre-item-label">
                    <span class="fin-dre-item-tag">(-)</span>
                    <span>Total de Despesas & Custos Operacionais</span>
                </div>
                <div class="fin-dre-item-valor">
                    R$ <?= number_format($totalCustos ?? 0, 2, ',', '.') ?>
                </div>
            </div>

            <div class="fin-dre-item resultado <?= $isResultadoPositivo ? '' : 'negativo' ?>">
                <div class="fin-dre-item-label">
                    <span class="fin-dre-item-tag">(=)</span>
                    <span>Resultado Líquido do Período</span>
                </div>
                <div class="fin-dre-item-valor">
                    R$ <?= number_format($resultado ?? 0, 2, ',', '.') ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Tabela de Lançamentos de Custos -->
    <section class="fin-table-card">
        <div class="fin-table-header">
            <h2>Lançamentos de Custos</h2>
            <span style="font-size: 13px; color: var(--fin-text-muted);">
                <?= count($custos ?? []) ?> registro(s)
            </span>
        </div>

        <div class="fin-table-responsive">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th style="text-align: right;">Valor</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($custos)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 32px; color: var(--fin-text-muted);">
                                Nenhum custo lançado no período selecionado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($custos as $custo): ?>
                            <tr>
                                <td>
                                    <?= date('d/m/Y', strtotime($custo['data'])) ?>
                                </td>

                                <td>
                                    <strong><?= htmlspecialchars($custo['descricao']) ?></strong>
                                </td>

                                <td>
                                    <span class="fin-badge-categoria">
                                        <?= htmlspecialchars($custo['categoria']) ?>
                                    </span>
                                </td>

                                <td style="text-align: right; color: var(--fin-danger-dark); font-weight: 700;">
                                    - R$ <?= number_format($custo['valor'], 2, ',', '.') ?>
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