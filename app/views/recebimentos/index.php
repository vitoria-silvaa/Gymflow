<?php
// app/views/recebimentos/index.php

if (
    !isset($estado, $url, $csrf) ||
    ($_SESSION['usuario_role'] ?? '') !== 'Recepcao'
) {
    http_response_code(403);
    exit;
}

$tituloPagina = 'Recebimentos';

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
    date('d/m/Y', strtotime($v));

$origens = [
    'portfolio' => 'Portfólio',
    'recepcao' => 'Recepção',
    'crm' => 'CRM'
];

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main class="conteudo financeiro-page contas-receber-page">

    <div class="fin-page-header">

        <div class="fin-header-info">

            <h1>Recebimentos</h1>

            <p>
                Localize o aluno e confirme o recebimento
                de suas cobranças.
            </p>

        </div>

        <div class="fin-header-actions">

            <a
                href="<?= BASE_URL ?>/app/controllers/CaixaFisicoController.php"
                class="fin-btn fin-btn-secondary">

                Caixa físico

            </a>

        </div>

    </div>

    <?php if ($erro !== ''): ?>

        <div class="fin-alert fin-alert-danger" role="alert">
            <?= $h($erro) ?>
        </div>

    <?php endif; ?>

    <?php if ($sucesso !== ''): ?>

        <div class="fin-alert fin-alert-success" role="status">
            <?= $h($sucesso) ?>
        </div>

    <?php endif; ?>

    <?php if (!$conta): ?>

        <section class="fin-form-card" style="max-width:none">

            <form method="POST" action="<?= $h($url) ?>">

                <input type="hidden" name="csrf"
                       value="<?= $h($csrf) ?>">

                <div class="fin-form-grid">

                    <div class="fin-form-group">

                        <label class="fin-form-label" for="tipo">
                            Buscar por
                        </label>

                        <select
                            class="fin-form-select"
                            name="tipo"
                            id="tipo">

                            <?php foreach ([
                                'cpf' => 'CPF',
                                'nome' => 'Nome completo',
                                'matricula' => 'Número da matrícula'
                            ] as $valor => $rotulo): ?>

                                <option
                                    value="<?= $h($valor) ?>"
                                    <?= $estado['tipo'] === $valor
                                        ? 'selected'
                                        : '' ?>>

                                    <?= $h($rotulo) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="fin-form-group">

                        <label class="fin-form-label" for="busca">
                            Identificação do aluno
                        </label>

                        <input
                            type="text"
                            class="fin-form-input"
                            id="busca"
                            name="busca"
                            maxlength="100"
                            autocomplete="off"
                            value="<?= $h($estado['busca']) ?>"
                            required>

                    </div>

                </div>

                <div class="fin-form-actions">

                    <button
                        type="submit"
                        name="acao"
                        value="buscar"
                        class="fin-btn fin-btn-primary">

                        Buscar aluno

                    </button>

                    <button
                        type="submit"
                        name="acao"
                        value="limpar"
                        class="fin-btn fin-btn-secondary"
                        formnovalidate>

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
                                <th>Unidade</th>
                                <th>Origem</th>
                                <th>Vencimento</th>
                                <th>Valor</th>
                                <th>Situação</th>
                                <th>Ações</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php if (!$contas): ?>

                                <tr>
                                    <td colspan="8" class="contas-vazio">
                                        Nenhuma cobrança pendente encontrada.
                                    </td>
                                </tr>

                            <?php endif; ?>

                            <?php foreach ($contas as $c): ?>

                                <tr>

                                    <td>
                                        #<?= (int) $c['matricula_id'] ?>
                                    </td>

                                    <td><?= $h($c['plano_nome']) ?></td>

                                    <td><?= $h($c['filial_nome']) ?></td>

                                    <td>
                                        <?= $h(
                                            $origens[$c['origem'] ?? '']
                                            ?? 'Não registrada'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= $h($data($c['vencimento'])) ?>
                                    </td>

                                    <td>
                                        <?= $h($moeda($c['valor'])) ?>
                                    </td>

                                    <td class="conta-status-aberto">

                                        <?= $h(
                                            $c['vencimento'] < $c['hoje']
                                                ? 'Vencido'
                                                : $c['status']
                                        ) ?>

                                    </td>

                                    <td>

                                        <form
                                            method="POST"
                                            action="<?= $h($url) ?>">

                                            <input
                                                type="hidden"
                                                name="csrf"
                                                value="<?= $h($csrf) ?>">

                                            <input
                                                type="hidden"
                                                name="acao"
                                                value="selecionar">

                                            <input
                                                type="hidden"
                                                name="conta_id"
                                                value="<?= (int) $c['id'] ?>">

                                            <button
                                                type="submit"
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

            <?php if (count($contas) === 100): ?>

                <p>
                    São exibidas até 100 cobranças.
                    Pesquise pela matrícula para consultar
                    um contrato específico.
                </p>

            <?php endif; ?>

        <?php endif; ?>

    <?php else: ?>

        <section class="fin-form-card">

            <h2>Confirmar recebimento</h2>

            <p class="subtitle">
                Confira os dados antes de confirmar o pagamento.
            </p>

            <div class="fin-info-box">

                <?php foreach ([
                    'Aluno' => $conta['aluno_nome'],
                    'Unidade' => $conta['filial_nome'],
                    'Plano' => $conta['plano_nome'],
                    'Matrícula' => '#' . $conta['matricula_id'],
                    'Vencimento' => $data($conta['vencimento']),
                    'Valor a receber' => $moeda($conta['valor'])
                ] as $rotulo => $valor): ?>

                    <div class="fin-info-box-item">

                        <span class="label">
                            <?= $h($rotulo) ?>
                        </span>

                        <span class="value">
                            <?= $h($valor) ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

            <?php if ($conta['ativar']): ?>

                <p class="subtitle">
                    A matrícula será ativada após a confirmação
                    do recebimento.
                </p>

            <?php endif; ?>

            <form method="POST" action="<?= $h($url) ?>">

                <input type="hidden" name="csrf"
                       value="<?= $h($csrf) ?>">

                <input type="hidden" name="acao"
                       value="confirmar">

                <input type="hidden" name="conta_id"
                       value="<?= (int) $conta['id'] ?>">

                <div class="fin-form-group" style="margin-bottom:18px">

                    <label class="fin-form-label" for="metodo">
                        Forma de pagamento
                    </label>

                    <select
                        class="fin-form-select"
                        name="metodo"
                        id="metodo"
                        required>

                        <?php foreach (
                            $conta['metodos'] as $valor => $rotulo
                        ): ?>

                            <option
                                value="<?= $h($valor) ?>"
                                <?= $estado['metodo'] === $valor
                                    ? 'selected'
                                    : '' ?>>

                                <?= $h($rotulo) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <?php if (isset($conta['metodos']['dinheiro'])): ?>

                    <div
                        id="informacao-caixa"
                        data-aberto="<?= $conta['caixa_aberto']
                            ? '1'
                            : '0' ?>"
                        style="display:none;margin-bottom:18px">

                        <?php if ($conta['caixa_aberto']): ?>

                            <p class="subtitle">
                                Caixa aberto. O valor será registrado
                                automaticamente na unidade.
                            </p>

                        <?php else: ?>

                            <div class="fin-alert fin-alert-danger">

                                Abra o caixa desta unidade antes
                                de confirmar um pagamento em dinheiro.

                            </div>

                            <a
                                class="fin-btn fin-btn-secondary"
                                href="<?= BASE_URL ?>/app/controllers/CaixaFisicoController.php?filial_id=<?= (int) $conta['unidade_id'] ?>">

                                Abrir caixa

                            </a>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

                <div
                    class="fin-form-group"
                    id="grupo-referencia"
                    style="margin-bottom:18px">

                    <label
                        class="fin-form-label"
                        for="referencia"
                        id="referencia-label">

                        Referência da transação

                    </label>

                    <input
                        type="text"
                        class="fin-form-input"
                        name="referencia"
                        id="referencia"
                        maxlength="100"
                        autocomplete="off"
                        value="<?= $h($estado['referencia']) ?>">

                    <small id="referencia-ajuda"></small>

                </div>

                <div class="fin-form-group">

                    <label style="display:flex;gap:10px;align-items:flex-start">

                        <input
                            type="checkbox"
                            id="verificado"
                            name="verificado"
                            value="1"
                            required>

                        <span id="texto-verificacao">
                            Confirmo que verifiquei o recebimento
                            integral do valor.
                        </span>

                    </label>

                </div>

                <div class="fin-form-actions">

                    <button
                        type="submit"
                        id="confirmar-recebimento"
                        class="fin-btn fin-btn-primary">

                        Confirmar recebimento

                    </button>

                </div>

            </form>

            <form
                method="POST"
                action="<?= $h($url) ?>"
                style="margin-top:12px">

                <input type="hidden" name="csrf"
                       value="<?= $h($csrf) ?>">

                <input type="hidden" name="acao"
                       value="voltar">

                <button
                    type="submit"
                    class="fin-btn fin-btn-secondary">

                    Voltar

                </button>

            </form>

        </section>

    <?php endif; ?>

</main>

<script>
(function () {

    const tipo = document.getElementById('tipo');
    const busca = document.getElementById('busca');

    if (tipo && busca) {

        const configurarBusca = function () {

            if (tipo.value === 'cpf') {
                busca.maxLength = 14;
                busca.inputMode = 'numeric';
                busca.placeholder = '000.000.000-00';

            } else if (tipo.value === 'matricula') {
                busca.maxLength = 11;
                busca.inputMode = 'numeric';
                busca.placeholder = 'Número da matrícula';

            } else {
                busca.maxLength = 100;
                busca.inputMode = 'text';
                busca.placeholder = 'Nome completo';
            }
        };

        tipo.addEventListener('change', function () {
            busca.value = '';
            configurarBusca();
        });

        busca.addEventListener('input', function () {

            if (tipo.value !== 'cpf') {
                return;
            }

            const n = busca.value
                .replace(/\D/g, '')
                .slice(0, 11);

            busca.value =
                n.slice(0, 3) +
                (n.length > 3 ? '.' + n.slice(3, 6) : '') +
                (n.length > 6 ? '.' + n.slice(6, 9) : '') +
                (n.length > 9 ? '-' + n.slice(9) : '');
        });

        configurarBusca();
    }

    const metodo = document.getElementById('metodo');

    if (!metodo) {
        return;
    }

    const referencia = document.getElementById('referencia');
    const grupo = document.getElementById('grupo-referencia');
    const rotulo = document.getElementById('referencia-label');
    const ajuda = document.getElementById('referencia-ajuda');
    const caixa = document.getElementById('informacao-caixa');
    const verificar = document.getElementById('verificado');
    const texto = document.getElementById('texto-verificacao');
    const confirmar = document.getElementById('confirmar-recebimento');

    function configurarMetodo() {

        const dinheiro = metodo.value === 'dinheiro';
        const pix = metodo.value === 'pix';

        grupo.style.display = dinheiro ? 'none' : '';

        referencia.disabled = dinheiro;
        referencia.required = pix;

        rotulo.textContent = pix
            ? 'Referência da transação Pix *'
            : 'Referência da transação (opcional)';

        ajuda.textContent = pix
            ? 'Informe a identificação da transação recebida.'
            : 'Informe o código da transação, se disponível.';

        if (caixa) {
            caixa.style.display = dinheiro ? '' : 'none';
        }

        confirmar.disabled =
            dinheiro &&
            (!caixa || caixa.dataset.aberto !== '1');

        texto.textContent = dinheiro
            ? 'Confirmo que recebi e conferi o valor integral em dinheiro.'
            : 'Confirmo que verifiquei o recebimento integral na conta bancária ou na maquininha da academia.';
    }

    metodo.addEventListener('change', function () {

        verificar.checked = false;
        referencia.value = '';

        configurarMetodo();
    });

    configurarMetodo();

})();
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>