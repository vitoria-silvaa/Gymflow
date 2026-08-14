<?php
// app/controllers/CrmController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

$tituloPagina = "CRM";

// Apenas administradores e recepção podem gerenciar o CRM
verificarRole(['Admin', 'Recepcao']);

$baseUrl = '/Gymflow/app/controllers/CrmController.php';
$acao = $_GET['acao'] ?? 'listar';

/* LISTAR LEADS */
if ($acao === 'listar') {

    $operacao = 'listar';
    require __DIR__ . '/../models/Crm.php';

    /** @var array $leads */
    $sucesso = '';
    $erro = '';
    if (($_GET['msg'] ?? '') === 'cadastrado') {
        $sucesso = 'Lead cadastrado com sucesso!';
    } elseif (($_GET['msg'] ?? '') === 'status_atualizado') {
        $sucesso = 'Status do lead atualizado com sucesso!';
    } elseif (($_GET['msg'] ?? '') === 'erro_status') {
        $erro = 'Erro ao atualizar status do lead.';
    }

    require __DIR__ . '/../views/crm/index.php';
} elseif ($acao === 'cadastrar') {

    $erro = '';
    $dados = $_POST;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $dados['nome'] = trim($_POST['nome'] ?? '');
        $dados['telefone'] = trim($_POST['telefone'] ?? '');
        $dados['objetivo'] = trim($_POST['objetivo'] ?? '');
        $dados['campanha'] = trim($_POST['campanha'] ?? '');
        $dados['filial_id'] = (int) ($_POST['filial_id'] ?? 0);

        if (
            $dados['nome'] === ''
            || $dados['telefone'] === ''
            || $dados['filial_id'] <= 0
        ) {
            $erro = 'Preencha todos os campos obrigatórios (*).';
        } else {

            $operacao = 'cadastrar';
            require __DIR__ . '/../models/Crm.php';

            if ($erroModel === '') {
                header("Location: $baseUrl?acao=listar&msg=cadastrado");
                exit;
            }

            $erro = $erroModel;
        }
    }

    /* Buscar filiais para o select */
    $operacao = 'listar_filiais';
    require __DIR__ . '/../models/Crm.php';
    /** @var array $filiais */

    require __DIR__ . '/../views/crm/cadastrar.php';
} elseif ($acao === 'atualizar_status') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int) ($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $statusValidos = ['Novo', 'Contato Agendado', 'Experimental', 'Convertido', 'Perdido'];

        if ($id > 0 && in_array($status, $statusValidos, true)) {
            $operacao = 'atualizar_status';
            require __DIR__ . '/../models/Crm.php';

            if (empty($erroModel)) {
                header("Location: $baseUrl?acao=listar&msg=status_atualizado");
                exit;
            }
        }
    }

    header("Location: $baseUrl?acao=listar&msg=erro_status");
    exit;
} else {
    header("Location: $baseUrl?acao=listar");
    exit;
}
