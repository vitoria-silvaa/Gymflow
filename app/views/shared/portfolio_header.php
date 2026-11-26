
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

        /* =====================================
           CABEÇALHO
        ===================================== */

        .portfolio-header {
            position: sticky;
            top: 0;
            z-index: 9999;
        }

        /* Logo e nome */
        .portfolio-header .portfolio-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            min-width: 0;
        }

        .portfolio-header .portfolio-header-logo {
            display: block;
            width: 65px;
            height: 65px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .portfolio-header .portfolio-logo h1 {
            margin: 0;
            line-height: 1.2;
        }

        /* =====================================
           BOTÃO HAMBÚRGUER
        ===================================== */

        .portfolio-menu-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;

            width: 44px;
            height: 44px;
            padding: 9px;

            background: transparent;
            border: 1px solid var(--portfolio-primary);
            border-radius: 8px;

            cursor: pointer;
            flex-shrink: 0;
        }

        .portfolio-menu-toggle span {
            display: block;
            width: 100%;
            height: 2px;
            margin: 3px 0;

            background: var(--portfolio-primary);
            border-radius: 3px;

            transition: transform 0.25s ease,
                        opacity 0.25s ease;
        }

        /* =====================================
           MENU MOBILE
        ===================================== */

        @media (max-width: 600px) {

            /* Cabeçalho */
            .portfolio-header {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 12px;

                padding: 12px 5%;

                position: sticky;
                top: 0;
                z-index: 9999;
            }

            /* Logo na esquerda */
            .portfolio-header .portfolio-logo {
                width: auto;
                flex: 1;
                flex-basis: auto;
                min-width: 0;
                gap: 8px;
            }

            .portfolio-header .portfolio-header-logo {
                width: 38px;
                height: 38px;
            }

            .portfolio-header .portfolio-logo h1 {
                font-size: 17px;
                white-space: normal;
            }

            /* Botão na direita */
            .portfolio-menu-toggle {
                display: flex;
                order: 2;
            }

            /* Ações de login e matrícula */
            .portfolio-header .portfolio-acoes {
                order: 3;

                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;

                width: 100%;
                margin: 0;
            }

            .portfolio-header .portfolio-acoes a {
                box-sizing: border-box;
                width: 100%;
                margin-left: 0 !important;
                text-align: center;
            }

            /* Menu fechado */
            .portfolio-header .portfolio-nav {
                display: none;

                position: absolute;
                top: 100%;
                left: 0;

                width: 100%;
                box-sizing: border-box;

                padding: 10px 5% 18px;

                background: var(--portfolio-secondary);

                border-top: 1px solid
                    color-mix(
                        in srgb,
                        var(--portfolio-primary) 30%,
                        transparent
                    );

                box-shadow: 0 8px 15px rgba(0, 0, 0, 0.4);

                z-index: 9998;
            }

            /* Menu aberto */
            .portfolio-header.menu-aberto .portfolio-nav {
                display: block;
            }

            /* Lista vertical */
            .portfolio-header .portfolio-nav ul {
                display: flex;
                flex-direction: column;
                align-items: stretch;

                width: 100%;
                gap: 0;

                margin: 0;
                padding: 0;

                list-style: none;
                overflow: visible;
            }

            .portfolio-header .portfolio-nav li {
                width: 100%;
                margin: 0;

                border-bottom: 1px solid
                    rgba(255, 255, 255, 0.08);
            }

            .portfolio-header .portfolio-nav a {
                display: block;
                padding: 14px 5px;

                font-size: 15px;
                color: #ffffff;

                white-space: normal;
            }

            .portfolio-header .portfolio-nav a:hover {
                color: var(--portfolio-primary);
            }

            /* Animação das três linhas */
            .portfolio-header.menu-aberto
            .portfolio-menu-toggle span:nth-child(1) {
                transform: translateY(8px) rotate(45deg);
            }

            .portfolio-header.menu-aberto
            .portfolio-menu-toggle span:nth-child(2) {
                opacity: 0;
            }

            .portfolio-header.menu-aberto
            .portfolio-menu-toggle span:nth-child(3) {
                transform: translateY(-8px) rotate(-45deg);
            }
        }

        /* Celulares pequenos */
        @media (max-width: 380px) {

            .portfolio-header .portfolio-logo h1 {
                font-size: 15px;
            }

            .portfolio-menu-toggle {
                width: 40px;
                height: 40px;
            }

            .portfolio-header .portfolio-acoes {
                grid-template-columns: 1fr;
            }
        }
    </style>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/portfolio-mobile.css?v=1">
<script src="<?= BASE_URL ?>/assets/js/portfolio-mobile.js?v=1" defer></script>
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
                    style="margin-left: 10px;">
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

    <!-- SCRIPT DO MENU MOBILE -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const header = document.querySelector('.portfolio-header');
            const botaoMenu = document.querySelector('.portfolio-menu-toggle');
            const linksMenu = document.querySelectorAll('.portfolio-nav a');

            if (!header || !botaoMenu) {
                return;
            }

            function fecharMenu() {
                header.classList.remove('menu-aberto');

                botaoMenu.setAttribute(
                    'aria-expanded',
                    'false'
                );

                botaoMenu.setAttribute(
                    'aria-label',
                    'Abrir menu'
                );
            }

            // Abrir e fechar ao clicar no botão.
            botaoMenu.addEventListener('click', function () {

                const menuAberto =
                    header.classList.toggle('menu-aberto');

                botaoMenu.setAttribute(
                    'aria-expanded',
                    menuAberto ? 'true' : 'false'
                );

                botaoMenu.setAttribute(
                    'aria-label',
                    menuAberto ? 'Fechar menu' : 'Abrir menu'
                );

            });

            // Fechar ao clicar em uma opção.
            linksMenu.forEach(function (link) {

                link.addEventListener('click', function () {
                    fecharMenu();
                });

            });

            // Fechar ao clicar fora do cabeçalho.
            document.addEventListener('click', function (evento) {

                if (!header.contains(evento.target)) {
                    fecharMenu();
                }

            });

            // Fechar ao voltar para o tamanho desktop.
            window.addEventListener('resize', function () {

                if (window.innerWidth > 600) {
                    fecharMenu();
                }

            });

        });
    </script>
</body>
</html>
