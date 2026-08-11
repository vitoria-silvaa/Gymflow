<?php
// app/controllers/FilialController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

// Apenas funcionários autorizados podem gerenciar filiais
verificarRole(['Admin', 'Professor', 'Recepcao']);

$baseUrl = '/Gymflow/app/controllers/FilialController.php';
$acao = $_GET['acao'] ?? 'listar';

/* LISTAR FILIAIS */
if ($acao === 'listar') {
    $statusFiltro = $_GET['status'] ?? '';
    
    $operacao = 'listar';
    require __DIR__ . '/../models/Filial.php';

    /** @var array $filiais */
    require __DIR__ . '/../views/filiais/index.php';
}

/* CADASTRAR FILIAL */
elseif ($acao === 'cadastrar') {
    $erro = '';
    $dados = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dados['nome'] = trim($_POST['nome'] ?? '');
        $dados['cnpj'] = trim($_POST['cnpj'] ?? '');
        $dados['telefone'] = trim($_POST['telefone'] ?? '');
        $dados['responsavel'] = trim($_POST['responsavel'] ?? '');
        $dados['company_id'] = 1; // Empresa padrão no MVP
        $dados['ativo'] = 1;

        if ($dados['nome'] === '' || $dados['cnpj'] === '' || $dados['responsavel'] === '') {
            $erro = "Por favor, preencha todos os campos obrigatórios (*).";
        } else {
            // Verificar duplicidade de CNPJ
            $cnpj = $dados['cnpj'];
            $companyId = $dados['company_id'];
            $ignorarId = 0;
            $operacao = 'verificar_cnpj';
            require __DIR__ . '/../models/Filial.php';

            /** @var bool $cnpjExiste */
            if ($cnpjExiste) {
                $erro = "Este CNPJ já está cadastrado para esta rede.";
            } else {
                $operacao = 'cadastrar';
                require __DIR__ . '/../models/Filial.php';

                if (empty($erroModel)) {
                    header("Location: $baseUrl?acao=listar");
                    exit;
                }
                $erro = $erroModel;
            }
        }
    }

    require __DIR__ . '/../views/filiais/cadastrar.php';
}

/* EDITAR FILIAL */
elseif ($acao === 'editar') {
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

    if ($id <= 0) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $operacao = 'buscar';
    require __DIR__ . '/../models/Filial.php';

    /** @var array|false $filial */
    if (!$filial) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $erro = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dados = [];
        $dados['nome'] = trim($_POST['nome'] ?? '');
        $dados['cnpj'] = trim($_POST['cnpj'] ?? '');
        $dados['telefone'] = trim($_POST['telefone'] ?? '');
        $dados['responsavel'] = trim($_POST['responsavel'] ?? '');

        if ($dados['nome'] === '' || $dados['cnpj'] === '' || $dados['responsavel'] === '') {
            $erro = "Por favor, preencha todos os campos obrigatórios (*).";
        } else {
            // Verificar duplicidade de CNPJ ignorando a própria filial
            $cnpj = $dados['cnpj'];
            $companyId = $filial['company_id'];
            $ignorarId = $id;
            $operacao = 'verificar_cnpj';
            require __DIR__ . '/../models/Filial.php';

            if ($cnpjExiste) {
                $erro = "Este CNPJ já pertence a outra filial.";
            } else {
                $operacao = 'atualizar';
                require __DIR__ . '/../models/Filial.php';

                if (empty($erroModel)) {
                    header("Location: $baseUrl?acao=listar");
                    exit;
                }
                $erro = $erroModel;
            }
        }
        
        // Recarrega dados atualizados para exibir no form em caso de erro ou persistência
        $operacao = 'buscar';
        require __DIR__ . '/../models/Filial.php';
    }

    require __DIR__ . '/../views/filiais/editar.php';
}

else {
    header("Location: $baseUrl?acao=listar");
    exit;
}
