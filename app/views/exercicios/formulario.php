<?php
// ========================================
// GYMFLOW - FORMULÁRIO DE EXERCÍCIOS
// app/views/exercicios/formulario.php
// ========================================

$modoEdicao = (bool) ($modoEdicao ?? false);
$exercicio = $exercicio ?? [];

$tituloPagina = $modoEdicao
    ? 'Editar Exercício'
    : 'Novo Exercício';

$cssEspecifico = BASE_URL . '/assets/css/exercicios-form.css';

$urlController = BASE_URL .
    '/app/controllers/ExercicioController.php';

$esc = static function ($valor): string {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};

// ========================================
// DADOS DO EXERCÍCIO
// ========================================

$id = (int) ($exercicio['id'] ?? 0);
$nome = (string) ($exercicio['nome'] ?? '');
$grupoAtual = (string) ($exercicio['grupo'] ?? '');
$midia = trim((string) ($exercicio['midia'] ?? ''));
$tipo = (string) ($exercicio['tipo_midia'] ?? 'imagem');

$possuiMidia = $modoEdicao && $midia !== '';

$extensao = strtolower(
    pathinfo(
        parse_url($midia, PHP_URL_PATH) ?: '',
        PATHINFO_EXTENSION
    )
);

$descricaoMidia = match ($extensao) {
    'gif' => 'GIF animado',
    'mp4' => 'Vídeo MP4',
    'webm' => 'Vídeo WEBM',
    'jpg', 'jpeg' => 'Imagem JPEG',
    'png' => 'Imagem PNG',
    'webp' => 'Imagem WEBP',
    default => $tipo === 'video' ? 'Vídeo' : 'Imagem'
};

$gruposMusculares = [
    'Abdômen',
    'Bíceps',
    'Costas',
    'Glúteo',
    'Glúteos',
    'Ombro',
    'Ombros',
    'Peito',
    'Pernas',
    'Posterior de coxa',
    'Quadríceps',
    'Tríceps'
];

if (
    $grupoAtual !== '' &&
    !in_array($grupoAtual, $gruposMusculares, true)
) {
    $gruposMusculares[] = $grupoAtual;
}

sort($gruposMusculares, SORT_NATURAL);

// ========================================
// MENSAGENS
// ========================================

$erros = [
    'campos' => 'Confira o nome e o grupo muscular.',
    'arquivo' => 'Não foi possível receber o arquivo.',
    'csrf' => 'A sessão expirou. Tente novamente.',
    'invalido' => 'Exercício inválido.'
];

$codigoErro = $_GET['erro'] ?? '';

$mensagemErro = is_string($codigoErro) && $codigoErro !== ''
    ? ($erros[$codigoErro] ?? mb_substr($codigoErro, 0, 300))
    : '';

$csrfToken = $_SESSION['exercicio_csrf'] ?? '';

$arquivoScript = dirname(__DIR__, 3) .
    '/assets/js/exercicio-midia.js';

$versaoScript = is_file($arquivoScript)
    ? filemtime($arquivoScript)
    : '1';

require __DIR__ . '/../shared/header.php';
require __DIR__ . '/../shared/sidebar.php';
?>

<main class="conteudo exercicios-cadastro-page">

    <!-- CABEÇALHO -->

    <header class="exercicios-cadastro-header">

        <div class="exercicios-cadastro-titulos">

            <span class="exercicios-cadastro-etiqueta">
                BIBLIOTECA DE EXERCÍCIOS
            </span>

            <h1><?= $esc($tituloPagina) ?></h1>

            <p>
                Gerencie as informações e a demonstração
                utilizada nas fichas de treino.
            </p>

        </div>

        <a
            href="<?= $esc($urlController) ?>"
            class="exercicios-btn exercicios-btn-secundario">

            <i data-lucide="arrow-left"></i>
            Voltar

        </a>

    </header>

    <?php if ($mensagemErro !== ''): ?>

        <div class="exercicios-alerta-erro" role="alert">

            <i data-lucide="alert-circle"></i>
            <?= $esc($mensagemErro) ?>

        </div>

    <?php endif; ?>

    <form
        id="formExercicio"
        class="exercicios-form"
        action="<?= $esc($urlController) ?>"
        method="POST"
        enctype="multipart/form-data">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= $esc($csrfToken) ?>">

        <?php if ($modoEdicao): ?>

            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" value="<?= $id ?>">

        <?php endif; ?>

        <div class="exercicio-editor-grid">

            <!-- =====================================
                 COLUNA DE INFORMAÇÕES
                 ===================================== -->

            <section class="exercicios-cadastro-card">

                <div class="exercicios-card-header">

                    <h2>Informações do exercício</h2>

                    <p>
                        Identifique o exercício na biblioteca.
                    </p>

                </div>

                <div class="editor-fields">

                    <div class="exercicios-campo">

                        <label for="nome">

                            Nome do exercício
                            <span class="campo-obrigatorio">*</span>

                        </label>

                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            value="<?= $esc($nome) ?>"
                            placeholder="Ex.: Supino reto"
                            maxlength="100"
                            required>

                    </div>

                    <div class="exercicios-campo">

                        <label for="grupo_muscular">

                            Grupo muscular
                            <span class="campo-obrigatorio">*</span>

                        </label>

                        <select
                            id="grupo_muscular"
                            name="grupo_muscular"
                            required>

                            <option value="">
                                Selecione o grupo muscular
                            </option>

                            <?php foreach ($gruposMusculares as $grupo): ?>

                                <option
                                    value="<?= $esc($grupo) ?>"
                                    <?= $grupoAtual === $grupo
                                        ? 'selected'
                                        : '' ?>>

                                    <?= $esc($grupo) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

                <div class="editor-info-box">

                    <i data-lucide="info"></i>

                    <div>

                        <strong>Utilização nas fichas</strong>

                        <p>
                            Alterações neste exercício também
                            serão refletidas nas fichas que
                            utilizam sua referência.
                        </p>

                    </div>

                </div>

            </section>

            <!-- =====================================
                 COLUNA DE MÍDIA
                 ===================================== -->

            <section
                class="exercicios-cadastro-card media-manager"
                id="mediaManager"
                data-possui-midia="<?= $possuiMidia ? '1' : '0' ?>"
                data-descricao-original="<?= $esc($descricaoMidia) ?>"
                data-state="<?= $possuiMidia ? 'atual' : 'vazio' ?>">

                <div class="exercicios-card-header">

                    <div class="media-heading">

                        <h2>Mídia do exercício</h2>

                        <span class="media-optional">
                            Opcional
                        </span>

                    </div>

                    <p>
                        Gerencie a foto, GIF ou vídeo do exercício.
                    </p>

                </div>

                <!-- STATUS -->

                <div class="media-status-row">

                    <span
                        id="mediaStatus"
                        class="media-status"
                        role="status">

                        <?= $possuiMidia
                            ? 'Mídia cadastrada'
                            : 'Sem mídia' ?>

                    </span>

                    <span id="mediaFormat">

                        <?= $possuiMidia
                            ? $esc($descricaoMidia)
                            : '' ?>

                    </span>

                </div>

                <!-- CONTAINER ÚNICO DE DEMONSTRAÇÃO -->

                <div
                    class="media-stage"
                    id="mediaStage">

                    <?php if ($possuiMidia): ?>

                        <div
                            id="mediaOriginal"
                            class="media-layer">

                            <?php if ($tipo === 'video'): ?>

                                <video
                                    src="<?= $esc($midia) ?>"
                                    controls
                                    playsinline
                                    preload="metadata">
                                </video>

                            <?php else: ?>

                                <img
                                    src="<?= $esc($midia) ?>"
                                    alt="Demonstração de <?= $esc($nome) ?>">

                            <?php endif; ?>

                        </div>

                    <?php endif; ?>

                    <div
                        id="mediaPreview"
                        class="media-layer"
                        hidden>
                    </div>

                    <div
                        id="mediaEmpty"
                        class="media-empty"
                        <?= $possuiMidia ? 'hidden' : '' ?>>

                        <div class="media-empty-icon">

                            <i data-lucide="image-off"></i>

                        </div>

                        <strong id="mediaEmptyTitle">
                            Nenhuma demonstração
                        </strong>

                        <p id="mediaEmptyText">
                            Adicione uma mídia quando desejar.
                        </p>

                    </div>

                </div>

                <!-- INFORMAÇÕES -->

                <div
                    class="media-file-info"
                    id="mediaFileInfo"
                    hidden>

                    <i data-lucide="file-check"></i>

                    <div class="media-file-details">

                        <strong id="mediaFileName"></strong>

                        <small id="mediaFileSize"></small>

                    </div>

                    <button
                        type="button"
                        class="media-icon-button"
                        id="mediaClear"
                        aria-label="Cancelar seleção"
                        title="Cancelar seleção">

                        <i data-lucide="x"></i>

                    </button>

                </div>

                <!-- SELEÇÃO DE ARQUIVO -->

                <div class="media-upload-area">

                    <label for="arquivo" class="media-input-label">

                        Arquivo de demonstração

                    </label>

                    <input
                        type="file"
                        name="arquivo"
                        id="arquivo"
                        accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.webm"
                        aria-describedby="mediaHelp mediaError">

                    <small id="mediaHelp" class="media-help">

                        Fotos até 5 MB · GIFs até 15 MB ·
                        Vídeos até 50 MB.

                    </small>

                    <div
                        id="mediaError"
                        class="media-error"
                        role="alert"
                        hidden>
                    </div>

                </div>

                <!-- AÇÕES -->

                <div class="media-actions">

                    <button
                        type="button"
                        id="mediaChoose"
                        class="media-button media-button-primary"
                        hidden>

                        <i data-lucide="upload"></i>

                        <span id="mediaChooseText">

                            <?= $possuiMidia
                                ? 'Substituir mídia'
                                : 'Adicionar mídia' ?>

                        </span>

                    </button>

                    <?php if ($possuiMidia): ?>

                        <!--
                            Checkbox nativo:
                            também funciona sem JavaScript.
                        -->

                        <label
                            class="media-remove-option"
                            id="mediaRemoveOption">

                            <input
                                type="checkbox"
                                name="remover_midia"
                                id="remover_midia"
                                value="1">

                            <i data-lucide="trash-2"></i>

                            <span id="mediaRemoveText">

                                Remover mídia ao salvar

                            </span>

                        </label>

                    <?php endif; ?>

                </div>

                <!-- MENSAGEM SOBRE O ESTADO ATUAL -->

                <div
                    id="mediaNotice"
                    class="media-notice"
                    role="status">

                    <i data-lucide="info"></i>

                    <p id="mediaNoticeText">

                        <?= $possuiMidia
                            ? 'A mídia atual será preservada até que você confirme uma alteração.'
                            : 'O exercício pode ser cadastrado normalmente sem mídia.' ?>

                    </p>

                </div>

            </section>

        </div>

        <!-- =====================================
             AÇÕES DO FORMULÁRIO
             ===================================== -->

        <div class="editor-footer">

            <div class="editor-footer-info">

                <i data-lucide="shield-check"></i>

                <span>
                    As alterações serão aplicadas somente ao salvar.
                </span>

            </div>

            <div class="editor-footer-actions">

                <a
                    href="<?= $esc($urlController) ?>"
                    class="exercicios-btn exercicios-btn-secundario">

                    Cancelar

                </a>

                <button
                    type="submit"
                    class="exercicios-btn exercicios-btn-primario">

                    <i data-lucide="check"></i>

                    <?= $modoEdicao
                        ? 'Salvar alterações'
                        : 'Cadastrar exercício' ?>

                </button>

            </div>

        </div>

    </form>

</main>

<script
    src="<?= $esc(
                BASE_URL .
                    '/assets/js/exercicio-midia.js?v=' .
                    $versaoScript
            ) ?>"
    defer>
</script>

<?php require __DIR__ . '/../shared/footer.php'; ?>