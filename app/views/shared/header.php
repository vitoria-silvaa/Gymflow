<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <?php

    $nomePainel = $_SESSION['nome_painel'] ?? 'GymCore';

    $tema = $_SESSION['tema'] ?? 'dark';

    $corPrimaria =
        $_SESSION['cor_primaria']
        ?? '#ffb000';

    $corSecundaria =
        $_SESSION['cor_secundaria']
        ?? '#000000';


    if ($tema === 'light') {

        $corFundo = '#ffffff';

        $corTexto = '#000000';
    } else {

        $corFundo = $corSecundaria;

        $corTexto = '#ffffff';
    }

    ?>


    <title>
        <?= htmlspecialchars($nomePainel); ?>
        |
        <?= htmlspecialchars($tituloPagina ?? 'Dashboard'); ?>
    </title>

    <link rel="stylesheet" href="/Gymflow/assets/css/css/global.css">

    <!-- CSS TEMPORÁRIO PARA TESTAR O MINHA MARCA -->

    <style>
        body {

            margin: 0;

            background-color:
                <?= htmlspecialchars($corFundo); ?>;

            color:
                <?= htmlspecialchars($corTexto); ?>;

            font-family: Arial, sans-serif;
        }


        header {

            background-color:
                <?= htmlspecialchars($corSecundaria); ?>;

            color:
                <?= htmlspecialchars($corTexto); ?>;

            padding: 20px;
        }


        header h1 {

            margin: 0;

            color:
                <?= htmlspecialchars($corPrimaria); ?>;
        }


        header h2 {

            color:
                <?= htmlspecialchars($corTexto); ?>;
        }


        header span {

            color:
                <?= htmlspecialchars($corTexto); ?>;
        }


        a {

            color:
                <?= htmlspecialchars($corPrimaria); ?>;
        }


        button,
        input[type="submit"] {

            background-color:
                <?= htmlspecialchars($corPrimaria); ?>;

            color:
                <?= $tema === 'dark'
                    ? '#000000'
                    : '#ffffff'; ?>;

            border: none;

            padding: 8px 15px;
        }


        input,
        select,
        textarea {

            background-color:
                <?= $tema === 'dark'
                    ? '#1f1f1f'
                    : '#ffffff'; ?>;

            color:
                <?= htmlspecialchars($corTexto); ?>;

            border: 1px solid <?= htmlspecialchars($corPrimaria); ?>;

            padding: 8px;
        }
    </style>

</head>


<body>


    <header>

        <h1>
            <?= htmlspecialchars($nomePainel); ?>
        </h1>


        <h2>
            <?= htmlspecialchars(
                $tituloPagina ?? 'Dashboard'
            ); ?>
        </h2>


        <?php if (
            ($_SESSION['usuario_role'] ?? '') === 'Aluno'
        ): ?>

            <a
                href="/Gymflow/app/controllers/PortalAlunoController.php?acao=aluno">
                Portal do Aluno
            </a>

        <?php endif; ?>


        <span>

            <?= htmlspecialchars(
                $_SESSION['usuario_nome']
                    ?? 'Usuário'
            ); ?>

            (

            <?= htmlspecialchars(
                $_SESSION['usuario_role']
                    ?? 'Admin'
            ); ?>

            )

        </span>

    </header>