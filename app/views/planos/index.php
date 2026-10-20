<?php
if (!isset($planos)) {
    header("Location: " . BASE_URL . "/app/controllers/PlanoController.php?acao=listar");
    exit;
}
/** @var array $planos */

$tituloPagina = "Planos de Acesso";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<main class="conteudo financeiro-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Gestão de Planos de Acesso</h1>
            <p>Cadastre e gerencie os pacotes e mensalidades oferecidos na rede.</p>
        </div>
        <div class="fin-header-actions">
            <a href="<?= BASE_URL ?>/app/controllers/PlanoController.php?acao=cadastrar" class="fin-btn fin-btn-primary">
                + Novo Plano
            </a>
        </div>
    </div>

    <!-- Tabela de Listagem -->
    <section class="fin-table-card">
        <div class="fin-table-responsive">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>Nome do Plano</th>
                        <th>Categoria</th>
                        <th>Valor</th>
                        <th>Duração</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($planos) == 0): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 32px; color: var(--fin-text-muted);">Nenhum plano cadastrado no sistema.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($planos as $plano): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($plano['nome']); ?></strong></td>
                                <td><span class="fin-badge fin-badge-aberto"><?= htmlspecialchars($plano['categoria']); ?></span></td>
                                <td><strong>R$ <?= number_format($plano['valor'], 2, ',', '.'); ?></strong></td>
                                <td><?= htmlspecialchars($plano['duracao']); ?></td>
                                <td style="text-align: center;">
                                    <a href="<?= BASE_URL ?>/app/controllers/PlanoController.php?acao=editar&id=<?= $plano['id']; ?>"
                                       title="Editar plano" aria-label="Editar plano <?= htmlspecialchars($plano['nome']); ?>"
                                       style="display: inline-flex; align-items: center; justify-content: center; color: var(--fin-primary);">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
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