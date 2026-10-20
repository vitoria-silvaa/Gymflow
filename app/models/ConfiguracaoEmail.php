
<?php

require_once __DIR__ . '/Database.php';

/**
 * Carrega a chave privada usada para proteger as senhas SMTP.
 * A chave deve ser hexadecimal, com 64 caracteres (32 bytes).
 */
function gymflowCarregarChaveCriptografiaEmail(): string
{
    $arquivo = __DIR__ . '/../../config/email.encryption.local.php';

    if (!is_file($arquivo)) {
        throw new RuntimeException(
            'Arquivo local da chave de criptografia não encontrado.'
        );
    }

    $config = require $arquivo;
    $chaveHex = $config['key'] ?? '';

    if (
        !is_string($chaveHex) ||
        !preg_match('/\A[a-f0-9]{64}\z/i', $chaveHex)
    ) {
        throw new RuntimeException(
            'A chave de criptografia deve conter 64 caracteres hexadecimais.'
        );
    }

    $chave = hex2bin($chaveHex);

    if ($chave === false || strlen($chave) !== 32) {
        throw new RuntimeException('Chave de criptografia inválida.');
    }

    return $chave;
}

/**
 * Criptografa uma senha SMTP usando AES-256-GCM.
 */
function gymflowCriptografarSenhaSmtp(string $senha): string
{
    $chave = gymflowCarregarChaveCriptografiaEmail();
    $iv = random_bytes(12);
    $tag = '';

    $cifra = openssl_encrypt(
        $senha,
        'aes-256-gcm',
        $chave,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '',
        16
    );

    if ($cifra === false || strlen($tag) !== 16) {
        throw new RuntimeException('Não foi possível proteger a senha SMTP.');
    }

    // Formato: versão + IV + tag de autenticação + conteúdo criptografado.
    return 'v1:' . base64_encode($iv . $tag . $cifra);
}

/**
 * Descriptografa uma senha SMTP para uso interno no envio de e-mails.
 */
function gymflowDescriptografarSenhaSmtp(string $senhaCriptografada): string
{
    if (strpos($senhaCriptografada, 'v1:') !== 0) {
        throw new RuntimeException('Formato da senha SMTP não reconhecido.');
    }

    $conteudo = base64_decode(substr($senhaCriptografada, 3), true);

    if ($conteudo === false || strlen($conteudo) < 29) {
        throw new RuntimeException('Conteúdo da senha SMTP inválido.');
    }

    $iv = substr($conteudo, 0, 12);
    $tag = substr($conteudo, 12, 16);
    $cifra = substr($conteudo, 28);

    $senha = openssl_decrypt(
        $cifra,
        'aes-256-gcm',
        gymflowCarregarChaveCriptografiaEmail(),
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        ''
    );

    if ($senha === false) {
        throw new RuntimeException(
            'Não foi possível descriptografar a senha SMTP.'
        );
    }

    return $senha;
}

/**
 * Busca as configurações de uma empresa.
 * A senha nunca é devolvida para a página.
 */
function buscarConfiguracaoEmail(PDO $pdo, int $empresaId): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            usar_personalizado,
            email_remetente,
            nome_remetente,
            smtp_host,
            smtp_porta,
            smtp_seguranca,
            smtp_usuario,
            CASE
                WHEN smtp_senha_criptografada IS NOT NULL
                     AND smtp_senha_criptografada <> ''
                THEN 1
                ELSE 0
            END AS tem_senha_smtp,
            modo_cores_email,
            cor_email_fundo,
            cor_email_cartao,
            cor_email_destaque,
            cor_email_botao,
            cor_email_texto_botao
        FROM configuracoes_email
        WHERE company_id = :company_id
        LIMIT 1
    ");

    $stmt->execute([
        ':company_id' => $empresaId,
    ]);

    $configuracao = $stmt->fetch(PDO::FETCH_ASSOC);

    return $configuracao ?: null;
}

/**
 * Salva as configurações da empresa.
 * Senha vazia preserva a senha criptografada anteriormente.
 */
function salvarConfiguracaoEmail(
    PDO $pdo,
    int $empresaId,
    int $usuarioId,
    array $dados
): void {
    // Recupera a senha atual sem descriptografá-la.
    $stmtAtual = $pdo->prepare("
        SELECT smtp_senha_criptografada
        FROM configuracoes_email
        WHERE company_id = :company_id
        LIMIT 1
    ");

    $stmtAtual->execute([
        ':company_id' => $empresaId,
    ]);

    $senhaAtual = $stmtAtual->fetchColumn();

    if ($senhaAtual === false) {
        $senhaAtual = null;
    }

    $senhaInformada = $dados['smtp_senha'] ?? '';

    if (!is_string($senhaInformada)) {
        throw new InvalidArgumentException('Senha SMTP inválida.');
    }

    if (trim($senhaInformada) !== '') {
        $senhaCriptografada = gymflowCriptografarSenhaSmtp(
            $senhaInformada
        );
    } else {
        $senhaCriptografada = $senhaAtual;
    }

    $stmt = $pdo->prepare("
        INSERT INTO configuracoes_email (
            company_id,
            usar_personalizado,
            email_remetente,
            nome_remetente,
            smtp_host,
            smtp_porta,
            smtp_seguranca,
            smtp_usuario,
            smtp_senha_criptografada,
            modo_cores_email,
            cor_email_fundo,
            cor_email_cartao,
            cor_email_destaque,
            cor_email_botao,
            cor_email_texto_botao,
            atualizado_por
        ) VALUES (
            :company_id,
            :usar_personalizado,
            :email_remetente,
            :nome_remetente,
            :smtp_host,
            :smtp_porta,
            :smtp_seguranca,
            :smtp_usuario,
            :smtp_senha_criptografada,
            :modo_cores_email,
            :cor_email_fundo,
            :cor_email_cartao,
            :cor_email_destaque,
            :cor_email_botao,
            :cor_email_texto_botao,
            :atualizado_por
        )
        ON DUPLICATE KEY UPDATE
            usar_personalizado = VALUES(usar_personalizado),
            email_remetente = VALUES(email_remetente),
            nome_remetente = VALUES(nome_remetente),
            smtp_host = VALUES(smtp_host),
            smtp_porta = VALUES(smtp_porta),
            smtp_seguranca = VALUES(smtp_seguranca),
            smtp_usuario = VALUES(smtp_usuario),
            smtp_senha_criptografada =
                VALUES(smtp_senha_criptografada),
            modo_cores_email = VALUES(modo_cores_email),
            cor_email_fundo = VALUES(cor_email_fundo),
            cor_email_cartao = VALUES(cor_email_cartao),
            cor_email_destaque = VALUES(cor_email_destaque),
            cor_email_botao = VALUES(cor_email_botao),
            cor_email_texto_botao = VALUES(cor_email_texto_botao),
            atualizado_por = VALUES(atualizado_por)
    ");

    $stmt->execute([
        ':company_id' => $empresaId,
        ':usar_personalizado' => (int) ($dados['usar_personalizado'] ?? 0),
        ':email_remetente' => $dados['email_remetente'] ?? null,
        ':nome_remetente' => $dados['nome_remetente'] ?? null,
        ':smtp_host' => $dados['smtp_host'] ?? null,
        ':smtp_porta' => $dados['smtp_porta'] ?? 587,
        ':smtp_seguranca' => $dados['smtp_seguranca'] ?? 'tls',
        ':smtp_usuario' => $dados['smtp_usuario'] ?? null,
        ':smtp_senha_criptografada' => $senhaCriptografada,
        ':modo_cores_email' => $dados['modo_cores_email'] ?? 'personalizado',
        ':cor_email_fundo' => $dados['cor_email_fundo'] ?? '#0b0b0b',
        ':cor_email_cartao' => $dados['cor_email_cartao'] ?? '#151515',
        ':cor_email_destaque' => $dados['cor_email_destaque'] ?? '#ffb000',
        ':cor_email_botao' => $dados['cor_email_botao'] ?? '#ffb000',
        ':cor_email_texto_botao' => $dados['cor_email_texto_botao'] ?? '#151515',
        ':atualizado_por' => $usuarioId,
    ]);
}
