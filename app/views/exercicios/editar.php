<?php
// ========================================
// GYMFLOW - EDITAR EXERCÍCIO
// app/views/exercicios/editar.php
// ========================================

// Verifica se os dados do exercício
// foram enviados pelo controller.
if (!isset($exercicio) || !is_array($exercicio)) {
    header(
        "Location: " . BASE_URL .
            "/app/controllers/ExercicioController.php"
    );
    exit;
}

$tituloPagina = "Editar Exercício";

// Reutiliza o mesmo CSS do cadastro.
$cssEspecifico = BASE_URL . '/assets/css/exercicios.css';

// Valores atuais.
$idExercicio = (int) ($exercicio['id'] ?? 0);
$nomeAtual = (string) ($exercicio['nome'] ?? '');
$grupoAtual = (string) ($exercicio['grupo'] ?? '');
$tipoMidiaAtual = (string) ($exercicio['tipo_midia'] ?? '');
$midiaAtual = (string) ($exercicio['midia'] ?? '');

// Preserva inclusive grupos utilizados
// em versões anteriores do sistema.
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

// Se houver um grupo antigo diferente,
// ele continua disponível para edição.
if (
    $grupoAtual !== '' &&
    !in_array($grupoAtual, $gruposMusculares, true)
) {
    $gruposMusculares[] = $grupoAtual;
    sort($gruposMusculares, SORT_NATURAL);
}

require __DIR__ . '/../shared/header.php';
require __DIR__ . '/../shared/sidebar.php';
?>

<style>
    /* ========================================
   PRÉVIA DE MÍDIA - EDIÇÃO
   ======================================== */

    .exercicios-cadastro-page .exercicios-midia-section {
        margin-top: 28px;
        padding-top: 22px;
        border-top: 1px solid var(--ui-borda, #e2e8f0);
    }

    .exercicios-cadastro-page .exercicios-midia-title {
        margin: 0 0 6px;
        color: var(--ui-texto, #111827);
        font-size: 16px;
        font-weight: 700;
    }

    .exercicios-cadastro-page .exercicios-midia-description {
        margin: 0 0 16px;
        color: var(--ui-texto-suave, #64748b);
        font-size: 13px;
    }

    .exercicios-cadastro-page .exercicios-midia-preview {
        display: flex;
        align-items: center;
        justify-content: center;

        width: min(100%, 400px);
        min-height: 190px;
        padding: 14px;

        background: var(--ui-fundo-pagina, #f8fafc);

        border: 1px solid var(--ui-borda, #e2e8f0);
        border-radius: 10px;

        overflow: hidden;
    }

    .exercicios-cadastro-page .exercicios-midia-preview img,
    .exercicios-cadastro-page .exercicios-midia-preview video {
        display: block;

        width: 100%;
        max-height: 270px;

        object-fit: contain;
        border-radius: 6px;
    }

    .exercicios-cadastro-page .exercicios-midia-vazia {
        margin: 0;
        color: var(--ui-texto-suave, #64748b);
        font-size: 13px;
        text-align: center;
    }

    .exercicios-cadastro-page .exercicios-midia-aviso {
        margin-top: 12px;
        color: var(--ui-texto-suave, #64748b);
        font-size: 12px;
        line-height: 1.5;
    }

    .exercicios-cadastro-page .exercicios-validacao {
        margin-top: 8px;
        color: #b91c1c;
        font-size: 13px;
    }

    .exercicios-cadastro-page [hidden] {
        display: none !important;
    }
</style>

<main class="conteudo exercicios-cadastro-page">

    <!-- =====================================
         CABEÇALHO
         ===================================== -->

    <header class="exercicios-cadastro-header">

        <div class="exercicios-cadastro-titulos">

            <span class="exercicios-cadastro-etiqueta">
                BIBLIOTECA DE EXERCÍCIOS
            </span>

            <h1>Editar Exercício</h1>

            <p>
                Atualize as informações de
                <strong>
                    <?= htmlspecialchars(
                        $nomeAtual,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </strong>.
            </p>

        </div>

        <a
            href="<?= BASE_URL ?>/app/controllers/ExercicioController.php"
            class="exercicios-btn exercicios-btn-secundario">
            ← Voltar
        </a>

    </header>

    <!-- =====================================
         CARD PRINCIPAL
         ===================================== -->

    <section class="exercicios-cadastro-card">

        <div class="exercicios-card-header">

            <h2>Informações do exercício</h2>

            <p>
                Altere os dados necessários e
                salve para atualizar a biblioteca.
            </p>

        </div>

        <!-- =================================
             FORMULÁRIO DE EDIÇÃO
             ================================= -->

        <form
            id="formEditarExercicio"
            action="<?= BASE_URL ?>/app/controllers/ExercicioController.php"
            method="POST"
            enctype="multipart/form-data"
            class="exercicios-form">

            <!-- Mantém o contrato do controller -->

            <input
                type="hidden"
                name="acao"
                value="editar">

            <input
                type="hidden"
                name="id"
                value="<?= $idExercicio ?>">

            <div class="exercicios-form-grid">

                <!-- =========================
                     NOME
                     ========================= -->

                <div class="exercicios-campo">

                    <label for="nome">
                        Nome do exercício
                        <span class="campo-obrigatorio">*</span>
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        value="<?= htmlspecialchars(
                                    $nomeAtual,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                        maxlength="150"
                        required>

                </div>

                <!-- =========================
                     GRUPO MUSCULAR
                     ========================= -->

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
                                value="<?= htmlspecialchars(
                                            $grupo,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                <?= $grupoAtual === $grupo
                                    ? 'selected'
                                    : '' ?>>
                                <?= htmlspecialchars(
                                    $grupo,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- =========================
                     TIPO DE MÍDIA
                     ========================= -->

                <div class="exercicios-campo">

                    <label for="tipo_midia">
                        Tipo de mídia
                        <span class="campo-obrigatorio">*</span>
                    </label>

                    <select
                        id="tipo_midia"
                        name="tipo_midia"
                        required>

                        <option
                            value="imagem"
                            <?= $tipoMidiaAtual === 'imagem'
                                ? 'selected'
                                : '' ?>>
                            Imagem
                        </option>

                        <option
                            value="video"
                            <?= $tipoMidiaAtual === 'video'
                                ? 'selected'
                                : '' ?>>
                            Vídeo
                        </option>

                    </select>

                </div>

                <!-- =========================
                     SUBSTITUIR ARQUIVO
                     ========================= -->

                <div class="exercicios-campo">

                    <label for="arquivo">
                        Substituir mídia atual
                    </label>

                    <input
                        type="file"
                        id="arquivo"
                        name="arquivo"
                        accept=".jpg,.jpeg,.png,.webp,.mp4,.webm">

                    <small
                        class="exercicios-ajuda"
                        id="ajudaArquivo">
                        Deixe em branco para manter
                        a mídia atual.
                    </small>

                    <p
                        id="erroArquivo"
                        class="exercicios-validacao"
                        role="alert"
                        hidden></p>

                </div>

            </div>

            <!-- =================================
                 MÍDIA ATUAL
                 ================================= -->

            <div class="exercicios-midia-section">

                <h3 class="exercicios-midia-title">
                    Mídia atual
                </h3>

                <p class="exercicios-midia-description">
                    Confira o arquivo associado
                    a este exercício.
                </p>

                <div class="exercicios-midia-preview">

                    <?php if (
                        $tipoMidiaAtual === 'imagem' &&
                        $midiaAtual !== ''
                    ): ?>

                        <img
                            src="<?= htmlspecialchars(
                                        $midiaAtual,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                            alt="Imagem do exercício <?= htmlspecialchars(
                                                            $nomeAtual,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>">

                    <?php elseif (
                        $tipoMidiaAtual === 'video' &&
                        $midiaAtual !== ''
                    ): ?>

                        <video
                            src="<?= htmlspecialchars(
                                        $midiaAtual,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                            controls
                            preload="metadata">
                            Seu navegador não suporta vídeos.
                        </video>

                    <?php else: ?>

                        <p class="exercicios-midia-vazia">
                            Nenhuma mídia disponível.
                        </p>

                    <?php endif; ?>

                </div>

                <p class="exercicios-midia-aviso">
                    A mídia atual será mantida caso
                    você não selecione outro arquivo
                    e não altere o tipo de mídia.
                </p>

            </div>

            <!-- =================================
                 AÇÕES
                 ================================= -->

            <div class="exercicios-form-actions">

                <a
                    href="<?= BASE_URL ?>/app/controllers/ExercicioController.php"
                    class="exercicios-btn exercicios-btn-secundario">
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="exercicios-btn exercicios-btn-primario">
                    Salvar Alterações
                </button>

            </div>

        </form>

    </section>

</main>

<!-- ========================================
     VALIDAÇÃO DA MÍDIA
     ======================================== -->

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const form = document.getElementById(
            'formEditarExercicio'
        );

        const tipoMidia = document.getElementById(
            'tipo_midia'
        );

        const arquivo = document.getElementById(
            'arquivo'
        );

        const ajudaArquivo = document.getElementById(
            'ajudaArquivo'
        );

        const erroArquivo = document.getElementById(
            'erroArquivo'
        );

        const tipoOriginal = <?= json_encode(
                                    $tipoMidiaAtual,
                                    JSON_HEX_TAG |
                                        JSON_HEX_AMP |
                                        JSON_HEX_APOS |
                                        JSON_HEX_QUOT
                                ) ?>;

        const existeMidiaAtual = <?= $midiaAtual !== ''
                                        ? 'true'
                                        : 'false' ?>;

        function atualizarCampoArquivo() {

            const mudouTipo =
                tipoMidia.value !== tipoOriginal;

            // É obrigatório escolher um novo arquivo
            // se o tipo foi alterado ou se não há
            // mídia anterior para manter.
            arquivo.required =
                mudouTipo || !existeMidiaAtual;

            if (tipoMidia.value === 'video') {

                arquivo.accept = '.mp4,.webm';

                ajudaArquivo.textContent = mudouTipo ?
                    'Selecione um vídeo MP4 ou WEBM para substituir a imagem atual.' :
                    'Opcional: selecione um novo vídeo MP4 ou WEBM.';

            } else {

                arquivo.accept =
                    '.jpg,.jpeg,.png,.webp';

                ajudaArquivo.textContent = mudouTipo ?
                    'Selecione uma imagem JPG, JPEG, PNG ou WEBP para substituir o vídeo atual.' :
                    'Opcional: selecione uma nova imagem JPG, JPEG, PNG ou WEBP.';

            }

            // Evita manter um arquivo selecionado
            // incompatível após trocar o tipo.
            if (arquivo.files.length > 0) {

                const nome = arquivo.files[0].name;
                const extensao = nome
                    .split('.')
                    .pop()
                    .toLowerCase();

                const permitidas =
                    tipoMidia.value === 'video' ?
                    ['mp4', 'webm'] :
                    ['jpg', 'jpeg', 'png', 'webp'];

                if (!permitidas.includes(extensao)) {
                    arquivo.value = '';
                }
            }

            erroArquivo.textContent = '';
            erroArquivo.hidden = true;
        }

        tipoMidia.addEventListener(
            'change',
            atualizarCampoArquivo
        );

        arquivo.addEventListener('change', function() {
            erroArquivo.textContent = '';
            erroArquivo.hidden = true;
        });

        form.addEventListener('submit', function(event) {

            atualizarCampoArquivo();

            if (arquivo.required && arquivo.files.length === 0) {

                event.preventDefault();

                erroArquivo.textContent =
                    'Selecione uma nova mídia para continuar.';

                erroArquivo.hidden = false;

                arquivo.focus();

                return;
            }

            if (arquivo.files.length > 0) {

                const arquivoSelecionado = arquivo.files[0];

                const limiteMB =
                    tipoMidia.value === 'video' ? 50 : 5;

                const limiteBytes =
                    limiteMB * 1024 * 1024;

                if (arquivoSelecionado.size > limiteBytes) {

                    event.preventDefault();

                    erroArquivo.textContent =
                        'O arquivo ultrapassa o limite de ' +
                        limiteMB + ' MB.';

                    erroArquivo.hidden = false;

                    arquivo.focus();
                }
            }
        });

        atualizarCampoArquivo();
    });
</script>

<?php
require __DIR__ . '/../shared/footer.php';
?>