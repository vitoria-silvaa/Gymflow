<?php
// app/views/alunos/index.php

if (!isset($alunos, $baseUrl)) {
    http_response_code(403);
    exit;
}

$tituloPagina = 'Alunos';

$cssEspecifico =
    BASE_URL . '/assets/css/jorge-financeiro.css';

$h = static fn($v) => htmlspecialchars(
    (string) $v,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$filiais = $filiais ?? [];

$filial_id_filter = (int) ($filial_id_filter ?? 0);

$recepcao =
    ($_SESSION['usuario_role'] ?? '') === 'Recepcao';

$urlMatricula = BASE_URL .
    '/app/controllers/MatriculaController.php' .
    '?acao=identificacao&modo=interno&novo=1';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link
    rel="stylesheet"
    href="<?= BASE_URL ?>/assets/css/alunos.css">

<main class="conteudo financeiro-page alunos-page">

    <div class="fin-page-header">

        <div class="fin-header-info">

            <h1>Alunos</h1>

            <p>

                <?= $recepcao
                    ? 'Gerencie os alunos das unidades autorizadas.'
                    : 'Gerencie os alunos cadastrados na empresa.' ?>

            </p>

        </div>

        <div class="fin-header-actions">

            <a
                href="<?= $h($urlMatricula) ?>"
                class="fin-btn fin-btn-primary">

                Matricular aluno

            </a>

        </div>

    </div>

    <?php if (!empty($erro)): ?>

        <div class="fin-alert fin-alert-danger" role="alert">

            <?= $h($erro) ?>

        </div>

    <?php endif; ?>

    <?php if (!empty($sucesso)): ?>

        <div class="fin-alert fin-alert-success" role="status">

            <?= $h($sucesso) ?>

        </div>

    <?php endif; ?>

    <?php if ($recepcao && !$filiais): ?>

        <div class="fin-alert fin-alert-danger">

            Seu usuário não possui filiais vinculadas.
            Solicite a configuração ao administrador.

        </div>

    <?php endif; ?>

    <section class="fin-table-card alunos-filtros-card">

        <form
            action="<?= $h($baseUrl) ?>"
            method="GET"
            class="fin-form-grid alunos-filtros">

            <input
                type="hidden"
                name="acao"
                value="listar">

            <div class="fin-form-group alunos-filtro-campo">

                <label for="cpf">CPF</label>

                <input
                    type="text"
                    id="cpf"
                    name="cpf"
                    class="fin-input"
                    placeholder="Buscar por CPF"
                    maxlength="14"
                    inputmode="numeric"
                    autocomplete="off"
                    value="<?= $h($cpf ?? '') ?>">

            </div>

            <div class="fin-form-group alunos-filtro-campo">

                <label for="status">Status</label>

                <select
                    id="status"
                    name="status"
                    class="fin-select"
                    autocomplete="off">

                    <option
                        value=""
                        <?= ($status ?? '') === ''
                            ? 'selected'
                            : '' ?>>

                        Todos os status

                    </option>

                    <?php foreach ([
                        'Ativo' => 'Ativo',
                        'Inativo' => 'Inativo',
                        'Pendente' => 'Matrícula pendente'
                    ] as $valor => $rotulo): ?>

                        <option
                            value="<?= $h($valor) ?>"

                            <?= ($status ?? '') === $valor
                                ? 'selected'
                                : '' ?>>

                            <?= $h($rotulo) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="fin-form-group alunos-filtro-campo">

                <label for="filial_id">Filial</label>

                <select
                    id="filial_id"
                    name="filial_id"
                    class="fin-select"
                    autocomplete="off">

                    <option
                        value="0"

                        <?= $filial_id_filter === 0
                            ? 'selected'
                            : '' ?>>

                        <?= $recepcao
                            ? 'Todas as autorizadas'
                            : 'Todas as filiais' ?>

                    </option>

                    <?php foreach ($filiais as $filial): ?>

                        <option
                            value="<?= (int) $filial['id'] ?>"

                            <?= $filial_id_filter ===
                                (int) $filial['id']
                                ? 'selected'
                                : '' ?>>

                            <?= $h($filial['nome']) ?>

                            <?= (int) $filial['ativo'] === 0
                                ? ' (Inativa)'
                                : '' ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="fin-form-group">

                <button
                    type="submit"
                    class="fin-btn fin-btn-secondary">

                    Filtrar

                </button>

                <a
                    href="<?= $h($baseUrl) ?>?acao=listar"
                    class="fin-btn fin-btn-secondary">

                    Limpar

                </a>

            </div>

        </form>

    </section>

    <section class="fin-table-card">

        <div class="fin-table-header">

            <h2>Alunos cadastrados</h2>

            <span>
                <?= count($alunos) ?> aluno(s)
            </span>

        </div>

        <div class="fin-table-responsive">

            <table class="fin-table">

                <thead>

                    <tr>

                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Filial</th>
                        <th>Status</th>
                        <th>Matrícula</th>

                        <th class="alunos-coluna-acoes">
                            Ações
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (!$alunos): ?>

                        <tr>

                            <td
                                colspan="6"
                                class="alunos-vazio">

                                Nenhum aluno encontrado.

                            </td>

                        </tr>

                    <?php endif; ?>

                    <?php foreach ($alunos as $aluno): ?>

                        <?php

                        $numeroWhats = preg_replace(
                            '/\D/',
                            '',
                            (string) ($aluno['telefone'] ?? '')
                        );

                        $pendente =
                            !empty($aluno['matricula_pendente']);

                        $alunoAtivo =
                            $aluno['status'] === 'Ativo';

                        ?>

                        <tr>

                            <td>

                                <strong>
                                    <?= $h($aluno['nome']) ?>
                                </strong>

                            </td>

                            <td>
                                <?= $h($aluno['cpf']) ?>
                            </td>

                            <td>
                                <?= $h($aluno['nome_filial']) ?>
                            </td>

                            <td class="<?= $alunoAtivo
                                ? 'status-ativo'
                                : 'status-inativo' ?>">

                                <?= $h($aluno['status']) ?>

                            </td>

                            <td>

                                <?php if ($pendente): ?>

                                    <span class="ui-texto-aviso">

                                        Aguardando pagamento

                                    </span>

                                <?php else: ?>

                                    <span class="ui-texto-suave">

                                        Sem pendência inicial

                                    </span>

                                <?php endif; ?>

                            </td>

                            <td class="alunos-coluna-acoes">

                                <div class="fin-actions-dropdown icones-acoes">

                                    <?php if (in_array(
                                        strlen($numeroWhats),
                                        [10, 11],
                                        true
                                    )): ?>

                                        <a
                                            href="https://wa.me/55<?= $h($numeroWhats) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            title="WhatsApp"
                                            aria-label="WhatsApp"
                                            class="icone icone-whats">

                                            <i data-lucide="message-circle"></i>

                                        </a>

                                    <?php endif; ?>

                                    <?php if (!empty($aluno['email'])): ?>

                                        <a
                                            href="https://mail.google.com/mail/?view=cm&amp;fs=1&amp;to=<?= urlencode($aluno['email']) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            title="E-mail"
                                            aria-label="E-mail"
                                            class="icone">

                                            <i data-lucide="mail"></i>

                                        </a>

                                    <?php endif; ?>

                                    <a
                                        href="<?= $h($baseUrl) ?>?acao=visualizar&amp;id=<?= (int) $aluno['id'] ?>"
                                        title="Visualizar"
                                        aria-label="Visualizar"
                                        class="icone">

                                        <i data-lucide="eye"></i>

                                    </a>

                                    <a
                                        href="<?= $h($baseUrl) ?>?acao=editar&amp;id=<?= (int) $aluno['id'] ?>"
                                        title="Editar"
                                        aria-label="Editar"
                                        class="icone icone-editar">

                                        <i data-lucide="square-pen"></i>

                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<script>
(function () {

    const cpf = document.getElementById('cpf');

    if (!cpf) {
        return;
    }

    cpf.addEventListener('input', function () {

        const n = cpf.value
            .replace(/\D/g, '')
            .slice(0, 11);

        cpf.value =
            n.slice(0, 3) +
            (n.length > 3 ? '.' + n.slice(3, 6) : '') +
            (n.length > 6 ? '.' + n.slice(6, 9) : '') +
            (n.length > 9 ? '-' + n.slice(9) : '');
    });

})();
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>