<?php
// app/controllers/DashboardController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';
verificarRole(['Admin', 'Professor', 'Recepcao']);

$operacao = 'metricas_executivas';
require __DIR__ . '/../models/Dashboard.php';

// Calcula a taxa de inadimplência evitando divisão por zero
$taxaInadimplencia = ($totalContasVencidas > 0)
    ? round(($contasVencidasNaoPagas / $totalContasVencidas) * 100, 1)
    : 0.0;

// Lista de cards para exibição simplificada na view
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

$tituloPagina = "Dashboard";
require __DIR__ . '/../views/dashboard/index.php';
