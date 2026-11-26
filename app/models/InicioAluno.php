<?php
// ============================================
// GYMFLOW - PÁGINA INICIAL DO ALUNO
// app/models/InicioAluno.php
// ============================================

require_once __DIR__ . '/Database.php';

function consultarInicioAluno(
    PDO $pdo,
    int $usuarioId,
    int $alunoId,
    int $empresaId
): ?array {

    // DATA E HORÁRIO

    $agora = new DateTimeImmutable(
        'now',
        new DateTimeZone('America/Sao_Paulo')
    );

    $hoje = $agora->format('Y-m-d');

    $inicioMes = $agora
        ->modify('first day of this month')
        ->setTime(0, 0, 0);

    $proximoMes = $inicioMes
        ->modify('+1 month');

    // ========================================
    // 1. VALIDAR O ALUNO E A EMPRESA
    // ========================================

    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.nome,
            a.status,
            f.nome AS nome_filial

        FROM alunos a

        INNER JOIN filiais f
            ON f.id = a.filial_id

        INNER JOIN users u
            ON u.aluno_id = a.id
            AND u.company_id = f.company_id

        WHERE a.id = :aluno_id
          AND u.id = :usuario_id
          AND u.role = 'Aluno'
          AND f.company_id = :empresa_id

        LIMIT 1
    ");

    $stmt->execute([

        ':aluno_id' => $alunoId,

        ':usuario_id' => $usuarioId,

        ':empresa_id' => $empresaId
    ]);

    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$aluno) {

        return null;
    }

    // ========================================
    // 2. CONSULTAR MATRÍCULA E PLANO
    // ========================================

    $stmt = $pdo->prepare("
        SELECT
            m.id,
            m.inicio,
            m.fim,
            m.ativa,
            p.nome AS nome_plano,
            p.categoria,
            p.duracao

        FROM matriculas m

        INNER JOIN planos p
            ON p.id = m.plano_id

        WHERE m.aluno_id = :aluno_id
          AND p.company_id = :empresa_id
          AND m.ativa = TRUE

        ORDER BY
            CASE
                WHEN m.inicio <= :hoje_inicio
                 AND m.fim >= :hoje_fim
                THEN 0

                WHEN m.inicio > :hoje_futuro
                THEN 1

                ELSE 2
            END,
            m.inicio DESC,
            m.id DESC

        LIMIT 1
    ");

    $stmt->execute([

        ':aluno_id' => $alunoId,

        ':empresa_id' => $empresaId,

        ':hoje_inicio' => $hoje,

        ':hoje_fim' => $hoje,

        ':hoje_futuro' => $hoje
    ]);

    $matricula = $stmt->fetch(
        PDO::FETCH_ASSOC
    ) ?: null;

    $statusPlano = 'sem_plano';

    if ($matricula) {

        if ($matricula['inicio'] > $hoje) {

            $statusPlano = 'futuro';
        } elseif ($matricula['fim'] < $hoje) {

            $statusPlano = 'vencido';
        } else {

            $statusPlano = 'ativo';
        }
    }

    // ========================================
    // 3. FREQUÊNCIA REAL DOS TREINOS
    // ========================================

    $stmt = $pdo->prepare("
        SELECT
            DATE(e.concluido_em) AS dia,

            COUNT(
                DISTINCT e.exercicio_origem_id
            ) AS total_exercicios

        FROM treino_sessao_exercicios e

        INNER JOIN treino_sessoes s
            ON s.id = e.sessao_id

        WHERE s.aluno_id = :aluno_id
          AND s.company_id = :empresa_id
          AND e.concluido_em >= :inicio_mes
          AND e.concluido_em < :proximo_mes

        GROUP BY DATE(e.concluido_em)
    ");

    $stmt->execute([

        ':aluno_id' => $alunoId,

        ':empresa_id' => $empresaId,

        ':inicio_mes' => $inicioMes->format(
            'Y-m-d H:i:s'
        ),

        ':proximo_mes' => $proximoMes->format(
            'Y-m-d H:i:s'
        )
    ]);

    $diasCompletos = 0;
    $diasParciais = 0;

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $dia) {

        $total = (int) $dia['total_exercicios'];

        if ($total >= 4) {

            $diasCompletos++;
        } elseif ($total >= 1) {

            $diasParciais++;
        }
    }

    // ========================================
    // 4. FATURAS PENDENTES
    // ========================================

    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total,

            COALESCE(
                SUM(
                    CASE
                        WHEN c.vencimento < :hoje
                        THEN 1
                        ELSE 0
                    END
                ),
                0
            ) AS atrasadas

        FROM contas c

        INNER JOIN matriculas m
            ON m.id = c.matricula_id
            AND m.aluno_id = c.aluno_id

        INNER JOIN planos p
            ON p.id = m.plano_id

        WHERE c.aluno_id = :aluno_id
          AND p.company_id = :empresa_id
          AND c.status != 'Pago'
    ");

    $stmt->execute([

        ':hoje' => $hoje,

        ':aluno_id' => $alunoId,

        ':empresa_id' => $empresaId
    ]);

    $financeiro = $stmt->fetch(PDO::FETCH_ASSOC);

    // ========================================
    // 5. MESES EM PORTUGUÊS
    // ========================================

    $meses = [

        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro'
    ];

    $mesAtual = $meses[(int) $agora->format('n')];

    // ========================================
    // 6. RETORNAR DADOS DA PÁGINA
    // ========================================

    return [

        'aluno' => $aluno,

        'matricula' => $matricula,

        'status_plano' => $statusPlano,

        'dias_completos' => $diasCompletos,

        'dias_parciais' => $diasParciais,

        'faturas_abertas' => (int) (
            $financeiro['total'] ?? 0
        ),

        'faturas_atrasadas' => (int) (
            $financeiro['atrasadas'] ?? 0
        ),

        'mes_atual' => $mesAtual,

        'ano_atual' => $agora->format('Y')
    ];
}
