<?php

/** @var array<string, mixed> $aluno */
/** @var array<string, mixed>|false $matricula */
/** @var array<int, array<string, mixed>> $contas */
/** @var array<int, array<string, mixed>> $planos */
/** @var string $baseUrl */
/** @var string $erro */
/** @var string $sucesso */
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<main class="conteudo financeiro-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Dados do Aluno</h1>
            <p>Visualize as informações completas, matrícula e histórico de pagamentos.</p>
        </div>
        <div class="fin-header-actions">
            <a href="<?= $baseUrl ?>?acao=listar" class="fin-btn fin-btn-secondary">
                Voltar
            </a>
            <a href="<?= $baseUrl ?>?acao=editar&id=<?= $aluno['id'] ?>" class="fin-btn fin-btn-primary">
                Editar Aluno
            </a>
        </div>
    </div>

    <?php if ($erro !== ''): ?>
        <div style="background-color: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px; font-weight: bold;">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <?php if ($sucesso !== ''): ?>
        <div style="background-color: #dcfce3; color: #15803d; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px; font-weight: bold;">
            <?= htmlspecialchars($sucesso) ?>
        </div>
    <?php endif; ?>

    <!-- Seção de Dados Cadastrais -->
    <section class="fin-table-card" style="margin-bottom: 24px;">
        <h3 style="padding: 20px; border-bottom: 1px solid #e2e8f0; margin: 0; color: #1e293b;">Dados Cadastrais</h3>
        <div style="padding: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
            <p><strong>Nome:</strong> <br> <?= htmlspecialchars($aluno['nome']) ?></p>
            <p><strong>CPF:</strong> <br> <?= htmlspecialchars($aluno['cpf']) ?></p>
            <p><strong>RG:</strong> <br> <?= htmlspecialchars($aluno['rg'] ?? '') ?></p>
            <p><strong>Sexo:</strong> <br> <?= htmlspecialchars($aluno['sexo']) ?></p>
            <p><strong>Nascimento:</strong> <br> <?= date('d/m/Y', strtotime($aluno['nascimento'])) ?></p>
            <p><strong>E-mail:</strong> <br> <?= htmlspecialchars($aluno['email']) ?></p>
            <p><strong>Telefone:</strong> <br> <?= htmlspecialchars($aluno['telefone']) ?></p>
            <p><strong>Endereço:</strong> <br> <?= htmlspecialchars($aluno['endereco'] ?? '') ?></p>
            <p><strong>Filial:</strong> <br> <?= htmlspecialchars($aluno['nome_filial']) ?></p>
            <p><strong>Status:</strong> <br> 
                <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background-color: <?= $aluno['status'] === 'Ativo' ? '#dcfce3' : '#fee2e2' ?>; color: <?= $aluno['status'] === 'Ativo' ? '#15803d' : '#b91c1c' ?>;">
                    <?= htmlspecialchars($aluno['status']) ?>
                </span>
            </p>
        </div>
    </section>

    <!-- Seção de Matrícula -->
    <section class="fin-table-card" style="margin-bottom: 24px;">
        <h3 style="padding: 20px; border-bottom: 1px solid #e2e8f0; margin: 0; color: #1e293b;">Matrícula</h3>
        <div style="padding: 20px;">
            <?php if ($matricula): ?>
                <div style="display: flex; gap: 40px; flex-wrap: wrap;">
                    <p><strong>Plano:</strong> <br> <?= htmlspecialchars($matricula['nome_plano']) ?></p>
                    <p><strong>Período:</strong> <br> <?= date('d/m/Y', strtotime($matricula['inicio'])) ?> até <?= date('d/m/Y', strtotime($matricula['fim'])) ?></p>
                    <p><strong>Valor:</strong> <br> R$ <?= number_format($matricula['valor'], 2, ',', '.') ?></p>
                </div>
            <?php else: ?>
                <p style="color: #64748b; margin-bottom: 16px;">O aluno não possui matrícula ativa.</p>

                <form action="<?= $baseUrl ?>?acao=matricular" method="POST" class="fin-form-grid" style="display: flex; gap: 16px; align-items: flex-end; padding: 16px; background-color: #f8fafc; border-radius: 8px;">
                    <input type="hidden" name="aluno_id" value="<?= $aluno['id'] ?>">

                    <div class="fin-form-group" style="flex: 2;">
                        <label>Plano:</label>
                        <select name="plano_id" class="fin-select" required>
                            <option value="">Selecione o Plano</option>
                            <?php foreach ($planos as $plano): ?>
                                <option value="<?= $plano['id'] ?>">
                                    <?= htmlspecialchars($plano['nome']) ?> - R$ <?= number_format($plano['valor'], 2, ',', '.') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fin-form-group" style="flex: 1;">
                        <label>Data de início:</label>
                        <input type="date" name="data_inicio" class="fin-input" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="fin-form-group" style="flex: 1;">
                        <label>Desconto (R$):</label>
                        <input type="text" name="desconto" class="fin-input" value="0,00">
                    </div>

                    <div class="fin-form-group">
                        <button type="submit" class="fin-btn fin-btn-primary">Matricular</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <!-- Seção de Mensalidades -->
    <section class="fin-table-card">
        <h3 style="padding: 20px; border-bottom: 1px solid #e2e8f0; margin: 0; color: #1e293b;">Mensalidades</h3>
        <div class="fin-table-responsive">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Pagamento</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($contas) === 0): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #64748b;">Nenhuma mensalidade encontrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contas as $conta): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($conta['vencimento'])) ?></td>
                                <td>R$ <?= number_format($conta['valor'], 2, ',', '.') ?></td>
                                <td>
                                    <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background-color: <?= $conta['status'] === 'Pago' ? '#dcfce3' : '#fee2e2' ?>; color: <?= $conta['status'] === 'Pago' ? '#15803d' : '#b91c1c' ?>;">
                                        <?= htmlspecialchars($conta['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($conta['forma_pagamento'] ?? 'Pendente') ?></td>
                                <td>
                                    <?php if ($conta['status'] === 'Aberto'): ?>
                                        <form action="<?= $baseUrl ?>?acao=pagar" method="POST" style="margin: 0;">
                                            <input type="hidden" name="aluno_id" value="<?= $aluno['id'] ?>">
                                            <input type="hidden" name="conta_id" value="<?= $conta['id'] ?>">
                                            <button type="submit" class="fin-btn" style="background-color: #15803d; color: white; padding: 6px 12px; font-size: 12px;">
                                                Registrar Pagamento
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #64748b; font-size: 14px;">Pago</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>