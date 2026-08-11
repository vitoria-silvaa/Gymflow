<?php
if (!isset($plano)) {
    $id = $_GET['id'] ?? null;
    header("Location: /Gymflow/app/controllers/PlanoController.php?acao=editar" . ($id ? "&id=" . $id : ""));
    exit;
}
/** @var array $plano */
/** @var string $erro */

$tituloPagina = "Editar Plano";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<h1>Editar Plano</h1>
<a href="/Gymflow/app/controllers/PlanoController.php?acao=listar">Voltar</a>
<br><br>

<!-- Mensagem de Erro -->
<?php if (!empty($erro)): ?>
    <div style="color: red; font-weight: bold; margin-bottom: 15px;">
        <?php echo htmlspecialchars($erro); ?>
    </div>
<?php endif; ?>

<form action="/Gymflow/app/controllers/PlanoController.php?acao=editar&id=<?= $plano['id'] ?>" method="POST">
    
    <label>Nome do Plano *:</label><br>
    <input type="text" name="nome" value="<?php echo htmlspecialchars($plano['nome']); ?>" required>
    <br><br>

    <label>Categoria *:</label><br>
    <input type="text" name="categoria" value="<?php echo htmlspecialchars($plano['categoria']); ?>" required>
    <br><br>

    <label>Valor Mensal/Total (R$) *:</label><br>
    <input type="text" name="valor" value="<?php echo htmlspecialchars($plano['valor']); ?>" required>
    <br><br>

    <label>Duração *:</label><br>
    <select name="duracao" required>
        <option value="">Selecione...</option>
        <option value="1 Mês" <?php if($plano['duracao'] === '1 Mês') echo 'selected'; ?>>1 Mês</option>
        <option value="3 Meses" <?php if($plano['duracao'] === '3 Meses') echo 'selected'; ?>>3 Meses</option>
        <option value="6 Meses" <?php if($plano['duracao'] === '6 Meses') echo 'selected'; ?>>6 Meses</option>
        <option value="1 Ano" <?php if($plano['duracao'] === '1 Ano') echo 'selected'; ?>>1 Ano</option>
    </select>
    <br><br>

    <button type="submit">Salvar Alterações</button>
    <a href="/Gymflow/app/controllers/PlanoController.php?acao=listar">Cancelar</a>

</form>

<?php include __DIR__ . '/../shared/footer.php'; ?>
