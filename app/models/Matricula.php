<?php
// app/models/Matricula.php

require_once __DIR__ . '/Database.php';

$operacao  = $operacao ?? '';
$erroModel = '';
$dados     = $dados ?? [];

/* 1. BUSCAR PLANO POR ID */
if ($operacao === 'buscar_plano') {
    $plano_id = (int) ($plano_id ?? $id ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM planos WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $plano_id]);
    $plano = $stmt->fetch() ?: null;
}

/* 2. LISTAR TODOS OS PLANOS ATIVOS */
elseif ($operacao === 'listar_planos') {
    $stmt = $pdo->query("SELECT * FROM planos ORDER BY valor ASC");
    $planos = $stmt->fetchAll();
}

/* 3. LISTAR FILIAIS ATIVAS */
elseif ($operacao === 'listar_filiais') {
    $stmt = $pdo->query("SELECT * FROM filiais WHERE ativo = 1 ORDER BY nome ASC");
    $filiais = $stmt->fetchAll();
}

/* 4. BUSCAR FILIAL POR ID */
elseif ($operacao === 'buscar_filial') {
    $filial_id = (int) ($filial_id ?? $id ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM filiais WHERE id = :id AND ativo = 1 LIMIT 1");
    $stmt->execute([':id' => $filial_id]);
    $filial = $stmt->fetch() ?: null;
}

/* 5. VALIDAR SE CPF OU EMAIL JÁ EXISTEM NO SISTEMA */
elseif ($operacao === 'validar_dados_cadastrais') {
    $cpf   = trim($dados['cpf'] ?? $cpf ?? '');
    $email = trim($dados['email'] ?? $email ?? '');

    try {
        if ($cpf !== '') {
            $stmt = $pdo->prepare("SELECT id FROM alunos WHERE cpf = :cpf LIMIT 1");
            $stmt->execute([':cpf' => $cpf]);
            if ($stmt->fetch()) {
                throw new Exception("Este CPF já está cadastrado no sistema.");
            }
        }

        if ($email !== '') {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                throw new Exception("Este e-mail já está sendo utilizado.");
            }
        }
    } catch (Throwable $e) {
        $erroModel = $e->getMessage();
    }
}

/* 6. CONCRETIZAR MATRÍCULA ONLINE (TRANSAÇÃO ATÔMICA) */
elseif ($operacao === 'concretizar_matricula') {
    $dadosAluno     = $dados['aluno'] ?? [];
    $dadosPlano     = $dados['plano'] ?? [];
    $dadosPagamento = $dados['pagamento'] ?? [];

    try {
        $pdo->beginTransaction();

        // 1. Valida novamente duplicidades por segurança concorrencial
        $stmt = $pdo->prepare("SELECT id FROM alunos WHERE cpf = :cpf LIMIT 1");
        $stmt->execute([':cpf' => $dadosAluno['cpf']]);
        if ($stmt->fetch()) {
            throw new Exception("O CPF informado já está cadastrado.");
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $dadosAluno['email']]);
        if ($stmt->fetch()) {
            throw new Exception("O e-mail informado já está em uso.");
        }

        // 2. Insere o Aluno na tabela 'alunos'
        $stmtAluno = $pdo->prepare("
            INSERT INTO alunos (filial_id, nome, cpf, rg, sexo, nascimento, email, telefone, endereco, status)
            VALUES (:filial_id, :nome, :cpf, :rg, :sexo, :nascimento, :email, :telefone, :endereco, 'Ativo')
        ");
        $stmtAluno->execute([
            ':filial_id'  => (int) $dadosAluno['filial_id'],
            ':nome'       => trim($dadosAluno['nome']),
            ':cpf'        => trim($dadosAluno['cpf']),
            ':rg'         => !empty($dadosAluno['rg']) ? trim($dadosAluno['rg']) : null,
            ':sexo'       => trim($dadosAluno['sexo']),
            ':nascimento' => trim($dadosAluno['nascimento']),
            ':email'      => trim($dadosAluno['email']),
            ':telefone'   => trim($dadosAluno['telefone']),
            ':endereco'   => !empty($dadosAluno['endereco']) ? trim($dadosAluno['endereco']) : null
        ]);
        $novoAlunoId = (int) $pdo->lastInsertId();

        // 3. Cria o usuário com role 'Aluno' na tabela 'users' para acesso ao portal
        $stmtUser = $pdo->prepare("
            INSERT INTO users (name, email, password, role, aluno_id)
            VALUES (:name, :email, :password, 'Aluno', :aluno_id)
        ");
        $stmtUser->execute([
            ':name'     => trim($dadosAluno['nome']),
            ':email'    => trim($dadosAluno['email']),
            ':password' => password_hash($dadosAluno['senha'], PASSWORD_DEFAULT),
            ':aluno_id' => $novoAlunoId
        ]);

        // 4. Calcula as datas da matrícula com base na duração do plano
        $duracao = $dadosPlano['duracao'] ?? '1 Mês';
        $meses = match ($duracao) {
            '1 Mês'   => 1,
            '3 Meses' => 3,
            '6 Meses' => 6,
            '1 Ano'   => 12,
            default   => 1
        };

        $dataInicio = date('Y-m-d');
        $dataFim    = date('Y-m-d', strtotime("+{$meses} months"));
        $valorPlano = (float) $dadosPlano['valor'];

        $pagamentoAprovado = ($dadosPagamento['status'] ?? '') === 'aprovado';
        $matriculaAtiva    = $pagamentoAprovado ? 1 : 0;

        // 5. Insere a Matrícula
        $stmtMatricula = $pdo->prepare("
            INSERT INTO matriculas (aluno_id, plano_id, inicio, fim, valor, desconto, ativa)
            VALUES (:aluno_id, :plano_id, :inicio, :fim, :valor, 0.00, :ativa)
        ");
        $stmtMatricula->execute([
            ':aluno_id' => $novoAlunoId,
            ':plano_id' => (int) $dadosPlano['id'],
            ':inicio'   => $dataInicio,
            ':fim'      => $dataFim,
            ':valor'    => $valorPlano,
            ':ativa'    => $matriculaAtiva
        ]);
        $novaMatriculaId = (int) $pdo->lastInsertId();

        // 6. Gera as parcelas em 'contas' (Módulo Financeiro)
        $valorParcela = floor(($valorPlano / $meses) * 100) / 100;
        $stmtConta = $pdo->prepare("
            INSERT INTO contas (aluno_id, matricula_id, vencimento, valor, status, forma_pagamento, pago_em)
            VALUES (:aluno_id, :matricula_id, :vencimento, :valor, :status, :forma_pagamento, :pago_em)
        ");

        $formaPagamentoNome = ($dadosPagamento['metodo'] === 'pix') ? 'PIX' : 'Cartão de Crédito';

        for ($i = 0; $i < $meses; $i++) {
            $valorAtual = ($i === $meses - 1) ? ($valorPlano - ($valorParcela * ($meses - 1))) : $valorParcela;
            $vencimento = date('Y-m-d', strtotime("+{$i} months"));

            // A 1ª parcela reflete o pagamento online atual
            if ($i === 0 && $pagamentoAprovado) {
                $statusConta    = 'Pago';
                $formaConta     = $formaPagamentoNome;
                $pagoEmConta    = date('Y-m-d H:i:s');
            } else {
                $statusConta    = 'Aberto';
                $formaConta     = null;
                $pagoEmConta    = null;
            }

            $stmtConta->execute([
                ':aluno_id'        => $novoAlunoId,
                ':matricula_id'    => $novaMatriculaId,
                ':vencimento'      => $vencimento,
                ':valor'           => $valorAtual,
                ':status'          => $statusConta,
                ':forma_pagamento' => $formaConta,
                ':pago_em'         => $pagoEmConta
            ]);
        }

        // 7. Registra na tabela 'pagamentos'
        $stmtPagamento = $pdo->prepare("
            INSERT INTO pagamentos (matricula_id, mercado_pago_id, valor, metodo, status)
            VALUES (:matricula_id, :mercado_pago_id, :valor, :metodo, :status)
        ");
        $stmtPagamento->execute([
            ':matricula_id'    => $novaMatriculaId,
            ':mercado_pago_id' => $dadosPagamento['mercado_pago_id'] ?? null,
            ':valor'           => $valorPlano,
            ':metodo'          => $dadosPagamento['metodo'] ?? 'pix',
            ':status'          => $dadosPagamento['status'] ?? 'pendente'
        ]);

        $pdo->commit();

        $matriculaConcretizada = [
            'aluno_id'        => $novoAlunoId,
            'matricula_id'    => $novaMatriculaId,
            'status'          => $dadosPagamento['status'] ?? 'pendente',
            'aluno_nome'      => $dadosAluno['nome'],
            'aluno_email'     => $dadosAluno['email'],
            'plano_nome'      => $dadosPlano['nome'],
            'plano_valor'     => $valorPlano,
            'filial_id'       => $dadosAluno['filial_id'],
            'metodo'          => $dadosPagamento['metodo'] ?? 'pix',
            'mercado_pago_id' => $dadosPagamento['mercado_pago_id'] ?? ''
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erroModel = $e->getMessage();
    }
}
