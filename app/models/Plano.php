<?php
// app/models/Plano.php

require_once __DIR__ . '/Database.php';

/** @var PDO $pdo */
/** @var string $operacao */
/** @var array<string, mixed> $dados */
/** @var int $id */

$operacao = $operacao ?? '';

/* LISTAR PLANOS */
if ($operacao === 'listar') {
    $stmt = $pdo->query("SELECT * FROM planos ORDER BY id DESC");
    $planos = $stmt->fetchAll();
}

/* BUSCAR PLANO POR ID */
elseif ($operacao === 'buscar') {
    $stmt = $pdo->prepare("SELECT * FROM planos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $plano = $stmt->fetch();
}

/* CADASTRAR PLANO */
elseif ($operacao === 'cadastrar') {
    $erroModel = '';
    try {
        $sql = "INSERT INTO planos (company_id, nome, categoria, valor, duracao) 
                VALUES (:company_id, :nome, :categoria, :valor, :duracao)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':company_id' => $dados['company_id'],
            ':nome'       => $dados['nome'],
            ':categoria'  => $dados['categoria'],
            ':valor'      => $dados['valor'],
            ':duracao'    => $dados['duracao']
        ]);
    } catch (Throwable $e) {
        $erroModel = 'Erro ao cadastrar plano: ' . $e->getMessage();
    }
}

/* ATUALIZAR PLANO */
elseif ($operacao === 'atualizar') {
    $erroModel = '';
    try {
        $sql = "UPDATE planos SET nome = :nome, categoria = :categoria, valor = :valor, duracao = :duracao WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome'      => $dados['nome'],
            ':categoria' => $dados['categoria'],
            ':valor'     => $dados['valor'],
            ':duracao'   => $dados['duracao'],
            ':id'        => $id
        ]);
    } catch (Throwable $e) {
        $erroModel = 'Erro ao atualizar plano: ' . $e->getMessage();
    }
}
