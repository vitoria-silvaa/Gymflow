<?php
if (!isset($filiais)) {
    header("Location: " . BASE_URL . "/app/controllers/FilialController.php?acao=listar");
    exit;
}

/** @var array $filiais */
/** @var string $statusFiltro */

$mensagem = $_GET['mensagem'] ?? '';

$tituloPagina = "Filiais";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

?>

<main class="filiais-page">

    <?php if ($mensagem !== ''): ?>
        <div class="mensagem-sucesso">
            <span class="mensagem-sucesso-icone">✓</span>
            <span><?= htmlspecialchars($mensagem) ?></span>
        </div>
    <?php endif; ?>

    <script>
        setTimeout(function() {
            const mensagem = document.querySelector('.mensagem-sucesso');

            if (mensagem) {
                mensagem.style.opacity = '0';

                setTimeout(function() {
                    mensagem.remove();
                }, 300);
            }
        }, 3000);
    </script>

    <div class="filiais-topo">
        <div class="filiais-titulo">
            <h1>Filiais</h1>
            <p>Gerencie as unidades da sua rede.</p>
        </div>

        <div class="filiais-acoes">
            <form
                method="GET"
                action="<?= BASE_URL ?>/app/controllers/FilialController.php">
                <input type="hidden" name="acao" value="listar">

                <select name="status" onchange="this.form.submit()">
                    <option value="" <?= $statusFiltro === '' ? 'selected' : '' ?>>
                        Todos
                    </option>

                    <option value="Ativa" <?= $statusFiltro === 'Ativa' ? 'selected' : '' ?>>
                        Ativo
                    </option>

                    <option value="Inativa" <?= $statusFiltro === 'Inativa' ? 'selected' : '' ?>>
                        Inativo
                    </option>
                </select>
            </form>

            <a
                class="fin-btn fin-btn-primary"
                href="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=cadastrar">
                + &nbsp; Nova Filial
            </a>
        </div>
    </div>


    <div class="cards-container">

        <?php if (empty($filiais)): ?>

            <p class="filiais-vazio">
                Nenhuma filial encontrada no banco de dados.
            </p>

        <?php else: ?>

            <?php foreach ($filiais as $filial): ?>

                <div class="card-filial">

                    <div class="card-header">

                        <h2>
                            <?= htmlspecialchars($filial['nome']) ?>
                        </h2>

                        <span class="badge <?= $filial['ativo'] ? 'ativa' : 'inativa' ?>">
                            <?= $filial['ativo'] ? 'Ativa' : 'Inativa' ?>
                        </span>

                    </div>


                    <div class="card-body">

                        <p>
                            <span>CNPJ:</span>
                            <?= htmlspecialchars($filial['cnpj']) ?>
                        </p>

                        <p>
                            <span>Telefone:</span>
                            <?= htmlspecialchars($filial['telefone']) ?>
                        </p>

                        <p>
                            <span>Responsável:</span>
                            <?= htmlspecialchars($filial['responsavel']) ?>
                        </p>

                    </div>


                    <div class="card-footer">

                        <a
                            class="btn-editar"
                            href="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=editar&id=<?= $filial['id'] ?>">
                            ✎ &nbsp; Editar
                        </a>

                        <a
                            href="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=alternar_status&id=<?= $filial['id'] ?>"
                            class="<?= $filial['ativo'] ? 'btn-inativar' : 'btn-ativar' ?>">
                            ⏻ &nbsp;
                            <?= $filial['ativo'] ? 'Inativar' : 'Ativar' ?>
                        </a>

                        <a
                            href="#"
                            class="btn-historico">
                            ↶ &nbsp; Histórico
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>