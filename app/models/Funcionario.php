<?php
// app/models/Funcionario.php

require_once __DIR__ . '/Database.php';

/** @var PDO $pdo */
/** @var string $operacao */
/** @var string $email */
/** @var string $nome */
/** @var string $role */
/** @var string $senha */
/** @var int $id */
/** @var int $id_filial */
/** @var string $nome_busca */
/** @var string $role_busca */

$operacao = $operacao ?? '';

/* BUSCAR USUÁRIO POR EMAIL (Login / Validação) */
if ($operacao === 'buscar_por_email') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();
}

/* VERIFICAR EMAIL JÁ EXISTENTE */
elseif ($operacao === 'verificar_email') {
    $sql = "SELECT id FROM users WHERE email = :email";
    $parametros = [':email' => $email];
    if (!empty($id)) {
        $sql .= " AND id != :id";
        $parametros[':id'] = $id;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $emailExiste = (bool)$stmt->fetch();
}

/* LISTAR FUNCIONÁRIOS */
elseif ($operacao === 'listar') {
    $sql = "SELECT id, name, email, role FROM users WHERE role != 'Aluno'";
    $parametros = [];

    if (!empty($nome_busca)) {
        $sql .= " AND name LIKE :nome";
        $parametros[':nome'] = "%$nome_busca%";
    }

    if (!empty($role_busca)) {
        $sql .= " AND role = :role";
        $parametros[':role'] = $role_busca;
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $funcionarios = $stmt->fetchAll();
}

/* CADASTRAR FUNCIONÁRIO */
elseif ($operacao === 'cadastrar') {
    $erroModel = '';
    try {
        $pdo->beginTransaction();

        $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
        $sql_user = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt_user = $pdo->prepare($sql_user);
        $stmt_user->execute([
            ':name'     => $nome,
            ':email'    => $email,
            ':password' => $senha_hash,
            ':role'     => $role
        ]);

        $novoUserId = $pdo->lastInsertId();

        $sql_filial = "INSERT INTO user_filiais (user_id, filial_id) VALUES (:user_id, :filial_id)";
        $stmt_filial = $pdo->prepare($sql_filial);
        $stmt_filial->execute([
            ':user_id'   => $novoUserId,
            ':filial_id' => $id_filial
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erroModel = 'Erro ao cadastrar funcionário: ' . $e->getMessage();
    }
}

/* BUSCAR DETALHES DE UM FUNCIONÁRIO */
elseif ($operacao === 'buscar') {
    // Busca os dados do usuário
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id AND role != 'Aluno'");
    $stmt->execute([':id' => $id]);
    $funcionario = $stmt->fetch();

    if ($funcionario) {
        // Busca a filial vinculada
        $stmt_filial = $pdo->prepare("
            SELECT f.* 
            FROM filiais f
            JOIN user_filiais uf ON f.id = uf.filial_id
            WHERE uf.user_id = :user_id
            LIMIT 1
        ");
        $stmt_filial->execute([':user_id' => $id]);
        $filial_vinculada = $stmt_filial->fetch();
    } else {
        $filial_vinculada = null;
    }
}

/* ATUALIZAR FUNCIONÁRIO */
elseif ($operacao === 'atualizar') {
    $erroModel = '';
    try {
        $pdo->beginTransaction();

        if (!empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, password = :password, role = :role WHERE id = :id");
            $stmt->execute([
                ':name'     => $nome,
                ':email'    => $email,
                ':password' => $senha_hash,
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

        // Atualiza a filial vinculada
        $stmt_del = $pdo->prepare("DELETE FROM user_filiais WHERE user_id = :user_id");
        $stmt_del->execute([':user_id' => $id]);

        $stmt_ins = $pdo->prepare("INSERT INTO user_filiais (user_id, filial_id) VALUES (:user_id, :filial_id)");
        $stmt_ins->execute([
            ':user_id'   => $id,
            ':filial_id' => $id_filial
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erroModel = 'Erro ao atualizar funcionário: ' . $e->getMessage();
    }
}
