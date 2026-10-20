<?php
// =============================================
// GYMFLOW - HEADER GLOBAL
// app/views/shared/header.php
// =============================================

// INFORMAÇÕES DO SISTEMA

$nomePainel = $_SESSION['nome_painel'] ?? 'Gymflow';
$titulo = $tituloPagina ?? 'Dashboard';

$usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$role = $_SESSION['usuario_role'] ?? 'Admin';

// =============================================
// 1. IDENTIDADE VISUAL
// =============================================

$corPrimaria = $_SESSION['cor_primaria'] ?? '#ffb000';
$corSecundaria = $_SESSION['cor_secundaria'] ?? '#111827';
$tema = $_SESSION['tema'] ?? 'light';

// Apenas cores hexadecimais são aceitas.
// Isso evita inserir valores inválidos
// ou CSS arbitrário no HTML.

function validarCorGymflow($cor, $padrao)
{
    if (!is_string($cor)) {
        return $padrao;
    }

    $cor = trim($cor);

    return preg_match('/^#[0-9a-fA-F]{6}$/', $cor)
        ? $cor
        : $padrao;
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
// 2. CONTRASTE AUTOMÁTICO
// =============================================

// Determina se o texto sobre determinada
// cor deve ser claro ou escuro.

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

    $contrasteComBranco = 1.05 / ($luminancia + 0.05);
    $contrasteComEscuro = ($luminancia + 0.05) / 0.05;

    return $contrasteComEscuro >= $contrasteComBranco
        ? '#000000'
        : '#ffffff';
}

$textoSobrePrimaria = corTextoContrasteGymflow(
    $corPrimaria
);

$textoSobreSecundaria = corTextoContrasteGymflow(
    $corSecundaria
);

// =============================================
// 3. TEMA DO CABEÇALHO
// =============================================

// Por enquanto, preservamos as páginas
// internas com fundo claro.
//
// A preferência dark/light controla o
// cabeçalho, como já acontecia anteriormente.
//
// Na etapa de temas, os demais componentes
// poderão consumir variáveis específicas.

$tema = $tema === 'dark' ? 'dark' : 'light';

if ($tema === 'dark') {
    $corFundoHeader = $corSecundaria;
    $corTextoHeader = $textoSobreSecundaria;
    $corBordaHeader = 'rgba(0, 0, 0, 0.1)';
} else {
    $corFundoHeader = '#ffffff';
    $corTextoHeader = '#111827';
    $corBordaHeader = '#e2e8f0';
}

// =============================================
// 4. CSS ESPECÍFICO
// =============================================

$cssEspecifico = $cssEspecifico ?? null;

$escapar = static function ($valor) {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?= $escapar($nomePainel) ?> |
        <?= $escapar($titulo) ?>
    </title>

    <!-- =====================================
         CSS GLOBAL
         ===================================== -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/global.css">

    <!-- =====================================
         LAYOUT COMPARTILHADO
         ===================================== -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/layout.css">

    <!-- =====================================
         CSS DO PORTAL DO ALUNO
         ===================================== -->

    <?php if ($role === 'Aluno'): ?>

        <link
            rel="stylesheet"
            href="<?= BASE_URL ?>/assets/css/portal.css">

        <link
            rel="stylesheet"
            href="<?= BASE_URL ?>/assets/css/treinos.css">

    <?php endif; ?>

    <!-- =====================================
         CSS DOS CADASTROS
         ===================================== -->

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/cadastros.css">

    <!-- =====================================
         CSS ESPECÍFICO DA PÁGINA
         ===================================== -->

    <?php if (!empty($cssEspecifico)): ?>

        <link
            rel="stylesheet"
            href="<?= $escapar($cssEspecifico) ?>">

    <?php endif; ?>

    <!-- =====================================
         VARIÁVEIS DA IDENTIDADE VISUAL
         ===================================== -->

    <style>
        :root {
            /*
             * Identidade visual da academia.
             *
             * Os valores são definidos pelo PHP,
             * a partir da sessão autenticada.
             */

            --cor-primaria: <?= $corPrimaria ?>;
            --cor-secundaria: <?= $corSecundaria ?>;

            --cor-sobre-primaria: <?= $textoSobrePrimaria ?>;
            --cor-sobre-secundaria: <?= $textoSobreSecundaria ?>;

            /*
             * Compatibilidade com módulos
             * financeiros existentes.
             */

            --fin-primary: var(--cor-primaria);
            --fin-primary-dark: var(--cor-primaria-hover);

            /*
             * Cores específicas do cabeçalho.
             */

            --header-fundo: <?= $corFundoHeader ?>;
            --header-texto: <?= $corTextoHeader ?>;
            --header-borda: <?= $corBordaHeader ?>;
        }

        /*
         * Preservamos o fundo claro do painel
         * enquanto os módulos são padronizados.
         */

        body {
            background-color: var(--ui-fundo-pagina);
            color: var(--ui-texto);
        }

        /*
         * Cabeçalho compartilhado.
         *
         * Agora depende de variáveis, sem
         * bloquear futuras personalizações.
         */

        body>.header {
            background-color: var(--header-fundo);
            color: var(--header-texto);

            border-bottom: 1px solid var(--header-borda);
        }

        body>.header h1,
        body>.header .header-direita,
        body>.header .header-direita span {
            color: var(--header-texto);
        }

        /*
         * Botão de menu no celular.
         *
         * Mantemos o posicionamento definido
         * em layout.css.
         */

        body>.header .mobile-toggle {
            color: var(--header-texto);
        }
    </style>

</head>

<body>

    <!-- =====================================
         OVERLAY DO MENU MOBILE
         ===================================== -->

    <div
        class="mobile-overlay"
        id="mobileOverlay"
        onclick="
            document.getElementById('appSidebar')
                .classList.remove('open');

            this.classList.remove('active');
        "></div>

    <!-- =====================================
         CABEÇALHO
         ===================================== -->

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

            <span>
                <?= $escapar($usuario) ?>
                (<?= $escapar($role) ?>)
            </span>

        </div>

    </header>