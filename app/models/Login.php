<?php
// =============================================
// GYMFLOW - MODEL DE AUTENTICAÇÃO E RECUPERAÇÃO
// app/models/Login.php
// =============================================

function loginSalvarRememberToken(
    PDO $pdo,
    int $userId,
    string $token
): void {
    $stmt = $pdo->prepare(
        "UPDATE users
         SET remember_token = ?
         WHERE id = ?"
    );
    $stmt->execute([$token, $userId]);
}

function loginBuscarMarcaEmpresa(
    PDO $pdo,
    int $empresaId
): ?array {
    $stmt = $pdo->prepare("
        SELECT
            c.id,
            pc.app_name,
            pc.primary_color,
            pc.secondary_color
        FROM companies c
        LEFT JOIN portfolio_config pc
            ON pc.company_id = c.id
        WHERE c.id = ?
        LIMIT 1
    ");
    $stmt->execute([$empresaId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function loginBuscarUsuarioPorEmailRecuperacao(
    PDO $pdo,
    string $email
): ?array {
    $stmt = $pdo->prepare(
        "SELECT id, email, company_id
         FROM users
         WHERE email = ?
         LIMIT 1"
    );
    $stmt->execute([$email]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function loginBuscarCoresEmail(
    PDO $pdo,
    int $empresaId
): ?array {
    $stmt = $pdo->prepare("
        SELECT
            ce.modo_cores_email,
            ce.cor_email_fundo,
            ce.cor_email_cartao,
            ce.cor_email_destaque,
            ce.cor_email_botao,
            ce.cor_email_texto_botao,
            p.cor_primaria,
            p.cor_secundaria
        FROM configuracoes_email ce
        LEFT JOIN preferencias_usuario p
            ON p.user_id = ce.atualizado_por
        WHERE ce.company_id = ?
        LIMIT 1
    ");
    $stmt->execute([$empresaId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function loginSalvarResetToken(
    PDO $pdo,
    int $userId,
    string $token,
    string $expires
): void {
    $stmt = $pdo->prepare(
        "UPDATE users
         SET reset_token = ?, reset_token_expires_at = ?
         WHERE id = ?"
    );
    $stmt->execute([$token, $expires, $userId]);
}

function loginBuscarSenhaSmtpCriptografada(
    PDO $pdo,
    int $empresaId
): ?string {
    $stmt = $pdo->prepare(
        'SELECT smtp_senha_criptografada
         FROM configuracoes_email
         WHERE company_id = ?
         LIMIT 1'
    );
    $stmt->execute([$empresaId]);
    $valor = $stmt->fetchColumn();

    return is_string($valor) ? $valor : null;
}

function loginInvalidarResetToken(
    PDO $pdo,
    int $userId
): void {
    $stmt = $pdo->prepare(
        "UPDATE users
         SET reset_token = NULL,
             reset_token_expires_at = NULL
         WHERE id = ?"
    );
    $stmt->execute([$userId]);
}

function loginBuscarUsuarioPorResetToken(
    PDO $pdo,
    string $token
): ?array {
    $stmt = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE reset_token = ?
           AND reset_token_expires_at > NOW()"
    );
    $stmt->execute([$token]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function loginAtualizarSenhaReset(
    PDO $pdo,
    int $userId,
    string $hash
): void {
    $stmt = $pdo->prepare(
        "UPDATE users
         SET password = ?,
             reset_token = NULL,
             reset_token_expires_at = NULL
         WHERE id = ?"
    );
    $stmt->execute([$hash, $userId]);
}
