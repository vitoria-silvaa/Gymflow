<?php
// app/models/Dashboard.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';

if ($operacao !== 'metricas_executivas') {
    return;
}

$company_id = (int) ($_SESSION['company_id'] ?? 0);
$id_logado = (int) ($_SESSION['usuario_id'] ?? 0);
$role_logado = $_SESSION['usuario_role'] ?? '';

if (
    $company_id <= 0 ||
    $id_logado <= 0 ||
    !in_array(
        $role_logado,
        ['Admin', 'Professor', 'Recepcao'],
        true
    )
) {
    throw new RuntimeException(
        'Sessão ou perfil inválido.'
    );
}

// Função auxiliar para consultas.

$consultarDashboard = static function (
    string $sql,
    array $params = []
) use ($pdo): PDOStatement {

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt;
};

// Confirma a identidade do usuário.

$autorizado = $consultarDashboard("
    SELECT id
    FROM users
    WHERE id = ?
      AND company_id = ?
      AND role = ?
    LIMIT 1
", [
    $id_logado,
    $company_id,
    $role_logado
])->fetchColumn();

if (!$autorizado) {

    throw new RuntimeException(
        'Usuário não autorizado.'
    );
}

// Escopo de alunos por empresa e perfil.

$filtro = "f.company_id = :company_id";

$parametros = [
    ':company_id' => $company_id
];

if ($role_logado === 'Recepcao') {

    $filtro .= "
        AND EXISTS (
            SELECT 1
            FROM user_filiais uf
            WHERE uf.filial_id = f.id
              AND uf.user_id = :user_id
        )
    ";

    $parametros[':user_id'] = $id_logado;

} elseif ($role_logado === 'Professor') {

    $filtro .= "
        AND a.professor_id = :professor_id
    ";

    $parametros[':professor_id'] = $id_logado;
}

// Alunos ativos.

$totalAlunosAtivos = (int) $consultarDashboard("
    SELECT COUNT(a.id)
    FROM alunos a

    INNER JOIN filiais f
        ON f.id = a.filial_id

    WHERE a.status = 'Ativo'
      AND $filtro

", $parametros)->fetchColumn();

// Alunos inativos.

$totalAlunosInativos = (int) $consultarDashboard("
    SELECT COUNT(a.id)
    FROM alunos a

    INNER JOIN filiais f
        ON f.id = a.filial_id

    WHERE a.status = 'Inativo'
      AND $filtro

", $parametros)->fetchColumn();

// Matrículas atualmente vigentes.

$totalMatriculasAtivas = 0;

if (in_array(
    $role_logado,
    ['Admin', 'Recepcao'],
    true
)) {

    $filtroMatriculas = $filtro;

    $parametrosMatriculas = array_merge(
        $parametros,
        [
            ':data_inicio_vigencia' => $dataHoje,
            ':data_fim_vigencia' => $dataHoje
        ]
    );

    if ($role_logado === 'Recepcao') {

        $filtroMatriculas .= "
            AND EXISTS (
                SELECT 1
                FROM user_filiais ufm
                WHERE ufm.filial_id = fm.id
                  AND ufm.user_id = :user_matricula
            )
        ";

        $parametrosMatriculas[':user_matricula'] =
            $id_logado;
    }

    $totalMatriculasAtivas = (int) $consultarDashboard("
        SELECT COUNT(m.id)

        FROM matriculas m

        INNER JOIN alunos a
            ON a.id = m.aluno_id

        INNER JOIN filiais f
            ON f.id = a.filial_id

        INNER JOIN filiais fm
            ON fm.id = COALESCE(
                m.filial_id,
                a.filial_id
            )
            AND fm.company_id = f.company_id

        INNER JOIN planos p
            ON p.id = m.plano_id
            AND p.company_id = f.company_id

        WHERE m.ativa = 1

          AND m.inicio <= :data_inicio_vigencia
          AND m.fim >= :data_fim_vigencia

          AND (
              m.status_matricula IS NULL
              OR m.status_matricula = 'ativa'
          )

          AND $filtroMatriculas

    ", $parametrosMatriculas)->fetchColumn();
}

// Indicadores financeiros exclusivos do Admin.

$receitaEsperada = 0.0;

$contasVencidasNaoPagas = 0;
$totalContasVencidas = 0;

if ($role_logado === 'Admin') {

    $escopoFinanceiro = "
        FROM contas c

        INNER JOIN alunos a
            ON a.id = c.aluno_id

        INNER JOIN matriculas m
            ON m.id = c.matricula_id
            AND m.aluno_id = a.id

        INNER JOIN filiais f
            ON f.id = a.filial_id

        INNER JOIN filiais fm
            ON fm.id = COALESCE(
                m.filial_id,
                a.filial_id
            )
            AND fm.company_id = f.company_id

        INNER JOIN planos p
            ON p.id = m.plano_id
            AND p.company_id = f.company_id

        WHERE f.company_id = :company_id

          AND c.vencimento >= :inicio
          AND c.vencimento < :fim

          AND c.status IN (
              'Aberto',
              'Vencido',
              'Atrasado',
              'Pago'
          )
    ";

    $parametrosFinanceiro = [
        ':company_id' => $company_id,
        ':inicio' => $inicioConsulta,
        ':fim' => $fimConsulta
    ];

    // Receita prevista no período selecionado.

    $receitaEsperada = (float) $consultarDashboard("
        SELECT COALESCE(SUM(c.valor), 0)
        $escopoFinanceiro
    ", $parametrosFinanceiro)->fetchColumn();

    // Inadimplência atual das cobranças do período.

    $parametrosVencimento = array_merge(
        $parametrosFinanceiro,
        [
            ':hoje' => $dataHoje
        ]
    );

    $dadosVencimento = $consultarDashboard("
        SELECT
            COUNT(c.id) AS total,

            COALESCE(
                SUM(
                    CASE
                        WHEN c.status IN (
                            'Aberto',
                            'Vencido',
                            'Atrasado'
                        )
                        THEN 1
                        ELSE 0
                    END
                ),
                0
            ) AS nao_pagas

        $escopoFinanceiro

        AND c.vencimento < :hoje

    ", $parametrosVencimento)->fetch(PDO::FETCH_ASSOC);

    $totalContasVencidas =
        (int) ($dadosVencimento['total'] ?? 0);

    $contasVencidasNaoPagas =
        (int) ($dadosVencimento['nao_pagas'] ?? 0);
}

// Novos alunos cadastrados no período.

$formatoAgrupamento = $agrupamentoGrafico === 'dia'
    ? '%Y-%m-%d'
    : '%Y-%m';

$parametrosGrafico = array_merge(
    $parametros,
    [
        ':inicio' => $inicioConsulta,
        ':fim' => $fimConsulta
    ]
);

$novosAlunosPorPeriodo = $consultarDashboard("
    SELECT
        DATE_FORMAT(
            a.criado_em,
            '$formatoAgrupamento'
        ) AS referencia,

        COUNT(a.id) AS total

    FROM alunos a

    INNER JOIN filiais f
        ON f.id = a.filial_id

    WHERE a.criado_em >= :inicio
      AND a.criado_em < :fim

      AND $filtro

    GROUP BY referencia

    ORDER BY referencia ASC

", $parametrosGrafico)->fetchAll(PDO::FETCH_ASSOC);