<?php
// =============================================
// GYMFLOW - PÁGINA DE FREQUÊNCIA DO ALUNO
// =============================================

if (
    !defined('BASE_URL') ||
    ($_SESSION['usuario_role'] ?? '') !== 'Aluno'
) {
    http_response_code(403);
    exit;
}

$tituloPagina = 'Minha Frequência';

$cssEspecifico = BASE_URL .
    '/assets/css/frequencia.css';

$urlVoltar = BASE_URL .
    '/app/controllers/PortalAlunoController.php?acao=treinos';

if (!empty($idFicha)) {

    $urlVoltar .= '&ficha=' . (int) $idFicha;
}

require __DIR__ . '/../shared/header.php';
require __DIR__ . '/../shared/sidebar.php';
?>

<main class="tg-page gf-page">

    <header class="gf-page-header">

        <a
            href="<?= htmlspecialchars(
                        $urlVoltar,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
            class="gf-back-link">

            <i data-lucide="arrow-left"></i>

            Voltar aos treinos

        </a>

        <h1>Minha frequência</h1>

        <p>
            Acompanhe sua constância e seu
            desempenho nos treinos.
        </p>

    </header>

    <?php require __DIR__ . '/frequencia.php'; ?>

</main>

<script
    src="<?= BASE_URL ?>/assets/js/frequencia.js"
    defer>
</script>

<?php
require __DIR__ . '/../shared/footer.php';
?>