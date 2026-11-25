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

    $alunoId = $aluno_id;

    $operacao = 'dados_portal_aluno';

    require __DIR__ . '/../models/Aluno.php';

    require __DIR__ . '/../views/portal_aluno/aluno.php';

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

    $operacao = 'listar_fichas_aluno';

    require __DIR__ . '/../models/Treino.php';

    $itens = [];
    $ficha_atual = null;
    $ficha_id_selecionada = null;

    // Seleciona a ficha solicitada ou a mais recente.

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

    // Confirma que a ficha pertence ao aluno.

    foreach ($fichas as $f) {

        if ((int) $f['id'] === $fichaSolicitada) {

            $ficha_atual = $f;

            $ficha_id_selecionada = (int) $f['id'];

            break;
        }
    }

    // Uma ficha desconhecida não pode ser consultada.

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

    // Consulta exclusivamente a ficha validada.

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

elseif ($acao === 'contratos') {

    $operacao = 'listar_contratos';

    require __DIR__ . '/../models/Aluno.php';

    require __DIR__ . '/../views/portal_aluno/contratos.php';

    exit;
}

// ========================================
// 5. IMPRIMIR CONTRATO
// ========================================

elseif ($acao === 'imprimir_contrato') {

    $contrato_id = (int) ($_GET['id'] ?? 0);

    $operacao = 'buscar_contrato_impressao';

    require __DIR__ . '/../models/Aluno.php';

    if (!$contrato) {

        header(
            'Location: ' . $baseUrl .
                '?acao=contratos'
        );

        exit;
    }

    require __DIR__ .
        '/../views/portal_aluno/imprimir_contrato.php';

    exit;
}

// ========================================
// 6. MINHA AVALIAÇÃO
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

    try {

        // VALIDAR VÍNCULO DO ALUNO

        $stmtAluno = $pdo->prepare("
            SELECT
                u.id AS usuario_id,
                u.name AS nome_usuario,
                u.company_id,
                a.id AS aluno_id,
                f.id AS filial_id,
                f.nome AS nome_filial

            FROM users u

            INNER JOIN alunos a
                ON a.id = u.aluno_id

            INNER JOIN filiais f
                ON f.id = a.filial_id

            WHERE u.id = :usuario_id
              AND u.aluno_id = :aluno_id
              AND u.company_id = :company_id
              AND f.company_id = :company_id_filial
              AND u.role = 'Aluno'

            LIMIT 1
        ");

        $stmtAluno->execute([
            ':usuario_id' => $usuario_id,
            ':aluno_id' => $aluno_id,
            ':company_id' => $company_id,
            ':company_id_filial' => $company_id
        ]);

        $dadosAlunoAvaliacao =
            $stmtAluno->fetch(PDO::FETCH_ASSOC);

        if (!$dadosAlunoAvaliacao) {

            http_response_code(403);

            exit('Não foi possível validar seu vínculo ' .
                'com esta academia.');
        }

        // BUSCAR AVALIAÇÃO DO ALUNO

        $stmtAvaliacao = $pdo->prepare("
            SELECT
                id,
                nome,
                nota,
                mensagem,
                criado_em,
                ativo

            FROM portfolio_feedbacks

            WHERE user_id = :usuario_id
              AND company_id = :company_id

            LIMIT 1
        ");

        $stmtAvaliacao->execute([
            ':usuario_id' => $usuario_id,
            ':company_id' => $company_id
        ]);

        $minhaAvaliacao =
            $stmtAvaliacao->fetch(PDO::FETCH_ASSOC) ?: null;

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
// 7. AÇÃO DESCONHECIDA
// ========================================

else {

    header(
        'Location: ' . $baseUrl .
            '?acao=aluno'
    );

    exit;
}
