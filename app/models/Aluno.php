<?php

require_once __DIR__ . '/Database.php';

/** @var PDO $pdo */
/** @var string $operacao */
/** @var array<string, mixed> $dados */
/** @var int $id */
/** @var string $cpf */
/** @var string $status */
/** @var int $ignorarId */
/** @var string $email */
/** @var int $alunoId */
/** @var int $planoId */
/** @var int $contaId */
/** @var string $dataInicio */
/** @var float $desconto */

$operacao = $operacao ?? '';

/* LISTAR ALUNOS */
if ($operacao === 'listar') {
    $sql = "
        SELECT a.*, f.nome AS nome_filial
        FROM alunos a
        INNER JOIN filiais f ON f.id = a.filial_id
        WHERE 1 = 1
    ";

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

/* LISTAR FILIAIS */ elseif ($operacao === 'listar_filiais') {
    $stmt = $pdo->query("
        SELECT id, nome
        FROM filiais
        WHERE ativo = TRUE
        ORDER BY nome
    ");

    $filiais = $stmt->fetchAll();
}

/* VERIFICAR CPF */ elseif ($operacao === 'verificar_cpf') {
    $sql = "SELECT id FROM alunos WHERE cpf = :cpf";
    $parametros = [':cpf' => $cpf];

    if (!empty($ignorarId)) {
        $sql .= " AND id != :id";
        $parametros[':id'] = $ignorarId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $cpfExiste = (bool) $stmt->fetch();
}

/* VERIFICAR E-MAIL */ elseif ($operacao === 'verificar_email') {
    $sql = "SELECT id FROM users WHERE email = :email";
    $parametros = [':email' => $email];

    if (!empty($ignorarId)) {
        $sql .= " AND (aluno_id IS NULL OR aluno_id != :aluno_id)";
        $parametros[':aluno_id'] = $ignorarId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $emailExiste = (bool) $stmt->fetch();
}

/* CADASTRAR ALUNO */ elseif ($operacao === 'cadastrar') {
    $erroModel = '';

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO alunos (
                filial_id, nome, cpf, rg, sexo, nascimento,
                email, telefone, endereco, status
            ) VALUES (
                :filial_id, :nome, :cpf, :rg, :sexo, :nascimento,
                :email, :telefone, :endereco, 'Ativo'
            )
        ");

        $stmt->execute([
            ':filial_id'  => $dados['filial_id'],
            ':nome'       => $dados['nome'],
            ':cpf'        => $dados['cpf'],
            ':rg'         => $dados['rg'] ?: null,
            ':sexo'       => $dados['sexo'],
            ':nascimento' => $dados['nascimento'],
            ':email'      => $dados['email'],
            ':telefone'   => $dados['telefone'],
            ':endereco'   => $dados['endereco'] ?: null
        ]);

        $novoAlunoId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role, aluno_id)
            VALUES (:nome, :email, :senha, 'Aluno', :aluno_id)
        ");

        $stmt->execute([
            ':nome'     => $dados['nome'],
            ':email'    => $dados['email'],
            ':senha'    => password_hash(
                $dados['senha'],
                PASSWORD_DEFAULT
            ),
            ':aluno_id' => $novoAlunoId
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $erroModel = 'Erro ao cadastrar o aluno.';
    }
}

/* BUSCAR ALUNO */ elseif ($operacao === 'buscar') {
    $stmt = $pdo->prepare("
        SELECT a.*, f.nome AS nome_filial
        FROM alunos a
        INNER JOIN filiais f ON f.id = a.filial_id
        WHERE a.id = :id
    ");

    $stmt->execute([':id' => $id]);
    $aluno = $stmt->fetch();
}

/* ATUALIZAR ALUNO */ elseif ($operacao === 'atualizar') {
    $erroModel = '';

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE alunos
            SET filial_id = :filial_id,
                nome = :nome,
                cpf = :cpf,
                rg = :rg,
                sexo = :sexo,
                nascimento = :nascimento,
                email = :email,
                telefone = :telefone,
                endereco = :endereco,
                status = :status
            WHERE id = :id
        ");

        $stmt->execute([
            ':filial_id'  => $dados['filial_id'],
            ':nome'       => $dados['nome'],
            ':cpf'        => $dados['cpf'],
            ':rg'         => $dados['rg'] ?: null,
            ':sexo'       => $dados['sexo'],
            ':nascimento' => $dados['nascimento'],
            ':email'      => $dados['email'],
            ':telefone'   => $dados['telefone'],
            ':endereco'   => $dados['endereco'] ?: null,
            ':status'     => $dados['status'],
            ':id'         => $id
        ]);

        if ($dados['senha'] !== '') {
            $stmt = $pdo->prepare("
                UPDATE users
                SET name = :nome,
                    email = :email,
                    password = :senha
                WHERE aluno_id = :aluno_id
            ");

            $stmt->execute([
                ':nome'     => $dados['nome'],
                ':email'    => $dados['email'],
                ':senha'    => password_hash(
                    $dados['senha'],
                    PASSWORD_DEFAULT
                ),
                ':aluno_id' => $id
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE users
                SET name = :nome, email = :email
                WHERE aluno_id = :aluno_id
            ");

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

        $erroModel = 'Erro ao atualizar o aluno.';
    }
}

/* DADOS DA VISUALIZAÇÃO */ elseif ($operacao === 'visualizar') {
    $stmt = $pdo->prepare("
        SELECT a.*, f.nome AS nome_filial
        FROM alunos a
        INNER JOIN filiais f ON f.id = a.filial_id
        WHERE a.id = :id
    ");

    $stmt->execute([':id' => $id]);
    $aluno = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT m.*, p.nome AS nome_plano, p.duracao
        FROM matriculas m
        INNER JOIN planos p ON p.id = m.plano_id
        WHERE m.aluno_id = :id AND m.ativa = TRUE
        ORDER BY m.id DESC
        LIMIT 1
    ");

    $stmt->execute([':id' => $id]);
    $matricula = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT *
        FROM contas
        WHERE aluno_id = :id
        ORDER BY vencimento DESC
    ");

    $stmt->execute([':id' => $id]);
    $contas = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT id, nome, valor, duracao
        FROM planos
        WHERE company_id = (
            SELECT f.company_id
            FROM alunos a
            INNER JOIN filiais f ON f.id = a.filial_id
            WHERE a.id = :id
        )
        ORDER BY nome
    ");

    $stmt->execute([':id' => $id]);
    $planos = $stmt->fetchAll();
}

/* MATRICULAR ALUNO */ elseif ($operacao === 'matricular') {
    $erroModel = '';

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT id
            FROM matriculas
            WHERE aluno_id = :aluno_id AND ativa = TRUE
        ");

        $stmt->execute([':aluno_id' => $alunoId]);

        if ($stmt->fetch()) {
            throw new Exception('Este aluno já possui matrícula ativa.');
        }

        $stmt = $pdo->prepare("
            SELECT id, valor, duracao
            FROM planos
            WHERE id = :plano_id
        ");

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
            throw new Exception(
                'O desconto não pode ser maior que o valor do plano.'
            );
        }

        $dataFim = date(
            'Y-m-d',
            strtotime($dataInicio . " +$meses months")
        );

        $stmt = $pdo->prepare("
            INSERT INTO matriculas (
                aluno_id, plano_id, inicio, fim,
                valor, desconto, ativa
            ) VALUES (
                :aluno_id, :plano_id, :inicio, :fim,
                :valor, :desconto, TRUE
            )
        ");

        $stmt->execute([
            ':aluno_id' => $alunoId,
            ':plano_id' => $planoId,
            ':inicio'   => $dataInicio,
            ':fim'      => $dataFim,
            ':valor'    => $valorFinal,
            ':desconto' => $desconto
        ]);

        $matriculaId = $pdo->lastInsertId();
        
        // Calcula o valor base de cada parcela com duas casas decimais
        $valorParcela = floor(($valorFinal / $meses) * 100) / 100;

        $stmtConta = $pdo->prepare("
            INSERT INTO contas (aluno_id, matricula_id, vencimento, valor, status) 
            VALUES (:aluno_id, :matricula_id, :vencimento, :valor, 'Aberto')
        ");

        for ($i = 0; $i < $meses; $i++) {
            // Se for a última parcela, joga a diferença de arredondamento nela
            if ($i === $meses - 1) {
                $valorAtual = $valorFinal - ($valorParcela * ($meses - 1));
            } else {
                $valorAtual = $valorParcela;
            }

            // Calcula o vencimento (soma de meses a partir da data inicial)
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

/* REGISTRAR PAGAMENTO */ elseif ($operacao === 'pagar') {
    $stmt = $pdo->prepare("
        UPDATE contas
        SET status = 'Pago',
            forma_pagamento = 'Dinheiro',
            pago_em = NOW()
        WHERE id = :conta_id
          AND aluno_id = :aluno_id
          AND status = 'Aberto'
    ");

    $stmt->execute([
        ':conta_id' => $contaId,
        ':aluno_id' => $alunoId
    ]);

    $pagamentoRealizado = $stmt->rowCount() > 0;
}

/* DADOS DO PORTAL ALUNO */
elseif ($operacao === 'dados_portal_aluno') {
    // 1. Busca dados do aluno
    $stmt_aluno = $pdo->prepare("SELECT nome FROM alunos WHERE id = :id");
    $stmt_aluno->execute([':id' => $alunoId]);
    $aluno = $stmt_aluno->fetch();

    // 2. Busca matrícula ativa e o plano vinculado
    $stmt_mat = $pdo->prepare(
        "SELECT m.fim, p.nome AS nome_plano
         FROM matriculas m
         JOIN planos p ON m.plano_id = p.id
         WHERE m.aluno_id = :aluno_id AND m.ativa = TRUE
         LIMIT 1"
    );
    $stmt_mat->execute([':aluno_id' => $alunoId]);
    $matricula = $stmt_mat->fetch();

    // 3. Conta quantos check-ins o aluno fez no mês atual
    $stmt_freq = $pdo->prepare(
        "SELECT COUNT(*) AS total
         FROM checkins
         WHERE aluno_id = :aluno_id
           AND MONTH(data) = MONTH(CURDATE())
           AND YEAR(data) = YEAR(CURDATE())"
    );
    $stmt_freq->execute([':aluno_id' => $alunoId]);
    $frequencia = $stmt_freq->fetchColumn();

    // 4. Conta faturas em aberto
    $stmt_faturas = $pdo->prepare(
        "SELECT COUNT(*) AS total FROM contas WHERE aluno_id = :aluno_id AND status = 'Aberto'"
    );
    $stmt_faturas->execute([':aluno_id' => $alunoId]);
    $faturas_abertas = $stmt_faturas->fetchColumn();
}

