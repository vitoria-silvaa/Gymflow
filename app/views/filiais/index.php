<?php
if (!isset($filiais)) {
    header(
        "Location: " .
        BASE_URL .
        "/app/controllers/FilialController.php?acao=listar"
    );
    exit;
}

/** @var array $filiais */
/** @var string $statusFiltro */

$mensagem = $_GET['mensagem'] ?? '';

$tituloPagina = "Filiais";

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link
    rel="stylesheet"
    href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css"
>
<link
    rel="stylesheet"
    href="<?= BASE_URL ?>/assets/css/filiais.css"
>

<main class="filiais-page">

    <?php if ($mensagem !== ''): ?>

        <div class="mensagem-sucesso">
            <span>
                <?= htmlspecialchars(
                    $mensagem,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>
        </div>

    <?php endif; ?>

    <div class="filiais-topo">

        <div class="filiais-titulo">

            <h1>
                Filiais
            </h1>

            <p>
                Gerencie as unidades da sua rede.
            </p>

        </div>

        <div class="filiais-acoes">

            <form
                method="GET"
                action="<?= BASE_URL ?>/app/controllers/FilialController.php"
            >

                <input
                    type="hidden"
                    name="acao"
                    value="listar"
                >

                <select
                    name="status"
                    class="fin-select filiais-filtro-select"
                    onchange="this.form.submit()"
                >

                    <option
                        value=""
                        <?= $statusFiltro === '' ? 'selected' : '' ?>
                    >
                        Todos
                    </option>

                    <option
                        value="Ativa"
                        <?= $statusFiltro === 'Ativa' ? 'selected' : '' ?>
                    >
                        Ativo
                    </option>

                    <option
                        value="Inativa"
                        <?= $statusFiltro === 'Inativa' ? 'selected' : '' ?>
                    >
                        Inativo
                    </option>

                </select>

            </form>

            <a
                class="fin-btn fin-btn-primary"
                href="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=cadastrar"
            >
                + Nova Filial
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
                            <?= htmlspecialchars(
                                $filial['nome'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h2>

                        <span
                            class="badge <?= $filial['ativo'] ? 'ativa' : 'inativa' ?>"
                        >
                            <?= $filial['ativo'] ? 'Ativa' : 'Inativa' ?>
                        </span>

                    </div>

                    <div class="card-body">

                        <p>

                            <span>
                                CNPJ:
                            </span>

                            <?= htmlspecialchars(
                                $filial['cnpj'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </p>

                        <p>

                            <span>
                                Telefone:
                            </span>

                            <?= htmlspecialchars(
                                $filial['telefone'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </p>

                        <?php if (!empty($filial['email'])): ?>

                            <p>

                                <span>
                                    E-mail:
                                </span>

                                <?= htmlspecialchars(
                                    $filial['email'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </p>

                        <?php endif; ?>

                        <?php if (!empty($filial['endereco'])): ?>

                            <p>

                                <span>
                                    Endereço:
                                </span>

                                <?= htmlspecialchars(
                                    $filial['endereco'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                <?php if (!empty($filial['numero'])): ?>

                                    ,
                                    <?= htmlspecialchars(
                                        $filial['numero'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                <?php endif; ?>

                                <?php if (!empty($filial['complemento'])): ?>

                                    (
                                    <?= htmlspecialchars(
                                        $filial['complemento'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                    )

                                <?php endif; ?>

                            </p>

                        <?php endif; ?>

                        <p>

                            <span>
                                Responsável:
                            </span>

                            <?= htmlspecialchars(
                                $filial['responsavel'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </p>

                    </div>

                    <div class="card-footer">

                        <a
                            class="fin-btn fin-btn-secondary"
                            href="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=editar&id=<?= $filial['id'] ?>"
                        >
                            Editar
                        </a>

                        <a
                            href="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=alternar_status&id=<?= $filial['id'] ?>"
                            class="fin-btn <?= $filial['ativo']
                                ? 'fin-btn-danger fin-btn-inativar'
                                : 'fin-btn-primary'
                            ?>"
                        >
                            <?= $filial['ativo']
                                ? 'Inativar'
                                : 'Ativar'
                            ?>
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</main>

<script src="<?= BASE_URL ?>/assets/js/filiais.js" defer></script>

<?php
include __DIR__ . '/../shared/footer.php';
?>