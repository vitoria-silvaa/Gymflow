<?php

require_once __DIR__ . '/../config/conexao.php';

$name = 'Setup Admin';
$email = 'adminfoda@gmail.com';
$senha = 'admin123';

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

$role = 'Admin';

$sql = "INSERT INTO users (
            name,
            email,
            password,
            role,
            aluno_id
        ) VALUES (
            :name,
            :email,
            :password,
            :role,
            NULL
        )";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':name' => $name,
    ':email' => $email,
    ':password' => $senha_hash,
    ':role' => $role
]);

echo "Admin criado com sucesso!";