<?php
// app/models/Treino.php

require_once __DIR__ . '/Database.php';

/** @var PDO $pdo */
/** @var string $operacao */
/** @var int $aluno_id */
/** @var int $ficha_id */

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

/* BUSCAR ITENS DA FICHA */
elseif ($operacao === 'buscar_itens_ficha') {
    $stmt_itens = $pdo->prepare(
        "SELECT fi.ordem, fi.series, fi.repeticoes, fi.carga, fi.intervalo,
                e.nome AS nome_exercicio, e.grupo
         FROM ficha_itens fi
         JOIN exercicios e ON fi.exercicio_id = e.id
         WHERE fi.ficha_id = :ficha_id
         ORDER BY fi.ordem ASC"
    );
    $stmt_itens->execute([':ficha_id' => $ficha_id]);
    $itens = $stmt_itens->fetchAll();
}
