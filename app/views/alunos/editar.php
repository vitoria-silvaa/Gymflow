<?php
// app/views/alunos/editar.php

if (!isset($dados, $aluno, $baseUrl, $csrf)) {
    http_response_code(403);
    exit;
}

$tituloPagina = 'Editar aluno';

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

<link
    rel="stylesheet"
    href="<?= BASE_URL ?>/assets/css/alunos.css">

<main class="conteudo financeiro-page alunos-page">

    <div class="fin-page-header">

        <div class="fin-header-info">

            <h1>Editar aluno</h1>

            <p>
                Atualize as informações cadastrais permitidas.
            </p>

        </div>

        <div class="fin-header-actions">

            <a
                href="<?= $h($baseUrl) ?>?acao=visualizar&amp;id=<?= (int) $aluno['id'] ?>"
                class="fin-btn fin-btn-secondary">

                Voltar

            </a>

        </div>

    </div>

    <?php if (!empty($erro)): ?>

        <div
            class="fin-alert fin-alert-danger"
            role="alert">

            <?= $h($erro) ?>

        </div>

    <?php endif; ?>

    <section class="fin-form-card ui-card-lg">

        <form
            method="POST"
            action="<?= $h($baseUrl) ?>?acao=editar&amp;id=<?= (int) $aluno['id'] ?>">

            <input
                type="hidden"
                name="csrf"
                value="<?= $h($csrf) ?>">

            <input
                type="hidden"
                name="id"
                value="<?= (int) $aluno['id'] ?>">

            <h2>Dados pessoais</h2>

            <div class="fin-form-grid ui-mt-24">

                <div class="fin-form-group col-span-2">

                    <label for="nome">
                        Nome completo *
                    </label>

                    <input
                        type="text"
                        name="nome"
                        id="nome"
                        class="fin-form-input"
                        maxlength="100"
                        value="<?= $h($dados['nome'] ?? '') ?>"
                        required>

                </div>

                <div class="fin-form-group">

                    <label for="cpf">
                        CPF
                    </label>

                    <input
                        type="text"
                        id="cpf"
                        class="fin-form-input"
                        value="<?= $h($aluno['cpf']) ?>"
                        readonly>

                </div>

                <div class="fin-form-group">

                    <label for="rg">
                        RG
                    </label>

                    <input
                        type="text"
                        id="rg"
                        name="rg"
                        class="fin-form-input"
                        maxlength="20"
                        value="<?= $h($dados['rg'] ?? '') ?>">

                </div>

                <div class="fin-form-group">

                    <label for="nascimento">
                        Data de nascimento
                    </label>

                    <input
                        type="date"
                        id="nascimento"
                        class="fin-form-input"
                        value="<?= $h($aluno['nascimento']) ?>"
                        readonly>

                </div>

                <div class="fin-form-group">

                    <label for="sexo">
                        Sexo
                    </label>

                    <input
                        type="text"
                        id="sexo"
                        class="fin-form-input"
                        value="<?= $h($aluno['sexo']) ?>"
                        readonly>

                </div>

                <div class="fin-form-group">

                    <label for="email">
                        E-mail de acesso
                    </label>

                    <input
                        type="email"
                        id="email"
                        class="fin-form-input"
                        value="<?= $h($aluno['email']) ?>"
                        readonly>

                </div>

                <div class="fin-form-group">

                    <label for="telefone">
                        Telefone *
                    </label>

                    <input
                        type="tel"
                        id="telefone"
                        name="telefone"
                        class="fin-form-input"
                        maxlength="15"
                        value="<?= $h($dados['telefone'] ?? '') ?>"
                        required>

                </div>

                <div class="fin-form-group col-span-2">

                    <label for="endereco">
                        Endereço
                    </label>

                    <textarea
                        id="endereco"
                        name="endereco"
                        class="fin-form-input"
                        maxlength="1000"
                        rows="3"><?= $h($dados['endereco'] ?? '') ?></textarea>

                </div>

            </div>

            <h2>Vínculo com a academia</h2>

            <div class="fin-form-grid ui-mt-24">

                <div class="fin-form-group">

                    <label for="filial">
                        Unidade
                    </label>

                    <input
                        type="text"
                        id="filial"
                        class="fin-form-input"
                        value="<?= $h($aluno['nome_filial']) ?>"
                        readonly>

                </div>

                <div class="fin-form-group">

                    <label for="status">
                        Status
                    </label>

                    <input
                        type="text"
                        id="status"
                        class="fin-form-input"
                        value="<?= $h($aluno['status']) ?>"
                        readonly>

                </div>

            </div>

            <p class="subtitle">

                Alterações de acesso, unidade e situação
                da matrícula devem ser realizadas pelos
                respectivos processos autorizados.

            </p>

            <div class="fin-form-actions">

                <button
                    type="submit"
                    class="fin-btn fin-btn-primary">

                    Salvar alterações

                </button>

                <a
                    href="<?= $h($baseUrl) ?>?acao=visualizar&amp;id=<?= (int) $aluno['id'] ?>"
                    class="fin-btn fin-btn-secondary">

                    Cancelar

                </a>

            </div>

        </form>

    </section>

</main>

<script>
(function () {

    const telefone =
        document.getElementById('telefone');

    if (!telefone) {
        return;
    }

    telefone.addEventListener('input', function () {

        const n = telefone.value
            .replace(/\D/g, '')
            .slice(0, 11);

        if (n.length < 3) {

            telefone.value = n.length
                ? '(' + n
                : '';

            return;
        }

        const ddd = n.slice(0, 2);

        const restante = n.slice(2);

        const tamanho = n.length > 10 ? 5 : 4;

        telefone.value =
            '(' + ddd + ') ' +
            restante.slice(0, tamanho) +
            (
                restante.length > tamanho
                    ? '-' + restante.slice(tamanho)
                    : ''
            );

    });

})();
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>