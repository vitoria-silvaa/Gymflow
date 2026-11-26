<?php
// app/views/shared/sidebar.php

$nomePainel = $_SESSION['nome_painel'] ?? 'Gymflow';
$role = $_SESSION['usuario_role'] ?? '';
$logoPainel = $_SESSION['logo_url'] ?? '';

$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
$companyId = (int) ($_SESSION['company_id'] ?? 0);

if ($role === 'Aluno') {

    require_once __DIR__ .
        '/../../models/IdentidadePortalAluno.php';

    $logoPainel = validarLogoPortalAluno($logoPainel);

} else {

    $prefixoLogo = BASE_URL .
        '/assets/uploads/minha-marca/' .
        $usuarioId . '/';

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

$escSidebar = static fn($v) =>
    htmlspecialchars(
        (string) $v,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );

$controllerUrl = BASE_URL . '/app/controllers/';

$menu = [];

$adicionarMenu = static function (
    string $titulo,
    string $icone,
    string $rota
) use (&$menu): void {

    $menu[] = [
        'titulo' => $titulo,
        'icone' => $icone,
        'rota' => $rota
    ];
};

if ($role === 'Aluno') {

    $portal = 'PortalAlunoController.php';

    $adicionarMenu('Início', 'house', $portal . '?acao=aluno');
    $adicionarMenu('Treinos', 'dumbbell', $portal . '?acao=treinos');
    $adicionarMenu('Minhas Medidas', 'ruler', $portal . '?acao=minhas_medidas');
    $adicionarMenu('Faturas', 'credit-card', $portal . '?acao=faturas');
    $adicionarMenu('Contratos', 'file-text', $portal . '?acao=contratos');
    $adicionarMenu('Meus Dados', 'user-round', $portal . '?acao=meus_dados');
    $adicionarMenu('Avaliar Academia', 'star', $portal . '?acao=avaliacao');

} elseif (in_array(
    $role,
    ['Admin', 'Recepcao', 'Professor'],
    true
)) {

    $adicionarMenu(
        'Dashboard',
        'layout-dashboard',
        'DashboardController.php'
    );

    if ($role === 'Admin') {

        $adicionarMenu('Filiais', 'map-pin', 'FilialController.php?acao=listar');

        $adicionarMenu(
            'Funcionários',
            'users',
            'FuncionarioController.php?acao=listar'
        );
    }

    if (in_array($role, ['Admin', 'Recepcao'], true)) {

        $adicionarMenu('Alunos', 'graduation-cap', 'AlunoController.php?acao=listar');

        $adicionarMenu('Planos', 'layers', 'PlanoController.php?acao=listar');
    }

    if ($role === 'Recepcao') {

        $adicionarMenu(
            'Recebimentos',
            'credit-card',
            'RecebimentoController.php'
        );

        $adicionarMenu(
            'Caixa físico',
            'banknote',
            'CaixaFisicoController.php'
        );

        $adicionarMenu('CRM Leads', 'target', 'CrmController.php');
    }

    if ($role === 'Admin') {

        $adicionarMenu('Financeiro', 'credit-card', 'FinanceiroController.php');

        $adicionarMenu('CRM Leads', 'target', 'CrmController.php');

        $adicionarMenu(
            'Fluxo de caixa',
            'chart-no-axes-column-increasing',
            'FluxoCaixaController.php'
        );

        $adicionarMenu('Minha marca', 'tag', 'MinhaMarcaController.php');
    }

    if (in_array($role, ['Admin', 'Professor'], true)) {

        $adicionarMenu(
            'Biblioteca de exercícios',
            'dumbbell',
            'ExercicioController.php'
        );

        $adicionarMenu(
            'Ficha de treino',
            'clipboard-list',
            'TreinoController.php'
        );
    }

    if ($role === 'Admin') {

        $adicionarMenu('Site / Portfólio', 'globe', 'PortfolioController.php');

        $adicionarMenu(
            'Configurações de e-mail',
            'mail',
            'ConfiguracaoEmailController.php'
        );
    }

    if ($role === 'Professor') {

        $adicionarMenu(
            'Avaliações físicas',
            'clipboard-check',
            'AvaliacaoFisicaController.php'
        );
    }
}

$siteUrl = BASE_URL . '/index.php';

if ($companyId > 0) {
    $siteUrl .= '?id=' . $companyId;
}

?>

<aside class="sidebar" id="appSidebar">

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

            <span><?= $escSidebar($nomePainel) ?></span>

        </h1>

        <button type="button"
                class="sidebar-toggle"
                id="sidebarToggle"
                aria-label="Recolher menu"
                title="Recolher menu">

            <i data-lucide="menu"></i>

        </button>

    </div>

    <nav class="sidebar-nav">

        <ul>

            <?php foreach ($menu as $item): ?>

                <li>

                    <a href="<?= $escSidebar(
                        $controllerUrl . $item['rota']
                    ) ?>"
                       title="<?= $escSidebar($item['titulo']) ?>">

                        <span class="menu-icon">

                            <i data-lucide="<?= $escSidebar(
                                $item['icone']
                            ) ?>"></i>

                        </span>

                        <span class="menu-text">
                            <?= $escSidebar($item['titulo']) ?>
                        </span>

                    </a>

                </li>

            <?php endforeach; ?>

        </ul>

    </nav>

    <div class="sidebar-footer">

        <a class="sidebar-action voltar-site"
           href="<?= $escSidebar($siteUrl) ?>"
           title="Voltar ao Site">

            <span class="menu-icon">
                <i data-lucide="arrow-left"></i>
            </span>

            <span class="menu-text">Voltar ao Site</span>

        </a>

        <a class="sidebar-action sair"
           href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=logout"
           title="Sair">

            <span class="menu-icon">
                <i data-lucide="log-out"></i>
            </span>

            <span class="menu-text">Sair</span>

        </a>

    </div>

</aside>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    if (window.lucide) {

        lucide.createIcons({
            attrs: {
                width: 20,
                height: 20,
                'stroke-width': 1.8
            }
        });
    }
});
</script>

<script src="<?= BASE_URL ?>/assets/js/sidebar.js"></script>