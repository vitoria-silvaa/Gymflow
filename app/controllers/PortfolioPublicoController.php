<?php
// ========================================
// GYMFLOW - CONTROLLER DO PORTFÓLIO PÚBLICO
// app/controllers/PortfolioPublicoController.php
// ========================================

require_once __DIR__ . '/../../config/sessao.php';

$company_id = (int) ($_GET['id'] ?? 1);
$operacao = 'buscar_publico';
require_once __DIR__ . '/../models/Portfolio.php';

$usuarioLogado = isset($_SESSION['usuario_id']);
$roleUsuario = $_SESSION['usuario_role'] ?? '';
$nomeUsuarioLogado = $usuarioLogado
    ? trim($_SESSION['usuario_nome'] ?? '')
    : '';

$tituloPagina = $config['app_name'] ?? 'GymCore';

// Dados de sessão para avaliação pública
$empresaDoSite = (int) ($company_id ?? 0);
$usuarioIdFeedback = (int) ($_SESSION['usuario_id'] ?? 0);
$empresaDoUsuario = (int) ($_SESSION['company_id'] ?? 0);
$papelUsuario = $_SESSION['usuario_role'] ?? '';

$ehAluno = $usuarioIdFeedback > 0 && $papelUsuario === 'Aluno';
$alunoDaAcademia = $ehAluno && $empresaDoSite > 0 && $empresaDoUsuario === $empresaDoSite;
$usuarioDeOutraAcademia = $ehAluno && $empresaDoSite > 0 && $empresaDoUsuario !== $empresaDoSite;

if ($alunoDaAcademia) {
    if (
        empty($_SESSION['feedback_csrf_token']) ||
        !is_string($_SESSION['feedback_csrf_token'])
    ) {
        $_SESSION['feedback_csrf_token'] = bin2hex(random_bytes(32));
    }
}

$statusFeedback = $_GET['feedback'] ?? '';
$nomeExibicao = trim((string) ($_SESSION['usuario_nome'] ?? ''));

$aceitaWellhub = !empty($config['accepts_wellhub']);
$aceitaTotalpass = !empty($config['accepts_totalpass']);
$wpPort = preg_replace('/\D/', '', $config['contact_phone'] ?? '');

require __DIR__ . '/../views/portfolio/publico.php';
