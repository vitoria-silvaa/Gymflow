<?php
// app/models/Dashboard.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';

if ($operacao === 'metricas_executivas') {
    // 1. Alunos Ativos e Inativos
    if ($role_logado === 'Professor') {
        $totalAlunosAtivos = (int) $pdo->query("
            SELECT COUNT(*) FROM alunos WHERE status = 'Ativo' AND professor_id = $id_logado
        ")->fetchColumn();
        
        $totalAlunosInativos = (int) $pdo->query("
            SELECT COUNT(*) FROM alunos WHERE status = 'Inativo' AND professor_id = $id_logado
        ")->fetchColumn();
    } else {
        $totalAlunosAtivos = (int) $pdo->query("
            SELECT COUNT(*) FROM alunos WHERE status = 'Ativo'
        ")->fetchColumn();
        
        $totalAlunosInativos = (int) $pdo->query("
            SELECT COUNT(*) FROM alunos WHERE status = 'Inativo'
        ")->fetchColumn();
    }

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

    // 5. Novos Alunos agrupados por mês no ano atual
    if ($role_logado === 'Professor') {
        $stmtNovosAlunos = $pdo->query("
            SELECT MONTH(criado_em) AS mes, COUNT(*) AS total
            FROM alunos
            WHERE YEAR(criado_em) = YEAR(CURDATE()) AND professor_id = $id_logado
            GROUP BY MONTH(criado_em)
            ORDER BY MONTH(criado_em)
        ");
    } else {
        $stmtNovosAlunos = $pdo->query("
            SELECT MONTH(criado_em) AS mes, COUNT(*) AS total
            FROM alunos
            WHERE YEAR(criado_em) = YEAR(CURDATE())
            GROUP BY MONTH(criado_em)
            ORDER BY MONTH(criado_em)
        ");
    }
    $novosAlunosPorMes = $stmtNovosAlunos->fetchAll(PDO::FETCH_ASSOC);
}
