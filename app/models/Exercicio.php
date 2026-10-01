<?php
// app/models/Exercicio.php

require_once __DIR__ . '/Database.php';

/**
 * Busca todos os exercícios cadastrados.
 */
function listarExercicios($pdo)
{
    $company_id = $_SESSION['company_id'] ?? 0;
    $sql = "SELECT id, nome, grupo, midia, tipo_midia
            FROM exercicios
            WHERE ativo = 1 AND company_id = ?
            ORDER BY nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$company_id]);

    return $stmt->fetchAll();
}

/**
 * Pesquisa exercícios pelo nome.
 */
function pesquisarExercicios($pdo, $pesquisa)
{
    $company_id = $_SESSION['company_id'] ?? 0;
    $sql = "SELECT id, nome, grupo, midia, tipo_midia
            FROM exercicios
            WHERE nome LIKE ?
            AND ativo = 1 AND company_id = ?
            ORDER BY nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%" . $pesquisa . "%", $company_id]);

    return $stmt->fetchAll();
}

/**
 * Filtra exercícios por grupo muscular.
 */
function filtrarExerciciosPorGrupo($pdo, $grupo)
{
    $company_id = $_SESSION['company_id'] ?? 0;
    $sql = "SELECT id, nome, grupo, midia, tipo_midia
            FROM exercicios
            WHERE grupo = ?
            AND ativo = 1 AND company_id = ?
            ORDER BY nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$grupo, $company_id]);

    return $stmt->fetchAll();
}

/**
 * Cadastra um novo exercício.
 */
function cadastrarExercicio($pdo, $nome, $grupo, $midia, $tipo_midia)
{
    $company_id = $_SESSION['company_id'] ?? 0;
    $sql = "INSERT INTO exercicios (company_id, nome, grupo, midia, tipo_midia)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        $company_id,
        $nome,
        $grupo,
        $midia,
        $tipo_midia
    ]);
}

/**
 * Desativa um exercício.
 */
function excluirExercicio($pdo, $id)
{
    $company_id = $_SESSION['company_id'] ?? 0;
    $sql = "UPDATE exercicios
            SET ativo = 0
            WHERE id = ? AND company_id = ?";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([$id, $company_id]);
}

/**
 * Busca um exercício pelo ID.
 */
function buscarExercicioPorId($pdo, $id)
{
    $company_id = $_SESSION['company_id'] ?? 0;
    $sql = "SELECT id, nome, grupo, midia, tipo_midia
            FROM exercicios
            WHERE id = ? AND company_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $company_id]);

    return $stmt->fetch();
}


/**
 * Atualiza um exercício.
 */
function atualizarExercicio($pdo, $id, $nome, $grupo, $midia, $tipo_midia)
{
    $company_id = $_SESSION['company_id'] ?? 0;
    $sql = "UPDATE exercicios
            SET nome = ?, grupo = ?, midia = ?, tipo_midia = ?
            WHERE id = ? AND company_id = ?";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        $nome,
        $grupo,
        $midia,
        $tipo_midia,
        $id,
        $company_id
    ]);
}
