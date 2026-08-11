<?php

/** @var int $id */
/** @var array<string, mixed> $dados */
/** @var array<int, array<string, mixed>> $filiais */
/** @var string $erro */
/** @var string $baseUrl */
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<h1>Editar Aluno</h1>

<?php if ($erro !== ''): ?>
    <p style="color: red; font-weight: bold;">
        <?= htmlspecialchars($erro) ?>
    </p>
<?php endif; ?>

<form
    action="<?= $baseUrl ?>?acao=editar&id=<?= $id ?>"
    method="POST">
    <input type="hidden" name="id" value="<?= $id ?>">

    <label>Nome *:</label>
    <input
        type="text"
        name="nome"
        value="<?= htmlspecialchars($dados['nome']) ?>"
        required>
    <br><br>

    <label>CPF *:</label>
    <input
        type="text"
        name="cpf"
        value="<?= htmlspecialchars($dados['cpf']) ?>"
        required>
    <br><br>

    <label>RG:</label>
    <input
        type="text"
        name="rg"
        value="<?= htmlspecialchars($dados['rg'] ?? '') ?>">
    <br><br>

    <label>Sexo *:</label>
    <select name="sexo" required>
        <option
            value="Masculino"
            <?= $dados['sexo'] === 'Masculino' ? 'selected' : '' ?>>
            Masculino
        </option>
        <option
            value="Feminino"
            <?= $dados['sexo'] === 'Feminino' ? 'selected' : '' ?>>
            Feminino
        </option>
        <option
            value="Outro"
            <?= $dados['sexo'] === 'Outro' ? 'selected' : '' ?>>
            Outro
        </option>
    </select>
    <br><br>

    <label>Data de Nascimento *:</label>
    <input
        type="date"
        name="nascimento"
        value="<?= htmlspecialchars($dados['nascimento']) ?>"
        required>
    <br><br>

    <label>E-mail *:</label>
    <input
        type="email"
        name="email"
        value="<?= htmlspecialchars($dados['email']) ?>"
        required>
    <br><br>

    <label>Telefone *:</label>
    <input
        type="text"
        name="telefone"
        value="<?= htmlspecialchars($dados['telefone']) ?>"
        required>
    <br><br>

    <label>Endereço:</label>
    <input
        type="text"
        name="endereco"
        value="<?= htmlspecialchars($dados['endereco'] ?? '') ?>">
    <br><br>

    <label>Filial *:</label>
    <select name="filial_id" required>
        <?php foreach ($filiais as $filial): ?>
            <option
                value="<?= $filial['id'] ?>"
                <?= $dados['filial_id'] == $filial['id']
                    ? 'selected'
                    : '' ?>>
                <?= htmlspecialchars($filial['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label>Status *:</label>
    <select name="status" required>
        <option
            value="Ativo"
            <?= $dados['status'] === 'Ativo' ? 'selected' : '' ?>>
            Ativo
        </option>
        <option
            value="Inativo"
            <?= $dados['status'] === 'Inativo' ? 'selected' : '' ?>>
            Inativo
        </option>
    </select>
    <br><br>

    <label>Nova Senha:</label>
    <input
        type="password"
        name="senha"
        minlength="6"
        placeholder="Deixe vazio para manter a senha">
    <br><br>

    <button type="submit">Salvar</button>

    <a
        href="<?= $baseUrl ?>?acao=visualizar&id=<?= $id ?>">
        Cancelar
    </a>
</form>

<?php include __DIR__ . '/../shared/footer.php'; ?>