<?php
// app/models/Dashboard.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';

if ($operacao === 'metricas_executivas') {
    // 1. Alunos Ativos
    $totalAlunosAtivos = (int) $pdo->query("
        SELECT COUNT(*) FROM alunos WHERE status = 'Ativo'
    ")->fetchColumn();

    // 2. Matrículas Ativas
    $totalMatriculasAtivas = (int) $pdo->query("
        SELECT COUNT(*) FROM matriculas WHERE ativa = TRUE
    ")->fetchColumn();

    // 3. Receita Mensal Esperada (Mês Atual)
    $receitaMensalEsperada = (float) $pdo->query("
        SELECT COALESCE(SUM(valor), 0) 
        FROM contas 
        WHERE MONTH(vencimento) = MONTH(CURDATE()) 
          AND YEAR(vencimento) = YEAR(CURDATE())
    ")->fetchColumn();

    // 4. Inadimplência: Contas vencidas não pagas e Total de contas vencidas
    $contasVencidasNaoPagas = (int) $pdo->query("
        SELECT COUNT(*) FROM contas WHERE vencimento < CURDATE() AND status != 'Pago'
    ")->fetchColumn();

    $totalContasVencidas = (int) $pdo->query("
        SELECT COUNT(*) FROM contas WHERE vencimento < CURDATE()
    ")->fetchColumn();
}
