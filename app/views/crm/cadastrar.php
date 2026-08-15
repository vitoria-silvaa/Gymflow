<?php
if (!isset($tituloPagina)) { header("Location: /Gymflow/app/controllers/CrmController.php"); exit; }

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

$isEdicao = !empty($dados['id']);
$colunasStatus = ['Novo', 'Contato Agendado', 'Experimental', 'Convertido', 'Perdido'];
?>

<h1><?= $isEdicao ? 'Editar Lead' : 'Cadastrar Lead' ?></h1>

<?php if (!empty($erro)): ?>
    <p style="color: red; font-weight: bold;"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<form method="POST" action="<?= $baseUrl ?>?acao=<?= $isEdicao ? 'editar' : 'cadastrar' ?>">
    <?php if ($isEdicao): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($dados['id'] ?? '')) ?>">
    <?php endif; ?>

    <label>Filial *</label><br>
    <select name="filial_id" required>
        <option value="">Selecione</option>
        <?php foreach ($filiais ?? [] as $filial): ?>
            <option value="<?= $filial['id'] ?>" <?= ($dados['filial_id'] ?? 0) == $filial['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($filial['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label>Nome *</label><br>
    <input type="text" name="nome" value="<?= htmlspecialchars($dados['nome'] ?? '') ?>" required>
    <br><br>

    <label>Telefone *</label><br>
    <input type="text" name="telefone" value="<?= htmlspecialchars($dados['telefone'] ?? '') ?>" required>
    <br><br>

    <label>Objetivo</label><br>
    <input type="text" name="objetivo" value="<?= htmlspecialchars($dados['objetivo'] ?? '') ?>">
    <br><br>

    <label>Campanha</label><br>
    <input type="text" name="campanha" value="<?= htmlspecialchars($dados['campanha'] ?? '') ?>">
    <br><br>

    <?php if ($isEdicao): ?>
        <label>Status</label><br>
        <select name="status">
            <?php foreach ($colunasStatus as $st): ?>
                <option value="<?= $st ?>" <?= ($dados['status'] ?? 'Novo') === $st ? 'selected' : '' ?>><?= $st ?></option>
            <?php endforeach; ?>
        </select>
        <br><br>
    <?php endif; ?>

    <button type="submit"><?= $isEdicao ? 'Salvar' : 'Cadastrar' ?></button>
    <a href="<?= $baseUrl ?>" style="margin-left: 10px;">Cancelar</a>
</form>

<?php include __DIR__ . '/../shared/footer.php'; ?>