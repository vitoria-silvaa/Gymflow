<?php
if (!isset($funcionario)) {
    $id = $_GET['id'] ?? null;
    header("Location: /Gymflow/app/controllers/FuncionarioController.php?acao=visualizar" . ($id ? "&id=" . $id : ""));
    exit;
}
/** @var array $funcionario */
/** @var array|false $filial_vinculada */

$tituloPagina = "Visualizar Funcionário";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<h1>Detalhes do Funcionário</h1>
<a href="/Gymflow/app/controllers/FuncionarioController.php?acao=listar">Voltar para a Lista</a>

<br><br>

<section style="background-color: #f9f9f9; padding: 20px; border-radius: 5px; border: 1px solid #ddd; max-width: 600px;">
    <p><strong>ID:</strong> <?php echo htmlspecialchars($funcionario['id']); ?></p>
    <p><strong>Nome Completo:</strong> <?php echo htmlspecialchars($funcionario['name']); ?></p>
    <p><strong>E-mail:</strong> <?php echo htmlspecialchars($funcionario['email']); ?></p>
    <p><strong>Perfil de Acesso (Cargo):</strong> <?php echo htmlspecialchars($funcionario['role']); ?></p>
        <?php if ($filial_vinculada): ?>
            <?= htmlspecialchars($filial_vinculada['nome']) ?>
            <?= !empty($filial_vinculada['cnpj']) ? '(CNPJ: ' . htmlspecialchars($filial_vinculada['cnpj']) . ')' : '' ?>
        <?php else: ?>
            Nenhuma
        <?php endif; ?>
    </p>
</section>

<br>
<a href="/Gymflow/app/controllers/FuncionarioController.php?acao=editar&id=<?php echo $funcionario['id']; ?>">Editar Cadastro</a>

<?php include __DIR__ . '/../shared/footer.php'; ?>
