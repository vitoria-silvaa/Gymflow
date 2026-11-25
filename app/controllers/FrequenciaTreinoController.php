<?php
// GYMFLOW - FREQUÊNCIA MENSAL DE TREINOS

require_once __DIR__ . '/../../config/sessao.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

function respostaFrequencia(
    array $dados,
    int $status = 200
): void {

    http_response_code($status);

    echo json_encode(
        $dados,
        JSON_UNESCAPED_UNICODE |
            JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

$empresa = (int) ($_SESSION['company_id'] ?? 0);
$aluno = (int) ($_SESSION['aluno_id'] ?? 0);
$usuario = (int) ($_SESSION['usuario_id'] ?? 0);

if (
    ($_SESSION['usuario_role'] ?? '') !== 'Aluno' ||
    min($empresa, $aluno, $usuario) <= 0
) {

    respostaFrequencia([
        'sucesso' => false,
        'mensagem' => 'Acesso não autorizado.'
    ], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {

    header('Allow: GET');

    respostaFrequencia([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], 405);
}

try {

    require_once __DIR__ . '/../models/Database.php';
    require_once __DIR__ . '/../models/HistoricoTreino.php';

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    if (!historicoAutorizar(
        $pdo,
        $empresa,
        $aluno,
        $usuario
    )) {

        respostaFrequencia([
            'sucesso' => false,
            'mensagem' => 'Acesso não autorizado.'
        ], 403);
    }

    $hoje = new DateTimeImmutable(
        'today',
        new DateTimeZone('America/Sao_Paulo')
    );

    $mes = $_GET['mes'] ?? $hoje->format('Y-m');

    if (
        !is_string($mes) ||
        !preg_match('/^20[0-9]{2}-(0[1-9]|1[0-2])$/D', $mes) ||
        $mes > $hoje->format('Y-m')
    ) {

        respostaFrequencia([
            'sucesso' => false,
            'mensagem' => 'Mês inválido.'
        ], 422);
    }

    $inicio = new DateTimeImmutable(
        $mes . '-01',
        $hoje->getTimezone()
    );

    $fim = $inicio->modify('+1 month');

    // Buscar os exercícios distintos realizados
    // pelo aluno em cada dia do mês.

    $stmt = $pdo->prepare("
        SELECT
            DATE(e.concluido_em) AS dia,
            e.exercicio_origem_id AS exercicio_id,
            MAX(e.nome_exercicio) AS nome,
            COUNT(*) AS execucoes

        FROM treino_sessao_exercicios e

        INNER JOIN treino_sessoes s
            ON s.id = e.sessao_id

        WHERE s.company_id = ?
          AND s.aluno_id = ?
          AND e.concluido_em >= ?
          AND e.concluido_em < ?
          AND e.exercicio_origem_id IS NOT NULL

        GROUP BY
            DATE(e.concluido_em),
            e.exercicio_origem_id

        ORDER BY dia ASC, nome ASC
    ");

    $stmt->execute([
        $empresa,
        $aluno,
        $inicio->format('Y-m-d H:i:s'),
        $fim->format('Y-m-d H:i:s')
    ]);

    $dias = [];

    foreach (
        $stmt->fetchAll(PDO::FETCH_ASSOC) as $r
    ) {

        $dias[$r['dia']][] = [

            'nome' => $r['nome'],

            'execucoes' => (int) $r['execucoes']
        ];
    }

    respostaFrequencia([

        'sucesso' => true,

        'mes' => $mes,

        'hoje' => $hoje->format('Y-m-d'),

        'dias' => (object) $dias
    ]);
} catch (Throwable $erro) {

    error_log(
        '[GymFlow - Frequência] ' .
            $erro->getMessage()
    );

    respostaFrequencia([
        'sucesso' => false,
        'mensagem' => 'Não foi possível consultar a frequência.'
    ], 500);
}
