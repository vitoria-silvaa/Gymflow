<?php
// app/models/Aluno.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';

$erroModel = '';

$company_id = (int) ($_SESSION['company_id'] ?? 0);

$usuario_id = (int) ($_SESSION['usuario_id'] ?? 0);

$role_logado = $_SESSION['usuario_role'] ?? '';

$executarAluno = static function (
    string $sql,
    array $params = []
) use ($pdo): PDOStatement {

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt;
};

$mensagemSeguraAluno = '';

$transacaoAluno = false;

$impedirAluno = static function (
    string $mensagem
) use (&$mensagemSeguraAluno): void {

    $mensagemSeguraAluno = $mensagem;

    throw new DomainException(
        'Operação cadastral não autorizada.'
    );
};

try {

    // Validar identidade e perfil.

    if (
        $company_id <= 0 ||
        $usuario_id <= 0 ||
        !in_array(
            $role_logado,
            ['Admin', 'Recepcao', 'Professor', 'Aluno'],
            true
        )
    ) {
        $impedirAluno('Acesso não autorizado.');
    }

    $usuarioLogado = $executarAluno(
        "SELECT id, aluno_id
         FROM users
         WHERE id = ?
           AND company_id = ?
           AND role = ?
         LIMIT 1",
        [
            $usuario_id,
            $company_id,
            $role_logado
        ]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$usuarioLogado) {
        $impedirAluno('Sua sessão não está autorizada.');
    }

    // Operações exclusivas do Portal do Aluno.

    if (in_array(
        $operacao,
        [
            'dados_portal_aluno',
            'listar_contratos',
            'buscar_contrato_impressao'
        ],
        true
    )) {

        $aluno = null;
        $matricula = false;
        $frequencia = 0;
        $faturas_abertas = 0;
        $contratos = [];
        $contrato = false;

        $alunoPortalId = (int) (
            $_SESSION['aluno_id'] ?? 0
        );

        if (
            $role_logado !== 'Aluno' ||
            $alunoPortalId <= 0 ||
            $alunoPortalId !==
                (int) $usuarioLogado['aluno_id']
        ) {
            return;
        }

        $aluno = $executarAluno(
            "SELECT a.nome
             FROM alunos a
             INNER JOIN filiais f
                ON f.id = a.filial_id
             WHERE a.id = ?
               AND f.company_id = ?",
            [
                $alunoPortalId,
                $company_id
            ]
        )->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$aluno) {
            return;
        }

        if ($operacao === 'dados_portal_aluno') {

            $matricula = $executarAluno(
                "SELECT
                    m.fim,
                    p.nome AS nome_plano

                 FROM matriculas m

                 INNER JOIN planos p
                    ON p.id = m.plano_id

                 WHERE m.aluno_id = ?
                   AND p.company_id = ?
                   AND m.ativa = 1
                   AND m.inicio <= CURRENT_DATE()
                   AND m.fim >= CURRENT_DATE()
                   AND (
                       m.status_matricula IS NULL
                       OR m.status_matricula = 'ativa'
                   )

                 ORDER BY m.id DESC
                 LIMIT 1",
                [
                    $alunoPortalId,
                    $company_id
                ]
            )->fetch(PDO::FETCH_ASSOC) ?: false;

            $frequencia = (int) $executarAluno(
                "SELECT COUNT(*)
                 FROM checkins
                 WHERE aluno_id = ?
                   AND MONTH(data) = MONTH(CURDATE())
                   AND YEAR(data) = YEAR(CURDATE())",
                [$alunoPortalId]
            )->fetchColumn();

            $faturas_abertas = (int) $executarAluno(
                "SELECT COUNT(*)
                 FROM contas c
                 INNER JOIN matriculas m
                    ON m.id = c.matricula_id
                    AND m.aluno_id = c.aluno_id
                 INNER JOIN planos p
                    ON p.id = m.plano_id
                 WHERE c.aluno_id = ?
                   AND p.company_id = ?
                   AND c.status IN (
                       'Aberto',
                       'Vencido',
                       'Atrasado'
                   )",
                [
                    $alunoPortalId,
                    $company_id
                ]
            )->fetchColumn();

        } elseif ($operacao === 'listar_contratos') {

            $contratos = $executarAluno(
                "SELECT
                    m.id,
                    m.inicio,
                    m.fim,
                    m.valor,
                    m.desconto,
                    m.ativa,
                    m.status_matricula,

                    p.nome AS nome_plano,
                    p.categoria,
                    p.duracao

                 FROM matriculas m

                 INNER JOIN planos p
                    ON p.id = m.plano_id

                 WHERE m.aluno_id = ?
                   AND p.company_id = ?

                 ORDER BY m.inicio DESC, m.id DESC",
                [
                    $alunoPortalId,
                    $company_id
                ]
            )->fetchAll(PDO::FETCH_ASSOC);

        } else {

            $contratoId = (int) ($contrato_id ?? 0);

            $contrato = $executarAluno(
                "SELECT
                    m.*,

                    p.nome AS nome_plano,
                    p.categoria,
                    p.duracao,

                    a.nome AS nome_aluno,
                    a.cpf AS cpf_aluno,
                    a.telefone AS tel_aluno,

                    f.nome AS nome_filial

                 FROM matriculas m

                 INNER JOIN planos p
                    ON p.id = m.plano_id

                 INNER JOIN alunos a
                    ON a.id = m.aluno_id

                 INNER JOIN filiais f
                    ON f.id = COALESCE(
                        m.filial_id,
                        a.filial_id
                    )

                 WHERE m.aluno_id = ?
                   AND m.id = ?
                   AND p.company_id = ?
                   AND f.company_id = ?

                 LIMIT 1",
                [
                    $alunoPortalId,
                    $contratoId,
                    $company_id,
                    $company_id
                ]
            )->fetch(PDO::FETCH_ASSOC);
        }

        return;
    }

    // Operações internas.

    if (!in_array(
        $role_logado,
        ['Admin', 'Recepcao', 'Professor'],
        true
    )) {
        $impedirAluno('Acesso não autorizado.');
    }

    // Restringir alunos por empresa e funcionário.

    $escopoAluno = "
        FROM alunos a

        INNER JOIN filiais f
            ON f.id = a.filial_id

        WHERE f.company_id = ?
    ";

    $paramsAluno = [$company_id];

    if ($role_logado === 'Recepcao') {

        $escopoAluno .= "
            AND EXISTS (
                SELECT 1

                FROM user_filiais uf

                WHERE uf.filial_id = a.filial_id
                  AND uf.user_id = ?
            )
        ";

        $paramsAluno[] = $usuario_id;

    } elseif ($role_logado === 'Professor') {

        $escopoAluno .= ' AND a.professor_id = ?';

        $paramsAluno[] = $usuario_id;
    }

    // Restringir contratos e cobranças à unidade autorizada.

    $escopoMatricula = "
        FROM matriculas m

        INNER JOIN alunos a
            ON a.id = m.aluno_id

        INNER JOIN filiais fa
            ON fa.id = a.filial_id

        INNER JOIN filiais fm
            ON fm.id = COALESCE(
                m.filial_id,
                a.filial_id
            )
            AND fm.company_id = fa.company_id

        INNER JOIN planos p
            ON p.id = m.plano_id
            AND p.company_id = fa.company_id

        WHERE fa.company_id = ?
    ";

    $paramsMatricula = [$company_id];

    if ($role_logado === 'Recepcao') {

        $escopoMatricula .= "
            AND EXISTS (
                SELECT 1
                FROM user_filiais uf
                WHERE uf.filial_id = fa.id
                  AND uf.user_id = ?
            )

            AND EXISTS (
                SELECT 1
                FROM user_filiais uf
                WHERE uf.filial_id = fm.id
                  AND uf.user_id = ?
            )
        ";

        $paramsMatricula[] = $usuario_id;
        $paramsMatricula[] = $usuario_id;

    } elseif ($role_logado === 'Professor') {

        $escopoMatricula .= ' AND a.professor_id = ?';

        $paramsMatricula[] = $usuario_id;
    }

    // Listar apenas filiais autorizadas.

    if ($operacao === 'listar_filiais') {

        $sql = "
            SELECT f.id, f.nome, f.ativo

            FROM filiais f

            WHERE f.company_id = ?
        ";

        $params = [$company_id];

        if (in_array(
            $role_logado,
            ['Recepcao', 'Professor'],
            true
        )) {

            $sql .= "
                AND EXISTS (
                    SELECT 1
                    FROM user_filiais uf
                    WHERE uf.filial_id = f.id
                      AND uf.user_id = ?
                )
            ";

            $params[] = $usuario_id;
        }

        $filiais = $executarAluno(
            $sql . ' ORDER BY f.nome',
            $params
        )->fetchAll(PDO::FETCH_ASSOC);

        return;
    }

    // Listagem de alunos.

    if ($operacao === 'listar') {

        $alunos = [];

        $cpf = is_string($cpf ?? null)
            ? trim($cpf)
            : '';

        $status = is_string($status ?? null)
            ? $status
            : '';

        $filial_id_filter = (int) (
            $filial_id_filter ?? 0
        );

        $pendente = "
            EXISTS (
                SELECT 1

                FROM matriculas mp

                INNER JOIN planos pp
                    ON pp.id = mp.plano_id
                    AND pp.company_id = f.company_id

                WHERE mp.aluno_id = a.id
                  AND mp.status_matricula = 'pendente'
                  AND mp.ativa = 0
                  AND COALESCE(
                      mp.filial_id,
                      a.filial_id
                  ) = a.filial_id
            )
        ";

        $sql = "
            SELECT
                a.*,
                f.nome AS nome_filial,

                $pendente AS matricula_pendente
        " . $escopoAluno;

        $params = $paramsAluno;

        if ($cpf !== '') {

            if (
                !preg_match('/^[0-9. -]+$/', $cpf) ||
                strlen($cpf) > 14
            ) {

                $erroModel = 'Informe um CPF válido.';

                return;
            }

            $cpfNumeros = preg_replace(
                '/\D/',
                '',
                $cpf
            );

            if ($cpfNumeros === '') {

                $erroModel = 'Informe os números do CPF.';

                return;
            }

            $sql .= "
                AND REPLACE(
                    REPLACE(
                        REPLACE(a.cpf, '.', ''),
                        '-', ''
                    ),
                    ' ', ''
                ) LIKE ?
            ";

            $params[] = '%' . $cpfNumeros . '%';
        }

        if ($status === 'Pendente') {

            $sql .= " AND $pendente";

        } elseif (in_array(
            $status,
            ['Ativo', 'Inativo'],
            true
        )) {

            $sql .= ' AND a.status = ?';

            $params[] = $status;
        }

        if ($filial_id_filter > 0) {

            $sql .= ' AND a.filial_id = ?';

            $params[] = $filial_id_filter;
        }

        $alunos = $executarAluno(
            $sql . ' ORDER BY a.nome, a.id',
            $params
        )->fetchAll(PDO::FETCH_ASSOC);

        return;
    }

    // Buscar ou visualizar aluno.

    if (in_array(
        $operacao,
        ['buscar', 'visualizar'],
        true
    )) {

        $id = (int) ($id ?? 0);

        $aluno = null;
        $matricula = false;
        $contas = [];
        $planos = [];
        $contratos = [];

        $aluno = $executarAluno(
            "SELECT
                a.*,
                f.nome AS nome_filial" .
            $escopoAluno .
            ' AND a.id = ? LIMIT 1',
            array_merge(
                $paramsAluno,
                [$id]
            )
        )->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$aluno || $operacao === 'buscar') {
            return;
        }

        // Dados financeiros individuais apenas para
        // Admin e Recepção.

        if ($role_logado === 'Professor') {
            return;
        }

        $contratos = $executarAluno(
            "SELECT
                m.*,

                p.nome AS nome_plano,
                p.duracao,

                fm.nome AS nome_filial
            " .
            $escopoMatricula .
            ' AND a.id = ?
              ORDER BY m.id DESC',
            array_merge(
                $paramsMatricula,
                [$id]
            )
        )->fetchAll(PDO::FETCH_ASSOC);

        foreach ($contratos as $registro) {

            if (
                (int) $registro['ativa'] === 1 ||
                $registro['status_matricula'] === 'pendente'
            ) {

                $matricula = $registro;

                break;
            }
        }

        $contas = $executarAluno(
            "SELECT
                c.*,
                p.nome AS nome_plano,
                fm.nome AS nome_filial

             FROM contas c

             INNER JOIN matriculas m
                ON m.id = c.matricula_id
                AND m.aluno_id = c.aluno_id

             INNER JOIN alunos a
                ON a.id = m.aluno_id

             INNER JOIN filiais fa
                ON fa.id = a.filial_id

             INNER JOIN filiais fm
                ON fm.id = COALESCE(
                    m.filial_id,
                    a.filial_id
                )
                AND fm.company_id = fa.company_id

             INNER JOIN planos p
                ON p.id = m.plano_id
                AND p.company_id = fa.company_id

             WHERE a.id = ?
               AND fa.company_id = ?" .

            ($role_logado === 'Recepcao'
                ? "
                    AND EXISTS (
                        SELECT 1
                        FROM user_filiais uf
                        WHERE uf.filial_id = fa.id
                          AND uf.user_id = ?
                    )

                    AND EXISTS (
                        SELECT 1
                        FROM user_filiais uf
                        WHERE uf.filial_id = fm.id
                          AND uf.user_id = ?
                    )
                  "
                : '') .

            ' ORDER BY c.vencimento DESC, c.id DESC',

            $role_logado === 'Recepcao'
                ? [
                    $id,
                    $company_id,
                    $usuario_id,
                    $usuario_id
                ]
                : [
                    $id,
                    $company_id
                ]
        )->fetchAll(PDO::FETCH_ASSOC);

        return;
    }

    // Atualização cadastral com campos permitidos.

    if ($operacao === 'atualizar') {

        if (!in_array(
            $role_logado,
            ['Admin', 'Recepcao'],
            true
        )) {
            $impedirAluno(
                'Você não pode editar este cadastro.'
            );
        }

        $id = (int) ($id ?? 0);

        $dados = $dados ?? [];

        $normalizar = static fn($v) =>
            is_string($v) ? trim($v) : '';

        $nome = $normalizar($dados['nome'] ?? '');
        $rg = $normalizar($dados['rg'] ?? '');

        $telefone = $normalizar(
            $dados['telefone'] ?? ''
        );

        $endereco = $normalizar(
            $dados['endereco'] ?? ''
        );

        $tamanho = static function (string $v): int {

            $n = preg_match_all('/./us', $v);

            return $n === false
                ? PHP_INT_MAX
                : $n;
        };

        if (
            $nome === '' ||
            $tamanho($nome) > 100
        ) {
            $impedirAluno(
                'Informe o nome completo com até 100 caracteres.'
            );
        }

        if (
            $tamanho($rg) > 20 ||
            (
                $rg !== '' &&
                !preg_match(
                    '/^[0-9A-Za-z.\/ -]+$/',
                    $rg
                )
            )
        ) {
            $impedirAluno(
                'Informe um RG válido com até 20 caracteres.'
            );
        }

        $numero = preg_replace(
            '/\D/',
            '',
            $telefone
        );

        if (
            !preg_match(
                '/^[0-9() .-]+$/',
                $telefone
            ) ||
            !in_array(
                strlen($numero),
                [10, 11],
                true
            )
        ) {
            $impedirAluno(
                'Informe um telefone válido com DDD.'
            );
        }

        $telefone =
            '(' . substr($numero, 0, 2) . ') ' .
            substr($numero, 2, -4) . '-' .
            substr($numero, -4);

        if ($tamanho($endereco) > 1000) {

            $impedirAluno(
                'O endereço deve ter até 1.000 caracteres.'
            );
        }

        $pdo->beginTransaction();

        $transacaoAluno = true;

        $autorizado = $executarAluno(
            'SELECT u.id FROM users u
             WHERE u.id = ?
               AND u.company_id = ?
               AND u.role = ?
             FOR UPDATE',
            [
                $usuario_id,
                $company_id,
                $role_logado
            ]
        )->fetchColumn();

        if (!$autorizado) {

            $impedirAluno(
                'Sua sessão não está autorizada.'
            );
        }

        $registro = $executarAluno(
            'SELECT a.id, a.filial_id' .
            $escopoAluno .
            ' AND a.id = ? FOR UPDATE',
            array_merge(
                $paramsAluno,
                [$id]
            )
        )->fetch(PDO::FETCH_ASSOC);

        if (!$registro) {

            $impedirAluno(
                'Aluno não encontrado ou não autorizado.'
            );
        }

        if ($role_logado === 'Recepcao') {

            $vinculo = $executarAluno(
                'SELECT filial_id
                 FROM user_filiais
                 WHERE user_id = ?
                   AND filial_id = ?
                 FOR UPDATE',
                [
                    $usuario_id,
                    $registro['filial_id']
                ]
            )->fetchColumn();

            if (!$vinculo) {

                $impedirAluno(
                    'Você não possui acesso a esta filial.'
                );
            }
        }

        $executarAluno(
            "UPDATE alunos
             SET
                nome = ?,
                rg = ?,
                telefone = ?,
                endereco = ?
             WHERE id = ?",
            [
                $nome,
                $rg !== '' ? $rg : null,
                $telefone,
                $endereco !== '' ? $endereco : null,
                $id
            ]
        );

        $executarAluno(
            "UPDATE users
             SET
                name = ?,
                telefone = ?,
                endereco = ?
             WHERE aluno_id = ?
               AND company_id = ?
               AND role = 'Aluno'",
            [
                $nome,
                $telefone,
                $endereco,
                $id,
                $company_id
            ]
        );

        $pdo->commit();

        $transacaoAluno = false;

        return;
    }

    // Impedir o uso das operações financeiras antigas.

    if (in_array(
        $operacao,
        ['cadastrar', 'matricular', 'pagar'],
        true
    )) {

        $pagamentoRealizado = false;

        $impedirAluno(
            'Utilize o fluxo autorizado de matrícula e recebimentos.'
        );
    }

    $impedirAluno('Operação não autorizada.');

} catch (Throwable $e) {

    if (
        $transacaoAluno &&
        $pdo->inTransaction()
    ) {
        $pdo->rollBack();
    }

    if ($mensagemSeguraAluno === '') {

        error_log(
            '[GymFlow][Aluno] ' .
            get_class($e) .
            ' | código ' .
            $e->getCode() .
            ' | linha ' .
            $e->getLine()
        );
    }

    $erroModel = $mensagemSeguraAluno ?:
        'Não foi possível concluir a operação. Tente novamente.';

    if ($operacao !== 'atualizar') {

        $aluno = null;
        $alunos = [];
        $filiais = [];
        $contas = [];
        $contratos = [];
        $contrato = false;
        $matricula = false;
        $frequencia = 0;
        $faturas_abertas = 0;
    }
}