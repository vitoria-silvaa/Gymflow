<?php
// Se alguém abrir esta página direto, sem passar pelo controller, manda pro controller
if (!isset($funcionarios)) {
    header("Location: " . BASE_URL . "/app/controllers/FuncionarioController.php?acao=listar");
    exit;
}

$tituloPagina = "Funcionários";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

// Endereço do controller, para não repetir em todos os links
$controller = BASE_URL . "/app/controllers/FuncionarioController.php";
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

    .icone-editar:hover {
        color: #f59e0b;
    }
</style>

<main class="conteudo financeiro-page">

    <!-- Título da página e botão de cadastrar -->
    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Gestão de Colaboradores</h1>
            <p>Gerencie a equipe e defina as permissões de acesso ao sistema.</p>
        </div>
        <div class="fin-header-actions">
            <a href="<?= $controller ?>?acao=cadastrar" class="fin-btn fin-btn-primary">+ Novo Funcionário</a>
        </div>
    </div>

    <!-- Formulário de busca -->
    <section class="fin-table-card" style="margin-bottom: 24px;">
        <form action="<?= $controller ?>" method="GET" class="fin-form-grid" style="display: flex; gap: 16px; align-items: flex-end; padding: 20px;">
            <input type="hidden" name="acao" value="listar">

            <div class="fin-form-group" style="flex: 1;">
                <label>Nome do Colaborador</label>
                <input type="text" name="nome_busca" class="fin-input" placeholder="Buscar por nome" value="<?= htmlspecialchars($nome_busca) ?>">
            </div>

            <div class="fin-form-group" style="flex: 1;">
                <label>Cargo / Perfil</label>
                <select name="role_busca" class="fin-select">
                    <option value="">Todos os cargos</option>
                    <option value="Admin" <?php if ($role_busca == 'Admin') { echo 'selected'; } ?>>Administrador</option>
                    <option value="Professor" <?php if ($role_busca == 'Professor') { echo 'selected'; } ?>>Instrutor / Professor</option>
                    <option value="Recepcao" <?php if ($role_busca == 'Recepcao') { echo 'selected'; } ?>>Recepção</option>
                </select>
            </div>

            <div class="fin-form-group">
                <button type="submit" class="fin-btn fin-btn-secondary">Buscar</button>
            </div>
        </form>
    </section>

    <!-- Tabela com os funcionários -->
    <section class="fin-table-card">
        <div class="fin-table-responsive">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th class="titulo-coluna">Nome</th>
                        <th class="titulo-coluna">E-mail</th>
                        <th class="titulo-coluna">Perfil (Role)</th>
                        <th class="titulo-coluna" style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if (count($funcionarios) == 0): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 32px; color: var(--fin-text-muted);">Nenhum funcionário encontrado.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($funcionarios as $func): ?>

                        <?php
                        // Para o link do WhatsApp, o telefone precisa ter só números
                        $numeroWhats = '';
                        if (isset($func['telefone'])) {
                            $numeroWhats = str_replace([' ', '(', ')', '-'], '', $func['telefone']);
                        }
                        ?>

                        <tr>
                            <td><strong><?= htmlspecialchars($func['name']) ?></strong></td>
                            <td><?= htmlspecialchars($func['email']) ?></td>

                            <!-- Perfil com uma bolinha laranja na frente -->
                            <td>
                                <span style="color: #f59e0b; font-weight: bold; margin-right: 6px;">&bull;</span>
                                <span style="color: #d97706; font-weight: 500; font-size: 14px;"><?= htmlspecialchars($func['role']) ?></span>
                            </td>

                            <td style="text-align: right;">
                                <div class="icones-acoes">

                                    <!-- WhatsApp (só aparece se tiver telefone) -->
                                    <?php if ($numeroWhats != ''): ?>
                                        <a href="https://wa.me/55<?= $numeroWhats ?>" target="_blank" title="WhatsApp" class="icone icone-whats">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>

                                    <!-- E-mail (abre o Gmail já com o destinatário) -->
                                    <?php if ($func['email'] != ''): ?>
                                        <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= $func['email'] ?>" target="_blank" title="E-mail" class="icone">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Ver detalhes -->
                                    <a href="<?= $controller ?>?acao=visualizar&id=<?= $func['id'] ?>" title="Visualizar" class="icone">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>

                                    <!-- Editar -->
                                    <a href="<?= $controller ?>?acao=editar&id=<?= $func['id'] ?>" title="Editar" class="icone icone-editar">
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