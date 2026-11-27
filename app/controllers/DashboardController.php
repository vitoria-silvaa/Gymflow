<?php
// app/controllers/DashboardController.php

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin', 'Professor', 'Recepcao']);

$role_logado = $_SESSION['usuario_role'];
$id_logado = (int) $_SESSION['usuario_id'];
$company_id = obterEmpresaLogada();

$baseUrl = BASE_URL .
    '/app/controllers/DashboardController.php';

$fuso = new DateTimeZone('America/Sao_Paulo');
$hoje = new DateTimeImmutable('today', $fuso);

$periodos = [
    'hoje' => 'Hoje',
    'semana' => 'Esta semana',
    'mes' => 'Este mês',
    'ano' => 'Este ano',
    'personalizado' => 'Personalizado'
];

$periodo = $_GET['periodo'] ?? 'mes';

if (
    !is_string($periodo) ||
    !isset($periodos[$periodo])
) {
    $periodo = 'mes';
}

$erroFiltro = '';

$inicio = $hoje->modify('first day of this month');
$fim = $hoje->modify('last day of this month');

$validarData = static function ($valor) use ($fuso) {

    if (
        !is_string($valor) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) ||
        $valor < '1000-01-01' ||
        $valor > '9999-12-30'
    ) {
        return null;
    }

    $data = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $valor,
        $fuso
    );

    return $data && $data->format('Y-m-d') === $valor
        ? $data
        : null;
};

switch ($periodo) {

    case 'hoje':

        $inicio = $hoje;
        $fim = $hoje;

        break;

    case 'semana':

        $inicio = $hoje->modify('monday this week');
        $fim = $hoje->modify('sunday this week');

        break;

    case 'ano':

        $inicio = $hoje->setDate(
            (int) $hoje->format('Y'),
            1,
            1
        );

        $fim = $hoje->setDate(
            (int) $hoje->format('Y'),
            12,
            31
        );

        break;

    case 'personalizado':

        $inicioInformado = $validarData(
            $_GET['data_inicio'] ?? null
        );

        $fimInformado = $validarData(
            $_GET['data_fim'] ?? null
        );

        if (
            $inicioInformado &&
            $fimInformado &&
            $inicioInformado <= $fimInformado &&
            $fimInformado <= $inicioInformado->modify('+5 years')
        ) {

            $inicio = $inicioInformado;
            $fim = $fimInformado;

        } else {

            $erroFiltro =
                'Informe um período válido de até cinco anos. ' .
                'Exibindo o mês atual.';

            $periodo = 'mes';
        }

        break;
}

$dataInicio = $inicio->format('Y-m-d');
$dataFim = $fim->format('Y-m-d');

$inicioConsulta = $dataInicio;

$fimConsulta = $fim
    ->modify('+1 day')
    ->format('Y-m-d');

$dataHoje = $hoje->format('Y-m-d');

$periodoDescricao =
    $inicio->format('d/m/Y') .
    ' a ' .
    $fim->format('d/m/Y');

// Agrupa por dia em períodos curtos
// e por mês em períodos maiores.

$agrupamentoGrafico =
    $inicio->diff($fim)->days <= 62
        ? 'dia'
        : 'mes';

// Consultar métricas.

$operacao = 'metricas_executivas';

try {

    require __DIR__ . '/../models/Dashboard.php';

} catch (Throwable $e) {

    error_log(
        '[GymFlow][Dashboard] ' .
        get_class($e) . ' | ' .
        $e->getMessage()
    );

    http_response_code(500);

    exit('Não foi possível consultar o Dashboard.');
}

// Indicadores por perfil.

$cards = [];

if ($role_logado === 'Admin') {

    $taxaInadimplencia = $totalContasVencidas > 0
        ? round(
            ($contasVencidasNaoPagas /
                $totalContasVencidas) * 100,
            1
        )
        : 0.0;

    $cards = [
        [
            'titulo' => 'Alunos Ativos',
            'valor' => $totalAlunosAtivos,
            'info' => 'Cadastros atualmente ativos',
            'icone' => 'users'
        ],
        [
            'titulo' => 'Matrículas Ativas',
            'valor' => $totalMatriculasAtivas,
            'info' => 'Planos atualmente vigentes',
            'icone' => 'calendar-check'
        ],
        [
            'titulo' => 'Receita Esperada',
            'valor' => 'R$ ' . number_format(
                $receitaEsperada,
                2,
                ',',
                '.'
            ),
            'info' => 'Previsão para o período',
            'icone' => 'coins'
        ],
        [
            'titulo' => 'Taxa de Inadimplência',
            'valor' => number_format(
                $taxaInadimplencia,
                1,
                ',',
                '.'
            ) . '%',
            'info' => 'Contas do período vencidas e não pagas',
            'icone' => 'percent'
        ]
    ];

} elseif ($role_logado === 'Recepcao') {

    $cards = [
        [
            'titulo' => 'Alunos Ativos',
            'valor' => $totalAlunosAtivos,
            'info' => 'Cadastros atualmente ativos',
            'icone' => 'users'
        ],
        [
            'titulo' => 'Matrículas Ativas',
            'valor' => $totalMatriculasAtivas,
            'info' => 'Planos atualmente vigentes',
            'icone' => 'calendar-check'
        ]
    ];

} elseif ($role_logado === 'Professor') {

    $cards = [
        [
            'titulo' => 'Alunos Ativos',
            'valor' => $totalAlunosAtivos,
            'info' => 'Alunos ativos na sua carteira',
            'icone' => 'users'
        ],
        [
            'titulo' => 'Alunos Inativos',
            'valor' => $totalAlunosInativos,
            'info' => 'Alunos inativos na sua carteira',
            'icone' => 'user-x'
        ]
    ];
}

// Preparar gráfico.

$mapaGrafico = [];

foreach ($novosAlunosPorPeriodo as $item) {

    $mapaGrafico[$item['referencia']] =
        (int) $item['total'];
}

$labelsGrafico = [];
$valoresGrafico = [];

$meses = [
    1 => 'Jan',
    2 => 'Fev',
    3 => 'Mar',
    4 => 'Abr',
    5 => 'Mai',
    6 => 'Jun',
    7 => 'Jul',
    8 => 'Ago',
    9 => 'Set',
    10 => 'Out',
    11 => 'Nov',
    12 => 'Dez'
];

if ($agrupamentoGrafico === 'dia') {

    for (
        $cursor = $inicio;
        $cursor <= $fim;
        $cursor = $cursor->modify('+1 day')
    ) {

        $chave = $cursor->format('Y-m-d');

        $labelsGrafico[] = $cursor->format('d/m');

        $valoresGrafico[] =
            $mapaGrafico[$chave] ?? 0;
    }

} else {

    for (
        $cursor = $inicio->modify('first day of this month');
        $cursor <= $fim;
        $cursor = $cursor->modify('+1 month')
    ) {

        $chave = $cursor->format('Y-m');

        $labelsGrafico[] =
            $meses[(int) $cursor->format('n')] .
            '/' .
            $cursor->format('Y');

        $valoresGrafico[] =
            $mapaGrafico[$chave] ?? 0;
    }
}

$totalNovosPeriodo = array_sum($valoresGrafico);

$exibirFinanceiro = $role_logado === 'Admin';

$tituloPagina = 'Dashboard';

require __DIR__ . '/../views/dashboard/index.php';