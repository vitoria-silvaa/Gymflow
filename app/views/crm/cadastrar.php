<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/CrmController.php");
    exit;
}

/** @var array $dados */
/** @var array $filiais */
/** @var string $erro */
/** @var string $baseUrl */

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<h1>Cadastrar Lead</h1>

<?php if (!empty($erro)): ?>
    <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold;">
        <?= htmlspecialchars($erro) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= htmlspecialchars($baseUrl ?? '/Gymflow/app/controllers/CrmController.php') ?>?acao=cadastrar">

    <label>Filial *</label>
    <br>
    <select name="filial_id" required>
        <option value="">Selecione a Filial</option>
        <?php if (!empty($filiais)): ?>
            <?php foreach ($filiais as $filial): ?>
                <option value="<?= $filial['id'] ?>" <?= (($dados['filial_id'] ?? 0) == $filial['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($filial['nome']) ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>

    <br><br>

    <label>Nome *</label>
    <br>
    <input
        type="text"
        name="nome"
        value="<?= htmlspecialchars($dados['nome'] ?? '') ?>"
        required>

    <br><br>

    <label>Telefone *</label>
    <br>
    <input
        type="text"
        name="telefone"
        value="<?= htmlspecialchars($dados['telefone'] ?? '') ?>"
        required>

    <br><br>

    <label>Objetivo</label>
    <br>
    <input
        type="text"
        name="objetivo"
        value="<?= htmlspecialchars($dados['objetivo'] ?? '') ?>">

    <br><br>

    <label>Campanha</label>
    <br>
    <input
        type="text"
        name="campanha"
        value="<?= htmlspecialchars($dados['campanha'] ?? '') ?>">

    <br><br>

    <button type="submit">
        Cadastrar Lead
    </button>
    <a href="<?= htmlspecialchars($baseUrl ?? '/Gymflow/app/controllers/CrmController.php') ?>?acao=listar" style="margin-left: 10px;">Cancelar</a>

</form>

<?php include __DIR__ . '/../shared/footer.php'; ?>