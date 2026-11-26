<?php
// ============================================
// GYMFLOW - PORTAL DO ALUNO
// app/views/portal_aluno/faturas.php
// ============================================

if (!isset($todas_contas)) {
    http_response_code(403);
    exit('Acesso negado.');
}

$tituloPagina = 'Minhas Faturas';

$hoje = $hoje ?? date('Y-m-d');

$cssEspecifico = BASE_URL . '/assets/css/faturas.css';

$url = BASE_URL . '/app/controllers/PortalAlunoController.php?acao=faturas';

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

$esc = function ($valor) {
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
};

$data = function ($valor) {

    if (empty($valor)) {
        return 'Não informado';
    }

    $ts = strtotime($valor);

    return $ts === false
        ? 'Não informado'
        : date('d/m/Y', $ts);
};

// ============================================
// ORGANIZAÇÃO DAS ABAS
// ============================================

$abas = [

    'vencer' => [
        'titulo' => 'A vencer',
        'icone' => 'wallet',
        'classe' => 'aberto',
        'lista' => $a_vencer ?? []
    ],

    'atraso' => [
        'titulo' => 'Em atraso',
        'icone' => 'circle-alert',
        'classe' => 'atraso',
        'lista' => $em_atraso ?? []
    ],

    'pagas' => [
        'titulo' => 'Pagas',
        'icone' => 'circle-check',
        'classe' => 'pago',
        'lista' => $pagas ?? []
    ]
];

$aba = $aba ?? 'vencer';

if (!isset($abas[$aba])) {
    $aba = 'vencer';
}

$lista = $abas[$aba]['lista'];

$mensagens = [

    'vencer' =>
    'Você não possui faturas a vencer.',

    'atraso' =>
    'Você não possui faturas em atraso.',

    'pagas' =>
    'Você ainda não possui pagamentos registrados.'
];

require __DIR__ . '/../shared/header.php';

require __DIR__ . '/../shared/sidebar.php';

?>

<main class="faturas-page">

    <!-- CABEÇALHO -->

    <header class="faturas-header">

        <h1>Minhas Faturas</h1>

        <p>
            Acompanhe suas mensalidades,
            pagamentos e recibos.
        </p>

    </header>

    <!-- RESUMO FINANCEIRO -->

    <section class="faturas-resumo">

        <?php foreach ($abas as $item): ?>

            <article class="resumo-card">

                <div class="resumo-icone resumo-icone-<?= $esc($item['classe']) ?>">

                    <i data-lucide="<?= $esc($item['icone']) ?>"></i>

                </div>

                <div>

                    <span>
                        <?= $esc($item['titulo']) ?>
                    </span>

                    <strong>
                        <?= count($item['lista']) ?>
                    </strong>

                </div>

            </article>

        <?php endforeach; ?>

    </section>

    <!-- LISTAGEM -->

    <section class="faturas-lista">

        <!-- ABAS -->

        <nav
            class="faturas-tabs"
            aria-label="Situação das faturas">

            <?php foreach ($abas as $chave => $item): ?>

                <a
                    href="<?= $esc($url . '&aba=' . $chave) ?>"
                    class="<?= $aba === $chave ? 'ativo' : '' ?>">

                    <span>
                        <?= $esc($item['titulo']) ?>
                    </span>

                    <strong>
                        <?= count($item['lista']) ?>
                    </strong>

                </a>

            <?php endforeach; ?>

        </nav>

        <!-- MENSAGEM DO DOWNLOAD -->

        <div
            id="reciboMensagem"
            class="recibo-mensagem"
            role="status"
            aria-live="polite"
            hidden>
        </div>

        <!-- ESTADO VAZIO -->

        <?php if (empty($lista)): ?>

            <div class="faturas-vazio">

                <div class="vazio-icone">

                    <i data-lucide="<?= $esc($abas[$aba]['icone']) ?>"></i>

                </div>

                <h2>Nenhuma fatura encontrada</h2>

                <p>
                    <?= $esc($mensagens[$aba]) ?>
                </p>

            </div>

        <?php else: ?>

            <div class="faturas-grid">

                <?php foreach ($lista as $conta): ?>

                    <?php

                    $paga = ($conta['status'] ?? '') === 'Pago';

                    $atrasada =
                        !$paga &&
                        ($conta['vencimento'] ?? '') < $hoje;

                    $classe = 'aberta';

                    $status = 'Aberto';

                    $classeStatus = 'aberto';

                    if ($paga) {

                        $classe = 'paga';

                        $status = 'Pago';

                        $classeStatus = 'pago';
                    } elseif ($atrasada) {

                        $classe = 'atrasada';

                        $status = 'Em atraso';

                        $classeStatus = 'atrasado';
                    }

                    $id = (int) ($conta['id'] ?? 0);

                    $urlRecibo = BASE_URL .
                        '/app/controllers/ReciboAlunoController.php?id=' .
                        $id;

                    ?>

                    <article class="fatura-card fatura-<?= $classe ?>">

                        <!-- CABEÇALHO -->

                        <div class="fatura-card-header">

                            <div>

                                <span class="fatura-label">
                                    MENSALIDADE
                                </span>

                                <h3 class="fatura-plano">

                                    <?= $esc(
                                        $conta['nome_plano']
                                            ?? 'Plano contratado'
                                    ) ?>

                                </h3>

                                <span class="fatura-data">

                                    Fatura nº <?= $id ?>

                                </span>

                            </div>

                            <span class="status status-<?= $classeStatus ?>">

                                <?= $status ?>

                            </span>

                        </div>

                        <!-- VALOR -->

                        <div class="fatura-valor">

                            <span>

                                <?= $paga
                                    ? 'Valor registrado como pago'
                                    : 'Valor da fatura' ?>

                            </span>

                            <strong>

                                R$ <?= number_format(
                                        (float) ($conta['valor'] ?? 0),
                                        2,
                                        ',',
                                        '.'
                                    ) ?>

                            </strong>

                        </div>

                        <!-- INFORMAÇÕES -->

                        <div class="fatura-info">

                            <div class="info-item">

                                <span>Vencimento</span>

                                <strong>

                                    <?= $data(
                                        $conta['vencimento'] ?? null
                                    ) ?>

                                </strong>

                            </div>

                            <div class="info-item">

                                <span>Unidade</span>

                                <strong>

                                    <?= $esc(
                                        $conta['nome_filial']
                                            ?? 'Não informado'
                                    ) ?>

                                </strong>

                            </div>

                            <?php if ($paga): ?>

                                <div class="info-item">

                                    <span>Pago em</span>

                                    <strong>

                                        <?= $data(
                                            $conta['pago_em'] ?? null
                                        ) ?>

                                    </strong>

                                </div>

                                <div class="info-item">

                                    <span>Forma de pagamento</span>

                                    <strong>

                                        <?= $esc(
                                            $conta['forma_pagamento']
                                                ?? 'Não informado'
                                        ) ?>

                                    </strong>

                                </div>

                            <?php else: ?>

                                <div class="info-item">

                                    <span>Situação</span>

                                    <strong class="<?= $atrasada
                                                        ? 'texto-atrasado'
                                                        : '' ?>">

                                        Pagamento pendente

                                    </strong>

                                </div>

                            <?php endif; ?>

                        </div>

                        <!-- AÇÕES -->

                        <div class="fatura-acoes">

                            <?php if ($paga && $id > 0): ?>

                                <button
                                    type="button"
                                    class="btn-recibo"
                                    data-recibo-url="<?= $esc($urlRecibo) ?>">

                                    <i data-lucide="download"></i>

                                    <span data-recibo-label>
                                        Baixar recibo em PDF
                                    </span>

                                </button>

                            <?php elseif (!$paga): ?>

                                <span class="aviso-pagamento">

                                    <i data-lucide="info"></i>

                                    <?= $atrasada
                                        ? 'Entre em contato com a recepção.'
                                        : 'Pagamento disponível na recepção.' ?>

                                </span>

                            <?php endif; ?>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</main>

<!-- BIBLIOTECAS PDF -->

<script
    src="<?= BASE_URL ?>/vendor/jspdf/jspdf.umd.min.js"
    defer>
</script>

<script
    src="<?= BASE_URL ?>/vendor/jspdf/jspdf.plugin.autotable.min.js"
    defer>
</script>

<!-- GERADOR DE RECIBO -->

<script
    src="<?= BASE_URL ?>/assets/js/recibo-pdf.js"
    defer>
</script>

<?php require __DIR__ . '/../shared/footer.php'; ?>