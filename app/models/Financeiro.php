<?php
// app/models/Financeiro.php

require_once __DIR__ . '/Database.php';

/** @var PDO $pdo */
/** @var string $operacao */
/** @var int $aluno_id */

$operacao = $operacao ?? '';

/* BUSCAR FATURAS DE UM ALUNO */
if ($operacao === 'listar_faturas_aluno') {
    $stmt = $pdo->prepare(
        "SELECT id, vencimento, valor, status, forma_pagamento, pago_em
         FROM contas
         WHERE aluno_id = :aluno_id
         ORDER BY vencimento ASC"
    );
    $stmt->execute([':aluno_id' => $aluno_id]);
    $todas_contas = $stmt->fetchAll();
}
