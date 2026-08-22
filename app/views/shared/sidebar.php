<?php
$nomePainel = $_SESSION['nome_painel'] ?? 'Gymflow';
?>

<aside class="sidebar">

    <h1><?= htmlspecialchars($nomePainel) ?></h1>

    <nav>
        <ul>
            <li><a href="/Gymflow/app/controllers/DashboardController.php">Dashboard</a></li>
            <li><a href="/Gymflow/app/controllers/FilialController.php?acao=listar">Filiais</a></li>
            <li><a href="/Gymflow/app/controllers/FuncionarioController.php?acao=listar">Funcionários</a></li>
            <li><a href="/Gymflow/app/controllers/AlunoController.php?acao=listar">Alunos</a></li>
            <li><a href="/Gymflow/app/controllers/PlanoController.php?acao=listar">Planos</a></li>
            <li><a href="/Gymflow/app/controllers/FinanceiroController.php">Financeiro</a></li>
            <li><a href="/Gymflow/app/controllers/CrmController.php">CRM Leads</a></li>
            <li><a href="/Gymflow/app/controllers/FluxoCaixaController.php">Fluxo de caixa</a></li>
            <li><a href="/Gymflow/app/controllers/MinhaMarcaController.php">Minha marca</a></li>
            <li><a href="#">Biblioteca de exercícios</a></li>
            <li><a href="#">Ficha de treino</a></li>
            <li><a href="/Gymflow/app/controllers/PortfolioController.php">Site / Portfólio</a></li>
        </ul>
    </nav>

    <a class="sair" href="/Gymflow/app/controllers/LoginController.php?acao=logout">
        Sair
    </a>

</aside>