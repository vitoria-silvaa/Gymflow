<?php
// config/sessao.php

// Inicializa a sessão de forma segura se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
        header("Location: /Gymflow/app/controllers/LoginController.php?acao=login");
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
            header("Location: /Gymflow/app/controllers/PortalAlunoController.php?acao=aluno");
        } else {
            header("Location: /Gymflow/app/controllers/DashboardController.php");
        }
        exit;
    }
}

/**
 * Encerra a sessão atual de forma limpa, limpando variáveis e cookies de sessão, e redireciona ao login.
 */
function efetuarLogout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: /Gymflow/app/controllers/LoginController.php?acao=login");
    exit;
}
