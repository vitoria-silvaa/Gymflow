<?php
// app/controllers/AlunoController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';
verificarRole(['Admin', 'Professor', 'Recepcao']);

$baseUrl = '/Gymflow/app/controllers/AlunoController.php';
$acao = $_GET['acao'] ?? 'listar';

/* 1. LISTAR ALUNOS */
if ($acao === 'listar') {
    $cpf = trim($_GET['cpf'] ?? '');
    $status = trim($_GET['status'] ?? '');

    $operacao = 'listar';
    require __DIR__ . '/../models/Aluno.php';

    require __DIR__ . '/../views/alunos/index.php';
}

/* 2. CADASTRAR ALUNO */
elseif ($acao === 'cadastrar') {
    $erro = '';
    $dados = $_POST;

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $dados['nome']        = trim($_POST['nome'] ?? '');
        $dados['cpf']         = trim($_POST['cpf'] ?? '');
        $dados['rg']          = trim($_POST['rg'] ?? '');
        $dados['sexo']        = trim($_POST['sexo'] ?? '');
        $dados['nascimento']  = trim($_POST['nascimento'] ?? '');
        $dados['email']       = trim($_POST['email'] ?? '');
        $dados['telefone']    = trim($_POST['telefone'] ?? '');
        $dados['endereco']    = trim($_POST['endereco'] ?? '');
        $dados['filial_id']   = (int) ($_POST['filial_id'] ?? 0);
        $dados['senha']       = $_POST['senha'] ?? '';

        if (
            $dados['nome'] === '' || $dados['cpf'] === '' || $dados['sexo'] === '' ||
            $dados['nascimento'] === '' || $dados['email'] === '' || $dados['telefone'] === '' ||
            $dados['filial_id'] <= 0 || $dados['senha'] === ''
        ) {
            $erro = 'Preencha todos os campos obrigatórios.';
        } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um e-mail válido.';
        } elseif (strlen($dados['senha']) < 6) {
            $erro = 'A senha deve ter pelo menos 6 caracteres.';
        } else {
            $operacao = 'cadastrar';
            require __DIR__ . '/../models/Aluno.php';

            if ($erroModel === '') {
                header("Location: $baseUrl?acao=visualizar&id=$novoAlunoId&msg=cadastrado");
                exit;
            }
            $erro = $erroModel;
        }
    }

    $operacao = 'listar_filiais';
    require __DIR__ . '/../models/Aluno.php';

    require __DIR__ . '/../views/alunos/cadastrar.php';
}

/* 3. EDITAR ALUNO */
elseif ($acao === 'editar') {
    $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

    $operacao = 'buscar';
    require __DIR__ . '/../models/Aluno.php';

    if (!$aluno) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $erro = '';
    $dados = $aluno;

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $dados['nome']        = trim($_POST['nome'] ?? '');
        $dados['cpf']         = trim($_POST['cpf'] ?? '');
        $dados['rg']          = trim($_POST['rg'] ?? '');
        $dados['sexo']        = trim($_POST['sexo'] ?? '');
        $dados['nascimento']  = trim($_POST['nascimento'] ?? '');
        $dados['email']       = trim($_POST['email'] ?? '');
        $dados['telefone']    = trim($_POST['telefone'] ?? '');
        $dados['endereco']    = trim($_POST['endereco'] ?? '');
        $dados['filial_id']   = (int) ($_POST['filial_id'] ?? 0);
        $dados['status']      = trim($_POST['status'] ?? '');
        $dados['senha']       = $_POST['senha'] ?? '';

        if (
            $dados['nome'] === '' || $dados['cpf'] === '' || $dados['sexo'] === '' ||
            $dados['nascimento'] === '' || $dados['email'] === '' || $dados['telefone'] === '' ||
            $dados['filial_id'] <= 0 || $dados['status'] === ''
        ) {
            $erro = 'Preencha todos os campos obrigatórios.';
        } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um e-mail válido.';
        } elseif ($dados['senha'] !== '' && strlen($dados['senha']) < 6) {
            $erro = 'A nova senha deve ter pelo menos 6 caracteres.';
        } else {
            $operacao = 'atualizar';
            require __DIR__ . '/../models/Aluno.php';

            if ($erroModel === '') {
                header("Location: $baseUrl?acao=visualizar&id=$id&msg=atualizado");
                exit;
            }
            $erro = $erroModel;
        }
    }

    $operacao = 'listar_filiais';
    require __DIR__ . '/../models/Aluno.php';

    require __DIR__ . '/../views/alunos/editar.php';
}

/* 4. VISUALIZAR ALUNO */
elseif ($acao === 'visualizar') {
    $id = (int) ($_GET['id'] ?? 0);

    $operacao = 'visualizar';
    require __DIR__ . '/../models/Aluno.php';

    if (!$aluno) {
        header("Location: $baseUrl?acao=listar");
        exit;
    }

    $erro = $_SESSION['erro_aluno'] ?? '';
    unset($_SESSION['erro_aluno']);

    $sucesso = match ($_GET['msg'] ?? '') {
        'cadastrado'  => 'Aluno cadastrado com sucesso!',
        'atualizado'  => 'Aluno atualizado com sucesso!',
        'matriculado' => 'Matrícula realizada com sucesso!',
        'pago'        => 'Pagamento registrado com sucesso!',
        default       => ''
    };

    require __DIR__ . '/../views/alunos/visualizar.php';
}

/* 5. MATRICULAR ALUNO */
elseif ($acao === 'matricular') {
    $alunoId    = (int) ($_POST['aluno_id'] ?? 0);
    $planoId    = (int) ($_POST['plano_id'] ?? 0);
    $dataInicio = $_POST['data_inicio'] ?? '';
    $desconto   = (float) str_replace(',', '.', $_POST['desconto'] ?? '0');

    if ($alunoId <= 0 || $planoId <= 0 || empty($dataInicio) || $desconto < 0) {
        $_SESSION['erro_aluno'] = 'Preencha corretamente os dados da matrícula.';
    } else {
        $operacao = 'matricular';
        require __DIR__ . '/../models/Aluno.php';

        if ($erroModel !== '') {
            $_SESSION['erro_aluno'] = $erroModel;
        } else {
            header("Location: $baseUrl?acao=visualizar&id=$alunoId&msg=matriculado");
            exit;
        }
    }

    header("Location: $baseUrl?acao=visualizar&id=$alunoId");
    exit;
}

/* 6. REGISTRAR PAGAMENTO */
elseif ($acao === 'pagar') {
    $alunoId = (int) ($_POST['aluno_id'] ?? 0);
    $contaId = (int) ($_POST['conta_id'] ?? 0);

    $operacao = 'pagar';
    require __DIR__ . '/../models/Aluno.php';

    if (!empty($pagamentoRealizado)) {
        header("Location: $baseUrl?acao=visualizar&id=$alunoId&msg=pago");
        exit;
    }

    $_SESSION['erro_aluno'] = 'A mensalidade não foi encontrada ou já está paga.';
    header("Location: $baseUrl?acao=visualizar&id=$alunoId");
    exit;
}

/* REDIRECIONAMENTO PADRÃO */
else {
    header("Location: $baseUrl?acao=listar");
    exit;
}
