<?php
// app/controllers/PortalAlunoController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

// Garante que apenas alunos autenticados acessem o portal
verificarRole(['Aluno']);

$baseUrl = '/Gymflow/app/controllers/PortalAlunoController.php';
$acao = $_GET['acao'] ?? 'aluno';

$aluno_id = $_SESSION['aluno_id'];

if (empty($aluno_id)) {
    $_SESSION = [];
    session_destroy();
    header("Location: /Gymflow/app/controllers/LoginController.php?acao=login");
    exit;
}

/* HOME DO PORTAL DO ALUNO */
if ($acao === 'aluno') {
    $alunoId = $aluno_id;
    $operacao = 'dados_portal_aluno';
    require __DIR__ . '/../models/Aluno.php';

    /** @var array|false $aluno */
    /** @var array|false $matricula */
    /** @var int $frequencia */
    /** @var int $faturas_abertas */
    require __DIR__ . '/../views/portal_aluno/aluno.php';
}

/* LISTAR FATURAS DO ALUNO */
elseif ($acao === 'faturas') {
    $operacao = 'listar_faturas_aluno';
    require __DIR__ . '/../models/Financeiro.php';

    /** @var array $todas_contas */
    $hoje = date('Y-m-d');
    $a_vencer = [];
    $em_atraso = [];
    $pagas = [];

    foreach ($todas_contas as $conta) {
        if ($conta['status'] === 'Pago') {
            $pagas[] = $conta;
        } elseif ($conta['vencimento'] < $hoje) {
            $em_atraso[] = $conta;
        } else {
            $a_vencer[] = $conta;
        }
    }

    $aba = $_GET['aba'] ?? 'vencer';

    require __DIR__ . '/../views/portal_aluno/faturas.php';
}

/* LISTAR TREINOS DO ALUNO */
elseif ($acao === 'treinos') {
    $operacao = 'listar_fichas_aluno';
    require __DIR__ . '/../models/Treino.php';

    /** @var array $fichas */
    $ficha_id_selecionada = $_GET['ficha'] ?? ($fichas[0]['id'] ?? null);

    $itens = [];
    $ficha_atual = null;

    if ($ficha_id_selecionada) {
        foreach ($fichas as $f) {
            if ($f['id'] == $ficha_id_selecionada) {
                $ficha_atual = $f;
                break;
            }
        }

        $ficha_id = (int)$ficha_id_selecionada;
        $operacao = 'buscar_itens_ficha';
        require __DIR__ . '/../models/Treino.php';
        /** @var array $itens */
    }

    require __DIR__ . '/../views/portal_aluno/treinos.php';
}

else {
    header("Location: $baseUrl?acao=aluno");
    exit;
}
