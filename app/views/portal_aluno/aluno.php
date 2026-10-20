<?php
if (!isset($aluno)) {
    header(
        "Location: " . BASE_URL .
            "/app/controllers/PortalAlunoController.php?acao=aluno"
    );
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

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main class="portal-aluno">

    <!-- CABEÇALHO -->
    <header class="portal-header">
        <div>
            <span class="portal-eyebrow">
                PORTAL DO ALUNO
            </span>

            <h1>
                Olá, <?= htmlspecialchars(
                            $aluno['nome'] ?? 'Aluno',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>!
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
                    <span class="card-label">
                        MEU PLANO
                    </span>

                    <?php if (!empty($matricula)): ?>

                        <h2>
                            <?= htmlspecialchars(
                                $matricula['nome_plano'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
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
                            <?= !empty($matricula['fim'])
                                ? date(
                                    'd/m/Y',
                                    strtotime($matricula['fim'])
                                )
                                : 'Não informada' ?>
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
                    Procure a recepção para realizar
                    sua matrícula.
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
                    <?= (int) $frequencia ?>

                    <small>
                        dia<?= $frequencia != 1 ? 's' : '' ?>
                    </small>
                </strong>

                <p>
                    Treinados em <?= date('F') ?>
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
                        <?= (int) $faturas_abertas ?>

                        <small>em aberto</small>
                    </strong>

                    <p class="texto-alerta">
                        Existem pagamentos pendentes.
                    </p>

                <?php else: ?>

                    <strong class="metric">
                        0
                        <small>pendentes</small>
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
                <span class="portal-eyebrow">
                    ACESSO RÁPIDO
                </span>

                <h2>O que você deseja fazer?</h2>
            </div>
        </div>

        <div class="quick-actions">

            <!-- TREINOS -->
            <a
                href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=treinos"
                class="quick-action">
                <strong>Meus treinos</strong>
                <span>Visualizar seus treinos →</span>
            </a>

            <!-- FINANCEIRO -->
            <a
                href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=faturas"
                class="quick-action">
                <strong>Financeiro</strong>
                <span>Consultar pagamentos →</span>
            </a>

            <!-- MINHA AVALIAÇÃO -->
            <a
                href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=avaliacao"
                class="quick-action">
                <strong>Minha Avaliação</strong>
                <span>
                    Avaliar minha academia →
                </span>
            </a>

        </div>

    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>