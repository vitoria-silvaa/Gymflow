<?php
// app/models/Filial.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';
$erroModel = '';
$company_id = $_SESSION['company_id'] ?? 0;

/* 1. LISTAR FILIAIS */
if ($operacao === 'listar') {
    $statusFiltro = $statusFiltro ?? '';

    $sql = "SELECT * FROM filiais WHERE company_id = ?";
    $params = [$company_id];

    if ($statusFiltro === 'Ativa') {
        $sql .= " AND ativo = 1";
    } elseif ($statusFiltro === 'Inativa') {
        $sql .= " AND ativo = 0";
    }

    $sql .= " ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $filiais = $stmt->fetchAll();
}

/* 2. BUSCAR FILIAL POR ID */ elseif ($operacao === 'buscar') {
    $id = (int) ($id ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM filiais WHERE id = :id AND company_id = :company_id");
    $stmt->execute([':id' => $id, ':company_id' => $company_id]);
    $filial = $stmt->fetch() ?: null;
}

/* 3. CADASTRAR FILIAL */ elseif ($operacao === 'cadastrar') {
    $companyId = (int) ($dados['company_id'] ?? $company_id);
    $cnpj = trim($dados['cnpj'] ?? '');

    try {
        // Valida duplicidade de CNPJ
        $stmt = $pdo->prepare("SELECT id FROM filiais WHERE company_id = :company_id AND cnpj = :cnpj");
        $stmt->execute([':company_id' => $companyId, ':cnpj' => $cnpj]);
        if ($stmt->fetch()) {
            throw new Exception('Este CNPJ já está cadastrado para esta rede.');
        }

        $stmt = $pdo->prepare("
            INSERT INTO filiais (company_id, nome, cnpj, telefone, endereco, numero, complemento, email, responsavel, ativo) 
            VALUES (:company_id, :nome, :cnpj, :telefone, :endereco, :numero, :complemento, :email, :responsavel, :ativo)
        ");
        $stmt->execute([
            ':company_id'  => $companyId,
            ':nome'        => trim($dados['nome'] ?? ''),
            ':cnpj'        => $cnpj,
            ':telefone'    => trim($dados['telefone'] ?? ''),
            ':endereco'    => trim($dados['endereco'] ?? ''),
            ':numero'      => trim($dados['numero'] ?? ''),
            ':complemento' => trim($dados['complemento'] ?? ''),
            ':email'       => trim($dados['email'] ?? ''),
            ':responsavel' => trim($dados['responsavel'] ?? ''),
            ':ativo'       => 1
        ]);
    } catch (Throwable $e) {
        $erroModel = $e->getMessage();
    }
}

/* 4. ATUALIZAR FILIAL */ elseif ($operacao === 'atualizar') {
    $id = (int) ($id ?? 0);
    $companyId = (int) ($dados['company_id'] ?? $company_id);
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
            SET nome = :nome, cnpj = :cnpj, telefone = :telefone, endereco = :endereco, numero = :numero, complemento = :complemento, email = :email, responsavel = :responsavel 
            WHERE id = :id AND company_id = :company_id
        ");
        $stmt->execute([
            ':nome'        => trim($dados['nome'] ?? ''),
            ':cnpj'        => $cnpj,
            ':telefone'    => trim($dados['telefone'] ?? ''),
            ':endereco'    => trim($dados['endereco'] ?? ''),
            ':numero'      => trim($dados['numero'] ?? ''),
            ':complemento' => trim($dados['complemento'] ?? ''),
            ':email'       => trim($dados['email'] ?? ''),
            ':responsavel' => trim($dados['responsavel'] ?? ''),
            ':id'          => $id,
            ':company_id'  => $companyId
        ]);
    } catch (Throwable $e) {
        $erroModel = $e->getMessage();
    }
}

/* 5. ATIVAR / INATIVAR FILIAL */ elseif ($operacao === 'alternar_status') {
    $id = (int) ($id ?? 0);

    try {
        $stmt = $pdo->prepare("
            UPDATE filiais
            SET ativo = CASE
                WHEN ativo = 1 THEN 0
                ELSE 1
            END
            WHERE id = :id AND company_id = :company_id
        ");

        $stmt->execute([
            ':id'         => $id,
            ':company_id' => $company_id
        ]);
    } catch (Throwable $e) {
        $erroModel = $e->getMessage();
    }
}
