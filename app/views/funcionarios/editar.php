<?php
if (!isset($funcionario)) {
    $id = $_GET['id'] ?? null;
    header("Location: /Gymflow/app/controllers/FuncionarioController.php?acao=editar" . ($id ? "&id=" . $id : ""));
    exit;
}
/** @var array $funcionario */
/** @var array $filial_vinculada */
/** @var array $filiais */
/** @var string $erro */

$tituloPagina = "Editar Funcionário";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<h1>Editar Funcionário</h1>
<a href="/Gymflow/app/controllers/FuncionarioController.php?acao=listar">Voltar</a>

<!-- Exibição de mensagens de erro -->
<?php if (!empty($erro)): ?>
    <div style="color: red; font-weight: bold; margin-bottom: 15px;">
        <?php echo htmlspecialchars($erro); ?>
    </div>
<?php endif; ?>

<form action="/Gymflow/app/controllers/FuncionarioController.php?acao=editar&id=<?= $funcionario['id'] ?>" method="POST">
    <input type="hidden" name="id" value="<?= $funcionario['id'] ?>">

    <h3>Dados do Colaborador</h3>

    <label>Nome Completo *:</label>
    <input type="text" name="nome" value="<?php echo htmlspecialchars($_POST['nome'] ?? $funcionario['name']); ?>" required>
    <br><br>

    <label>E-mail *:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? $funcionario['email']); ?>" required>
    <br><br>

    <label>Nova Senha (deixe em branco para manter a atual):</label>
    <input type="password" name="senha">
    <br><br>

    <label>Cargo / Perfil (Role) *:</label>
    <select name="role" required>
        <option value="">Selecione...</option>
        <option value="Admin" <?php if (($_POST['role'] ?? $funcionario['role']) === 'Admin') echo 'selected'; ?>>Administrador</option>
        <option value="Professor" <?php if (($_POST['role'] ?? $funcionario['role']) === 'Professor') echo 'selected'; ?>>Instrutor / Professor</option>
        <option value="Recepcao" <?php if (($_POST['role'] ?? $funcionario['role']) === 'Recepcao') echo 'selected'; ?>>Recepção</option>
    </select>
    <br><br>

    <hr>

    <h3>Vínculo de Unidade</h3>

    <label>Selecione a Unidade (Filial) *:</label>
    <select name="id_filial" required>
        <option value="">Selecione uma filial...</option>
        <?php 
        $filialSelecionadaId = $_POST['id_filial'] ?? ($filial_vinculada['id'] ?? 0);
        foreach ($filiais as $filial): 
        ?>
            <option value="<?php echo $filial['id']; ?>" <?php if ($filialSelecionadaId == $filial['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($filial['nome']); ?> (ID: <?php echo $filial['id']; ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <button type="submit">Salvar Alterações</button>
    <a href="/Gymflow/app/controllers/FuncionarioController.php?acao=listar">Cancelar</a>
</form>

<?php include __DIR__ . '/../shared/footer.php'; ?>
