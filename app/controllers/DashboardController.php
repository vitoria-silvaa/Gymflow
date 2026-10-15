
<?php
// app/controllers/DashboardController.php

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin', 'Professor', 'Recepcao']);

$role_logado = $_SESSION['usuario_role'];
$id_logado = (int) $_SESSION['usuario_id'];
$company_id = obterEmpresaLogada();

// =====================================================
// 1. BUSCAR MÉTRICAS
// =====================================================

// Mantém a integração atual com o model.
// Precisamos revisar Dashboard.php para garantir que
// as consultas respeitem empresa, filial e perfil.

$operacao = 'metricas_executivas';
require __DIR__ . '/../models/Dashboard.php';

// =====================================================
// 2. PREPARAR CARDS POR PERFIL
// =====================================================

$cards = [];

if ($role_logado === 'Admin') {

    // Apenas o administrador vê indicadores financeiros.

    $taxaInadimplencia = ($totalContasVencidas > 0)
        ? round(
            ($contasVencidasNaoPagas / $totalContasVencidas) * 100,
            1
        )
        : 0.0;

    $cards = [
        [
            'titulo' => 'Alunos Ativos',
            'valor' => $totalAlunosAtivos ?? 0,
            'info' => 'Alunos com cadastro ativo'
        ],
        [
            'titulo' => 'Matrículas Ativas',
            'valor' => $totalMatriculasAtivas ?? 0,
            'info' => 'Planos vigentes'
        ],
        [
            'titulo' => 'Receita Mensal Esperada',
            'valor' => 'R$ ' . number_format(
                $receitaMensalEsperada ?? 0,
                2,
                ',',
                '.'
            ),
            'info' => 'Previsão para o mês atual'
        ],
        [
            'titulo' => 'Taxa de Inadimplência',
            'valor' => number_format(
                $taxaInadimplencia,
                1,
                ',',
                '.'
            ) . '%',
            'info' => 'Contas vencidas não pagas'
        ]
    ];
} elseif ($role_logado === 'Recepcao') {

    // Recepção: somente indicadores operacionais.
    // Os valores deverão ser filtrados pelas filiais
    // autorizadas no model Dashboard.php.

    $cards = [
        [
            'titulo' => 'Alunos Ativos',
            'valor' => $totalAlunosAtivos ?? 0,
            'info' => 'Alunos ativos'
        ],
        [
            'titulo' => 'Matrículas Ativas',
            'valor' => $totalMatriculasAtivas ?? 0,
            'info' => 'Matrículas vigentes'
        ]
    ];
} elseif ($role_logado === 'Professor') {

    $cards = [
        [
            'titulo' => 'Alunos Ativos',
            'valor' => $totalAlunosAtivos ?? 0,
            'info' => 'Alunos ativos na sua carteira'
        ],
        [
            'titulo' => 'Alunos Inativos',
            'valor' => $totalAlunosInativos ?? 0,
            'info' => 'Alunos inativos na sua carteira'
        ]
    ];
}

// =====================================================
// 3. DADOS DO GRÁFICO
// =====================================================

$dadosGraficoNovosAlunos = $novosAlunosPorMes ?? [];

// Identifica o perfil para a view.
// Não basta esconder indicadores: a view também
// deve respeitar o perfil antes de exibir informações.

$exibirFinanceiro = ($role_logado === 'Admin');

$tituloPagina = "Dashboard";

require __DIR__ . '/../views/dashboard/index.php';
