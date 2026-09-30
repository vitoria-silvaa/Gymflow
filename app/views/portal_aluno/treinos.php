<?php

if (!isset($fichas)) {

    header(
        "Location: /Gymflow/app/controllers/PortalAlunoController.php?acao=treinos"
    );

    exit;
}

/** @var array $fichas */
/** @var array $itens */
/** @var array|null $ficha_atual */
/** @var mixed $ficha_id_selecionada */

$tituloPagina = "Treinos";

?>

<?php include __DIR__ . '/../shared/navbar.php'; ?>


<!-- CSS EXCLUSIVO DA PÁGINA DE TREINOS -->
<link
    rel="stylesheet"
    href="/Gymflow/assets/css/css/treinos.css?v=<?= time() ?>">


<main class="treinos-page">


    <!-- =========================================
         CABEÇALHO
         ========================================= -->

    <header class="treinos-header">

        <span class="portal-eyebrow">
            PORTAL DO ALUNO
        </span>

        <h1>
            Meus treinos
        </h1>

        <p>
            Escolha sua ficha e acompanhe os exercícios do seu treino.
        </p>

    </header>



    <?php if (empty($fichas)): ?>


        <!-- =========================================
             SEM FICHAS
             ========================================= -->

        <section class="treinos-empty">

            <div class="empty-icon">
                +
            </div>

            <h2>
                Nenhuma ficha de treino
            </h2>

            <p>
                Você ainda não possui nenhuma ficha de treino cadastrada.
            </p>

            <span>
                Solicite ao seu professor para criar uma ficha personalizada
                para você.
            </span>

        </section>



    <?php else: ?>


        <!-- =========================================
             SELEÇÃO DE FICHAS
             ========================================= -->

        <section class="fichas-section">

            <div class="section-title">

                <span class="section-label">
                    SUAS FICHAS
                </span>

                <h2>
                    Escolha seu treino
                </h2>

            </div>


            <nav class="fichas-nav">

                <?php foreach ($fichas as $ficha): ?>

                    <a
                        class="ficha-tab <?= (
                                                $ficha_id_selecionada == $ficha['id']
                                            ) ? 'ativo' : ''; ?>"
                        href="/Gymflow/app/controllers/PortalAlunoController.php?acao=treinos&ficha=<?= (int)$ficha['id']; ?>">

                        <span class="ficha-tab-titulo">

                            <?= htmlspecialchars(
                                $ficha['objetivo'] ?? 'Treino'
                            ); ?>

                        </span>

                        <span class="ficha-tab-versao">

                            v<?= htmlspecialchars(
                                    $ficha['versao'] ?? '1'
                                ); ?>

                        </span>

                    </a>

                <?php endforeach; ?>

            </nav>

        </section>



        <?php if ($ficha_atual): ?>


            <!-- =========================================
                 FICHA ATUAL
                 ========================================= -->

            <section class="ficha-container">


                <!-- CABEÇALHO DA FICHA -->

                <header class="ficha-header">

                    <div class="ficha-header-info">

                        <span class="section-label">
                            FICHA ATUAL
                        </span>

                        <h2>

                            <?= htmlspecialchars(
                                $ficha_atual['objetivo'] ?? 'Treino'
                            ); ?>

                        </h2>


                        <div class="ficha-meta">

                            <span>

                                Versão
                                <?= htmlspecialchars(
                                    $ficha_atual['versao'] ?? '1'
                                ); ?>

                            </span>


                            <span>

                                Prof.
                                <?= htmlspecialchars(
                                    $ficha_atual['nome_professor'] ?? 'Não informado'
                                ); ?>

                            </span>


                            <span>

                                Criada em
                                <?= !empty($ficha_atual['criada_em'])
                                    ? date(
                                        'd/m/Y',
                                        strtotime($ficha_atual['criada_em'])
                                    )
                                    : 'Não informado';
                                ?>

                            </span>

                        </div>

                    </div>

                </header>



                <!-- =========================================
                     EXERCÍCIOS
                     ========================================= -->

                <?php if (empty($itens)): ?>


                    <div class="exercicios-empty">

                        <h3>
                            Nenhum exercício cadastrado
                        </h3>

                        <p>
                            Esta ficha ainda não possui exercícios cadastrados.
                        </p>

                    </div>



                <?php else: ?>


                    <div class="exercicios-list">


                        <?php foreach ($itens as $index => $item): ?>


                            <article class="exercicio-card">


                                <!-- TOPO DO EXERCÍCIO -->

                                <div class="exercicio-top">


                                    <label class="exercicio-check">

                                        <input
                                            type="checkbox">

                                        <span class="checkmark"></span>

                                    </label>


                                    <div class="exercicio-numero">

                                        <?= str_pad(
                                            $index + 1,
                                            2,
                                            '0',
                                            STR_PAD_LEFT
                                        ); ?>

                                    </div>


                                    <div class="exercicio-info">

                                        <h3>

                                            <?= htmlspecialchars(
                                                $item['nome_exercicio'] ?? 'Exercício'
                                            ); ?>

                                        </h3>


                                        <span class="grupo-muscular">

                                            <?= htmlspecialchars(
                                                $item['grupo'] ?? 'Grupo não informado'
                                            ); ?>

                                        </span>

                                    </div>

                                </div>



                                <!-- DADOS DO EXERCÍCIO -->

                                <div class="exercicio-dados">


                                    <div class="dado">

                                        <span>
                                            Séries
                                        </span>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $item['series'] ?? '0'
                                            ); ?>

                                        </strong>

                                    </div>



                                    <div class="dado">

                                        <span>
                                            Repetições
                                        </span>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $item['repeticoes'] ?? '—'
                                            ); ?>

                                        </strong>

                                    </div>



                                    <?php if (!empty($item['intervalo'])): ?>

                                        <div class="dado">

                                            <span>
                                                Descanso
                                            </span>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $item['intervalo']
                                                ); ?>

                                            </strong>

                                        </div>

                                    <?php endif; ?>


                                </div>



                                <!-- =================================
                                     CARGA + PAUSA
                                     ================================= -->

                                <div class="exercicio-acoes">


                                    <div class="carga-container">

                                        <label>
                                            Carga
                                        </label>


                                        <input
                                            type="text"
                                            value="<?= htmlspecialchars(
                                                        $item['carga'] ?? ''
                                                    ); ?>"
                                            placeholder="Ex.: 20 kg">

                                    </div>



                                    <button
                                        type="button"
                                        class="btn-pausa">

                                        Iniciar pausa

                                    </button>


                                </div>


                            </article>


                        <?php endforeach; ?>


                    </div>


                <?php endif; ?>


            </section>


        <?php endif; ?>


    <?php endif; ?>


</main>


<?php include __DIR__ . '/../shared/footer.php'; ?>