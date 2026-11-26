<?php
// ============================================
// GYMFLOW - MODEL FINANCEIRO
// app/models/Financeiro.php
// ============================================

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';
$company_id = $_SESSION['company_id'] ?? 0;

// ============================================
// 1. LISTAR CONTAS
// ============================================

if ($operacao === 'listar_contas') {

    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.aluno_id,
            c.matricula_id,
            c.vencimento,
            c.valor,
            c.status,
            c.forma_pagamento,
            c.pago_em,
            a.nome AS aluno_nome

        FROM contas c

        INNER JOIN alunos a
            ON a.id = c.aluno_id

        INNER JOIN filiais f
            ON f.id = a.filial_id

        WHERE f.company_id = :company_id

        ORDER BY c.vencimento ASC
    ");

    $stmt->execute([
        ':company_id' => $company_id
    ]);

    $contas = $stmt->fetchAll();

    // TOTAL EM ABERTO

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(c.valor), 0) AS total

        FROM contas c

        INNER JOIN alunos a
            ON a.id = c.aluno_id

        INNER JOIN filiais f
            ON f.id = a.filial_id

        WHERE c.status != 'Pago'
          AND f.company_id = ?
    ");

    $stmt->execute([$company_id]);

    $totalAberto = $stmt->fetch()['total'];

    // TOTAL RECEBIDO

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(c.valor), 0) AS total

        FROM contas c

        INNER JOIN alunos a
            ON a.id = c.aluno_id

        INNER JOIN filiais f
            ON f.id = a.filial_id

        WHERE c.status = 'Pago'
          AND f.company_id = ?
    ");

    $stmt->execute([$company_id]);

    $totalRecebido = $stmt->fetch()['total'];
}

// ============================================
// 2. BUSCAR CONTA
// ============================================

elseif ($operacao === 'buscar_conta') {

    $conta_id = (int) ($conta_id ?? 0);

    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.aluno_id,
            c.matricula_id,
            c.vencimento,
            c.valor,
            c.status,
            c.forma_pagamento,
            c.pago_em,
            a.nome AS aluno_nome

        FROM contas c

        INNER JOIN alunos a
            ON a.id = c.aluno_id

        INNER JOIN filiais f
            ON f.id = a.filial_id

        WHERE c.id = :id
          AND f.company_id = :company_id

        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $conta_id,
        ':company_id' => $company_id
    ]);

    $conta = $stmt->fetch() ?: null;
}

// ============================================
// 3. CONFIRMAR PAGAMENTO
// ============================================

elseif ($operacao === 'baixar_pagamento') {

    $conta_id = (int) ($conta_id ?? 0);

    $forma_pagamento = trim(
        $forma_pagamento ?? ''
    );

    if (
        $conta_id > 0 &&
        $forma_pagamento !== ''
    ) {

        $stmt = $pdo->prepare("
            UPDATE contas c

            INNER JOIN alunos a
                ON a.id = c.aluno_id

            INNER JOIN filiais f
                ON f.id = a.filial_id

            SET
                c.status = 'Pago',
                c.forma_pagamento = :forma_pagamento,
                c.pago_em = CURRENT_TIMESTAMP

            WHERE c.id = :id
              AND c.status != 'Pago'
              AND f.company_id = :company_id
        ");

        $stmt->execute([
            ':forma_pagamento' => $forma_pagamento,
            ':id' => $conta_id,
            ':company_id' => $company_id
        ]);
    }
}

// ============================================
// 4. PORTAL DO ALUNO
// FATURAS E RECIBOS
// ============================================

elseif (
    in_array(
        $operacao,
        [
            'listar_faturas_aluno',
            'buscar_recibo_aluno'
        ],
        true
    )
) {

    // Utiliza exclusivamente os dados da sessão.

    $usuario_id = (int) (
        $_SESSION['usuario_id'] ?? 0
    );

    $aluno_id = (int) (
        $_SESSION['aluno_id'] ?? 0
    );

    $company_id = (int) (
        $_SESSION['company_id'] ?? 0
    );

    $todas_contas = [];

    $recibo = null;

    if (
        $usuario_id > 0 &&
        $aluno_id > 0 &&
        $company_id > 0 &&
        ($_SESSION['usuario_role'] ?? '') === 'Aluno'
    ) {

        $sql = "
            SELECT
                c.id,
                c.aluno_id,
                c.matricula_id,
                c.vencimento,
                c.valor,
                c.status,
                c.forma_pagamento,
                c.pago_em,

                a.nome AS nome_aluno,

                p.nome AS nome_plano,

                f.nome AS nome_filial,
                f.cnpj AS cnpj_filial,

                CONCAT_WS(
                    ', ',
                    NULLIF(TRIM(f.endereco), ''),
                    NULLIF(TRIM(f.numero), ''),
                    NULLIF(TRIM(f.complemento), '')
                ) AS endereco_filial

            FROM contas c

            INNER JOIN alunos a
                ON a.id = c.aluno_id

            INNER JOIN filiais f
                ON f.id = a.filial_id

            INNER JOIN users u
                ON u.aluno_id = a.id
                AND u.company_id = f.company_id

            INNER JOIN matriculas m
                ON m.id = c.matricula_id
                AND m.aluno_id = a.id

            INNER JOIN planos p
                ON p.id = m.plano_id
                AND p.company_id = f.company_id

            WHERE u.id = :usuario_id
              AND u.role = 'Aluno'
              AND a.id = :aluno_id
              AND f.company_id = :company_id
        ";

        $parametros = [

            ':usuario_id' => $usuario_id,

            ':aluno_id' => $aluno_id,

            ':company_id' => $company_id
        ];

        // ====================================
        // CONSULTAR RECIBO ESPECÍFICO
        // ====================================

        if ($operacao === 'buscar_recibo_aluno') {

            $conta_id = (int) ($conta_id ?? 0);

            $sql .= "
                AND c.id = :conta_id
                AND c.status = 'Pago'
                LIMIT 1
            ";

            $parametros[':conta_id'] = $conta_id;

            $stmt = $pdo->prepare($sql);

            $stmt->execute($parametros);

            $recibo = $stmt->fetch(
                PDO::FETCH_ASSOC
            ) ?: null;

            if (
                $recibo &&
                $recibo['status'] !== 'Pago'
            ) {

                $recibo = null;
            }
        }

        // ====================================
        // LISTAR TODAS AS FATURAS
        // ====================================

        else {

            $sql .= "
                ORDER BY c.vencimento ASC, c.id ASC
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute($parametros);

            $todas_contas = $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
        }
    }
}
