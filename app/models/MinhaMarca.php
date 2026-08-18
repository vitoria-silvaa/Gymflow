<?php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';
$erroModel = '';

// 1. BUSCAR PREFERÊNCIAS DO USUÁRIO
if ($operacao === 'buscar') {

    $user_id = $user_id ?? null;

    if (!$user_id) {
        $erroModel = 'Usuário não informado.';
        return;
    }

    $stmt = $pdo->prepare("
        SELECT *
        FROM preferencias_usuario
        WHERE user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        ':user_id' => $user_id
    ]);

    $preferencias = $stmt->fetch() ?: null;
}


// 2. SALVAR PREFERÊNCIAS DO USUÁRIO
elseif ($operacao === 'salvar') {

    $user_id = $user_id ?? null;

    $nome_painel = trim($dados['nome_painel'] ?? 'Gymflow');
    $tema = trim($dados['tema'] ?? 'light');
    $cor_primaria = trim($dados['cor_primaria'] ?? '#ffb000');
    $cor_secundaria = trim($dados['cor_secundaria'] ?? '#000000');
    $tema_predefinido = trim($dados['tema_predefinido'] ?? 'padrao');
    $logo_url = trim($dados['logo_url'] ?? '');

    if (!$user_id) {
        $erroModel = 'Usuário não informado.';
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO preferencias_usuario (
            user_id,
            nome_painel,
            tema,
            cor_primaria,
            cor_secundaria,
            tema_predefinido,
            logo_url
        )
        VALUES (
            :user_id,
            :nome_painel,
            :tema,
            :cor_primaria,
            :cor_secundaria,
            :tema_predefinido,
            :logo_url
        )
        ON DUPLICATE KEY UPDATE
            nome_painel = VALUES(nome_painel),
            tema = VALUES(tema),
            cor_primaria = VALUES(cor_primaria),
            cor_secundaria = VALUES(cor_secundaria),
            tema_predefinido = VALUES(tema_predefinido),
            logo_url = VALUES(logo_url)
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':nome_painel' => $nome_painel,
        ':tema' => $tema,
        ':cor_primaria' => $cor_primaria,
        ':cor_secundaria' => $cor_secundaria,
        ':tema_predefinido' => $tema_predefinido,
        ':logo_url' => $logo_url
    ]);
}
