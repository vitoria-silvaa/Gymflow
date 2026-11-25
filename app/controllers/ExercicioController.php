<?php
// ==========================================
// GYMFLOW — CONTROLLER DE EXERCÍCIOS
// app/controllers/ExercicioController.php
// ==========================================

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin', 'Professor']);

require_once __DIR__ . '/../models/Exercicio.php';

$tituloPagina = 'Biblioteca de Exercícios';

$cssEspecifico = BASE_URL . '/assets/css/biblioteca.css';

// ==========================================
// 1. PROTEÇÃO CSRF
// ==========================================

if (
    !isset($_SESSION['exercicio_csrf']) ||
    !is_string($_SESSION['exercicio_csrf']) ||
    $_SESSION['exercicio_csrf'] === ''
) {
    $_SESSION['exercicio_csrf'] = bin2hex(random_bytes(32));
}

function validarCsrfExercicio(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['exercicio_csrf'], $token)
    ) {
        http_response_code(403);

        exit('Não foi possível validar a solicitação. ' .
            'Recarregue o formulário e tente novamente.');
    }
}

// ==========================================
// 2. FUNÇÕES AUXILIARES
// ==========================================

function redirecionarExercicios(
    string $parametros = ''
): void {

    $url = BASE_URL .
        '/app/controllers/ExercicioController.php';

    if ($parametros !== '') {
        $url .= '?' . $parametros;
    }

    header('Location: ' . $url, true, 303);

    exit;
}

function obterIdExercicio($valor): int
{
    $id = filter_var(
        $valor,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ]
        ]
    );

    return $id === false ? 0 : (int) $id;
}

function obterTextoExercicio($valor): string
{
    return is_string($valor)
        ? trim($valor)
        : '';
}

function erroUploadExercicio(int $codigo): string
{
    return match ($codigo) {

        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE =>
        'O arquivo ultrapassa o limite permitido pelo servidor.',

        UPLOAD_ERR_PARTIAL =>
        'O envio foi interrompido. Tente novamente.',

        UPLOAD_ERR_NO_FILE =>
        'Nenhum arquivo foi selecionado.',

        UPLOAD_ERR_NO_TMP_DIR =>
        'A pasta temporária de uploads não está disponível.',

        UPLOAD_ERR_CANT_WRITE =>
        'Não foi possível gravar o arquivo.',

        UPLOAD_ERR_EXTENSION =>
        'O envio foi bloqueado por uma extensão do PHP.',

        default =>
        'Não foi possível enviar o arquivo.'
    };
}

// ==========================================
// 3. FORMATOS DE MÍDIA
// ==========================================

function formatosMidiaExercicio(): array
{
    return [

        'jpg' => [
            'tipo' => 'imagem',
            'mime' => 'image/jpeg',
            'limite' => 5
        ],

        'jpeg' => [
            'tipo' => 'imagem',
            'mime' => 'image/jpeg',
            'limite' => 5
        ],

        'png' => [
            'tipo' => 'imagem',
            'mime' => 'image/png',
            'limite' => 5
        ],

        'webp' => [
            'tipo' => 'imagem',
            'mime' => 'image/webp',
            'limite' => 5
        ],

        'gif' => [
            'tipo' => 'imagem',
            'mime' => 'image/gif',
            'limite' => 15
        ],

        'mp4' => [
            'tipo' => 'video',
            'mime' => 'video/mp4',
            'limite' => 50
        ],

        'webm' => [
            'tipo' => 'video',
            'mime' => 'video/webm',
            'limite' => 50
        ]

    ];
}

// ==========================================
// 4. PROCESSAMENTO SEGURO DO UPLOAD
// ==========================================

function processarUploadMidia(array $arquivo): array
{
    $erro = $arquivo['error'] ?? UPLOAD_ERR_NO_FILE;

    if (!is_int($erro)) {

        throw new InvalidArgumentException(
            'Estrutura de upload inválida.'
        );
    }

    if ($erro !== UPLOAD_ERR_OK) {

        throw new RuntimeException(
            erroUploadExercicio($erro)
        );
    }

    // VALIDAR ARQUIVO RECEBIDO

    if (
        !isset($arquivo['name'], $arquivo['tmp_name']) ||
        !is_string($arquivo['name']) ||
        !is_string($arquivo['tmp_name']) ||
        !is_uploaded_file($arquivo['tmp_name'])
    ) {

        throw new InvalidArgumentException(
            'O arquivo enviado não é válido.'
        );
    }

    // IDENTIFICAR EXTENSÃO

    $extensao = strtolower(
        pathinfo(
            $arquivo['name'],
            PATHINFO_EXTENSION
        )
    );

    $formatos = formatosMidiaExercicio();

    if (!isset($formatos[$extensao])) {

        throw new InvalidArgumentException(
            'Formato de mídia não permitido.'
        );
    }

    $config = $formatos[$extensao];

    // VALIDAR TAMANHO REAL

    $tamanho = filesize($arquivo['tmp_name']);

    if ($tamanho === false || $tamanho <= 0) {

        throw new InvalidArgumentException(
            'O arquivo está vazio ou inválido.'
        );
    }

    $limiteBytes = $config['limite'] * 1024 * 1024;

    if ($tamanho > $limiteBytes) {

        throw new InvalidArgumentException(
            'O limite para este formato é de ' .
                $config['limite'] .
                ' MB.'
        );
    }

    // VALIDAR CONTEÚDO REAL

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mimeReal = $finfo->file(
        $arquivo['tmp_name']
    );

    if ($mimeReal !== $config['mime']) {

        throw new InvalidArgumentException(
            'O conteúdo do arquivo não corresponde à extensão informada.'
        );
    }

    // VALIDAR IMAGENS E GIFS

    if ($config['tipo'] === 'imagem') {

        $info = @getimagesize(
            $arquivo['tmp_name']
        );

        if (
            $info === false ||
            ($info['mime'] ?? '') !== $mimeReal ||
            $info[0] <= 0 ||
            $info[1] <= 0 ||
            $info[0] > 4096 ||
            $info[1] > 4096
        ) {

            throw new InvalidArgumentException(
                'A imagem é inválida ou ultrapassa a resolução máxima de 4096 pixels.'
            );
        }
    }

    // DIRETÓRIO DE UPLOAD

    $pasta = dirname(__DIR__, 2) .
        '/assets/uploads/exercicios/';

    if (
        !is_dir($pasta) &&
        !mkdir($pasta, 0755, true) &&
        !is_dir($pasta)
    ) {

        throw new RuntimeException(
            'Não foi possível criar a pasta de mídia.'
        );
    }

    if (!is_writable($pasta)) {

        throw new RuntimeException(
            'A pasta de mídia não possui permissão de escrita.'
        );
    }

    // NOME ÚNICO

    $nomeArquivo =
        'exercicio_' .
        bin2hex(random_bytes(16)) .
        '.' .
        $extensao;

    if (!move_uploaded_file(
        $arquivo['tmp_name'],
        $pasta . $nomeArquivo
    )) {

        throw new RuntimeException(
            'Não foi possível armazenar a demonstração.'
        );
    }

    return [

        'midia' => BASE_URL .
            '/assets/uploads/exercicios/' .
            $nomeArquivo,

        'tipo_midia' => $config['tipo']

    ];
}

// ==========================================
// 5. LIMPEZA EM CASO DE FALHA
// ==========================================

function limparUploadNovoExercicio(string $url): void
{
    $prefixo = BASE_URL .
        '/assets/uploads/exercicios/';

    if (!str_starts_with($url, $prefixo)) {
        return;
    }

    $nome = substr(
        $url,
        strlen($prefixo)
    );

    if (!preg_match(
        '/^exercicio_[a-f0-9]{32}\.(jpg|jpeg|png|webp|gif|mp4|webm)$/',
        $nome
    )) {
        return;
    }

    $caminho = dirname(__DIR__, 2) .
        '/assets/uploads/exercicios/' .
        $nome;

    if (is_file($caminho)) {

        if (!@unlink($caminho)) {

            error_log(
                '[GymFlow][Exercicios] Falha ao limpar upload.'
            );
        }
    }
}

// ==========================================
// 6. IDENTIFICAR REQUISIÇÃO
// ==========================================

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$acaoGet = obterTextoExercicio(
    $_GET['acao'] ?? ''
);

$acaoPost = obterTextoExercicio(
    $_POST['acao'] ?? ''
);

// Detectar requisição rejeitada pelo post_max_size.

if (
    $metodo === 'POST' &&
    empty($_POST) &&
    empty($_FILES) &&
    (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
) {

    http_response_code(413);

    exit('O formulário ultrapassa o limite de envio do PHP.');
}

if ($metodo === 'POST') {

    validarCsrfExercicio();
}

// ==========================================
// 7. NOVO EXERCÍCIO
// ==========================================

if (
    $metodo === 'GET' &&
    $acaoGet === 'novo'
) {

    require __DIR__ .
        '/../views/exercicios/exercicios.php';

    exit;
}

// ==========================================
// 8. ABRIR EDIÇÃO
// ==========================================

if (
    $metodo === 'GET' &&
    $acaoGet === 'editar'
) {

    $id = obterIdExercicio(
        $_GET['id'] ?? null
    );

    if (!$id) {

        redirecionarExercicios(
            'erro=invalido'
        );
    }

    // O Model restringe o acesso à própria empresa.

    $exercicio = buscarExercicioPorId(
        $pdo,
        $id
    );

    if (!$exercicio) {

        redirecionarExercicios(
            'erro=nao_encontrado'
        );
    }

    require __DIR__ .
        '/../views/exercicios/editar.php';

    exit;
}

// ==========================================
// 9. DESATIVAR EXERCÍCIO
// ==========================================

// Mantido por compatibilidade com biblioteca.php.
// Migrar posteriormente para POST + CSRF.

if (
    $metodo === 'GET' &&
    $acaoGet === 'excluir'
) {

    $id = obterIdExercicio(
        $_GET['id'] ?? null
    );

    if (!$id) {

        redirecionarExercicios(
            'erro=invalido'
        );
    }

    try {

        excluirExercicio($pdo, $id);

        redirecionarExercicios(
            'sucesso=excluido'
        );
    } catch (Throwable $e) {

        error_log(
            '[GymFlow][Exercicios] ' .
                $e->getMessage()
        );

        redirecionarExercicios(
            'erro=exclusao'
        );
    }
}

// ==========================================
// 10. CADASTRAR OU ATUALIZAR
// ==========================================

if (
    $metodo === 'POST' &&
    (
        !isset($_POST['acao']) ||
        $acaoPost === 'editar'
    )
) {

    $modoEdicao = $acaoPost === 'editar';

    $id = $modoEdicao
        ? obterIdExercicio($_POST['id'] ?? null)
        : 0;

    $exercicio = [];

    if ($modoEdicao) {

        if (!$id) {

            redirecionarExercicios(
                'erro=invalido'
            );
        }

        $exercicio = buscarExercicioPorId(
            $pdo,
            $id
        );

        if (!$exercicio) {

            redirecionarExercicios(
                'erro=nao_encontrado'
            );
        }
    }

    $urlRetorno = $modoEdicao
        ? 'acao=editar&id=' . $id
        : 'acao=novo';

    // ======================================
    // 10.1. VALIDAR INFORMAÇÕES
    // ======================================

    $nome = obterTextoExercicio(
        $_POST['nome'] ?? ''
    );

    $grupo = obterTextoExercicio(
        $_POST['grupo_muscular'] ?? ''
    );

    if (
        $nome === '' ||
        $grupo === '' ||
        mb_strlen($nome) > 100 ||
        mb_strlen($grupo) > 30
    ) {

        redirecionarExercicios(
            $urlRetorno . '&erro=campos'
        );
    }

    // ======================================
    // 10.2. IDENTIFICAR MÍDIA EXISTENTE
    // ======================================

    $midiaAnterior = obterTextoExercicio(
        $exercicio['midia'] ?? ''
    );

    $tipoAnterior = obterTextoExercicio(
        $exercicio['tipo_midia'] ?? 'imagem'
    );

    $midia = $midiaAnterior !== ''
        ? $midiaAnterior
        : null;

    $tipo_midia = (
        $midia !== null &&
        $tipoAnterior === 'video'
    )
        ? 'video'
        : 'imagem';

    // ======================================
    // 10.3. VALIDAR AÇÃO DE REMOÇÃO
    // ======================================

    $remocao = $_POST['remover_midia'] ?? '0';

    if (!in_array(
        $remocao,
        ['0', '1'],
        true
    )) {

        redirecionarExercicios(
            $urlRetorno .
                '&erro=' .
                urlencode('Solicitação de remoção inválida.')
        );
    }

    $removerMidia =
        $modoEdicao &&
        $remocao === '1';

    // ======================================
    // 10.4. IDENTIFICAR NOVO ARQUIVO
    // ======================================

    $arquivo = $_FILES['arquivo'] ?? null;

    if (
        $arquivo !== null &&
        (
            !is_array($arquivo) ||
            !isset($arquivo['error']) ||
            !is_int($arquivo['error'])
        )
    ) {

        redirecionarExercicios(
            $urlRetorno . '&erro=arquivo'
        );
    }

    $codigoUpload = $arquivo['error']
        ?? UPLOAD_ERR_NO_FILE;

    $novoArquivo =
        $codigoUpload !== UPLOAD_ERR_NO_FILE;

    $midiaNova = null;

    // ======================================
    // 10.5. GERENCIAR A MÍDIA
    // ======================================

    try {

        // Requisições contraditórias são rejeitadas.

        if ($removerMidia && $novoArquivo) {

            throw new InvalidArgumentException(
                'Não é possível remover e substituir a mídia simultaneamente.'
            );
        }

        // CENÁRIO A: NOVO ARQUIVO

        if ($novoArquivo) {

            $resultado = processarUploadMidia(
                $arquivo
            );

            $midiaNova = $resultado['midia'];

            $midia = $resultado['midia'];

            $tipo_midia = $resultado['tipo_midia'];
        }

        // CENÁRIO B: REMOVER MÍDIA EXISTENTE

        elseif ($removerMidia) {

            $midia = null;

            $tipo_midia = 'imagem';
        }

        // CENÁRIO C: PRESERVAR OU SALVAR SEM MÍDIA
        //
        // Sem upload e sem remoção, mantém o estado
        // anterior. Em um cadastro, permanece NULL.

        // ==================================
        // 10.6. PERSISTIR NO BANCO
        // ==================================

        if ($modoEdicao) {

            atualizarExercicio(
                $pdo,
                $id,
                $nome,
                $grupo,
                $midia,
                $tipo_midia
            );
        } else {

            cadastrarExercicio(
                $pdo,
                $nome,
                $grupo,
                $midia,
                $tipo_midia
            );
        }
    } catch (Throwable $e) {

        // Desfaz somente o novo upload se a
        // operação no banco não for concluída.

        if ($midiaNova !== null) {

            limparUploadNovoExercicio(
                $midiaNova
            );
        }

        error_log(
            '[GymFlow][Exercicios] ' .
                $e->getMessage()
        );

        $mensagem = (
            $e instanceof InvalidArgumentException ||
            (
                $e instanceof RuntimeException &&
                !($e instanceof PDOException)
            )
        )
            ? $e->getMessage()
            : 'Não foi possível salvar o exercício.';

        redirecionarExercicios(
            $urlRetorno .
                '&erro=' .
                urlencode($mensagem)
        );
    }

    // ======================================
    // 10.7. FINALIZAR
    // ======================================

    redirecionarExercicios(
        $modoEdicao
            ? 'sucesso=atualizado'
            : 'sucesso=cadastrado'
    );
}

// ==========================================
// 11. REQUISIÇÕES DESCONHECIDAS
// ==========================================

if ($metodo !== 'GET') {

    http_response_code(405);

    exit('Método ou ação não permitida.');
}

// ==========================================
// 12. LISTAGEM E FILTROS
// ==========================================

$pesquisa = obterTextoExercicio(
    $_GET['pesquisa'] ?? ''
);

$grupo = obterTextoExercicio(
    $_GET['grupo'] ?? ''
);

if ($pesquisa !== '') {

    $exercicios = pesquisarExercicios(
        $pdo,
        $pesquisa
    );
} elseif ($grupo !== '') {

    $exercicios = filtrarExerciciosPorGrupo(
        $pdo,
        $grupo
    );
} else {

    $exercicios = listarExercicios($pdo);
}

// ==========================================
// 13. VIEW
// ==========================================

require __DIR__ .
    '/../views/treinos/biblioteca.php';
