<?php
// app/models/Pagamento.php

require_once __DIR__ . '/Database.php';

$operacao  = $operacao ?? '';
$erroModel = '';

/* 1. BUSCAR PAGAMENTO POR MATRÍCULA */
if ($operacao === 'buscar_por_matricula') {
    $matricula_id = (int) ($matricula_id ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM pagamentos WHERE matricula_id = :matricula_id ORDER BY id DESC LIMIT 1");
    $stmt->execute([':matricula_id' => $matricula_id]);
    $pagamento = $stmt->fetch() ?: null;
}

/* 2. ATUALIZAR STATUS DO PAGAMENTO (WEBHOOK / CONFIRMAÇÃO) */
elseif ($operacao === 'atualizar_status') {
    $mercado_pago_id = trim($mercado_pago_id ?? '');
    $novo_status     = trim($novo_status ?? 'pendente');

    try {
        $stmt = $pdo->prepare("SELECT * FROM pagamentos WHERE mercado_pago_id = :mp_id LIMIT 1");
        $stmt->execute([':mp_id' => $mercado_pago_id]);
        $pagamento = $stmt->fetch();

        if ($pagamento) {
            $pdo->beginTransaction();

            $stmtUpdate = $pdo->prepare("UPDATE pagamentos SET status = :status WHERE id = :id");
            $stmtUpdate->execute([
                ':status' => $novo_status,
                ':id'     => $pagamento['id']
            ]);

            // Se aprovado, ativa a matrícula e liquida a 1ª fatura em contas
            if ($novo_status === 'aprovado') {
                $stmtAtiva = $pdo->prepare("UPDATE matriculas SET ativa = TRUE WHERE id = :matricula_id");
                $stmtAtiva->execute([':matricula_id' => $pagamento['matricula_id']]);

                $forma = ($pagamento['metodo'] === 'pix') ? 'PIX' : 'Cartão de Crédito';
                $stmtConta = $pdo->prepare("
                    UPDATE contas 
                    SET status = 'Pago', forma_pagamento = :forma, pago_em = NOW() 
                    WHERE matricula_id = :matricula_id AND status = 'Aberto' 
                    ORDER BY vencimento ASC LIMIT 1
                ");
                $stmtConta->execute([
                    ':forma'        => $forma,
                    ':matricula_id' => $pagamento['matricula_id']
                ]);
            }

            $pdo->commit();
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erroModel = $e->getMessage();
    }
}
