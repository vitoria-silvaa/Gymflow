<?php
if (!isset($alunos)) {
    header(
        'Location: /Gymflow/app/controllers/AlunoController.php?acao=listar'
    );
    exit;
}
/** @var string $status */
/** @var string $cpf */
/** @var array<int, array<string, mixed>> $alunos */
/** @var string $baseUrl */
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<h1>Alunos</h1>

<a href="<?= $baseUrl ?>?acao=cadastrar">
    Cadastrar Aluno
</a>

<br><br>

<form action="<?= $baseUrl ?>" method="GET">
    <input type="hidden" name="acao" value="listar">

    <label>CPF:</label>
    <input
        type="text"
        name="cpf"
        value="<?= htmlspecialchars($cpf) ?>">

    <label>Status:</label>
    <select name="status">
        <option value="">Todos</option>
        <option
            value="Ativo"
            <?= $status === 'Ativo' ? 'selected' : '' ?>>
            Ativo
        </option>
        <option
            value="Inativo"
            <?= $status === 'Inativo' ? 'selected' : '' ?>>
            Inativo
        </option>
    </select>

    <button type="submit">Buscar</button>
</form>

<br>

<table border="1" width="100%" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>CPF</th>
        <th>Filial</th>
        <th>Status</th>
        <th>Ações</th>
    </tr>

    <?php if (count($alunos) === 0): ?>
        <tr>
            <td colspan="6">Nenhum aluno encontrado.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($alunos as $aluno): ?>
            <tr>
                <td><?= htmlspecialchars($aluno['id']) ?></td>
                <td><?= htmlspecialchars($aluno['nome']) ?></td>
                <td><?= htmlspecialchars($aluno['cpf']) ?></td>
                <td><?= htmlspecialchars($aluno['nome_filial']) ?></td>
                <td><?= htmlspecialchars($aluno['status']) ?></td>
                <td>
                    <a
                        href="<?= $baseUrl ?>?acao=visualizar&id=<?= $aluno['id'] ?>">
                        Ver
                    </a>

                    |

                    <a href="<?= $baseUrl ?>?acao=editar&id=<?= $aluno['id'] ?>">
                        Editar
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<?php include __DIR__ . '/../shared/footer.php'; ?>