<?php
// ========================================
// GYMFLOW - BIBLIOTECA DE EXERCÍCIOS
// app/views/treinos/biblioteca.php
// ========================================

if (!isset($tituloPagina)) {

    header(
        'Location: ' . BASE_URL .
            '/app/controllers/ExercicioController.php'
    );

    exit;
}

$tituloPagina = 'Biblioteca de Exercícios';

$cssEspecifico = BASE_URL .
    '/assets/css/biblioteca-midia.css';

$pesquisa = (string) ($pesquisa ?? '');
$grupo = (string) ($grupo ?? '');
$exercicios = $exercicios ?? [];

$urlController = BASE_URL .
    '/app/controllers/ExercicioController.php';

$esc = static function ($valor): string {

    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
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
    $grupo !== '' &&
    !in_array($grupo, $gruposMusculares, true)
) {

    $gruposMusculares[] = $grupo;
}

sort($gruposMusculares, SORT_NATURAL);

$mensagensSucesso = [
    'cadastrado' => 'Exercício cadastrado com sucesso!',
    'atualizado' => 'Exercício atualizado com sucesso!',
    'excluido' => 'Exercício desativado com sucesso!'
];

$mensagensErro = [
    'invalido' => 'Exercício inválido.',
    'nao_encontrado' => 'Exercício não encontrado.',
    'exclusao' => 'Não foi possível excluir o exercício.'
];

$codigoSucesso = $_GET['sucesso'] ?? '';
$codigoErro = $_GET['erro'] ?? '';

$mensagemSucesso = is_string($codigoSucesso)
    ? ($mensagensSucesso[$codigoSucesso] ?? '')
    : '';

$mensagemErro = is_string($codigoErro) && $codigoErro !== ''
    ? ($mensagensErro[$codigoErro] ??
        'Não foi possível concluir a operação.')
    : '';

require __DIR__ . '/../shared/header.php';
require __DIR__ . '/../shared/sidebar.php';
?>

<main class="conteudo biblioteca-page">

    <!-- CABEÇALHO -->

    <header class="biblioteca-header">

        <div class="biblioteca-header-info">

            <span class="biblioteca-etiqueta">
                GESTÃO DE TREINOS
            </span>

            <h1>Biblioteca de Exercícios</h1>

            <p>
                Gerencie os exercícios e as mídias
                demonstrativas utilizadas nas fichas.
            </p>

        </div>

        <a
            href="<?= $esc($urlController . '?acao=novo') ?>"
            class="biblioteca-btn biblioteca-btn-primary">

            + Novo exercício

        </a>

    </header>

    <!-- MENSAGENS -->

    <?php if ($mensagemSucesso !== ''): ?>

        <div
            class="biblioteca-alerta biblioteca-alerta-sucesso"
            role="status">

            <?= $esc($mensagemSucesso) ?>

        </div>

    <?php endif; ?>

    <?php if ($mensagemErro !== ''): ?>

        <div
            class="biblioteca-alerta biblioteca-alerta-erro"
            role="alert">

            <?= $esc($mensagemErro) ?>

        </div>

    <?php endif; ?>

    <!-- FILTROS -->

    <section class="biblioteca-filtros-card">

        <div class="biblioteca-section-title">

            <div>

                <h2>Pesquisar exercícios</h2>

                <p>
                    Encontre exercícios pelo nome
                    ou grupo muscular.
                </p>

            </div>

        </div>

        <form
            method="GET"
            action="<?= $esc($urlController) ?>"
            class="biblioteca-filtros-form">

            <div class="biblioteca-campo">

                <label for="pesquisa">
                    Pesquisar pelo nome
                </label>

                <input
                    type="search"
                    id="pesquisa"
                    name="pesquisa"
                    placeholder="Ex.: Agachamento"
                    value="<?= $esc($pesquisa) ?>">

            </div>

            <div class="biblioteca-campo">

                <label for="grupo">Grupo muscular</label>

                <select id="grupo" name="grupo">

                    <option value="">
                        Todos os grupos
                    </option>

                    <?php foreach ($gruposMusculares as $opcao): ?>

                        <option
                            value="<?= $esc($opcao) ?>"
                            <?= $grupo === $opcao
                                ? 'selected'
                                : '' ?>>

                            <?= $esc($opcao) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="biblioteca-filtros-acoes">

                <button
                    type="submit"
                    class="biblioteca-btn biblioteca-btn-primary">

                    Buscar

                </button>

                <a
                    href="<?= $esc($urlController) ?>"
                    class="biblioteca-btn biblioteca-btn-secondary">

                    Limpar

                </a>

            </div>

        </form>

        <p class="biblioteca-filtro-ajuda">

            A pesquisa por nome tem prioridade
            sobre o filtro de grupo muscular.

        </p>

    </section>

    <!-- RESULTADOS -->

    <section class="biblioteca-resultados">

        <div class="biblioteca-resultados-header">

            <div>

                <h2>Exercícios cadastrados</h2>

                <p>
                    Gerencie os exercícios da sua biblioteca.
                </p>

            </div>

            <span class="biblioteca-contador">

                <?= count($exercicios) ?>

                <?= count($exercicios) === 1
                    ? 'exercício'
                    : 'exercícios' ?>

            </span>

        </div>

        <?php if (!empty($exercicios)): ?>

            <div class="exercicios-grid">

                <?php foreach ($exercicios as $exercicio): ?>

                    <?php

                    $id = (int) ($exercicio['id'] ?? 0);

                    $nome = (string) (
                        $exercicio['nome'] ?? ''
                    );

                    $grupoExercicio = (string) (
                        $exercicio['grupo'] ?? ''
                    );

                    $midia = trim(
                        (string) ($exercicio['midia'] ?? '')
                    );

                    $tipo = (string) (
                        $exercicio['tipo_midia'] ?? ''
                    );

                    $extensao = strtolower(
                        pathinfo(
                            parse_url($midia, PHP_URL_PATH) ?: '',
                            PATHINFO_EXTENSION
                        )
                    );

                    $possuiMidia =
                        $midia !== '' &&
                        in_array(
                            $tipo,
                            ['imagem', 'video'],
                            true
                        );

                    $urlEditar = $urlController .
                        '?acao=editar&id=' . $id;

                    $urlExcluir = $urlController .
                        '?acao=excluir&id=' . $id;

                    ?>

                    <article class="exercicio-card">

                        <!-- MÍDIA RESPONSIVA -->

                        <div class="exercicio-midia">

                            <?php if (
                                $possuiMidia &&
                                $tipo === 'video'
                            ): ?>

                                <video
                                    src="<?= $esc($midia) ?>"
                                    controls
                                    playsinline
                                    preload="metadata"
                                    aria-label="Vídeo de <?= $esc($nome) ?>"
                                    onerror="this.hidden=true;this.nextElementSibling.hidden=false;">
                                </video>

                            <?php elseif (
                                $possuiMidia &&
                                $tipo === 'imagem'
                            ): ?>

                                <img
                                    src="<?= $esc($midia) ?>"
                                    alt="Demonstração de <?= $esc($nome) ?>"
                                    loading="lazy"
                                    onerror="this.hidden=true;this.nextElementSibling.hidden=false;">

                            <?php endif; ?>

                            <div
                                class="exercicio-sem-midia"
                                <?= $possuiMidia ? 'hidden' : '' ?>>

                                <i data-lucide="image-off"></i>

                                <p>Mídia indisponível</p>

                            </div>

                        </div>

                        <!-- INFORMAÇÕES -->

                        <div class="exercicio-info">

                            <span class="exercicio-grupo">

                                <?= $esc(
                                    $grupoExercicio !== ''
                                        ? $grupoExercicio
                                        : 'Sem grupo'
                                ) ?>

                            </span>

                            <?php if (in_array(
                                $extensao,
                                [
                                    'jpg',
                                    'jpeg',
                                    'png',
                                    'webp',
                                    'gif',
                                    'mp4',
                                    'webm'
                                ],
                                true
                            )): ?>

                                <span class="exercicio-formato">

                                    <?= $esc(strtoupper($extensao)) ?>

                                </span>

                            <?php endif; ?>

                            <h3>
                                <?= $esc($nome) ?>
                            </h3>

                        </div>

                        <!-- AÇÕES -->

                        <div class="exercicio-acoes">

                            <a
                                href="<?= $esc($urlEditar) ?>"
                                class="exercicio-btn-editar">

                                Editar

                            </a>

                            <a
                                href="<?= $esc($urlExcluir) ?>"
                                class="exercicio-btn-excluir"
                                onclick="return confirm('Deseja desativar este exercício?');">

                                Excluir

                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="nenhum-exercicio">

                <div class="nenhum-exercicio-icone">

                    <i data-lucide="dumbbell"></i>

                </div>

                <h3>Nenhum exercício encontrado</h3>

                <p>
                    Nenhum resultado corresponde aos filtros.
                </p>

                <a
                    href="<?= $esc($urlController) ?>"
                    class="biblioteca-btn biblioteca-btn-secondary">

                    Limpar filtros

                </a>

            </div>

        <?php endif; ?>

    </section>

</main>

<?php require __DIR__ . '/../shared/footer.php'; ?>