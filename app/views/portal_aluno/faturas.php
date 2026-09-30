<?php

if (!isset($todas_contas)) {

    header(
        "Location: /Gymflow/app/controllers/PortalAlunoController.php?acao=faturas"
    );

    exit;
}


/** @var array $todas_contas */
/** @var array $a_vencer */
/** @var array $em_atraso */
/** @var array $pagas */
/** @var string $aba */
/** @var string $hoje */

$tituloPagina = "Faturas";

?>

<?php include __DIR__ . '/../shared/navbar.php'; ?>


<!-- CSS EXCLUSIVO DA PÁGINA DE FATURAS -->

<link
    rel="stylesheet"
    href="/Gymflow/assets/css/css/faturas.css?v=<?= time() ?>">


<?php

$lista = match ($aba) {

    'atraso' => $em_atraso,

    'pagas' => $pagas,

    default => $a_vencer
};

?>


<main class="faturas-page">


    <!-- =========================================
         CABEÇALHO
         ========================================= -->

    <header class="faturas-header">

        <span class="portal-eyebrow">
            PORTAL DO ALUNO
        </span>

        <h1>
            Minhas faturas
        </h1>

        <p>
            Acompanhe suas mensalidades e o histórico de pagamentos.
        </p>

    </header>



    <!-- =========================================
         RESUMO FINANCEIRO
         ========================================= -->

    <section class="faturas-resumo">


        <div class="resumo-card">

            <div class="resumo-icone resumo-icone-aberto">
                $
            </div>

            <div>

                <span>
                    A vencer
                </span>

                <strong>
                    <?= count($a_vencer) ?>
                </strong>

            </div>

        </div>



        <div class="resumo-card">

            <div class="resumo-icone resumo-icone-atraso">
                !
            </div>

            <div>

                <span>
                    Em atraso
                </span>

                <strong>
                    <?= count($em_atraso) ?>
                </strong>

            </div>

        </div>



        <div class="resumo-card">

            <div class="resumo-icone resumo-icone-pago">
                ✓
            </div>

            <div>

                <span>
                    Pagas
                </span>

                <strong>
                    <?= count($pagas) ?>
                </strong>

            </div>

        </div>


    </section>



    <!-- =========================================
         ABAS
         ========================================= -->

    <section class="faturas-lista">


        <nav class="faturas-tabs">


            <a
                href="/Gymflow/app/controllers/PortalAlunoController.php?acao=faturas&aba=vencer"
                class="<?= $aba === 'vencer' ? 'ativo' : '' ?>">

                <span>
                    A vencer
                </span>

                <strong>
                    <?= count($a_vencer) ?>
                </strong>

            </a>



            <a
                href="/Gymflow/app/controllers/PortalAlunoController.php?acao=faturas&aba=atraso"
                class="<?= $aba === 'atraso' ? 'ativo' : '' ?>">

                <span>
                    Em atraso
                </span>

                <strong>
                    <?= count($em_atraso) ?>
                </strong>

            </a>



            <a
                href="/Gymflow/app/controllers/PortalAlunoController.php?acao=faturas&aba=pagas"
                class="<?= $aba === 'pagas' ? 'ativo' : '' ?>">

                <span>
                    Pagas
                </span>

                <strong>
                    <?= count($pagas) ?>
                </strong>

            </a>


        </nav>



        <!-- =========================================
             LISTAGEM
             ========================================= -->

        <?php if (empty($lista)): ?>


            <div class="faturas-vazio">

                <div class="vazio-icone">

                    <?php if ($aba === 'pagas'): ?>

                        ✓

                    <?php elseif ($aba === 'atraso'): ?>

                        !

                    <?php else: ?>

                        $

                    <?php endif; ?>

                </div>


                <h2>
                    Nenhuma fatura encontrada
                </h2>


                <p>

                    <?php if ($aba === 'atraso'): ?>

                        Você não possui faturas em atraso.

                    <?php elseif ($aba === 'pagas'): ?>

                        Você ainda não possui pagamentos registrados.

                    <?php else: ?>

                        Você não possui faturas próximas do vencimento.

                    <?php endif; ?>

                </p>

            </div>



        <?php else: ?>


            <div class="faturas-grid">


                <?php foreach ($lista as $conta): ?>


                    <?php

                    $statusPago = $conta['status'] === 'Pago';

                    $estaAtrasada =
                        !$statusPago &&
                        $conta['vencimento'] < $hoje;

                    ?>


                    <article
                        class="fatura-card
                        <?= $statusPago
                            ? 'fatura-paga'
                            : ($estaAtrasada
                                ? 'fatura-atrasada'
                                : 'fatura-aberta')
                        ?>">


                        <!-- CABEÇALHO -->

                        <div class="fatura-card-header">


                            <div>

                                <span class="fatura-label">
                                    MENSALIDADE
                                </span>

                                <span class="fatura-data">

                                    Vencimento
                                    <?= date(
                                        'd/m/Y',
                                        strtotime($conta['vencimento'])
                                    ); ?>

                                </span>

                            </div>



                            <?php if ($statusPago): ?>

                                <span class="status status-pago">
                                    Pago
                                </span>


                            <?php elseif ($estaAtrasada): ?>

                                <span class="status status-atrasado">
                                    Em atraso
                                </span>


                            <?php else: ?>

                                <span class="status status-aberto">
                                    Aberto
                                </span>

                            <?php endif; ?>


                        </div>



                        <!-- VALOR -->

                        <div class="fatura-valor">

                            <span>
                                Valor
                            </span>

                            <strong>

                                R$

                                <?= number_format(
                                    $conta['valor'],
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            </strong>

                        </div>



                        <!-- INFORMAÇÕES -->

                        <div class="fatura-info">


                            <?php if ($statusPago): ?>


                                <div class="info-item">

                                    <span>
                                        Pago em
                                    </span>

                                    <strong>

                                        <?php

                                        if (!empty($conta['pago_em'])) {

                                            echo date(
                                                'd/m/Y',
                                                strtotime($conta['pago_em'])
                                            );
                                        } else {

                                            echo '—';
                                        }

                                        ?>

                                    </strong>

                                </div>



                                <div class="info-item">

                                    <span>
                                        Forma de pagamento
                                    </span>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $conta['forma_pagamento'] ?? '—'
                                        ); ?>

                                    </strong>

                                </div>


                            <?php elseif ($estaAtrasada): ?>


                                <div class="info-item">

                                    <span>
                                        Situação
                                    </span>

                                    <strong class="texto-atrasado">
                                        Pagamento pendente
                                    </strong>

                                </div>


                                <div class="info-item">

                                    <span>
                                        Vencimento
                                    </span>

                                    <strong>

                                        <?= date(
                                            'd/m/Y',
                                            strtotime($conta['vencimento'])
                                        ); ?>

                                    </strong>

                                </div>


                            <?php else: ?>


                                <div class="info-item">

                                    <span>
                                        Situação
                                    </span>

                                    <strong>
                                        Pagamento em aberto
                                    </strong>

                                </div>


                                <div class="info-item">

                                    <span>
                                        Vencimento
                                    </span>

                                    <strong>

                                        <?= date(
                                            'd/m/Y',
                                            strtotime($conta['vencimento'])
                                        ); ?>

                                    </strong>

                                </div>


                            <?php endif; ?>


                        </div>



                        <!-- AÇÕES -->

                        <div class="fatura-acoes">


                            <?php if ($statusPago): ?>


                                <button
                                    type="button"
                                    class="btn-recibo">

                                    Baixar recibo

                                </button>


                            <?php elseif ($estaAtrasada): ?>


                                <span class="aviso-pagamento">

                                    Entre em contato com a recepção.

                                </span>


                            <?php else: ?>


                                <span class="aviso-pagamento">

                                    Pagamento disponível na recepção.

                                </span>


                            <?php endif; ?>


                        </div>


                    </article>


                <?php endforeach; ?>


            </div>


        <?php endif; ?>


    </section>


</main>


<?php include __DIR__ . '/../shared/footer.php'; ?>