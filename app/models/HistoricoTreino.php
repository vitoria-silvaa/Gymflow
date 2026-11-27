<?php
// =============================================
// GYMFLOW - MODEL DE HISTÓRICO DE TREINOS
// app/models/HistoricoTreino.php
// =============================================

function historicoAutorizar(
    PDO $pdo,
    int $empresa,
    int $aluno,
    int $usuario
): bool {

    $stmt = $pdo->prepare("
        SELECT u.id
        FROM users u
        INNER JOIN alunos a
            ON a.id = u.aluno_id
        INNER JOIN filiais f
            ON f.id = a.filial_id
        WHERE u.id = ?
          AND u.company_id = ?
          AND u.aluno_id = ?
          AND u.role = 'Aluno'
          AND f.company_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $usuario,
        $empresa,
        $aluno,
        $empresa
    ]);

    return (bool) $stmt->fetchColumn();
}

// =============================================
// FREQUÊNCIA DIÁRIA
// =============================================

function obterFrequenciaHoje(
    PDO $pdo,
    int $empresa,
    int $aluno
): array {

    $hoje = new DateTimeImmutable(
        'today',
        new DateTimeZone('America/Sao_Paulo')
    );

    $amanha = $hoje->modify('+1 day');

    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT e.exercicio_origem_id)
        FROM treino_sessao_exercicios e
        INNER JOIN treino_sessoes s
            ON s.id = e.sessao_id
        WHERE s.company_id = ?
          AND s.aluno_id = ?
          AND e.concluido_em >= ?
          AND e.concluido_em < ?
    ");

    $stmt->execute([
        $empresa,
        $aluno,
        $hoje->format('Y-m-d H:i:s'),
        $amanha->format('Y-m-d H:i:s')
    ]);

    $quantidade = (int) $stmt->fetchColumn();

    $meta = 4;

    $status = match (true) {
        $quantidade >= $meta => 'concluido',
        $quantidade > 0 => 'parcial',
        default => 'sem_treino'
    };

    return [
        'data' => $hoje->format('Y-m-d'),
        'exercicios' => $quantidade,
        'meta' => $meta,
        'percentual' => min(
            100,
            (int) round($quantidade / $meta * 100)
        ),
        'status' => $status
    ];
}

// =============================================
// REGISTRAR EXERCÍCIO CONCLUÍDO
// =============================================

function registrarExercicioHistorico(
    PDO $pdo,
    int $empresa,
    int $aluno,
    array $dados
): array {

    $fichaId = $dados['ficha_id'] ?? null;
    $itemId = $dados['item_id'] ?? null;
    $versao = $dados['versao'] ?? null;
    $token = $dados['token'] ?? null;
    $tipo = $dados['tipo'] ?? null;
    $inicioMs = $dados['inicio'] ?? null;
    $series = $dados['series'] ?? null;

    // Validação dos dados enviados.

    if (
        !is_int($fichaId) ||
        $fichaId <= 0 ||
        !is_int($itemId) ||
        $itemId <= 0 ||
        !is_int($versao) ||
        $versao <= 0 ||
        !is_string($token) ||
        !preg_match('/^[a-f0-9]{32}$/D', $token) ||
        !in_array(
            $tipo,
            ['completo', 'individual'],
            true
        ) ||
        !is_int($inicioMs) ||
        $inicioMs <= 0 ||
        !is_array($series) ||
        !array_is_list($series) ||
        !$series ||
        count($series) > 500
    ) {

        throw new InvalidArgumentException(
            'Dados do exercício inválidos.'
        );
    }

    $seriesValidadas = [];

    foreach ($series as $serie) {

        if (!is_array($serie)) {

            throw new InvalidArgumentException(
                'Série inválida.'
            );
        }

        $reps = $serie['reps'] ?? null;
        $carga = $serie['carga'] ?? '';

        if (
            !is_int($reps) ||
            $reps < 1 ||
            $reps > 999 ||
            !is_string($carga) ||
            mb_strlen($carga, 'UTF-8') > 40
        ) {

            throw new InvalidArgumentException(
                'Repetições ou carga inválidas.'
            );
        }

        $seriesValidadas[] = [
            'reps' => $reps,
            'carga' => trim($carga)
        ];
    }

    $fuso = new DateTimeZone(
        'America/Sao_Paulo'
    );

    $agora = new DateTimeImmutable(
        'now',
        $fuso
    );

    $inicioUnix = intdiv($inicioMs, 1000);

    // Limite de 24 horas para uma sessão temporária.

    if (
        $inicioUnix < $agora->getTimestamp() - 86400 ||
        $inicioUnix > $agora->getTimestamp() + 300
    ) {

        throw new InvalidArgumentException(
            'Horário de início inválido. Inicie uma nova sessão.'
        );
    }

    $inicioUnix = min(
        $inicioUnix,
        $agora->getTimestamp()
    );

    $inicio = $agora->setTimestamp(
        $inicioUnix
    );

    $agoraSql = $agora->format(
        'Y-m-d H:i:s'
    );

    // Todos os registros serão gravados juntos.
    // Caso alguma operação falhe, nada será gravado.

    $pdo->beginTransaction();

    try {

        // =====================================
        // BUSCAR FICHA AUTORIZADA
        // =====================================

        $stmt = $pdo->prepare("
            SELECT
                ft.id,
                ft.objetivo,
                ft.versao
            FROM fichas_treino ft
            INNER JOIN alunos a
                ON a.id = ft.aluno_id
            INNER JOIN filiais f
                ON f.id = a.filial_id
            INNER JOIN users u
                ON u.id = ft.professor_id
               AND u.company_id = f.company_id
            WHERE ft.id = ?
              AND ft.aluno_id = ?
              AND f.company_id = ?
            FOR UPDATE
        ");

        $stmt->execute([
            $fichaId,
            $aluno,
            $empresa
        ]);

        $ficha = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        if (!$ficha) {

            throw new DomainException(
                'Ficha indisponível para este aluno.'
            );
        }

        if ((int) $ficha['versao'] !== $versao) {

            throw new DomainException(
                'A ficha foi atualizada pelo professor. ' .
                    'Atualize a página para iniciar o treino com a nova versão.'
            );
        }

        // =====================================
        // BUSCAR PRESCRIÇÃO ORIGINAL
        // =====================================

        $stmt = $pdo->prepare("
            SELECT
                fi.id AS item_id,
                fi.exercicio_id,
                fi.ordem,
                fi.series,
                fi.repeticoes,
                fi.carga,
                fi.intervalo,
                e.nome,
                e.grupo
            FROM ficha_itens fi
            INNER JOIN exercicios e
                ON e.id = fi.exercicio_id
            WHERE fi.ficha_id = ?
              AND e.company_id = ?
            ORDER BY fi.ordem ASC, fi.id ASC
            FOR UPDATE
        ");

        $stmt->execute([
            $fichaId,
            $empresa
        ]);

        $itens = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

        $item = null;
        $ordemFicha = 0;

        foreach ($itens as $posicao => $registro) {

            if ((int) $registro['item_id'] === $itemId) {

                $item = $registro;

                $ordemFicha = $posicao + 1;

                break;
            }
        }

        if (!$item) {

            throw new DomainException(
                'O exercício não pertence à ficha selecionada.'
            );
        }

        if (
            count($seriesValidadas) !==
            (int) $item['series']
        ) {

            throw new DomainException(
                'Conclua todas as séries prescritas.'
            );
        }

        $ordem = $tipo === 'individual'
            ? 1
            : $ordemFicha;

        // =====================================
        // CRIAR OU RECUPERAR SESSÃO
        // =====================================

        $stmt = $pdo->prepare("
            INSERT INTO treino_sessoes (
                company_id,
                aluno_id,
                ficha_id,
                token_sessao,
                objetivo_ficha,
                versao_ficha,
                tipo,
                iniciado_em
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)

            ON DUPLICATE KEY UPDATE
                id = id
        ");

        $stmt->execute([
            $empresa,
            $aluno,
            $fichaId,
            $token,
            $ficha['objetivo'],
            $versao,
            $tipo,
            $inicio->format('Y-m-d H:i:s')
        ]);

        $stmt = $pdo->prepare("
            SELECT *
            FROM treino_sessoes
            WHERE company_id = ?
              AND aluno_id = ?
              AND token_sessao = ?
            FOR UPDATE
        ");

        $stmt->execute([
            $empresa,
            $aluno,
            $token
        ]);

        $sessao = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        if (
            !$sessao ||
            (int) $sessao['ficha_id'] !== $fichaId ||
            (int) $sessao['versao_ficha'] !== $versao ||
            $sessao['tipo'] !== $tipo
        ) {

            throw new DomainException(
                'Sessão incompatível com a ficha atual.'
            );
        }

        $sessaoId = (int) $sessao['id'];

        // =====================================
        // EVITAR DUPLICIDADE
        // =====================================

        $stmt = $pdo->prepare("
            SELECT
                id,
                ficha_item_id,
                concluido_em
            FROM treino_sessao_exercicios
            WHERE sessao_id = ?
              AND ordem = ?
            LIMIT 1
        ");

        $stmt->execute([
            $sessaoId,
            $ordem
        ]);

        $existente = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        if ($existente) {

            if (
                (int) $existente['ficha_item_id'] !== $itemId ||
                !$existente['concluido_em']
            ) {

                throw new DomainException(
                    'Registro anterior incompatível.'
                );
            }

            // Recupera as séries já salvas.
            // Isso permite repetir uma requisição
            // sem duplicar o exercício.

            $stmt = $pdo->prepare("
                SELECT
                    repeticoes_realizadas AS reps,
                    carga_utilizada AS carga
                FROM treino_sessao_series
                WHERE sessao_exercicio_id = ?
                ORDER BY numero_serie ASC
            ");

            $stmt->execute([
                $existente['id']
            ]);

            $registros = $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );

            $registros = array_map(
                static fn(array $r): array => [
                    'reps' => (int) $r['reps'],
                    'carga' => (string) ($r['carga'] ?? '')
                ],
                $registros
            );

            $pdo->commit();

            return [
                'sessao_id' => $sessaoId,
                'series' => $registros,
                'duplicado' => true
            ];
        }

        if ($sessao['concluido_em'] !== null) {

            throw new DomainException(
                'Esta sessão já foi concluída.'
            );
        }

        // Na execução completa, respeitar a
        // ordem dos exercícios da ficha.

        if (
            $ordem !==
            (int) $sessao['total_exercicios'] + 1
        ) {

            throw new DomainException(
                'Existe um exercício anterior ainda não registrado.'
            );
        }

        // =====================================
        // SALVAR EXERCÍCIO CONCLUÍDO
        // =====================================

        $stmt = $pdo->prepare("
            INSERT INTO treino_sessao_exercicios (
                sessao_id,
                ficha_item_id,
                exercicio_id,
                exercicio_origem_id,
                ordem,
                nome_exercicio,
                grupo_muscular,
                series_prescritas,
                repeticoes_prescritas,
                carga_prescrita,
                intervalo_prescrito,
                concluido_em
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?
            )
        ");

        $stmt->execute([
            $sessaoId,
            $itemId,
            $item['exercicio_id'],
            $item['exercicio_id'],
            $ordem,
            $item['nome'],
            $item['grupo'],
            $item['series'],
            $item['repeticoes'],
            $item['carga'],
            $item['intervalo'],
            $agoraSql
        ]);

        $sessaoExercicioId = (int)
        $pdo->lastInsertId();

        // =====================================
        // SALVAR AS SÉRIES REALIZADAS
        // =====================================

        $stmt = $pdo->prepare("
            INSERT INTO treino_sessao_series (
                sessao_exercicio_id,
                numero_serie,
                repeticoes_realizadas,
                carga_utilizada
            ) VALUES (?, ?, ?, ?)
        ");

        foreach (
            $seriesValidadas as $numero => $serie
        ) {

            $stmt->execute([
                $sessaoExercicioId,
                $numero + 1,
                $serie['reps'],
                $serie['carga'] !== ''
                    ? $serie['carga']
                    : null
            ]);
        }

        // =====================================
        // ATUALIZAR RESUMO DA SESSÃO
        // =====================================

        $totalExercicios =
            (int) $sessao['total_exercicios'] + 1;

        $totalSeries =
            (int) $sessao['total_series'] +
            count($seriesValidadas);

        $sessaoCompleta =
            $tipo === 'individual' ||
            $totalExercicios === count($itens);

        $inicioReal = new DateTimeImmutable(
            $sessao['iniciado_em'],
            $fuso
        );

        $duracao = max(
            0,
            $agora->getTimestamp() -
                $inicioReal->getTimestamp()
        );

        $stmt = $pdo->prepare("
            UPDATE treino_sessoes
            SET
                total_exercicios = ?,
                total_series = ?,
                duracao_segundos = ?,
                concluido_em = ?
            WHERE id = ?
              AND company_id = ?
              AND aluno_id = ?
        ");

        $stmt->execute([
            $totalExercicios,
            $totalSeries,
            $duracao,
            $sessaoCompleta ? $agoraSql : null,
            $sessaoId,
            $empresa,
            $aluno
        ]);

        $pdo->commit();

        return [
            'sessao_id' => $sessaoId,
            'series' => $seriesValidadas,
            'duplicado' => false
        ];
    } catch (Throwable $erro) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();
        }

        throw $erro;
    }
}

// =============================================
// FREQUÊNCIA MENSAL
// =============================================

function obterFrequenciaMensal(
    PDO $pdo,
    int $empresa,
    int $aluno,
    DateTimeImmutable $inicio,
    DateTimeImmutable $fim
): array {
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
        $fim->format('Y-m-d H:i:s'),
    ]);

    $dias = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $dias[$r['dia']][] = [
            'nome'      => $r['nome'],
            'execucoes' => (int) $r['execucoes'],
        ];
    }

    return $dias;
}
