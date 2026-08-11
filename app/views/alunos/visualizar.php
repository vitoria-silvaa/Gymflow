<?php

/** @var array<string, mixed> $aluno */
/** @var array<string, mixed>|false $matricula */
/** @var array<int, array<string, mixed>> $contas */
/** @var array<int, array<string, mixed>> $planos */
/** @var string $baseUrl */
/** @var string $erro */
/** @var string $sucesso */
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<h1>Dados do Aluno</h1>

<a href="<?= $baseUrl ?>?acao=listar">Voltar</a>

<?php if ($erro !== ''): ?>
    <p style="color: red; font-weight: bold;">
        <?= htmlspecialchars($erro) ?>
    </p>
<?php endif; ?>

<?php if ($sucesso !== ''): ?>
    <p style="color: green; font-weight: bold;">
        <?= htmlspecialchars($sucesso) ?>
    </p>
<?php endif; ?>

<h3>Dados Cadastrais</h3>

<p><strong>Nome:</strong> <?= htmlspecialchars($aluno['nome']) ?></p>
<p><strong>CPF:</strong> <?= htmlspecialchars($aluno['cpf']) ?></p>
<p><strong>RG:</strong> <?= htmlspecialchars($aluno['rg'] ?? '') ?></p>
<p><strong>Sexo:</strong> <?= htmlspecialchars($aluno['sexo']) ?></p>

<p>
    <strong>Nascimento:</strong>
    <?= date('d/m/Y', strtotime($aluno['nascimento'])) ?>
</p>

<p><strong>E-mail:</strong> <?= htmlspecialchars($aluno['email']) ?></p>
<p><strong>Telefone:</strong> <?= htmlspecialchars($aluno['telefone']) ?></p>
<p><strong>Endereço:</strong> <?= htmlspecialchars($aluno['endereco'] ?? '') ?></p>
<p><strong>Filial:</strong> <?= htmlspecialchars($aluno['nome_filial']) ?></p>
<p><strong>Status:</strong> <?= htmlspecialchars($aluno['status']) ?></p>

<a href="<?= $baseUrl ?>?acao=editar&id=<?= $aluno['id'] ?>">
    Editar
</a>

<hr>

<h3>Matrícula</h3>

<?php if ($matricula): ?>
    <p>
        <strong>Plano:</strong>
        <?= htmlspecialchars($matricula['nome_plano']) ?>
    </p>

    <p>
        <strong>Período:</strong>
        <?= date('d/m/Y', strtotime($matricula['inicio'])) ?>
        até
        <?= date('d/m/Y', strtotime($matricula['fim'])) ?>
    </p>

    <p>
        <strong>Valor:</strong>
        R$ <?= number_format($matricula['valor'], 2, ',', '.') ?>
    </p>
<?php else: ?>
    <p>O aluno não possui matrícula ativa.</p>

    <form action="<?= $baseUrl ?>?acao=matricular" method="POST">
        <input
            type="hidden"
            name="aluno_id"
            value="<?= $aluno['id'] ?>">

        <label>Plano:</label>
        <select name="plano_id" required>
            <option value="">Selecione</option>

            <?php foreach ($planos as $plano): ?>
                <option value="<?= $plano['id'] ?>">
                    <?= htmlspecialchars($plano['nome']) ?>
                    -
                    R$ <?= number_format($plano['valor'], 2, ',', '.') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label>Data de início:</label>
        <input
            type="date"
            name="data_inicio"
            value="<?= date('Y-m-d') ?>"
            required>
        <br><br>

        <label>Desconto:</label>
        <input type="text" name="desconto" value="0,00">
        <br><br>

        <button type="submit">Matricular</button>
    </form>
<?php endif; ?>

<hr>

<h3>Mensalidades</h3>

<table border="1" width="100%" cellpadding="6">
    <tr>
        <th>Vencimento</th>
        <th>Valor</th>
        <th>Status</th>
        <th>Pagamento</th>
        <th>Ação</th>
    </tr>

    <?php if (count($contas) === 0): ?>
        <tr>
            <td colspan="5">Nenhuma mensalidade encontrada.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($contas as $conta): ?>
            <tr>
                <td>
                    <?= date(
                        'd/m/Y',
                        strtotime($conta['vencimento'])
                    ) ?>
                </td>

                <td>
                    R$ <?= number_format(
                            $conta['valor'],
                            2,
                            ',',
                            '.'
                        ) ?>
                </td>

                <td><?= htmlspecialchars($conta['status']) ?></td>

                <td>
                    <?= htmlspecialchars(
                        $conta['forma_pagamento'] ?? 'Pendente'
                    ) ?>
                </td>

                <td>
                    <?php if ($conta['status'] === 'Aberto'): ?>
                        <form
                            action="<?= $baseUrl ?>?acao=pagar"
                            method="POST">
                            <input
                                type="hidden"
                                name="aluno_id"
                                value="<?= $aluno['id'] ?>">

                            <input
                                type="hidden"
                                name="conta_id"
                                value="<?= $conta['id'] ?>">

                            <button type="submit">
                                Registrar Pagamento
                            </button>
                        </form>
                    <?php else: ?>
                        Pago
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<?php include __DIR__ . '/../shared/footer.php'; ?>