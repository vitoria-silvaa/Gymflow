<?php
if (!isset($alunos)) {
    header("Location: " . BASE_URL . "/app/controllers/AlunoController.php?acao=listar");
    exit;
}
/** @var string $status */
/** @var string $cpf */
/** @var array<int, array<string, mixed>> $alunos */
/** @var string $baseUrl */
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<main class="conteudo financeiro-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Alunos</h1>
            <p>Gerencie todos os alunos cadastrados no sistema.</p>
        </div>
        <div class="fin-header-actions">
            <a href="<?= $baseUrl ?>?acao=cadastrar" class="fin-btn fin-btn-primary">
                + Novo Aluno
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <section class="fin-table-card" style="margin-bottom: 24px;">
        <form action="<?= $baseUrl ?>" method="GET" class="fin-form-grid" style="display: flex; gap: 16px; align-items: flex-end; padding: 20px;">
            <input type="hidden" name="acao" value="listar">

            <div class="fin-form-group" style="flex: 1;">
                <label>CPF</label>
                <input type="text" name="cpf" class="fin-input" placeholder="Buscar por CPF" value="<?= htmlspecialchars($cpf) ?>">
            </div>

            <div class="fin-form-group" style="flex: 1;">
                <label>Status</label>
                <select name="status" class="fin-select">
                    <option value="">Todos os status</option>
                    <option value="Ativo" <?= $status === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="Inativo" <?= $status === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>

            <div class="fin-form-group" style="flex: 1;">
                <label>Filial</label>
                <select name="filial_id" class="fin-select">
                    <option value="">Todas as filiais</option>
                    <?php foreach ($filiais ?? [] as $filial): ?>
                        <option value="<?= $filial['id'] ?>" <?= ($filial_id_filter ?? 0) == $filial['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($filial['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="fin-form-group">
                <button type="submit" class="fin-btn fin-btn-secondary">Filtrar</button>
            </div>
        </form>
    </section>

    <!-- Tabela -->
    <section class="fin-table-card">
        <div class="fin-table-responsive">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Filial</th>
                        <th>Status</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($alunos) === 0): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 32px; color: var(--fin-text-muted);">Nenhum aluno encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($alunos as $aluno): ?>
                            <tr>
                                <td><span class="fin-badge-categoria">#<?= htmlspecialchars($aluno['id']) ?></span></td>
                                <td><strong><?= htmlspecialchars($aluno['nome']) ?></strong></td>
                                <td><?= htmlspecialchars($aluno['cpf']) ?></td>
                                <td><?= htmlspecialchars($aluno['nome_filial']) ?></td>
                                <td>
                                    <?php if ($aluno['status'] === 'Ativo'): ?>
                                        <span class="fin-badge fin-badge-pago">Ativo</span>
                                    <?php else: ?>
                                        <span class="fin-badge fin-badge-aberto">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="fin-actions-dropdown" style="display: inline-flex; gap: 8px;">
                                        <?php 
                                            $whatsappNumber = preg_replace('/\D/', '', $aluno['telefone'] ?? '');
                                            if (!empty($whatsappNumber)): 
                                        ?>
                                            <a href="https://wa.me/55<?= $whatsappNumber ?>" target="_blank" class="fin-btn fin-btn-sm" style="background: #25D366; color: white;">💬 WhatsApp</a>
                                        <?php endif; ?>

                                        <?php if (!empty($aluno['email'])): ?>
                                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($aluno['email']) ?>" target="_blank" class="fin-btn fin-btn-sm" style="background: #EA4335; color: white;">✉️ E-mail</a>
                                        <?php endif; ?>
                                        <a href="<?= $baseUrl ?>?acao=visualizar&id=<?= $aluno['id'] ?>" class="fin-btn fin-btn-sm" style="background: #f1f5f9; color: #475569;">Ver</a>
                                        <a href="<?= $baseUrl ?>?acao=editar&id=<?= $aluno['id'] ?>" class="fin-btn fin-btn-primary fin-btn-sm">Editar</a>
                                    </div>
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