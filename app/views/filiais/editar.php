<?php
require_once __DIR__ . '/../../../config/conexao.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ./index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_post     = $_POST['id'] ?? null;
    $nome        = $_POST['nome'] ?? '';
    $cnpj        = $_POST['cnpj'] ?? '';
    $telefone    = $_POST['telefone'] ?? '';
    $responsavel = $_POST['responsavel'] ?? '';

    if ($id_post && !empty($nome) && !empty($cnpj) && !empty($responsavel)) {
        
        $sql = "UPDATE filiais 
                SET nome = :nome, 
                    cnpj = :cnpj, 
                    telefone = :telefone, 
                    responsavel = :responsavel 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':nome'        => $nome,
            ':cnpj'        => $cnpj,
            ':telefone'    => $telefone,
            ':responsavel' => $responsavel,
            ':id'          => $id_post
        ]);

        header("Location: ./index.php");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM filiais WHERE id = :id");
$stmt->execute([':id' => $id]);
$filial = $stmt->fetch();

if (!$filial) {
    header("Location: ./index.php");
    exit;
}
?>

<h1>Editar Filial</h1>
<a href="./index.php">Voltar</a>

<form action="./editar.php?id=<?= $filial['id'] ?>" method="POST">
    
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
    <a href="./index.php">Cancelar</a>
</form>