<?php
// 1. Conexão com o banco de dados
include '../../../config/conexao.php';

$erro = "";

// 2. Processar o formulário quando enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $role = $_POST['role'] ?? '';
    $id_filial = $_POST['id_filial'] ?? '';

    // Validação básica dos campos obrigatórios
    if (empty($nome) || empty($email) || empty($senha) || empty($role) || empty($id_filial)) {
        $erro = "Por favor, preencha todos os campos obrigatórios (*).";
    } else {
        // Verificar se o E-mail já está cadastrado na tabela users
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt_check->execute([':email' => $email]);
        
        if ($stmt_check->fetch()) {
            $erro = "Este e-mail já está cadastrado.";
        } else {
            // Criptografar a senha usando bcrypt
            $senha_hash = password_hash($senha, PASSWORD_BCRYPT);

            // 1. Inserir na tabela de usuários (users)
            $sql_user = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
            $stmt_user = $pdo->prepare($sql_user);
            $stmt_user->execute([
                ':name'     => $nome,
                ':email'    => $email,
                ':password' => $senha_hash,
                ':role'     => $role
            ]);

            // Pega o ID do usuário que acabou de ser inserido
            $user_id = $pdo->lastInsertId();

            // 2. Inserir o vínculo com a filial na tabela user_filiais
            $sql_filial = "INSERT INTO user_filiais (user_id, filial_id) VALUES (:user_id, :filial_id)";
            $stmt_filial = $pdo->prepare($sql_filial);
            $stmt_filial->execute([
                ':user_id'   => $user_id,
                ':filial_id' => $id_filial
            ]);

            // Redireciona para a lista de funcionários
            header("Location: index.php");
            exit;
        }
    }
}

// 3. Buscar as filiais cadastradas para popular o campo select dinamicamente
$stmt_filiais = $pdo->query("SELECT id, nome FROM filiais WHERE ativo = 1 ORDER BY nome");
$filiais = $stmt_filiais->fetchAll();

// 4. Inclusão do cabeçalho e menu lateral
$tituloPagina = "Cadastrar Funcionário";
include '../shared/header.php';
include '../shared/sidebar.php';
?>

<!-- Título principal -->
<h1>Cadastrar Novo Funcionário</h1>

<!-- Exibição de mensagens de erro -->
<?php if (!empty($erro)): ?>
    <div style="color: red; font-weight: bold; margin-bottom: 15px;">
        <?php echo htmlspecialchars($erro); ?>
    </div>
<?php endif; ?>

<form action="" method="POST">
    
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
    <a href="index.php">Cancelar</a>

</form>

<?php include '../shared/footer.php'; ?>