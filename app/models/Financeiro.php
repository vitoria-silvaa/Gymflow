<?php
// app/models/Financeiro.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';
$erroModel = '';

$company_id = (int) ($_SESSION['company_id'] ?? 0);
$usuario_id = (int) ($_SESSION['usuario_id'] ?? 0);
$roleFinanceiro = $_SESSION['usuario_role'] ?? '';

$executarFinanceiro = static function (
    string $sql,
    array $params = []
) use ($pdo): PDOStatement {

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt;
};

try {

    // Faturas e recibos do Portal do Aluno.

    if (in_array(
        $operacao,
        ['listar_faturas_aluno', 'buscar_recibo_aluno'],
        true
    )) {

        $todas_contas = [];
        $recibo = null;

        $aluno_id = (int) ($_SESSION['aluno_id'] ?? 0);

        if (
            $roleFinanceiro !== 'Aluno' ||
            $company_id <= 0 ||
            $usuario_id <= 0 ||
            $aluno_id <= 0
        ) {
            return;
        }

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

            INNER JOIN matriculas m
                ON m.id = c.matricula_id
                AND m.aluno_id = a.id

            INNER JOIN filiais fa
                ON fa.id = a.filial_id

            INNER JOIN filiais f
                ON f.id = COALESCE(
                    m.filial_id,
                    a.filial_id
                )
                AND f.company_id = fa.company_id

            INNER JOIN planos p
                ON p.id = m.plano_id
                AND p.company_id = f.company_id

            INNER JOIN users u
                ON u.aluno_id = a.id
                AND u.company_id = f.company_id

            WHERE u.id = ?
              AND u.role = 'Aluno'
              AND a.id = ?
              AND f.company_id = ?
        ";

        $params = [
            $usuario_id,
            $aluno_id,
            $company_id
        ];

        if ($operacao === 'buscar_recibo_aluno') {

            $sql .= "
                AND c.id = ?
                AND c.status = 'Pago'
                LIMIT 1
            ";

            $params[] = (int) ($conta_id ?? 0);

            $recibo = $executarFinanceiro(
                $sql,
                $params
            )->fetch(PDO::FETCH_ASSOC) ?: null;

        } else {

            $todas_contas = $executarFinanceiro(
                $sql . ' ORDER BY c.vencimento, c.id',
                $params
            )->fetchAll(PDO::FETCH_ASSOC);
        }

        return;
    }

    // Financeiro administrativo.

    $contas = [];
    $filiais = [];

    $totais = [
        'aberto' => 0,
        'dinheiro' => 0,
        'digital' => 0,
        'outros' => 0
    ];

    $totalRecebido = 0;

    if (
        $operacao !== 'listar_contas' ||
        $roleFinanceiro !== 'Admin' ||
        $company_id <= 0 ||
        $usuario_id <= 0
    ) {
        $erroModel = 'Acesso não autorizado.';
        return;
    }

    $autorizado = $executarFinanceiro(
        "SELECT id
         FROM users
         WHERE id = ?
           AND company_id = ?
           AND role = 'Admin'",
        [$usuario_id, $company_id]
    )->fetchColumn();

    if (!$autorizado) {
        $erroModel = 'Acesso não autorizado.';
        return;
    }

    $filiais = $executarFinanceiro(
        'SELECT id, nome
         FROM filiais
         WHERE company_id = ?
         ORDER BY nome',
        [$company_id]
    )->fetchAll(PDO::FETCH_ASSOC);

    if (!($filtroValido ?? true)) {
        return;
    }

    $filtro = $filtro ?? 'nenhum';
    $filial_id = (int) ($filial_id ?? 0);
    $busca = $busca ?? '';

    if (
        $filtro === 'filial' &&
        $filial_id > 0 &&
        !in_array(
            $filial_id,
            array_map(
                'intval',
                array_column($filiais, 'id')
            ),
            true
        )
    ) {
        $erroModel = 'Filial não autorizada.';
        return;
    }

    // Classificação dos recebimentos.

    $forma = "
        LOWER(TRIM(COALESCE(
            NULLIF(c.forma_pagamento, ''),
            pg.metodo,
            ''
        )))
    ";

    $grupo = "
        CASE

            WHEN c.status <> 'Pago'
                THEN 'aberto'

            WHEN $forma = 'dinheiro'
                THEN 'dinheiro'

            WHEN $forma IN (
                'pix',
                'credito',
                'crédito',
                'debito',
                'débito',
                'cartao de credito',
                'cartão de crédito',
                'cartao de debito',
                'cartão de débito',
                'cartao',
                'cartão'
            )
                THEN 'digital'

            ELSE 'outros'

        END
    ";

    // Consulta principal.
    // Cada cobrança é contabilizada uma única vez.

    $sql = "
        SELECT
            c.id,
            c.aluno_id,
            c.matricula_id,
            c.vencimento,
            c.valor,
            c.status,
            c.pago_em,

            COALESCE(
                NULLIF(c.forma_pagamento, ''),
                pg.metodo
            ) AS forma_pagamento,

            a.nome AS aluno_nome,
            a.cpf,

            f.nome AS filial_nome,
            p.nome AS plano_nome,

            m.origem,

            u.name AS recebido_nome,

            pg.referencia,
            pg.confirmado_em,

            $grupo AS grupo

        FROM contas c

        INNER JOIN alunos a
            ON a.id = c.aluno_id

        INNER JOIN matriculas m
            ON m.id = c.matricula_id
            AND m.aluno_id = a.id

        INNER JOIN filiais fa
            ON fa.id = a.filial_id

        INNER JOIN filiais f
            ON f.id = COALESCE(
                m.filial_id,
                a.filial_id
            )
            AND f.company_id = fa.company_id

        INNER JOIN planos p
            ON p.id = m.plano_id
            AND p.company_id = f.company_id

        LEFT JOIN pagamentos pg
            ON pg.conta_id = c.id
            AND pg.matricula_id = m.id
            AND pg.status = 'aprovado'

        LEFT JOIN users u
            ON u.id = pg.recebido_por
            AND u.company_id = f.company_id

        WHERE f.company_id = ?

          AND c.status IN (
              'Aberto',
              'Vencido',
              'Atrasado',
              'Pago'
          )
    ";

    $params = [$company_id];

    // Aplicar exclusivamente o filtro selecionado.

    switch ($filtro) {

        case 'nome':

            if ($busca !== '') {

                $sql .= ' AND LOCATE(?, a.nome) > 0';

                $params[] = $busca;
            }

            break;

        case 'cpf':

            if ($busca !== '') {

                $cpf = preg_replace('/\D/', '', $busca);

                $sql .= "
                    AND REPLACE(
                        REPLACE(
                            REPLACE(a.cpf, '.', ''),
                            '-', ''
                        ),
                        ' ', ''
                    ) = ?
                ";

                $params[] = $cpf;
            }

            break;

        case 'matricula':

            if ($busca !== '') {

                $sql .= ' AND c.matricula_id = ?';

                $params[] = (int) ltrim($busca, '#');
            }

            break;

        case 'filial':

            if ($filial_id > 0) {

                $sql .= ' AND f.id = ?';

                $params[] = $filial_id;
            }

            break;

        case 'periodo':

            $dataReferencia = "
                CASE
                    WHEN c.status = 'Pago'
                        THEN c.pago_em
                    ELSE c.vencimento
                END
            ";

            if (!empty($inicio)) {

                $sql .= " AND $dataReferencia >= ?";

                $params[] = $inicio;
            }

            if (!empty($fim)) {

                $sql .= "
                    AND $dataReferencia <
                        DATE_ADD(?, INTERVAL 1 DAY)
                ";

                $params[] = $fim;
            }

            break;
    }

    // Resumo financeiro dos registros filtrados.

    $resumo = $executarFinanceiro(
        "SELECT
            grupo,
            COALESCE(SUM(valor), 0) AS total

         FROM ($sql) AS financeiro

         GROUP BY grupo",
        $params
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resumo as $registro) {

        $totais[$registro['grupo']] =
            $registro['total'];
    }

    $totalRecebido =
        (float) $totais['dinheiro'] +
        (float) $totais['digital'] +
        (float) $totais['outros'];

    // Listagem da aba atual.

    $params[] = $aba ?? 'aberto';

    $contas = $executarFinanceiro(
        "SELECT *
         FROM ($sql) AS financeiro

         WHERE grupo = ?

         ORDER BY

            CASE
                WHEN status = 'Pago' THEN pago_em
                ELSE vencimento
            END DESC,

            id DESC

         LIMIT 500",
        $params
    )->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {

    error_log(
        '[GymFlow][Financeiro] ' .
        get_class($e) .
        ' | código ' . $e->getCode()
    );

    $erroModel =
        'Não foi possível consultar os dados financeiros.';

    $contas = [];
    $filiais = $filiais ?? [];

    $todas_contas = [];
    $recibo = null;

    $totais = [
        'aberto' => 0,
        'dinheiro' => 0,
        'digital' => 0,
        'outros' => 0
    ];

    $totalRecebido = 0;
}