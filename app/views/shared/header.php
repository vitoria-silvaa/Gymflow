<?php
// =============================================
// GYMFLOW - HEADER GLOBAL
// app/views/shared/header.php
// =============================================

// =============================================
// 1. INFORMAÇÕES DO SISTEMA
// =============================================

$role = $_SESSION['usuario_role'] ?? '';

$usuario = $_SESSION['usuario_nome'] ?? 'Usuário';

$titulo = $tituloPagina ?? 'Dashboard';

$identidadeAlunoDisponivel = false;

// =============================================
// 2. IDENTIDADE INSTITUCIONAL DO ALUNO
// =============================================

if ($role === 'Aluno') {

    require_once __DIR__ .
        '/../../models/IdentidadePortalAluno.php';

    $identidadeAluno =
        identidadePadraoPortalAluno();

    try {

        require_once __DIR__ .
            '/../../models/Database.php';

        $identidadeAluno =
            carregarIdentidadePortalAluno($pdo);

        $identidadeAlunoDisponivel = true;
    } catch (Throwable $e) {

        error_log(
            '[GymFlow][IdentidadeAluno] ' .
                $e->getMessage()
        );
    }

    // Atualiza somente a identidade visual da sessão.

    foreach (
        $identidadeAluno as $chave => $valor
    ) {
        $_SESSION[$chave] = $valor;
    }

    // Token exclusivo para troca de tema.

    if (
        empty($_SESSION['tema_aluno_csrf']) ||
        !is_string($_SESSION['tema_aluno_csrf'])
    ) {

        $_SESSION['tema_aluno_csrf'] =
            bin2hex(random_bytes(32));
    }
}

// =============================================
// 3. IDENTIDADE VISUAL DO PAINEL
// =============================================

$nomePainel =
    $_SESSION['nome_painel'] ?? 'Gymflow';

$corPrimaria =
    $_SESSION['cor_primaria'] ?? '#ffb000';

$corSecundaria =
    $_SESSION['cor_secundaria'] ?? '#111827';

$temaPainel =
    $_SESSION['tema'] ?? 'dark';

// =============================================
// 4. VALIDAR CORES
// =============================================

function validarCorGymflow($cor, $padrao)
{
    if (!is_string($cor)) {
        return $padrao;
    }

    $cor = trim($cor);

    return preg_match(
        '/^#[0-9a-fA-F]{6}$/',
        $cor
    ) ? $cor : $padrao;
}

$corPrimaria = validarCorGymflow(
    $corPrimaria,
    '#ffb000'
);

$corSecundaria = validarCorGymflow(
    $corSecundaria,
    '#111827'
);

// =============================================
// 5. CONTRASTE AUTOMÁTICO
// =============================================

function corTextoContrasteGymflow($hex)
{
    $hex = ltrim($hex, '#');

    $r = hexdec(substr($hex, 0, 2)) / 255;
    $g = hexdec(substr($hex, 2, 2)) / 255;
    $b = hexdec(substr($hex, 4, 2)) / 255;

    $linearizar = static function ($canal) {

        return $canal <= 0.04045
            ? $canal / 12.92
            : (($canal + 0.055) / 1.055) ** 2.4;
    };

    $luminancia =
        0.2126 * $linearizar($r) +
        0.7152 * $linearizar($g) +
        0.0722 * $linearizar($b);

    $contrasteComBranco =
        1.05 / ($luminancia + 0.05);

    $contrasteComEscuro =
        ($luminancia + 0.05) / 0.05;

    return $contrasteComEscuro >= $contrasteComBranco
        ? '#000000'
        : '#ffffff';
}

$textoSobrePrimaria =
    corTextoContrasteGymflow($corPrimaria);

$textoSobreSecundaria =
    corTextoContrasteGymflow($corSecundaria);

// =============================================
// 6. APARÊNCIA DO PAINEL
// =============================================

$temaPainel = $temaPainel === 'dark'
    ? 'dark'
    : 'light';

if ($temaPainel === 'dark') {

    $corFundoHeader = $corSecundaria;
    $corTextoHeader = $textoSobreSecundaria;
    $corBordaHeader = '#334155';
} else {

    $corFundoHeader = '#ffffff';
    $corTextoHeader = '#111827';
    $corBordaHeader = '#e2e8f0';
}

// =============================================
// 7. CONFIGURAÇÕES DA TROCA DE TEMA
// =============================================

$proximoTema = $temaPainel === 'dark'
    ? 'light'
    : 'dark';

$rotuloTema = $proximoTema === 'dark'
    ? 'Ativar tema escuro'
    : 'Ativar tema claro';

$iconeTema = $proximoTema === 'dark'
    ? 'moon'
    : 'sun';

$acaoAtual = $_GET['acao'] ?? 'aluno';

$acoesPortal = [
    'aluno',
    'treinos',
    'faturas',
    'contratos',
    'avaliacao',
    'meus_dados'
];

if (!in_array(
    $acaoAtual,
    $acoesPortal,
    true
)) {
    $acaoAtual = 'aluno';
}

// =============================================
// 8. FUNÇÕES AUXILIARES
// =============================================

$escapar = static function ($valor) {

    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

// =============================================
// 9. VERSIONAMENTO DOS ARQUIVOS CSS
// =============================================

$versionarCss = static function ($url) {

    $prefixo = BASE_URL . '/assets/css/';

    if (!str_starts_with($url, $prefixo)) {
        return $url;
    }

    $partes = parse_url($url);

    if ($partes === false) {
        return $url;
    }

    $caminhoUrl = $partes['path'] ?? '';

    $arquivo = substr(
        $caminhoUrl,
        strlen($prefixo)
    );

    if (!preg_match(
        '/^[a-zA-Z0-9_-]+\.css$/',
        $arquivo
    )) {
        return $url;
    }

    $caminho = dirname(__DIR__, 3) .
        '/assets/css/' .
        $arquivo;

    if (!is_file($caminho)) {
        return $url;
    }

    $versao = filemtime($caminho);

    if ($versao === false) {
        return $url;
    }

    $parametros = [];

    parse_str(
        $partes['query'] ?? '',
        $parametros
    );

    $parametros['v'] = $versao;

    $urlVersionada = $caminhoUrl .
        '?' .
        http_build_query($parametros);

    if (isset($partes['fragment'])) {

        $urlVersionada .=
            '#' . $partes['fragment'];
    }

    return $urlVersionada;
};

$carregarCss = static function (
    $arquivo
) use ($versionarCss) {

    return $versionarCss(
        BASE_URL .
            '/assets/css/' .
            $arquivo
    );
};

$cssEspecifico = $cssEspecifico ?? null;

if (!empty($cssEspecifico)) {

    $cssEspecifico =
        $versionarCss($cssEspecifico);
}

?>

<!DOCTYPE html>
<html
    lang="pt-BR"
    data-theme="<?= $escapar($temaPainel) ?>">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?= $escapar($nomePainel) ?> |
        <?= $escapar($titulo) ?>
    </title>

    <!-- CSS GLOBAL -->

    <link
        rel="stylesheet"
        href="<?= $escapar(
                    $carregarCss('global.css')
                ) ?>">

    <!-- LAYOUT -->

    <link
        rel="stylesheet"
        href="<?= $escapar(
                    $carregarCss('layout.css')
                ) ?>">

    <!-- PORTAL DO ALUNO -->

    <?php if ($role === 'Aluno'): ?>

        <link
            rel="stylesheet"
            href="<?= $escapar(
                        $carregarCss('portal.css')
                    ) ?>">

        <link
            rel="stylesheet"
            href="<?= $escapar(
                        $carregarCss('treinos.css')
                    ) ?>">

    <?php endif; ?>

    <!-- CADASTROS -->

    <link
        rel="stylesheet"
        href="<?= $escapar(
                    $carregarCss('cadastros.css')
                ) ?>">

    <!-- CSS ESPECÍFICO -->

    <?php if (!empty($cssEspecifico)): ?>

        <link
            rel="stylesheet"
            href="<?= $escapar(
                        $cssEspecifico
                    ) ?>">

    <?php endif; ?>

    <!-- IDENTIDADE DO PORTAL -->

    <?php if ($role === 'Aluno'): ?>

        <link
            rel="stylesheet"
            href="<?= $escapar(
                        $carregarCss('portal-identidade.css')
                    ) ?>">

    <?php endif; ?>

    <!-- IDENTIDADE VISUAL DINÂMICA -->

    <style>
        :root {

            --cor-primaria: <?= $corPrimaria ?>;

            --cor-secundaria: <?= $corSecundaria ?>;

            --cor-sobre-primaria:
                <?= $textoSobrePrimaria ?>;

            --cor-sobre-secundaria:
                <?= $textoSobreSecundaria ?>;

            --header-fundo: <?= $corFundoHeader ?>;

            --header-texto: <?= $corTextoHeader ?>;

            --header-borda: <?= $corBordaHeader ?>;

            --fin-primary: var(--cor-primaria);

            --fin-primary-dark:
                var(--cor-primaria-hover);
        }

        body {
            background-color: var(--ui-fundo-pagina);
            color: var(--ui-texto);
        }

        body>.header {
            background-color: var(--header-fundo);
            color: var(--header-texto);
            border-bottom: 1px solid var(--header-borda);
        }

        body>.header h1,
        body>.header .header-direita,
        body>.header .header-direita span,
        body>.header .mobile-toggle {
            color: var(--header-texto);
        }

        body>#appSidebar {
            background-color: var(--cor-secundaria);
            color: var(--cor-sobre-secundaria);
        }
    </style>

</head>

<body class="<?= $role === 'Aluno'
                    ? 'perfil-aluno'
                    : '' ?>">

    <!-- OVERLAY MOBILE -->

    <div
        class="mobile-overlay"
        id="mobileOverlay"
        onclick="
            document.getElementById('appSidebar')
                .classList.remove('open');

            this.classList.remove('active');
        ">
    </div>

    <!-- CABEÇALHO -->

    <header class="header">

        <div class="header-esquerda">

            <button
                type="button"
                class="mobile-toggle"
                aria-label="Abrir ou fechar o menu"
                onclick="
                    document.getElementById('appSidebar')
                        .classList.toggle('open');

                    document.getElementById('mobileOverlay')
                        .classList.toggle('active');
                ">
                ☰
            </button>

            <h1>
                <?= $escapar($titulo) ?>
            </h1>

        </div>

        <div class="header-direita">

            <!-- TROCA DE TEMA EXCLUSIVA DO ALUNO -->

            <?php if (
                $role === 'Aluno' &&
                $identidadeAlunoDisponivel
            ): ?>

                <form
                    class="portal-tema-form"
                    method="POST"
                    action="<?= BASE_URL ?>/app/controllers/TemaAlunoController.php">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= $escapar(
                                    $_SESSION['tema_aluno_csrf']
                                ) ?>">

                    <input
                        type="hidden"
                        name="tema"
                        value="<?= $escapar(
                                    $proximoTema
                                ) ?>">

                    <input
                        type="hidden"
                        name="acao_retorno"
                        value="<?= $escapar(
                                    $acaoAtual
                                ) ?>">

                    <?php if (
                        $acaoAtual === 'faturas' &&
                        is_string($_GET['aba'] ?? null)
                    ): ?>

                        <input
                            type="hidden"
                            name="aba"
                            value="<?= $escapar(
                                        $_GET['aba']
                                    ) ?>">

                    <?php endif; ?>

                    <?php if (
                        $acaoAtual === 'treinos' &&
                        filter_var(
                            $_GET['ficha'] ?? null,
                            FILTER_VALIDATE_INT
                        )
                    ): ?>

                        <input
                            type="hidden"
                            name="ficha"
                            value="<?= (int) $_GET['ficha'] ?>">

                    <?php endif; ?>

                    <button
                        type="submit"
                        class="portal-tema-toggle"
                        aria-label="<?= $escapar($rotuloTema) ?>"
                        title="<?= $escapar($rotuloTema) ?>">

                        <i data-lucide="<?= $escapar(
                                            $iconeTema
                                        ) ?>"></i>

                    </button>

                </form>

            <?php endif; ?>

            <!-- USUÁRIO -->

            <span>
                <?= $escapar($usuario) ?>
                (<?= $escapar($role) ?>)
            </span>

        </div>

    </header>