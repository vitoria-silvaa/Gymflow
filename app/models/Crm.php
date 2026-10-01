<?php
// app/models/Crm.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';
$erroModel = '';
$company_id = $_SESSION['company_id'] ?? 0;

try {
    switch ($operacao) {
        case 'listar':
            // Filtra leads pelas filiais da empresa logada
            $leads = $pdo->prepare("
                SELECT l.*, f.nome AS nome_filial 
                FROM leads l 
                LEFT JOIN filiais f ON f.id = l.filial_id 
                WHERE f.company_id = ?
                ORDER BY l.id DESC
            ");
            $leads->execute([$company_id]);
            $leads = $leads->fetchAll();
            break;

        case 'buscar':
            $id = (int) ($id ?? 0);
            $stmt = $pdo->prepare("
                SELECT l.* FROM leads l
                LEFT JOIN filiais f ON f.id = l.filial_id
                WHERE l.id = :id AND f.company_id = :company_id
            ");
            $stmt->execute([':id' => $id, ':company_id' => $company_id]);
            $lead = $stmt->fetch() ?: null;
            break;

        case 'listar_filiais':
            $stmt = $pdo->prepare("SELECT id, nome FROM filiais WHERE ativo = TRUE AND company_id = ? ORDER BY nome");
            $stmt->execute([$company_id]);
            $filiais = $stmt->fetchAll();
            break;

        case 'cadastrar':
            $stmt = $pdo->prepare("
                INSERT INTO leads (filial_id, nome, telefone, email, objetivo, campanha, status) 
                VALUES (:filial_id, :nome, :telefone, :email, :objetivo, :campanha, :status)
            ");
            $stmt->execute([
                ':filial_id' => (int) ($dados['filial_id'] ?? 0),
                ':nome'      => trim($dados['nome'] ?? ''),
                ':telefone'  => trim($dados['telefone'] ?? ''),
                ':email'     => !empty($dados['email']) ? trim($dados['email']) : null,
                ':objetivo'  => !empty($dados['objetivo']) ? trim($dados['objetivo']) : null,
                ':campanha'  => !empty($dados['campanha']) ? trim($dados['campanha']) : null,
                ':status'    => $dados['status'] ?? 'Novo'
            ]);
            break;

        case 'atualizar':
            $id = (int) ($id ?? 0);
            $stmt = $pdo->prepare("
                UPDATE leads 
                SET filial_id = :filial_id, nome = :nome, telefone = :telefone, email = :email,
                    objetivo = :objetivo, campanha = :campanha, status = :status 
                WHERE id = :id AND filial_id IN (SELECT id FROM filiais WHERE company_id = :company_id)
            ");
            $stmt->execute([
                ':filial_id'  => (int) ($dados['filial_id'] ?? 0),
                ':nome'       => trim($dados['nome'] ?? ''),
                ':telefone'   => trim($dados['telefone'] ?? ''),
                ':email'      => !empty($dados['email']) ? trim($dados['email']) : null,
                ':objetivo'   => !empty($dados['objetivo']) ? trim($dados['objetivo']) : null,
                ':campanha'   => !empty($dados['campanha']) ? trim($dados['campanha']) : null,
                ':status'     => $dados['status'] ?? 'Novo',
                ':id'         => $id,
                ':company_id' => $company_id
            ]);
            break;

        case 'atualizar_status':
            $id = (int) ($id ?? 0);
            $stmt = $pdo->prepare("
                UPDATE leads SET status = :status 
                WHERE id = :id AND filial_id IN (SELECT id FROM filiais WHERE company_id = :company_id)
            ");
            $stmt->execute([':status' => $status ?? 'Novo', ':id' => $id, ':company_id' => $company_id]);
            break;

        case 'excluir':
            $id = (int) ($id ?? 0);
            $stmt = $pdo->prepare("
                DELETE FROM leads WHERE id = :id 
                AND filial_id IN (SELECT id FROM filiais WHERE company_id = :company_id)
            ");
            $stmt->execute([':id' => $id, ':company_id' => $company_id]);
            break;
    }
} catch (Throwable $e) {
    $erroModel = $e->getMessage();
}
