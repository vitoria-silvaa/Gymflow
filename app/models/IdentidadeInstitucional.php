<?php
// app/models/IdentidadeInstitucional.php

require_once __DIR__ . '/IdentidadePortalAluno.php';

/*
 * Carrega a identidade institucional
 * dos perfis Professor e Recepcao.
 */

function carregarIdentidadePainelInterno(PDO $pdo): array
{
    $role = $_SESSION['usuario_role'] ?? '';

    $usuarioId = (int) (
        $_SESSION['usuario_id'] ?? 0
    );

    $empresaId = (int) (
        $_SESSION['company_id'] ?? 0
    );

    /* VALIDAR CONTEXTO */

    if (
        !in_array(
            $role,
            ['Professor', 'Recepcao'],
            true
        ) ||
        $usuarioId <= 0 ||
        $empresaId <= 0
    ) {

        throw new RuntimeException(
            'Contexto institucional invalido.'
        );
    }

    /* CONSULTAR IDENTIDADE DA EMPRESA */

    $stmt = $pdo->prepare("
        SELECT
            pc.app_name,
            pc.primary_color,
            pc.secondary_color,
            pc.theme_mode,
            pc.logo_url,
            p.tema AS tema_usuario

        FROM users u

        INNER JOIN companies c
            ON c.id = u.company_id

        LEFT JOIN portfolio_config pc
            ON pc.company_id = c.id

        LEFT JOIN preferencias_usuario p
            ON p.user_id = u.id

        WHERE u.id = :usuario_id
          AND u.company_id = :company_id
          AND u.role = :role

        LIMIT 1
    ");

    $stmt->execute([
        ':usuario_id' => $usuarioId,
        ':company_id' => $empresaId,
        ':role' => $role
    ]);

    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$config) {

        throw new RuntimeException(
            'Vinculo institucional nao encontrado.'
        );
    }

    /* IDENTIDADE PADRAO */

    $identidade = identidadePadraoPortalAluno();

    /* NOME */

    $nome = trim(
        (string) ($config['app_name'] ?? '')
    );

    if ($nome !== '') {

        $identidade['nome_painel'] = $nome;
    }

    /* COR PRIMARIA */

    $identidade['cor_primaria'] =
        validarCorPortalAluno(
            $config['primary_color'] ?? null,
            $identidade['cor_primaria']
        );

    /* COR SECUNDARIA */

    $identidade['cor_secundaria'] =
        validarCorPortalAluno(
            $config['secondary_color'] ?? null,
            $identidade['cor_secundaria']
        );

    /* LOGOTIPO */

    $identidade['logo_url'] =
        validarLogoPortalAluno(
            $config['logo_url'] ?? null
        );

    /* TEMA INSTITUCIONAL */

    if (in_array(
        $config['theme_mode'] ?? null,
        ['light', 'dark'],
        true
    )) {

        $identidade['tema'] =
            $config['theme_mode'];
    }

    /* PREFERENCIA INDIVIDUAL DE TEMA */

    if (in_array(
        $config['tema_usuario'] ?? null,
        ['light', 'dark'],
        true
    )) {

        $identidade['tema'] =
            $config['tema_usuario'];
    }

    return $identidade;
}
