<?php
// =============================================
// GYMFLOW - MODEL DE AVALIAÇÕES / FEEDBACKS
// app/models/Feedback.php
// =============================================

function feedbackBuscarAlunoAutorizado(
    PDO $pdo,
    int $usuarioId,
    int $companyId,
    ?int $alunoId = null
): ?array {
    $sql = "
        SELECT
            u.id AS usuario_id,
            u.id,
            u.name,
            u.name AS nome_usuario,
            u.aluno_id,
            u.company_id,
            f.id AS filial_id,
            f.nome AS nome_filial
        FROM users u
        INNER JOIN alunos a
            ON a.id = u.aluno_id
        INNER JOIN filiais f
            ON f.id = a.filial_id
        WHERE u.id = :usuario_id
          AND u.company_id = :company_id
          AND f.company_id = :filial_company_id
          AND u.role = 'Aluno'
    ";

    $params = [
        ':usuario_id'        => $usuarioId,
        ':company_id'        => $companyId,
        ':filial_company_id' => $companyId,
    ];

    if ($alunoId !== null) {
        $sql .= " AND u.aluno_id = :aluno_id";
        $params[':aluno_id'] = $alunoId;
    }

    $sql .= " LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function feedbackSalvarAvaliacao(
    PDO $pdo,
    int $companyId,
    int $usuarioId,
    string $nome,
    int $nota,
    string $mensagem
): void {
    $stmt = $pdo->prepare("
        INSERT INTO portfolio_feedbacks (
            company_id,
            user_id,
            nome,
            nota,
            mensagem,
            ativo
        )
        VALUES (
            :company_id,
            :user_id,
            :nome,
            :nota,
            :mensagem,
            0
        )
        ON DUPLICATE KEY UPDATE
            nome = VALUES(nome),
            nota = VALUES(nota),
            mensagem = VALUES(mensagem),
            ativo = 0
    ");

    $stmt->execute([
        ':company_id' => $companyId,
        ':user_id'    => $usuarioId,
        ':nome'       => $nome,
        ':nota'       => $nota,
        ':mensagem'   => $mensagem,
    ]);
}

function feedbackBuscarDoUsuario(
    PDO $pdo,
    int $usuarioId,
    int $companyId
): ?array {
    $stmt = $pdo->prepare("
        SELECT
            id,
            nome,
            nota,
            mensagem,
            criado_em,
            ativo
        FROM portfolio_feedbacks
        WHERE user_id = :usuario_id
          AND company_id = :company_id
        LIMIT 1
    ");

    $stmt->execute([
        ':usuario_id' => $usuarioId,
        ':company_id' => $companyId,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
