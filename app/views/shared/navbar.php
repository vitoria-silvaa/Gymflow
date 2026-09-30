<?php

$nomePainel = $_SESSION['nome_painel'] ?? 'Gymflow';
$titulo = $tituloPagina ?? 'Portal do Aluno';
$usuario = $_SESSION['usuario_nome'] ?? 'Aluno';

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($nomePainel) ?> |
        <?= htmlspecialchars($titulo) ?>
    </title>


    <!-- CSS GERAL -->

    <link
        rel="stylesheet"
        href="/Gymflow/assets/css/css/global.css?v=2">


    <!-- CSS DO LAYOUT -->

    <link
        rel="stylesheet"
        href="/Gymflow/assets/css/css/layout.css?v=2">


    <!-- CSS DO PORTAL -->

    <link
        rel="stylesheet"
        href="/Gymflow/assets/css/css/portal.css?v=2">


    <!-- CSS DOS TREINOS -->

    <link
        rel="stylesheet"
        href="/Gymflow/assets/css/css/treinos.css?v=2">

</head>


<body>


    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h1>
            <?= htmlspecialchars($nomePainel) ?>
        </h1>


        <nav>

            <ul>

                <li>

                    <a href="/Gymflow/app/controllers/PortalAlunoController.php?acao=aluno">
                        Início
                    </a>

                </li>


                <li>

                    <a href="/Gymflow/app/controllers/PortalAlunoController.php?acao=treinos">
                        Treinos
                    </a>

                </li>


                <li>

                    <a href="/Gymflow/app/controllers/PortalAlunoController.php?acao=faturas">
                        Faturas
                    </a>

                </li>


                <li>

                    <a href="#">
                        Contratos
                    </a>

                </li>

            </ul>

        </nav>


        <a
            class="sair"
            href="/Gymflow/app/controllers/LoginController.php?acao=logout">

            Sair

        </a>

    </aside>


    <!-- HEADER -->

    <header class="header">

        <div class="header-esquerda">

            <h1>
                <?= htmlspecialchars($titulo) ?>
            </h1>

        </div>


        <div class="header-direita">

            <span>

                <?= htmlspecialchars($usuario) ?>

                (Aluno)

            </span>

        </div>

    </header>