<?php
// app/controllers/CaixaFisicoController.php

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin', 'Recepcao']);

if ($_SESSION['usuario_role'] === 'Admin') {
    header(
        'Location: ' . BASE_URL .
        '/app/controllers/FinanceiroController.php?aba=dinheiro'
    );
    exit;
}

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../models/Recebimento.php';

$tituloPagina = 'Caixa físico';

$baseUrl = BASE_URL .
    '/app/controllers/CaixaFisicoController.php';

$empresaId = (int) $_SESSION['company_id'];
$usuarioId = (int) $_SESSION['usuario_id'];

$identidade = $empresaId . ':' . $usuarioId;

$chave = 'caixa_recebimentos';

$novoEstado = static fn() => [
    'identidade' => $identidade,
    'csrf' => bin2hex(random_bytes(32)),
    'tipo' => 'cpf',
    'busca' => '',
    'aluno_id' => 0,
    'matricula_id' => 0,
    'conta_id' => 0
];

if (
    ($_SESSION[$chave]['identidade'] ?? '') !== $identidade
) {
    $_SESSION[$chave] = $novoEstado();
}

$estado = $_SESSION[$chave];

$erro = $estado['erro'] ?? '';
$sucesso = $estado['sucesso'] ?? '';

unset($estado['erro'], $estado['sucesso']);

$aluno = null;
$contas = [];
$conta = null;

$texto = static fn($v) =>
    is_string($v) ? trim($v) : '';

$redirecionar = static function (
    array $estado
) use ($chave, $baseUrl): void {
    $_SESSION[$chave] = $estado;

    header('Location: ' . $baseUrl, true, 303);
    exit;
};

try {

    $model = new RecebimentoModel(
        $pdo,
        $empresaId,
        $usuarioId
    );

    $requisicao = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if (!in_array($requisicao, ['GET', 'POST'], true)) {
        http_response_code(405);
        exit;
    }

    if ($requisicao === 'POST') {

        $token = $_POST['csrf'] ?? null;

        if (
            !is_string($token) ||
            !hash_equals($estado['csrf'], $token)
        ) {
            $estado['erro'] =
                'Atualize a página e tente novamente.';

            $redirecionar($estado);
        }

        $estado['csrf'] = bin2hex(random_bytes(32));

        $acao = $texto($_POST['acao'] ?? '');

        try {

            if ($acao === 'buscar') {

                $estado = $novoEstado();

                $tipo = $texto($_POST['tipo'] ?? '');
                $busca = $texto($_POST['busca'] ?? '');

                $estado['tipo'] = in_array(
                    $tipo,
                    ['cpf', 'nome', 'matricula'],
                    true
                ) ? $tipo : 'cpf';

                $estado['busca'] = $busca;

                $resultado = $model->buscarAluno(
                    $tipo,
                    $busca
                );

                $estado['aluno_id'] =
                    $resultado['aluno_id'];

                $estado['matricula_id'] =
                    $resultado['matricula_id'];

            } elseif ($acao === 'selecionar') {

                $estado['conta_id'] = 0;

                $id = filter_var(
                    $_POST['conta_id'] ?? 0,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );

                if (!$id || !$estado['aluno_id']) {
                    throw new RecebimentoErro(
                        'Selecione uma mensalidade válida.'
                    );
                }

                $selecionada = $model->consultarConta(
                    (int) $estado['aluno_id'],
                    (int) $estado['matricula_id'],
                    (int) $id
                );

                if (!isset($selecionada['metodos']['dinheiro'])) {
                    throw new RecebimentoErro(
                        'Esta cobrança deve ser recebida pelo método autorizado na matrícula.'
                    );
                }

                $estado['conta_id'] = (int) $id;

            } elseif ($acao === 'confirmar') {

                $id = filter_var(
                    $_POST['conta_id'] ?? 0,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );

                if (
                    !$id ||
                    $id !== (int) $estado['conta_id'] ||
                    !$estado['aluno_id']
                ) {
                    throw new RecebimentoErro(
                        'Selecione novamente a mensalidade.'
                    );
                }

                if (($_POST['confirmado'] ?? '') !== '1') {
                    throw new RecebimentoErro(
                        'Confirme que recebeu o dinheiro.'
                    );
                }

                $selecionada = $model->consultarConta(
                    (int) $estado['aluno_id'],
                    (int) $estado['matricula_id'],
                    (int) $id
                );

                if (!isset($selecionada['metodos']['dinheiro'])) {
                    throw new RecebimentoErro(
                        'Esta cobrança não aceita pagamento em dinheiro.'
                    );
                }

                $filialId =
                    (int) $selecionada['unidade_id'];

                // Inicialização interna com saldo zero.
                // Não representa receita nem exige preenchimento.

                $caixa = new CaixaFisicoModel(
                    $pdo,
                    $empresaId,
                    $usuarioId,
                    'Recepcao'
                );

                if (!$caixa->caixaAberto($filialId)) {

                    try {

                        $caixa->abrir($filialId, '0.00');

                    } catch (CaixaFisicoErro $e) {

                        if (!$caixa->caixaAberto($filialId)) {
                            throw $e;
                        }
                    }
                }

                // Utiliza o valor original da cobrança.
                // A baixa e a entrada em dinheiro são integradas.

                $model->confirmar(
                    (int) $estado['aluno_id'],
                    (int) $estado['matricula_id'],
                    (int) $id,
                    'dinheiro',
                    ''
                );

                $estado['conta_id'] = 0;

                $estado['sucesso'] =
                    'Pagamento em dinheiro registrado com sucesso.';

            } elseif ($acao === 'voltar') {

                $estado['conta_id'] = 0;

            } elseif ($acao === 'limpar') {

                $estado = $novoEstado();

            } else {

                throw new RecebimentoErro(
                    'Operação não autorizada.'
                );
            }

        } catch (RecebimentoErro | CaixaFisicoErro $e) {

            $estado['erro'] = $e->getMessage();

        } catch (Throwable $e) {

            error_log(
                '[GymFlow][Caixa] ' .
                get_class($e) .
                ' | ' . $e->getCode()
            );

            $estado['erro'] =
                'Não foi possível registrar o recebimento.';
        }

        $redirecionar($estado);
    }

    if ($estado['aluno_id']) {

        $aluno = $model->aluno(
            (int) $estado['aluno_id']
        );

        if (!$aluno) {

            $estado = $novoEstado();

            $erro = 'Aluno não encontrado ou não autorizado.';

        } else {

            $contas = $model->listarContas(
                (int) $estado['aluno_id'],
                (int) $estado['matricula_id']
            );

            if ($estado['conta_id']) {

                try {

                    $conta = $model->consultarConta(
                        (int) $estado['aluno_id'],
                        (int) $estado['matricula_id'],
                        (int) $estado['conta_id']
                    );

                    if (!isset($conta['metodos']['dinheiro'])) {
                        throw new RecebimentoErro(
                            'Esta cobrança não aceita dinheiro.'
                        );
                    }

                } catch (RecebimentoErro | CaixaFisicoErro $e) {

                    $conta = null;
                    $estado['conta_id'] = 0;
                    $erro = $e->getMessage();
                }
            }
        }
    }

} catch (RecebimentoErro | CaixaFisicoErro $e) {

    $erro = $e->getMessage();

} catch (Throwable $e) {

    error_log(
        '[GymFlow][Caixa] ' .
        get_class($e) .
        ' | ' . $e->getCode()
    );

    $erro = 'Não foi possível carregar os recebimentos.';
}

$_SESSION[$chave] = $estado;

$csrf = $estado['csrf'];

require __DIR__ . '/../views/caixa_fisico/index.php';