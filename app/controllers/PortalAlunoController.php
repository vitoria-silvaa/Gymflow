
<?php
// ========================================
// GYMFLOW - PORTAL DO ALUNO
// app/controllers/PortalAlunoController.php
// ========================================

require_once __DIR__ . '/../../config/sessao.php';

// Apenas alunos autenticados
verificarRole(['Aluno']);

$baseUrl = BASE_URL . '/app/controllers/PortalAlunoController.php';
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
        "Location: " . BASE_URL .
            "/app/controllers/LoginController.php?acao=login"
    );
    exit;
}

$aluno_id = (int) $aluno_id;

// ========================================
// 1. HOME DO PORTAL DO ALUNO
// ========================================

if ($acao === 'aluno') {

    $alunoId = $aluno_id;
    $operacao = 'dados_portal_aluno';

    require __DIR__ . '/../models/Aluno.php';

    require __DIR__ . '/../views/portal_aluno/aluno.php';
}

// ========================================
// 2. FATURAS DO ALUNO
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

    require __DIR__ . '/../views/portal_aluno/faturas.php';
}

// ========================================
// 3. TREINOS DO ALUNO
// ========================================

elseif ($acao === 'treinos') {

    $operacao = 'listar_fichas_aluno';

    require __DIR__ . '/../models/Treino.php';

    $ficha_id_selecionada =
        $_GET['ficha'] ?? ($fichas[0]['id'] ?? null);

    $itens = [];
    $ficha_atual = null;

    if ($ficha_id_selecionada) {

        foreach ($fichas as $f) {

            if ($f['id'] == $ficha_id_selecionada) {
                $ficha_atual = $f;
                break;
            }
        }

        $ficha_id = (int) $ficha_id_selecionada;

        $operacao = 'buscar_itens_ficha';

        require __DIR__ . '/../models/Treino.php';
    }

    require __DIR__ . '/../views/portal_aluno/treinos.php';
}

// ========================================
// 4. CONTRATOS DO ALUNO
// ========================================

elseif ($acao === 'contratos') {

    $aluno_id = (int) $_SESSION['aluno_id'];

    $operacao = 'listar_contratos';

    require __DIR__ . '/../models/Aluno.php';

    require __DIR__ . '/../views/portal_aluno/contratos.php';
}

// ========================================
// 5. IMPRIMIR CONTRATO
// ========================================

elseif ($acao === 'imprimir_contrato') {

    $contrato_id = (int) ($_GET['id'] ?? 0);

    $aluno_id = (int) $_SESSION['aluno_id'];

    $operacao = 'buscar_contrato_impressao';

    require __DIR__ . '/../models/Aluno.php';

    if (!$contrato) {

        header("Location: $baseUrl?acao=contratos");
        exit;
    }

    require __DIR__ . '/../views/portal_aluno/imprimir_contrato.php';
}

// ========================================
// 6. MINHA AVALIAÇÃO
// ========================================

elseif ($acao === 'avaliacao') {

    // A avaliação pertence ao usuário
    // autenticado e à sua academia.

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

        // Confere se o usuário é um aluno da
        // empresa e se sua filial pertence
        // à mesma empresa.

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

        // Busca a avaliação do próprio aluno,
        // exclusivamente na empresa da sessão.

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

        // Variáveis para a nova view.
        $tituloPagina = 'Minha Avaliação';

        $nomeAluno = $dadosAlunoAvaliacao['nome_usuario'];
        $nomeFilial = $dadosAlunoAvaliacao['nome_filial'];

        require __DIR__ .
            '/../views/portal_aluno/avaliacao.php';
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
// AÇÃO DESCONHECIDA
// ========================================

else {

    header("Location: $baseUrl?acao=aluno");
    exit;
}
