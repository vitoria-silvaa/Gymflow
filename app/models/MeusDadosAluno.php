<?php
// app/models/MeusDadosAluno.php

// =============================================
// CONSULTAR DADOS DO ALUNO
// =============================================

function consultarMeusDadosAluno(
    PDO $pdo,
    int $usuarioId,
    int $alunoId,
    int $empresaId
): ?array {

    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.nome,
            a.cpf,
            a.rg,
            a.sexo,
            a.nascimento,
            a.telefone,
            a.endereco,
            a.status,

            u.email AS email_acesso,

            f.nome AS nome_filial,

            professor.name AS nome_professor

        FROM alunos a

        INNER JOIN filiais f
            ON f.id = a.filial_id

        INNER JOIN users u
            ON u.aluno_id = a.id
            AND u.company_id = f.company_id

        LEFT JOIN users professor
            ON professor.id = a.professor_id
            AND professor.company_id = f.company_id
            AND professor.role = 'Professor'

        WHERE a.id = :aluno_id
          AND u.id = :usuario_id
          AND u.role = 'Aluno'
          AND f.company_id = :empresa_id

        LIMIT 1
    ");

    $stmt->execute([
        ':aluno_id' => $alunoId,
        ':usuario_id' => $usuarioId,
        ':empresa_id' => $empresaId
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// =============================================
// VALIDAR VÍNCULO ANTES DE ALTERAR OS DADOS
// =============================================

function validarVinculoMeusDados(
    PDO $pdo,
    int $usuarioId,
    int $alunoId,
    int $empresaId
): array {

    $stmt = $pdo->prepare("
        SELECT u.id, u.password

        FROM users u

        INNER JOIN alunos a
            ON a.id = u.aluno_id

        INNER JOIN filiais f
            ON f.id = a.filial_id
            AND f.company_id = u.company_id

        WHERE u.id = :usuario_id
          AND a.id = :aluno_id
          AND u.company_id = :empresa_id
          AND u.role = 'Aluno'

        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([
        ':usuario_id' => $usuarioId,
        ':aluno_id' => $alunoId,
        ':empresa_id' => $empresaId
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        throw new DomainException(
            'Não foi possível validar seu cadastro.'
        );
    }

    return $usuario;
}

// =============================================
// ATUALIZAR TELEFONE E ENDEREÇO
// =============================================

function atualizarContatoAluno(
    PDO $pdo,
    int $usuarioId,
    int $alunoId,
    int $empresaId,
    string $telefone,
    string $endereco
): void {

    $pdo->beginTransaction();

    try {

        validarVinculoMeusDados(
            $pdo,
            $usuarioId,
            $alunoId,
            $empresaId
        );

        // Atualizar cadastro do aluno.

        $stmt = $pdo->prepare("
            UPDATE alunos
            SET telefone = :telefone,
                endereco = :endereco
            WHERE id = :aluno_id
        ");

        $stmt->execute([
            ':telefone' => $telefone,
            ':endereco' => $endereco ?: null,
            ':aluno_id' => $alunoId
        ]);

        // Sincronizar conta de usuário.

        $stmt = $pdo->prepare("
            UPDATE users
            SET telefone = :telefone,
                endereco = :endereco
            WHERE id = :usuario_id
              AND aluno_id = :aluno_id
              AND company_id = :empresa_id
              AND role = 'Aluno'
        ");

        $stmt->execute([
            ':telefone' => $telefone,
            ':endereco' => $endereco ?: null,
            ':usuario_id' => $usuarioId,
            ':aluno_id' => $alunoId,
            ':empresa_id' => $empresaId
        ]);

        $pdo->commit();
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}

// =============================================
// ALTERAR SENHA DO ALUNO
// =============================================

function alterarSenhaMeusDados(
    PDO $pdo,
    int $usuarioId,
    int $alunoId,
    int $empresaId,
    string $senhaAtual,
    string $novaSenha
): void {

    $pdo->beginTransaction();

    try {

        $usuario = validarVinculoMeusDados(
            $pdo,
            $usuarioId,
            $alunoId,
            $empresaId
        );

        if (!password_verify(
            $senhaAtual,
            $usuario['password']
        )) {

            throw new DomainException(
                'A senha atual está incorreta.'
            );
        }

        if (password_verify(
            $novaSenha,
            $usuario['password']
        )) {

            throw new DomainException(
                'A nova senha deve ser diferente da atual.'
            );
        }

        $senhaHash = password_hash(
            $novaSenha,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare("
            UPDATE users

            SET password = :senha,
                remember_token = NULL,
                reset_token = NULL,
                reset_token_expires_at = NULL

            WHERE id = :usuario_id
              AND aluno_id = :aluno_id
              AND company_id = :empresa_id
              AND role = 'Aluno'
        ");

        $stmt->execute([
            ':senha' => $senhaHash,
            ':usuario_id' => $usuarioId,
            ':aluno_id' => $alunoId,
            ':empresa_id' => $empresaId
        ]);

        $pdo->commit();
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
