<?php
// app/controllers/ExercicioController.php

require_once __DIR__ . '/../../config/sessao.php';
require_once __DIR__ . '/../models/Exercicio.php';

$tituloPagina = "Biblioteca de Exercícios";
$cssEspecifico = BASE_URL . '/assets/css/biblioteca.css?v=' . time();


// =====================================================
// FUNÇÃO AUXILIAR — PROCESSAR UPLOAD DE MÍDIA
// Valida extensão, MIME type real e tamanho do arquivo.
// Retorna o caminho público salvo ou lança Exception.
// =====================================================

function processarUploadMidia(array $arquivo, string $tipo_midia): string
{
    $extensoesPermitidas = [
        'imagem' => ['jpg', 'jpeg', 'png', 'webp'],
        'video'  => ['mp4', 'webm'],
    ];

    $mimesPermitidos = [
        'imagem' => ['image/jpeg', 'image/png', 'image/webp'],
        'video'  => ['video/mp4', 'video/webm'],
    ];

    if (!isset($extensoesPermitidas[$tipo_midia])) {
        throw new InvalidArgumentException('Tipo de mídia inválido.');
    }

    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    if (!in_array($extensao, $extensoesPermitidas[$tipo_midia], true)) {
        throw new InvalidArgumentException('Formato de arquivo não permitido.');
    }

    // Valida MIME type real (lê os bytes do arquivo, não confia no nome)
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeReal = $finfo->file($arquivo['tmp_name']);

    if (!in_array($mimeReal, $mimesPermitidos[$tipo_midia], true)) {
        throw new InvalidArgumentException('Tipo de arquivo real não corresponde ao formato esperado.');
    }

    // Limite de tamanho: 5 MB para imagem, 50 MB para vídeo
    $limiteBytes = $tipo_midia === 'video' ? 50 * 1024 * 1024 : 5 * 1024 * 1024;
    if ($arquivo['size'] > $limiteBytes) {
        $limite = $tipo_midia === 'video' ? '50 MB' : '5 MB';
        throw new InvalidArgumentException("O arquivo excede o tamanho máximo permitido ($limite).");
    }

    $pastaUpload = __DIR__ . '/../../assets/uploads/exercicios/';

    if (!is_dir($pastaUpload)) {
        mkdir($pastaUpload, 0755, true);
    }

    $nomeArquivo  = uniqid('exercicio_', true) . '.' . $extensao;
    $caminhoCompleto = $pastaUpload . $nomeArquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
        throw new RuntimeException('Não foi possível salvar o arquivo.');
    }

    return BASE_URL . '/assets/uploads/exercicios/' . $nomeArquivo;
}


// =====================================================
// NOVO EXERCÍCIO - ABRIR FORMULÁRIO
// =====================================================

if (
    isset($_GET['acao']) &&
    $_GET['acao'] === 'novo' &&
    $_SERVER['REQUEST_METHOD'] === 'GET'
) {
    require __DIR__ . '/../views/exercicios/exercicios.php';
    exit;
}

// =====================================================
// EXCLUSÃO DE EXERCÍCIO
// =====================================================

if (
    isset($_GET['acao']) &&
    $_GET['acao'] === 'excluir'
) {

    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        header("Location: " . BASE_URL . "/app/controllers/ExercicioController.php?erro=invalido");
        exit;
    }

    excluirExercicio($pdo, $id);

    header("Location: " . BASE_URL . "/app/controllers/ExercicioController.php?sucesso=excluido");

    exit;
}



// =====================================================
// EDITAR EXERCÍCIO - ABRIR FORMULÁRIO
// =====================================================

if (
    isset($_GET['acao']) &&
    $_GET['acao'] === 'editar' &&
    $_SERVER['REQUEST_METHOD'] === 'GET'
) {

    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        header("Location: " . BASE_URL . "/app/controllers/ExercicioController.php?erro=invalido");
        exit;
    }

    $exercicio = buscarExercicioPorId($pdo, $id);

    if (!$exercicio) {
        header("Location: " . BASE_URL . "/app/controllers/ExercicioController.php?erro=nao_encontrado");
        exit;
    }

    require __DIR__ . '/../views/exercicios/editar.php';

    exit;
}


// =====================================================
// EDITAR EXERCÍCIO - SALVAR ALTERAÇÕES
// =====================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['acao'] ?? '') === 'editar'
) {

    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        header("Location: " . BASE_URL . "/app/controllers/ExercicioController.php?erro=invalido");
        exit;
    }

    $exercicio = buscarExercicioPorId($pdo, $id);

    if (!$exercicio) {
        header("Location: " . BASE_URL . "/app/controllers/ExercicioController.php?erro=nao_encontrado");
        exit;
    }

    $nome       = trim($_POST['nome'] ?? '');
    $grupo      = trim($_POST['grupo_muscular'] ?? '');
    $tipo_midia = trim($_POST['tipo_midia'] ?? '');

    if ($nome === '' || $grupo === '' || $tipo_midia === '') {
        header("Location: " . BASE_URL . "/app/controllers/ExercicioController.php?acao=editar&id=$id&erro=campos");
        exit;
    }

    // Mantém a mídia atual
    $midia = $exercicio['midia'];

    // Nova mídia (se enviada)
    if (
        isset($_FILES['arquivo']) &&
        $_FILES['arquivo']['error'] === UPLOAD_ERR_OK
    ) {
        try {
            $midia = processarUploadMidia($_FILES['arquivo'], $tipo_midia);
        } catch (Throwable $e) {
            header("Location: " . BASE_URL . "/app/controllers/ExercicioController.php?acao=editar&id=$id&erro=" . urlencode($e->getMessage()));
            exit;
        }
    }

    atualizarExercicio($pdo, $id, $nome, $grupo, $midia, $tipo_midia);

    header("Location: " . BASE_URL . "/app/controllers/ExercicioController.php?sucesso=atualizado");

    exit;
}


// =====================================================
// CADASTRO DE EXERCÍCIO
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome       = trim($_POST['nome'] ?? '');
    $grupo      = trim($_POST['grupo_muscular'] ?? '');
    $tipo_midia = trim($_POST['tipo_midia'] ?? '');

    if ($nome === '' || $grupo === '' || $tipo_midia === '') {
        header("Location: " . BASE_URL . "/app/controllers/ExercicioController.php?acao=novo&erro=campos");
        exit;
    }

    if (
        !isset($_FILES['arquivo']) ||
        $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK
    ) {
        header("Location: " . BASE_URL . "/app/controllers/ExercicioController.php?acao=novo&erro=arquivo");
        exit;
    }

    try {
        $midia = processarUploadMidia($_FILES['arquivo'], $tipo_midia);
    } catch (Throwable $e) {
        header("Location: " . BASE_URL . "/app/controllers/ExercicioController.php?acao=novo&erro=" . urlencode($e->getMessage()));
        exit;
    }

    cadastrarExercicio($pdo, $nome, $grupo, $midia, $tipo_midia);

    header("Location: " . BASE_URL . "/app/controllers/ExercicioController.php?sucesso=cadastrado");

    exit;
}


// =====================================================
// PESQUISA E FILTRO
// =====================================================

$pesquisa = $_GET['pesquisa'] ?? '';

$grupo = $_GET['grupo'] ?? '';


// =====================================================
// BUSCA OS EXERCÍCIOS
// =====================================================

if (!empty($pesquisa)) {

    $exercicios = pesquisarExercicios($pdo, $pesquisa);

} elseif (!empty($grupo)) {

    $exercicios = filtrarExerciciosPorGrupo($pdo, $grupo);

} else {

    $exercicios = listarExercicios($pdo);
}


// =====================================================
// VIEW DA BIBLIOTECA
// =====================================================

require __DIR__ . '/../views/treinos/biblioteca.php';
