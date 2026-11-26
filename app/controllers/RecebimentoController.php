<?php
// app/controllers/RecebimentoController.php

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Recepcao']);

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../models/Recebimento.php';

$url = BASE_URL .
    '/app/controllers/RecebimentoController.php';

$tituloPagina = 'Recebimentos';

$empresaId = (int) $_SESSION['company_id'];
$usuarioId = (int) $_SESSION['usuario_id'];

$identidade = $empresaId . ':' . $usuarioId;

$chave = 'recebimento_operacao';

$texto = static fn($v) =>
    is_string($v) ? trim($v) : '';

$inteiro = static fn($v) =>
    (int) filter_var(
        $v,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

$novoEstado = static fn() => [
    'identidade' => $identidade,
    'csrf' => bin2hex(random_bytes(32)),
    'tipo' => 'cpf',
    'busca' => '',
    'aluno_id' => 0,
    'matricula_id' => 0,
    'conta_id' => 0,
    'metodo' => '',
    'referencia' => ''
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

$redirecionar = static function (
    array $estado
) use ($chave, $url): void {

    $_SESSION[$chave] = $estado;

    header('Location: ' . $url, true, 303);
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
                $estado['metodo'] = '';
                $estado['referencia'] = '';

                $id = $inteiro($_POST['conta_id'] ?? 0);

                if (!$id || !$estado['aluno_id']) {
                    throw new RecebimentoErro(
                        'Selecione uma cobrança válida.'
                    );
                }

                $model->consultarConta(
                    (int) $estado['aluno_id'],
                    (int) $estado['matricula_id'],
                    $id
                );

                $estado['conta_id'] = $id;

            } elseif ($acao === 'confirmar') {

                $id = $inteiro($_POST['conta_id'] ?? 0);

                if (
                    !$id ||
                    $id !== (int) $estado['conta_id'] ||
                    !$estado['aluno_id']
                ) {
                    throw new RecebimentoErro(
                        'Selecione novamente a cobrança.'
                    );
                }

                $metodo = $texto($_POST['metodo'] ?? '');
                $referencia = $texto($_POST['referencia'] ?? '');

                if (!in_array(
                    $metodo,
                    ['pix', 'credito', 'debito'],
                    true
                )) {
                    throw new RecebimentoErro(
                        'Para pagamentos em dinheiro, utilize o Caixa físico.'
                    );
                }

                $estado['metodo'] = $metodo;
                $estado['referencia'] = $referencia;

                if (($_POST['verificado'] ?? '') !== '1') {
                    throw new RecebimentoErro(
                        'Confirme que verificou o recebimento.'
                    );
                }

                $model->confirmar(
                    (int) $estado['aluno_id'],
                    (int) $estado['matricula_id'],
                    $id,
                    $metodo,
                    $referencia
                );

                $estado['conta_id'] = 0;
                $estado['metodo'] = '';
                $estado['referencia'] = '';

                $estado['sucesso'] =
                    'Pagamento confirmado com sucesso.';

            } elseif ($acao === 'voltar') {

                $estado['conta_id'] = 0;
                $estado['metodo'] = '';
                $estado['referencia'] = '';

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
                '[GymFlow][Recebimentos] ' .
                get_class($e) . ' | ' . $e->getCode()
            );

            $estado['erro'] =
                'Não foi possível concluir o recebimento.';
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

                    // Dinheiro é exclusivo da aba Caixa físico.

                    unset($conta['metodos']['dinheiro']);

                    if (
                        !isset(
                            $conta['metodos'][$estado['metodo']]
                        )
                    ) {
                        $estado['metodo'] = '';
                    }

                } catch (RecebimentoErro | CaixaFisicoErro $e) {

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
        '[GymFlow][Recebimentos] ' .
        get_class($e) . ' | ' . $e->getCode()
    );

    $erro = 'Não foi possível carregar os recebimentos.';
}

$_SESSION[$chave] = $estado;

$csrf = $estado['csrf'];

require __DIR__ . '/../views/recebimentos/index.php';