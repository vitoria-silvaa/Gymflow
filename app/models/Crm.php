<?php
// app/models/Crm.php

require_once __DIR__ . '/Database.php';

/** @var PDO $pdo */
$operacao = $operacao ?? '';
$id = (int) ($id ?? 0);
$dados = $dados ?? [];
$status = $status ?? '';
$erroModel = '';

try {
    switch ($operacao) {
        case 'listar':
            $leads = $pdo->query("SELECT l.*, f.nome AS nome_filial FROM leads l LEFT JOIN filiais f ON f.id = l.filial_id ORDER BY l.id DESC")->fetchAll();
            break;

        case 'buscar':
            $stmt = $pdo->prepare("SELECT * FROM leads WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $lead = $stmt->fetch() ?: null;
            break;

        case 'listar_filiais':
            $filiais = $pdo->query("SELECT id, nome FROM filiais WHERE ativo = TRUE ORDER BY nome")->fetchAll();
            break;

        case 'cadastrar':
            $stmt = $pdo->prepare("INSERT INTO leads (filial_id, nome, telefone, objetivo, campanha, status) VALUES (:filial_id, :nome, :telefone, :objetivo, :campanha, :status)");
            $stmt->execute([
                ':filial_id' => (int) ($dados['filial_id'] ?? 0),
                ':nome'      => trim($dados['nome'] ?? ''),
                ':telefone'  => trim($dados['telefone'] ?? ''),
                ':objetivo'  => trim($dados['objetivo'] ?? '') ?: null,
                ':campanha'  => trim($dados['campanha'] ?? '') ?: null,
                ':status'    => $dados['status'] ?? 'Novo'
            ]);
            break;

        case 'atualizar':
            $stmt = $pdo->prepare("UPDATE leads SET filial_id = :filial_id, nome = :nome, telefone = :telefone, objetivo = :objetivo, campanha = :campanha, status = :status WHERE id = :id");
            $stmt->execute([
                ':filial_id' => (int) ($dados['filial_id'] ?? 0),
                ':nome'      => trim($dados['nome'] ?? ''),
                ':telefone'  => trim($dados['telefone'] ?? ''),
                ':objetivo'  => trim($dados['objetivo'] ?? '') ?: null,
                ':campanha'  => trim($dados['campanha'] ?? '') ?: null,
                ':status'    => $dados['status'] ?? 'Novo',
                ':id'        => $id
            ]);
            break;

        case 'atualizar_status':
            $stmt = $pdo->prepare("UPDATE leads SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $status, ':id' => $id]);
            break;

        case 'excluir':
            $stmt = $pdo->prepare("DELETE FROM leads WHERE id = :id");
            $stmt->execute([':id' => $id]);
            break;
    }
} catch (Throwable $e) {
    $erroModel = $e->getMessage();
}
