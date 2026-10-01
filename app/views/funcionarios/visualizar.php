<?php
if (!isset($funcionario)) {
    $id = $_GET['id'] ?? null;
    header("Location: " . BASE_URL . "/app/controllers/FuncionarioController.php?acao=visualizar" . ($id ? "&id=" . $id : ""));
    exit;
}
/** @var array $funcionario */
/** @var array|false $filial_vinculada */

$tituloPagina = "Visualizar Funcionário";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<main class="conteudo financeiro-page">
    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Detalhes do Funcionário</h1>
            <p>Visualizando informações de <?= htmlspecialchars($funcionario['name']) ?></p>
        </div>
        <div class="fin-header-actions" style="display: flex; gap: 8px;">
            <a href="<?= BASE_URL ?>/app/controllers/FuncionarioController.php?acao=listar" class="fin-btn fin-btn-secondary">Voltar</a>
            <a href="<?= BASE_URL ?>/app/controllers/FuncionarioController.php?acao=editar&id=<?= $funcionario['id'] ?>" class="fin-btn fin-btn-primary">Editar Cadastro</a>
        </div>
    </div>

    <div class="fin-table-card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
            <div>
                <h2 style="margin: 0 0 4px 0; color: #1e293b;"><?= htmlspecialchars($funcionario['name']) ?></h2>
                <span class="fin-status-badge fin-status-ativo"><?= htmlspecialchars($funcionario['role']) ?></span>
            </div>
            
            <div style="display: flex; gap: 8px;">
                <?php 
                    $whatsappNumber = preg_replace('/\D/', '', $funcionario['telefone'] ?? '');
                    if (!empty($whatsappNumber)): 
                ?>
                    <a href="https://wa.me/55<?= $whatsappNumber ?>" target="_blank" class="fin-btn fin-btn-sm" style="background: #25D366; color: white; display: inline-flex; align-items: center; gap: 4px;">
                        <span>💬</span> WhatsApp
                    </a>
                <?php endif; ?>

                <?php if (!empty($funcionario['email'])): ?>
                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($funcionario['email']) ?>" target="_blank" class="fin-btn fin-btn-sm" style="background: #EA4335; color: white; display: inline-flex; align-items: center; gap: 4px;">
                        <span>✉️</span> E-mail
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
            <div>
                <h3 style="font-size: 14px; color: #64748b; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Contato</h3>
                
                <div style="margin-bottom: 12px;">
                    <span style="display: block; font-size: 12px; color: #94a3b8;">E-mail</span>
                    <span style="font-weight: 500; color: #1e293b;"><?= htmlspecialchars($funcionario['email'] ?? 'Não informado') ?></span>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <span style="display: block; font-size: 12px; color: #94a3b8;">Telefone</span>
                    <span style="font-weight: 500; color: #1e293b;"><?= htmlspecialchars($funcionario['telefone'] ?? 'Não informado') ?></span>
                </div>
                
                <div>
                    <span style="display: block; font-size: 12px; color: #94a3b8;">Endereço</span>
                    <span style="font-weight: 500; color: #1e293b;"><?= htmlspecialchars($funcionario['endereco'] ?? 'Não informado') ?></span>
                </div>
            </div>

            <div>
                <h3 style="font-size: 14px; color: #64748b; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Informações do Sistema</h3>
                
                <div style="margin-bottom: 12px;">
                    <span style="display: block; font-size: 12px; color: #94a3b8;">ID no Sistema</span>
                    <span style="font-weight: 500; color: #1e293b;">#<?= htmlspecialchars($funcionario['id']) ?></span>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <span style="display: block; font-size: 12px; color: #94a3b8;">Vínculo de Unidade</span>
                    <span style="font-weight: 500; color: #1e293b;">
                        <?php if ($filial_vinculada): ?>
                            <?= htmlspecialchars($filial_vinculada['nome']) ?>
                            <?= !empty($filial_vinculada['cnpj']) ? '<br><small style="color: #64748b;">CNPJ: ' . htmlspecialchars($filial_vinculada['cnpj']) . '</small>' : '' ?>
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
