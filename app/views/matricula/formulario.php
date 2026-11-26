<?php
// app/views/matricula/formulario.php

if (!isset($model, $acao, $rota, $interno)) {
    http_response_code(403);
    exit;
}

$dados = $dados ?? [];
$erros = $erros ?? [];

$metodos = $metodos ?? $model->metodos();

$existente = $existente ?? null;

$titulos = [
    'identificacao' => 'Dados pessoais',
    'filial' => 'Unidade e plano',
    'pagamento' => 'Pagamento',
    'sucesso' => 'Matrícula registrada'
];

$subtitulos = [
    'identificacao' => $interno
        ? 'Identifique o aluno para realizar a matrícula.'
        : 'Preencha seus dados para iniciar a matrícula.',

    'filial' =>
        'Selecione a unidade e o plano contratado.',

    'pagamento' =>
        'Confira os dados e o valor antes de finalizar.',

    'sucesso' =>
        'A matrícula foi registrada e aguarda a confirmação do pagamento.'
];

$tituloPagina = $interno
    ? 'Matricular aluno'
    : $titulos[$acao];

$cssEspecifico = BASE_URL . '/assets/css/' .
    ($interno ? 'jorge-financeiro.css' : 'matricula.css');

$h = static fn($v) => htmlspecialchars(
    (string) $v,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$classes = $interno
    ? [
        'grupo' => 'fin-form-group',
        'label' => 'fin-form-label',
        'campo' => 'fin-form-input',
        'select' => 'fin-form-select',
        'linha' => 'fin-form-grid',
        'acoes' => 'fin-form-actions',
        'principal' => 'fin-btn fin-btn-primary',
        'secundario' => 'fin-btn fin-btn-secondary'
    ]
    : [
        'grupo' => 'form-group',
        'label' => 'form-label',
        'campo' => 'form-control',
        'select' => 'form-control',
        'linha' => 'form-row',
        'acoes' => 'matricula-acoes',
        'principal' => 'btn-matricula-principal',
        'secundario' => 'btn-matricula-secundario'
    ];

$urlCancelar = $interno
    ? $urlAlunos
    : BASE_URL . '/index.php?id=' . $company_id;

$mensagemCampo = static function (
    string $nome
) use ($erros, $h): void {

    if (isset($erros[$nome])) {

        echo '<small class="matricula-campo-erro" id="erro-' .
            $h($nome) . '">' .
            $h($erros[$nome]) .
            '</small>';
    }
};

$campo = static function (
    string $nome,
    string $label,
    string $tipo = 'text',
    array $atributos = []
) use ($dados, $erros, $h, $classes, $mensagemCampo): void {

    $valor = $tipo === 'password'
        ? ''
        : ($dados[$nome] ?? '');

    ?>

    <div class="<?= $h($classes['grupo']) ?>">

        <label
            class="<?= $h($classes['label']) ?>"
            for="<?= $h($nome) ?>">

            <?= $h($label) ?>

        </label>

        <input
            class="<?= $h($classes['campo']) ?>"
            id="<?= $h($nome) ?>"
            name="<?= $h($nome) ?>"
            type="<?= $h($tipo) ?>"
            value="<?= $h($valor) ?>"

            <?= isset($erros[$nome])
                ? 'aria-invalid="true" aria-describedby="erro-' .
                    $h($nome) . '"'
                : '' ?>

            <?php foreach ($atributos as $atributo => $v): ?>

                <?= $h($atributo) ?><?= $v === true
                    ? ''
                    : '="' . $h($v) . '"' ?>

            <?php endforeach; ?>
        >

        <?php $mensagemCampo($nome); ?>

    </div>

    <?php
};

$selecao = static function (
    string $nome,
    string $label,
    array $opcoes,
    string $placeholder = 'Selecione'
) use ($classes, $h, $dados, $erros, $mensagemCampo): void {

    ?>

    <div class="<?= $h($classes['grupo']) ?>">

        <label
            class="<?= $h($classes['label']) ?>"
            for="<?= $h($nome) ?>">

            <?= $h($label) ?>

        </label>

        <select
            class="<?= $h($classes['select']) ?>"
            id="<?= $h($nome) ?>"
            name="<?= $h($nome) ?>"
            required

            <?= isset($erros[$nome])
                ? 'aria-invalid="true" aria-describedby="erro-' .
                    $h($nome) . '"'
                : '' ?>>

            <option value="">
                <?= $h($placeholder) ?>
            </option>

            <?php foreach ($opcoes as $valor => $rotulo): ?>

                <option
                    value="<?= $h($valor) ?>"

                    <?= (string) ($dados[$nome] ?? '') ===
                        (string) $valor
                        ? 'selected'
                        : '' ?>>

                    <?= $h($rotulo) ?>

                </option>

            <?php endforeach; ?>

        </select>

        <?php $mensagemCampo($nome); ?>

    </div>

    <?php
};

if ($interno) {

    include __DIR__ . '/../shared/header.php';
    include __DIR__ . '/../shared/sidebar.php';

} else {

    include __DIR__ . '/../shared/portfolio_header.php';
}

?>

<style>

.matricula-unificada .matricula-campo-erro {
    color: var(--fin-danger-dark, #ef4444);
    display: block;
    margin-top: 5px;
    font-size: 13px;
}

.matricula-unificada [aria-invalid="true"] {
    border-color: var(--fin-danger, #ef4444);
}

.matricula-unificada .matricula-header-secao {
    text-align: left;
}

.matricula-unificada button:disabled {
    opacity: .55;
    cursor: not-allowed;
    transform: none;
}

.matricula-unificada .matricula-resumo {
    margin: 0 0 24px;
    padding: 0;
    list-style: none;
}

.matricula-unificada .matricula-resumo li {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    padding: 13px 0;
    border-bottom: 1px solid var(
        --ui-borda,
        rgba(128, 128, 128, .2)
    );
}

.matricula-unificada .matricula-resumo strong {
    text-align: right;
    overflow-wrap: anywhere;
}

.matricula-unificada .matricula-nota {
    margin: 18px 0;
    line-height: 1.6;
    font-size: 14px;
}

.matricula-unificada .matricula-opcoes-pagamento {
    display: grid;
    gap: 10px;
    margin: 12px 0 20px;
}

.matricula-unificada .matricula-opcao-pagamento {
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1px solid var(
        --ui-borda,
        rgba(128, 128, 128, .3)
    );
    border-radius: 8px;
    padding: 14px;
    cursor: pointer;
}

.matricula-unificada .matricula-opcao-pagamento input {
    accent-color: var(--cor-primaria, var(--mat-primary));
}

.matricula-unificada .matricula-opcao-pagamento:has(input:checked) {
    border-color: var(--cor-primaria, var(--mat-primary));
}

.matricula-interna .fin-form-card {
    max-width: none;
    margin: 0;
}

.matricula-interna .fin-form-group {
    margin-bottom: 18px;
}

.matricula-interna .fin-form-grid {
    margin-bottom: 0;
}

.matricula-interna .matricula-opcao-pagamento {
    background: var(--ui-input-fundo);
}

.matricula-interna .matricula-container {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
}

@media (max-width: 600px) {

    .matricula-interna .fin-form-grid {
        grid-template-columns: 1fr;
    }

    .matricula-unificada .matricula-resumo li {
        flex-wrap: wrap;
    }
}

</style>

<main class="<?= $interno
    ? 'conteudo financeiro-page matricula-interna matricula-unificada'
    : 'matricula-wrapper matricula-unificada' ?>">

    <div
        class="matricula-container"
        style="<?= $interno ? '' : 'max-width:820px' ?>">

        <header class="<?= $interno
            ? 'fin-page-header'
            : 'matricula-header-secao' ?>">

            <div class="<?= $interno ? 'fin-header-info' : '' ?>">

                <?php if ($interno): ?>

                    <h1><?= $h($titulos[$acao]) ?></h1>

                <?php else: ?>

                    <h2><?= $h($titulos[$acao]) ?></h2>

                <?php endif; ?>

                <p><?= $h($subtitulos[$acao]) ?></p>

            </div>

        </header>

        <?php if ($erro || $erros): ?>

            <div
                class="<?= $interno
                    ? 'fin-alert fin-alert-danger'
                    : 'matricula-alerta-erro' ?>"
                role="alert">

                <?= $h(
                    $erro ?:
                    'Revise os campos indicados antes de continuar.'
                ) ?>

            </div>

        <?php endif; ?>

        <section class="<?= $interno
            ? 'fin-form-card'
            : 'matricula-card' ?>">

            <?php if ($acao === 'identificacao'): ?>

                <?php if (
                    $interno &&
                    empty($dados['cpf_consultado'])
                ): ?>

                    <form
                        method="POST"
                        action="<?= $h($rota('identificacao')) ?>">

                        <input
                            type="hidden"
                            name="csrf"
                            value="<?= $h($csrf) ?>">

                        <input
                            type="hidden"
                            name="operacao"
                            value="buscar_cpf">

                        <div class="<?= $h($classes['grupo']) ?>">

                            <label for="cpf_busca">
                                CPF do aluno *
                            </label>

                            <input
                                type="text"
                                class="<?= $h($classes['campo']) ?>"
                                id="cpf_busca"
                                name="cpf_busca"
                                maxlength="14"
                                inputmode="numeric"
                                autocomplete="off"
                                placeholder="000.000.000-00"
                                required>

                        </div>

                        <p class="matricula-nota">
                            A pesquisa verifica se o aluno já
                            possui cadastro na unidade autorizada.
                        </p>

                        <div class="<?= $h($classes['acoes']) ?>">

                            <a
                                class="<?= $h($classes['secundario']) ?>"
                                href="<?= $h($urlCancelar) ?>">

                                Cancelar

                            </a>

                            <button
                                type="submit"
                                class="<?= $h($classes['principal']) ?>">

                                Buscar CPF

                            </button>

                        </div>

                    </form>

                <?php elseif ($interno && $existente): ?>

                    <h2>Cadastro localizado</h2>

                    <ul class="matricula-resumo">

                        <?php foreach ([
                            'Aluno' => $existente['nome'],
                            'CPF' => $existente['cpf'],
                            'Unidade' => $existente['nome_filial'],
                            'Status do cadastro' => $existente['status']
                        ] as $rotulo => $valor): ?>

                            <li>
                                <span><?= $h($rotulo) ?></span>
                                <strong><?= $h($valor) ?></strong>
                            </li>

                        <?php endforeach; ?>

                    </ul>

                    <p class="matricula-nota">
                        Os dados cadastrais e a senha do aluno
                        serão mantidos.
                    </p>

                    <form
                        method="POST"
                        action="<?= $h($rota('identificacao')) ?>">

                        <input
                            type="hidden"
                            name="csrf"
                            value="<?= $h($csrf) ?>">

                        <input
                            type="hidden"
                            name="operacao"
                            value="usar_existente">

                        <div class="<?= $h($classes['acoes']) ?>">

                            <a
                                class="<?= $h($classes['secundario']) ?>"
                                href="<?= $h($rota(
                                    'identificacao',
                                    ['novo' => 1]
                                )) ?>">

                                Buscar outro CPF

                            </a>

                            <button
                                type="submit"
                                class="<?= $h($classes['principal']) ?>">

                                Continuar

                            </button>

                        </div>

                    </form>

                <?php else: ?>

                    <?php if ($interno): ?>

                        <p class="matricula-nota">
                            Nenhum cadastro disponível foi localizado.
                            Preencha os dados do novo aluno.
                        </p>

                    <?php endif; ?>

                    <form
                        method="POST"
                        action="<?= $h($rota('identificacao')) ?>">

                        <input
                            type="hidden"
                            name="csrf"
                            value="<?= $h($csrf) ?>">

                        <input
                            type="hidden"
                            name="operacao"
                            value="identificar">

                        <?php

                        $campo(
                            'nome',
                            'Nome completo *',
                            'text',
                            [
                                'required' => true,
                                'maxlength' => 100,
                                'autocomplete' => 'name'
                            ]
                        );

                        ?>

                        <div class="<?= $h($classes['linha']) ?>">

                            <?php

                            $atributosCpf = [
                                'required' => true,
                                'maxlength' => 14,
                                'inputmode' => 'numeric',
                                'placeholder' => '000.000.000-00'
                            ];

                            if ($interno) {
                                $atributosCpf['readonly'] = true;
                            }

                            $campo(
                                'cpf',
                                'CPF *',
                                'text',
                                $atributosCpf
                            );

                            $campo(
                                'rg',
                                'RG (opcional)',
                                'text',
                                ['maxlength' => 20]
                            );

                            ?>

                        </div>

                        <div class="<?= $h($classes['linha']) ?>">

                            <?php

                            $campo(
                                'nascimento',
                                'Data de nascimento *',
                                'date',
                                [
                                    'required' => true,
                                    'min' => '1000-01-01',
                                    'max' => date('Y-m-d')
                                ]
                            );

                            $selecao(
                                'sexo',
                                'Sexo *',
                                [
                                    'Masculino' => 'Masculino',
                                    'Feminino' => 'Feminino',
                                    'Outro' => 'Outro'
                                ]
                            );

                            ?>

                        </div>

                        <div class="<?= $h($classes['linha']) ?>">

                            <?php

                            $campo(
                                'email',
                                'E-mail *',
                                'email',
                                [
                                    'required' => true,
                                    'maxlength' => 100,
                                    'autocomplete' => 'email'
                                ]
                            );

                            $campo(
                                'telefone',
                                'Telefone com DDD *',
                                'tel',
                                [
                                    'required' => true,
                                    'maxlength' => 15,
                                    'autocomplete' => 'tel-national',
                                    'placeholder' => '(11) 99999-9999'
                                ]
                            );

                            ?>

                        </div>

                        <?php

                        $campo(
                            'endereco',
                            'Endereço (opcional)',
                            'text',
                            [
                                'maxlength' => 1000,
                                'autocomplete' => 'street-address'
                            ]
                        );

                        $attrsSenha = [
                            'minlength' => 8,
                            'maxlength' => 72,
                            'autocomplete' => 'new-password',
                            'placeholder' => 'Mínimo de 8 caracteres'
                        ];

                        if (empty($dados['senha_hash'])) {

                            $attrsSenha['required'] = true;

                        } else {

                            $attrsSenha['placeholder'] =
                                'Deixe em branco para manter a senha';
                        }

                        $campo(
                            'senha',
                            'Senha de acesso' .
                            (
                                empty($dados['senha_hash'])
                                    ? ' *'
                                    : ''
                            ),
                            'password',
                            $attrsSenha
                        );

                        ?>

                        <?php if ($interno): ?>

                            <p class="matricula-nota">
                                Solicite que o próprio aluno digite
                                a senha de acesso ao portal.
                            </p>

                        <?php endif; ?>

                        <p class="form-help">
                            Os campos marcados com * são obrigatórios.
                        </p>

                        <div class="<?= $h($classes['acoes']) ?>">

                            <a
                                class="<?= $h($classes['secundario']) ?>"
                                href="<?= $h(
                                    $interno
                                        ? $rota(
                                            'identificacao',
                                            ['novo' => 1]
                                        )
                                        : $urlCancelar
                                ) ?>">

                                <?= $interno
                                    ? 'Buscar outro CPF'
                                    : 'Cancelar' ?>

                            </a>

                            <button
                                type="submit"
                                class="<?= $h($classes['principal']) ?>">

                                Continuar

                            </button>

                        </div>

                    </form>

                <?php endif; ?>

            <?php elseif ($acao === 'filial'): ?>

                <form
                    method="POST"
                    action="<?= $h($rota('filial')) ?>">

                    <input
                        type="hidden"
                        name="csrf"
                        value="<?= $h($csrf) ?>">

                    <?php

                    $opcoesFiliais = [];

                    foreach ($filiais as $f) {
                        $opcoesFiliais[$f['id']] = $f['nome'];
                    }

                    $opcoesPlanos = [];

                    foreach ($planos as $p) {

                        $opcoesPlanos[$p['id']] =
                            $p['nome'] .
                            ' — R$ ' .
                            number_format(
                                (float) $p['valor'],
                                2,
                                ',',
                                '.'
                            ) .
                            ' (' . $p['duracao'] . ')';
                    }

                    $selecao(
                        'filial_id',
                        'Unidade *',
                        $opcoesFiliais,
                        'Selecione a unidade'
                    );

                    $selecao(
                        'plano_id',
                        'Plano *',
                        $opcoesPlanos,
                        'Selecione o plano'
                    );

                    ?>

                    <?php if (!$planos || !$filiais): ?>

                        <p class="matricula-nota">
                            Não há planos ou unidades disponíveis.
                        </p>

                    <?php endif; ?>

                    <div class="<?= $h($classes['acoes']) ?>">

                        <a
                            class="<?= $h($classes['secundario']) ?>"
                            href="<?= $h($rota('identificacao')) ?>">

                            Voltar

                        </a>

                        <button
                            type="submit"
                            class="<?= $h($classes['principal']) ?>"

                            <?= !$planos || !$filiais
                                ? 'disabled'
                                : '' ?>>

                            Continuar

                        </button>

                    </div>

                </form>

            <?php elseif ($acao === 'pagamento'): ?>

                <?php

                $resumo = [
                    'Aluno' => $dados['nome'],
                    'Unidade' => $filial['nome'],
                    'Plano' => $plano['nome'],
                    'Duração' => $plano['duracao'],

                    'Valor total do contrato' =>
                        MatriculaModel::moeda(
                            $cobrancas['total']
                        )
                ];

                if ($cobrancas['meses'] > 1) {
                    $resumo['Cobranças mensais'] =
                        $cobrancas['meses'];
                }

                $resumo['Valor inicial a pagar'] =
                    MatriculaModel::moeda(
                        $cobrancas['parcelas'][0]
                    );

                ?>

                <ul class="matricula-resumo">

                    <?php foreach ($resumo as $rotulo => $valor): ?>

                        <li>

                            <span><?= $h($rotulo) ?></span>

                            <strong><?= $h($valor) ?></strong>

                        </li>

                    <?php endforeach; ?>

                </ul>

                <form
                    method="POST"
                    action="<?= $h($rota('pagamento')) ?>">

                    <input
                        type="hidden"
                        name="csrf"
                        value="<?= $h($csrf) ?>">

                    <label class="<?= $h($classes['label']) ?>">
                        Forma de pagamento
                    </label>

                    <div class="matricula-opcoes-pagamento">

                        <?php foreach ($metodos as $valor => $rotulo): ?>

                            <label class="matricula-opcao-pagamento">

                                <input
                                    type="radio"
                                    name="metodo"
                                    value="<?= $h($valor) ?>"
                                    required

                                    <?= ($dados['metodo'] ?? 'pix') ===
                                        $valor
                                        ? 'checked'
                                        : '' ?>>

                                <span><?= $h($rotulo) ?></span>

                            </label>

                        <?php endforeach; ?>

                    </div>

                    <p class="matricula-nota">

                        <?php if ($interno): ?>

                            O pagamento deverá ser realizado
                            na conta bancária ou na maquininha
                            da academia e confirmado no módulo
                            Recebimentos.

                        <?php else: ?>

                            Após registrar a matrícula, entre
                            em contato com a recepção para
                            receber as instruções de Pix.

                        <?php endif; ?>

                        A matrícula permanecerá pendente
                        até a confirmação do recebimento.

                    </p>

                    <div class="<?= $h($classes['acoes']) ?>">

                        <a
                            class="<?= $h($classes['secundario']) ?>"
                            href="<?= $h($rota('filial')) ?>">

                            Voltar

                        </a>

                        <button
                            type="submit"
                            class="<?= $h($classes['principal']) ?>">

                            Finalizar matrícula

                        </button>

                    </div>

                </form>

            <?php else: ?>

                <?php

                $resumo = [
                    'Matrícula' =>
                        '#' . $resultado['matricula_id'],

                    'Aluno' =>
                        $resultado['aluno_nome'],

                    'Plano' =>
                        $resultado['plano_nome'],

                    'Unidade' =>
                        $resultado['filial_nome'],

                    'Valor inicial' =>
                        MatriculaModel::moeda(
                            $resultado['primeira_parcela']
                        ),

                    'Forma de pagamento' =>
                        $resultado['metodo'] ?? 'Pix',

                    'Situação' =>
                        'Aguardando pagamento'
                ];

                ?>

                <ul class="matricula-resumo">

                    <?php foreach ($resumo as $rotulo => $valor): ?>

                        <li>

                            <span><?= $h($rotulo) ?></span>

                            <strong><?= $h($valor) ?></strong>

                        </li>

                    <?php endforeach; ?>

                </ul>

                <?php if ($interno): ?>

                    <p class="matricula-nota">
                        Utilize o número da matrícula para
                        localizar a cobrança no módulo
                        Recebimentos.
                    </p>

                <?php else: ?>

                    <p class="matricula-nota">

                        Entre em contato com a recepção da
                        unidade e informe o número da matrícula.

                    </p>

                    <?php if (!empty($resultado['filial_telefone'])): ?>

                        <p class="matricula-nota">

                            Telefone da unidade:

                            <?= $h($resultado['filial_telefone']) ?>

                        </p>

                    <?php endif; ?>

                <?php endif; ?>

                <div class="<?= $h($classes['acoes']) ?>">

                    <?php if ($interno): ?>

                        <a
                            class="<?= $h($classes['secundario']) ?>"
                            href="<?= $h($urlAlunos) ?>">

                            Voltar aos alunos

                        </a>

                        <?php if ($roleMatricula === 'Recepcao'): ?>

                            <a
                                class="<?= $h($classes['principal']) ?>"
                                href="<?= BASE_URL ?>/app/controllers/RecebimentoController.php">

                                Ir para recebimentos

                            </a>

                        <?php else: ?>

                            <a
                                class="<?= $h($classes['principal']) ?>"
                                href="<?= BASE_URL ?>/app/controllers/AlunoController.php?acao=visualizar&amp;id=<?= (int) $resultado['aluno_id'] ?>">

                                Visualizar aluno

                            </a>

                        <?php endif; ?>

                    <?php else: ?>

                        <a
                            class="<?= $h($classes['secundario']) ?>"
                            href="<?= $h($urlCancelar) ?>">

                            Voltar ao site

                        </a>

                        <a
                            class="<?= $h($classes['principal']) ?>"
                            href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=login&amp;empresa=<?= (int) $company_id ?>">

                            Portal do Aluno

                        </a>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

        </section>

    </div>

</main>

<script>
(function () {

    const camposCpf = [
        document.getElementById('cpf'),
        document.getElementById('cpf_busca')
    ];

    camposCpf.forEach(function (campo) {

        if (!campo || campo.readOnly) {
            return;
        }

        campo.addEventListener('input', function () {

            const n = campo.value
                .replace(/\D/g, '')
                .slice(0, 11);

            campo.value =
                n.slice(0, 3) +
                (n.length > 3
                    ? '.' + n.slice(3, 6)
                    : '') +
                (n.length > 6
                    ? '.' + n.slice(6, 9)
                    : '') +
                (n.length > 9
                    ? '-' + n.slice(9, 11)
                    : '');
        });

    });

    const telefone = document.getElementById('telefone');

    if (telefone) {

        telefone.addEventListener('input', function () {

            const n = telefone.value
                .replace(/\D/g, '')
                .slice(0, 11);

            if (n.length <= 2) {
                telefone.value = n;
                return;
            }

            const corpo = n.slice(2);

            telefone.value =
                '(' + n.slice(0, 2) + ') ' +
                (
                    corpo.length > 4
                        ? corpo.slice(0, -4) +
                            '-' +
                            corpo.slice(-4)
                        : corpo
                );
        });

    }

})();
</script>

<?php

if ($interno) {

    include __DIR__ . '/../shared/footer.php';

} else {

    include __DIR__ . '/../shared/portfolio_footer.php';
}
?>