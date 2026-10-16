
<?php
// ========================================
// GYMFLOW - PORTFOLIO CONTROLLER
// app/controllers/PortfolioController.php
// ========================================

require_once __DIR__ . '/../../config/sessao.php';

// ========================================
// CONTROLE DE ACESSO
// ========================================

// Somente administradores podem gerenciar
// o Site / Portfólio.
verificarRole(['Admin']);

// ========================================
// IDENTIFICAÇÃO DA EMPRESA (MULTI-TENANT)
// ========================================

// A empresa deve obrigatoriamente vir da
// sessão autenticada.
//
// Nunca utilizar $_GET, $_POST ou um ID
// padrão para definir a empresa do Admin.

$company_id = filter_var(
    $_SESSION['company_id'] ?? null,
    FILTER_VALIDATE_INT
);

if (
    $company_id === false ||
    $company_id === null ||
    $company_id <= 0
) {
    http_response_code(403);

    exit('Acesso negado. Não foi possível identificar ' .
        'a academia associada à sua sessão.');
}

// ========================================
// CONFIGURAÇÕES GERAIS
// ========================================

$tituloPagina = "Site / Portfólio";

$acao = $_GET['acao'] ?? 'index';

$urlController = BASE_URL .
    '/app/controllers/PortfolioController.php';

// ========================================
// SALVAR ALTERAÇÕES
// ========================================

if ($acao === 'salvar') {

    // Impede salvar utilizando GET.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');

        exit('Método não permitido.');
    }

    // A empresa continua sendo exclusivamente
    // aquela identificada na sessão.
    $operacao = 'salvar';

    $dadosPost = $_POST;
    $dadosFiles = $_FILES;

    require __DIR__ . '/../models/Portfolio.php';

    if (!empty($erroModel)) {

        error_log(
            '[GymFlow][Portfolio] Falha ao salvar ' .
                'configurações da empresa ' .
                $company_id . ': ' .
                $erroModel
        );

        header(
            'Location: ' .
                $urlController .
                '?status=erro'
        );

        exit;
    }

    header(
        'Location: ' .
            $urlController .
            '?status=sucesso'
    );

    exit;
}

// ========================================
// EXIBIR PAINEL ADMINISTRATIVO
// ========================================

if (
    $acao === 'index' &&
    $_SERVER['REQUEST_METHOD'] === 'GET'
) {

    $operacao = 'buscar_admin';

    require __DIR__ . '/../models/Portfolio.php';

    if (!empty($erroModel)) {

        error_log(
            '[GymFlow][Portfolio] Falha ao consultar ' .
                'configurações da empresa ' .
                $company_id . ': ' .
                $erroModel
        );

        http_response_code(500);

        exit('Não foi possível carregar as configurações ' .
            'do site. Tente novamente.');
    }

    require __DIR__ . '/../views/portfolio/index.php';

    exit;
}

// ========================================
// AÇÃO NÃO RECONHECIDA
// ========================================

http_response_code(404);

exit('Ação não encontrada.');
