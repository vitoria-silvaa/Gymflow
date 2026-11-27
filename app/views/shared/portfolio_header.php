
<?php
// ============================================
// GYMFLOW - CABEÇALHO DO PORTFÓLIO PÚBLICO
// app/views/shared/portfolio_header.php
// ============================================

// Empresa do portfólio atualmente acessado.
$portfolioEmpresaId = (int) ($company_id ?? 0);

// Endereço do portfólio da academia.
$portfolioUrl = BASE_URL . '/index.php';

if ($portfolioEmpresaId > 0) {
    $portfolioUrl .= '?id=' . $portfolioEmpresaId;
}

// Endereço do login.
$loginUrl = BASE_URL .
    '/app/controllers/LoginController.php?acao=login';

if ($portfolioEmpresaId > 0) {
    $loginUrl .= '&empresa=' . $portfolioEmpresaId;
}

// Endereço da matrícula pública.
$matriculaUrl = BASE_URL .
    '/app/controllers/MatriculaController.php?acao=identificacao';

if ($portfolioEmpresaId > 0) {
    $matriculaUrl .= '&company_id=' . $portfolioEmpresaId;
}

// Escapar valores utilizados no HTML.
$portfolioEsc = static function ($valor): string {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

// Configurações visuais.
$portfolioPrimaria = $config['primary_color'] ?? '#C9A227';
$portfolioSecundaria = $config['secondary_color'] ?? '#000000';

// Logo e nome da academia.
$portfolioLogo = trim((string) ($config['logo_url'] ?? ''));

$portfolioNome = $config['app_name']
    ?? $tituloPagina
    ?? 'GymFlow';

// Validar cores.
if (
    !is_string($portfolioPrimaria) ||
    !preg_match('/^#[0-9a-fA-F]{6}$/', $portfolioPrimaria)
) {
    $portfolioPrimaria = '#C9A227';
}

if (
    !is_string($portfolioSecundaria) ||
    !preg_match('/^#[0-9a-fA-F]{6}$/', $portfolioSecundaria)
) {
    $portfolioSecundaria = '#000000';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?= $portfolioEsc(
            !empty($tituloPagina)
                ? $tituloPagina . ' — ' . ($config['app_name'] ?? 'GymFlow')
                : ($config['app_name'] ?? 'GymFlow')
        ) ?>
    </title>

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/global.css">

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/portifolio.css?v=<?= time() ?>">

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        defer></script>

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/portfolio-mapa.css">

    <?php if (!empty($cssEspecifico)): ?>
        <link
            rel="stylesheet"
            href="<?= $portfolioEsc($cssEspecifico) ?>">
    <?php endif; ?>

    <style>
        :root {
            --portfolio-primary: <?= $portfolioPrimaria ?>;
            --portfolio-secondary: <?= $portfolioSecundaria ?>;
        }
    </style>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/portfolio-mobile.css?v=1">
    <script src="<?= BASE_URL ?>/assets/js/portfolio-mobile.js?v=1" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/portfolio-header.js?v=1" defer></script>
    <!-- Tratamento de imagens -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/image-placeholder.css">

    <script
        src="<?= BASE_URL ?>/assets/js/image-placeholder.js"
        defer>
    </script>

</head>

<body>

    <header class="portfolio-header">

        <!-- IDENTIDADE -->
        <div class="portfolio-logo">

            <?php if ($portfolioLogo !== ''): ?>
                <img
                    src="<?= $portfolioEsc($portfolioLogo) ?>"
                    alt="Logo da academia"
                    class="portfolio-header-logo"
                    onerror="this.style.display='none';"
                >
            <?php endif; ?>

            <h1>
                <?= $portfolioEsc($portfolioNome) ?>
            </h1>

        </div>

        <!-- BOTÃO MENU MOBILE -->
        <button
            type="button"
            class="portfolio-menu-toggle"
            aria-label="Abrir menu"
            aria-expanded="false"
            aria-controls="portfolio-menu">

            <span></span>
            <span></span>
            <span></span>

        </button>

        <!-- NAVEGAÇÃO -->
        <nav
            class="portfolio-nav"
            id="portfolio-menu">

            <ul>
                <li>
                    <a href="<?= $portfolioEsc($portfolioUrl) ?>#inicio">
                        Início
                    </a>
                </li>

                <li>
                    <a href="<?= $portfolioEsc($portfolioUrl) ?>#planos">
                        Planos
                    </a>
                </li>

                <li>
                    <a href="<?= $portfolioEsc($portfolioUrl) ?>#unidades">
                        Unidades
                    </a>
                </li>

                <li>
                    <a href="<?= $portfolioEsc($portfolioUrl) ?>#modalidades">
                        Modalidades
                    </a>
                </li>

                <li>
                    <a href="<?= $portfolioEsc($portfolioUrl) ?>#sobre">
                        Sobre Nós
                    </a>
                </li>

                <li>
                    <a href="<?= $portfolioEsc($portfolioUrl) ?>#contato">
                        Contato
                    </a>
                </li>
            </ul>

        </nav>

        <!-- AÇÕES -->
        <div class="portfolio-acoes">

            <?php if ($usuarioLogado ?? false): ?>

                <?php if (($roleUsuario ?? '') === 'Aluno'): ?>

                    <a
                        href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=aluno">
                        Área do Aluno
                    </a>

                <?php else: ?>

                    <a
                        href="<?= BASE_URL ?>/app/controllers/DashboardController.php">
                        Painel GymFlow
                    </a>

                <?php endif; ?>

                <a
                    href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=logout"
                    class="portfolio-link-sair">
                    Sair
                </a>

            <?php else: ?>

                <a href="<?= $portfolioEsc($loginUrl) ?>">
                    Entrar
                </a>

                <a href="<?= $portfolioEsc($matriculaUrl) ?>">
                    Matricule-se
                </a>

            <?php endif; ?>

        </div>

    </header>
