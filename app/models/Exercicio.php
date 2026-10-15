
<?php
// ========================================
// GYMFLOW - MODEL DE EXERCÍCIOS
// app/models/Exercicio.php
// ========================================

require_once __DIR__ . '/Database.php';

/**
 * Obtém a academia da sessão.
 *
 * Nunca realiza consultas de exercícios
 * sem identificar uma academia válida.
 */
function obterCompanyIdExercicios(): int
{
    $companyId = filter_var(
        $_SESSION['company_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($companyId === false || $companyId === null || $companyId <= 0) {
        throw new RuntimeException(
            'Não foi possível identificar a academia da sessão.'
        );
    }

    return $companyId;
}

/**
 * Executa uma instrução SQL preparada.
 *
 * Garante que falhas de execute() sejam
 * sinalizadas ao controller.
 */
function executarSQLExercicios(
    PDO $pdo,
    string $sql,
    array $parametros = []
): PDOStatement {

    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        throw new RuntimeException(
            'Não foi possível preparar a operação de exercícios.'
        );
    }

    if (!$stmt->execute($parametros)) {
        throw new RuntimeException(
            'Não foi possível executar a operação de exercícios.'
        );
    }

    return $stmt;
}

/**
 * Busca todos os exercícios ativos
 * pertencentes à academia logada.
 */
function listarExercicios($pdo)
{
    $companyId = obterCompanyIdExercicios();

    $sql = "
        SELECT
            id,
            nome,
            grupo,
            midia,
            tipo_midia

        FROM exercicios

        WHERE ativo = 1
          AND company_id = ?

        ORDER BY nome ASC
    ";

    $stmt = executarSQLExercicios(
        $pdo,
        $sql,
        [$companyId]
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Pesquisa exercícios ativos pelo nome.
 */
function pesquisarExercicios($pdo, $pesquisa)
{
    $companyId = obterCompanyIdExercicios();

    $pesquisa = trim((string) $pesquisa);

    $sql = "
        SELECT
            id,
            nome,
            grupo,
            midia,
            tipo_midia

        FROM exercicios

        WHERE nome LIKE ?
          AND ativo = 1
          AND company_id = ?

        ORDER BY nome ASC
    ";

    $stmt = executarSQLExercicios(
        $pdo,
        $sql,
        [
            '%' . $pesquisa . '%',
            $companyId
        ]
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Filtra exercícios ativos
 * por grupo muscular.
 */
function filtrarExerciciosPorGrupo($pdo, $grupo)
{
    $companyId = obterCompanyIdExercicios();

    $grupo = trim((string) $grupo);

    $sql = "
        SELECT
            id,
            nome,
            grupo,
            midia,
            tipo_midia

        FROM exercicios

        WHERE grupo = ?
          AND ativo = 1
          AND company_id = ?

        ORDER BY nome ASC
    ";

    $stmt = executarSQLExercicios(
        $pdo,
        $sql,
        [
            $grupo,
            $companyId
        ]
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Busca combinada por nome e grupo.
 *
 * Ambos os filtros são opcionais.
 * Quando utilizados juntos, os dois
 * devem corresponder ao resultado.
 *
 * Esta função será usada pelo controller
 * quando atualizarmos os filtros.
 */
function buscarExerciciosFiltrados(
    $pdo,
    $pesquisa = '',
    $grupo = ''
) {
    $companyId = obterCompanyIdExercicios();

    $pesquisa = trim((string) $pesquisa);
    $grupo = trim((string) $grupo);

    $sql = "
        SELECT
            id,
            nome,
            grupo,
            midia,
            tipo_midia

        FROM exercicios

        WHERE ativo = 1
          AND company_id = ?
    ";

    $parametros = [$companyId];

    if ($pesquisa !== '') {
        $sql .= " AND nome LIKE ?";

        $parametros[] = '%' . $pesquisa . '%';
    }

    if ($grupo !== '') {
        $sql .= " AND grupo = ?";

        $parametros[] = $grupo;
    }

    $sql .= " ORDER BY nome ASC";

    $stmt = executarSQLExercicios(
        $pdo,
        $sql,
        $parametros
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Cadastra um exercício.
 *
 * A empresa é sempre obtida da sessão,
 * nunca de um campo enviado pelo usuário.
 */
function cadastrarExercicio(
    $pdo,
    $nome,
    $grupo,
    $midia,
    $tipo_midia
) {
    $companyId = obterCompanyIdExercicios();

    $sql = "
        INSERT INTO exercicios (
            company_id,
            nome,
            grupo,
            midia,
            tipo_midia
        )
        VALUES (?, ?, ?, ?, ?)
    ";

    executarSQLExercicios(
        $pdo,
        $sql,
        [
            $companyId,
            $nome,
            $grupo,
            $midia,
            $tipo_midia
        ]
    );

    return true;
}

/**
 * Desativa um exercício.
 *
 * Soft delete: mantém o registro
 * no banco, mas define ativo = 0.
 *
 * Somente exercícios ativos da
 * própria academia podem ser desativados.
 */
function excluirExercicio($pdo, $id)
{
    $companyId = obterCompanyIdExercicios();
    $id = (int) $id;

    if ($id <= 0) {
        throw new InvalidArgumentException(
            'Identificador de exercício inválido.'
        );
    }

    $sql = "
        UPDATE exercicios

        SET ativo = 0

        WHERE id = ?
          AND company_id = ?
          AND ativo = 1
    ";

    $stmt = executarSQLExercicios(
        $pdo,
        $sql,
        [
            $id,
            $companyId
        ]
    );

    if ($stmt->rowCount() === 0) {
        throw new RuntimeException(
            'O exercício não foi encontrado ou já está desativado.'
        );
    }

    return true;
}

/**
 * Busca um exercício ativo pelo ID.
 *
 * Não retorna dados de outras
 * academias ou exercícios desativados.
 */
function buscarExercicioPorId($pdo, $id)
{
    $companyId = obterCompanyIdExercicios();
    $id = (int) $id;

    if ($id <= 0) {
        return false;
    }

    $sql = "
        SELECT
            id,
            nome,
            grupo,
            midia,
            tipo_midia

        FROM exercicios

        WHERE id = ?
          AND company_id = ?
          AND ativo = 1

        LIMIT 1
    ";

    $stmt = executarSQLExercicios(
        $pdo,
        $sql,
        [
            $id,
            $companyId
        ]
    );

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Atualiza um exercício.
 *
 * Mantém os nomes dos campos e
 * a assinatura original da função,
 * garantindo compatibilidade
 * com ExercicioController.php.
 */
function atualizarExercicio(
    $pdo,
    $id,
    $nome,
    $grupo,
    $midia,
    $tipo_midia
) {
    $companyId = obterCompanyIdExercicios();
    $id = (int) $id;

    if ($id <= 0) {
        throw new InvalidArgumentException(
            'Identificador de exercício inválido.'
        );
    }

    $sql = "
        UPDATE exercicios

        SET
            nome = ?,
            grupo = ?,
            midia = ?,
            tipo_midia = ?

        WHERE id = ?
          AND company_id = ?
          AND ativo = 1
    ";

    executarSQLExercicios(
        $pdo,
        $sql,
        [
            $nome,
            $grupo,
            $midia,
            $tipo_midia,
            $id,
            $companyId
        ]
    );

    // Não verificamos rowCount() aqui.
    // No MySQL, salvar dados idênticos aos
    // existentes pode retornar zero linhas
    // alteradas, mesmo quando tudo funcionou.

    return true;
}
