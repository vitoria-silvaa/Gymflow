<?php
if (!isset($erro)) {
    header("Location: /Gymflow/app/controllers/PlanoController.php?acao=cadastrar");
    exit;
}
/** @var string $erro */

$tituloPagina = "Cadastrar Plano";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<h1>Cadastrar Novo Plano</h1>
<a href="/Gymflow/app/controllers/PlanoController.php?acao=listar">Voltar</a>
<br><br>

<!-- Mensagem de Erro -->
<?php if (!empty($erro)): ?>
    <div style="color: red; font-weight: bold; margin-bottom: 15px;">
        <?php echo htmlspecialchars($erro); ?>
    </div>
<?php endif; ?>

<form action="/Gymflow/app/controllers/PlanoController.php?acao=cadastrar" method="POST">
    
    <label>Nome do Plano *:</label><br>
    <input type="text" name="nome" placeholder="Ex: Plano Trimestral Black" value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required>
    <br><br>

    <label>Categoria (Modalidade principal) *:</label><br>
    <input type="text" name="categoria" placeholder="Ex: Musculação / Pilates" value="<?php echo htmlspecialchars($_POST['categoria'] ?? ''); ?>" required>
    <br><br>

    <label>Valor Mensal/Total (R$) *:</label><br>
    <input type="text" name="valor" placeholder="99.90" value="<?php echo htmlspecialchars($_POST['valor'] ?? ''); ?>" required>
    <br><br>

    <label>Duração *:</label><br>
    <select name="duracao" required>
        <option value="">Selecione...</option>
        <option value="1 Mês" <?php if(($_POST['duracao'] ?? '') === '1 Mês') echo 'selected'; ?>>1 Mês</option>
        <option value="3 Meses" <?php if(($_POST['duracao'] ?? '') === '3 Meses') echo 'selected'; ?>>3 Meses</option>
        <option value="6 Meses" <?php if(($_POST['duracao'] ?? '') === '6 Meses') echo 'selected'; ?>>6 Meses</option>
        <option value="1 Ano" <?php if(($_POST['duracao'] ?? '') === '1 Ano') echo 'selected'; ?>>1 Ano</option>
    </select>
    <br><br>

    <button type="submit">Salvar Plano</button>
    <a href="/Gymflow/app/controllers/PlanoController.php?acao=listar">Cancelar</a>

</form>

<?php include __DIR__ . '/../shared/footer.php'; ?>
