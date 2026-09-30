<?php
$nomePainel = $_SESSION['nome_painel'] ?? 'Gymflow';
$role = $_SESSION['usuario_role'] ?? 'Admin';
?>

<aside class="sidebar">

    <h1><?= htmlspecialchars($nomePainel) ?></h1>

    <nav>
        <ul>
            <?php if ($role === 'Aluno'): ?>
                <li><a href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=aluno">Início</a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=treinos">Treinos</a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=faturas">Faturas</a></li>
                <li><a href="#">Contratos</a></li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>/app/controllers/DashboardController.php">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=listar">Filiais</a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/FuncionarioController.php?acao=listar">Funcionários</a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/AlunoController.php?acao=listar">Alunos</a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/PlanoController.php?acao=listar">Planos</a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/FinanceiroController.php">Financeiro</a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/CrmController.php">CRM Leads</a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/FluxoCaixaController.php">Fluxo de caixa</a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/MinhaMarcaController.php">Minha marca</a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/ExercicioController.php">Biblioteca de exercícios</a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/TreinoController.php">Ficha de treino</a></li>
                <li><a href="<?= BASE_URL ?>/app/controllers/PortfolioController.php">Site / Portfólio</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <a class="sair" href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=logout">
        Sair
    </a>

</aside>