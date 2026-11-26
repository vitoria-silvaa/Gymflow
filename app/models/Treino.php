<?php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';

$company_id = (int) ($_SESSION['company_id'] ?? 0);
$usuario_id = (int) ($_SESSION['usuario_id'] ?? 0);
$usuario_role = $_SESSION['usuario_role'] ?? '';

$aluno_id = (int) ($aluno_id ?? 0);

$acessoPermitido =
    $company_id > 0 &&
    $usuario_id > 0 &&
    $aluno_id > 0;

if ($usuario_role === 'Aluno') {
    $acessoPermitido =
        $acessoPermitido &&
        (int) ($_SESSION['aluno_id'] ?? 0) === $aluno_id;

} elseif (!in_array(
    $usuario_role,
    ['Admin', 'Professor'],
    true
)) {
    $acessoPermitido = false;
}

$escopoFilial = '';
$paramsFilial = [];

if ($usuario_role === 'Professor') {

    $escopoFilial = "
        AND EXISTS (
            SELECT 1
            FROM user_filiais uf
            WHERE uf.user_id = :usuario_filial
              AND uf.filial_id = a.filial_id
        )
    ";

    $paramsFilial[':usuario_filial'] = $usuario_id;
}

// LISTAR FICHAS

if ($operacao === 'listar_fichas_aluno') {

    $fichas = [];

    if ($acessoPermitido) {

        $stmt = $pdo->prepare("
            SELECT
                ft.id,
                ft.aluno_id,
                ft.professor_id,
                COALESCE(
                    NULLIF(ft.nome, ''),
                    ft.objetivo
                ) AS nome_ficha,
                ft.objetivo,
                ft.versao,
                ft.criada_em,
                u.name AS nome_professor,
                a.nome AS nome_aluno,
                f.nome AS nome_filial,
                (
                    SELECT COUNT(*)
                    FROM ficha_itens fi
                    WHERE fi.ficha_id = ft.id
                ) AS total_exercicios

            FROM fichas_treino ft

            INNER JOIN alunos a
                ON a.id = ft.aluno_id

            INNER JOIN filiais f
                ON f.id = a.filial_id

            INNER JOIN users u
                ON u.id = ft.professor_id
               AND u.company_id = f.company_id

            WHERE ft.aluno_id = :aluno_id
              AND f.company_id = :company_id

            $escopoFilial

            ORDER BY ft.versao DESC, ft.id DESC
        ");

        $stmt->execute(array_merge([
            ':aluno_id' => $aluno_id,
            ':company_id' => $company_id
        ], $paramsFilial));

        $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// BUSCAR ITENS DA FICHA

elseif ($operacao === 'buscar_itens_ficha') {

    $itens = [];

    $ficha_id = (int) ($ficha_id ?? 0);

    if ($acessoPermitido && $ficha_id > 0) {

        $stmt = $pdo->prepare("
            SELECT
                fi.id AS item_id,
                fi.ficha_id,
                fi.exercicio_id,
                fi.ordem,
                fi.series,
                fi.repeticoes,
                fi.carga,
                fi.intervalo,
                e.nome AS nome_exercicio,
                e.grupo,
                e.midia,
                e.tipo_midia

            FROM ficha_itens fi

            INNER JOIN exercicios e
                ON e.id = fi.exercicio_id

            INNER JOIN fichas_treino ft
                ON ft.id = fi.ficha_id

            INNER JOIN alunos a
                ON a.id = ft.aluno_id

            INNER JOIN filiais f
                ON f.id = a.filial_id

            INNER JOIN users u
                ON u.id = ft.professor_id
               AND u.company_id = f.company_id

            WHERE fi.ficha_id = :ficha_id
              AND ft.aluno_id = :aluno_id
              AND f.company_id = :company_id
              AND e.company_id = :empresa_exercicio

            $escopoFilial

            ORDER BY fi.ordem ASC, fi.id ASC
        ");

        $stmt->execute(array_merge([
            ':ficha_id' => $ficha_id,
            ':aluno_id' => $aluno_id,
            ':company_id' => $company_id,
            ':empresa_exercicio' => $company_id
        ], $paramsFilial));

        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// SALVAR OU EXCLUIR FICHA

elseif (in_array(
    $operacao,
    ['salvar_ficha', 'excluir_ficha'],
    true
)) {

    if (
        !$acessoPermitido ||
        !in_array(
            $usuario_role,
            ['Admin', 'Professor'],
            true
        )
    ) {
        throw new RuntimeException('Acesso não autorizado.');
    }

    $ficha_origem_id = (int) ($ficha_origem_id ?? 0);

    $editando = $ficha_origem_id > 0;

    try {

        $pdo->beginTransaction();

        // Validar aluno e filial

        $stmtAluno = $pdo->prepare("
            SELECT a.id, a.filial_id
            FROM alunos a
            INNER JOIN filiais f
                ON f.id = a.filial_id
            WHERE a.id = :aluno_id
              AND f.company_id = :company_id

            $escopoFilial

            LIMIT 1
            FOR UPDATE
        ");

        $stmtAluno->execute(array_merge([
            ':aluno_id' => $aluno_id,
            ':company_id' => $company_id
        ], $paramsFilial));

        if (!$stmtAluno->fetch(PDO::FETCH_ASSOC)) {
            throw new RuntimeException(
                'Aluno não encontrado nesta empresa ou filial.'
            );
        }

        // Validar ficha existente

        if ($editando) {

            $stmtFicha = $pdo->prepare("
                SELECT id
                FROM fichas_treino
                WHERE id = :ficha_id
                  AND aluno_id = :aluno_id
                LIMIT 1
                FOR UPDATE
            ");

            $stmtFicha->execute([
                ':ficha_id' => $ficha_origem_id,
                ':aluno_id' => $aluno_id
            ]);

            if (!$stmtFicha->fetch(PDO::FETCH_ASSOC)) {
                throw new RuntimeException(
                    'Ficha de treino não encontrada.'
                );
            }
        }

        // EXCLUIR FICHA

        if ($operacao === 'excluir_ficha') {

            if (!$editando) {
                throw new RuntimeException('Ficha inválida.');
            }

            // Preservar vínculos com registros de frequência

            $stmtUso = $pdo->prepare("
                SELECT EXISTS (
                    SELECT 1
                    FROM checkins
                    WHERE ficha_id = ?
                ) OR EXISTS (
                    SELECT 1
                    FROM treino_sessoes
                    WHERE ficha_id = ?
                )
            ");

            $stmtUso->execute([
                $ficha_origem_id,
                $ficha_origem_id
            ]);

            if ((int) $stmtUso->fetchColumn() === 1) {
                throw new RuntimeException(
                    'Esta ficha possui registros de frequência '
                    . 'e não pode ser excluída permanentemente.'
                );
            }

            $stmtExcluir = $pdo->prepare("
                DELETE FROM fichas_treino
                WHERE id = :ficha_id
                  AND aluno_id = :aluno_id
            ");

            $stmtExcluir->execute([
                ':ficha_id' => $ficha_origem_id,
                ':aluno_id' => $aluno_id
            ]);

            $pdo->commit();

            return true;
        }

        // VALIDAR DADOS DA FICHA

        $professor_id = (int) ($professor_id ?? 0);

        $nome_ficha = trim((string) ($nome_ficha ?? ''));
        $objetivo = trim((string) ($objetivo ?? ''));

        $itens = $itens ?? [];

        if (
            $professor_id <= 0 ||
            $nome_ficha === '' ||
            mb_strlen($nome_ficha) > 100 ||
            $objetivo === '' ||
            mb_strlen($objetivo) > 50 ||
            !is_array($itens) ||
            empty($itens)
        ) {
            throw new RuntimeException(
                'Preencha os campos obrigatórios '
                . 'e adicione pelo menos um exercício.'
            );
        }

        if (
            $usuario_role === 'Professor' &&
            $professor_id !== $usuario_id
        ) {
            throw new RuntimeException(
                'Você não pode salvar em nome de outro professor.'
            );
        }

        $stmtProfessor = $pdo->prepare("
            SELECT id
            FROM users
            WHERE id = :professor_id
              AND company_id = :company_id
              AND role = 'Professor'
            LIMIT 1
        ");

        $stmtProfessor->execute([
            ':professor_id' => $professor_id,
            ':company_id' => $company_id
        ]);

        if (!$stmtProfessor->fetch(PDO::FETCH_ASSOC)) {
            throw new RuntimeException(
                'Professor inválido para esta academia.'
            );
        }

        // Não alterar fichas em execução

        if ($editando) {

            $stmtSessao = $pdo->prepare("
                SELECT id
                FROM treino_sessoes
                WHERE ficha_id = :ficha_id
                  AND concluido_em IS NULL
                LIMIT 1
            ");

            $stmtSessao->execute([
                ':ficha_id' => $ficha_origem_id
            ]);

            if ($stmtSessao->fetchColumn()) {
                throw new RuntimeException(
                    'Existe um treino em andamento com esta ficha.'
                );
            }
        }

        // VALIDAR EXERCÍCIOS

        $stmtExercicio = $pdo->prepare("
            SELECT id
            FROM exercicios
            WHERE id = :exercicio_id
              AND company_id = :company_id
              AND ativo = 1
            LIMIT 1
        ");

        $itensValidados = [];

        foreach ($itens as $item) {

            if (!is_array($item)) {
                throw new RuntimeException('Exercício inválido.');
            }

            $exercicio_id = filter_var(
                $item['exercicio_id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            $series = filter_var(
                $item['series'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => [
                    'min_range' => 1,
                    'max_range' => 2147483647
                ]]
            );

            $repeticoes = is_scalar($item['repeticoes'] ?? null)
                ? trim((string) $item['repeticoes'])
                : '';

            if (
                !$exercicio_id ||
                !$series ||
                !preg_match(
                    '/^[1-9]\d*(?:\s*(?:a|-)\s*[1-9]\d*)?$/i',
                    $repeticoes
                ) ||
                strlen($repeticoes) > 30
            ) {
                throw new RuntimeException(
                    'Verifique as séries e repetições dos exercícios.'
                );
            }

            $stmtExercicio->execute([
                ':exercicio_id' => $exercicio_id,
                ':company_id' => $company_id
            ]);

            if (!$stmtExercicio->fetch(PDO::FETCH_ASSOC)) {
                throw new RuntimeException(
                    'Um dos exercícios está indisponível.'
                );
            }

            $cargaRecebida = $item['carga'] ?? '';

            if (!is_scalar($cargaRecebida)) {
                throw new RuntimeException('Carga inválida.');
            }

            $carga = trim((string) $cargaRecebida);

            if (mb_strlen($carga) > 20) {
                throw new RuntimeException(
                    'A carga deve possuir até 20 caracteres.'
                );
            }

            $intervaloRecebido = $item['intervalo'] ?? '';

            if (!is_scalar($intervaloRecebido)) {
                throw new RuntimeException('Intervalo inválido.');
            }

            $intervalo = trim((string) $intervaloRecebido);

            if ($intervalo !== '') {

                if (
                    !preg_match(
                        '/^(\d+)\s*s?$/i',
                        $intervalo,
                        $partes
                    ) ||
                    (int) $partes[1] > 1000
                ) {
                    throw new RuntimeException(
                        'O intervalo deve estar entre 0 e 1000 segundos.'
                    );
                }

                $intervalo = (int) $partes[1] . 's';
            }

            $itensValidados[] = [
                'item_id' => (int) ($item['item_id'] ?? 0),
                'exercicio_id' => $exercicio_id,
                'series' => $series,
                'repeticoes' => $repeticoes,
                'carga' => $carga === '' ? null : $carga,
                'intervalo' => $intervalo === '' ? null : $intervalo
            ];
        }

        // ATUALIZAR FICHA EXISTENTE

        if ($editando) {

            $ficha_id = $ficha_origem_id;

            $stmt = $pdo->prepare("
                UPDATE fichas_treino
                SET nome = :nome,
                    objetivo = :objetivo,
                    professor_id = :professor_id
                WHERE id = :ficha_id
                  AND aluno_id = :aluno_id
            ");

            $stmt->execute([
                ':nome' => $nome_ficha,
                ':objetivo' => $objetivo,
                ':professor_id' => $professor_id,
                ':ficha_id' => $ficha_id,
                ':aluno_id' => $aluno_id
            ]);

        } else {

            // CADASTRAR NOVA FICHA

            $stmtVersao = $pdo->prepare("
                SELECT COALESCE(MAX(versao), 0) + 1
                FROM fichas_treino
                WHERE aluno_id = :aluno_id
            ");

            $stmtVersao->execute([
                ':aluno_id' => $aluno_id
            ]);

            $versao = (int) $stmtVersao->fetchColumn();

            $stmt = $pdo->prepare("
                INSERT INTO fichas_treino (
                    aluno_id,
                    professor_id,
                    nome,
                    objetivo,
                    criada_em,
                    versao
                )
                VALUES (
                    :aluno_id,
                    :professor_id,
                    :nome,
                    :objetivo,
                    NOW(),
                    :versao
                )
            ");

            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':professor_id' => $professor_id,
                ':nome' => $nome_ficha,
                ':objetivo' => $objetivo,
                ':versao' => $versao
            ]);

            $ficha_id = (int) $pdo->lastInsertId();
        }

        // IDENTIFICAR ITENS EXISTENTES

        $stmtExistentes = $pdo->prepare("
            SELECT id, exercicio_id
            FROM ficha_itens
            WHERE ficha_id = :ficha_id
            FOR UPDATE
        ");

        $stmtExistentes->execute([
            ':ficha_id' => $ficha_id
        ]);

        $existentes = [];

        foreach (
            $stmtExistentes->fetchAll(PDO::FETCH_ASSOC)
            as $registro
        ) {
            $existentes[(int) $registro['id']] =
                (int) $registro['exercicio_id'];
        }

        $mantidos = [];
        $processados = [];

        $stmtAtualizar = $pdo->prepare("
            UPDATE ficha_itens
            SET ordem = :ordem,
                series = :series,
                repeticoes = :repeticoes,
                carga = :carga,
                intervalo = :intervalo
            WHERE id = :item_id
              AND ficha_id = :ficha_id
        ");

        $stmtInserir = $pdo->prepare("
            INSERT INTO ficha_itens (
                ficha_id,
                exercicio_id,
                ordem,
                series,
                repeticoes,
                carga,
                intervalo
            )
            VALUES (
                :ficha_id,
                :exercicio_id,
                :ordem,
                :series,
                :repeticoes,
                :carga,
                :intervalo
            )
        ");

        foreach ($itensValidados as $indice => $item) {

            $item_id = $item['item_id'];

            $dados = [
                ':ficha_id' => $ficha_id,
                ':ordem' => $indice + 1,
                ':series' => $item['series'],
                ':repeticoes' => $item['repeticoes'],
                ':carga' => $item['carga'],
                ':intervalo' => $item['intervalo']
            ];

            if ($item_id > 0) {

                if (
                    !isset($existentes[$item_id]) ||
                    isset($processados[$item_id])
                ) {
                    throw new RuntimeException(
                        'Item de treino inválido.'
                    );
                }

                $processados[$item_id] = true;
            }

            if (
                $item_id > 0 &&
                $existentes[$item_id] === $item['exercicio_id']
            ) {

                $stmtAtualizar->execute(array_merge(
                    $dados,
                    [':item_id' => $item_id]
                ));

                $mantidos[$item_id] = true;

            } else {

                // Exercício novo ou substituído

                $stmtInserir->execute(array_merge(
                    $dados,
                    [':exercicio_id' => $item['exercicio_id']]
                ));
            }
        }

        // Remover somente os itens retirados ou substituídos

        $stmtRemover = $pdo->prepare("
            DELETE FROM ficha_itens
            WHERE id = :item_id
              AND ficha_id = :ficha_id
        ");

        foreach ($existentes as $item_id => $exercicio_id) {

            if (!isset($mantidos[$item_id])) {

                $stmtRemover->execute([
                    ':item_id' => $item_id,
                    ':ficha_id' => $ficha_id
                ]);
            }
        }

        $pdo->commit();

        return $ficha_id;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}