<?php
// 1. Conexão com o banco de dados
include '../../../config/conexao.php';

// 2. Recebendo os dados da busca (filtros via GET)
$nome = "";
if (isset($_GET['nome_busca'])) {
    $nome = $_GET['nome_busca'];
}

$role = "";
if (isset($_GET['role_busca'])) {
    $role = $_GET['role_busca'];
}

// 3. Montando a consulta SQL (exclui o perfil 'Aluno')
$sql = "SELECT id, name, email, role FROM users WHERE role != 'Aluno'";

if ($nome != "") {
    $sql = $sql . " AND name LIKE :nome";
}

if ($role != "") {
    $sql = $sql . " AND role = :role";
}

$sql = $sql . " ORDER BY id DESC";

// 4. Executando a consulta com PDO
$stmt = $pdo->prepare($sql);

if ($nome != "") {
    $stmt->bindValue(':nome', '%' . $nome . '%');
}
if ($role != "") {
    $stmt->bindValue(':role', $role);
}

$stmt->execute();
$funcionarios = $stmt->fetchAll();

// 5. Inclusão do cabeçalho e menu lateral
$tituloPagina = "Funcionários";
include '../shared/header.php';
include '../shared/sidebar.php';
?>

<!-- Título principal -->
<h1>Gestão de Funcionários / Colaboradores</h1>

<!-- Formulário de Busca Simples -->
<form action="" method="GET">
    <label>Nome:</label>
    <input type="text" name="nome_busca" value="<?php echo htmlspecialchars($nome); ?>">

    <label>Cargo / Perfil (Role):</label>
    <select name="role_busca">
        <option value="">Todos</option>
        <option value="Admin" <?php if ($role == 'Admin') { echo 'selected'; } ?>>Administrador</option>
        <option value="Professor" <?php if ($role == 'Professor') { echo 'selected'; } ?>>Instrutor / Professor</option>
        <option value="Recepcao" <?php if ($role == 'Recepcao') { echo 'selected'; } ?>>Recepção</option>
    </select>

    <button type="submit">Buscar</button>
</form>

<br>
<a href="cadastrar.php"> + Novo Funcionário</a>
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
    // Se não encontrou funcionários cadastrados
    if (count($funcionarios) == 0) {
    ?>
        <tr>
            <td colspan="6" align="center">Nenhum funcionário encontrado.</td>
        </tr>
    <?php
    } else {
        // Exibe cada funcionário encontrado na tabela
        foreach ($funcionarios as $func) {
        ?>
            <tr>
                <td><?php echo htmlspecialchars($func['id']); ?></td>
                <td><?php echo htmlspecialchars($func['name']); ?></td>
                <td><?php echo htmlspecialchars($func['email']); ?></td>
                <td><?php echo htmlspecialchars($func['role']); ?></td>
                <td>Ativo</td>
                <td>
                    <a href="visualizar.php?id=<?php echo $func['id']; ?>">Ver</a> |
                    <a href="editar.php?id=<?php echo $func['id']; ?>">Editar</a>
                </td>
            </tr>
        <?php
        }
    }
    ?>
</table>

<?php include '../shared/footer.php'; ?>