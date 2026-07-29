<?php
// 1. Conexão com o banco de dados
include '../../../config/conexao.php';

$erro = "";

// 2. Processar o formulário quando enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $rg = trim($_POST['rg'] ?? '');
    $sexo = $_POST['sexo'] ?? '';
    $nascimento = $_POST['data_nascimento'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $id_filial = $_POST['id_filial'] ?? '';
    
    $cep = trim($_POST['cep'] ?? '');
    $logradouro = trim($_POST['logradouro'] ?? '');
    $endereco = "";
    if ($logradouro !== '' || $cep !== '') {
        $endereco = $logradouro . ($cep !== '' ? ", CEP: " . $cep : "");
    }
    
    // Validação básica dos campos obrigatórios
    if (empty($nome) || empty($cpf) || empty($nascimento) || empty($sexo) || empty($email) || empty($telefone) || empty($id_filial)) {
        $erro = "Por favor, preencha todos os campos obrigatórios (*).";
    } else {
        // Verificar se o CPF já está cadastrado
        $stmt_check = $pdo->prepare("SELECT id FROM alunos WHERE cpf = :cpf");
        $stmt_check->execute([':cpf' => $cpf]);
        
        if ($stmt_check->fetch()) {
            $erro = "Este CPF já está cadastrado para outro aluno.";
        } else {
            // Inserir o novo aluno no banco de dados
            $sql = "INSERT INTO alunos (filial_id, nome, cpf, rg, sexo, nascimento, email, telefone, endereco, status) 
                    VALUES (:filial_id, :nome, :cpf, :rg, :sexo, :nascimento, :email, :telefone, :endereco, 'Ativo')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':filial_id'  => $id_filial,
                ':nome'       => $nome,
                ':cpf'        => $cpf,
                ':rg'         => !empty($rg) ? $rg : null,
                ':sexo'       => $sexo,
                ':nascimento' => $nascimento,
                ':email'      => $email,
                ':telefone'   => $telefone,
                ':endereco'   => !empty($endereco) ? $endereco : null
            ]);
            
            // Redireciona para a lista de alunos
            header("Location: index.php");
            exit;
        }
    }
}

// 3. Buscar as filiais cadastradas para popular o campo select dinamicamente
$stmt_filiais = $pdo->query("SELECT id, nome FROM filiais WHERE ativo = 1 ORDER BY nome");
$filiais = $stmt_filiais->fetchAll();

// 4. Inclusão do cabeçalho e menu lateral
$tituloPagina = "Cadastrar Aluno";
include '../shared/header.php';
include '../shared/sidebar.php';
?>

<!-- Título principal -->
<h1>Cadastrar Novo Aluno</h1>

<!-- Exibição de mensagens de erro -->
<?php if (!empty($erro)): ?>
    <div style="color: red; font-weight: bold; margin-bottom: 15px;">
        <?php echo htmlspecialchars($erro); ?>
    </div>
<?php endif; ?>

<form action="" method="POST">
    
    <!-- Seção 1: Dados Pessoais -->
    <h3>Dados Pessoais</h3>
    
    <label>Nome Completo *:</label>
    <input type="text" name="nome" value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required>
    <br><br>

    <label>CPF *:</label>
    <input type="text" name="cpf" placeholder="000.000.000-00" value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>" required>
    <br><br>

    <label>RG:</label>
    <input type="text" name="rg" value="<?php echo htmlspecialchars($_POST['rg'] ?? ''); ?>">
    <br><br>

    <label>Sexo *:</label>
    <select name="sexo" required>
        <option value="">Selecione...</option>
        <option value="Masculino" <?php if(($_POST['sexo'] ?? '') === 'Masculino') echo 'selected'; ?>>Masculino</option>
        <option value="Feminino" <?php if(($_POST['sexo'] ?? '') === 'Feminino') echo 'selected'; ?>>Feminino</option>
        <option value="Outro" <?php if(($_POST['sexo'] ?? '') === 'Outro') echo 'selected'; ?>>Outro</option>
    </select>
    <br><br>
    
    <label>Data de Nascimento *:</label>
    <input type="date" name="data_nascimento" value="<?php echo htmlspecialchars($_POST['data_nascimento'] ?? ''); ?>" required>
    <br><br>

    <!-- Seção de Contatos -->
    <h3>Contatos</h3>
    
    <label>E-mail *:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
    <br><br>

    <label>Telefone *:</label>
    <input type="text" name="telefone" placeholder="(00) 00000-0000" value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>" required>
    <br><br>
    
    <hr>

    <!-- Seção 2: Endereço -->
    <h3>Endereço</h3>
    
    <label>CEP:</label>
    <input type="text" name="cep" placeholder="00000-000" value="<?php echo htmlspecialchars($_POST['cep'] ?? ''); ?>">
    <br><br>

    <label>Logradouro:</label>
    <input type="text" name="logradouro" placeholder="Rua, número, bairro" value="<?php echo htmlspecialchars($_POST['logradouro'] ?? ''); ?>">
    <br><br>

    <hr>

    <!-- Seção 3: Vínculo de Unidade -->
    <h3>Vínculo de Unidade</h3>
    
    <label>Selecione a Unidade (Filial) *:</label>
    <select name="id_filial" required>
        <option value="">Selecione uma filial...</option>
        <?php foreach ($filiais as $filial): ?>
            <option value="<?php echo $filial['id']; ?>" <?php if(($_POST['id_filial'] ?? '') == $filial['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($filial['nome']); ?> (ID: <?php echo $filial['id']; ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <button type="submit">Salvar Cadastro</button>
    <a href="index.php">Cancelar</a>

</form>

<?php include '../shared/footer.php'; ?>