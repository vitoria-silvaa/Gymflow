<?php
$nomePainel = $_SESSION['nome_painel'] ?? 'Gymflow';
$role = $_SESSION['usuario_role'] ?? 'Admin';
?>

<aside class="sidebar" id="appSidebar">

    <h1>
        <span><?= htmlspecialchars($nomePainel) ?></span>
    </h1>

    <nav>
        <ul>
            <?php if ($role === 'Aluno'): ?>
                <li><a href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=aluno"><span>Início</span></a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=treinos"><span>Treinos</span></a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=faturas"><span>Faturas</span></a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=contratos"><span>Contratos</span></a></li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>/app/controllers/DashboardController.php"><span>Dashboard</span></a></li>
                
                <?php if ($role === 'Admin'): ?>
                    <li><a href="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=listar"><span>Filiais</span></a></li>
                    <li><a href="<?= BASE_URL ?>/app/controllers/FuncionarioController.php?acao=listar"><span>Funcionários</span></a></li>
                <?php endif; ?>
                
                <?php if (in_array($role, ['Admin', 'Recepcao'])): ?>
                    <li><a href="<?= BASE_URL ?>/app/controllers/AlunoController.php?acao=listar"><span>Alunos</span></a></li>
                    <li><a href="<?= BASE_URL ?>/app/controllers/PlanoController.php?acao=listar"><span>Planos</span></a></li>
                    <li><a href="<?= BASE_URL ?>/app/controllers/FinanceiroController.php"><span>Financeiro</span></a></li>
                    <li><a href="<?= BASE_URL ?>/app/controllers/CrmController.php"><span>CRM Leads</span></a></li>
                    <li><a href="<?= BASE_URL ?>/app/controllers/FluxoCaixaController.php"><span>Fluxo de caixa</span></a></li>
                <?php endif; ?>

                <?php if ($role === 'Admin'): ?>
                    <li><a href="<?= BASE_URL ?>/app/controllers/MinhaMarcaController.php"><span>Minha marca</span></a></li>
                <?php endif; ?>

                <?php if (in_array($role, ['Admin', 'Professor'])): ?>
                    <li><a href="<?= BASE_URL ?>/app/controllers/ExercicioController.php"><span>Biblioteca de exercícios</span></a></li>
                    <li><a href="<?= BASE_URL ?>/app/controllers/TreinoController.php"><span>Ficha de treino</span></a></li>
                <?php endif; ?>

                <?php if ($role === 'Admin'): ?>
                    <li><a href="<?= BASE_URL ?>/app/controllers/PortfolioController.php"><span>Site / Portfólio</span></a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </nav>

    <a class="sair" href="<?= BASE_URL ?>/" style="margin-top: auto; border-top: 1px solid #e2e8f0; color: #374151;">
        <span>Voltar ao Site</span>
    </a>

    <a class="sair" href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=logout" style="margin-top: 0;">
        <span>Sair</span>
    </a>

</aside>