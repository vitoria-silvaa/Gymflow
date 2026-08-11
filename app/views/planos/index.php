<?php
if (!isset($planos)) {
    header("Location: /Gymflow/app/controllers/PlanoController.php?acao=listar");
    exit;
}
/** @var array $planos */

$tituloPagina = "Planos de Acesso";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<!-- Título principal -->
<h1>Gestão de Planos de Acesso</h1>
<p>Cadastre e gerencie os pacotes e mensalidades oferecidos na rede.</p>

<br>
<a href="/Gymflow/app/controllers/PlanoController.php?acao=cadastrar"> + Novo Plano</a>
<br><br>

<!-- Tabela de Listagem -->
<table border="1" width="100%" cellpadding="8" style="border-collapse: collapse;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>ID</th>
            <th>Nome do Plano</th>
            <th>Categoria</th>
            <th>Valor</th>
            <th>Duração</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($planos) == 0): ?>
            <tr>
                <td colspan="6" align="center">Nenhum plano cadastrado no sistema.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($planos as $plano): ?>
                <tr>
                    <td align="center"><?php echo htmlspecialchars($plano['id']); ?></td>
                    <td><strong><?php echo htmlspecialchars($plano['nome']); ?></strong></td>
                    <td><?php echo htmlspecialchars($plano['categoria']); ?></td>
                    <td>R$ <?php echo number_format($plano['valor'], 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($plano['duracao']); ?></td>
                    <td align="center">
                        <a href="/Gymflow/app/controllers/PlanoController.php?acao=editar&id=<?php echo $plano['id']; ?>">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../shared/footer.php'; ?>