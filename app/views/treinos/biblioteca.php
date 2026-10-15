<?php
// ============================================
// GYMFLOW - BIBLIOTECA DE EXERCÍCIOS
// app/views/treinos/biblioteca.php
// ============================================

if (!isset($tituloPagina)) {
    header(
        "Location: " . BASE_URL .
            "/app/controllers/ExercicioController.php"
    );
    exit;
}

$tituloPagina = "Biblioteca de Exercícios";
$cssEspecifico = BASE_URL . '/assets/css/biblioteca.css';

$pesquisa = (string) ($pesquisa ?? '');
$grupo = (string) ($grupo ?? '');
$exercicios = $exercicios ?? [];

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

if ($grupo !== '' && !in_array($grupo, $gruposMusculares, true)) {
    $gruposMusculares[] = $grupo;
    sort($gruposMusculares, SORT_NATURAL);
}

$mensagensSucesso = [
    'cadastrado' => 'Exercício cadastrado com sucesso!',
    'atualizado' => 'Exercício atualizado com sucesso!',
    'excluido' => 'Exercício desativado com sucesso!'
];

$mensagensErro = [
    'invalido' => 'O exercício informado é inválido.',
    'nao_encontrado' => 'O exercício não foi encontrado.',
    'exclusao' => 'Não foi possível excluir o exercício.'
];

$codigoSucesso = (string) ($_GET['sucesso'] ?? '');
$codigoErro = (string) ($_GET['erro'] ?? '');

$mensagemSucesso = $mensagensSucesso[$codigoSucesso] ?? '';
$mensagemErro = $mensagensErro[$codigoErro] ?? '';

if ($mensagemErro === '' && $codigoErro !== '') {
    $mensagemErro = 'Não foi possível concluir a operação.';
}

$urlController = BASE_URL .
    '/app/controllers/ExercicioController.php';

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
                Consulte, cadastre e gerencie os exercícios
                disponíveis para a montagem dos treinos.
            </p>

        </div>

        <a
            href="<?= htmlspecialchars(
                        $urlController . '?acao=novo',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
            class="biblioteca-btn biblioteca-btn-primary">
            <span aria-hidden="true">+</span>
            Novo exercício
        </a>

    </header>

    <!-- MENSAGENS -->

    <?php if ($mensagemSucesso !== ''): ?>
        <div class="biblioteca-alerta biblioteca-alerta-sucesso"
            role="status">
            <?= htmlspecialchars(
                $mensagemSucesso,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>
    <?php endif; ?>

    <?php if ($mensagemErro !== ''): ?>
        <div class="biblioteca-alerta biblioteca-alerta-erro"
            role="alert">
            <?= htmlspecialchars(
                $mensagemErro,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>
    <?php endif; ?>

    <!-- FILTROS -->

    <section class="biblioteca-filtros-card">

        <div class="biblioteca-section-title">
            <div>
                <h2>Pesquisar exercícios</h2>
                <p>Encontre exercícios pelo nome ou grupo muscular.</p>
            </div>
        </div>

        <form
            method="GET"
            action="<?= htmlspecialchars(
                        $urlController,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
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
                    value="<?= htmlspecialchars(
                                $pesquisa,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">

            </div>

            <div class="biblioteca-campo">

                <label for="grupo">
                    Grupo muscular
                </label>

                <select id="grupo" name="grupo">

                    <option value="">
                        Todos os grupos
                    </option>

                    <?php foreach ($gruposMusculares as $opcao): ?>
                        <option
                            value="<?= htmlspecialchars(
                                        $opcao,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                            <?= $grupo === $opcao
                                ? 'selected'
                                : '' ?>>
                            <?= htmlspecialchars(
                                $opcao,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
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
                    href="<?= htmlspecialchars(
                                $urlController,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                    class="biblioteca-btn biblioteca-btn-secondary">
                    Limpar
                </a>

            </div>

        </form>

        <p class="biblioteca-filtro-ajuda">
            A pesquisa por nome tem prioridade sobre o filtro
            de grupo muscular na versão atual do sistema.
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
                    ? ' exercício'
                    : ' exercícios' ?>
            </span>

        </div>

        <?php if (!empty($exercicios)): ?>

            <div class="exercicios-grid">

                <?php foreach ($exercicios as $exercicio): ?>

                    <?php
                    $id = (int) ($exercicio['id'] ?? 0);
                    $nome = (string) ($exercicio['nome'] ?? '');
                    $grupoExercicio = (string) ($exercicio['grupo'] ?? '');
                    $tipoMidia = (string) ($exercicio['tipo_midia'] ?? '');
                    $midia = (string) ($exercicio['midia'] ?? '');

                    $urlEditar = $urlController .
                        '?acao=editar&id=' . $id;

                    $urlExcluir = $urlController .
                        '?acao=excluir&id=' . $id;

                    $extensao = strtolower(
                        pathinfo(
                            parse_url($midia, PHP_URL_PATH) ?: '',
                            PATHINFO_EXTENSION
                        )
                    );

                    $mimeVideo = $extensao === 'webm'
                        ? 'video/webm'
                        : 'video/mp4';
                    ?>

                    <article class="exercicio-card">

                        <!-- MÍDIA -->

                        <div class="exercicio-midia">

                            <?php if ($midia === ''): ?>

                                <div class="exercicio-sem-midia">
                                    <span aria-hidden="true">▧</span>
                                    <p>Sem mídia disponível</p>
                                </div>

                            <?php elseif ($tipoMidia === 'video'): ?>

                                <video
                                    controls
                                    preload="metadata"
                                    playsinline
                                    aria-label="Vídeo do exercício <?= htmlspecialchars(
                                                                        $nome,
                                                                        ENT_QUOTES,
                                                                        'UTF-8'
                                                                    ) ?>">
                                    <source
                                        src="<?= htmlspecialchars(
                                                    $midia,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                        type="<?= $mimeVideo ?>">
                                    Seu navegador não suporta vídeos.
                                </video>

                            <?php elseif ($tipoMidia === 'imagem'): ?>

                                <img
                                    src="<?= htmlspecialchars(
                                                $midia,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                    alt="Demonstração do exercício <?= htmlspecialchars(
                                                                        $nome,
                                                                        ENT_QUOTES,
                                                                        'UTF-8'
                                                                    ) ?>"
                                    loading="lazy">

                            <?php else: ?>

                                <div class="exercicio-sem-midia">
                                    <span aria-hidden="true">▧</span>
                                    <p>Mídia indisponível</p>
                                </div>

                            <?php endif; ?>

                        </div>

                        <!-- INFORMAÇÕES -->

                        <div class="exercicio-info">

                            <span class="exercicio-grupo">
                                <?= htmlspecialchars(
                                    $grupoExercicio !== ''
                                        ? $grupoExercicio
                                        : 'Sem grupo',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </span>

                            <h3>
                                <?= htmlspecialchars(
                                    $nome,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </h3>

                        </div>

                        <!-- AÇÕES -->

                        <div class="exercicio-acoes">

                            <a
                                href="<?= htmlspecialchars(
                                            $urlEditar,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                class="exercicio-btn-editar">
                                Editar
                            </a>

                            <!--
                              Mantido via GET para não quebrar
                              o controller atual.

                              Próxima etapa:
                              substituir por POST + CSRF.
                            -->
                            <a
                                href="<?= htmlspecialchars(
                                            $urlExcluir,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                class="exercicio-btn-excluir"
                                onclick="return confirm('Tem certeza que deseja desativar este exercício?');">
                                Excluir
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="nenhum-exercicio">

                <div class="nenhum-exercicio-icone"
                    aria-hidden="true">
                    ▧
                </div>

                <h3>Nenhum exercício encontrado</h3>

                <p>
                    Não existem exercícios cadastrados
                    ou nenhum resultado corresponde
                    aos filtros informados.
                </p>

                <a
                    href="<?= htmlspecialchars(
                                $urlController,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                    class="biblioteca-btn biblioteca-btn-secondary">
                    Limpar filtros
                </a>

            </div>

        <?php endif; ?>

    </section>

</main>

<?php
require __DIR__ . '/../shared/footer.php';
?>