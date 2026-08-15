<?php
// app/models/Filial.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';
$erroModel = '';

/* 1. LISTAR FILIAIS */
if ($operacao === 'listar') {
    $statusFiltro = $statusFiltro ?? '';

    if ($statusFiltro === 'Ativa') {
        $stmt = $pdo->prepare("SELECT * FROM filiais WHERE ativo = 1 ORDER BY id DESC");
        $stmt->execute();
    } elseif ($statusFiltro === 'Inativa') {
        $stmt = $pdo->prepare("SELECT * FROM filiais WHERE ativo = 0 ORDER BY id DESC");
        $stmt->execute();
    } else {
        $stmt = $pdo->query("SELECT * FROM filiais ORDER BY id DESC");
    }

    $filiais = $stmt->fetchAll();
}

/* 2. BUSCAR FILIAL POR ID */
elseif ($operacao === 'buscar') {
    $id = (int) ($id ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM filiais WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $filial = $stmt->fetch() ?: null;
}

/* 3. CADASTRAR FILIAL */
elseif ($operacao === 'cadastrar') {
    $companyId = (int) ($dados['company_id'] ?? 1);
    $cnpj = trim($dados['cnpj'] ?? '');

    try {
        // Valida duplicidade de CNPJ
        $stmt = $pdo->prepare("SELECT id FROM filiais WHERE company_id = :company_id AND cnpj = :cnpj");
        $stmt->execute([':company_id' => $companyId, ':cnpj' => $cnpj]);
        if ($stmt->fetch()) {
            throw new Exception('Este CNPJ já está cadastrado para esta rede.');
        }

        $stmt = $pdo->prepare("
            INSERT INTO filiais (company_id, nome, cnpj, telefone, responsavel, ativo) 
            VALUES (:company_id, :nome, :cnpj, :telefone, :responsavel, :ativo)
        ");
        $stmt->execute([
            ':company_id'  => $companyId,
            ':nome'        => trim($dados['nome'] ?? ''),
            ':cnpj'        => $cnpj,
            ':telefone'    => trim($dados['telefone'] ?? ''),
            ':responsavel' => trim($dados['responsavel'] ?? ''),
            ':ativo'       => 1
        ]);
    } catch (Throwable $e) {
        $erroModel = $e->getMessage();
    }
}

/* 4. ATUALIZAR FILIAL */
elseif ($operacao === 'atualizar') {
    $id = (int) ($id ?? 0);
    $companyId = (int) ($dados['company_id'] ?? 1);
    $cnpj = trim($dados['cnpj'] ?? '');

    try {
        // Valida duplicidade de CNPJ ignorando a própria filial
        $stmt = $pdo->prepare("SELECT id FROM filiais WHERE company_id = :company_id AND cnpj = :cnpj AND id != :id");
        $stmt->execute([':company_id' => $companyId, ':cnpj' => $cnpj, ':id' => $id]);
        if ($stmt->fetch()) {
            throw new Exception('Este CNPJ já pertence a outra filial.');
        }

        $stmt = $pdo->prepare("
            UPDATE filiais 
            SET nome = :nome, cnpj = :cnpj, telefone = :telefone, responsavel = :responsavel 
            WHERE id = :id
        ");
        $stmt->execute([
            ':nome'        => trim($dados['nome'] ?? ''),
            ':cnpj'        => $cnpj,
            ':telefone'    => trim($dados['telefone'] ?? ''),
            ':responsavel' => trim($dados['responsavel'] ?? ''),
            ':id'          => $id
        ]);
    } catch (Throwable $e) {
        $erroModel = $e->getMessage();
    }
}
