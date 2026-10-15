<?php
// Se alguém abrir esta página direto, sem passar pelo controller, manda pro controller
if (!isset($alunos)) {
    header("Location: " . BASE_URL . "/app/controllers/AlunoController.php?acao=listar");
    exit;
}

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

// Se o controller não mandou as filiais ou o filtro, usa valores vazios
if (!isset($filiais)) {
    $filiais = [];
}
if (!isset($filial_id_filter)) {
    $filial_id_filter = 0;
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<!-- Estilos só desta página -->
<style>
    .titulo-coluna {
        text-transform: uppercase;
        font-size: 12px;
        color: #64748b;
        letter-spacing: 0.5px;
    }

    .icones-acoes {
        display: inline-flex;
        gap: 16px;
        align-items: center;
        justify-content: flex-end;
    }

    .icone {
        display: flex;
        color: #64748b;
        transition: 0.2s;
    }

    .icone:hover {
        color: #3b82f6;
    }

    .icone-whats {
        color: #22c55e;
    }

    .icone-whats:hover {
        color: #22c55e;
        opacity: 0.7;
    }

    .icone-editar {
        color: #475569;
    }

    .icone-editar:hover {
        color: #f59e0b;
    }
</style>

<main class="conteudo financeiro-page">

    <!-- Título da página e botão de cadastrar -->
    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Alunos</h1>
            <p>Gerencie todos os alunos cadastrados no sistema.</p>
        </div>
        <div class="fin-header-actions">
            <a href="<?= $baseUrl ?>?acao=cadastrar" class="fin-btn fin-btn-primary">+ Novo Aluno</a>
        </div>
    </div>

    <!-- Filtros de busca -->
    <section class="fin-table-card" style="margin-bottom: 24px;">
        <form action="<?= $baseUrl ?>" method="GET" class="fin-form-grid" style="display: flex; gap: 16px; align-items: flex-end; padding: 20px;">
            <input type="hidden" name="acao" value="listar">

            <div class="fin-form-group" style="flex: 1;">
                <label>CPF</label>
                <input type="text" name="cpf" class="fin-input" placeholder="Buscar por CPF" value="<?= htmlspecialchars($cpf) ?>">
            </div>

            <div class="fin-form-group" style="flex: 1;">
                <label>Status</label>
                <select name="status" class="fin-select">
                    <option value="">Todos os status</option>
                    <option value="Ativo" <?php if ($status == 'Ativo') { echo 'selected'; } ?>>Ativo</option>
                    <option value="Inativo" <?php if ($status == 'Inativo') { echo 'selected'; } ?>>Inativo</option>
                </select>
            </div>

            <div class="fin-form-group" style="flex: 1;">
                <label>Filial</label>
                <select name="filial_id" class="fin-select">
                    <option value="">Todas as filiais</option>
                    <?php foreach ($filiais as $filial): ?>
                        <option value="<?= $filial['id'] ?>" <?php if ($filial_id_filter == $filial['id']) { echo 'selected'; } ?>>
                            <?= htmlspecialchars($filial['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="fin-form-group">
                <button type="submit" class="fin-btn fin-btn-secondary">Filtrar</button>
            </div>
        </form>
    </section>

    <!-- Tabela com os alunos -->
    <section class="fin-table-card">
        <div class="fin-table-responsive">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th class="titulo-coluna">Nome</th>
                        <th class="titulo-coluna">CPF</th>
                        <th class="titulo-coluna">Filial</th>
                        <th class="titulo-coluna">Status</th>
                        <th class="titulo-coluna" style="text-align: right;">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($alunos) == 0): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 32px; color: var(--fin-text-muted);">Nenhum aluno encontrado.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($alunos as $aluno): ?>

                        <?php
                        // Para o link do WhatsApp o telefone precisa ter só números.
                        // O preg_replace com '/\D/' apaga tudo que não for número.
                        $numeroWhats = '';
                        if (isset($aluno['telefone'])) {
                            $numeroWhats = preg_replace('/\D/', '', $aluno['telefone']);
                        }
                        ?>

                        <tr>
                            <td><strong><?= htmlspecialchars($aluno['nome']) ?></strong></td>
                            <td><?= htmlspecialchars($aluno['cpf']) ?></td>
                            <td><?= htmlspecialchars($aluno['nome_filial']) ?></td>

                            <!-- Etiqueta verde para Ativo e amarela para Inativo -->
                            <td>
                                <?php if ($aluno['status'] == 'Ativo'): ?>
                                    <span class="fin-badge fin-badge-pago">Ativo</span>
                                <?php else: ?>
                                    <span class="fin-badge fin-badge-aberto">Inativo</span>
                                <?php endif; ?>
                            </td>

                            <td style="text-align: right;">
                                <div class="fin-actions-dropdown icones-acoes">

                                    <!-- WhatsApp (só aparece se tiver telefone) -->
                                    <?php if ($numeroWhats != ''): ?>
                                        <a href="https://wa.me/55<?= $numeroWhats ?>" target="_blank" title="WhatsApp" class="icone icone-whats">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>

                                    <!-- E-mail (abre o Gmail já com o destinatário) -->
                                    <!-- urlencode deixa o e-mail no formato certo para ir dentro de um link -->
                                    <?php if (!empty($aluno['email'])): ?>
                                        <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($aluno['email']) ?>" target="_blank" title="E-mail" class="icone">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Ver detalhes -->
                                    <a href="<?= $baseUrl ?>?acao=visualizar&id=<?= $aluno['id'] ?>" title="Visualizar" class="icone">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>

                                    <!-- Editar -->
                                    <a href="<?= $baseUrl ?>?acao=editar&id=<?= $aluno['id'] ?>" title="Editar" class="icone icone-editar">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
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