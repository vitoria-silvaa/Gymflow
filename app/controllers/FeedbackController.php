<?php
// app/controllers/FeedbackController.php

require_once __DIR__ . '/../../config/sessao.php';
require_once __DIR__ . '/../models/Database.php';

// Só aceita envio pelo formulário
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Gymflow/#feedback');
    exit;
}

// Apenas alunos autenticados podem enviar feedback
if (
    !isset($_SESSION['usuario_id']) ||
    ($_SESSION['usuario_role'] ?? '') !== 'Aluno' ||
    empty($_SESSION['usuario_nome'])
) {
    header('Location: /Gymflow/app/controllers/LoginController.php?acao=login');
    exit;
}

$company_id = 1;

// Nome vem da sessão do usuário autenticado
$nome = trim($_SESSION['usuario_nome']);

$nota = (int)($_POST['nota'] ?? 0);
$mensagem = trim($_POST['mensagem'] ?? '');

$tamanhoMensagem = function_exists('mb_strlen')
    ? mb_strlen($mensagem, 'UTF-8')
    : strlen($mensagem);

$tamanhoNome = function_exists('mb_strlen')
    ? mb_strlen($nome, 'UTF-8')
    : strlen($nome);

// Validação dos dados
if (
    $nome === '' ||
    $tamanhoNome > 100 ||
    $nota < 1 ||
    $nota > 5 ||
    $mensagem === '' ||
    $tamanhoMensagem > 1000
) {
    header('Location: /Gymflow/?feedback=erro#feedback');
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO portfolio_feedbacks (
            company_id,
            nome,
            nota,
            mensagem,
            ativo
        ) VALUES (?, ?, ?, ?, 1)
    ");

    $stmt->execute([
        $company_id,
        $nome,
        $nota,
        $mensagem
    ]);

    header('Location: /Gymflow/?feedback=sucesso#feedback');
    exit;

} catch (Throwable $e) {
    header('Location: /Gymflow/?feedback=erro#feedback');
    exit;
}
