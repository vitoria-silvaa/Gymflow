<?php
// app/views/crm/cadastrar.php

if (!isset($dados, $csrf, $baseUrl, $isEdicao)) {
    http_response_code(403);
    exit;
}

$cssEspecifico =
    BASE_URL . '/assets/css/jorge-financeiro.css';

$h = static fn($v) => htmlspecialchars(
    (string) $v,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main class="conteudo crm-page">

    <div class="fin-page-header">

        <div class="fin-header-info">

            <h1><?= $isEdicao ? 'Editar lead' : 'Novo lead' ?></h1>

            <p>
                <?= $isEdicao
                    ? 'Atualize os dados do interessado.'
                    : 'Cadastre um interessado nos serviços da academia.' ?>
            </p>

        </div>

        <a
            href="<?= $h($baseUrl) ?>"
            class="fin-btn fin-btn-secondary">

            Voltar

        </a>

    </div>

    <section class="fin-form-card">

        <?php if (!empty($erro)): ?>

            <div
                class="fin-alert fin-alert-danger"
                role="alert">

                <?= $h($erro) ?>

            </div>

        <?php endif; ?>

        <form
            method="POST"
            action="<?= $h($baseUrl) ?>?acao=<?= $isEdicao
                ? 'editar&amp;id=' . (int) $dados['id']
                : 'cadastrar' ?>">

            <input
                type="hidden"
                name="csrf"
                value="<?= $h($csrf) ?>">

            <div class="fin-form-grid">

                <div class="fin-form-group col-span-2">

                    <label
                        class="fin-form-label"
                        for="filial_id">

                        Filial *

                    </label>

                    <select
                        id="filial_id"
                        name="filial_id"
                        class="fin-form-select"
                        required>

                        <option value="">Selecione a filial</option>

                        <?php foreach ($filiais ?? [] as $f): ?>

                            <option
                                value="<?= (int) $f['id'] ?>"

                                <?= (int) ($dados['filial_id'] ?? 0)
                                    === (int) $f['id']
                                    ? 'selected'
                                    : '' ?>>

                                <?= $h($f['nome']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <?php foreach ([
                    'nome' => ['Nome completo *', 100, 'text', true],
                    'telefone' => ['Telefone com DDD *', 15, 'tel', true],
                    'objetivo' => ['Objetivo principal', 100, 'text', false],
                    'campanha' => ['Origem / Campanha', 100, 'text', false]
                ] as $campo => $configCampo): ?>

                    <div class="fin-form-group">

                        <label
                            class="fin-form-label"
                            for="<?= $h($campo) ?>">

                            <?= $h($configCampo[0]) ?>

                        </label>

                        <input
                            id="<?= $h($campo) ?>"
                            name="<?= $h($campo) ?>"
                            type="<?= $h($configCampo[2]) ?>"
                            class="fin-form-input"
                            maxlength="<?= (int) $configCampo[1] ?>"
                            value="<?= $h($dados[$campo] ?? '') ?>"
                            <?= $configCampo[3] ? 'required' : '' ?>>

                    </div>

                <?php endforeach; ?>

                <?php if ($isEdicao): ?>

                    <div class="fin-form-group col-span-2">

                        <label
                            class="fin-form-label"
                            for="status">

                            Etapa do funil

                        </label>

                        <select
                            id="status"
                            name="status"
                            class="fin-form-select"
                            required>

                            <?php foreach ($statusManuais as $opcao): ?>

                                <option
                                    value="<?= $h($opcao) ?>"

                                    <?= ($dados['status'] ?? '') === $opcao
                                        ? 'selected'
                                        : '' ?>>

                                    <?= $h($opcao) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                <?php endif; ?>

            </div>

            <div class="fin-form-actions">

                <button
                    type="submit"
                    class="fin-btn fin-btn-primary"
                    <?= empty($filiais) ? 'disabled' : '' ?>>

                    <?= $isEdicao
                        ? 'Salvar alterações'
                        : 'Cadastrar lead' ?>

                </button>

                <a
                    href="<?= $h($baseUrl) ?>"
                    class="fin-btn fin-btn-secondary">

                    Cancelar

                </a>

            </div>

        </form>

    </section>

</main>

<script>
(function () {

    const telefone = document.getElementById('telefone');

    if (!telefone) {
        return;
    }

    telefone.addEventListener('input', function () {

        const n = telefone.value
            .replace(/\D/g, '')
            .slice(0, 11);

        if (n.length <= 2) {
            telefone.value = n;
            return;
        }

        const corpo = n.slice(2);

        telefone.value =
            '(' + n.slice(0, 2) + ') ' +
            (
                corpo.length > 4
                    ? corpo.slice(0, -4) + '-' + corpo.slice(-4)
                    : corpo
            );
    });

})();
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>