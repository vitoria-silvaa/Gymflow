
<?php
// ================================================
// GYMFLOW - CONTROLLER DE AVALIAÇÕES
// app/controllers/FeedbackController.php
// ================================================

require_once __DIR__ . '/../../config/sessao.php';
require_once __DIR__ . '/../models/Database.php';

// ================================================
// 1. ROTAS DE RETORNO
// ================================================

$portalUrl = BASE_URL .
    '/app/controllers/PortalAlunoController.php?acao=avaliacao';

// "portal" será enviado pelo formulário do aluno.
// O formulário público continuará usando "site".
$origem = ($_POST['origem'] ?? 'site') === 'portal'
    ? 'portal'
    : 'site';

$redirecionar = static function (
    string $status
) use ($origem, $portalUrl): void {

    if ($origem === 'portal') {
        header(
            'Location: ' . $portalUrl .
                '&avaliacao=' . urlencode($status)
        );
        exit;
    }

    $companyId = (int) ($_SESSION['company_id'] ?? 0);

    $url = BASE_URL . '/';

    if ($companyId > 0) {
        $url .= '?id=' . $companyId .
            '&feedback=' . urlencode($status);
    } else {
        $url .= '?feedback=' . urlencode($status);
    }

    header('Location: ' . $url . '#feedback');
    exit;
};

// ================================================
// 2. ACEITAR SOMENTE POST
// ================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}

// ================================================
// 3. EXIGIR SESSÃO AUTENTICADA
// ================================================

$usuarioId = filter_var(
    $_SESSION['usuario_id'] ?? null,
    FILTER_VALIDATE_INT
);

$companyId = filter_var(
    $_SESSION['company_id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$usuarioId) {
    header(
        'Location: ' . BASE_URL .
            '/app/controllers/LoginController.php?acao=login'
    );
    exit;
}

if (!$companyId || $companyId <= 0) {
    http_response_code(403);
    exit('Academia não identificada na sessão.');
}

$usuarioId = (int) $usuarioId;
$companyId = (int) $companyId;

// ================================================
// 4. PROTEÇÃO CSRF
// ================================================
//
// Os formulários do Portal do Aluno e do Site
// Público devem enviar um campo oculto chamado:
//
// csrf_token
//
// cujo valor é armazenado na sessão em:
//
// $_SESSION['feedback_csrf_token']
//
// Vamos adicionar esse campo nas views
// na próxima etapa.
// ================================================

$tokenSessao = $_SESSION['feedback_csrf_token'] ?? '';
$tokenFormulario = $_POST['csrf_token'] ?? '';

if (
    !is_string($tokenSessao) ||
    !is_string($tokenFormulario) ||
    $tokenSessao === '' ||
    $tokenFormulario === '' ||
    !hash_equals($tokenSessao, $tokenFormulario)
) {
    http_response_code(419);
    exit('Sessão de avaliação expirada ou inválida. ' .
        'Atualize a página e tente novamente.');
}

// ================================================
// 5. VALIDAR NOTA E COMENTÁRIO
// ================================================

$notaBruta = $_POST['nota'] ?? null;

$nota = filter_var(
    $notaBruta,
    FILTER_VALIDATE_INT
);

$mensagem = trim(
    (string) ($_POST['mensagem'] ?? '')
);

$tamanhoMensagem = function_exists('mb_strlen')
    ? mb_strlen($mensagem, 'UTF-8')
    : strlen($mensagem);

if (
    $nota === false ||
    $nota === null ||
    $nota < 1 ||
    $nota > 5 ||
    $mensagem === '' ||
    $tamanhoMensagem > 1000
) {
    $redirecionar('dados_invalidos');
}

// ================================================
// 6. VALIDAR USUÁRIO E EMPRESA NO BANCO
// ================================================

try {

    // Não confiamos somente no papel informado
    // pela sessão.
    //
    // O usuário precisa:
    // - existir no banco;
    // - ter papel Aluno;
    // - pertencer à empresa da sessão;
    // - estar vinculado a um cadastro de aluno;
    // - ter uma filial da mesma empresa.

    $stmtUsuario = $pdo->prepare("
        SELECT
            u.id,
            u.name,
            u.aluno_id,
            u.company_id

        FROM users u

        INNER JOIN alunos a
            ON a.id = u.aluno_id

        INNER JOIN filiais f
            ON f.id = a.filial_id

        WHERE u.id = :usuario_id
          AND u.company_id = :company_id
          AND f.company_id = :filial_company_id
          AND u.role = 'Aluno'

        LIMIT 1
    ");

    $stmtUsuario->execute([
        ':usuario_id'       => $usuarioId,
        ':company_id'       => $companyId,
        ':filial_company_id' => $companyId
    ]);

    $usuario = $stmtUsuario->fetch(
        PDO::FETCH_ASSOC
    );

    if (!$usuario) {
        http_response_code(403);
        exit('Somente alunos vinculados a esta ' .
            'academia podem enviar avaliações.');
    }

    // Se a sessão também identificar um aluno,
    // ela precisa corresponder ao registro real.

    if (
        isset($_SESSION['aluno_id']) &&
        (int) $_SESSION['aluno_id'] !==
        (int) $usuario['aluno_id']
    ) {
        http_response_code(403);
        exit('Vínculo do aluno inválido.');
    }

    // O nome é obtido do banco, não do POST.

    $nome = trim(
        (string) $usuario['name']
    );

    $tamanhoNome = function_exists('mb_strlen')
        ? mb_strlen($nome, 'UTF-8')
        : strlen($nome);

    if (
        $nome === '' ||
        $tamanhoNome > 100
    ) {
        $redirecionar('dados_invalidos');
    }

    // ============================================
    // 7. INSERIR OU ATUALIZAR A AVALIAÇÃO
    // ============================================
    //
    // UNIQUE(company_id, user_id) garante:
    //
    // - Uma avaliação por aluno/academia.
    // - Nova avaliação: INSERT.
    // - Avaliação existente: UPDATE.
    //
    // ativo = 0:
    // Toda avaliação nova ou editada
    // retorna para moderação.
    //
    // A coluna nome é atualizada com o nome
    // atual do cadastro do usuário.

    $stmtSalvar = $pdo->prepare("
        INSERT INTO portfolio_feedbacks (
            company_id,
            user_id,
            nome,
            nota,
            mensagem,
            ativo
        )
        VALUES (
            :company_id,
            :user_id,
            :nome,
            :nota,
            :mensagem,
            0
        )

        ON DUPLICATE KEY UPDATE
            nome = VALUES(nome),
            nota = VALUES(nota),
            mensagem = VALUES(mensagem),
            ativo = 0
    ");

    $stmtSalvar->execute([
        ':company_id' => $companyId,
        ':user_id'    => $usuarioId,
        ':nome'       => $nome,
        ':nota'       => $nota,
        ':mensagem'   => $mensagem
    ]);

    // ============================================
    // 8. REDIRECIONAR PARA A ORIGEM
    // ============================================

    $redirecionar('sucesso');
} catch (Throwable $e) {

    error_log(
        '[GymFlow][Feedback] Falha ao salvar avaliação. ' .
            'company_id=' . $companyId .
            '; user_id=' . $usuarioId .
            '; erro=' . $e->getMessage()
    );

    $redirecionar('erro');
}
