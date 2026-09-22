<?php
// app/models/Treino.php

require_once __DIR__ . '/Database.php';

/** @var PDO $pdo */
/** @var string $operacao */
/** @var int $aluno_id */
/** @var int $ficha_id */
/** @var array $itens */
/** @var array $objetivo */
/** @var array $professor_id */

$operacao = $operacao ?? '';

/* LISTAR FICHAS DE UM ALUNO */
if ($operacao === 'listar_fichas_aluno') {
    $stmt_fichas = $pdo->prepare(
        "SELECT ft.id, ft.objetivo, ft.versao, ft.criada_em, u.name AS nome_professor
         FROM fichas_treino ft
         JOIN users u ON ft.professor_id = u.id
         WHERE ft.aluno_id = :aluno_id
         ORDER BY ft.criada_em DESC"
    );
    $stmt_fichas->execute([':aluno_id' => $aluno_id]);
    $fichas = $stmt_fichas->fetchAll();
}

/* BUSCAR ITENS DA FICHA */ elseif ($operacao === 'buscar_itens_ficha') {

    $stmt_itens = $pdo->prepare("
        SELECT
            fi.ordem,
            fi.series,
            fi.repeticoes,
            fi.carga,
            fi.intervalo,
            e.nome AS nome_exercicio,
            e.grupo
        FROM ficha_itens fi

        JOIN exercicios e
            ON fi.exercicio_id = e.id

        JOIN fichas_treino ft
            ON ft.id = fi.ficha_id

        WHERE fi.ficha_id = :ficha_id
          AND ft.aluno_id = :aluno_id

        ORDER BY fi.ordem ASC
    ");

    $stmt_itens->execute([
        ':ficha_id' => $ficha_id,
        ':aluno_id' => $aluno_id
    ]);

    $itens = $stmt_itens->fetchAll();
}

/* SALVAR NOVA FICHA DE TREINO */ elseif ($operacao === 'salvar_ficha') {

    try {

        $pdo->beginTransaction();

        // Calcula a próxima versão da ficha para este aluno
        $stmt_versao = $pdo->prepare("
            SELECT COALESCE(MAX(versao), 0) + 1
            FROM fichas_treino
            WHERE aluno_id = :aluno_id
        ");

        $stmt_versao->execute([
            ':aluno_id' => $aluno_id
        ]);

        $versao = (int) $stmt_versao->fetchColumn();

        // Cria a ficha
        $stmt_ficha = $pdo->prepare("
            INSERT INTO fichas_treino
                (aluno_id, professor_id, objetivo, criada_em, versao)
            VALUES
                (:aluno_id, :professor_id, :objetivo, NOW(), :versao)
        ");

        $stmt_ficha->execute([
            ':aluno_id' => $aluno_id,
            ':professor_id' => $professor_id,
            ':objetivo' => $objetivo,
            ':versao' => $versao
        ]);

        $ficha_id = $pdo->lastInsertId();

        // Insere os exercícios da ficha
        $stmt_item = $pdo->prepare("
            INSERT INTO ficha_itens
                (ficha_id, exercicio_id, ordem, series, repeticoes, carga, intervalo)
            VALUES
                (:ficha_id, :exercicio_id, :ordem, :series, :repeticoes, :carga, :intervalo)
        ");

        foreach ($itens as $ordem => $item) {

            $carga = $item['carga'] !== ''
                ? $item['carga']
                : null;

            $intervalo = $item['intervalo'] !== ''
                ? $item['intervalo']
                : null;

            $stmt_item->execute([
                ':ficha_id' => $ficha_id,
                ':exercicio_id' => (int) $item['exercicio_id'],
                ':ordem' => $ordem + 1,
                ':series' => (int) $item['series'],
                ':repeticoes' => (int) $item['repeticoes'],
                ':carga' => $carga,
                ':intervalo' => $intervalo
            ]);
        }

        $pdo->commit();

        return $ficha_id;
    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
