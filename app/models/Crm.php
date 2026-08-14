<?php
// app/models/Crm.php

require_once __DIR__ . '/Database.php';

/** @var PDO $pdo */
/** @var string $operacao */
/** @var array<string, mixed> $dados */
/** @var int $id */
/** @var string $status */

$operacao = $operacao ?? '';
$erroModel = '';

/* LISTAR Leads */
if ($operacao === 'listar') {

    $leads = [];
    try {
        $sql = "
            SELECT l.*, f.nome AS nome_filial
            FROM leads l
            LEFT JOIN filiais f ON f.id = l.filial_id
            ORDER BY l.id DESC
        ";
        $stmt = $pdo->query($sql);
        $leads = $stmt->fetchAll();
    } catch (Throwable $e) {
        $erroModel = 'Erro ao buscar leads: ' . $e->getMessage();
    }
}
/* LISTAR FILIAIS */
elseif ($operacao === 'listar_filiais') {

    $filiais = [];
    try {
        $stmt = $pdo->query("
            SELECT id, nome
            FROM filiais
            WHERE ativo = TRUE
            ORDER BY nome
        ");
        $filiais = $stmt->fetchAll();
    } catch (Throwable $e) {
        $erroModel = 'Erro ao buscar filiais: ' . $e->getMessage();
    }
}
/* CADASTRAR Lead */
elseif ($operacao === 'cadastrar') {

    try {

        $stmt = $pdo->prepare("
            INSERT INTO leads (
                filial_id,
                nome,
                telefone,
                objetivo,
                campanha
            ) VALUES (
                :filial_id,
                :nome,
                :telefone,
                :objetivo,
                :campanha
            )
        ");

        $stmt->execute([
            ':filial_id' => $dados['filial_id'],
            ':nome'      => $dados['nome'],
            ':telefone'  => $dados['telefone'],
            ':objetivo'  => $dados['objetivo'] ?: null,
            ':campanha'  => $dados['campanha'] ?: null
        ]);
    } catch (Throwable $e) {

        $erroModel = 'Erro ao cadastrar o lead: ' . $e->getMessage();
    }
}
/* ATUALIZAR STATUS DO LEAD */
elseif ($operacao === 'atualizar_status') {

    try {

        $stmt = $pdo->prepare("
            UPDATE leads
            SET status = :status
            WHERE id = :id
        ");

        $stmt->execute([
            ':status' => $status,
            ':id'     => $id
        ]);
    } catch (Throwable $e) {

        $erroModel = 'Erro ao atualizar o status do lead: ' . $e->getMessage();
    }
}
