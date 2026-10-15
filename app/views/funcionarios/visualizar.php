<?php
// Se alguém abrir esta página direto, sem passar pelo controller, manda pro controller
if (!isset($funcionario)) {
    $link = BASE_URL . "/app/controllers/FuncionarioController.php?acao=visualizar";

    // Se veio um id na URL, manda ele junto
    if (isset($_GET['id'])) {
        $link = $link . "&id=" . $_GET['id'];
    }

    header("Location: " . $link);
    exit;
}

$tituloPagina = "Visualizar Funcionário";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

// Endereço do controller, para não repetir nos links
$controller = BASE_URL . "/app/controllers/FuncionarioController.php";

// Dados de contato (se estiver vazio, mostra "Não informado")
$email    = 'Não informado';
$telefone = 'Não informado';
$endereco = 'Não informado';

if (!empty($funcionario['email'])) {
    $email = $funcionario['email'];
}
if (!empty($funcionario['telefone'])) {
    $telefone = $funcionario['telefone'];
}
if (!empty($funcionario['endereco'])) {
    $endereco = $funcionario['endereco'];
}

// Para o link do WhatsApp, o telefone precisa ter só números
$numeroWhats = '';
if (!empty($funcionario['telefone'])) {
    $numeroWhats = str_replace([' ', '(', ')', '-'], '', $funcionario['telefone']);
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<!-- Estilos só desta página -->
<style>
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

    .titulo-bloco {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info {
        margin-bottom: 12px;
    }

    .info-nome {
        display: block;
        font-size: 12px;
        color: #94a3b8;
    }

    .info-valor {
        font-weight: 500;
        color: #1e293b;
    }
</style>

<main class="conteudo financeiro-page">

    <!-- Título e botões de voltar e editar -->
    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Detalhes do Funcionário</h1>
            <p>Visualizando informações de <?= htmlspecialchars($funcionario['name']) ?></p>
        </div>
        <div class="fin-header-actions" style="display: flex; gap: 8px;">
            <a href="<?= $controller ?>?acao=listar" class="fin-btn fin-btn-secondary">Voltar</a>
            <a href="<?= $controller ?>?acao=editar&id=<?= $funcionario['id'] ?>" class="fin-btn fin-btn-primary">Editar Cadastro</a>
        </div>
    </div>

    <div class="fin-table-card" style="padding: 24px;">

        <!-- Nome, cargo e botões de contato -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
            <div>
                <h2 style="margin: 0 0 4px 0; color: #1e293b;"><?= htmlspecialchars($funcionario['name']) ?></h2>
                <span class="fin-status-badge fin-status-ativo"><?= htmlspecialchars($funcionario['role']) ?></span>
            </div>

            <div style="display: flex; gap: 16px; align-items: center;">

                <!-- WhatsApp (só aparece se tiver telefone) -->
                <?php if ($numeroWhats != ''): ?>
                    <a href="https://wa.me/55<?= $numeroWhats ?>" target="_blank" title="WhatsApp" class="icone icone-whats">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                    </a>
                <?php endif; ?>

                <!-- E-mail (abre o Gmail já com o destinatário) -->
                <?php if (!empty($funcionario['email'])): ?>
                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= $funcionario['email'] ?>" target="_blank" title="E-mail" class="icone">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                        </svg>
                    </a>
                <?php endif; ?>

            </div>
        </div>

        <!-- Duas colunas: contato e informações do sistema -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0;">

            <div>
                <h3 class="titulo-bloco">Contato</h3>

                <div class="info">
                    <span class="info-nome">E-mail</span>
                    <span class="info-valor"><?= htmlspecialchars($email) ?></span>
                </div>

                <div class="info">
                    <span class="info-nome">Telefone</span>
                    <span class="info-valor"><?= htmlspecialchars($telefone) ?></span>
                </div>

                <div>
                    <span class="info-nome">Endereço</span>
                    <span class="info-valor"><?= htmlspecialchars($endereco) ?></span>
                </div>
            </div>

            <div>
                <h3 class="titulo-bloco">Informações do Sistema</h3>

                <div class="info">
                    <span class="info-nome">ID no Sistema</span>
                    <span class="info-valor">#<?= $funcionario['id'] ?></span>
                </div>

                <div class="info">
                    <span class="info-nome">Vínculo de Unidade</span>
                    <span class="info-valor">
                        <?php if ($filial_vinculada): ?>
                            <?= htmlspecialchars($filial_vinculada['nome']) ?>

                            <?php if (!empty($filial_vinculada['cnpj'])): ?>
                                <br><small style="color: #64748b;">CNPJ: <?= htmlspecialchars($filial_vinculada['cnpj']) ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            Nenhuma
                        <?php endif; ?>
                    </span>
                </div>
            </div>

        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
