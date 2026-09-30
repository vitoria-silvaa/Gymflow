<?php
if (!isset($aluno)) {
    header("Location: " . BASE_URL . "/app/controllers/PortalAlunoController.php?acao=aluno");
    exit;
}

/** @var array $aluno */
/** @var array|false $matricula */
/** @var int $frequencia */
/** @var int $faturas_abertas */

$matricula = $matricula ?? false;
$frequencia = $frequencia ?? 0;
$faturas_abertas = $faturas_abertas ?? 0;

$tituloPagina = "Portal do Aluno";
?>

<?php 
include __DIR__ . '/../shared/header.php'; 
include __DIR__ . '/../shared/sidebar.php'; 
?>

<main class="portal-aluno">

    <!-- CABEÇALHO -->
    <header class="portal-header">
        <div>
            <span class="portal-eyebrow">PORTAL DO ALUNO</span>

            <h1>
                Olá, <?php echo htmlspecialchars($aluno['nome'] ?? 'Aluno'); ?>!
            </h1>

            <p>Pronto para o treino de hoje?</p>
        </div>
    </header>


    <!-- RESUMO -->
    <section class="portal-grid">

        <!-- PLANO -->
        <article class="portal-card plano-card">

            <div class="card-top">
                <div>
                    <span class="card-label">MEU PLANO</span>

                    <?php if (!empty($matricula)): ?>

                        <h2>
                            <?php echo htmlspecialchars($matricula['nome_plano']); ?>
                        </h2>

                        <span class="status status-ativo">
                            Plano ativo
                        </span>

                    <?php else: ?>

                        <h2>Nenhum plano</h2>

                        <span class="status status-pendente">
                            Sem matrícula ativa
                        </span>

                    <?php endif; ?>
                </div>
            </div>


            <?php if (!empty($matricula)): ?>

                <div class="plano-info">

                    <div>
                        <span>Validade</span>

                        <strong>
                            <?php echo date('d/m/Y', strtotime($matricula['fim'])); ?>
                        </strong>
                    </div>

                </div>

                <a
                    href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=treinos"
                    class="portal-btn">
                    Treinar agora
                    <span>→</span>
                </a>

            <?php else: ?>

                <p class="card-description">
                    Procure a recepção para realizar sua matrícula.
                </p>

            <?php endif; ?>

        </article>


        <!-- FREQUÊNCIA -->
        <article class="portal-card frequencia-card">

            <div class="card-icon">
                ✓
            </div>

            <div class="card-content">

                <span class="card-label">
                    FREQUÊNCIA DO MÊS
                </span>

                <strong class="metric">
                    <?php echo (int)$frequencia; ?>
                    <small>
                        dia<?php echo $frequencia != 1 ? 's' : ''; ?>
                    </small>
                </strong>

                <p>
                    Treinados em <?php echo date('F'); ?>
                </p>

            </div>

        </article>


        <!-- FATURAS -->
        <article class="portal-card fatura-card">

            <div class="card-icon">
                $
            </div>

            <div class="card-content">

                <span class="card-label">
                    MINHAS FATURAS
                </span>

                <?php if ($faturas_abertas > 0): ?>

                    <strong class="metric">
                        <?php echo (int)$faturas_abertas; ?>
                        <small>
                            em aberto
                        </small>
                    </strong>

                    <p class="texto-alerta">
                        Existem pagamentos pendentes.
                    </p>

                <?php else: ?>

                    <strong class="metric">
                        0
                        <small>
                            pendentes
                        </small>
                    </strong>

                    <p class="texto-sucesso">
                        Tudo em dia!
                    </p>

                <?php endif; ?>

            </div>

            <a
                href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=faturas"
                class="card-link">
                Ver faturas →
            </a>

        </article>

    </section>


    <!-- ÁREA INFERIOR -->
    <section class="portal-bottom">

        <div class="section-heading">
            <div>
                <span class="portal-eyebrow">ACESSO RÁPIDO</span>
                <h2>O que você deseja fazer?</h2>
            </div>
        </div>

        <div class="quick-actions">

            <a
                href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=treinos"
                class="quick-action">
                <strong>Meus treinos</strong>
                <span>Visualizar seus treinos →</span>
            </a>

            <a
                href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=faturas"
                class="quick-action">
                <strong>Financeiro</strong>
                <span>Consultar pagamentos →</span>
            </a>

        </div>

    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>