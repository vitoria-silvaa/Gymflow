<?php
// ============================================
// GYMFLOW - INÍCIO DO PORTAL DO ALUNO
// app/views/portal_aluno/aluno.php
// ============================================

if (!isset($inicioPortal)) {

    http_response_code(403);

    exit('Acesso negado.');
}

$tituloPagina = 'Portal do Aluno';

$cssEspecifico = BASE_URL .
    '/assets/css/portal-inicio.css';

// ============================================
// DADOS DA PÁGINA
// ============================================

$aluno = $inicioPortal['aluno'];

$matricula = $inicioPortal['matricula'];

$statusPlano = $inicioPortal['status_plano'];

$diasCompletos = $inicioPortal['dias_completos'];

$diasParciais = $inicioPortal['dias_parciais'];

$faturasAbertas = $inicioPortal['faturas_abertas'];

$faturasAtrasadas = $inicioPortal['faturas_atrasadas'];

$mesAtual = $inicioPortal['mes_atual'];

$anoAtual = $inicioPortal['ano_atual'];

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

$escInicio = static function ($valor) {

    return htmlspecialchars(

        (string) ($valor ?? ''),

        ENT_QUOTES,

        'UTF-8'
    );
};

$formatarDataInicio = static function ($valor) {

    if (empty($valor)) {

        return 'Não informado';
    }

    $timestamp = strtotime($valor);

    return $timestamp !== false

        ? date('d/m/Y', $timestamp)

        : 'Não informado';
};

// ============================================
// ROTAS DO PORTAL
// ============================================

$urlInicio = BASE_URL .
    '/app/controllers/PortalAlunoController.php';

$urlTreinos = $urlInicio . '?acao=treinos';

$urlFrequencia = $urlTreinos .
    '&visualizacao=frequencia';

$urlFaturas = $urlInicio . '?acao=faturas';

$urlContratos = $urlInicio . '?acao=contratos';

$urlAvaliacao = $urlInicio . '?acao=avaliacao';

$urlFaturaPendente = $urlFaturas;

if ($faturasAtrasadas > 0) {

    $urlFaturaPendente .= '&aba=atraso';
}

// ============================================
// STATUS DA MATRÍCULA
// ============================================

$statusLabels = [

    'ativo' => 'Plano ativo',

    'vencido' => 'Plano vencido',

    'futuro' => 'Aguardando início',

    'sem_plano' => 'Sem matrícula ativa'
];

$labelPlano = $statusLabels[$statusPlano]
    ?? 'Não informado';

// ============================================
// AÇÕES RÁPIDAS
// ============================================

$acoesRapidas = [

    [
        'titulo' => 'Meus treinos',
        'descricao' => 'Acesse suas fichas de treinamento.',
        'icone' => 'dumbbell',
        'url' => $urlTreinos
    ],

    [
        'titulo' => 'Minha frequência',
        'descricao' => 'Consulte seu calendário de treinos.',
        'icone' => 'calendar-days',
        'url' => $urlFrequencia
    ],

    [
        'titulo' => 'Minhas faturas',
        'descricao' => 'Consulte pagamentos e recibos.',
        'icone' => 'credit-card',
        'url' => $urlFaturas
    ],

    [
        'titulo' => 'Meus contratos',
        'descricao' => 'Consulte e baixe seus contratos.',
        'icone' => 'file-text',
        'url' => $urlContratos
    ],

    [
        'titulo' => 'Avaliar Academia',
        'descricao' => 'Avalie sua experiência na academia.',
        'icone' => 'star',
        'url' => $urlAvaliacao
    ]
];

require __DIR__ . '/../shared/header.php';

require __DIR__ . '/../shared/sidebar.php';

?>

<main class="portal-aluno inicio-aluno">

    <!-- ====================================
         CABEÇALHO
    ===================================== -->

    <header class="inicio-header">

        <div>

            <span class="inicio-eyebrow">

                PORTAL DO ALUNO

            </span>

            <h1>

                Olá, <?= $escInicio(
                            $aluno['nome'] ?? 'Aluno'
                        ) ?>!

            </h1>

            <p>

                Acompanhe seus treinos e sua rotina
                na academia.

            </p>

        </div>

        <div class="inicio-unidade">

            <i data-lucide="map-pin"></i>

            <div>

                <span>Minha unidade</span>

                <strong>

                    <?= $escInicio(
                        $aluno['nome_filial'] ?? ''
                    ) ?>

                </strong>

            </div>

        </div>

    </header>

    <!-- ====================================
         RESUMO PRINCIPAL
    ===================================== -->

    <section class="inicio-grid">

        <!-- PLANO -->

        <article class="inicio-card inicio-plano">

            <div class="inicio-card-topo">

                <div class="inicio-icone">

                    <i data-lucide="badge-check"></i>

                </div>

                <span class="inicio-status inicio-status-<?= $escInicio($statusPlano) ?>">

                    <?= $escInicio($labelPlano) ?>

                </span>

            </div>

            <span class="inicio-label">

                MEU PLANO

            </span>

            <h2 class="inicio-plano-nome">

                <?= $escInicio(
                    $matricula['nome_plano']
                        ?? 'Nenhum plano ativo'
                ) ?>

            </h2>

            <?php if ($matricula): ?>

                <div class="inicio-plano-info">

                    <div>

                        <span>Início</span>

                        <strong>

                            <?= $formatarDataInicio(
                                $matricula['inicio']
                            ) ?>

                        </strong>

                    </div>

                    <div>

                        <span>Validade</span>

                        <strong>

                            <?= $formatarDataInicio(
                                $matricula['fim']
                            ) ?>

                        </strong>

                    </div>

                </div>

            <?php else: ?>

                <p class="inicio-descricao">

                    Procure a recepção para realizar
                    ou renovar sua matrícula.

                </p>

            <?php endif; ?>

            <div class="inicio-card-acoes">

                <?php if ($statusPlano === 'ativo'): ?>

                    <a
                        href="<?= $escInicio($urlTreinos) ?>"
                        class="inicio-btn">

                        <i data-lucide="dumbbell"></i>

                        Treinar agora

                        <i
                            class="inicio-seta"
                            data-lucide="arrow-right">
                        </i>

                    </a>

                <?php else: ?>

                    <a
                        href="<?= $escInicio($urlContratos) ?>"
                        class="inicio-btn">

                        Consultar contratos

                        <i
                            class="inicio-seta"
                            data-lucide="arrow-right">
                        </i>

                    </a>

                <?php endif; ?>

            </div>

        </article>

        <!-- FREQUÊNCIA -->

        <article class="inicio-card">

            <div class="inicio-card-topo">

                <div class="inicio-icone inicio-icone-verde">

                    <i data-lucide="calendar-check"></i>

                </div>

            </div>

            <span class="inicio-label">

                FREQUÊNCIA DO MÊS

            </span>

            <strong class="inicio-metrica">

                <?= (int) $diasCompletos ?>

                <small>

                    <?= $diasCompletos === 1
                        ? 'dia completo'
                        : 'dias completos' ?>

                </small>

            </strong>

            <p class="inicio-descricao">

                <?= $escInicio($mesAtual) ?>

                de

                <?= $escInicio($anoAtual) ?>

            </p>

            <div class="inicio-frequencia-parcial">

                <span class="inicio-ponto-amarelo"></span>

                <?= (int) $diasParciais ?>

                <?= $diasParciais === 1
                    ? 'dia parcial'
                    : 'dias parciais' ?>

            </div>

            <div class="inicio-card-acoes">

                <a
                    href="<?= $escInicio($urlFrequencia) ?>"
                    class="inicio-link">

                    Ver calendário

                    <i data-lucide="arrow-right"></i>

                </a>

            </div>

        </article>

        <!-- FATURAS -->

        <article class="inicio-card">

            <div class="inicio-card-topo">

                <div class="inicio-icone <?= $faturasAtrasadas > 0
                                                ? 'inicio-icone-vermelho'
                                                : '' ?>">

                    <i data-lucide="wallet"></i>

                </div>

            </div>

            <span class="inicio-label">

                MINHAS FATURAS

            </span>

            <strong class="inicio-metrica">

                <?= (int) $faturasAbertas ?>

                <small>

                    <?= $faturasAbertas === 1
                        ? 'pendente'
                        : 'pendentes' ?>

                </small>

            </strong>

            <?php if ($faturasAtrasadas > 0): ?>

                <p class="inicio-texto-alerta">

                    <?= (int) $faturasAtrasadas ?>

                    <?= $faturasAtrasadas === 1
                        ? 'fatura em atraso'
                        : 'faturas em atraso' ?>

                </p>

            <?php elseif ($faturasAbertas > 0): ?>

                <p class="inicio-descricao">

                    Pagamentos a vencer.

                </p>

            <?php else: ?>

                <p class="inicio-texto-sucesso">

                    Seus pagamentos estão em dia!

                </p>

            <?php endif; ?>

            <div class="inicio-card-acoes">

                <a
                    href="<?= $escInicio($urlFaturaPendente) ?>"
                    class="inicio-link">

                    Ver faturas

                    <i data-lucide="arrow-right"></i>

                </a>

            </div>

        </article>

    </section>

    <!-- ====================================
         ACESSOS RÁPIDOS
    ===================================== -->

    <section class="inicio-acessos">

        <header class="inicio-section-header">

            <span class="inicio-eyebrow">

                ACESSO RÁPIDO

            </span>

            <h2>O que você deseja fazer?</h2>

        </header>

        <div class="inicio-acessos-grid">

            <?php foreach ($acoesRapidas as $acaoRapida): ?>

                <a
                    href="<?= $escInicio($acaoRapida['url']) ?>"
                    class="inicio-atalho">

                    <div class="inicio-atalho-icone">

                        <i data-lucide="<?= $escInicio(
                                            $acaoRapida['icone']
                                        ) ?>"></i>

                    </div>

                    <div class="inicio-atalho-texto">

                        <strong>

                            <?= $escInicio(
                                $acaoRapida['titulo']
                            ) ?>

                        </strong>

                        <span>

                            <?= $escInicio(
                                $acaoRapida['descricao']
                            ) ?>

                        </span>

                    </div>

                    <i
                        class="inicio-atalho-seta"
                        data-lucide="chevron-right">
                    </i>

                </a>

            <?php endforeach; ?>

        </div>

    </section>

</main>

<?php require __DIR__ . '/../shared/footer.php'; ?>