<?php
// config/sessao.php

// Inicializa a sessão de forma segura se ainda não estiver ativa
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Gymflow');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lógica do "Lembrar de mim"
if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['gymflow_remember'])) {
    require_once __DIR__ . '/conexao.php';
    $token = $_COOKIE['gymflow_remember'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario) {
        // Loga automaticamente
        $_SESSION['usuario_id']    = $usuario['id'];
        $_SESSION['company_id']    = $usuario['company_id'];
        $_SESSION['usuario_nome']  = $usuario['name'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_role']  = $usuario['role'];
        $_SESSION['aluno_id']      = $usuario['aluno_id'] ?? null;
        
        // Simula preferências para não quebrar o header, ou busca real
        $stmtPref = $pdo->prepare("SELECT * FROM preferencias_usuario WHERE user_id = ? LIMIT 1");
        $stmtPref->execute([$usuario['id']]);
        $preferencias = $stmtPref->fetch(PDO::FETCH_ASSOC);
        if ($preferencias) {
            $_SESSION['nome_painel'] = $preferencias['nome_painel'];
            $_SESSION['tema'] = $preferencias['tema'];
            $_SESSION['cor_primaria'] = $preferencias['cor_primaria'];
            $_SESSION['cor_secundaria'] = $preferencias['cor_secundaria'];
            $_SESSION['tema_predefinido'] = $preferencias['tema_predefinido'];
            $_SESSION['logo_url'] = $preferencias['logo_url'];
        } else {
            $_SESSION['nome_painel'] = 'Gymflow';
            $_SESSION['tema'] = 'dark';
            $_SESSION['cor_primaria'] = '#ffb000';
            $_SESSION['cor_secundaria'] = '#000000';
            $_SESSION['tema_predefinido'] = 'padrao';
            $_SESSION['logo_url'] = null;
        }
    }
}

/**
 * Registra os dados do usuário autenticado na sessão ativa do PHP.
 *
 * @param array $usuario Array associativo contendo os dados do usuário vindos do banco de dados.
 */
function iniciarSessao($usuario) {
    $_SESSION['usuario_id']    = $usuario['id'];
    $_SESSION['usuario_nome']  = $usuario['name'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_role']  = $usuario['role'];
    $_SESSION['aluno_id']      = $usuario['aluno_id'] ?? null;
}

/**
 * Impede o acesso de usuários não autenticados.
 * Se o usuário não possuir sessão ativa, é redirecionado para a tela de login.
 */
function verificarLogado() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: " . BASE_URL . "/app/controllers/LoginController.php?acao=login");
        exit;
    }
}

/**
 * Controla o acesso às páginas baseado no cargo (role) do usuário.
 * Se o usuário logado tentar acessar uma área não permitida, é redirecionado para a página adequada.
 *
 * @param array $rolesPermitidas Lista de strings contendo os perfis autorizados (ex: ['Admin', 'Recepcao']).
 */
function verificarRole(array $rolesPermitidas) {
    verificarLogado();
    
    if (!in_array($_SESSION['usuario_role'], $rolesPermitidas)) {
        // Redirecionamento de segurança caso tente acessar módulo indevido
        if ($_SESSION['usuario_role'] === 'Aluno') {
            header("Location: " . BASE_URL . "/app/controllers/PortalAlunoController.php?acao=aluno");
        } else {
            header("Location: " . BASE_URL . "/app/controllers/DashboardController.php");
        }
        exit;
    }
}

/**
 * Encerra a sessão atual de forma limpa, limpando variáveis e cookies de sessão, e redireciona ao login.
 */
function efetuarLogout() {
    // Remove token do banco se houver cookie
    if (isset($_COOKIE['gymflow_remember'])) {
        require_once __DIR__ . '/conexao.php';
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE remember_token = ?");
        $stmt->execute([$_COOKIE['gymflow_remember']]);
        setcookie('gymflow_remember', '', time() - 3600, "/");
    }

    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: " . BASE_URL . "/app/controllers/LoginController.php?acao=login");
    exit;
}
