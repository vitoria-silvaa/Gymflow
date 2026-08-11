<?php
if (!isset($funcionarios)) {
    header("Location: /Gymflow/app/controllers/FuncionarioController.php?acao=listar");
    exit;
}
/** @var array $funcionarios */
/** @var string $nome_busca */
/** @var string $role_busca */

$tituloPagina = "Funcionários";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<!-- Título principal -->
<h1>Gestão de Funcionários / Colaboradores</h1>

<!-- Formulário de Busca Simples -->
<form action="/Gymflow/app/controllers/FuncionarioController.php" method="GET">
    <input type="hidden" name="acao" value="listar">
    <label>Nome:</label>
    <input type="text" name="nome_busca" value="<?php echo htmlspecialchars($nome_busca); ?>">

    <label>Cargo / Perfil (Role):</label>
    <select name="role_busca">
        <option value="">Todos</option>
        <option value="Admin" <?php if ($role_busca == 'Admin') { echo 'selected'; } ?>>Administrador</option>
        <option value="Professor" <?php if ($role_busca == 'Professor') { echo 'selected'; } ?>>Instrutor / Professor</option>
        <option value="Recepcao" <?php if ($role_busca == 'Recepcao') { echo 'selected'; } ?>>Recepção</option>
    </select>

    <button type="submit">Buscar</button>
</form>

<br>
<a href="/Gymflow/app/controllers/FuncionarioController.php?acao=cadastrar"> + Novo Funcionário</a>
<br><br>

<!-- Tabela de Listagem -->
<table border="1" width="100%">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>E-mail</th>
        <th>Perfil (Role)</th>
        <th>Status</th>
        <th>Ações</th>
    </tr>

    <?php
    if (count($funcionarios) == 0) {
    ?>
        <tr>
            <td colspan="6" align="center">Nenhum funcionário encontrado.</td>
        </tr>
    <?php
    } else {
        foreach ($funcionarios as $func) {
        ?>
            <tr>
                <td><?php echo htmlspecialchars($func['id']); ?></td>
                <td><?php echo htmlspecialchars($func['name']); ?></td>
                <td><?php echo htmlspecialchars($func['email']); ?></td>
                <td><?php echo htmlspecialchars($func['role']); ?></td>
                <td>Ativo</td>
                <td>
                    <a href="/Gymflow/app/controllers/FuncionarioController.php?acao=visualizar&id=<?php echo $func['id']; ?>">Ver</a> |
                    <a href="/Gymflow/app/controllers/FuncionarioController.php?acao=editar&id=<?php echo $func['id']; ?>">Editar</a>
                </td>
            </tr>
        <?php
        }
    }
    ?>
</table>

<?php include __DIR__ . '/../shared/footer.php'; ?>