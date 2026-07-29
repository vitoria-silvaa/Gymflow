<?php
// 1. Conexão com o banco de dados
include '../../../config/conexao.php';

// 2. Recebendo os dados da busca (filtros via GET)
$nome = "";
if (isset($_GET['nome_busca'])) {
    $nome = $_GET['nome_busca'];
}

$cpf = "";
if (isset($_GET['cpf_busca'])) {
    $cpf = $_GET['cpf_busca'];
}

$status = "";
if (isset($_GET['status_busca'])) {
    $status = $_GET['status_busca'];
}

// 3. Montando a consulta SQL
$sql = "SELECT * FROM alunos WHERE 1=1";

if ($nome != "") {
    $sql = $sql . " AND nome LIKE :nome";
}

if ($cpf != "") {
    $sql = $sql . " AND cpf LIKE :cpf";
}

if ($status != "") {
    $sql = $sql . " AND status = :status";
}

$sql = $sql . " ORDER BY id DESC";

// 4. Executando a consulta com PDO
$stmt = $pdo->prepare($sql);

if ($nome != "") {
    $stmt->bindValue(':nome', '%' . $nome . '%');
}
if ($cpf != "") {
    $stmt->bindValue(':cpf', '%' . $cpf . '%');
}
if ($status != "") {
    $stmt->bindValue(':status', $status);
}

$stmt->execute();
$alunos = $stmt->fetchAll();

// 5. Inclusão do cabeçalho e menu lateral
$tituloPagina = "Alunos";
include '../shared/header.php';
include '../shared/sidebar.php';
?>

<!-- Título principal -->
<h1>Gestão de Alunos</h1>

<!-- Formulário de Busca Simples -->
<form action="" method="GET">
    <label>Nome:</label>
    <input type="text" name="nome_busca" value="<?php echo htmlspecialchars($nome); ?>">

    <label>CPF:</label>
    <input type="text" name="cpf_busca" value="<?php echo htmlspecialchars($cpf); ?>">

    <label>Status:</label>
    <select name="status_busca">
        <option value="">Todos</option>
        <option value="Ativo" <?php if ($status == 'Ativo') { echo 'selected'; } ?>>Ativo</option>
        <option value="Inativo" <?php if ($status == 'Inativo') { echo 'selected'; } ?>>Inativo</option>
    </select>

    <button type="submit">Buscar</button>
</form>

<br>
<a href="cadastrar.php"> + Novo Aluno</a>
<br><br>

<!-- Tabela de Listagem -->
<table border="1" width="100%">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>CPF</th>
        <th>Status</th>
        <th>Ações</th>
    </tr>

    <?php
    // Se não encontrou alunos cadastrados
    if (count($alunos) == 0) {
    ?>
        <tr>
            <td colspan="5" align="center">Nenhum aluno encontrado.</td>
        </tr>
    <?php
    } else {
        // Exibe cada aluno encontrado na tabela
        foreach ($alunos as $aluno) {
        ?>
            <tr>
                <td><?php echo htmlspecialchars($aluno['id']); ?></td>
                <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                <td><?php echo htmlspecialchars($aluno['cpf']); ?></td>
                <td><?php echo htmlspecialchars($aluno['status']); ?></td>
                <td>
                    <a href="visualizar.php?id=<?php echo $aluno['id']; ?>">Ver</a> |
                    <a href="editar.php?id=<?php echo $aluno['id']; ?>">Editar</a>
                </td>
            </tr>
        <?php
        }
    }
    ?>
</table>

<?php include '../shared/footer.php'; ?>