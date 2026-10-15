<?php
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

// Campos que podem vir vazios do banco
$rg = '';
if (isset($aluno['rg'])) {
    $rg = $aluno['rg'];
}

$endereco = '';
if (isset($aluno['endereco'])) {
    $endereco = $aluno['endereco'];
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<!-- Estilos só desta página -->
<style>
    .titulo-secao {
        padding: 20px;
        border-bottom: 1px solid #e2e8f0;
        margin: 0;
        color: #1e293b;
    }

    .mensagem {
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 24px;
        font-weight: bold;
    }

    .mensagem-erro {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    .mensagem-sucesso {
        background-color: #dcfce3;
        color: #15803d;
    }

    /* Etiqueta de status (verde ou vermelha) */
    .etiqueta {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }

    .etiqueta-verde {
        background-color: #dcfce3;
        color: #15803d;
    }

    .etiqueta-vermelha {
        background-color: #fee2e2;
        color: #b91c1c;
    }
</style>

<main class="conteudo financeiro-page">

    <!-- Título e botões de voltar e editar -->
    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Dados do Aluno</h1>
            <p>Visualize as informações completas, matrícula e histórico de pagamentos.</p>
        </div>
        <div class="fin-header-actions">
            <a href="<?= $baseUrl ?>?acao=listar" class="fin-btn fin-btn-secondary">Voltar</a>
            <a href="<?= $baseUrl ?>?acao=editar&id=<?= $aluno['id'] ?>" class="fin-btn fin-btn-primary">Editar Aluno</a>
        </div>
    </div>

    <!-- Mensagens de erro e de sucesso -->
    <?php if ($erro != ''): ?>
        <div class="mensagem mensagem-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <?php if ($sucesso != ''): ?>
        <div class="mensagem mensagem-sucesso"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <!-- Dados cadastrais -->
    <section class="fin-table-card" style="margin-bottom: 24px;">
        <h3 class="titulo-secao">Dados Cadastrais</h3>
        <div style="padding: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
            <p><strong>Nome:</strong> <br> <?= htmlspecialchars($aluno['nome']) ?></p>
            <p><strong>CPF:</strong> <br> <?= htmlspecialchars($aluno['cpf']) ?></p>
            <p><strong>RG:</strong> <br> <?= htmlspecialchars($rg) ?></p>
            <p><strong>Sexo:</strong> <br> <?= htmlspecialchars($aluno['sexo']) ?></p>
            <!-- date() muda a data do formato do banco (2000-12-31) para 31/12/2000 -->
            <p><strong>Nascimento:</strong> <br> <?= date('d/m/Y', strtotime($aluno['nascimento'])) ?></p>
            <p><strong>E-mail:</strong> <br> <?= htmlspecialchars($aluno['email']) ?></p>
            <p><strong>Telefone:</strong> <br> <?= htmlspecialchars($aluno['telefone']) ?></p>
            <p><strong>Endereço:</strong> <br> <?= htmlspecialchars($endereco) ?></p>
            <p><strong>Filial:</strong> <br> <?= htmlspecialchars($aluno['nome_filial']) ?></p>
            <p><strong>Status:</strong> <br>
                <?php if ($aluno['status'] == 'Ativo'): ?>
                    <span class="etiqueta etiqueta-verde"><?= htmlspecialchars($aluno['status']) ?></span>
                <?php else: ?>
                    <span class="etiqueta etiqueta-vermelha"><?= htmlspecialchars($aluno['status']) ?></span>
                <?php endif; ?>
            </p>
        </div>
    </section>

    <!-- Matrícula -->
    <section class="fin-table-card" style="margin-bottom: 24px;">
        <h3 class="titulo-secao">Matrícula</h3>
        <div style="padding: 20px;">

            <?php if ($matricula): ?>
                <!-- O aluno já tem matrícula: mostra os dados -->
                <div style="display: flex; gap: 40px; flex-wrap: wrap;">
                    <p><strong>Plano:</strong> <br> <?= htmlspecialchars($matricula['nome_plano']) ?></p>
                    <p><strong>Período:</strong> <br> <?= date('d/m/Y', strtotime($matricula['inicio'])) ?> até <?= date('d/m/Y', strtotime($matricula['fim'])) ?></p>
                    <!-- number_format mostra o valor no formato brasileiro: 1.234,56 -->
                    <p><strong>Valor:</strong> <br> R$ <?= number_format($matricula['valor'], 2, ',', '.') ?></p>
                </div>

            <?php else: ?>
                <!-- O aluno não tem matrícula: mostra o formulário para matricular -->
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
                        <!-- Já vem preenchido com a data de hoje -->
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

    <!-- Mensalidades -->
    <section class="fin-table-card">
        <h3 class="titulo-secao">Mensalidades</h3>
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
                    <?php if (count($contas) == 0): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #64748b;">Nenhuma mensalidade encontrada.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($contas as $conta): ?>

                        <?php
                        // Se ainda não tem forma de pagamento, mostra "Pendente"
                        $formaPagamento = 'Pendente';
                        if (isset($conta['forma_pagamento'])) {
                            $formaPagamento = $conta['forma_pagamento'];
                        }
                        ?>

                        <tr>
                            <td><?= date('d/m/Y', strtotime($conta['vencimento'])) ?></td>
                            <td>R$ <?= number_format($conta['valor'], 2, ',', '.') ?></td>

                            <!-- Verde se estiver pago, vermelho se não -->
                            <td>
                                <?php if ($conta['status'] == 'Pago'): ?>
                                    <span class="etiqueta etiqueta-verde"><?= htmlspecialchars($conta['status']) ?></span>
                                <?php else: ?>
                                    <span class="etiqueta etiqueta-vermelha"><?= htmlspecialchars($conta['status']) ?></span>
                                <?php endif; ?>
                            </td>

                            <td><?= htmlspecialchars($formaPagamento) ?></td>

                            <!-- Se a conta está em aberto, mostra o botão para registrar o pagamento -->
                            <td>
                                <?php if ($conta['status'] == 'Aberto'): ?>
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
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>