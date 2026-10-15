
<?php
// ========================================
// GYMFLOW - CONTROLLER DE EXERCÍCIOS
// app/controllers/ExercicioController.php
// ========================================

require_once __DIR__ . '/../../config/sessao.php';
require_once __DIR__ . '/../models/Exercicio.php';

$tituloPagina = "Biblioteca de Exercícios";

// O header.php já adiciona a versão do CSS.
$cssEspecifico = BASE_URL . '/assets/css/biblioteca.css';

// ========================================
// FUNÇÕES AUXILIARES
// ========================================

function redirecionarExercicios(string $parametros = ''): void
{
    $url = BASE_URL . '/app/controllers/ExercicioController.php';

    if ($parametros !== '') {
        $url .= '?' . $parametros;
    }

    header('Location: ' . $url);
    exit;
}

function erroUploadExercicio(int $codigo): string
{
    switch ($codigo) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'O arquivo ultrapassa o limite permitido pelo servidor.';

        case UPLOAD_ERR_PARTIAL:
            return 'O envio do arquivo foi interrompido. Tente novamente.';

        case UPLOAD_ERR_NO_FILE:
            return 'Nenhum arquivo foi selecionado.';

        case UPLOAD_ERR_NO_TMP_DIR:
            return 'A pasta temporária de uploads não está disponível.';

        case UPLOAD_ERR_CANT_WRITE:
            return 'O servidor não conseguiu gravar o arquivo.';

        case UPLOAD_ERR_EXTENSION:
            return 'O envio foi bloqueado por uma extensão do PHP.';

        default:
            return 'Não foi possível enviar o arquivo.';
    }
}

function validarTipoMidiaExercicio(string $tipo): void
{
    if (!in_array($tipo, ['imagem', 'video'], true)) {
        throw new InvalidArgumentException(
            'Selecione um tipo de mídia válido.'
        );
    }
}

// ========================================
// PROCESSAMENTO SEGURO DO UPLOAD
// ========================================

function processarUploadMidia(
    array $arquivo,
    string $tipo_midia
): string {

    validarTipoMidiaExercicio($tipo_midia);

    $extensoesPermitidas = [
        'imagem' => ['jpg', 'jpeg', 'png', 'webp'],
        'video'  => ['mp4', 'webm']
    ];

    $mimesPorExtensao = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'webp' => ['image/webp'],
        'mp4'  => ['video/mp4'],
        'webm' => ['video/webm']
    ];

    $erro = (int) ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($erro !== UPLOAD_ERR_OK) {
        throw new RuntimeException(
            erroUploadExercicio($erro)
        );
    }

    if (
        !isset($arquivo['name'], $arquivo['tmp_name']) ||
        !is_string($arquivo['name']) ||
        !is_string($arquivo['tmp_name']) ||
        !is_uploaded_file($arquivo['tmp_name'])
    ) {
        throw new RuntimeException(
            'O arquivo enviado não é válido.'
        );
    }

    $tamanho = (int) ($arquivo['size'] ?? 0);

    if ($tamanho <= 0) {
        throw new InvalidArgumentException(
            'O arquivo está vazio.'
        );
    }

    $limiteBytes = $tipo_midia === 'video'
        ? 50 * 1024 * 1024
        : 5 * 1024 * 1024;

    if ($tamanho > $limiteBytes) {
        $limite = $tipo_midia === 'video'
            ? '50 MB'
            : '5 MB';

        throw new InvalidArgumentException(
            "O arquivo excede o limite de {$limite}."
        );
    }

    $extensao = strtolower(
        pathinfo(
            $arquivo['name'],
            PATHINFO_EXTENSION
        )
    );

    if (
        !in_array(
            $extensao,
            $extensoesPermitidas[$tipo_midia],
            true
        )
    ) {
        throw new InvalidArgumentException(
            'A extensão do arquivo não é permitida para o tipo de mídia selecionado.'
        );
    }

    // Validação do conteúdo efetivo do arquivo.
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mimeReal = $finfo->file($arquivo['tmp_name']);

    if (
        $mimeReal === false ||
        !in_array(
            $mimeReal,
            $mimesPorExtensao[$extensao],
            true
        )
    ) {
        throw new InvalidArgumentException(
            'O conteúdo do arquivo não corresponde à extensão informada.'
        );
    }

    $pastaUpload =
        __DIR__ . '/../../assets/uploads/exercicios/';

    if (
        !is_dir($pastaUpload) &&
        !mkdir($pastaUpload, 0755, true) &&
        !is_dir($pastaUpload)
    ) {
        throw new RuntimeException(
            'Não foi possível criar a pasta de uploads.'
        );
    }

    if (!is_writable($pastaUpload)) {
        throw new RuntimeException(
            'A pasta de uploads não possui permissão de escrita.'
        );
    }

    // Nome imprevisível para evitar colisões.
    $nomeArquivo =
        'exercicio_' .
        bin2hex(random_bytes(16)) .
        '.' .
        $extensao;

    $caminhoCompleto =
        $pastaUpload . $nomeArquivo;

    if (
        !move_uploaded_file(
            $arquivo['tmp_name'],
            $caminhoCompleto
        )
    ) {
        throw new RuntimeException(
            'Não foi possível salvar a mídia no servidor.'
        );
    }

    return BASE_URL .
        '/assets/uploads/exercicios/' .
        $nomeArquivo;
}

// ========================================
// LIMPEZA DE UPLOAD RECÉM-CRIADO
// Utilizar somente ao desfazer uma operação
// que falhou, nunca para remover mídia antiga.
// ========================================

function limparUploadNovoExercicio(string $url): void
{
    $prefixo =
        BASE_URL . '/assets/uploads/exercicios/';

    if (strpos($url, $prefixo) !== 0) {
        return;
    }

    $nome = substr($url, strlen($prefixo));

    // Aceita somente o padrão de nomes criado
    // pela função processarUploadMidia().
    if (
        !preg_match(
            '/^exercicio_[a-f0-9]{32}\.(jpg|jpeg|png|webp|mp4|webm)$/',
            $nome
        )
    ) {
        return;
    }

    $caminho =
        __DIR__ .
        '/../../assets/uploads/exercicios/' .
        $nome;

    if (is_file($caminho)) {
        unlink($caminho);
    }
}

// ========================================
// NOVO EXERCÍCIO - FORMULÁRIO
// ========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'GET' &&
    ($_GET['acao'] ?? '') === 'novo'
) {
    require __DIR__ . '/../views/exercicios/exercicios.php';
    exit;
}

// ========================================
// EXCLUSÃO
// Mantida por GET temporariamente para
// preservar a compatibilidade com a view.
// Migrar junto com biblioteca.php para
// POST + proteção CSRF.
// ========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'GET' &&
    ($_GET['acao'] ?? '') === 'excluir'
) {

    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        redirecionarExercicios('erro=invalido');
    }

    try {

        excluirExercicio($pdo, $id);

        redirecionarExercicios('sucesso=excluido');
    } catch (Throwable $e) {

        error_log(
            '[GymFlow] Falha ao excluir exercício: ' .
                $e->getMessage()
        );

        redirecionarExercicios('erro=exclusao');
    }
}

// ========================================
// EDITAR EXERCÍCIO - FORMULÁRIO
// ========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'GET' &&
    ($_GET['acao'] ?? '') === 'editar'
) {

    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        redirecionarExercicios('erro=invalido');
    }

    $exercicio = buscarExercicioPorId($pdo, $id);

    if (!$exercicio) {
        redirecionarExercicios('erro=nao_encontrado');
    }

    require __DIR__ . '/../views/exercicios/editar.php';
    exit;
}

// ========================================
// EDITAR EXERCÍCIO - SALVAR
// ========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['acao'] ?? '') === 'editar'
) {

    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        redirecionarExercicios('erro=invalido');
    }

    $exercicio = buscarExercicioPorId($pdo, $id);

    if (!$exercicio) {
        redirecionarExercicios('erro=nao_encontrado');
    }

    $nome = trim((string) ($_POST['nome'] ?? ''));
    $grupo = trim((string) ($_POST['grupo_muscular'] ?? ''));
    $tipo_midia = trim((string) ($_POST['tipo_midia'] ?? ''));

    $urlEdicao = 'acao=editar&id=' . $id;

    if (
        $nome === '' ||
        $grupo === '' ||
        $tipo_midia === ''
    ) {
        redirecionarExercicios(
            $urlEdicao . '&erro=campos'
        );
    }

    try {

        validarTipoMidiaExercicio($tipo_midia);
    } catch (InvalidArgumentException $e) {

        redirecionarExercicios(
            $urlEdicao . '&erro=' .
                urlencode($e->getMessage())
        );
    }

    $tipoAnterior =
        (string) ($exercicio['tipo_midia'] ?? '');

    $midiaAnterior =
        (string) ($exercicio['midia'] ?? '');

    $midia = $midiaAnterior;

    $arquivo =
        $_FILES['arquivo'] ?? null;

    // Um input file vazio normalmente retorna
    // UPLOAD_ERR_NO_FILE.
    $codigoUpload = is_array($arquivo)
        ? (int) ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE)
        : UPLOAD_ERR_NO_FILE;

    $novoArquivoEnviado =
        $codigoUpload !== UPLOAD_ERR_NO_FILE;

    // Regra de segurança importante:
    // não permite mudar o tipo de mídia
    // mantendo um arquivo incompatível.
    if (
        $tipo_midia !== $tipoAnterior &&
        !$novoArquivoEnviado
    ) {
        redirecionarExercicios(
            $urlEdicao . '&erro=' .
                urlencode(
                    'Ao alterar o tipo de mídia, selecione um novo arquivo.'
                )
        );
    }

    // Se não existe mídia anterior, um arquivo
    // passa a ser obrigatório.
    if (
        $midiaAnterior === '' &&
        !$novoArquivoEnviado
    ) {
        redirecionarExercicios(
            $urlEdicao . '&erro=arquivo'
        );
    }

    $midiaNova = null;

    try {

        if ($novoArquivoEnviado) {

            if (!is_array($arquivo)) {
                throw new RuntimeException(
                    'Arquivo inválido.'
                );
            }

            $midiaNova =
                processarUploadMidia(
                    $arquivo,
                    $tipo_midia
                );

            $midia = $midiaNova;
        }

        atualizarExercicio(
            $pdo,
            $id,
            $nome,
            $grupo,
            $midia,
            $tipo_midia
        );
    } catch (Throwable $e) {

        // Se a atualização falhou depois do
        // upload, descarta apenas o arquivo novo.
        if ($midiaNova !== null) {
            limparUploadNovoExercicio($midiaNova);
        }

        if (
            $e instanceof InvalidArgumentException ||
            $e instanceof RuntimeException
        ) {
            $mensagem = $e->getMessage();
        } else {
            error_log(
                '[GymFlow] Erro ao editar exercício: ' .
                    $e->getMessage()
            );

            $mensagem =
                'Não foi possível atualizar o exercício.';
        }

        redirecionarExercicios(
            $urlEdicao . '&erro=' .
                urlencode($mensagem)
        );
    }

    // A mídia antiga não é apagada aqui.
    // Antes de removê-la, precisamos garantir
    // que nenhum outro registro a utiliza.

    redirecionarExercicios('sucesso=atualizado');
}

// ========================================
// CADASTRAR EXERCÍCIO
// ========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !isset($_POST['acao'])
) {

    $nome = trim((string) ($_POST['nome'] ?? ''));
    $grupo = trim((string) ($_POST['grupo_muscular'] ?? ''));
    $tipo_midia = trim((string) ($_POST['tipo_midia'] ?? ''));

    if (
        $nome === '' ||
        $grupo === '' ||
        $tipo_midia === ''
    ) {
        redirecionarExercicios(
            'acao=novo&erro=campos'
        );
    }

    try {

        validarTipoMidiaExercicio($tipo_midia);
    } catch (InvalidArgumentException $e) {

        redirecionarExercicios(
            'acao=novo&erro=' .
                urlencode($e->getMessage())
        );
    }

    $arquivo = $_FILES['arquivo'] ?? null;

    if (!is_array($arquivo)) {
        redirecionarExercicios(
            'acao=novo&erro=arquivo'
        );
    }

    $midiaNova = null;

    try {

        $midiaNova =
            processarUploadMidia(
                $arquivo,
                $tipo_midia
            );

        cadastrarExercicio(
            $pdo,
            $nome,
            $grupo,
            $midiaNova,
            $tipo_midia
        );
    } catch (Throwable $e) {

        if ($midiaNova !== null) {
            limparUploadNovoExercicio($midiaNova);
        }

        if (
            $e instanceof InvalidArgumentException ||
            $e instanceof RuntimeException
        ) {
            $mensagem = $e->getMessage();
        } else {
            error_log(
                '[GymFlow] Erro ao cadastrar exercício: ' .
                    $e->getMessage()
            );

            $mensagem =
                'Não foi possível cadastrar o exercício.';
        }

        redirecionarExercicios(
            'acao=novo&erro=' .
                urlencode($mensagem)
        );
    }

    redirecionarExercicios('sucesso=cadastrado');
}

// ========================================
// REQUISIÇÕES POST DESCONHECIDAS
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(400);
    exit('Ação de exercício inválida.');
}

// ========================================
// PESQUISA E FILTRO
// ========================================

$pesquisa = trim((string) ($_GET['pesquisa'] ?? ''));
$grupo = trim((string) ($_GET['grupo'] ?? ''));

// ========================================
// CONSULTAR EXERCÍCIOS
// ========================================

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

// ========================================
// EXIBIR BIBLIOTECA
// ========================================

require __DIR__ . '/../views/treinos/biblioteca.php';
