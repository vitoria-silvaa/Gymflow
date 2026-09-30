<?php
if (!isset($funcionarios)) {
    header("Location: " . BASE_URL . "/app/controllers/FuncionarioController.php?acao=listar");
    exit;
}
/** @var array $funcionarios */
/** @var string $nome_busca */
/** @var string $role_busca */

$tituloPagina = "Funcionários";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<main class="conteudo financeiro-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Gestão de Colaboradores</h1>
            <p>Gerencie a equipe e defina as permissões de acesso ao sistema.</p>
        </div>
        <div class="fin-header-actions">
            <a href="<?= BASE_URL ?>/app/controllers/FuncionarioController.php?acao=cadastrar" class="fin-btn fin-btn-primary">
                + Novo Funcionário
            </a>
        </div>
    </div>

    <!-- Formulário de Busca -->
    <section class="fin-table-card" style="margin-bottom: 24px;">
        <form action="<?= BASE_URL ?>/app/controllers/FuncionarioController.php" method="GET" class="fin-form-grid" style="display: flex; gap: 16px; align-items: flex-end; padding: 20px;">
            <input type="hidden" name="acao" value="listar">

            <div class="fin-form-group" style="flex: 1;">
                <label>Nome do Colaborador</label>
                <input type="text" name="nome_busca" class="fin-input" placeholder="Buscar por nome" value="<?php echo htmlspecialchars($nome_busca); ?>">
            </div>

            <div class="fin-form-group" style="flex: 1;">
                <label>Cargo / Perfil</label>
                <select name="role_busca" class="fin-select">
                    <option value="">Todos os cargos</option>
                    <option value="Admin" <?php if ($role_busca == 'Admin') { echo 'selected'; } ?>>Administrador</option>
                    <option value="Professor" <?php if ($role_busca == 'Professor') { echo 'selected'; } ?>>Instrutor / Professor</option>
                    <option value="Recepcao" <?php if ($role_busca == 'Recepcao') { echo 'selected'; } ?>>Recepção</option>
                </select>
            </div>

            <div class="fin-form-group">
                <button type="submit" class="fin-btn fin-btn-secondary">Buscar</button>
            </div>
        </form>
    </section>

    <!-- Tabela de Listagem -->
    <section class="fin-table-card">
        <div class="fin-table-responsive">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil (Role)</th>
                        <th>Status</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($funcionarios) == 0): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 32px; color: var(--fin-text-muted);">Nenhum funcionário encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($funcionarios as $func): ?>
                            <tr>
                                <td><span class="fin-badge-categoria">#<?php echo htmlspecialchars($func['id']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($func['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($func['email']); ?></td>
                                <td><span class="fin-badge fin-badge-aberto"><?php echo htmlspecialchars($func['role']); ?></span></td>
                                <td><span class="fin-badge fin-badge-pago">Ativo</span></td>
                                <td style="text-align: right;">
                                    <div class="fin-actions-dropdown" style="display: inline-flex; gap: 8px;">
                                        <a href="<?= BASE_URL ?>/app/controllers/FuncionarioController.php?acao=visualizar&id=<?php echo $func['id']; ?>" class="fin-btn fin-btn-sm" style="background: #f1f5f9; color: #475569;">Ver</a>
                                        <a href="<?= BASE_URL ?>/app/controllers/FuncionarioController.php?acao=editar&id=<?php echo $func['id']; ?>" class="fin-btn fin-btn-primary fin-btn-sm">Editar</a>
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