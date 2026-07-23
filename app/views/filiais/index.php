<?php
require_once __DIR__ . '/../../../config/conexao.php';

$statusFiltro = $_GET['status'] ?? '';

if (!empty($statusFiltro)) {
    $sql = "SELECT * FROM filiais WHERE status = :status ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':status' => $statusFiltro]);
} else {
    $sql = "SELECT * FROM filiais ORDER BY id DESC";
    $stmt = $pdo->query($sql);
}

$filiais = $stmt->fetchAll(); 
?>

<?php
$tituloPagina = "Filiais";
include '../shared/header.php';
?>

<div>
    <h1>Filiais</h1>
    <p>Gerencie as unidades da sua rede</p>
</div>

<div>
    <form method="GET" action="./index.php" style="display: inline-block;">
        <select name="status" onchange="this.form.submit()">
            <option value="" <?= $statusFiltro === '' ? 'selected' : '' ?>>Todos</option>
            <option value="Ativa" <?= $statusFiltro === 'Ativa' ? 'selected' : '' ?>>Ativo</option>
            <option value="Inativa" <?= $statusFiltro === 'Inativa' ? 'selected' : '' ?>>Inativo</option>
        </select>
    </form>

    <a href="../filiais/cadastrar.php">Criar Filial</a>
</div>
<div class="cards-container">
    <?php if (empty($filiais)): ?>
        <p>Nenhuma filial encontrada no banco de dados.</p>
    <?php else: ?>
        
        <?php foreach ($filiais as $filial): ?>
            
            <div class="card-filial">
                <div class="card-header">
                    <h2><?= htmlspecialchars($filial['nome']) ?></h2>
                    <span class="badge <?= strtolower($filial['status'] ?? 'ativa') ?>">
                        <?= htmlspecialchars($filial['status'] ?? 'Ativa') ?>
                    </span>
                </div>

                <div class="card-body">
                    <p><strong>CNPJ:</strong> <?= htmlspecialchars($filial['cnpj']) ?></p>
                    <p><strong>Telefone:</strong> <?= htmlspecialchars($filial['telefone']) ?></p>
                    <p><strong>Responsável:</strong> <?= htmlspecialchars($filial['responsavel']) ?></p>
                </div>

                <div class="card-footer">
                    <a href="./editar.php?id=<?= $filial['id'] ?>">Editar</a>
                    <button type="button">Inativar</button>
                    <a href="#">Histórico</a>
                </div>
            </div>

        <?php endforeach; ?>
        
    <?php endif; ?>
</div>

<?php include '../shared/sidebar.php'; ?>

<?php include '../shared/footer.php'; ?>