<?php
// app/views/alunos/visualizar.php

if (!isset($aluno, $baseUrl)) {
    http_response_code(403);
    exit;
}

$tituloPagina = 'Dados do aluno';

$cssEspecifico =
    BASE_URL . '/assets/css/jorge-financeiro.css';

$h = static fn($v) => htmlspecialchars(
    (string) $v,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$moeda = static fn($v) =>
    'R$ ' . number_format((float) $v, 2, ',', '.');

$data = static fn($v) =>
    $v ? date('d/m/Y', strtotime($v)) : 'Não informado';

$recepcao =
    ($_SESSION['usuario_role'] ?? '') === 'Recepcao';

$contratos = $contratos ?? [];
$contas = $contas ?? [];

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link
    rel="stylesheet"
    href="<?= BASE_URL ?>/assets/css/alunos.css">

<main class="conteudo financeiro-page alunos-page">

    <div class="fin-page-header">

        <div class="fin-header-info">

            <h1><?= $h($aluno['nome']) ?></h1>

            <p>
                <?= $h($aluno['nome_filial']) ?>
            </p>

        </div>

        <div class="fin-header-actions">

            <a
                href="<?= $h($baseUrl) ?>?acao=listar"
                class="fin-btn fin-btn-secondary">

                Voltar

            </a>

            <a
                href="<?= $h($baseUrl) ?>?acao=editar&amp;id=<?= (int) $aluno['id'] ?>"
                class="fin-btn fin-btn-primary">

                Editar cadastro

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

    <section
        class="fin-form-card"
        style="max-width:none">

        <h2>Dados cadastrais</h2>

        <div
            class="fin-form-grid"
            style="margin-top:24px">

            <?php foreach ([
                'Nome completo' => $aluno['nome'],
                'CPF' => $aluno['cpf'],
                'RG' => $aluno['rg'],
                'Nascimento' => $data($aluno['nascimento']),
                'Sexo' => $aluno['sexo'],
                'E-mail' => $aluno['email'],
                'Telefone' => $aluno['telefone'],
                'Endereço' => $aluno['endereco'],
                'Unidade' => $aluno['nome_filial'],
                'Status do cadastro' => $aluno['status']
            ] as $rotulo => $valor): ?>

                <div class="fin-form-group">

                    <span class="fin-form-label">

                        <?= $h($rotulo) ?>

                    </span>

                    <span>

                        <?= $h(
                            $valor !== null && $valor !== ''
                                ? $valor
                                : 'Não informado'
                        ) ?>

                    </span>

                </div>

            <?php endforeach; ?>

        </div>

    </section>

    <section class="fin-table-card">

        <div class="fin-table-header">

            <h2>Matrículas e contratos</h2>

        </div>

        <div class="fin-table-responsive">

            <table class="fin-table">

                <thead>

                    <tr>

                        <th>Matrícula</th>
                        <th>Plano</th>
                        <th>Unidade</th>
                        <th>Início</th>
                        <th>Término</th>
                        <th>Valor do contrato</th>
                        <th>Situação</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (!$contratos): ?>

                        <tr>

                            <td
                                colspan="7"
                                class="contas-vazio">

                                Nenhuma matrícula encontrada.

                            </td>

                        </tr>

                    <?php endif; ?>

                    <?php foreach ($contratos as $m): ?>

                        <?php

                        if (
                            $m['status_matricula'] === 'pendente'
                        ) {

                            $situacao = 'Aguardando pagamento';

                            $cor = 'var(--fin-warning-dark)';

                        } elseif (
                            $m['fim'] < date('Y-m-d')
                        ) {

                            $situacao = 'Encerrada';

                            $cor = 'var(--fin-text-muted)';

                        } elseif ((int) $m['ativa'] === 1) {

                            $situacao = 'Ativa';

                            $cor = 'var(--fin-success)';

                        } else {

                            $situacao = 'Inativa';

                            $cor = 'var(--fin-text-muted)';
                        }

                        ?>

                        <tr>

                            <td>
                                #<?= (int) $m['id'] ?>
                            </td>

                            <td>
                                <?= $h($m['nome_plano']) ?>
                            </td>

                            <td>
                                <?= $h($m['nome_filial']) ?>
                            </td>

                            <td>
                                <?= $h($data($m['inicio'])) ?>
                            </td>

                            <td>
                                <?= $h($data($m['fim'])) ?>
                            </td>

                            <td>
                                <?= $h($moeda($m['valor'])) ?>
                            </td>

                            <td style="color:<?= $cor ?>">

                                <?= $h($situacao) ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </section>

    <section class="fin-table-card">

        <div class="fin-table-header">

            <h2>Cobranças individuais</h2>

            <?php if ($recepcao): ?>

                <a
                    href="<?= BASE_URL ?>/app/controllers/RecebimentoController.php"
                    class="fin-btn fin-btn-primary fin-btn-sm">

                    Recebimentos

                </a>

            <?php endif; ?>

        </div>

        <div class="fin-table-responsive">

            <table class="fin-table">

                <thead>

                    <tr>

                        <th>Matrícula</th>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Situação</th>
                        <th>Forma de pagamento</th>
                        <th>Recebido em</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (!$contas): ?>

                        <tr>

                            <td
                                colspan="6"
                                class="contas-vazio">

                                Nenhuma cobrança encontrada.

                            </td>

                        </tr>

                    <?php endif; ?>

                    <?php foreach ($contas as $c): ?>

                        <tr>

                            <td>
                                #<?= (int) $c['matricula_id'] ?>
                            </td>

                            <td>
                                <?= $h($data($c['vencimento'])) ?>
                            </td>

                            <td>
                                <?= $h($moeda($c['valor'])) ?>
                            </td>

                            <td style="color:<?= $c['status'] === 'Pago'
                                ? 'var(--fin-success)'
                                : 'var(--fin-warning-dark)' ?>">

                                <?= $h($c['status']) ?>

                            </td>

                            <td>
                                <?= $h(
                                    $c['forma_pagamento']
                                    ?: 'Não registrado'
                                ) ?>
                            </td>

                            <td>

                                <?= $h(
                                    $c['pago_em']
                                        ? date(
                                            'd/m/Y H:i',
                                            strtotime($c['pago_em'])
                                        )
                                        : 'Pendente'
                                ) ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>