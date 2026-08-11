<?php
if (!isset($filial)) {
    $id = $_GET['id'] ?? null;
    header("Location: /Gymflow/app/controllers/FilialController.php?acao=editar" . ($id ? "&id=" . $id : ""));
    exit;
}
/** @var array $filial */
/** @var string $erro */

$tituloPagina = "Editar Filial";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<h1>Editar Filial</h1>
<a href="/Gymflow/app/controllers/FilialController.php?acao=listar">Voltar</a>

<!-- Mensagem de Erro -->
<?php if (!empty($erro)): ?>
    <div style="color: red; font-weight: bold; margin-bottom: 15px;">
        <?php echo htmlspecialchars($erro); ?>
    </div>
<?php endif; ?>

<form action="/Gymflow/app/controllers/FilialController.php?acao=editar&id=<?= $filial['id'] ?>" method="POST">
    
    <input type="hidden" name="id" value="<?= $filial['id'] ?>">

    <label for="nome">Nome da Filial</label><br>
    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($filial['nome']) ?>" required><br><br>

    <label for="cnpj">CNPJ</label><br>
    <input type="text" id="cnpj" name="cnpj" value="<?= htmlspecialchars($filial['cnpj']) ?>" required><br><br>

    <label for="telefone">Telefone</label><br>
    <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($filial['telefone']) ?>"><br><br>

    <label for="responsavel">Responsável Técnico</label><br>
    <input type="text" id="responsavel" name="responsavel" value="<?= htmlspecialchars($filial['responsavel']) ?>" required><br><br>

    <button type="submit">Salvar Alterações</button>
    <a href="/Gymflow/app/controllers/FilialController.php?acao=listar">Cancelar</a>
</form>

<?php include __DIR__ . '/../shared/footer.php'; ?>