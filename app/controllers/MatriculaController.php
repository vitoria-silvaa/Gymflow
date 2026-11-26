<?php
// app/controllers/MatriculaController.php

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/../../config/sessao.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/Matricula.php';

$baseUrl = BASE_URL .
    '/app/controllers/MatriculaController.php';

$urlAlunos = BASE_URL .
    '/app/controllers/AlunoController.php?acao=listar';

$urlCrm = BASE_URL .
    '/app/controllers/CrmController.php';

$interno = ($_GET['modo'] ?? '') === 'interno';

$roleMatricula = '';
$usuarioMatricula = 0;
$empresaInterna = 0;

if ($interno) {

    verificarRole(['Admin', 'Recepcao']);

    $roleMatricula = $_SESSION['usuario_role'];
    $usuarioMatricula = (int) $_SESSION['usuario_id'];
    $empresaInterna = (int) $_SESSION['company_id'];
}

$identidade = $interno
    ? $empresaInterna . ':' .
        $usuarioMatricula . ':' .
        $roleMatricula
    : 'publico';

$chave = $interno
    ? 'matricula_interna'
    : 'matricula_manual';

$chaveResultado = $chave . '_resultado';

$leadIdAtual = 0;

$rota = static function (
    string $acao,
    array $extras = []
) use (
    $baseUrl,
    $interno,
    &$leadIdAtual
): string {

    $params = ['acao' => $acao];

    if ($interno) {

        $params['modo'] = 'interno';

        if (
            !empty($extras['novo']) &&
            $leadIdAtual > 0
        ) {
            $params['lead_id'] = $leadIdAtual;
        }
    }

    return $baseUrl . '?' . http_build_query(
        array_merge($params, $extras)
    );
};

function matriculaRedirecionar(string $url): void
{
    header('Location: ' . $url, true, 303);
    exit;
}

function matriculaId($valor): int
{
    return is_scalar($valor)
        ? (int) filter_var(
            $valor,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        )
        : 0;
}

$acao = $_GET['acao'] ?? 'identificacao';

$requisicao = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!in_array($requisicao, ['GET', 'POST'], true)) {
    http_response_code(405);
    exit('Operação não permitida.');
}

$post = $requisicao === 'POST';

if (!in_array(
    $acao,
    ['identificacao', 'filial', 'pagamento', 'sucesso'],
    true
)) {
    matriculaRedirecionar($rota('identificacao'));
}

$erro = '';
$erros = [];
$dados = [];
$planos = [];
$filiais = [];
$plano = null;
$filial = null;
$existente = null;
$leadAtual = null;
$cobrancas = [];
$resultado = null;

$salvar = static function (
    array $estado
) use ($chave): void {

    $_SESSION[$chave] = $estado;
};

$novoInterno = static function (
    int $leadId = 0
) use (
    $empresaInterna,
    $identidade
): array {

    return [
        'company_id' => $empresaInterna,
        'identidade' => $identidade,
        'csrf' => bin2hex(random_bytes(32)),
        'plano_id' => 0,
        'filial_id' => 0,
        'cpf_consultado' => false,
        'lead_id' => $leadId
    ];
};

try {

    // Conclusão.

    if ($acao === 'sucesso') {

        $resultado = $_SESSION[$chaveResultado] ?? null;

        if (
            !$resultado ||
            ($resultado['identidade'] ?? '') !== $identidade
        ) {
            matriculaRedirecionar(
                $interno
                    ? $urlAlunos
                    : BASE_URL . '/index.php'
            );
        }

        $company_id = (int) $resultado['company_id'];

        $model = new MatriculaModel(
            $pdo,
            $company_id,
            $usuarioMatricula,
            $roleMatricula
        );

        if (
            $interno &&
            (
                $company_id !== $empresaInterna ||
                !$model->filial(
                    (int) $resultado['filial_id']
                )
            )
        ) {
            matriculaRedirecionar($urlAlunos);
        }

        $config = $interno
            ? []
            : $model->configuracao();

        require __DIR__ . '/../views/matricula/sucesso.php';

        exit;
    }

    // Iniciar matrícula interna ou vinda do CRM.

    if (
        $interno &&
        !$post &&
        $acao === 'identificacao' &&
        (
            ($_GET['novo'] ?? '') === '1' ||
            empty($_SESSION[$chave])
        )
    ) {

        unset(
            $_SESSION[$chave],
            $_SESSION[$chaveResultado]
        );

        $leadIdAtual = matriculaId(
            $_GET['lead_id'] ?? 0
        );

        $dados = $novoInterno($leadIdAtual);

        if ($leadIdAtual > 0) {

            $model = new MatriculaModel(
                $pdo,
                $empresaInterna,
                $usuarioMatricula,
                $roleMatricula
            );

            try {

                $leadAtual = $model->lead($leadIdAtual);

                $dados['nome'] = $leadAtual['nome'];

                $dados['telefone'] =
                    $leadAtual['telefone'];

                $dados['filial_id'] =
                    (int) $leadAtual['filial_id'];

            } catch (MatriculaErro $e) {

                $_SESSION['crm_flash'] = [
                    'erro' => $e->getMessage()
                ];

                matriculaRedirecionar($urlCrm);
            }
        }

        $salvar($dados);
    }

    // Iniciar matrícula pública.

    if (
        !$interno &&
        !$post &&
        $acao === 'identificacao' &&
        (
            isset($_GET['company_id']) ||
            isset($_GET['id'])
        )
    ) {

        $empresa = matriculaId(
            $_GET['company_id'] ?? $_GET['id']
        );

        if (!$empresa) {
            matriculaRedirecionar(BASE_URL . '/index.php');
        }

        unset(
            $_SESSION[$chave],
            $_SESSION[$chaveResultado]
        );

        $salvar([
            'company_id' => $empresa,
            'identidade' => $identidade,
            'plano_id' => matriculaId(
                $_GET['plano_id'] ?? 0
            ),
            'csrf' => bin2hex(random_bytes(32))
        ]);
    }

    $dados = $_SESSION[$chave] ?? null;

    if (
        !$dados ||
        ($dados['identidade'] ?? '') !== $identidade ||
        (
            $interno &&
            (int) $dados['company_id'] !== $empresaInterna
        )
    ) {

        unset($_SESSION[$chave]);

        $ultimo = $_SESSION[$chaveResultado] ?? null;

        if (
            $post &&
            $ultimo &&
            ($ultimo['identidade'] ?? '') === $identidade
        ) {
            matriculaRedirecionar($rota('sucesso'));
        }

        matriculaRedirecionar(
            $interno
                ? $urlAlunos
                : BASE_URL . '/index.php'
        );
    }

    $company_id = (int) $dados['company_id'];

    $model = new MatriculaModel(
        $pdo,
        $company_id,
        $usuarioMatricula,
        $roleMatricula
    );

    $config = $interno
        ? []
        : $model->configuracao();

    $csrf = $dados['csrf'];

    $metodos = $model->metodos();

    $leadIdAtual = $interno
        ? (int) ($dados['lead_id'] ?? 0)
        : 0;

    // Revalidar lead em todas as etapas.

    if ($leadIdAtual > 0) {

        try {

            $leadAtual = $model->lead($leadIdAtual);

            if (
                !empty($dados['cpf_consultado']) &&
                (int) ($dados['filial_id'] ?? 0) !==
                    (int) $leadAtual['filial_id']
            ) {
                throw new MatriculaErro(
                    'A unidade do lead foi alterada. Inicie a matrícula novamente.'
                );
            }

        } catch (MatriculaErro $e) {

            unset($_SESSION[$chave]);

            $_SESSION['crm_flash'] = [
                'erro' => $e->getMessage()
            ];

            matriculaRedirecionar($urlCrm);
        }
    }

    $postValido =
        $post &&
        is_string($_POST['csrf'] ?? null) &&
        hash_equals($csrf, $_POST['csrf']);

    if ($post && !$postValido) {

        $erro =
            'A sessão do formulário expirou. Atualize a página e tente novamente.';
    }

    if (!empty($dados['erro_flash'])) {

        $erro = $dados['erro_flash'];

        unset($dados['erro_flash']);

        $salvar($dados);
    }

    if (
        $acao !== 'identificacao' &&
        empty($dados['identificacao_ok'])
    ) {
        matriculaRedirecionar($rota('identificacao'));
    }

    // Identificação.

    if ($acao === 'identificacao' && $postValido) {

        $operacao = MatriculaModel::texto(
            $_POST['operacao'] ?? 'identificar'
        );

        // Buscar CPF.

        if ($interno && $operacao === 'buscar_cpf') {

            $dados = $novoInterno($leadIdAtual);

            if ($leadAtual) {

                $dados['nome'] = $leadAtual['nome'];

                $dados['telefone'] =
                    $leadAtual['telefone'];

                $dados['filial_id'] =
                    (int) $leadAtual['filial_id'];
            }

            try {

                $cpf = MatriculaModel::normalizarCpf(
                    MatriculaModel::texto(
                        $_POST['cpf_busca'] ?? ''
                    )
                );

                $cadastro = $model->buscarAlunoCpf($cpf);

                $dados['cpf'] = $cpf;

                if ($cadastro) {

                    $cadastro = $model->validarAlunoExistente(
                        (int) $cadastro['id']
                    );

                    if (
                        $leadAtual &&
                        (int) $cadastro['filial_id'] !==
                            (int) $leadAtual['filial_id']
                    ) {
                        throw new MatriculaErro(
                            'O aluno pertence a uma unidade diferente da unidade do lead.'
                        );
                    }

                    $dados['aluno_existente_id'] =
                        (int) $cadastro['id'];

                    $dados['nome'] = $cadastro['nome'];

                    $dados['filial_id'] =
                        (int) $cadastro['filial_id'];
                }

                $dados['cpf_consultado'] = true;

            } catch (MatriculaErro $e) {

                $dados['erro_flash'] = $e->getMessage();
            }

            $salvar($dados);

            matriculaRedirecionar($rota('identificacao'));
        }

        // Reutilizar cadastro existente.

        if ($interno && $operacao === 'usar_existente') {

            try {

                $existente = $model->validarAlunoExistente(
                    (int) ($dados['aluno_existente_id'] ?? 0)
                );

                if (
                    $leadAtual &&
                    (int) $existente['filial_id'] !==
                        (int) $leadAtual['filial_id']
                ) {
                    throw new MatriculaErro(
                        'O aluno deve pertencer à unidade do lead.'
                    );
                }

                $dados['nome'] = $existente['nome'];

                $dados['filial_id'] =
                    (int) $existente['filial_id'];

                $dados['identificacao_ok'] = true;

                unset($dados['oferta']);

                $salvar($dados);

                matriculaRedirecionar($rota('filial'));

            } catch (MatriculaErro $e) {

                $erro = $e->getMessage();
            }

        } elseif ($operacao === 'identificar') {

            if (
                $interno &&
                (
                    empty($dados['cpf_consultado']) ||
                    !empty($dados['aluno_existente_id'])
                )
            ) {

                $erro = 'Pesquise o CPF antes de continuar.';

            } else {

                $entrada = $_POST;

                if ($interno) {
                    $entrada['cpf'] = $dados['cpf'];
                }

                $entrada = MatriculaModel::validarIdentificacao(
                    $entrada,
                    $erros
                );

                $senha = is_string($_POST['senha'] ?? null)
                    ? $_POST['senha']
                    : '';

                $hash = $dados['senha_hash'] ?? '';

                if ($senha !== '' || $hash === '') {

                    $tamanho = preg_match_all('/./us', $senha);

                    if (
                        $tamanho === false ||
                        $tamanho < 8 ||
                        strlen($senha) > 72 ||
                        str_contains($senha, "\0")
                    ) {

                        $erros['senha'] =
                            'Use uma senha com pelo menos 8 caracteres e até 72 bytes.';

                    } else {

                        $hash = password_hash(
                            $senha,
                            PASSWORD_DEFAULT
                        );
                    }
                }

                $dados = array_merge($dados, $entrada);

                unset(
                    $dados['identificacao_ok'],
                    $dados['oferta']
                );

                if (!$erros) {

                    try {

                        $model->validarCadastroNovo($entrada);

                        $dados['senha_hash'] = $hash;
                        $dados['identificacao_ok'] = true;

                        $salvar($dados);

                        matriculaRedirecionar($rota('filial'));

                    } catch (MatriculaErro $e) {

                        $erro = $e->getMessage();
                    }
                }

                $salvar($dados);
            }

        } else {

            $erro = 'Operação não autorizada.';
        }
    }

    // Revalidar aluno existente.

    if ($interno && !empty($dados['aluno_existente_id'])) {

        try {

            $existente = $model->validarAlunoExistente(
                (int) $dados['aluno_existente_id']
            );

            if (
                $leadAtual &&
                (int) $existente['filial_id'] !==
                    (int) $leadAtual['filial_id']
            ) {
                throw new MatriculaErro(
                    'A unidade do aluno não corresponde à do lead.'
                );
            }

            $dados['nome'] = $existente['nome'];

            $dados['filial_id'] =
                (int) $existente['filial_id'];

            $salvar($dados);

        } catch (MatriculaErro $e) {

            $dados = $novoInterno($leadIdAtual);

            if ($leadAtual) {

                $dados['nome'] = $leadAtual['nome'];

                $dados['telefone'] =
                    $leadAtual['telefone'];

                $dados['filial_id'] =
                    (int) $leadAtual['filial_id'];
            }

            $dados['erro_flash'] = $e->getMessage();

            $salvar($dados);

            matriculaRedirecionar($rota('identificacao'));
        }
    }

    // Unidade e plano.

    if ($acao === 'filial') {

        $planos = $model->planos();
        $filiais = $model->filiais();

        $filialObrigatoria = $leadAtual
            ? (int) $leadAtual['filial_id']
            : (
                $existente
                    ? (int) $existente['filial_id']
                    : 0
            );

        if ($filialObrigatoria) {

            $filiais = array_values(array_filter(
                $filiais,
                static fn($f) =>
                    (int) $f['id'] === $filialObrigatoria
            ));

            $dados['filial_id'] = $filialObrigatoria;

            $salvar($dados);
        }

        if ($postValido) {

            $planoEscolhido = matriculaId(
                $_POST['plano_id'] ?? 0
            );

            $filialEscolhida = matriculaId(
                $_POST['filial_id'] ?? 0
            );

            if (!in_array(
                $planoEscolhido,
                array_map('intval', array_column($planos, 'id')),
                true
            )) {
                $erros['plano_id'] =
                    'Selecione um plano disponível.';
            }

            if (!in_array(
                $filialEscolhida,
                array_map('intval', array_column($filiais, 'id')),
                true
            )) {
                $erros['filial_id'] =
                    'Selecione uma unidade autorizada.';
            }

            $dados['plano_id'] = $planoEscolhido;

            if (!isset($erros['filial_id'])) {
                $dados['filial_id'] = $filialEscolhida;
            }

            unset($dados['oferta']);

            $salvar($dados);

            if (!$erros) {
                matriculaRedirecionar($rota('pagamento'));
            }
        }
    }

    // Pagamento.

    if ($acao === 'pagamento') {

        $plano = $model->plano(
            (int) ($dados['plano_id'] ?? 0)
        );

        $filial = $model->filial(
            (int) ($dados['filial_id'] ?? 0)
        );

        if (
            !$plano ||
            !$filial ||
            (
                $existente &&
                (int) $filial['id'] !==
                    (int) $existente['filial_id']
            ) ||
            (
                $leadAtual &&
                (int) $filial['id'] !==
                    (int) $leadAtual['filial_id']
            )
        ) {

            $dados['erro_flash'] =
                'O plano ou a unidade não está disponível.';

            $salvar($dados);

            matriculaRedirecionar($rota('filial'));
        }

        try {

            $cobrancas = MatriculaModel::cobrancas($plano);

        } catch (MatriculaErro $e) {

            $dados['erro_flash'] = $e->getMessage();

            $salvar($dados);

            matriculaRedirecionar($rota('filial'));
        }

        $ofertaAtual = [
            'plano_id' => (int) $plano['id'],
            'filial_id' => (int) $filial['id'],
            'duracao' => $plano['duracao'],
            'total' => $cobrancas['total']
        ];

        if ($postValido) {

            $metodoEscolhido = MatriculaModel::texto(
                $_POST['metodo'] ?? ''
            );

            if (!isset($metodos[$metodoEscolhido])) {

                $erro = 'Selecione uma forma de pagamento autorizada.';

            } elseif (
                ($dados['oferta'] ?? null) !== $ofertaAtual
            ) {

                $erro =
                    'Confira as condições atualizadas antes de finalizar.';

            } else {

                $dados['metodo'] = $metodoEscolhido;

                try {

                    $resultado = $model->criarPendente(
                        $dados,
                        $ofertaAtual
                    );

                    $resultado['identidade'] = $identidade;

                    $_SESSION[$chaveResultado] = $resultado;

                    unset($_SESSION[$chave]);

                    matriculaRedirecionar($rota('sucesso'));

                } catch (MatriculaErro $e) {

                    $erro = $e->getMessage();

                } catch (Throwable $e) {

                    error_log(
                        '[GymFlow][Matricula] ' .
                        get_class($e) .
                        ' | código ' . $e->getCode() .
                        ' | linha ' . $e->getLine()
                    );

                    $erro =
                        'Não foi possível concluir a matrícula. Tente novamente.';
                }
            }
        }

        $dados['oferta'] = $ofertaAtual;

        $salvar($dados);
    }

    require __DIR__ .
        '/../views/matricula/' .
        $acao .
        '.php';

} catch (Throwable $e) {

    error_log(
        '[GymFlow][MatriculaController] ' .
        get_class($e) .
        ' | código ' . $e->getCode() .
        ' | linha ' . $e->getLine()
    );

    http_response_code(503);

    echo 'Não foi possível carregar a matrícula. Tente novamente mais tarde.';
}