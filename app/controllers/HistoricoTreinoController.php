<?php
// =============================================
// GYMFLOW - CONTROLLER DE HISTÓRICO
// app/controllers/HistoricoTreinoController.php
// =============================================

require_once __DIR__ .
    '/../../config/sessao.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

function responderHistorico(
    array $dados,
    int $status = 200
): void {

    http_response_code($status);

    echo json_encode(
        $dados,
        JSON_UNESCAPED_UNICODE |
            JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

// =============================================
// VALIDAR SESSÃO DO ALUNO
// =============================================

$empresa = (int) (
    $_SESSION['company_id'] ?? 0
);

$aluno = (int) (
    $_SESSION['aluno_id'] ?? 0
);

$usuario = (int) (
    $_SESSION['usuario_id'] ?? 0
);

$role = $_SESSION['usuario_role'] ?? '';

if (
    $empresa <= 0 ||
    $aluno <= 0 ||
    $usuario <= 0 ||
    $role !== 'Aluno'
) {

    responderHistorico([
        'sucesso' => false,
        'mensagem' => 'Acesso restrito ao aluno.'
    ], 403);
}

try {

    require_once __DIR__ .
        '/../models/Database.php';

    require_once __DIR__ .
        '/../models/HistoricoTreino.php';

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    if (!historicoAutorizar(
        $pdo,
        $empresa,
        $aluno,
        $usuario
    )) {

        responderHistorico([
            'sucesso' => false,
            'mensagem' => 'Aluno não autorizado.'
        ], 403);
    }

    $metodo = $_SERVER['REQUEST_METHOD'] ?? '';

    // =========================================
    // CONSULTAR FREQUÊNCIA DO DIA
    // =========================================

    if ($metodo === 'GET') {

        responderHistorico([

            'sucesso' => true,

            'frequencia' => obterFrequenciaHoje(
                $pdo,
                $empresa,
                $aluno
            )

        ]);
    }

    // =========================================
    // REGISTRAR EXERCÍCIO
    // =========================================

    if ($metodo !== 'POST') {

        header('Allow: GET, POST');

        responderHistorico([
            'sucesso' => false,
            'mensagem' => 'Método não permitido.'
        ], 405);
    }

    $csrfSessao =
        $_SESSION['historico_treino_csrf'] ?? '';

    $csrfRecebido =
        $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (
        !is_string($csrfSessao) ||
        $csrfSessao === '' ||
        !is_string($csrfRecebido) ||
        !hash_equals(
            $csrfSessao,
            $csrfRecebido
        )
    ) {

        responderHistorico([
            'sucesso' => false,
            'mensagem' => 'Sessão inválida. Atualize a página.'
        ], 403);
    }

    $conteudo = file_get_contents(
        'php://input',
        false,
        null,
        0,
        131073
    );

    if (
        $conteudo === false ||
        strlen($conteudo) > 131072
    ) {

        responderHistorico([
            'sucesso' => false,
            'mensagem' => 'Requisição inválida ou muito grande.'
        ], 413);
    }

    $dados = json_decode(
        $conteudo,
        true,
        32,
        JSON_THROW_ON_ERROR
    );

    if (!is_array($dados)) {

        responderHistorico([
            'sucesso' => false,
            'mensagem' => 'Dados inválidos.'
        ], 400);
    }

    $resultado = registrarExercicioHistorico(
        $pdo,
        $empresa,
        $aluno,
        $dados
    );

    $frequencia = obterFrequenciaHoje(
        $pdo,
        $empresa,
        $aluno
    );

    responderHistorico([

        'sucesso' => true,

        'mensagem' => 'Exercício registrado com sucesso.',

        'registro' => $resultado,

        'frequencia' => $frequencia

    ]);
} catch (JsonException $erro) {

    responderHistorico([
        'sucesso' => false,
        'mensagem' => 'Formato dos dados inválido.'
    ], 400);
} catch (InvalidArgumentException $erro) {

    responderHistorico([
        'sucesso' => false,
        'mensagem' => $erro->getMessage()
    ], 422);
} catch (DomainException $erro) {

    responderHistorico([
        'sucesso' => false,
        'mensagem' => $erro->getMessage()
    ], 409);
} catch (Throwable $erro) {

    error_log(
        '[GymFlow - Histórico] ' .
            $erro->getMessage()
    );

    responderHistorico([
        'sucesso' => false,
        'mensagem' => 'Não foi possível acessar o histórico. Tente novamente.'
    ], 500);
}
