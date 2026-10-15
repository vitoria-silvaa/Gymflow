
<?php
// app/models/Dashboard.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';

if ($operacao === 'metricas_executivas') {

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

    // =================================================
    // 1. FILTRO MULTI-TENANT
    // =================================================

    // Nunca usamos um company_id recebido por GET.
    // A empresa é identificada pela sessão.

    $filtro = "f.company_id = :company_id";

    $parametros = [
        ':company_id' => $company_id
    ];

    if ($role_logado === 'Recepcao') {

        // Somente as filiais vinculadas ao usuário.
        // EXISTS evita duplicar alunos nas contagens.

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

        // Apenas alunos atribuídos ao professor.
        $filtro .= " AND a.professor_id = :professor_id";

        $parametros[':professor_id'] = $id_logado;
    }

    // =================================================
    // 2. FUNÇÃO PARA CONSULTAS DE CONTAGEM
    // =================================================

    $consultarTotal = function (
        string $sql,
        array $params
    ) use ($pdo): int {

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    };

    // =================================================
    // 3. ALUNOS ATIVOS
    // =================================================

    $totalAlunosAtivos = $consultarTotal("
        SELECT COUNT(a.id)
        FROM alunos a
        INNER JOIN filiais f
            ON f.id = a.filial_id
        WHERE a.status = 'Ativo'
          AND $filtro
    ", $parametros);

    // =================================================
    // 4. ALUNOS INATIVOS
    // =================================================

    $totalAlunosInativos = $consultarTotal("
        SELECT COUNT(a.id)
        FROM alunos a
        INNER JOIN filiais f
            ON f.id = a.filial_id
        WHERE a.status = 'Inativo'
          AND $filtro
    ", $parametros);

    // =================================================
    // 5. MATRÍCULAS ATIVAS
    // =================================================

    $totalMatriculasAtivas = $consultarTotal("
        SELECT COUNT(m.id)
        FROM matriculas m
        INNER JOIN alunos a
            ON a.id = m.aluno_id
        INNER JOIN filiais f
            ON f.id = a.filial_id
        WHERE m.ativa = 1
          AND $filtro
    ", $parametros);

    // =================================================
    // 6. INDICADORES FINANCEIROS - APENAS ADMIN
    // =================================================

    // Inicializa variáveis para manter compatibilidade.
    $receitaMensalEsperada = 0.0;
    $contasVencidasNaoPagas = 0;
    $totalContasVencidas = 0;

    if ($role_logado === 'Admin') {

        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(c.valor), 0)
            FROM contas c
            INNER JOIN alunos a
                ON a.id = c.aluno_id
            INNER JOIN filiais f
                ON f.id = a.filial_id
            WHERE c.vencimento >=
                DATE_FORMAT(CURDATE(), '%Y-%m-01')
              AND c.vencimento <
                DATE_FORMAT(
                    CURDATE() + INTERVAL 1 MONTH,
                    '%Y-%m-01'
                )
              AND f.company_id = :company_id
        ");

        $stmt->execute([
            ':company_id' => $company_id
        ]);

        $receitaMensalEsperada =
            (float) $stmt->fetchColumn();

        // Contas vencidas e não pagas
        $contasVencidasNaoPagas = $consultarTotal("
            SELECT COUNT(c.id)
            FROM contas c
            INNER JOIN alunos a
                ON a.id = c.aluno_id
            INNER JOIN filiais f
                ON f.id = a.filial_id
            WHERE c.vencimento < CURDATE()
              AND c.status != 'Pago'
              AND f.company_id = :company_id
        ", [
            ':company_id' => $company_id
        ]);

        // Total de contas vencidas
        $totalContasVencidas = $consultarTotal("
            SELECT COUNT(c.id)
            FROM contas c
            INNER JOIN alunos a
                ON a.id = c.aluno_id
            INNER JOIN filiais f
                ON f.id = a.filial_id
            WHERE c.vencimento < CURDATE()
              AND f.company_id = :company_id
        ", [
            ':company_id' => $company_id
        ]);
    }

    // =================================================
    // 7. NOVOS ALUNOS POR MÊS
    // =================================================

    $stmt = $pdo->prepare("
        SELECT
            MONTH(a.criado_em) AS mes,
            COUNT(a.id) AS total
        FROM alunos a
        INNER JOIN filiais f
            ON f.id = a.filial_id
        WHERE a.criado_em >=
            DATE_FORMAT(CURDATE(), '%Y-01-01')
          AND a.criado_em <
            DATE_FORMAT(
                CURDATE() + INTERVAL 1 YEAR,
                '%Y-01-01'
            )
          AND $filtro
        GROUP BY MONTH(a.criado_em)
        ORDER BY mes ASC
    ");

    $stmt->execute($parametros);

    $novosAlunosPorMes = $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}
