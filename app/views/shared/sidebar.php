<?php
$nomePainel = $_SESSION['nome_painel'] ?? 'Gymflow';
$role = $_SESSION['usuario_role'] ?? 'Admin';
?>

<aside class="sidebar" id="appSidebar">

    <!-- TOPO DA SIDEBAR -->
    <div class="sidebar-header">

        <h1 class="sidebar-logo">
            <span><?= htmlspecialchars($nomePainel) ?></span>
        </h1>

        <button
            type="button"
            class="sidebar-toggle"
            id="sidebarToggle"
            aria-label="Recolher menu"
            title="Recolher menu">
            ☰
        </button>

    </div>


    <!-- MENU PRINCIPAL -->
    <nav class="sidebar-nav">
        <ul>

            <?php if ($role === 'Aluno'): ?>

                <li>
                    <a href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=aluno"
                        title="Início">
                        <span class="menu-icon">⌂</span>
                        <span class="menu-text">Início</span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=treinos"
                        title="Treinos">
                        <span class="menu-icon">♙</span>
                        <span class="menu-text">Treinos</span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=faturas"
                        title="Faturas">
                        <span class="menu-icon">$</span>
                        <span class="menu-text">Faturas</span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=contratos"
                        title="Contratos">
                        <span class="menu-icon">▤</span>
                        <span class="menu-text">Contratos</span>
                    </a>
                </li>


            <?php else: ?>

                <!-- DASHBOARD -->
                <li>
                    <a href="<?= BASE_URL ?>/app/controllers/DashboardController.php"
                        title="Dashboard">
                        <span class="menu-icon">⌂</span>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>


                <!-- ADMIN -->
                <?php if ($role === 'Admin'): ?>

                    <li>
                        <a href="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=listar"
                            title="Filiais">
                            <span class="menu-icon">▦</span>
                            <span class="menu-text">Filiais</span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= BASE_URL ?>/app/controllers/FuncionarioController.php?acao=listar"
                            title="Funcionários">
                            <span class="menu-icon">♙</span>
                            <span class="menu-text">Funcionários</span>
                        </a>
                    </li>

                <?php endif; ?>


                <!-- ADMIN + RECEPÇÃO -->
                <?php if (in_array($role, ['Admin', 'Recepcao'])): ?>

                    <li>
                        <a href="<?= BASE_URL ?>/app/controllers/AlunoController.php?acao=listar"
                            title="Alunos">
                            <span class="menu-icon">♙</span>
                            <span class="menu-text">Alunos</span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= BASE_URL ?>/app/controllers/PlanoController.php?acao=listar"
                            title="Planos">
                            <span class="menu-icon">▤</span>
                            <span class="menu-text">Planos</span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= BASE_URL ?>/app/controllers/FinanceiroController.php"
                            title="Financeiro">
                            <span class="menu-icon">$</span>
                            <span class="menu-text">Financeiro</span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= BASE_URL ?>/app/controllers/CrmController.php"
                            title="CRM Leads">
                            <span class="menu-icon">◎</span>
                            <span class="menu-text">CRM Leads</span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= BASE_URL ?>/app/controllers/FluxoCaixaController.php"
                            title="Fluxo de caixa">
                            <span class="menu-icon">▥</span>
                            <span class="menu-text">Fluxo de caixa</span>
                        </a>
                    </li>

                <?php endif; ?>


                <!-- MINHA MARCA -->
                <?php if ($role === 'Admin'): ?>

                    <li>
                        <a href="<?= BASE_URL ?>/app/controllers/MinhaMarcaController.php"
                            title="Minha marca">
                            <span class="menu-icon">◆</span>
                            <span class="menu-text">Minha marca</span>
                        </a>
                    </li>

                <?php endif; ?>


                <!-- PROFESSOR + ADMIN -->
                <?php if (in_array($role, ['Admin', 'Professor'])): ?>

                    <li>
                        <a href="<?= BASE_URL ?>/app/controllers/ExercicioController.php"
                            title="Biblioteca de exercícios">
                            <span class="menu-icon">▦</span>
                            <span class="menu-text">Biblioteca de exercícios</span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= BASE_URL ?>/app/controllers/TreinoController.php"
                            title="Ficha de treino">
                            <span class="menu-icon">♙</span>
                            <span class="menu-text">Ficha de treino</span>
                        </a>
                    </li>

                <?php endif; ?>


                <!-- PORTFÓLIO -->
                <?php if ($role === 'Admin'): ?>

                    <li>
                        <a href="<?= BASE_URL ?>/app/controllers/PortfolioController.php"
                            title="Site / Portfólio">
                            <span class="menu-icon">◈</span>
                            <span class="menu-text">Site / Portfólio</span>
                        </a>
                    </li>

                <?php endif; ?>

            <?php endif; ?>

        </ul>
    </nav>


    <!-- RODAPÉ DA SIDEBAR -->
    <div class="sidebar-footer">

        <a
            class="sidebar-action voltar-site"
            href="<?= BASE_URL ?>/"
            title="Voltar ao Site">
            <span class="menu-icon">←</span>
            <span class="menu-text">Voltar ao Site</span>
        </a>

        <a
            class="sidebar-action sair"
            href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=logout"
            title="Sair">
            <span class="menu-icon">↪</span>
            <span class="menu-text">Sair</span>
        </a>

    </div>

</aside>
<script src="<?= BASE_URL ?>/assets/js/sidebar.js"></script>