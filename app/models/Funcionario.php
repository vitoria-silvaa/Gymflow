<?php
// app/models/Funcionario.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';
$erroModel = '';
$dados = $dados ?? [];
$senha = $senha ?? '';

/* 1. BUSCAR USUÁRIO POR E-MAIL (Login e Validações) */
if ($operacao === 'buscar_por_email') {
    $email = trim($dados['email'] ?? $email ?? '');
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch() ?: null;
}

/* 2. LISTAR FUNCIONÁRIOS */
elseif ($operacao === 'listar') {
    $nome_busca = trim($nome_busca ?? '');
    $role_busca = trim($role_busca ?? '');

    $sql = "SELECT id, name, email, role FROM users WHERE role != 'Aluno'";
    $parametros = [];

    if ($nome_busca !== '') {
        $sql .= " AND name LIKE :nome";
        $parametros[':nome'] = "%$nome_busca%";
    }
    if ($role_busca !== '') {
        $sql .= " AND role = :role";
        $parametros[':role'] = $role_busca;
    }

    $sql .= " ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $funcionarios = $stmt->fetchAll();
}

/* 3. BUSCAR FUNCIONÁRIO COM SUA FILIAL */
elseif ($operacao === 'buscar') {
    $id = (int) ($id ?? 0);
    $stmt = $pdo->prepare("
        SELECT u.*, f.id AS filial_id, f.nome AS filial_nome, f.cnpj AS filial_cnpj 
        FROM users u 
        LEFT JOIN user_filiais uf ON uf.user_id = u.id 
        LEFT JOIN filiais f ON f.id = uf.filial_id 
        WHERE u.id = :id AND u.role != 'Aluno'
    ");
    $stmt->execute([':id' => $id]);
    $funcionario = $stmt->fetch() ?: null;

    if ($funcionario) {
        $filial_vinculada = !empty($funcionario['filial_id']) ? [
            'id'   => $funcionario['filial_id'],
            'nome' => $funcionario['filial_nome'],
            'cnpj' => $funcionario['filial_cnpj'] ?? ''
        ] : null;
    } else {
        $filial_vinculada = null;
    }
}

/* 4. CADASTRAR FUNCIONÁRIO */
elseif ($operacao === 'cadastrar') {
    $email = trim($dados['email'] ?? $email ?? '');
    $senha = $dados['senha'] ?? $senha ?? '';

    try {
        if ($senha === '') {
            throw new Exception('A senha é obrigatória.');
        }

        // Valida e-mail duplicado
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            throw new Exception('Este e-mail já está cadastrado.');
        }

        $pdo->beginTransaction();

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        $stmt->execute([
            ':name'     => trim($dados['nome'] ?? $nome ?? ''),
            ':email'    => $email,
            ':password' => $senhaHash,
            ':role'     => $dados['role'] ?? $role ?? 'Recepcao'
        ]);

        $novoUserId = (int) $pdo->lastInsertId();
        $idFilial = (int) ($dados['id_filial'] ?? $id_filial ?? 0);

        if ($idFilial > 0) {
            $stmt = $pdo->prepare("INSERT INTO user_filiais (user_id, filial_id) VALUES (:user_id, :filial_id)");
            $stmt->execute([':user_id' => $novoUserId, ':filial_id' => $idFilial]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erroModel = $e->getMessage();
    }
}

/* 5. ATUALIZAR FUNCIONÁRIO */
elseif ($operacao === 'atualizar') {
    $id = (int) ($id ?? 0);
    $email = trim($dados['email'] ?? $email ?? '');
    $novaSenha = $dados['senha'] ?? $senha ?? '';

    try {
        // Valida e-mail duplicado ignorando o próprio funcionário
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $stmt->execute([':email' => $email, ':id' => $id]);
        if ($stmt->fetch()) {
            throw new Exception('Este e-mail pertence a outro usuário.');
        }

        $pdo->beginTransaction();

        $nome = trim($dados['nome'] ?? $nome ?? '');
        $role = $dados['role'] ?? $role ?? 'Recepcao';

        if (!empty($novaSenha)) {
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, password = :password, role = :role WHERE id = :id");
            $stmt->execute([
                ':name'     => $nome,
                ':email'    => $email,
                ':password' => $senhaHash,
                ':role'     => $role,
                ':id'       => $id
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id");
            $stmt->execute([
                ':name'  => $nome,
                ':email' => $email,
                ':role'  => $role,
                ':id'    => $id
            ]);
        }

        $idFilial = (int) ($dados['id_filial'] ?? $id_filial ?? 0);
        $stmt = $pdo->prepare("DELETE FROM user_filiais WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $id]);

        if ($idFilial > 0) {
            $stmt = $pdo->prepare("INSERT INTO user_filiais (user_id, filial_id) VALUES (:user_id, :filial_id)");
            $stmt->execute([':user_id' => $id, ':filial_id' => $idFilial]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erroModel = $e->getMessage();
    }
}
