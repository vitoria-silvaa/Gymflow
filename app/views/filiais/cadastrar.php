<?php
if (!isset($erro)) {
    header("Location: /Gymflow/app/controllers/FilialController.php?acao=cadastrar");
    exit;
}
/** @var string $erro */
/** @var array $dados */

$tituloPagina = "Cadastrar Filial";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<h1>Nova Filial</h1>
<a href="/Gymflow/app/controllers/FilialController.php?acao=listar">Voltar</a>

<!-- Mensagem de Erro -->
<?php if (!empty($erro)): ?>
    <div style="color: red; font-weight: bold; margin-bottom: 15px;">
        <?php echo htmlspecialchars($erro); ?>
    </div>
<?php endif; ?>

<form action="" method="POST">
    <label for="nome">Nome da Filial</label><br>
    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($dados['nome'] ?? ''); ?>" required><br><br>

    <label for="cnpj">CNPJ</label><br>
    <input type="text" id="cnpj" name="cnpj" value="<?php echo htmlspecialchars($dados['cnpj'] ?? ''); ?>" required><br><br>

    <label for="telefone">Telefone</label><br>
    <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($dados['telefone'] ?? ''); ?>"><br><br>

    <label for="responsavel">Responsável Técnico</label><br>
    <input type="text" id="responsavel" name="responsavel" value="<?php echo htmlspecialchars($dados['responsavel'] ?? ''); ?>" required><br><br>

    <button type="submit">Criar Filial</button>
    <a href="/Gymflow/app/controllers/FilialController.php?acao=listar">Cancelar</a>
</form>

<?php include __DIR__ . '/../shared/footer.php'; ?>