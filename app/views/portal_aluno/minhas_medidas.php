<?php
// ================================================
// GYMFLOW - MINHAS MEDIDAS
// app/views/portal_aluno/minhas_medidas.php
// ================================================

/** @var array $dadosMedidasPortal */

// ================================================
// 1. VALIDAR ACESSO
// ================================================

if (
    !defined('BASE_URL') ||
    !isset($dadosMedidasPortal) ||
    !is_array($dadosMedidasPortal)
) {
    http_response_code(403);
    exit('Acesso negado.');
}

// ================================================
// 2. INICIALIZAR VARIÁVEIS
// ================================================

$avaliacoesMedidas =
    $dadosMedidasPortal['avaliacoes'] ?? [];

$ultimaAvaliacao =
    $avaliacoesMedidas[0] ?? null;

$avaliacoesAnteriores =
    array_slice($avaliacoesMedidas, 1);

$tituloPagina = 'Minhas Medidas';

$cssEspecifico = BASE_URL .
    '/assets/css/minhas-medidas.css';

// ================================================
// 3. DEFINIÇÃO DOS CAMPOS
// ================================================

$composicaoCorporal = [

    'peso' => ['Peso', 'kg', 2],

    'altura' => ['Altura', 'm', 2],

    'gordura' => ['Gordura corporal', '%', 2],

    'massa_magra' => ['Massa magra', 'kg', 2]

];

$medidasCorporais = [

    'braco' => ['Braço', 'cm', 1],

    'peitoral' => ['Peitoral', 'cm', 1],

    'abdomen' => ['Abdômen', 'cm', 1],

    'cintura' => ['Cintura', 'cm', 1],

    'quadril' => ['Quadril', 'cm', 1],

    'coxa' => ['Coxa', 'cm', 1],

    'panturrilha' => ['Panturrilha', 'cm', 1]

];

// ================================================
// 4. FUNÇÕES AUXILIARES
// ================================================

$escMedidas = static function ($valor): string {

    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};

$formatarDataMedidas = static function ($data): string {

    if (empty($data)) {
        return 'Não informado';
    }

    $timestamp = strtotime((string) $data);

    return $timestamp !== false
        ? date('d/m/Y', $timestamp)
        : 'Não informado';
};

$formatarValorMedida = static function (
    $valor,
    string $unidade,
    int $casas
): string {

    if (
        $valor === null ||
        $valor === '' ||
        !is_numeric($valor)
    ) {
        return '—';
    }

    $numero = number_format(
        (float) $valor,
        $casas,
        ',',
        '.'
    );

    return $unidade === '%'
        ? $numero . '%'
        : $numero . ' ' . $unidade;
};

// ================================================
// 5. COMPONENTE DE TABELA
// ================================================

$renderizarTabelaMedidas = static function (
    array $avaliacao,
    array $campos,
    string $titulo
) use (
    $escMedidas,
    $formatarValorMedida
): void {

?>

    <div class="mm-grupo">

        <h3><?= $escMedidas($titulo) ?></h3>

        <table class="mm-tabela"
            aria-label="<?= $escMedidas($titulo) ?>">

            <tbody>

                <?php foreach ($campos as $campo => $config): ?>

                    <tr>

                        <th scope="row">

                            <?= $escMedidas($config[0]) ?>

                        </th>

                        <td>

                            <?= $escMedidas(
                                $formatarValorMedida(
                                    $avaliacao[$campo] ?? null,
                                    $config[1],
                                    $config[2]
                                )
                            ) ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

<?php

};

// ================================================
// 6. LAYOUT
// ================================================

require __DIR__ . '/../shared/header.php';
require __DIR__ . '/../shared/sidebar.php';

?>

<main class="portal-aluno minhas-medidas-page">

    <!-- CABEÇALHO -->

    <header class="mm-cabecalho">

        <h1>Minhas Medidas</h1>

        <p>
            Consulte os resultados das suas avaliações físicas.
        </p>

    </header>

    <?php if ($ultimaAvaliacao === null): ?>

        <!-- ESTADO VAZIO -->

        <section class="mm-painel mm-vazio">

            <i data-lucide="clipboard-list"></i>

            <h2>Nenhuma avaliação disponível</h2>

            <p>
                As suas medidas serão exibidas quando
                o professor registrar uma avaliação física.
            </p>

        </section>

    <?php else: ?>

        <!-- ÚLTIMA AVALIAÇÃO -->

        <section class="mm-painel">

            <div class="mm-painel-cabecalho">

                <div>

                    <h2>Última avaliação</h2>

                    <p>

                        Realizada em

                        <?= $escMedidas(
                            $formatarDataMedidas(
                                $ultimaAvaliacao['data'] ?? null
                            )
                        ) ?>

                    </p>

                </div>

                <span class="mm-somente-leitura">

                    <i data-lucide="lock"></i>

                    Somente leitura

                </span>

            </div>

            <div class="mm-medidas-grid">

                <?php

                $renderizarTabelaMedidas(
                    $ultimaAvaliacao,
                    $composicaoCorporal,
                    'Composição corporal'
                );

                $renderizarTabelaMedidas(
                    $ultimaAvaliacao,
                    $medidasCorporais,
                    'Medidas corporais'
                );

                ?>

            </div>

        </section>

        <!-- HISTÓRICO -->

        <section class="mm-painel">

            <div class="mm-painel-cabecalho">

                <div>

                    <h2>Histórico de avaliações</h2>

                    <p>
                        Consulte os seus registros anteriores.
                    </p>

                </div>

                <span class="mm-quantidade">

                    <?= count($avaliacoesAnteriores) ?>

                    <?= count($avaliacoesAnteriores) === 1
                        ? 'registro'
                        : 'registros' ?>

                </span>

            </div>

            <?php if (empty($avaliacoesAnteriores)): ?>

                <p class="mm-sem-historico">

                    Você ainda não possui avaliações anteriores.

                </p>

            <?php else: ?>

                <div class="mm-historico">

                    <div class="mm-historico-colunas">

                        <span>Data</span>
                        <span>Peso</span>
                        <span>Gordura corporal</span>
                        <span></span>

                    </div>

                    <?php foreach (
                        $avaliacoesAnteriores as $avaliacao
                    ): ?>

                        <details class="mm-registro">

                            <summary>

                                <span class="mm-registro-data">

                                    <?= $escMedidas(
                                        $formatarDataMedidas(
                                            $avaliacao['data'] ?? null
                                        )
                                    ) ?>

                                </span>

                                <span>

                                    <?= $escMedidas(
                                        $formatarValorMedida(
                                            $avaliacao['peso'] ?? null,
                                            'kg',
                                            2
                                        )
                                    ) ?>

                                </span>

                                <span>

                                    <?= $escMedidas(
                                        $formatarValorMedida(
                                            $avaliacao['gordura'] ?? null,
                                            '%',
                                            2
                                        )
                                    ) ?>

                                </span>

                                <i data-lucide="chevron-down"></i>

                            </summary>

                            <div class="mm-registro-conteudo">

                                <div class="mm-medidas-grid">

                                    <?php

                                    $renderizarTabelaMedidas(
                                        $avaliacao,
                                        $composicaoCorporal,
                                        'Composição corporal'
                                    );

                                    $renderizarTabelaMedidas(
                                        $avaliacao,
                                        $medidasCorporais,
                                        'Medidas corporais'
                                    );

                                    ?>

                                </div>

                            </div>

                        </details>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    <?php endif; ?>

    <!-- INFORMAÇÃO FINAL -->

    <p class="mm-observacao">

        As avaliações físicas são registradas
        exclusivamente pelo professor. Em caso de dúvidas,
        consulte o profissional responsável pelo seu acompanhamento.

    </p>

</main>

<?php require __DIR__ . '/../shared/footer.php'; ?>