<?php
// app/controllers/DashboardController.php


require_once __DIR__ . '/../../config/sessao.php';
verificarRole(['Admin', 'Professor', 'Recepcao']);
$role_logado = $_SESSION['usuario_role'] ?? 'Admin';
$id_logado = $_SESSION['usuario_id'] ?? 0;

$operacao = 'metricas_executivas';
require __DIR__ . '/../models/Dashboard.php';

// Calcula a taxa de inadimplência evitando divisão por zero
$taxaInadimplencia = ($totalContasVencidas > 0)
    ? round(($contasVencidasNaoPagas / $totalContasVencidas) * 100, 1)
    : 0.0;

// Lista de cards para exibição simplificada na view
$cards = [];

if ($role_logado === 'Professor') {
    $cards = [
        [
            'titulo' => 'Alunos Ativos',
            'valor'  => $totalAlunosAtivos,
            'info'   => 'Alunos ativos na sua carteira'
        ],
        [
            'titulo' => 'Alunos Inativos',
            'valor'  => $totalAlunosInativos ?? 0,
            'info'   => 'Alunos inativos na sua carteira'
        ]
    ];
} else {
    $cards = [
        [
            'titulo' => 'Alunos Ativos',
            'valor'  => $totalAlunosAtivos,
            'info'   => 'Alunos com cadastro ativo'
        ],
        [
            'titulo' => 'Matrículas Ativas',
            'valor'  => $totalMatriculasAtivas,
            'info'   => 'Planos vigentes'
        ],
        [
            'titulo' => 'Receita Mensal Esperada',
            'valor'  => 'R$ ' . number_format($receitaMensalEsperada, 2, ',', '.'),
            'info'   => 'Previsão para o mês atual'
        ],
        [
            'titulo' => 'Taxa de Inadimplência',
            'valor'  => number_format($taxaInadimplencia, 1, ',', '.') . '%',
            'info'   => 'Contas vencidas não pagas'
        ]
    ];
}

// Dados dos novos alunos cadastrados por mês no ano atual
$dadosGraficoNovosAlunos = $novosAlunosPorMes ?? [];

$tituloPagina = "Dashboard";
require __DIR__ . '/../views/dashboard/index.php';
