<?php

if (!isset($funcionarios)) {
    header(
        "Location: " . BASE_URL .
        "/app/controllers/FuncionarioController.php?acao=listar"
    );
    exit;
}

$tituloPagina = "Funcionários";

$controller = BASE_URL .
    "/app/controllers/FuncionarioController.php";

$esc = static function ($valor) {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

?>

<link rel="stylesheet"
      href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<style>

    /* FILTROS DE COLABORADORES */

    .funcionarios-page .funcionarios-filtros {
        box-sizing: border-box;
        width: 100%;
        padding: 20px 24px;
        margin: 0 0 24px;
        background-color: var(--ui-fundo-card);
        border: 1px solid var(--ui-borda);
        border-radius: var(--fin-radius-md);
        box-shadow: none;
    }

    .funcionarios-page .funcionarios-filtro-form {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 16px;
        width: 100%;
        max-width: none;
        padding: 0;
        margin: 0;
    }

    .funcionarios-page .funcionarios-campo {
        display: flex;
        flex-direction: column;
        flex: 1 1 200px;
        min-width: 0;
        gap: 8px;
        padding: 0;
        margin: 0;
    }

    .funcionarios-page .funcionarios-campo-nome {
        flex: 2 1 260px;
    }

    .funcionarios-page .funcionarios-campo label {
        display: block;
        margin: 0;
        color: var(--ui-texto);
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.4;
    }

    /* CAMPOS DE PESQUISA */

    .funcionarios-page .funcionarios-campo input,
    .funcionarios-page .funcionarios-campo select {
        box-sizing: border-box;
        display: block;
        width: 100%;
        min-width: 0;
        height: 44px;
        padding: 10px 14px;
        margin: 0;
        background-color: var(--ui-input-fundo);
        color: var(--ui-input-texto);
        border: 1px solid var(--ui-input-borda);
        border-radius: var(--fin-radius-sm);
        font-family: inherit;
        font-size: 14px;
    }

    .funcionarios-page .funcionarios-campo input::placeholder {
        color: var(--ui-texto-suave);
    }

    .funcionarios-page .funcionarios-campo select {
        cursor: pointer;
    }

    .funcionarios-page .funcionarios-campo select option {
        background-color: var(--ui-input-fundo);
        color: var(--ui-input-texto);
    }

    .funcionarios-page .funcionarios-campo input:focus,
    .funcionarios-page .funcionarios-campo select:focus {
        border-color: var(--cor-primaria);
        outline: 2px solid var(--fin-primary-light);
        outline-offset: 1px;
    }

    /* ÍCONE DE PESQUISA */

    .funcionarios-page .funcionarios-input-icon {
        position: relative;
        display: block;
        width: 100%;
    }

    .funcionarios-page .funcionarios-input-icon svg {
        position: absolute;
        top: 50%;
        left: 14px;
        width: 18px;
        height: 18px;
        margin: 0;
        transform: translateY(-50%);
        color: var(--ui-texto-suave);
        pointer-events: none;
    }

    .funcionarios-page .funcionarios-input-icon input {
        padding-left: 42px;
    }

    /* BOTÕES DOS FILTROS */

    .funcionarios-page .funcionarios-filtro-acoes {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-shrink: 0;
        gap: 10px;
        margin: 0;
        padding: 0;
    }

    .funcionarios-page .funcionarios-filtro-acoes .fin-btn {
        box-sizing: border-box;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: auto;
        height: 44px;
        min-height: 44px;
        padding: 0 20px;
        margin: 0;
        font-family: inherit;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
    }

    .funcionarios-page .funcionarios-filtro-acoes .fin-btn-primary {
        background-color: var(--cor-primaria);
        color: var(--cor-sobre-primaria) !important;
    }

    .funcionarios-page .funcionarios-filtro-acoes .fin-btn-primary:hover {
        background-color: var(--cor-primaria-hover);
    }

    .funcionarios-page .funcionarios-filtro-acoes .fin-btn-secondary {
        background-color: transparent;
        border: 1px solid var(--ui-borda);
        color: var(--ui-texto-suave) !important;
    }

    .funcionarios-page .funcionarios-filtro-acoes .fin-btn-secondary:hover {
        background-color: var(--ui-fundo-pagina);
        color: var(--ui-texto) !important;
    }

    /* TABELA */

    .funcionarios-page .fin-table {
        min-width: 620px;
    }

    .funcionarios-page .fin-table td {
        vertical-align: middle;
    }

    .funcionarios-page .funcionarios-vazio {
        padding: 30px 20px;
        color: var(--ui-texto-suave);
        text-align: center;
    }

    /* ÍCONES DE AÇÕES */

    .funcionarios-page .icones-acoes {
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .funcionarios-page .icones-acoes .icone {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        margin: 0;
        padding: 0;
        background-color: transparent;
        border: none;
        border-radius: 5px;
        color: var(--ui-texto-suave) !important;
        text-decoration: none;
        transition:
            color .15s ease,
            background-color .15s ease;
    }

    .funcionarios-page .icones-acoes .icone svg {
        width: 20px;
        height: 20px;
    }

    .funcionarios-page .icones-acoes .icone:hover {
        background-color: var(--ui-fundo-pagina);
        color: var(--ui-texto) !important;
    }

    .funcionarios-page .icones-acoes .icone-whatsapp {
        color: #22c55e !important;
    }

    .funcionarios-page .icone:focus-visible,
    .funcionarios-page .fin-btn:focus-visible {
        outline: 2px solid var(--cor-primaria);
        outline-offset: 2px;
    }

    /* RESPONSIVIDADE */

    @media (max-width: 900px) {

        .funcionarios-page .funcionarios-filtros {
            padding: 18px;
        }

        .funcionarios-page .funcionarios-filtro-acoes {
            margin-left: auto;
        }
    }

    @media (max-width: 600px) {

        .funcionarios-page .funcionarios-filtros {
            padding: 16px;
            margin-bottom: 20px;
        }

        .funcionarios-page .funcionarios-filtro-form {
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
        }

        .funcionarios-page .funcionarios-campo,
        .funcionarios-page .funcionarios-campo-nome {
            flex: none;
            width: 100%;
        }

        .funcionarios-page .funcionarios-filtro-acoes {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            margin: 0;
        }

        .funcionarios-page .funcionarios-filtro-acoes .fin-btn {
            width: 100%;
        }
    }

</style>

<main class="conteudo financeiro-page funcionarios-page">

    <!-- CABEÇALHO -->

    <div class="fin-page-header">

        <div class="fin-header-info">

            <h1>Gestão de Colaboradores</h1>

            <p>
                Gerencie a equipe e defina as permissões
                de acesso ao sistema.
            </p>

        </div>

        <div class="fin-header-actions">

            <a
                href="<?= $esc($controller) ?>?acao=cadastrar"
                class="fin-btn fin-btn-primary">

                <svg
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true">

                    <path d="M12 5v14M5 12h14"/>

                </svg>

                Novo Funcionário

            </a>

        </div>

    </div>

    <!-- FILTROS -->

    <section class="fin-table-card funcionarios-filtros">

        <form
            action="<?= $esc($controller) ?>"
            method="GET"
            class="funcionarios-filtro-form">

            <input
                type="hidden"
                name="acao"
                value="listar">

            <div class="funcionarios-campo funcionarios-campo-nome">

                <label for="nome_busca">
                    Nome do colaborador
                </label>

                <div class="funcionarios-input-icon">

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true">

                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>

                    </svg>

                    <input
                        type="text"
                        id="nome_busca"
                        name="nome_busca"
                        class="fin-input"
                        placeholder="Buscar por nome"
                        value="<?= $esc($nome_busca ?? '') ?>">

                </div>

            </div>

            <div class="funcionarios-campo">

                <label for="role_busca">
                    Cargo / Perfil
                </label>

                <select
                    id="role_busca"
                    name="role_busca"
                    class="fin-select">

                    <option value="">
                        Todos os cargos
                    </option>

                    <option
                        value="Admin"
                        <?= ($role_busca ?? '') === 'Admin'
                            ? 'selected' : '' ?>>

                        Administrador

                    </option>

                    <option
                        value="Professor"
                        <?= ($role_busca ?? '') === 'Professor'
                            ? 'selected' : '' ?>>

                        Instrutor / Professor

                    </option>

                    <option
                        value="Recepcao"
                        <?= ($role_busca ?? '') === 'Recepcao'
                            ? 'selected' : '' ?>>

                        Recepção

                    </option>

                </select>

            </div>

            <div class="funcionarios-filtro-acoes">

                <button
                    type="submit"
                    class="fin-btn fin-btn-primary">

                    Buscar

                </button>

                <a
                    href="<?= $esc($controller) ?>?acao=listar"
                    class="fin-btn fin-btn-secondary">

                    Limpar

                </a>

            </div>

        </form>

    </section>

    <!-- LISTAGEM DE FUNCIONÁRIOS -->

    <section class="fin-table-card">

        <div class="fin-table-responsive">

            <table class="fin-table">

                <thead>

                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Ações</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if (empty($funcionarios)): ?>

                        <tr>

                            <td
                                colspan="4"
                                class="funcionarios-vazio">

                                Nenhum funcionário encontrado.

                            </td>

                        </tr>

                    <?php endif; ?>

                    <?php foreach ($funcionarios as $func): ?>

                        <?php

                        $numeroWhats = '';

                        if (!empty($func['telefone'])) {

                            $numeroWhats = preg_replace(
                                '/\D+/',
                                '',
                                $func['telefone']
                            );
                        }

                        $perfilExibicao = $func['role'] ?? '';

                        if ($perfilExibicao === 'Admin') {

                            $perfilExibicao = 'Administrador';
                        }

                        if ($perfilExibicao === 'Recepcao') {

                            $perfilExibicao = 'Recepção';
                        }

                        ?>

                        <tr>

                            <!-- NOME -->

                            <td>

                                <strong>

                                    <?= $esc($func['name'] ?? '') ?>

                                </strong>

                            </td>

                            <!-- E-MAIL -->

                            <td>

                                <?= $esc($func['email'] ?? '') ?>

                            </td>

                            <!-- PERFIL -->

                            <td>

                                <?= $esc($perfilExibicao) ?>

                            </td>

                            <!-- AÇÕES -->

                            <td>

                                <div class="icones-acoes">

                                    <!-- WHATSAPP -->

                                    <?php if ($numeroWhats !== ''): ?>

                                        <a
                                            href="https://wa.me/55<?= $esc($numeroWhats) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            title="WhatsApp"
                                            aria-label="WhatsApp"
                                            class="icone icone-whatsapp">

                                            <svg
                                                width="22"
                                                height="22"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true">

                                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>

                                            </svg>

                                        </a>

                                    <?php endif; ?>

                                    <!-- E-MAIL -->

                                    <?php if (!empty($func['email'])): ?>

                                        <a
                                            href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($func['email']) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            title="E-mail"
                                            aria-label="Enviar e-mail"
                                            class="icone">

                                            <svg
                                                width="22"
                                                height="22"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true">

                                                <rect
                                                    x="2"
                                                    y="4"
                                                    width="20"
                                                    height="16"
                                                    rx="2"/>

                                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>

                                            </svg>

                                        </a>

                                    <?php endif; ?>

                                    <!-- VISUALIZAR -->

                                    <a
                                        href="<?= $esc($controller) ?>?acao=visualizar&id=<?= urlencode($func['id']) ?>"
                                        title="Visualizar"
                                        aria-label="Visualizar funcionário"
                                        class="icone">

                                        <svg
                                            width="22"
                                            height="22"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            aria-hidden="true">

                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>

                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="3"/>

                                        </svg>

                                    </a>

                                    <!-- EDITAR -->

                                    <a
                                        href="<?= $esc($controller) ?>?acao=editar&id=<?= urlencode($func['id']) ?>"
                                        title="Editar"
                                        aria-label="Editar funcionário"
                                        class="icone">

                                        <svg
                                            width="22"
                                            height="22"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            aria-hidden="true">

                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>

                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>

                                        </svg>

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

<?php include __DIR__ . '/../shared/footer.php'; ?>