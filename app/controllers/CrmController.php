<?php
// app/controllers/CrmController.php

require_once __DIR__ . '/../../config/sessao.php';
verificarRole(['Admin', 'Recepcao']);

$tituloPagina = "CRM";
$baseUrl = '/Gymflow/app/controllers/CrmController.php';
$acao = $_GET['acao'] ?? 'listar';

if ($acao === 'listar') {
    $operacao = 'listar';
    require __DIR__ . '/../models/Crm.php';
    require __DIR__ . '/../views/crm/index.php';

} elseif ($acao === 'cadastrar' || $acao === 'editar') {
    $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
    $erro = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dados = $_POST;
        if (empty($dados['nome']) || empty($dados['telefone']) || empty($dados['filial_id'])) {
            $erro = 'Preencha todos os campos obrigatórios (*).';
        } else {
            $operacao = ($id > 0) ? 'atualizar' : 'cadastrar';
            require __DIR__ . '/../models/Crm.php';

            if (empty($erroModel)) {
                header("Location: $baseUrl");
                exit;
            }
            $erro = $erroModel;
        }
    } else {
        $dados = [];
        if ($id > 0) {
            $operacao = 'buscar';
            require __DIR__ . '/../models/Crm.php';
            $dados = $lead ?? [];
        }
    }

    $operacao = 'listar_filiais';
    require __DIR__ . '/../models/Crm.php';
    require __DIR__ . '/../views/crm/cadastrar.php';

} elseif ($acao === 'atualizar_status') {
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($id > 0 && !empty($status)) {
        $operacao = 'atualizar_status';
        require __DIR__ . '/../models/Crm.php';
    }
    header("Location: $baseUrl");
    exit;

} elseif ($acao === 'excluir') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $operacao = 'excluir';
        require __DIR__ . '/../models/Crm.php';
    }
    header("Location: $baseUrl");
    exit;

} else {
    header("Location: $baseUrl");
    exit;
}
