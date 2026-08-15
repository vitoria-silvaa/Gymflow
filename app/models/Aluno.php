<?php
// app/models/Aluno.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';
$erroModel = '';
$dados = $dados ?? [];

/* 1. LISTAR ALUNOS */
if ($operacao === 'listar') {
    $cpf = trim($cpf ?? '');
    $status = trim($status ?? '');

    $sql = "SELECT a.*, f.nome AS nome_filial FROM alunos a INNER JOIN filiais f ON f.id = a.filial_id WHERE 1 = 1";
    $parametros = [];

    if ($cpf !== '') {
        $sql .= " AND a.cpf LIKE :cpf";
        $parametros[':cpf'] = "%$cpf%";
    }
    if ($status !== '') {
        $sql .= " AND a.status = :status";
        $parametros[':status'] = $status;
    }

    $sql .= " ORDER BY a.nome";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $alunos = $stmt->fetchAll();
}

/* 2. LISTAR FILIAIS ATIVAS */
elseif ($operacao === 'listar_filiais') {
    $filiais = $pdo->query("SELECT id, nome FROM filiais WHERE ativo = TRUE ORDER BY nome")->fetchAll();
}

/* 3. BUSCAR ALUNO POR ID */
elseif ($operacao === 'buscar') {
    $id = (int) ($id ?? 0);
    $stmt = $pdo->prepare("SELECT a.*, f.nome AS nome_filial FROM alunos a INNER JOIN filiais f ON f.id = a.filial_id WHERE a.id = :id");
    $stmt->execute([':id' => $id]);
    $aluno = $stmt->fetch() ?: null;
}

/* 4. CADASTRAR ALUNO */
elseif ($operacao === 'cadastrar') {
    try {
        // Valida CPF duplicado
        $stmt = $pdo->prepare("SELECT id FROM alunos WHERE cpf = :cpf");
        $stmt->execute([':cpf' => $dados['cpf']]);
        if ($stmt->fetch()) {
            throw new Exception('Este CPF já está cadastrado.');
        }

        // Valida E-mail duplicado em usuários
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $dados['email']]);
        if ($stmt->fetch()) {
            throw new Exception('Este e-mail já está sendo utilizado.');
        }

        $pdo->beginTransaction();

        // Insere aluno
        $stmt = $pdo->prepare("
            INSERT INTO alunos (filial_id, nome, cpf, rg, sexo, nascimento, email, telefone, endereco, status)
            VALUES (:filial_id, :nome, :cpf, :rg, :sexo, :nascimento, :email, :telefone, :endereco, 'Ativo')
        ");
        $stmt->execute([
            ':filial_id'  => $dados['filial_id'],
            ':nome'       => $dados['nome'],
            ':cpf'        => $dados['cpf'],
            ':rg'         => !empty($dados['rg']) ? $dados['rg'] : null,
            ':sexo'       => $dados['sexo'],
            ':nascimento' => $dados['nascimento'],
            ':email'      => $dados['email'],
            ':telefone'   => $dados['telefone'],
            ':endereco'   => !empty($dados['endereco']) ? $dados['endereco'] : null
        ]);

        $novoAlunoId = (int) $pdo->lastInsertId();

        // Cria usuário de acesso para o aluno
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role, aluno_id)
            VALUES (:nome, :email, :senha, 'Aluno', :aluno_id)
        ");
        $stmt->execute([
            ':nome'     => $dados['nome'],
            ':email'    => $dados['email'],
            ':senha'    => password_hash($dados['senha'], PASSWORD_DEFAULT),
            ':aluno_id' => $novoAlunoId
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erroModel = $e->getMessage();
    }
}

/* 5. ATUALIZAR ALUNO */
elseif ($operacao === 'atualizar') {
    $id = (int) ($id ?? 0);

    try {
        // Valida CPF duplicado ignorando o próprio aluno
        $stmt = $pdo->prepare("SELECT id FROM alunos WHERE cpf = :cpf AND id != :id");
        $stmt->execute([':cpf' => $dados['cpf'], ':id' => $id]);
        if ($stmt->fetch()) {
            throw new Exception('Este CPF pertence a outro aluno.');
        }

        // Valida E-mail duplicado ignorando o próprio usuário do aluno
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND (aluno_id IS NULL OR aluno_id != :aluno_id)");
        $stmt->execute([':email' => $dados['email'], ':aluno_id' => $id]);
        if ($stmt->fetch()) {
            throw new Exception('Este e-mail pertence a outro usuário.');
        }

        $pdo->beginTransaction();

        // Atualiza aluno
        $stmt = $pdo->prepare("
            UPDATE alunos
            SET filial_id = :filial_id, nome = :nome, cpf = :cpf, rg = :rg, sexo = :sexo,
                nascimento = :nascimento, email = :email, telefone = :telefone,
                endereco = :endereco, status = :status
            WHERE id = :id
        ");
        $stmt->execute([
            ':filial_id'  => $dados['filial_id'],
            ':nome'       => $dados['nome'],
            ':cpf'        => $dados['cpf'],
            ':rg'         => !empty($dados['rg']) ? $dados['rg'] : null,
            ':sexo'       => $dados['sexo'],
            ':nascimento' => $dados['nascimento'],
            ':email'      => $dados['email'],
            ':telefone'   => $dados['telefone'],
            ':endereco'   => !empty($dados['endereco']) ? $dados['endereco'] : null,
            ':status'     => $dados['status'],
            ':id'         => $id
        ]);

        // Atualiza usuário
        if (!empty($dados['senha'])) {
            $stmt = $pdo->prepare("UPDATE users SET name = :nome, email = :email, password = :senha WHERE aluno_id = :aluno_id");
            $stmt->execute([
                ':nome'     => $dados['nome'],
                ':email'    => $dados['email'],
                ':senha'    => password_hash($dados['senha'], PASSWORD_DEFAULT),
                ':aluno_id' => $id
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = :nome, email = :email WHERE aluno_id = :aluno_id");
            $stmt->execute([
                ':nome'     => $dados['nome'],
                ':email'    => $dados['email'],
                ':aluno_id' => $id
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erroModel = $e->getMessage();
    }
}

/* 6. DADOS COMPLETOS DA VISUALIZAÇÃO DO ALUNO */
elseif ($operacao === 'visualizar') {
    $id = (int) ($id ?? 0);

    // Dados do aluno
    $stmt = $pdo->prepare("SELECT a.*, f.nome AS nome_filial FROM alunos a INNER JOIN filiais f ON f.id = a.filial_id WHERE a.id = :id");
    $stmt->execute([':id' => $id]);
    $aluno = $stmt->fetch() ?: null;

    // Matrícula ativa
    $stmt = $pdo->prepare("
        SELECT m.*, p.nome AS nome_plano, p.duracao
        FROM matriculas m
        INNER JOIN planos p ON p.id = m.plano_id
        WHERE m.aluno_id = :id AND m.ativa = TRUE
        ORDER BY m.id DESC
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $matricula = $stmt->fetch() ?: false;

    // Histórico de contas/mensalidades
    $stmt = $pdo->prepare("SELECT * FROM contas WHERE aluno_id = :id ORDER BY vencimento DESC");
    $stmt->execute([':id' => $id]);
    $contas = $stmt->fetchAll();

    // Planos disponíveis na rede do aluno
    $stmt = $pdo->prepare("
        SELECT id, nome, valor, duracao
        FROM planos
        WHERE company_id = (
            SELECT f.company_id FROM alunos a INNER JOIN filiais f ON f.id = a.filial_id WHERE a.id = :id
        )
        ORDER BY nome
    ");
    $stmt->execute([':id' => $id]);
    $planos = $stmt->fetchAll();
}

/* 7. MATRICULAR ALUNO */
elseif ($operacao === 'matricular') {
    $alunoId = (int) ($alunoId ?? 0);
    $planoId = (int) ($planoId ?? 0);
    $dataInicio = $dataInicio ?? '';
    $desconto = (float) ($desconto ?? 0.0);

    try {
        $pdo->beginTransaction();

        // Verifica se já possui matrícula ativa
        $stmt = $pdo->prepare("SELECT id FROM matriculas WHERE aluno_id = :aluno_id AND ativa = TRUE");
        $stmt->execute([':aluno_id' => $alunoId]);
        if ($stmt->fetch()) {
            throw new Exception('Este aluno já possui matrícula ativa.');
        }

        // Busca informações do plano
        $stmt = $pdo->prepare("SELECT id, valor, duracao FROM planos WHERE id = :plano_id");
        $stmt->execute([':plano_id' => $planoId]);
        $plano = $stmt->fetch();
        if (!$plano) {
            throw new Exception('Plano não encontrado.');
        }

        $meses = match ($plano['duracao']) {
            '1 Mês'   => 1,
            '3 Meses' => 3,
            '6 Meses' => 6,
            '1 Ano'   => 12,
            default   => 0
        };
        if ($meses === 0) {
            throw new Exception('Duração do plano inválida.');
        }

        $valorFinal = (float) $plano['valor'] - $desconto;
        if ($valorFinal < 0) {
            throw new Exception('O desconto não pode ser maior que o valor do plano.');
        }

        $dataFim = date('Y-m-d', strtotime($dataInicio . " +$meses months"));

        // Insere a matrícula
        $stmt = $pdo->prepare("
            INSERT INTO matriculas (aluno_id, plano_id, inicio, fim, valor, desconto, ativa)
            VALUES (:aluno_id, :plano_id, :inicio, :fim, :valor, :desconto, TRUE)
        ");
        $stmt->execute([
            ':aluno_id' => $alunoId,
            ':plano_id' => $planoId,
            ':inicio'   => $dataInicio,
            ':fim'      => $dataFim,
            ':valor'    => $valorFinal,
            ':desconto' => $desconto
        ]);

        $matriculaId = (int) $pdo->lastInsertId();
        $valorParcela = floor(($valorFinal / $meses) * 100) / 100;

        // Gera as parcelas financeiras
        $stmtConta = $pdo->prepare("
            INSERT INTO contas (aluno_id, matricula_id, vencimento, valor, status) 
            VALUES (:aluno_id, :matricula_id, :vencimento, :valor, 'Aberto')
        ");

        for ($i = 0; $i < $meses; $i++) {
            $valorAtual = ($i === $meses - 1) ? ($valorFinal - ($valorParcela * ($meses - 1))) : $valorParcela;
            $vencimento = date('Y-m-d', strtotime($dataInicio . " +$i months"));

            $stmtConta->execute([
                ':aluno_id'     => $alunoId,
                ':matricula_id' => $matriculaId,
                ':vencimento'   => $vencimento,
                ':valor'        => $valorAtual
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erroModel = $e->getMessage();
    }
}

/* 8. REGISTRAR PAGAMENTO */
elseif ($operacao === 'pagar') {
    $contaId = (int) ($contaId ?? 0);
    $alunoId = (int) ($alunoId ?? 0);

    $stmt = $pdo->prepare("
        UPDATE contas
        SET status = 'Pago', forma_pagamento = 'Dinheiro', pago_em = NOW()
        WHERE id = :conta_id AND aluno_id = :aluno_id AND status = 'Aberto'
    ");
    $stmt->execute([':conta_id' => $contaId, ':aluno_id' => $alunoId]);
    $pagamentoRealizado = $stmt->rowCount() > 0;
}

/* 9. DADOS DO PORTAL DO ALUNO */
elseif ($operacao === 'dados_portal_aluno') {
    $alunoId = (int) ($alunoId ?? 0);

    // Nome do aluno
    $stmt = $pdo->prepare("SELECT nome FROM alunos WHERE id = :id");
    $stmt->execute([':id' => $alunoId]);
    $aluno = $stmt->fetch();

    // Matrícula ativa
    $stmt = $pdo->prepare("
        SELECT m.fim, p.nome AS nome_plano
        FROM matriculas m
        JOIN planos p ON m.plano_id = p.id
        WHERE m.aluno_id = :aluno_id AND m.ativa = TRUE
        LIMIT 1
    ");
    $stmt->execute([':aluno_id' => $alunoId]);
    $matricula = $stmt->fetch() ?: false;

    // Frequência no mês
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM checkins
        WHERE aluno_id = :aluno_id
          AND MONTH(data) = MONTH(CURDATE())
          AND YEAR(data) = YEAR(CURDATE())
    ");
    $stmt->execute([':aluno_id' => $alunoId]);
    $frequencia = (int) $stmt->fetchColumn();

    // Faturas em aberto
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM contas WHERE aluno_id = :aluno_id AND status = 'Aberto'");
    $stmt->execute([':aluno_id' => $alunoId]);
    $faturas_abertas = (int) $stmt->fetchColumn();
}
