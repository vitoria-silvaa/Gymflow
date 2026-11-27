<?php
// app/views/crm/index.php

if (!isset($leads, $csrf, $baseUrl)) {
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

$colunas = [
    'Novo' => 'crm-col-novo',
    'Contato Agendado' => 'crm-col-contato',
    'Experimental' => 'crm-col-experimental',
    'Aguardando pagamento' => 'crm-col-contato',
    'Convertido' => 'crm-col-convertido',
    'Perdido' => 'crm-col-perdido'
];

$admin = ($_SESSION['usuario_role'] ?? '') === 'Admin';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main class="conteudo crm-page">

    <div class="fin-page-header">

        <div class="fin-header-info">

            <h1>CRM Leads</h1>

            <p>
                Acompanhe os interessados e suas matrículas.
            </p>

        </div>

        <div class="fin-header-actions">

            <a
                href="<?= $h($baseUrl) ?>?acao=cadastrar"
                class="fin-btn fin-btn-primary">

                Novo lead

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

    <div class="crm-kanban-board">

        <?php foreach ($colunas as $statusColuna => $classe): ?>

            <?php

            $leadsDaColuna = array_filter(
                $leads,
                static fn($l) =>
                    $l['status'] === $statusColuna
            );

            $aceitaDrop = in_array(
                $statusColuna,
                $statusManuais,
                true
            );

            ?>

            <div
                class="crm-kanban-column <?= $h($classe) ?>"

                <?php if ($aceitaDrop): ?>

                    ondragover="event.preventDefault()"

                    ondrop="soltarLead(
                        event,
                        '<?= $h($statusColuna) ?>'
                    )"

                <?php endif; ?>>

                <div class="crm-column-header">

                    <h3 class="crm-column-title">
                        <?= $h($statusColuna) ?>
                    </h3>

                    <span class="crm-column-count">
                        <?= count($leadsDaColuna) ?>
                    </span>

                </div>

                <div class="crm-column-cards">

                    <?php if (!$leadsDaColuna): ?>

                        <div class="crm-empty-state">
                            Nenhum lead nesta etapa
                        </div>

                    <?php endif; ?>

                    <?php foreach ($leadsDaColuna as $lead): ?>

                        <?php

                        $leadId = (int) $lead['id'];

                        $matriculaId =
                            (int) ($lead['matricula_id'] ?? 0);

                        $protegido =
                            $matriculaId > 0 ||
                            in_array(
                                $lead['status'],
                                [
                                    'Convertido',
                                    'Aguardando pagamento'
                                ],
                                true
                            );

                        $podeMatricular =
                            !$protegido &&
                            in_array(
                                $lead['status'],
                                [
                                    'Novo',
                                    'Contato Agendado',
                                    'Experimental'
                                ],
                                true
                            );

                        $telefone = preg_replace(
                            '/\D/',
                            '',
                            (string) $lead['telefone']
                        );

                        $urlMatricula = BASE_URL .
                            '/app/controllers/MatriculaController.php?' .
                            http_build_query([
                                'acao' => 'identificacao',
                                'modo' => 'interno',
                                'novo' => 1,
                                'lead_id' => $leadId
                            ]);

                        ?>

                        <article
                            class="crm-lead-card"
                            draggable="<?= $protegido ? 'false' : 'true' ?>"

                            <?php if (!$protegido): ?>

                                ondragstart="iniciarDragLead(
                                    event,
                                    <?= $leadId ?>
                                )"

                            <?php endif; ?>>

                            <div class="crm-card-topo">

                                <div class="crm-info-simples">

                                    <h4><?= $h($lead['nome']) ?></h4>

                                    <p><?= $h($lead['telefone']) ?></p>

                                </div>

                                <?php if (in_array(
                                    strlen($telefone),
                                    [10, 11],
                                    true
                                )): ?>

                                    <a
                                        href="https://wa.me/55<?= $h($telefone) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="crm-icon-btn crm-icon-wpp"
                                        title="WhatsApp"
                                        aria-label="WhatsApp">

                                        <i data-lucide="message-circle"></i>

                                    </a>

                                <?php endif; ?>

                            </div>

                            <div class="crm-lead-info">

                                <p>
                                    <?= $h($lead['nome_filial']) ?>
                                </p>

                                <?php if (!empty($lead['objetivo'])): ?>

                                    <p>
                                        Objetivo:
                                        <?= $h($lead['objetivo']) ?>
                                    </p>

                                <?php endif; ?>

                                <?php if (!empty($lead['campanha'])): ?>

                                    <p>
                                        Origem:
                                        <?= $h($lead['campanha']) ?>
                                    </p>

                                <?php endif; ?>

                                <?php if ($matriculaId): ?>

                                    <p>
                                        Matrícula #<?= $matriculaId ?>
                                    </p>

                                <?php endif; ?>

                            </div>

                            <?php if (!$protegido): ?>

                                <div class="crm-rodape-simples">

                                    <form
                                        method="POST"
                                        action="<?= $h($baseUrl) ?>?acao=atualizar_status"
                                        class="ui-m-0">

                                        <input
                                            type="hidden"
                                            name="csrf"
                                            value="<?= $h($csrf) ?>">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $leadId ?>">

                                        <select
                                            name="status"
                                            class="fin-form-select"
                                            aria-label="Etapa do lead"
                                            onchange="this.form.submit()">

                                            <?php foreach (
                                                $statusManuais as $opcao
                                            ): ?>

                                                <option
                                                    value="<?= $h($opcao) ?>"

                                                    <?= $lead['status'] === $opcao
                                                        ? 'selected'
                                                        : '' ?>>

                                                    <?= $h($opcao) ?>

                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </form>

                                    <div class="crm-lead-acoes">

                                        <a
                                            href="<?= $h($baseUrl) ?>?acao=editar&amp;id=<?= $leadId ?>"
                                            class="crm-icon-btn"
                                            title="Editar"
                                            aria-label="Editar">

                                            <i data-lucide="square-pen"></i>

                                        </a>

                                        <?php if ($admin): ?>

                                            <form
                                                method="POST"
                                                action="<?= $h($baseUrl) ?>?acao=excluir"
                                                onsubmit="return confirm('Deseja excluir este lead?')"
                                                class="ui-m-0">

                                                <input
                                                    type="hidden"
                                                    name="csrf"
                                                    value="<?= $h($csrf) ?>">

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= $leadId ?>">

                                                <button
                                                    type="submit"
                                                    class="crm-icon-btn crm-icon-btn-submit"
                                                    title="Excluir"
                                                    aria-label="Excluir">

                                                    <i data-lucide="trash-2"></i>

                                                </button>

                                            </form>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            <?php endif; ?>

                            <?php if ($podeMatricular): ?>

                                <a
                                    href="<?= $h($urlMatricula) ?>"
                                    class="fin-btn fin-btn-primary fin-btn-sm crm-btn-matricular">

                                    Matricular

                                </a>

                            <?php elseif (
                                $lead['status'] === 'Aguardando pagamento'
                            ): ?>

                                <p class="crm-status-aviso">

                                    Aguardando confirmação do recebimento.

                                </p>

                                <?php if (
                                    !$admin &&
                                    $matriculaId > 0
                                ): ?>

                                    <a
                                        href="<?= BASE_URL ?>/app/controllers/RecebimentoController.php"
                                        class="fin-btn fin-btn-secondary fin-btn-sm">

                                        Recebimentos

                                    </a>

                                <?php endif; ?>

                            <?php elseif (
                                $lead['status'] === 'Convertido'
                            ): ?>

                                <p class="crm-status-nota">

                                    <?= $matriculaId
                                        ? 'Matrícula vinculada ao lead.'
                                        : 'Conversão registrada anteriormente.' ?>

                                </p>

                            <?php endif; ?>

                        </article>

                    <?php endforeach; ?>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

    <form
        id="form-drag-drop"
        method="POST"
        action="<?= $h($baseUrl) ?>?acao=atualizar_status"
        hidden>

        <input
            type="hidden"
            name="csrf"
            value="<?= $h($csrf) ?>">

        <input type="hidden" name="id" id="dd_lead_id">

        <input type="hidden" name="status" id="dd_lead_status">

    </form>

</main>

<script>
function iniciarDragLead(evento, id) {

    evento.dataTransfer.setData(
        'text/plain',
        String(id)
    );

    evento.dataTransfer.effectAllowed = 'move';
}

function soltarLead(evento, status) {

    evento.preventDefault();

    const permitidos = [
        'Novo',
        'Contato Agendado',
        'Experimental',
        'Perdido'
    ];

    if (!permitidos.includes(status)) {
        return;
    }

    const id = evento.dataTransfer.getData('text/plain');

    if (!/^\d+$/.test(id)) {
        return;
    }

    document.getElementById('dd_lead_id').value = id;

    document.getElementById('dd_lead_status').value = status;

    document.getElementById('form-drag-drop').submit();
}
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>