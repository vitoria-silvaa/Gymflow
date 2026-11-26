<?php
// =============================================
// GYMFLOW - SIDEBAR GLOBAL
// app/views/shared/sidebar.php
// =============================================

$nomePainel =
    $_SESSION['nome_painel'] ?? 'Gymflow';

$role =
    $_SESSION['usuario_role'] ?? '';

$logoPainel =
    $_SESSION['logo_url'] ?? '';

$usuarioId =
    (int) ($_SESSION['usuario_id'] ?? 0);

$companyId =
    (int) ($_SESSION['company_id'] ?? 0);

// =============================================
// 1. VALIDAR LOGOTIPO
// =============================================

if ($role === 'Aluno') {

    // Logotipo institucional do Portfólio.

    require_once __DIR__ .
        '/../../models/IdentidadePortalAluno.php';

    $logoPainel =
        validarLogoPortalAluno($logoPainel);
} else {

    // Logotipo pessoal do Minha Marca.

    $prefixoLogo = BASE_URL .
        '/assets/uploads/minha-marca/' .
        $usuarioId .
        '/';

    if (
        !is_string($logoPainel) ||
        $usuarioId <= 0 ||
        !preg_match(
            '~^' .
                preg_quote($prefixoLogo, '~') .
                '[a-f0-9]{32}\.(png|jpg|webp)$~',
            $logoPainel
        )
    ) {
        $logoPainel = '';
    }
}

// =============================================
// 2. FUNÇÕES AUXILIARES
// =============================================

$escSidebar = static function ($valor) {

    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

$controllerUrl = BASE_URL .
    '/app/controllers/';

// =============================================
// 3. ITENS DO MENU
// =============================================

$menu = [];

// PORTAL DO ALUNO

if ($role === 'Aluno') {

    $portal = 'PortalAlunoController.php';

    $menu = [

        [
            'titulo' => 'Início',
            'icone' => 'house',
            'rota' => $portal . '?acao=aluno'
        ],

        [
            'titulo' => 'Treinos',
            'icone' => 'dumbbell',
            'rota' => $portal . '?acao=treinos'
        ],

        [
            'titulo' => 'Faturas',
            'icone' => 'credit-card',
            'rota' => $portal . '?acao=faturas'
        ],

        [
            'titulo' => 'Contratos',
            'icone' => 'file-text',
            'rota' => $portal . '?acao=contratos'
        ],

        [
            'titulo' => 'Meus Dados',
            'icone' => 'user-round',
            'rota' => $portal . '?acao=meus_dados'
        ],

        [
            'titulo' => 'Avaliar Academia',
            'icone' => 'star',
            'rota' => $portal . '?acao=avaliacao'
        ]
    ];
}

// PAINEL INTERNO

elseif (
    in_array(
        $role,
        ['Admin', 'Recepcao', 'Professor'],
        true
    )
) {

    $menu[] = [
        'titulo' => 'Dashboard',
        'icone' => 'layout-dashboard',
        'rota' => 'DashboardController.php'
    ];

    // ADMIN

    if ($role === 'Admin') {

        $menu[] = [
            'titulo' => 'Filiais',
            'icone' => 'map-pin',
            'rota' => 'FilialController.php?acao=listar'
        ];

        $menu[] = [
            'titulo' => 'Funcionários',
            'icone' => 'users',
            'rota' => 'FuncionarioController.php?acao=listar'
        ];
    }

    // ADMIN E RECEPÇÃO

    if (in_array(
        $role,
        ['Admin', 'Recepcao'],
        true
    )) {

        $menu[] = [
            'titulo' => 'Alunos',
            'icone' => 'graduation-cap',
            'rota' => 'AlunoController.php?acao=listar'
        ];

        $menu[] = [
            'titulo' => 'Planos',
            'icone' => 'layers',
            'rota' => 'PlanoController.php?acao=listar'
        ];
    }

    // FINANCEIRO E PERSONALIZAÇÃO

    if ($role === 'Admin') {

        $menu[] = [
            'titulo' => 'Financeiro',
            'icone' => 'credit-card',
            'rota' => 'FinanceiroController.php'
        ];

        $menu[] = [
            'titulo' => 'CRM Leads',
            'icone' => 'target',
            'rota' => 'CrmController.php'
        ];

        $menu[] = [
            'titulo' => 'Fluxo de caixa',
            'icone' => 'chart-no-axes-column-increasing',
            'rota' => 'FluxoCaixaController.php'
        ];

        $menu[] = [
            'titulo' => 'Minha marca',
            'icone' => 'tag',
            'rota' => 'MinhaMarcaController.php'
        ];
    }

    // ADMIN E PROFESSOR

    if (in_array(
        $role,
        ['Admin', 'Professor'],
        true
    )) {

        $menu[] = [
            'titulo' => 'Biblioteca de exercícios',
            'icone' => 'dumbbell',
            'rota' => 'ExercicioController.php'
        ];

        $menu[] = [
            'titulo' => 'Ficha de treino',
            'icone' => 'clipboard-list',
            'rota' => 'TreinoController.php'
        ];
    }

    // CONFIGURAÇÕES ADMINISTRATIVAS

    if ($role === 'Admin') {

        $menu[] = [
            'titulo' => 'Site / Portfólio',
            'icone' => 'globe',
            'rota' => 'PortfolioController.php'
        ];

        $menu[] = [
            'titulo' => 'Configurações de e-mail',
            'icone' => 'mail',
            'rota' => 'ConfiguracaoEmailController.php'
        ];
    }
}

// =============================================
// 4. ENDEREÇO DO SITE INSTITUCIONAL
// =============================================

$siteUrl = BASE_URL . '/index.php';

if ($companyId > 0) {

    $siteUrl .= '?id=' . $companyId;
}

?>

<aside class="sidebar" id="appSidebar">

    <!-- IDENTIDADE -->

    <div class="sidebar-header">

        <h1 class="sidebar-logo">

            <?php if ($logoPainel !== ''): ?>

                <img
                    src="<?= $escSidebar($logoPainel) ?>"
                    class="sidebar-logo-imagem"
                    alt=""
                    width="36"
                    height="36"
                    decoding="async">

            <?php endif; ?>

            <span>
                <?= $escSidebar($nomePainel) ?>
            </span>

        </h1>

        <button
            type="button"
            class="sidebar-toggle"
            id="sidebarToggle"
            aria-label="Recolher menu"
            title="Recolher menu">

            <i data-lucide="menu"></i>

        </button>

    </div>

    <!-- MENU PRINCIPAL -->

    <nav class="sidebar-nav">

        <ul>

            <?php foreach ($menu as $item): ?>

                <li>

                    <a
                        href="<?= $escSidebar(
                                    $controllerUrl .
                                        $item['rota']
                                ) ?>"
                        title="<?= $escSidebar(
                                    $item['titulo']
                                ) ?>">

                        <span class="menu-icon">

                            <i data-lucide="<?= $escSidebar(
                                                $item['icone']
                                            ) ?>"></i>

                        </span>

                        <span class="menu-text">

                            <?= $escSidebar(
                                $item['titulo']
                            ) ?>

                        </span>

                    </a>

                </li>

            <?php endforeach; ?>

        </ul>

    </nav>

    <!-- RODAPÉ -->

    <div class="sidebar-footer">

        <a
            class="sidebar-action voltar-site"
            href="<?= $escSidebar($siteUrl) ?>"
            title="Voltar ao Site">

            <span class="menu-icon">

                <i data-lucide="arrow-left"></i>

            </span>

            <span class="menu-text">
                Voltar ao Site
            </span>

        </a>

        <a
            class="sidebar-action sair"
            href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=logout"
            title="Sair">

            <span class="menu-icon">

                <i data-lucide="log-out"></i>

            </span>

            <span class="menu-text">
                Sair
            </span>

        </a>

    </div>

</aside>

<!-- LUCIDE ICONS -->

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function() {

            if (window.lucide) {

                lucide.createIcons({

                    attrs: {
                        width: 20,
                        height: 20,
                        'stroke-width': 1.8
                    }

                });
            }

        }
    );
</script>

<script src="<?= BASE_URL ?>/assets/js/sidebar.js"></script>