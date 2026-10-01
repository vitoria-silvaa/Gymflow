<?php
// config/conexao.php

// Configurações de conexão — lidas de variáveis de ambiente.
// Em ambiente local (XAMPP), os valores padrão são aplicados automaticamente.
// Em produção, defina as variáveis de ambiente no servidor (nunca comite credenciais no Git).
$host     = getenv('DB_HOST')     ?: 'localhost';
$dbname   = getenv('DB_NAME')     ?: 'gymcore_db';
$username = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

try {
    // Inicialização da conexão PDO com charset UTF-8 e tratamento de erro configurado para exceções
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em caso de erros SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna resultados como array associativo
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Desabilita emulação de prepared statements para segurança
    ]);
} catch (PDOException $e) {
    // Em caso de erro na conexão, registra no log e exibe mensagem genérica
    error_log('DB Connection Error: ' . $e->getMessage());
    http_response_code(503);
    die("Serviço temporariamente indisponível. Tente novamente em instantes.");
}
