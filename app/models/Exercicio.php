<?php
// app/models/Exercicio.php

require_once __DIR__ . '/Database.php';

/**
 * Busca todos os exercícios cadastrados.
 */
function listarExercicios($pdo)
{
    $sql = "SELECT id, nome, grupo, midia, tipo_midia
            FROM exercicios
            ORDER BY nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Pesquisa exercícios pelo nome.
 */
function pesquisarExercicios($pdo, $pesquisa)
{
    $sql = "SELECT id, nome, grupo, midia, tipo_midia
            FROM exercicios
            WHERE nome LIKE ?
            ORDER BY nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%" . $pesquisa . "%"]);

    return $stmt->fetchAll();
}

/**
 * Filtra exercícios por grupo muscular.
 */
function filtrarExerciciosPorGrupo($pdo, $grupo)
{
    $sql = "SELECT id, nome, grupo, midia, tipo_midia
            FROM exercicios
            WHERE grupo = ?
            ORDER BY nome ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$grupo]);

    return $stmt->fetchAll();
}

/**
 * Cadastra um novo exercício.
 */
function cadastrarExercicio($pdo, $nome, $grupo, $midia, $tipo_midia)
{
    $sql = "INSERT INTO exercicios (nome, grupo, midia, tipo_midia)
            VALUES (?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        $nome,
        $grupo,
        $midia,
        $tipo_midia
    ]);
}

/**
 * Exclui um exercício.
 */
function excluirExercicio($pdo, $id)

{
    $sql = "DELETE FROM exercicios WHERE id = ?";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([$id]);
}

/**
 * Busca um exercício pelo ID.
 */
function buscarExercicioPorId($pdo, $id)
{
    $sql = "SELECT id, nome, grupo, midia, tipo_midia
            FROM exercicios
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    return $stmt->fetch();
}


/**
 * Atualiza um exercício.
 */
function atualizarExercicio($pdo, $id, $nome, $grupo, $midia, $tipo_midia)
{
    $sql = "UPDATE exercicios
            SET nome = ?, grupo = ?, midia = ?, tipo_midia = ?
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        $nome,
        $grupo,
        $midia,
        $tipo_midia,
        $id
    ]);
}