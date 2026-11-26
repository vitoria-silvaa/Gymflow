<?php
// app/controllers/FinanceiroController.php

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin']);

$acao = $_GET['acao'] ?? 'listar';

if (
    $acao !== 'listar' ||
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET'
) {
    http_response_code(403);
    exit('Operação não autorizada.');
}

$tituloPagina = 'Financeiro';

$baseUrl = BASE_URL .
    '/app/controllers/FinanceiroController.php';

$texto = static fn($v) =>
    is_string($v) ? trim($v) : '';

$aba = $texto($_GET['aba'] ?? 'aberto');

if (!in_array(
    $aba,
    ['aberto', 'dinheiro', 'digital', 'outros'],
    true
)) {
    $aba = 'aberto';
}

$filtro = $texto($_GET['filtro'] ?? 'nenhum');

$filtrosPermitidos = [
    'nenhum',
    'nome',
    'cpf',
    'matricula',
    'filial',
    'periodo'
];

if (!in_array($filtro, $filtrosPermitidos, true)) {
    $filtro = 'nenhum';
}

$busca = '';
$filial_id = 0;

$periodo = 'todos';
$inicio = '';
$fim = '';

$erro = '';
$filtroValido = true;

$hoje = new DateTimeImmutable(
    'now',
    new DateTimeZone('America/Sao_Paulo')
);

$periodos = [
    'todos' => 'Todo o histórico',
    'hoje' => 'Hoje',
    'semana' => 'Esta semana',
    'mes' => 'Este mês',
    'mes_anterior' => 'Mês anterior',
    'ano' => 'Este ano',
    'personalizado' => 'Personalizado'
];

$dataValida = static function (string $valor): bool {

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
        return false;
    }

    $data = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $valor
    );

    return $data !== false &&
        $data->format('Y-m-d') === $valor &&
        $valor >= '1000-01-01' &&
        $valor <= '9999-12-30';
};

if (in_array(
    $filtro,
    ['nome', 'cpf', 'matricula'],
    true
)) {

    $busca = $texto($_GET['busca'] ?? '');

    $tamanho = preg_match_all('/./us', $busca);

    if ($tamanho === false || $tamanho > 100) {

        $erro = 'Informe uma pesquisa válida.';

    } elseif ($busca !== '' && $filtro === 'cpf') {

        $cpf = preg_replace('/\D/', '', $busca);

        if (
            !preg_match('/^[0-9.\- ]+$/', $busca) ||
            strlen($cpf) !== 11
        ) {
            $erro = 'Informe um CPF com 11 dígitos.';
        }

    } elseif ($busca !== '' && $filtro === 'matricula') {

        $numero = ltrim($busca, '#');

        if (
            !preg_match('/^[0-9]{1,10}$/', $numero) ||
            (int) $numero <= 0 ||
            (int) $numero > 2147483647
        ) {
            $erro = 'Informe um número de matrícula válido.';
        }
    }

} elseif ($filtro === 'filial') {

    $filialInformada = $texto(
        $_GET['filial_id'] ?? '0'
    );

    $id = filter_var(
        $filialInformada,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0]]
    );

    if ($id === false) {
        $erro = 'Selecione uma filial válida.';
    } else {
        $filial_id = (int) $id;
    }

} elseif ($filtro === 'periodo') {

    $periodo = $texto($_GET['periodo'] ?? 'todos');

    if (!isset($periodos[$periodo])) {
        $periodo = 'todos';
    }

    switch ($periodo) {

        case 'hoje':

            $inicio = $hoje->format('Y-m-d');
            $fim = $inicio;

            break;

        case 'semana':

            $inicio = $hoje
                ->modify('monday this week')
                ->format('Y-m-d');

            $fim = $hoje
                ->modify('sunday this week')
                ->format('Y-m-d');

            break;

        case 'mes':

            $inicio = $hoje->format('Y-m-01');
            $fim = $hoje->format('Y-m-t');

            break;

        case 'mes_anterior':

            $inicio = $hoje
                ->modify('first day of last month')
                ->format('Y-m-d');

            $fim = $hoje
                ->modify('last day of last month')
                ->format('Y-m-d');

            break;

        case 'ano':

            $inicio = $hoje->format('Y-01-01');
            $fim = $hoje->format('Y-12-31');

            break;

        case 'personalizado':

            $inicio = $texto($_GET['inicio'] ?? '');
            $fim = $texto($_GET['fim'] ?? '');

            if (
                !$dataValida($inicio) ||
                !$dataValida($fim) ||
                $inicio > $fim
            ) {
                $erro = 'Informe um intervalo de datas válido.';
            }

            break;
    }
}

$filtroValido = $erro === '';

$operacao = 'listar_contas';

require __DIR__ . '/../models/Financeiro.php';

if (!empty($erroModel)) {
    $erro = $erroModel;
}

// Mantém o filtro selecionado ao trocar de aba.

$parametrosFiltro = [
    'filtro' => $filtro
];

if (in_array(
    $filtro,
    ['nome', 'cpf', 'matricula'],
    true
)) {

    $parametrosFiltro['busca'] = $busca;

} elseif ($filtro === 'filial') {

    $parametrosFiltro['filial_id'] = $filial_id;

} elseif ($filtro === 'periodo') {

    $parametrosFiltro['periodo'] = $periodo;

    if ($periodo === 'personalizado') {

        $parametrosFiltro['inicio'] = $inicio;
        $parametrosFiltro['fim'] = $fim;
    }
}

require __DIR__ . '/../views/financeiro/index.php';