<?php
// ========================================
// GYMFLOW - PORTAL DO ALUNO
// app/controllers/PortalAlunoController.php
// ========================================

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Aluno']);

$baseUrl = BASE_URL .
    '/app/controllers/PortalAlunoController.php';

$acao = $_GET['acao'] ?? 'aluno';

// ========================================
// IDENTIFICAÇÃO DO ALUNO
// ========================================

$aluno_id = filter_var(
    $_SESSION['aluno_id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$aluno_id || $aluno_id <= 0) {

    $_SESSION = [];

    session_destroy();

    header(
        'Location: ' . BASE_URL .
            '/app/controllers/LoginController.php?acao=login'
    );

    exit;
}

$aluno_id = (int) $aluno_id;

// ========================================
// 1. HOME
// ========================================

if ($acao === 'aluno') {

    $usuarioId = (int) (
        $_SESSION['usuario_id'] ?? 0
    );

    $empresaId = (int) (
        $_SESSION['company_id'] ?? 0
    );

    if (
        $usuarioId <= 0 ||
        $empresaId <= 0
    ) {

        http_response_code(403);

        exit('Sessão inválida.');
    }

    header('Cache-Control: no-store, private');

    try {

        require_once __DIR__ .
            '/../models/InicioAluno.php';

        $inicioPortal = consultarInicioAluno(

            $pdo,

            $usuarioId,

            $aluno_id,

            $empresaId
        );

        if (!$inicioPortal) {

            http_response_code(403);

            exit('Não foi possível validar seu vínculo ' .
                'com esta academia.');
        }

        require __DIR__ .
            '/../views/portal_aluno/aluno.php';

        exit;
    } catch (Throwable $e) {

        error_log(
            '[GymFlow][InicioAluno] ' .
                $e->getMessage()
        );

        http_response_code(500);

        exit('Não foi possível carregar o portal. ' .
            'Tente novamente.');
    }
}
// =============================================
// MEUS DADOS
// =============================================

elseif ($acao === 'meus_dados') {

    require __DIR__ . '/MeusDadosAlunoController.php';

    exit;
}
// ================================================
// MINHAS MEDIDAS
// ================================================

elseif ($acao === 'minhas_medidas') {

    require __DIR__ . '/MedidasAlunoController.php';

    exit;
}
// ========================================
// 2. FATURAS
// ========================================

elseif ($acao === 'faturas') {

    $operacao = 'listar_faturas_aluno';

    require __DIR__ . '/../models/Financeiro.php';

    $hoje = date('Y-m-d');

    $a_vencer = [];
    $em_atraso = [];
    $pagas = [];

    foreach ($todas_contas as $conta) {

        if ($conta['status'] === 'Pago') {

            $pagas[] = $conta;
        } elseif ($conta['vencimento'] < $hoje) {

            $em_atraso[] = $conta;
        } else {

            $a_vencer[] = $conta;
        }
    }

    $aba = $_GET['aba'] ?? 'vencer';

    if (!in_array(
        $aba,
        ['vencer', 'atraso', 'pagas'],
        true
    )) {

        $aba = 'vencer';
    }

    require __DIR__ . '/../views/portal_aluno/faturas.php';

    exit;
}

// ========================================
// 3. TREINOS
// ========================================

elseif ($acao === 'treinos') {

    header('Cache-Control: no-store, private');

    require_once __DIR__ . '/../models/Database.php';
    require_once __DIR__ . '/../models/HistoricoTreino.php';

    try {

        if (!historicoAutorizar(
            $pdo,
            (int) ($_SESSION['company_id'] ?? 0),
            $aluno_id,
            (int) ($_SESSION['usuario_id'] ?? 0)
        )) {

            http_response_code(403);

            exit('Não foi possível validar seu vínculo com esta academia.');
        }
    } catch (Throwable $e) {

        error_log(
            '[GymFlow][PortalTreinos] ' .
                $e->getMessage()
        );

        http_response_code(500);

        exit('Não foi possível carregar os treinos.');
    }

    $operacao = 'listar_fichas_aluno';

    require __DIR__ . '/../models/Treino.php';

    $itens = [];
    $ficha_atual = null;
    $ficha_id_selecionada = null;

    if (isset($_GET['ficha'])) {

        $fichaSolicitada = filter_var(
            $_GET['ficha'],
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1
                ]
            ]
        );

        if (!$fichaSolicitada) {

            header(
                'Location: ' . $baseUrl .
                    '?acao=treinos'
            );

            exit;
        }
    } else {

        $fichaSolicitada = (int) (
            $fichas[0]['id'] ?? 0
        );
    }

    foreach ($fichas as $f) {

        if ((int) $f['id'] === $fichaSolicitada) {

            $ficha_atual = $f;

            $ficha_id_selecionada = (int) $f['id'];

            break;
        }
    }

    if (
        isset($_GET['ficha']) &&
        !$ficha_atual
    ) {

        header(
            'Location: ' . $baseUrl .
                '?acao=treinos'
        );

        exit;
    }

    if ($ficha_atual !== null) {

        $ficha_id = $ficha_id_selecionada;

        $operacao = 'buscar_itens_ficha';

        require __DIR__ . '/../models/Treino.php';
    }

    require __DIR__ . '/../views/portal_aluno/treinos.php';

    exit;
}

// ========================================
// 4. CONTRATOS
// ========================================

elseif (
    in_array(
        $acao,
        [
            'contratos',
            'baixar_contrato',
            'imprimir_contrato'
        ],
        true
    )
) {

    $respostaJson = $acao === 'baixar_contrato';

    header('Cache-Control: no-store, private');

    if ($respostaJson) {

        header('Content-Type: application/json; charset=utf-8');
    }

    // Função para tratar erros deste módulo.

    $erroContrato = static function (
        int $status,
        string $mensagem
    ) use ($respostaJson): void {

        http_response_code($status);

        if ($respostaJson) {

            echo json_encode(
                [
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ],
                JSON_UNESCAPED_UNICODE
            );
        } else {

            echo htmlspecialchars(
                $mensagem,
                ENT_QUOTES,
                'UTF-8'
            );
        }

        exit;
    };

    $usuarioId = filter_var(
        $_SESSION['usuario_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $empresaId = filter_var(
        $_SESSION['company_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        !$usuarioId ||
        !$empresaId ||
        $usuarioId <= 0 ||
        $empresaId <= 0
    ) {

        $erroContrato(403, 'Sessão inválida.');
    }

    require_once __DIR__ . '/../models/Database.php';

    require_once __DIR__ . '/../models/ContratoAluno.php';

    try {

        // LISTAR CONTRATOS

        if ($acao === 'contratos') {

            $contratos = consultarContratosPortal(
                $pdo,
                (int) $usuarioId,
                $aluno_id,
                (int) $empresaId
            );

            require __DIR__ .
                '/../views/portal_aluno/contratos.php';

            exit;
        }

        // VALIDAR ID DO CONTRATO

        $contratoId = filter_var(
            $_GET['id'] ?? null,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1
                ]
            ]
        );

        if (!$contratoId) {

            $erroContrato(400, 'Contrato inválido.');
        }

        // BUSCAR CONTRATO AUTORIZADO

        $resultado = consultarContratosPortal(
            $pdo,
            (int) $usuarioId,
            $aluno_id,
            (int) $empresaId,
            (int) $contratoId
        );

        $contrato = $resultado[0] ?? null;

        if (!$contrato) {

            $erroContrato(
                404,
                'Contrato não encontrado ou indisponível.'
            );
        }

        // ====================================
        // DOWNLOAD DIRETO - DADOS PARA O PDF
        // ====================================

        if ($acao === 'baixar_contrato') {

            $dataGeracao = new DateTimeImmutable(
                'now',
                new DateTimeZone('America/Sao_Paulo')
            );

            $contrato['gerado_em'] = $dataGeracao->format(
                'd/m/Y H:i:s'
            );

            echo json_encode(

                [
                    'sucesso' => true,

                    'contrato' => $contrato,

                    'clausulas' => obterClausulasContratoAluno()
                ],

                JSON_UNESCAPED_UNICODE |
                    JSON_INVALID_UTF8_SUBSTITUTE

            );

            exit;
        }

        // ====================================
        // IMPRESSÃO LEGADA
        // ====================================

        require __DIR__ .
            '/../views/portal_aluno/imprimir_contrato.php';

        exit;
    } catch (Throwable $e) {

        error_log(
            '[GymFlow][Contratos] ' .
                $e->getMessage()
        );

        $erroContrato(
            500,
            'Não foi possível carregar o contrato.'
        );
    }
}

// ========================================
// 5. MINHA AVALIAÇÃO
// ========================================

elseif ($acao === 'avaliacao') {

    $usuario_id = filter_var(
        $_SESSION['usuario_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $company_id = filter_var(
        $_SESSION['company_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        !$usuario_id ||
        !$company_id ||
        $usuario_id <= 0 ||
        $company_id <= 0
    ) {

        http_response_code(403);

        exit('Sessão inválida. Faça login novamente.');
    }

    $usuario_id = (int) $usuario_id;

    $company_id = (int) $company_id;

    require_once __DIR__ . '/../models/Database.php';
    require_once __DIR__ . '/../models/Feedback.php';

    try {

        $dadosAlunoAvaliacao = feedbackBuscarAlunoAutorizado(
            $pdo,
            $usuario_id,
            $company_id,
            $aluno_id
        );

        if (!$dadosAlunoAvaliacao) {

            http_response_code(403);

            exit('Não foi possível validar seu vínculo ' .
                'com esta academia.');
        }

        $minhaAvaliacao = feedbackBuscarDoUsuario(
            $pdo,
            $usuario_id,
            $company_id
        );

        $tituloPagina = 'Minha Avaliação';

        $nomeAluno =
            $dadosAlunoAvaliacao['nome_usuario'];

        $nomeFilial =
            $dadosAlunoAvaliacao['nome_filial'];

        require __DIR__ .
            '/../views/portal_aluno/avaliacao.php';

        exit;
    } catch (Throwable $e) {

        error_log(
            '[GymFlow][Avaliacao] Erro ao consultar ' .
                'avaliação do usuário ' .
                $usuario_id . ': ' .
                $e->getMessage()
        );

        http_response_code(500);

        exit('Não foi possível carregar sua avaliação. ' .
            'Tente novamente.');
    }
}

// ========================================
// 6. AÇÃO DESCONHECIDA
// ========================================

else {

    header(
        'Location: ' . $baseUrl .
            '?acao=aluno'
    );

    exit;
}
