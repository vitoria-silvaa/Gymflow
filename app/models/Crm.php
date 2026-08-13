<?php
// app/models/Crm.php

require_once __DIR__ . '/Database.php';

/** @var PDO $pdo */
/** @var string $operacao */
/** @var array<string, mixed> $dados */
/** @var int $id */
/** @var string $statusFiltro */
/** @var string $cnpj */
/** @var int $companyId */
/** @var int $ignorarId */

$operacao = $operacao ?? '';


/* LISTAR Leads */
if ($operacao === 'listar') {

    $sql = "SELECT nome,objetivo,campanha FROM leads ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    $leads = $stmt->fetchAll();
}
