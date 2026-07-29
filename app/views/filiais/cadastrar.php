<?php

// 1. Conexão com o banco de dados
include '../../../config/conexao.php';

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome        = $_POST['nome'] ?? '';
    $cnpj        = $_POST['cnpj'] ?? '';
    $telefone    = $_POST['telefone'] ?? '';
    $responsavel = $_POST['responsavel'] ?? '';
    $company_id  = 1; // ID da empresa padrão no banco
    $ativo       = 1; // 1 = Ativo (coluna BOOLEAN no banco)

    if (!empty($nome) && !empty($cnpj) && !empty($responsavel)) {

        $sql = "INSERT INTO filiais (company_id, nome, cnpj, telefone, responsavel, ativo) 
                VALUES (:company_id, :nome, :cnpj, :telefone, :responsavel, :ativo)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':company_id'  => $company_id,
            ':nome'        => $nome,
            ':cnpj'        => $cnpj,
            ':telefone'    => $telefone,
            ':responsavel' => $responsavel,
            ':ativo'       => $ativo
        ]);

        header("Location: ./index.php");
        exit;
    }
}
?>

<h1>Nova Filial</h1>
<a href="./index.php">Voltar</a>

<form action="" method="POST">
    <label for="nome">Nome da Filial</label><br>
    <input type="text" id="nome" name="nome" required><br><br>

    <label for="cnpj">CNPJ</label><br>
    <input type="text" id="cnpj" name="cnpj" required><br><br>

    <label for="telefone">Telefone</label><br>
    <input type="text" id="telefone" name="telefone"><br><br>

    <label for="responsavel">Responsável Técnico</label><br>
    <input type="text" id="responsavel" name="responsavel" required><br><br>

    <button type="submit">Criar Filial</button>
    <a href="./index.php">Cancelar</a>
</form>