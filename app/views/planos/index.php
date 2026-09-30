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
                        <th>ID</th>
                        <th>Nome do Plano</th>
                        <th>Categoria</th>
                        <th>Valor</th>
                        <th>Duração</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($planos) == 0): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 32px; color: var(--fin-text-muted);">Nenhum plano cadastrado no sistema.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($planos as $plano): ?>
                            <tr>
                                <td><span class="fin-badge-categoria">#<?= htmlspecialchars($plano['id']); ?></span></td>
                                <td><strong><?= htmlspecialchars($plano['nome']); ?></strong></td>
                                <td><span class="fin-badge fin-badge-aberto"><?= htmlspecialchars($plano['categoria']); ?></span></td>
                                <td><strong>R$ <?= number_format($plano['valor'], 2, ',', '.'); ?></strong></td>
                                <td><?= htmlspecialchars($plano['duracao']); ?></td>
                                <td style="text-align: right;">
                                    <a href="<?= BASE_URL ?>/app/controllers/PlanoController.php?acao=editar&id=<?= $plano['id']; ?>" class="fin-btn fin-btn-primary fin-btn-sm">Editar</a>
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