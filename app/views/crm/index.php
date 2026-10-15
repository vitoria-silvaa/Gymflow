<?php
// Se alguém abrir esta página direto, sem passar pelo controller, manda de volta pro controller
if (!isset($tituloPagina)) {
    header("Location: " . BASE_URL . "/app/controllers/CrmController.php");
    exit;
}

$cssEspecifico = BASE_URL . '/assets/css/jorge-financeiro.css';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

// Se o controller não mandou nenhum lead, começa com a lista vazia
if (!isset($leads)) {
    $leads = [];
}

// Etapas do funil (colunas do quadro) e a classe CSS de cada uma, que define a cor
$colunas = [
    'Novo'             => 'crm-col-novo',
    'Contato Agendado' => 'crm-col-contato',
    'Experimental'     => 'crm-col-experimental',
    'Convertido'       => 'crm-col-convertido',
    'Perdido'          => 'crm-col-perdido',
];

$totalLeads = count($leads);
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<main class="conteudo crm-page">

    <!-- Título da página e botão de novo lead -->
    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>CRM / Quadro de Leads</h1>
            <p>Gerencie o funil de prospecção e conversão de potenciais alunos (<?= $totalLeads ?> lead(s) ativos).</p>
        </div>

        <div class="fin-header-actions">
            <a href="<?= $baseUrl ?>?acao=cadastrar" class="fin-btn fin-btn-primary">+ Novo Lead</a>
        </div>
    </div>

    <!-- Quadro com uma coluna para cada etapa -->
    <div class="crm-kanban-board">

        <?php foreach ($colunas as $status => $classe): ?>

            <?php
            // Separa só os leads que estão nesta etapa
            $leadsDaColuna = [];
            foreach ($leads as $lead) {
                if ($lead['status'] == $status) {
                    $leadsDaColuna[] = $lead;
                }
            }
            ?>

            <div class="crm-kanban-column <?= $classe ?>" ondragover="permitirDrop(event)" ondrop="soltar(event, '<?= $status ?>')">

                <!-- Cabeçalho da coluna: nome da etapa e quantidade -->
                <div class="crm-column-header">
                    <h3 class="crm-column-title"><?= $status ?></h3>
                    <span class="crm-column-count"><?= count($leadsDaColuna) ?></span>
                </div>

                <div class="crm-column-cards">

                    <?php if (count($leadsDaColuna) == 0): ?>
                        <div class="crm-empty-state">Nenhum lead nesta etapa</div>
                    <?php endif; ?>

                    <?php foreach ($leadsDaColuna as $lead): ?>

                        <?php
                        // htmlspecialchars evita que alguém coloque código HTML no nome e quebre a página
                        $nome     = htmlspecialchars($lead['nome']);
                        $telefone = htmlspecialchars($lead['telefone']);
                        $inicial  = strtoupper(substr($lead['nome'], 0, 1));

                        // Para o link do WhatsApp, o telefone precisa ter só números
                        $numeroWhats = str_replace([' ', '(', ')', '-'], '', $lead['telefone']);
                        ?>

                        <!-- Card do lead (pode ser arrastado para outra coluna) -->
                        <div class="crm-lead-card" draggable="true" ondragstart="iniciarDrag(event, <?= $lead['id'] ?>)">

                            <div class="crm-card-topo">
                                <!-- Bolinha com a primeira letra do nome -->
                                <div class="crm-avatar-simples"><?= $inicial ?></div>

                                <div class="crm-info-simples">
                                    <h4><?= $nome ?></h4>
                                    <p><?= $telefone != '' ? $telefone : '-' ?></p>
                                </div>

                                <!-- Botão do WhatsApp (só aparece se tiver telefone) -->
                                <?php if ($numeroWhats != ''): ?>
                                    <a href="https://wa.me/55<?= $numeroWhats ?>" target="_blank" class="crm-icon-btn crm-icon-wpp" style="margin-left:auto;" title="WhatsApp">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Etiquetas de objetivo e campanha -->
                            <div class="crm-tags-simples">
                                <?php if ($lead['objetivo'] != ''): ?>
                                    <span class="crm-tag-min"><?= htmlspecialchars($lead['objetivo']) ?></span>
                                <?php endif; ?>

                                <?php if ($lead['campanha'] != ''): ?>
                                    <span class="crm-tag-min"><?= htmlspecialchars($lead['campanha']) ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Rodapé: trocar etapa, editar e excluir -->
                            <div class="crm-rodape-simples">

                                <!-- Ao escolher outra etapa, o formulário é enviado sozinho -->
                                <form method="POST" action="<?= $baseUrl ?>?acao=atualizar_status" style="margin:0;">
                                    <input type="hidden" name="id" value="<?= $lead['id'] ?>">
                                    <select name="status" onchange="this.form.submit()" style="border:none; background:transparent; font-size:12px; color:#64748b; cursor:pointer; outline:none; font-weight:500;">
                                        <?php foreach ($colunas as $opcao => $classeOpcao): ?>
                                            <?php if ($lead['status'] == $opcao): ?>
                                                <option value="<?= $opcao ?>" selected><?= $opcao ?></option>
                                            <?php else: ?>
                                                <option value="<?= $opcao ?>"><?= $opcao ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </form>

                                <div style="display:flex; gap:12px; align-items:center;">
                                    <!-- Editar -->
                                    <a href="<?= $baseUrl ?>?acao=editar&id=<?= $lead['id'] ?>" class="crm-icon-btn" title="Editar">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>

                                    <!-- Excluir (pede confirmação antes) -->
                                    <a href="<?= $baseUrl ?>?acao=excluir&id=<?= $lead['id'] ?>" onclick="return confirm('Deseja excluir?');" class="crm-icon-btn" title="Excluir">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <!-- Formulário escondido: usado quando o card é arrastado para outra coluna -->
    <form id="form-drag-drop" method="POST" action="<?= $baseUrl ?>?acao=atualizar_status" style="display: none;">
        <input type="hidden" name="id" id="dd_lead_id">
        <input type="hidden" name="status" id="dd_lead_status">
    </form>

</main>

<script>
    // Deixa soltar o card em cima da coluna
    function permitirDrop(evento) {
        evento.preventDefault();
    }

    // Quando começa a arrastar, guarda o id do lead
    function iniciarDrag(evento, idLead) {
        evento.dataTransfer.setData("idLead", idLead);
        evento.target.style.opacity = "0.5";
    }

    // Quando solta na coluna, preenche o formulário escondido e envia
    function soltar(evento, novoStatus) {
        evento.preventDefault();
        var idLead = evento.dataTransfer.getData("idLead");

        if (idLead) {
            document.getElementById('dd_lead_id').value = idLead;
            document.getElementById('dd_lead_status').value = novoStatus;
            document.getElementById('form-drag-drop').submit();
        }
    }

    // Quando termina de arrastar, o card volta ao normal
    document.addEventListener("dragend", function (evento) {
        evento.target.style.opacity = "1";
    });
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>