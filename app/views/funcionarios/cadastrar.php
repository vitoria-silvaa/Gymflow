<?php
if (!isset($filiais)) {
    header("Location: /Gymflow/app/controllers/FuncionarioController.php?acao=cadastrar");
    exit;
}
/** @var array $filiais */
/** @var string $erro */

$tituloPagina = "Cadastrar Funcionário";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<!-- Título principal -->
<h1>Cadastrar Novo Funcionário</h1>

<!-- Exibição de mensagens de erro -->
<?php if (!empty($erro)): ?>
    <div style="color: red; font-weight: bold; margin-bottom: 15px;">
        <?php echo htmlspecialchars($erro); ?>
    </div>
<?php endif; ?>

<form action="/Gymflow/app/controllers/FuncionarioController.php?acao=cadastrar" method="POST">

    <!-- Dados do Colaborador -->
    <h3>Dados do Colaborador</h3>

    <label>Nome Completo *:</label>
    <input type="text" name="nome" value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required>
    <br><br>

    <label>E-mail *:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
    <br><br>

    <label>Senha *:</label>
    <input type="password" name="senha" required>
    <br><br>

    <label>Cargo / Perfil (Role) *:</label>
    <select name="role" required>
        <option value="">Selecione...</option>
        <option value="Admin" <?php if (($_POST['role'] ?? '') === 'Admin') echo 'selected'; ?>>Administrador</option>
        <option value="Professor" <?php if (($_POST['role'] ?? '') === 'Professor') echo 'selected'; ?>>Instrutor / Professor</option>
        <option value="Recepcao" <?php if (($_POST['role'] ?? '') === 'Recepcao') echo 'selected'; ?>>Recepção</option>
    </select>
    <br><br>

    <hr>

    <!-- Vínculo de Unidade -->
    <h3>Vínculo de Unidade</h3>

    <label>Selecione a Unidade (Filial) *:</label>
    <select name="id_filial" required>
        <option value="">Selecione uma filial...</option>
        <?php foreach ($filiais as $filial): ?>
            <option value="<?php echo $filial['id']; ?>" <?php if (($_POST['id_filial'] ?? '') == $filial['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($filial['nome']); ?> (ID: <?php echo $filial['id']; ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <button type="submit">Salvar Cadastro</button>
    <a href="/Gymflow/app/controllers/FuncionarioController.php?acao=listar">Cancelar</a>

</form>

<?php include __DIR__ . '/../shared/footer.php'; ?>