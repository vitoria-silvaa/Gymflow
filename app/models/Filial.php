<?php
// app/models/Filial.php

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

/* LISTAR FILIAIS */
if ($operacao === 'listar') {
    if (!empty($statusFiltro)) {
        $ativoVal = ($statusFiltro === 'Ativa') ? 1 : 0;
        $sql = "SELECT * FROM filiais WHERE ativo = :ativo ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':ativo' => $ativoVal]);
    } else {
        $sql = "SELECT * FROM filiais ORDER BY id DESC";
        $stmt = $pdo->query($sql);
    }
    $filiais = $stmt->fetchAll();
}

/* BUSCAR FILIAL POR ID */
elseif ($operacao === 'buscar') {
    $stmt = $pdo->prepare("SELECT * FROM filiais WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $filial = $stmt->fetch();
}

/* CADASTRAR FILIAL */
elseif ($operacao === 'cadastrar') {
    $erroModel = '';
    try {
        $sql = "INSERT INTO filiais (company_id, nome, cnpj, telefone, responsavel, ativo) 
                VALUES (:company_id, :nome, :cnpj, :telefone, :responsavel, :ativo)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':company_id'  => $dados['company_id'],
            ':nome'        => $dados['nome'],
            ':cnpj'        => $dados['cnpj'],
            ':telefone'    => $dados['telefone'],
            ':responsavel' => $dados['responsavel'],
            ':ativo'       => $dados['ativo']
        ]);
    } catch (Throwable $e) {
        $erroModel = 'Erro ao cadastrar filial: ' . $e->getMessage();
    }
}

/* ATUALIZAR FILIAL */
elseif ($operacao === 'atualizar') {
    $erroModel = '';
    try {
        $sql = "UPDATE filiais 
                SET nome = :nome, 
                    cnpj = :cnpj, 
                    telefone = :telefone, 
                    responsavel = :responsavel 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome'        => $dados['nome'],
            ':cnpj'        => $dados['cnpj'],
            ':telefone'    => $dados['telefone'],
            ':responsavel' => $dados['responsavel'],
            ':id'          => $id
        ]);
    } catch (Throwable $e) {
        $erroModel = 'Erro ao atualizar filial: ' . $e->getMessage();
    }
}

/* VERIFICAR CNPJ */
elseif ($operacao === 'verificar_cnpj') {
    $sql = "SELECT id FROM filiais WHERE company_id = :company_id AND cnpj = :cnpj";
    $parametros = [
        ':company_id' => $companyId,
        ':cnpj'       => $cnpj
    ];
    if (!empty($ignorarId)) {
        $sql .= " AND id != :id";
        $parametros[':id'] = $ignorarId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $cnpjExiste = (bool)$stmt->fetch();
}
