
<?php
// config/sessao.php

if (!defined('BASE_URL')) {
    define('BASE_URL', '/Gymflow');
}

// =====================================================
// 1. CONFIGURAÇÕES DE SEGURANÇA DA SESSÃO
// =====================================================

if (session_status() === PHP_SESSION_NONE) {

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    $https = !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// =====================================================
// 2. REGISTRAR USUÁRIO NA SESSÃO
// =====================================================

function iniciarSessao(array $usuario): void
{
    if (
        empty($usuario['id']) ||
        empty($usuario['company_id']) ||
        empty($usuario['role'])
    ) {
        throw new RuntimeException(
            'Dados de autenticação incompletos.'
        );
    }

    // Evita reutilizar o ID de uma sessão anterior.
    session_regenerate_id(true);

    $_SESSION['usuario_id'] = (int) $usuario['id'];
    $_SESSION['company_id'] = (int) $usuario['company_id'];
    $_SESSION['usuario_nome'] = $usuario['name'] ?? '';
    $_SESSION['usuario_email'] = $usuario['email'] ?? '';
    $_SESSION['usuario_role'] = $usuario['role'];
    $_SESSION['aluno_id'] = $usuario['aluno_id'] ?? null;
}

// =====================================================
// 3. CARREGAR PREFERÊNCIAS DO USUÁRIO
// =====================================================

function carregarPreferenciasSessao(
    PDO $pdo,
    int $usuarioId
): void {

    $stmt = $pdo->prepare("
        SELECT *
        FROM preferencias_usuario
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$usuarioId]);

    $preferencias = $stmt->fetch(PDO::FETCH_ASSOC);

    $_SESSION['nome_painel'] =
        $preferencias['nome_painel'] ?? 'Gymflow';

    $_SESSION['tema'] =
        $preferencias['tema'] ?? 'dark';

    $_SESSION['cor_primaria'] =
        $preferencias['cor_primaria'] ?? '#ffb000';

    $_SESSION['cor_secundaria'] =
        $preferencias['cor_secundaria'] ?? '#000000';

    $_SESSION['tema_predefinido'] =
        $preferencias['tema_predefinido'] ?? 'padrao';

    $_SESSION['logo_url'] =
        $preferencias['logo_url'] ?? null;
}

// =====================================================
// 4. LOGIN AUTOMÁTICO - LEMBRAR DE MIM
// =====================================================

// Mantém a estrutura atual do banco.
// O armazenamento de hash e expiração dos tokens
// será implementado em uma etapa posterior.

if (
    !isset($_SESSION['usuario_id']) &&
    !empty($_COOKIE['gymflow_remember'])
) {

    require_once __DIR__ . '/conexao.php';

    $token = $_COOKIE['gymflow_remember'];

    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE remember_token = ?
        LIMIT 1
    ");

    $stmt->execute([$token]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {

        iniciarSessao($usuario);

        carregarPreferenciasSessao(
            $pdo,
            (int) $usuario['id']
        );
    }
}

// =====================================================
// 5. VERIFICAR SE ESTÁ LOGADO
// =====================================================

function verificarLogado(): void
{
    $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
    $empresaId = (int) ($_SESSION['company_id'] ?? 0);
    $role = $_SESSION['usuario_role'] ?? '';

    if (
        $usuarioId <= 0 ||
        $empresaId <= 0 ||
        !is_string($role) ||
        $role === ''
    ) {
        header(
            "Location: " . BASE_URL .
                "/app/controllers/LoginController.php?acao=login"
        );
        exit;
    }
}

// =====================================================
// 6. VERIFICAR PERMISSÕES POR PERFIL
// =====================================================

function verificarRole(array $rolesPermitidas): void
{
    verificarLogado();

    $role = $_SESSION['usuario_role'] ?? '';

    if (!in_array($role, $rolesPermitidas, true)) {

        if ($role === 'Aluno') {

            header(
                "Location: " . BASE_URL .
                    "/app/controllers/PortalAlunoController.php?acao=aluno"
            );
        } else {

            header(
                "Location: " . BASE_URL .
                    "/app/controllers/DashboardController.php"
            );
        }

        exit;
    }
}

// =====================================================
// 7. OBTENÇÃO DO CONTEXTO DA EMPRESA
// =====================================================

// Deve ser usado apenas após verificarLogado().
// O ID identifica o tenant autenticado.
// Controllers e models ainda precisam validar
// a propriedade dos recursos consultados.

function obterEmpresaLogada(): int
{
    verificarLogado();

    return (int) $_SESSION['company_id'];
}

// =====================================================
// 8. ENCERRAR SESSÃO
// =====================================================

function efetuarLogout(): void
{
    if (!empty($_COOKIE['gymflow_remember'])) {

        require_once __DIR__ . '/conexao.php';

        $stmt = $pdo->prepare("
            UPDATE users
            SET remember_token = NULL
            WHERE remember_token = ?
        ");

        $stmt->execute([
            $_COOKIE['gymflow_remember']
        ]);
    }

    $https = !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off';

    // Apaga o cookie de login automático.
    setcookie('gymflow_remember', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    $_SESSION = [];

    // Apaga o cookie de sessão PHP.
    if (ini_get('session.use_cookies')) {

        $params = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax'
        ]);
    }

    session_destroy();

    header(
        "Location: " . BASE_URL .
            "/app/controllers/LoginController.php?acao=login"
    );

    exit;
}
