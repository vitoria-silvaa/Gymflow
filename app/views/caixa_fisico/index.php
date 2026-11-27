<?php
// app/views/caixa_fisico/index.php

if (
    !isset($estado, $csrf, $baseUrl) ||
    ($_SESSION['usuario_role'] ?? '') !== 'Recepcao'
) {
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

$moeda = static fn($v) =>
    'R$ ' . number_format((float) $v, 2, ',', '.');

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main class="conteudo financeiro-page contas-receber-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Caixa físico</h1>
            <p>Registre pagamentos de mensalidades em dinheiro.</p>
        </div>
    </div>

    <?php if ($erro !== ''): ?>
        <div class="fin-alert fin-alert-danger">
            <?= $h($erro) ?>
        </div>
    <?php endif; ?>

    <?php if ($sucesso !== ''): ?>
        <div class="fin-alert fin-alert-success">
            <?= $h($sucesso) ?>
        </div>
    <?php endif; ?>

    <?php if (!$conta): ?>

        <section class="fin-form-card ui-card-full">

            <form method="POST" action="<?= $h($baseUrl) ?>">

                <input type="hidden" name="csrf"
                       value="<?= $h($csrf) ?>">

                <div class="fin-form-grid">

                    <div class="fin-form-group">

                        <label for="tipo" class="fin-form-label">
                            Buscar por
                        </label>

                        <select name="tipo" id="tipo"
                                class="fin-form-select">

                            <?php foreach ([
                                'cpf' => 'CPF',
                                'nome' => 'Nome completo',
                                'matricula' => 'Número da matrícula'
                            ] as $valor => $rotulo): ?>

                                <option value="<?= $h($valor) ?>"
                                    <?= $estado['tipo'] === $valor
                                        ? 'selected' : '' ?>>

                                    <?= $h($rotulo) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="fin-form-group">

                        <label for="busca" class="fin-form-label">
                            Identificação do aluno
                        </label>

                        <input type="text"
                               name="busca"
                               id="busca"
                               class="fin-form-input"
                               maxlength="100"
                               autocomplete="off"
                               value="<?= $h($estado['busca']) ?>"
                               required>

                    </div>

                </div>

                <div class="fin-form-actions">

                    <button type="submit"
                            name="acao"
                            value="buscar"
                            class="fin-btn fin-btn-primary">

                        Buscar aluno

                    </button>

                    <button type="submit"
                            name="acao"
                            value="limpar"
                            formnovalidate
                            class="fin-btn fin-btn-secondary">

                        Limpar

                    </button>

                </div>

            </form>

        </section>

        <?php if ($aluno): ?>

            <section class="fin-table-card">

                <div class="fin-table-header">
                    <div>
                        <h2><?= $h($aluno['nome']) ?></h2>
                        <p><?= $h($aluno['filial_nome']) ?></p>
                    </div>
                </div>

                <div class="fin-table-responsive">

                    <table class="fin-table">

                        <thead>
                            <tr>
                                <th>Matrícula</th>
                                <th>Plano</th>
                                <th>Vencimento</th>
                                <th>Valor</th>
                                <th>Ação</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (!$contas): ?>

                                <tr>
                                    <td colspan="5">
                                        Nenhuma mensalidade pendente.
                                    </td>
                                </tr>

                            <?php endif; ?>

                            <?php foreach ($contas as $c): ?>

                                <tr>

                                    <td>
                                        #<?= (int) $c['matricula_id'] ?>
                                    </td>

                                    <td><?= $h($c['plano_nome']) ?></td>

                                    <td>
                                        <?= date(
                                            'd/m/Y',
                                            strtotime($c['vencimento'])
                                        ) ?>
                                    </td>

                                    <td><?= $h($moeda($c['valor'])) ?></td>

                                    <td>

                                        <form method="POST"
                                              action="<?= $h($baseUrl) ?>">

                                            <input type="hidden"
                                                   name="csrf"
                                                   value="<?= $h($csrf) ?>">

                                            <input type="hidden"
                                                   name="acao"
                                                   value="selecionar">

                                            <input type="hidden"
                                                   name="conta_id"
                                                   value="<?= (int) $c['id'] ?>">

                                            <button type="submit"
                                                    class="fin-btn fin-btn-primary fin-btn-sm">

                                                Receber

                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </section>

        <?php endif; ?>

    <?php else: ?>

        <section class="fin-form-card">

            <h2>Receber em dinheiro</h2>

            <div class="fin-info-box">

                <?php foreach ([
                    'Aluno' => $conta['aluno_nome'],
                    'Unidade' => $conta['filial_nome'],
                    'Plano' => $conta['plano_nome'],
                    'Matrícula' => '#' . $conta['matricula_id'],
                    'Valor a receber' => $moeda($conta['valor'])
                ] as $rotulo => $valor): ?>

                    <div class="fin-info-box-item">
                        <span class="label"><?= $h($rotulo) ?></span>
                        <span class="value"><?= $h($valor) ?></span>
                    </div>

                <?php endforeach; ?>

            </div>

            <form method="POST" action="<?= $h($baseUrl) ?>">

                <input type="hidden" name="csrf"
                       value="<?= $h($csrf) ?>">

                <input type="hidden" name="acao"
                       value="confirmar">

                <input type="hidden" name="conta_id"
                       value="<?= (int) $conta['id'] ?>">

                <div class="fin-form-group">

                    <label class="ui-check-label">

                        <input type="checkbox"
                               name="confirmado"
                               value="1"
                               required>

                        Confirmo que recebi o valor integral
                        em dinheiro.

                    </label>

                </div>

                <div class="fin-form-actions">

                    <button type="submit"
                            class="fin-btn fin-btn-primary">

                        Confirmar pagamento

                    </button>

                </div>

            </form>

            <form method="POST"
                  action="<?= $h($baseUrl) ?>"
                  class="ui-mt-12">

                <input type="hidden" name="csrf"
                       value="<?= $h($csrf) ?>">

                <button type="submit"
                        name="acao"
                        value="voltar"
                        class="fin-btn fin-btn-secondary">

                    Voltar

                </button>

            </form>

        </section>

    <?php endif; ?>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>