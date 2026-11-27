<?php

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin', 'Professor']);

require_once __DIR__ . '/../models/Database.php';

$company_id = (int) ($_SESSION['company_id'] ?? 0);
$usuario_id = (int) ($_SESSION['usuario_id'] ?? 0);
$usuario_role = $_SESSION['usuario_role'] ?? '';

if ($company_id <= 0 || $usuario_id <= 0) {
    http_response_code(403);
    exit('Acesso não autorizado.');
}

$tituloPagina = 'Fichas de Treino';

$cssEspecifico = BASE_URL . '/assets/css/fichatreino.css';

$urlFicha = BASE_URL . '/app/controllers/TreinoController.php';

$perfilProfessor = $usuario_role === 'Professor';

$mensagemSucesso = $_SESSION['treino_sucesso'] ?? '';
$mensagemErro = '';

unset($_SESSION['treino_sucesso']);

if (empty($_SESSION['csrf_treino'])) {
    $_SESSION['csrf_treino'] = bin2hex(random_bytes(32));
}

$csrfTreino = $_SESSION['csrf_treino'];

$texto = static function ($valor) {
    return is_scalar($valor)
        ? trim((string) $valor)
        : '';
};

// ALUNOS

$operacao = 'listar_alunos_treino';
require __DIR__ . '/../models/Treino.php';

$idsAlunos = array_map(
    'intval',
    array_column($alunos, 'id')
);

// ALUNO SELECIONADO

$aluno_id = (int) ($_GET['aluno_id'] ?? 0);

if (!in_array($aluno_id, $idsAlunos, true)) {
    $aluno_id = 0;
}

// HISTÓRICO - COMPATIBILIDADE COM AJAX

if (($_GET['acao'] ?? '') === 'historico') {

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    if ($aluno_id <= 0) {

        http_response_code(404);

        echo json_encode([
            'erro' => 'Aluno não encontrado.'
        ]);

        exit;
    }

    try {

        $operacao = 'listar_fichas_aluno';

        require __DIR__ . '/../models/Treino.php';

        $historico = [];

        foreach ($fichas as $ficha) {

            $ficha_id = (int) $ficha['id'];

            $operacao = 'buscar_itens_ficha';

            require __DIR__ . '/../models/Treino.php';

            $ficha['ficha_id'] = $ficha_id;
            $ficha['itens'] = $itens;

            $historico[] = $ficha;
        }

        echo json_encode(
            $historico,
            JSON_UNESCAPED_UNICODE |
            JSON_INVALID_UTF8_SUBSTITUTE
        );

    } catch (Throwable $e) {

        error_log('[GymFlow][Treinos] ' . $e->getMessage());

        http_response_code(500);

        echo json_encode([
            'erro' => 'Não foi possível carregar as fichas.'
        ]);
    }

    exit;
}

// ESTADO DO EDITOR

$modoEditor = false;
$fichaForm = null;

// PROCESSAR FORMULÁRIO

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $acao = $texto($_POST['acao'] ?? '');

    $aluno_id = (int) ($_POST['aluno'] ?? 0);
    $ficha_origem_id = (int) ($_POST['ficha_origem_id'] ?? 0);

    $professor_id = (int) ($_POST['professor_id'] ?? 0);

    $nome_ficha = $texto($_POST['nome_ficha'] ?? '');
    $objetivo = $texto($_POST['objetivo'] ?? '');

    $itens = $_POST['itens'] ?? [];

    if (
        $acao === 'salvar' &&
        in_array($aluno_id, $idsAlunos, true)
    ) {

        $modoEditor = true;

        $fichaForm = [
            'id' => $ficha_origem_id,
            'professor_id' => $professor_id,
            'nome_ficha' => $nome_ficha,
            'objetivo' => $objetivo,
            'itens' => is_array($itens)
                ? array_values($itens)
                : []
        ];
    }

    try {

        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            !hash_equals($csrfTreino, $token)
        ) {
            throw new RuntimeException(
                'Formulário inválido. Atualize a página.'
            );
        }

        if (!in_array($aluno_id, $idsAlunos, true)) {
            throw new RuntimeException('Aluno inválido.');
        }

        if (!in_array($acao, ['salvar', 'excluir'], true)) {
            throw new RuntimeException('Operação inválida.');
        }

        $operacao = $acao === 'excluir'
            ? 'excluir_ficha'
            : 'salvar_ficha';

        require __DIR__ . '/../models/Treino.php';

        if ($acao === 'excluir') {

            $_SESSION['treino_sucesso'] =
                'Ficha excluída com sucesso.';

        } elseif ($ficha_origem_id > 0) {

            $_SESSION['treino_sucesso'] =
                'Ficha atualizada com sucesso.';

        } else {

            $_SESSION['treino_sucesso'] =
                'Ficha cadastrada com sucesso.';
        }

        header(
            'Location: ' . $urlFicha .
            '?aluno_id=' . $aluno_id
        );

        exit;

    } catch (PDOException $e) {

        error_log('[GymFlow][Treinos] ' . $e->getMessage());

        $mensagemErro =
            'Não foi possível concluir a operação.';

    } catch (RuntimeException $e) {

        $mensagemErro = $e->getMessage();

    } catch (Throwable $e) {

        error_log('[GymFlow][Treinos] ' . $e->getMessage());

        $mensagemErro = 'Ocorreu um erro inesperado.';
    }
}

// GARANTIR ALUNO AUTORIZADO

if (!in_array($aluno_id, $idsAlunos, true)) {

    $aluno_id = 0;
    $modoEditor = false;
    $fichaForm = null;
}

$alunoSelecionado = $aluno_id;
$nomeAlunoSelecionado = '';

foreach ($alunos as $registroAluno) {

    if ((int) $registroAluno['id'] === $alunoSelecionado) {

        $nomeAlunoSelecionado = $registroAluno['nome'];
        break;
    }
}

// LISTAR FICHAS

$operacao = 'listar_fichas_aluno';

require __DIR__ . '/../models/Treino.php';

// ABRIR NOVA FICHA OU EDITAR EXISTENTE

if (
    ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' &&
    $alunoSelecionado > 0
) {

    $editarId = (int) ($_GET['editar'] ?? 0);

    if ($editarId > 0) {

        foreach ($fichas as $registroFicha) {

            if ((int) $registroFicha['id'] !== $editarId) {
                continue;
            }

            $ficha_id = $editarId;

            $operacao = 'buscar_itens_ficha';

            require __DIR__ . '/../models/Treino.php';

            $fichaForm = $registroFicha;
            $fichaForm['itens'] = $itens;

            if ($perfilProfessor) {

                $fichaForm['professor_id'] = $usuario_id;
            }

            $modoEditor = true;

            break;
        }

        if (!$modoEditor) {

            $mensagemErro = 'Ficha não encontrada.';
        }

    } elseif (($_GET['novo'] ?? '') === '1') {

        $modoEditor = true;

        $fichaForm = [
            'id' => 0,
            'professor_id' => $perfilProfessor
                ? $usuario_id
                : 0,
            'nome_ficha' => '',
            'objetivo' => '',
            'itens' => []
        ];
    }
}

// PROFESSORES

$operacao = 'listar_professores_treino';
require __DIR__ . '/../models/Treino.php';

// BIBLIOTECA DE EXERCÍCIOS
// Somente consulta. Nenhuma alteração na biblioteca.

$operacao = 'listar_exercicios_ativos';
require __DIR__ . '/../models/Treino.php';

// VIEW

require __DIR__ . '/../views/treinos/index.php';