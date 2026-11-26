<?php
// app/views/financeiro/index.php

if (
    !isset($contas, $baseUrl) ||
    ($_SESSION['usuario_role'] ?? '') !== 'Admin'
) {
    http_response_code(403);
    exit;
}

$cssEspecifico =
    BASE_URL . '/assets/css/jorge-financeiro.css';

$h = static fn($v) => htmlspecialchars(
    (string) $v,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$moeda = static fn($v) =>
    'R$ ' . number_format((float) $v, 2, ',', '.');

$data = static fn($v) =>
    $v ? date('d/m/Y', strtotime($v)) : '—';

$abas = [
    'aberto' => 'Em aberto',
    'dinheiro' => 'Dinheiro',
    'digital' => 'Digital'
];

$titulosAbas = [
    'aberto' => 'Mensalidades em aberto',
    'dinheiro' => 'Pagamentos em dinheiro',
    'digital' => 'Pagamentos digitais',
    'outros' => 'Outros recebimentos'
];

if (
    (float) ($totais['outros'] ?? 0) !== 0.0 ||
    $aba === 'outros'
) {
    $abas['outros'] = 'Outros';
}

$filtros = [
    'nenhum' => 'Filtrar por',
    'nome' => 'Nome do aluno',
    'cpf' => 'CPF',
    'matricula' => 'Matrícula',
    'filial' => 'Filial',
    'periodo' => 'Período'
];

$buscaAtiva = in_array(
    $filtro,
    ['nome', 'cpf', 'matricula'],
    true
);

$personalizado =
    $filtro === 'periodo' &&
    $periodo === 'personalizado';

$consultaValida = empty($erro);

$exibirValor = static fn($valor) =>
    $consultaValida ? $moeda($valor) : '—';

$nomePagamento = static function ($metodo): string {

    $metodo = trim((string) $metodo);

    return match (strtolower($metodo)) {

        'pix' => 'Pix',

        'credito',
        'crédito',
        'cartao de credito',
        'cartão de crédito'
            => 'Cartão de crédito',

        'debito',
        'débito',
        'cartao de debito',
        'cartão de débito'
            => 'Cartão de débito',

        'dinheiro' => 'Dinheiro',

        'cartao',
        'cartão' => 'Cartão',

        '' => 'Não informado',

        default => $metodo
    };
};

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<style>

/* =========================================
   FINANCEIRO
========================================= */

.financeiro-painel {
    --painel-fundo: var(--fin-bg-card);
    --painel-borda: var(--fin-border-color);
    --painel-texto: var(--fin-text-main);
    --painel-suave: var(--fin-text-muted);
    --painel-primaria: var(--fin-primary);
}

.financeiro-painel *,
.financeiro-painel *::before,
.financeiro-painel *::after {
    box-sizing: border-box;
}

.financeiro-painel [hidden] {
    display: none !important;
}

/* Cabeçalho */

.financeiro-painel .financeiro-cabecalho {
    margin-bottom: 26px;
}

.financeiro-painel .financeiro-cabecalho h1 {
    margin: 0 0 6px;
    font-size: 28px;
    font-weight: 650;
    letter-spacing: -.6px;
    color: var(--painel-texto);
}

.financeiro-painel .financeiro-cabecalho p {
    margin: 0;
    color: var(--painel-suave);
    font-size: 14px;
    line-height: 1.5;
}

/* Resumo */

.financeiro-painel .financeiro-resumo {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    background: var(--painel-fundo);
    border: 1px solid var(--painel-borda);
    border-radius: var(--fin-radius-md, 10px);
    padding: 24px 0;
    margin-bottom: 24px;
}

.financeiro-painel .financeiro-indicador {
    min-width: 0;
    padding: 0 24px;
    border-right: 1px solid var(--painel-borda);
}

.financeiro-painel .financeiro-indicador:last-child {
    border-right: 0;
}

.financeiro-painel .financeiro-indicador-label {
    display: block;
    margin-bottom: 10px;
    color: var(--painel-suave);
    font-size: 13px;
    font-weight: 500;
}

.financeiro-painel .financeiro-indicador-valor {
    display: block;
    color: var(--painel-texto);
    font-size: clamp(19px, 1.8vw, 27px);
    font-weight: 650;
    letter-spacing: -.7px;
    font-variant-numeric: tabular-nums;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.financeiro-painel .financeiro-indicador.principal
.financeiro-indicador-valor {
    color: var(--fin-primary-dark);
}

/* Painel principal */

.financeiro-painel .financeiro-listagem {
    background: var(--painel-fundo);
    border: 1px solid var(--painel-borda);
    border-radius: var(--fin-radius-md, 10px);
    overflow: hidden;
}

/* Abas */

.financeiro-painel .financeiro-abas {
    display: flex;
    align-items: center;
    gap: 30px;
    padding: 0 24px;
    border-bottom: 1px solid var(--painel-borda);
    overflow-x: auto;
}

.financeiro-painel .financeiro-aba {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 56px;
    padding: 0 2px;
    color: var(--painel-suave);
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    white-space: nowrap;
    transition: color .15s ease;
}

.financeiro-painel .financeiro-aba:hover {
    color: var(--painel-texto);
}

.financeiro-painel .financeiro-aba.ativa {
    color: var(--painel-primaria);
    font-weight: 600;
}

.financeiro-painel .financeiro-aba.ativa::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--painel-primaria);
}

/* Cabeçalho da listagem */

.financeiro-painel .financeiro-controles {
    padding: 22px 24px;
}

.financeiro-painel .financeiro-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.financeiro-painel .financeiro-toolbar-info {
    min-width: 0;
}

.financeiro-painel .financeiro-toolbar-info h2 {
    margin: 0 0 6px;
    color: var(--painel-texto);
    font-size: 16px;
    font-weight: 600;
}

.financeiro-painel .financeiro-toolbar-total {
    color: var(--painel-suave);
    font-size: 14px;
    font-weight: 500;
    font-variant-numeric: tabular-nums;
}

/* Filtros */

.financeiro-painel .financeiro-seletor {
    flex: 0 0 180px;
}

.financeiro-painel .financeiro-seletor select,
.financeiro-painel .financeiro-campo input,
.financeiro-painel .financeiro-campo select {
    width: 100%;
    min-width: 0;
    height: 40px;
    padding: 0 12px;
    background: var(--ui-input-fundo, var(--painel-fundo));
    color: var(--ui-input-texto, var(--painel-texto));
    border: 1px solid var(--ui-input-borda, var(--painel-borda));
    border-radius: var(--fin-radius-sm, 6px);
    font: inherit;
    font-size: 13px;
}

.financeiro-painel select option {
    background: var(--ui-input-fundo, var(--painel-fundo));
    color: var(--painel-texto);
}

.financeiro-painel .financeiro-campo input::placeholder {
    color: var(--painel-suave);
}

.financeiro-painel .financeiro-seletor select:focus,
.financeiro-painel .financeiro-campo input:focus,
.financeiro-painel .financeiro-campo select:focus {
    outline: 1px solid var(--painel-primaria);
    outline-offset: 1px;
    border-color: var(--painel-primaria);
}

.financeiro-painel .financeiro-filtro-area {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 18px;
}

.financeiro-painel .financeiro-filtro-area
.financeiro-campo {
    flex: 1;
    min-width: 0;
    max-width: 430px;
}

.financeiro-painel .financeiro-limpar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 0 10px;
    color: var(--painel-suave);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    white-space: nowrap;
}

.financeiro-painel .financeiro-limpar:hover {
    color: var(--painel-primaria);
}

.financeiro-painel .financeiro-datas {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    margin-top: 14px;
}

.financeiro-painel .financeiro-datas
.financeiro-campo {
    flex: 1;
    max-width: 210px;
    min-width: 0;
}

.financeiro-painel .financeiro-datas label {
    display: block;
    margin-bottom: 6px;
    color: var(--painel-suave);
    font-size: 12px;
}

.financeiro-painel .financeiro-aplicar {
    min-height: 40px;
    padding: 0 16px;
    font-size: 13px;
}

.financeiro-painel .financeiro-ajuda {
    margin: 7px 0 0;
    color: var(--painel-suave);
    font-size: 12px;
}

/* Tabela */

.financeiro-painel .financeiro-tabela {
    width: 100%;
    min-width: 480px;
    border-collapse: collapse;
    text-align: left;
}

.financeiro-painel .financeiro-tabela thead th {
    padding: 13px 24px;
    background: var(--fin-bg-main);
    border-top: 1px solid var(--painel-borda);
    border-bottom: 1px solid var(--painel-borda);
    color: var(--painel-suave);
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0;
    text-transform: none;
}

.financeiro-painel .financeiro-tabela tbody td {
    padding: 17px 24px;
    border-bottom: 1px solid var(--painel-borda);
    color: var(--painel-texto);
    font-size: 14px;
    vertical-align: middle;
}

.financeiro-painel .financeiro-tabela
tbody tr:last-child td {
    border-bottom: none;
}

.financeiro-painel .financeiro-tabela
tbody tr:hover {
    background: var(--fin-bg-main);
}

.financeiro-painel .financeiro-nome {
    display: block;
    color: var(--painel-texto);
    font-size: 14px;
    font-weight: 550;
    overflow-wrap: anywhere;
}

.financeiro-painel .financeiro-detalhe {
    display: block;
    margin-top: 5px;
    color: var(--painel-suave);
    font-size: 12px;
    font-weight: 400;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.financeiro-painel .financeiro-tabela
.financeiro-coluna-valor {
    text-align: right;
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
}

.financeiro-painel .financeiro-tabela td
.financeiro-valor {
    font-weight: 600;
}

.financeiro-painel .financeiro-vencido {
    color: var(--fin-danger-dark);
}

.financeiro-painel .financeiro-aberto {
    color: var(--fin-warning-dark);
}

.financeiro-painel .financeiro-tabela
.financeiro-vazio {
    padding: 44px 20px;
    text-align: center;
    color: var(--painel-suave);
    font-size: 14px;
}

.financeiro-painel .financeiro-rodape {
    padding: 14px 24px;
    border-top: 1px solid var(--painel-borda);
    color: var(--painel-suave);
    font-size: 12px;
}

.financeiro-painel .financeiro-outros {
    margin: -8px 0 22px;
    color: var(--painel-suave);
    font-size: 12px;
}

/* Responsividade */

@media (max-width: 1100px) {

    .financeiro-painel .financeiro-indicador {
        padding: 0 16px;
    }
}

@media (max-width: 850px) {

    .financeiro-painel .financeiro-resumo {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        padding: 0;
    }

    .financeiro-painel .financeiro-indicador {
        padding: 20px;
    }

    .financeiro-painel
    .financeiro-indicador:nth-child(2) {
        border-right: none;
    }

    .financeiro-painel
    .financeiro-indicador:nth-child(-n+2) {
        border-bottom: 1px solid var(--painel-borda);
    }
}

@media (max-width: 550px) {

    .financeiro-painel .financeiro-cabecalho h1 {
        font-size: 24px;
    }

    .financeiro-painel .financeiro-indicador {
        padding: 16px;
    }

    .financeiro-painel .financeiro-indicador-valor {
        font-size: 19px;
    }

    .financeiro-painel .financeiro-abas {
        padding: 0 16px;
        gap: 20px;
    }

    .financeiro-painel .financeiro-controles {
        padding: 18px 16px;
    }

    .financeiro-painel .financeiro-toolbar {
        flex-wrap: wrap;
        gap: 14px;
    }

    .financeiro-painel .financeiro-seletor {
        flex: 1 1 100%;
    }

    .financeiro-painel .financeiro-datas {
        flex-wrap: wrap;
    }

    .financeiro-painel .financeiro-datas
    .financeiro-campo {
        flex: 1 1 calc(50% - 6px);
        max-width: none;
    }

    .financeiro-painel .financeiro-aplicar {
        width: 100%;
    }

    .financeiro-painel .financeiro-tabela thead th,
    .financeiro-painel .financeiro-tabela tbody td {
        padding: 14px 16px;
    }
}

</style>

<main class="conteudo financeiro-page financeiro-painel">

    <header class="financeiro-cabecalho">

        <h1>Financeiro</h1>

        <p>
            Gerencie as mensalidades e acompanhe os recebimentos.
        </p>

    </header>

    <?php if (!empty($erro)): ?>

        <div class="fin-alert fin-alert-danger" role="alert">
            <?= $h($erro) ?>
        </div>

    <?php endif; ?>

    <!-- Resumo financeiro -->

    <section
        class="financeiro-resumo"
        aria-label="Resumo financeiro">

        <?php foreach ([
            [
                'Total recebido',
                $totalRecebido ?? 0,
                'principal'
            ],
            [
                'Em aberto',
                $totais['aberto'] ?? 0,
                ''
            ],
            [
                'Dinheiro',
                $totais['dinheiro'] ?? 0,
                ''
            ],
            [
                'Digital',
                $totais['digital'] ?? 0,
                ''
            ]
        ] as $indicador): ?>

            <div class="financeiro-indicador <?= $h($indicador[2]) ?>">

                <span class="financeiro-indicador-label">
                    <?= $h($indicador[0]) ?>
                </span>

                <strong class="financeiro-indicador-valor">

                    <?= $h(
                        $exibirValor($indicador[1])
                    ) ?>

                </strong>

            </div>

        <?php endforeach; ?>

    </section>

    <?php if ((float) ($totais['outros'] ?? 0) !== 0.0): ?>

        <p class="financeiro-outros">

            Outros recebimentos:
            <?= $h($moeda($totais['outros'])) ?>

        </p>

    <?php endif; ?>

    <!-- Painel financeiro -->

    <section class="financeiro-listagem">

        <!-- Abas -->

        <nav
            class="financeiro-abas"
            aria-label="Categorias financeiras">

            <?php foreach ($abas as $valor => $rotulo): ?>

                <?php

                $urlAba = $baseUrl . '?' .
                    http_build_query(
                        array_merge(
                            ['aba' => $valor],
                            $parametrosFiltro
                        )
                    );

                ?>

                <a
                    href="<?= $h($urlAba) ?>"

                    class="financeiro-aba <?= $aba === $valor
                        ? 'ativa'
                        : '' ?>"

                    <?= $aba === $valor
                        ? 'aria-current="page"'
                        : '' ?>>

                    <?= $h($rotulo) ?>

                </a>

            <?php endforeach; ?>

        </nav>

        <!-- Filtro único -->

        <form
            method="GET"
            action="<?= $h($baseUrl) ?>"
            id="financeiro-form"
            class="financeiro-controles">

            <input
                type="hidden"
                name="aba"
                value="<?= $h($aba) ?>">

            <div class="financeiro-toolbar">

                <div class="financeiro-toolbar-info">

                    <h2>

                        <?= $h(
                            $titulosAbas[$aba]
                            ?? 'Recebimentos'
                        ) ?>

                    </h2>

                    <span class="financeiro-toolbar-total">

                        <?= $h(
                            $exibirValor(
                                $totais[$aba] ?? 0
                            )
                        ) ?>

                    </span>

                </div>

                <div class="financeiro-seletor">

                    <select
                        id="filtro"
                        name="filtro"
                        aria-label="Filtrar por">

                        <?php foreach ($filtros as $valor => $rotulo): ?>

                            <option
                                value="<?= $h($valor) ?>"

                                <?= $filtro === $valor
                                    ? 'selected'
                                    : '' ?>>

                                <?= $h($rotulo) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>

            <div
                class="financeiro-filtro-area"
                id="financeiro-filtro-area"

                <?= $filtro === 'nenhum'
                    ? 'hidden'
                    : '' ?>>

                <!-- Nome, CPF ou matrícula -->

                <div
                    class="financeiro-campo"
                    id="campo-busca"

                    <?= !$buscaAtiva
                        ? 'hidden'
                        : '' ?>>

                    <input
                        type="text"
                        name="busca"
                        id="busca"
                        maxlength="100"
                        autocomplete="off"
                        aria-label="Identificação do aluno"
                        placeholder="Digite para pesquisar"
                        value="<?= $h($busca) ?>"

                        <?= !$buscaAtiva
                            ? 'disabled'
                            : '' ?>>

                </div>

                <!-- Filial -->

                <div
                    class="financeiro-campo"
                    id="campo-filial"

                    <?= $filtro !== 'filial'
                        ? 'hidden'
                        : '' ?>>

                    <select
                        name="filial_id"
                        id="filial_id"
                        aria-label="Filial"

                        <?= $filtro !== 'filial'
                            ? 'disabled'
                            : '' ?>>

                        <option value="0">
                            Todas as unidades
                        </option>

                        <?php foreach ($filiais ?? [] as $f): ?>

                            <option
                                value="<?= (int) $f['id'] ?>"

                                <?= $filial_id === (int) $f['id']
                                    ? 'selected'
                                    : '' ?>>

                                <?= $h($f['nome']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- Período -->

                <div
                    class="financeiro-campo"
                    id="campo-periodo"

                    <?= $filtro !== 'periodo'
                        ? 'hidden'
                        : '' ?>>

                    <select
                        name="periodo"
                        id="periodo"
                        aria-label="Período"

                        <?= $filtro !== 'periodo'
                            ? 'disabled'
                            : '' ?>>

                        <?php foreach ($periodos as $valor => $rotulo): ?>

                            <option
                                value="<?= $h($valor) ?>"

                                <?= $periodo === $valor
                                    ? 'selected'
                                    : '' ?>>

                                <?= $h($rotulo) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <a
                    href="<?= $h($baseUrl) ?>?aba=<?= $h($aba) ?>"
                    class="financeiro-limpar">

                    Limpar

                </a>

            </div>

            <p
                id="financeiro-ajuda"
                class="financeiro-ajuda"

                <?= !$buscaAtiva
                    ? 'hidden'
                    : '' ?>>

                Pressione Enter para pesquisar.

            </p>

            <!-- Datas personalizadas -->

            <div
                class="financeiro-datas"
                id="financeiro-datas"

                <?= !$personalizado
                    ? 'hidden'
                    : '' ?>>

                <div class="financeiro-campo">

                    <label for="inicio">De</label>

                    <input
                        type="date"
                        name="inicio"
                        id="inicio"
                        value="<?= $h($inicio) ?>"

                        <?= !$personalizado
                            ? 'disabled'
                            : 'required' ?>>

                </div>

                <div class="financeiro-campo">

                    <label for="fim">Até</label>

                    <input
                        type="date"
                        name="fim"
                        id="fim"
                        value="<?= $h($fim) ?>"

                        <?= !$personalizado
                            ? 'disabled'
                            : 'required' ?>>

                </div>

                <button
                    type="submit"
                    class="fin-btn fin-btn-primary financeiro-aplicar">

                    Aplicar

                </button>

            </div>

        </form>

        <!-- Listagem -->

        <div class="fin-table-responsive">

            <table class="financeiro-tabela">

                <thead>

                    <tr>

                        <th scope="col">Aluno</th>

                        <th scope="col">

                            <?= $aba === 'aberto'
                                ? 'Vencimento'
                                : 'Pagamento' ?>

                        </th>

                        <th
                            scope="col"
                            class="financeiro-coluna-valor">

                            Valor

                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (!$contas): ?>

                        <tr>

                            <td
                                colspan="3"
                                class="financeiro-vazio">

                                <?= $consultaValida
                                    ? 'Nenhum registro encontrado.'
                                    : 'Não foi possível realizar a consulta.' ?>

                            </td>

                        </tr>

                    <?php endif; ?>

                    <?php foreach ($contas as $c): ?>

                        <?php

                        $vencida =
                            $c['vencimento'] < $hoje->format('Y-m-d') ||
                            in_array(
                                $c['status'],
                                ['Vencido', 'Atrasado'],
                                true
                            );

                        ?>

                        <tr>

                            <!-- Aluno -->

                            <td>

                                <strong class="financeiro-nome">

                                    <?= $h($c['aluno_nome']) ?>

                                </strong>

                                <span class="financeiro-detalhe">

                                    Matrícula
                                    #<?= (int) $c['matricula_id'] ?>

                                    · <?= $h($c['filial_nome']) ?>

                                </span>

                                <span class="financeiro-detalhe">

                                    <?= $h($c['plano_nome']) ?>

                                </span>

                            </td>

                            <!-- Pagamento ou vencimento -->

                            <td>

                                <?php if ($aba === 'aberto'): ?>

                                    <span>

                                        <?= $h(
                                            $data($c['vencimento'])
                                        ) ?>

                                    </span>

                                    <span
                                        class="financeiro-detalhe <?= $vencida
                                            ? 'financeiro-vencido'
                                            : 'financeiro-aberto' ?>">

                                        <?= $vencida
                                            ? 'Vencido'
                                            : 'Em aberto' ?>

                                    </span>

                                <?php else: ?>

                                    <span>

                                        <?= $h(
                                            $nomePagamento(
                                                $c['forma_pagamento']
                                                ?? ''
                                            )
                                        ) ?>

                                    </span>

                                    <span class="financeiro-detalhe">

                                        <?= $h(
                                            $data($c['pago_em'])
                                        ) ?>

                                    </span>

                                    <?php if (!empty($c['recebido_nome'])): ?>

                                        <span class="financeiro-detalhe">

                                            Recebido por
                                            <?= $h($c['recebido_nome']) ?>

                                        </span>

                                    <?php endif; ?>

                                <?php endif; ?>

                            </td>

                            <!-- Valor -->

                            <td class="financeiro-coluna-valor">

                                <strong class="financeiro-valor">

                                    <?= $h($moeda($c['valor'])) ?>

                                </strong>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <footer class="financeiro-rodape">

            <?php if (count($contas) === 500): ?>

                Exibindo até 500 registros.
                Utilize um filtro para refinar a consulta.

            <?php else: ?>

                <?= count($contas) ?>

                <?= count($contas) === 1
                    ? 'registro'
                    : 'registros' ?>

            <?php endif; ?>

        </footer>

    </section>

</main>

<script>
(function () {

    const form = document.getElementById('financeiro-form');

    const filtro = document.getElementById('filtro');

    const area = document.getElementById(
        'financeiro-filtro-area'
    );

    const campoBusca = document.getElementById('campo-busca');
    const campoFilial = document.getElementById('campo-filial');
    const campoPeriodo = document.getElementById('campo-periodo');

    const busca = document.getElementById('busca');
    const filial = document.getElementById('filial_id');
    const periodo = document.getElementById('periodo');

    const datas = document.getElementById('financeiro-datas');

    const inicio = document.getElementById('inicio');
    const fim = document.getElementById('fim');

    const ajuda = document.getElementById('financeiro-ajuda');

    if (!form || !filtro) {
        return;
    }

    function formatarCpf(valor) {

        const n = valor
            .replace(/\D/g, '')
            .slice(0, 11);

        return n.slice(0, 3) +
            (n.length > 3
                ? '.' + n.slice(3, 6)
                : '') +
            (n.length > 6
                ? '.' + n.slice(6, 9)
                : '') +
            (n.length > 9
                ? '-' + n.slice(9, 11)
                : '');
    }

    function atualizarDatas() {

        const ativo =
            filtro.value === 'periodo' &&
            periodo.value === 'personalizado';

        datas.hidden = !ativo;

        inicio.disabled = !ativo;
        fim.disabled = !ativo;

        inicio.required = ativo;
        fim.required = ativo;

        if (ativo) {

            fim.min = inicio.value || '';

            fim.setCustomValidity(
                inicio.value &&
                fim.value &&
                fim.value < inicio.value
                    ? 'A data final deve ser igual ou posterior à inicial.'
                    : ''
            );

        } else {

            fim.setCustomValidity('');
        }
    }

    function atualizarFiltro(limparValores = false) {

        const tipo = filtro.value;

        const mostrarBusca = [
            'nome',
            'cpf',
            'matricula'
        ].includes(tipo);

        if (limparValores) {

            busca.value = '';
            filial.value = '0';
            periodo.value = 'todos';

            inicio.value = '';
            fim.value = '';
        }

        area.hidden = tipo === 'nenhum';

        campoBusca.hidden = !mostrarBusca;
        campoFilial.hidden = tipo !== 'filial';
        campoPeriodo.hidden = tipo !== 'periodo';

        busca.disabled = !mostrarBusca;
        filial.disabled = tipo !== 'filial';
        periodo.disabled = tipo !== 'periodo';

        ajuda.hidden = !mostrarBusca;

        if (tipo === 'nome') {

            busca.maxLength = 100;
            busca.inputMode = 'text';
            busca.placeholder = 'Digite o nome do aluno';
            busca.setAttribute('aria-label', 'Nome do aluno');

        } else if (tipo === 'cpf') {

            busca.maxLength = 14;
            busca.inputMode = 'numeric';
            busca.placeholder = '000.000.000-00';
            busca.setAttribute('aria-label', 'CPF do aluno');

            busca.value = formatarCpf(busca.value);

        } else if (tipo === 'matricula') {

            busca.maxLength = 10;
            busca.inputMode = 'numeric';
            busca.placeholder = 'Número da matrícula';
            busca.setAttribute('aria-label', 'Número da matrícula');

            busca.value = busca.value
                .replace(/\D/g, '')
                .slice(0, 10);
        }

        atualizarDatas();

        if (limparValores && mostrarBusca) {
            busca.focus();
        }
    }

    // Exibe apenas o filtro escolhido.

    filtro.addEventListener('change', function () {

        atualizarFiltro(true);

        if (filtro.value === 'nenhum') {

            form.requestSubmit();
        }

    });

    // Máscaras.

    busca.addEventListener('input', function () {

        if (filtro.value === 'cpf') {

            busca.value = formatarCpf(busca.value);

        } else if (filtro.value === 'matricula') {

            busca.value = busca.value
                .replace(/\D/g, '')
                .slice(0, 10);
        }

    });

    // Pesquisa por texto ao pressionar Enter.

    busca.addEventListener('keydown', function (event) {

        if (event.key === 'Enter' && !event.isComposing) {

            event.preventDefault();

            form.requestSubmit();
        }

    });

    // Filial: consulta automática.

    filial.addEventListener('change', function () {

        if (filtro.value === 'filial') {

            form.requestSubmit();
        }

    });

    // Período: consulta automática, exceto personalizado.

    periodo.addEventListener('change', function () {

        atualizarDatas();

        if (
            filtro.value === 'periodo' &&
            periodo.value !== 'personalizado'
        ) {

            form.requestSubmit();

        } else {

            inicio.focus();
        }

    });

    inicio.addEventListener('change', atualizarDatas);
    fim.addEventListener('change', atualizarDatas);

    form.addEventListener('submit', function (event) {

        atualizarDatas();

        if (!form.checkValidity()) {

            event.preventDefault();

            form.reportValidity();
        }

    });

    atualizarFiltro();

})();
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>