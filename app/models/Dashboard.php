<?php
// app/models/Dashboard.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';

if ($operacao === 'metricas_executivas') {
    $company_id = $_SESSION['company_id'] ?? 0;
    
    // 1. Alunos Ativos e Inativos
    if ($role_logado === 'Professor') {
        $totalAlunosAtivos = (int) $pdo->query("
            SELECT COUNT(a.id) FROM alunos a INNER JOIN filiais f ON a.filial_id = f.id 
            WHERE a.status = 'Ativo' AND a.professor_id = $id_logado AND f.company_id = $company_id
        ")->fetchColumn();
        
        $totalAlunosInativos = (int) $pdo->query("
            SELECT COUNT(a.id) FROM alunos a INNER JOIN filiais f ON a.filial_id = f.id 
            WHERE a.status = 'Inativo' AND a.professor_id = $id_logado AND f.company_id = $company_id
        ")->fetchColumn();
    } else {
        $totalAlunosAtivos = (int) $pdo->query("
            SELECT COUNT(a.id) FROM alunos a INNER JOIN filiais f ON a.filial_id = f.id 
            WHERE a.status = 'Ativo' AND f.company_id = $company_id
        ")->fetchColumn();
        
        $totalAlunosInativos = (int) $pdo->query("
            SELECT COUNT(a.id) FROM alunos a INNER JOIN filiais f ON a.filial_id = f.id 
            WHERE a.status = 'Inativo' AND f.company_id = $company_id
        ")->fetchColumn();
    }

    // 2. Matrículas Ativas
    $totalMatriculasAtivas = (int) $pdo->query("
        SELECT COUNT(m.id) FROM matriculas m 
        INNER JOIN alunos a ON m.aluno_id = a.id 
        INNER JOIN filiais f ON a.filial_id = f.id 
        WHERE m.ativa = TRUE AND f.company_id = $company_id
    ")->fetchColumn();

    // 3. Receita Mensal Esperada (Mês Atual)
    $receitaMensalEsperada = (float) $pdo->query("
        SELECT COALESCE(SUM(c.valor), 0) 
        FROM contas c
        INNER JOIN alunos a ON c.aluno_id = a.id
        INNER JOIN filiais f ON a.filial_id = f.id
        WHERE MONTH(c.vencimento) = MONTH(CURDATE()) 
          AND YEAR(c.vencimento) = YEAR(CURDATE())
          AND f.company_id = $company_id
    ")->fetchColumn();

    // 4. Inadimplência: Contas vencidas não pagas e Total de contas vencidas
    $contasVencidasNaoPagas = (int) $pdo->query("
        SELECT COUNT(c.id) FROM contas c
        INNER JOIN alunos a ON c.aluno_id = a.id
        INNER JOIN filiais f ON a.filial_id = f.id
        WHERE c.vencimento < CURDATE() AND c.status != 'Pago' AND f.company_id = $company_id
    ")->fetchColumn();

    $totalContasVencidas = (int) $pdo->query("
        SELECT COUNT(c.id) FROM contas c
        INNER JOIN alunos a ON c.aluno_id = a.id
        INNER JOIN filiais f ON a.filial_id = f.id
        WHERE c.vencimento < CURDATE() AND f.company_id = $company_id
    ")->fetchColumn();

    // 5. Novos Alunos agrupados por mês no ano atual
    if ($role_logado === 'Professor') {
        $stmtNovosAlunos = $pdo->query("
            SELECT MONTH(a.criado_em) AS mes, COUNT(a.id) AS total
            FROM alunos a
            INNER JOIN filiais f ON a.filial_id = f.id
            WHERE YEAR(a.criado_em) = YEAR(CURDATE()) AND a.professor_id = $id_logado AND f.company_id = $company_id
            GROUP BY MONTH(a.criado_em)
            ORDER BY MONTH(a.criado_em)
        ");
    } else {
        $stmtNovosAlunos = $pdo->query("
            SELECT MONTH(a.criado_em) AS mes, COUNT(a.id) AS total
            FROM alunos a
            INNER JOIN filiais f ON a.filial_id = f.id
            WHERE YEAR(a.criado_em) = YEAR(CURDATE()) AND f.company_id = $company_id
            GROUP BY MONTH(a.criado_em)
            ORDER BY MONTH(a.criado_em)
        ");
    }
    $novosAlunosPorMes = $stmtNovosAlunos->fetchAll(PDO::FETCH_ASSOC);
}
