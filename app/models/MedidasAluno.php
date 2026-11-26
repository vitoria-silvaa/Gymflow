<?php
// ================================================
// GYMFLOW - MODEL DE MEDIDAS DO ALUNO
// app/models/MedidasAluno.php
// ================================================

require_once __DIR__ . '/AvaliacaoFisica.php';

// ================================================
// 1. VALIDAR VÍNCULO DO ALUNO
// ================================================

function medidasAlunoAutorizar(
    PDO $pdo,
    int $usuarioId,
    int $alunoId,
    int $empresaId
): bool {

    $stmt = $pdo->prepare("
        SELECT a.id

        FROM alunos a

        INNER JOIN filiais f
            ON f.id = a.filial_id

        INNER JOIN users u
            ON u.aluno_id = a.id
            AND u.company_id = f.company_id

        WHERE a.id = :aluno_id
          AND u.id = :usuario_id
          AND f.company_id = :empresa_id
          AND u.role = 'Aluno'

        LIMIT 1
    ");

    $stmt->execute([
        ':aluno_id' => $alunoId,
        ':usuario_id' => $usuarioId,
        ':empresa_id' => $empresaId
    ]);

    return $stmt->fetchColumn() !== false;
}

// ================================================
// 2. CONSULTAR AVALIAÇÕES FÍSICAS
// ================================================

function consultarAvaliacoesMedidasAluno(
    PDO $pdo,
    int $usuarioId,
    int $alunoId,
    int $empresaId
): array {

    $stmt = $pdo->prepare("
        SELECT
            av.id,
            av.data,
            av.peso,
            av.altura,
            av.gordura,
            av.massa_magra,
            av.braco,
            av.peitoral,
            av.abdomen,
            av.cintura,
            av.quadril,
            av.coxa,
            av.panturrilha

        FROM avaliacoes av

        INNER JOIN alunos a
            ON a.id = av.aluno_id

        INNER JOIN filiais f
            ON f.id = a.filial_id

        INNER JOIN users u
            ON u.aluno_id = a.id
            AND u.company_id = f.company_id

        WHERE a.id = :aluno_id
          AND u.id = :usuario_id
          AND f.company_id = :empresa_id
          AND u.role = 'Aluno'

        ORDER BY av.data DESC, av.id DESC
    ");

    $stmt->execute([
        ':aluno_id' => $alunoId,
        ':usuario_id' => $usuarioId,
        ':empresa_id' => $empresaId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
