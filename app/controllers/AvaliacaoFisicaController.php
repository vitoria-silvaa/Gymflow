<?php
// ================================================
// GYMFLOW - CONTROLLER DE AVALIAÇÕES FÍSICAS
// app/controllers/AvaliacaoFisicaController.php
// ================================================

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Professor']);

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/AvaliacaoFisica.php';

header('Cache-Control: no-store, private');

// ================================================
// 1. CONTEXTO DA SESSÃO
// ================================================

$professorId = (int) (
    $_SESSION['usuario_id'] ?? 0
);

$empresaId = (int) (
    $_SESSION['company_id'] ?? 0
);

if ($professorId <= 0 || $empresaId <= 0) {

    http_response_code(403);

    exit('Acesso não autorizado.');
}

$urlAvaliacaoFisica = BASE_URL .
    '/app/controllers/AvaliacaoFisicaController.php';

$hoje = (
    new DateTimeImmutable(
        'now',
        new DateTimeZone('America/Sao_Paulo')
    )
)->format('Y-m-d');

$camposAvaliacao = camposAvaliacaoFisica();

// ================================================
// 2. PROTEÇÃO CSRF
// ================================================

if (
    empty($_SESSION['avaliacao_fisica_csrf']) ||
    !is_string($_SESSION['avaliacao_fisica_csrf'])
) {

    $_SESSION['avaliacao_fisica_csrf'] =
        bin2hex(random_bytes(32));
}

$csrfAvaliacaoFisica =
    $_SESSION['avaliacao_fisica_csrf'];

// ================================================
// 3. VALIDAR MÉTODO
// ================================================

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!in_array($metodo, ['GET', 'POST'], true)) {

    header('Allow: GET, POST');

    http_response_code(405);

    exit('Método não permitido.');
}

try {

    // ============================================
    // 4. LISTAR ALUNOS AUTORIZADOS
    // ============================================

    $alunosAvaliacao = listarAlunosAvaliacaoFisica(
        $pdo,
        $professorId,
        $empresaId
    );

    $origemAluno = $metodo === 'POST'
        ? $_POST
        : $_GET;

    $alunoSelecionadoId = filter_var(
        $origemAluno['aluno_id'] ?? null,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ]
        ]
    );

    $alunoSelecionadoId =
        (int) $alunoSelecionadoId;

    if (
        array_key_exists('aluno_id', $origemAluno) &&
        $alunoSelecionadoId <= 0
    ) {

        http_response_code(400);

        exit('Aluno inválido.');
    }

    $alunoSelecionado = null;

    // Confirma se o aluno está na lista autorizada.

    foreach ($alunosAvaliacao as $aluno) {

        if (
            (int) $aluno['id'] ===
            $alunoSelecionadoId
        ) {

            $alunoSelecionado = $aluno;

            break;
        }
    }

    if (
        $alunoSelecionadoId > 0 &&
        !$alunoSelecionado
    ) {

        http_response_code(404);

        exit('Aluno não encontrado ou não autorizado.');
    }

    // ============================================
    // 5. SALVAR AVALIAÇÃO
    // ============================================

    if ($metodo === 'POST') {

        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            !hash_equals(
                $csrfAvaliacaoFisica,
                $token
            )
        ) {

            http_response_code(403);

            exit('Solicitação inválida. Atualize a página.');
        }

        if (!$alunoSelecionado) {

            http_response_code(400);

            exit('Selecione um aluno.');
        }

        try {

            $dadosAvaliacao = validarDadosAvaliacaoFisica(
                $_POST,
                $hoje
            );

            cadastrarAvaliacaoFisica(
                $pdo,
                $professorId,
                $empresaId,
                $alunoSelecionadoId,
                $dadosAvaliacao
            );

            $_SESSION['avaliacao_fisica_mensagem'] = [

                'tipo' => 'sucesso',

                'texto' =>
                'Avaliação física registrada com sucesso!'
            ];

            unset(
                $_SESSION['avaliacao_fisica_formulario']
            );
        } catch (DomainException $e) {

            $_SESSION['avaliacao_fisica_mensagem'] = [

                'tipo' => 'erro',

                'texto' => $e->getMessage()
            ];

            // Preserva somente os campos permitidos.

            $rascunho = [
                'aluno_id' => $alunoSelecionadoId
            ];

            foreach (
                array_merge(
                    ['data'],
                    array_keys($camposAvaliacao)
                ) as $campo
            ) {

                $valor = $_POST[$campo] ?? '';

                if (is_string($valor)) {

                    $rascunho[$campo] =
                        substr($valor, 0, 30);
                }
            }

            $_SESSION['avaliacao_fisica_formulario'] =
                $rascunho;
        } catch (Throwable $e) {

            error_log(
                '[GymFlow][AvaliacaoFisica] ' .
                    $e->getMessage()
            );

            $_SESSION['avaliacao_fisica_mensagem'] = [

                'tipo' => 'erro',

                'texto' =>
                'Não foi possível salvar a avaliação.'
            ];
        }

        header(
            'Location: ' .
                $urlAvaliacaoFisica .
                '?aluno_id=' .
                $alunoSelecionadoId,
            true,
            303
        );

        exit;
    }

    // ============================================
    // 6. CONSULTAR AVALIAÇÕES ANTERIORES
    // ============================================

    $historicoAvaliacoes = [];

    if ($alunoSelecionado) {

        $historicoAvaliacoes =
            listarAvaliacoesFisicasProfessor(
                $pdo,
                $professorId,
                $empresaId,
                $alunoSelecionadoId
            );
    }

    // ============================================
    // 7. MENSAGENS E VALORES DO FORMULÁRIO
    // ============================================

    $mensagemAvaliacaoFisica =
        $_SESSION['avaliacao_fisica_mensagem'] ?? null;

    $rascunhoAvaliacao =
        $_SESSION['avaliacao_fisica_formulario'] ?? [];

    unset(
        $_SESSION['avaliacao_fisica_mensagem'],
        $_SESSION['avaliacao_fisica_formulario']
    );

    if (
        (int) ($rascunhoAvaliacao['aluno_id'] ?? 0)
        !== $alunoSelecionadoId
    ) {

        $rascunhoAvaliacao = [];
    }

    // ============================================
    // 8. EXIBIR PÁGINA
    // ============================================

    require __DIR__ .
        '/../views/avaliacoes_fisicas/index.php';

    exit;
} catch (Throwable $e) {

    error_log(
        '[GymFlow][AvaliacaoFisica] ' .
            $e->getMessage()
    );

    http_response_code(500);

    exit('Não foi possível carregar as avaliações físicas.');
}
