<?php
if (!isset($filial)) {
    $id = $_GET['id'] ?? null;
    header("Location: " . BASE_URL . "/app/controllers/FilialController.php?acao=editar" . ($id ? "&id=" . $id : ""));
    exit;
}

/** @var array $filial */
/** @var string $erro */

$tituloPagina = "Editar Filial";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<div class="modal-overlay">

    <div class="modal-editar-filial">

        <div class="modal-editar-header">
            <h2>Editar Filial</h2>

            <a
                href="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=listar"
                class="modal-fechar"
                aria-label="Fechar">
                ×
            </a>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="mensagem-erro-filial">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form
            class="form-editar-filial"
            action="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=editar&id=<?= $filial['id'] ?>"
            method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= $filial['id'] ?>">

            <div class="campo-editar-filial">
                <label for="nome">Nome</label>

                <input
                    type="text"
                    id="nome"
                    name="nome"
                    value="<?= htmlspecialchars($filial['nome']) ?>"
                    required>
            </div>

            <div class="campo-editar-filial">
                <label for="cnpj">CNPJ</label>

                <input
                    type="text"
                    id="cnpj"
                    name="cnpj"
                    value="<?= htmlspecialchars($filial['cnpj']) ?>"
                    required>
            </div>

            <div class="campo-editar-filial">
                <label for="telefone">Telefone</label>

                <input
                    type="text"
                    id="telefone"
                    name="telefone"
                    value="<?= htmlspecialchars($filial['telefone']) ?>">
            </div>

            <div class="campo-editar-filial">
                <label for="responsavel">Responsável</label>

                <input
                    type="text"
                    id="responsavel"
                    name="responsavel"
                    value="<?= htmlspecialchars($filial['responsavel']) ?>"
                    required>
            </div>

            <div class="modal-editar-acoes">

                <a
                    href="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=listar"
                    class="btn-cancelar-editar">
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="btn-salvar-editar">
                    Salvar
                </button>

            </div>

        </form>

    </div>

</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>