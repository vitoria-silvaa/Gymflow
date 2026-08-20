<?php

require_once __DIR__ . '/../../config/sessao.php';

verificarLogado();

$acao = $_GET['acao'] ?? 'index';

$user_id = $_SESSION['usuario_id'] ?? null;

if (!$user_id) {
    die('Usuário não identificado.');
}


// 1. ABRIR TELA
if ($acao === 'index') {

    $preferencias = null;

    $operacao = 'buscar';

    require __DIR__ . '/../models/MinhaMarca.php';

    if (!$preferencias) {
        $preferencias = [
            'nome_painel' => 'Gymflow',
            'tema' => 'dark',
            'cor_primaria' => '#ffb000',
            'cor_secundaria' => '#000000',
            'tema_predefinido' => 'padrao',
            'logo_url' => null
        ];
    }

    $tituloPagina = 'Minha Marca';

    require __DIR__ . '/../views/minha_marca/index.php';
}


// 2. SALVAR PREFERÊNCIAS
elseif ($acao === 'salvar') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: MinhaMarcaController.php');
        exit;
    }

    $dados = [
        'nome_painel' => $_POST['nome_painel'] ?? 'Gymflow',
        'tema' => $_POST['tema'] ?? 'dark',
        'cor_primaria' => $_POST['cor_primaria'] ?? '#ffb000',
        'cor_secundaria' => $_POST['cor_secundaria'] ?? '#000000',
        'tema_predefinido' => $_POST['tema_predefinido'] ?? 'padrao',
        'logo_url' => $_POST['logo_url'] ?? ''
    ];

    $operacao = 'salvar';

    require __DIR__ . '/../models/MinhaMarca.php';


    // ATUALIZA A SESSÃO DO USUÁRIO
    $_SESSION['nome_painel'] = $dados['nome_painel'];

    $_SESSION['tema'] = $dados['tema'];

    $_SESSION['cor_primaria'] = $dados['cor_primaria'];

    $_SESSION['cor_secundaria'] = $dados['cor_secundaria'];

    $_SESSION['tema_predefinido'] = $dados['tema_predefinido'];

    $_SESSION['logo_url'] = $dados['logo_url'];


    header('Location: MinhaMarcaController.php?msg=salvo');
    exit;
}


// 3. AÇÃO INVÁLIDA
else {

    header('Location: MinhaMarcaController.php');
    exit;
}
