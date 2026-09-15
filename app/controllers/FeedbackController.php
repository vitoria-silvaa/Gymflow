<?php
// app/controllers/FeedbackController.php

require_once __DIR__ . '/../models/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Gymflow/#feedback');
    exit;
}

$company_id = 1;
$nota = (int)($_POST['nota'] ?? 0);
$mensagem = trim($_POST['mensagem'] ?? '');

$tamanhoMensagem = function_exists('mb_strlen')
    ? mb_strlen($mensagem, 'UTF-8')
    : strlen($mensagem);

if ($nota < 1 || $nota > 5 || $mensagem === '' || $tamanhoMensagem > 1000) {
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
        'Visitante',
        $nota,
        $mensagem
    ]);

    header('Location: /Gymflow/?feedback=sucesso#feedback');
    exit;
} catch (Throwable $e) {
    header('Location: /Gymflow/?feedback=erro#feedback');
    exit;
}
