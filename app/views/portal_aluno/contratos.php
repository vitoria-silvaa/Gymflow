<?php

// ============================================
// GYMFLOW - MEUS CONTRATOS
// app/views/portal_aluno/contratos.php
// ============================================

if (!isset($contratos)) {

    http_response_code(403);

    exit('Acesso negado.');
}

$tituloPagina = 'Meus Contratos';

$cssEspecifico = BASE_URL .
    '/assets/css/contratos.css';

$esc = static fn($valor) => htmlspecialchars(
    (string) ($valor ?? ''),
    ENT_QUOTES,
    'UTF-8'
);

$formatarData = static function ($valor): string {

    if (empty($valor)) {
        return 'Não informado';
    }

    $timestamp = strtotime($valor);

    return $timestamp !== false
        ? date('d/m/Y', $timestamp)
        : 'Não informado';
};

$totalContratos = count($contratos);

$totalAtivos = count(
    array_filter(
        $contratos,
        static fn($c) => (int) $c['ativa'] === 1
    )
);

$totalInativos = $totalContratos - $totalAtivos;

require __DIR__ . '/../shared/header.php';

require __DIR__ . '/../shared/sidebar.php';

?>

<main class="contratos-page">

    <!-- CABEÇALHO -->

    <header class="contratos-header">


        <h1>Meus Contratos</h1>

        <p>
            Consulte seus planos contratados e
            baixe seus documentos.
        </p>

    </header>

    <!-- RESUMO -->

    <section class="contratos-resumo">

        <article class="contratos-resumo-card">

            <div class="contratos-resumo-icone">
                <i data-lucide="files"></i>
            </div>

            <div>
                <span>Total de contratos</span>

                <strong>
                    <?= $totalContratos ?>
                </strong>
            </div>

        </article>

        <article class="contratos-resumo-card">

            <div class="contratos-resumo-icone icone-ativo">
                <i data-lucide="circle-check"></i>
            </div>

            <div>
                <span>Ativos</span>

                <strong>
                    <?= $totalAtivos ?>
                </strong>
            </div>

        </article>

        <article class="contratos-resumo-card">

            <div class="contratos-resumo-icone">
                <i data-lucide="archive"></i>
            </div>

            <div>
                <span>Inativos</span>

                <strong>
                    <?= $totalInativos ?>
                </strong>
            </div>

        </article>

    </section>

    <!-- LISTAGEM -->

    <div class="contratos-lista-header">

        <h2>Seus documentos</h2>

        <span>
            <?= $totalContratos ?> contrato(s)
        </span>

    </div>

    <!-- MENSAGEM DE DOWNLOAD -->

    <div
        id="contratoPdfMensagem"
        class="contrato-pdf-mensagem"
        role="status"
        aria-live="polite"
        hidden>
    </div>

    <?php if (empty($contratos)): ?>

        <div class="contratos-empty">

            <i data-lucide="file-x"></i>

            <h2>Nenhum contrato encontrado</h2>

            <p>
                Você ainda não possui contratos
                registrados no sistema.
            </p>

        </div>

    <?php else: ?>

        <section class="contratos-grid">

            <?php foreach ($contratos as $contrato): ?>

                <?php

                $ativo = (int) $contrato['ativa'] === 1;

                $urlPdf = BASE_URL .
                    '/app/controllers/PortalAlunoController.php' .
                    '?acao=baixar_contrato&id=' .
                    (int) $contrato['id'];

                ?>

                <article class="contrato-card">

                    <div class="contrato-card-topo">

                        <div class="contrato-card-icone">

                            <i data-lucide="file-text"></i>

                        </div>

                        <span class="contrato-status <?= $ativo
                                                            ? 'status-ativo'
                                                            : 'status-inativo' ?>">

                            <i data-lucide="<?= $ativo
                                                ? 'check-circle'
                                                : 'archive' ?>"></i>

                            <?= $ativo ? 'Ativo' : 'Inativo' ?>

                        </span>

                    </div>

                    <h3 class="contrato-title">

                        <?= $esc($contrato['nome_plano']) ?>

                    </h3>

                    <div class="contrato-category">

                        <i data-lucide="dumbbell"></i>

                        <?= $esc($contrato['categoria']) ?>

                    </div>

                    <div class="contrato-price">

                        R$ <?= number_format(
                                (float) $contrato['valor'],
                                2,
                                ',',
                                '.'
                            ) ?>

                        <span>

                            / <?= $esc($contrato['duracao']) ?>

                        </span>

                    </div>

                    <div class="contrato-details">

                        <div class="contrato-detail-row">

                            <span>Data de início</span>

                            <strong>
                                <?= $formatarData(
                                    $contrato['inicio']
                                ) ?>
                            </strong>

                        </div>

                        <div class="contrato-detail-row">

                            <span>Data de término</span>

                            <strong>
                                <?= $formatarData(
                                    $contrato['fim']
                                ) ?>
                            </strong>

                        </div>

                        <div class="contrato-detail-row">

                            <span>Unidade</span>

                            <strong>
                                <?= $esc(
                                    $contrato['nome_filial']
                                ) ?>
                            </strong>

                        </div>

                        <?php if ($contrato['desconto'] > 0): ?>

                            <div class="contrato-detail-row">

                                <span>Desconto aplicado</span>

                                <strong class="contrato-desconto">

                                    - R$ <?= number_format(
                                                (float) $contrato['desconto'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>

                                </strong>

                            </div>

                        <?php endif; ?>

                    </div>

                    <button
                        type="button"
                        class="btn-contrato"
                        data-contrato-pdf="<?= $esc($urlPdf) ?>">

                        <i data-lucide="download"></i>

                        <span data-contrato-label>
                            Baixar contrato em PDF
                        </span>

                    </button>

                </article>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>

</main>

<!-- BIBLIOTECAS JÁ INSTALADAS -->

<script
    src="<?= BASE_URL ?>/vendor/jspdf/jspdf.umd.min.js"
    defer>
</script>

<script
    src="<?= BASE_URL ?>/vendor/jspdf/jspdf.plugin.autotable.min.js"
    defer>
</script>

<!-- JAVASCRIPT DOS CONTRATOS -->

<script
    src="<?= BASE_URL ?>/assets/js/contrato-pdf.js"
    defer>
</script>

<?php

require __DIR__ . '/../shared/footer.php';
