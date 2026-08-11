<?php
if (!isset($filiais)) {
    header("Location: /Gymflow/app/controllers/FilialController.php?acao=listar");
    exit;
}
/** @var array $filiais */
/** @var string $statusFiltro */

$tituloPagina = "Filiais";
include __DIR__ . '/../shared/header.php';
?>

<div>
    <h1>Filiais</h1>
    <p>Gerencie as unidades da sua rede</p>
</div>

<div>
    <form method="GET" action="/Gymflow/app/controllers/FilialController.php" style="display: inline-block;">
        <input type="hidden" name="acao" value="listar">
        <select name="status" onchange="this.form.submit()">
            <option value="" <?= $statusFiltro === '' ? 'selected' : '' ?>>Todos</option>
            <option value="Ativa" <?= $statusFiltro === 'Ativa' ? 'selected' : '' ?>>Ativo</option>
            <option value="Inativa" <?= $statusFiltro === 'Inativa' ? 'selected' : '' ?>>Inativo</option>
        </select>
    </form>

    <a href="/Gymflow/app/controllers/FilialController.php?acao=cadastrar">Criar Filial</a>
</div>
<div class="cards-container">
    <?php if (empty($filiais)): ?>
        <p>Nenhuma filial encontrada no banco de dados.</p>
    <?php else: ?>
        
        <?php foreach ($filiais as $filial): ?>
            
            <div class="card-filial">
                <div class="card-header">
                    <h2><?= htmlspecialchars($filial['nome']) ?></h2>
                    <span class="badge <?= $filial['ativo'] ? 'ativa' : 'inativa' ?>">
                        <?= $filial['ativo'] ? 'Ativa' : 'Inativa' ?>
                    </span>
                </div>

                <div class="card-body">
                    <p><strong>CNPJ:</strong> <?= htmlspecialchars($filial['cnpj']) ?></p>
                    <p><strong>Telefone:</strong> <?= htmlspecialchars($filial['telefone']) ?></p>
                    <p><strong>Responsável:</strong> <?= htmlspecialchars($filial['responsavel']) ?></p>
                </div>

                <div class="card-footer">
                    <a href="/Gymflow/app/controllers/FilialController.php?acao=editar&id=<?= $filial['id'] ?>">Editar</a>
                    <button type="button">Inativar</button>
                    <a href="#">Histórico</a>
                </div>
            </div>

        <?php endforeach; ?>
        
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../shared/sidebar.php'; ?>

<?php include __DIR__ . '/../shared/footer.php'; ?>