<?php
// config/conexao.php

// Configurações de conexão com o banco de dados
$host = 'localhost';
$dbname = 'gymcore_db';
$username = 'root';
$password = ''; // Senha padrão vazia no XAMPP/WampServer

try {
    // Inicialização da conexão PDO com charset UTF-8 e tratamento de erro configurado para exceções
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em caso de erros SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna resultados como array associativo
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Desabilita emulação de prepared statements para segurança
    ]);
} catch (PDOException $e) {
    // Em caso de erro na conexão, interrompe a execução e exibe uma mensagem amigável
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
