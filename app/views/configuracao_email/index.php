<?php

$mensagemSalva = ($_GET['msg'] ?? '') === 'salvo';
$configuracao = $configuracao ?? [];
$configuracaoPersonalizada = $configuracaoPersonalizada ?? $configuracao;
$configuracaoLocal = $configuracaoLocal ?? [];

$modoCores = $configuracao['modo_cores_email'] ?? 'personalizado';

if (!in_array($modoCores, ['marca', 'personalizado'], true)) {
    $modoCores = 'personalizado';
}

$usarPersonalizadoAtual = !empty($configuracao['usar_personalizado']);

$coresPadrao = [
    'cor_email_fundo' => '#0b0b0b',
    'cor_email_cartao' => '#151515',
    'cor_email_destaque' => '#ffb000',
    'cor_email_botao' => '#ffb000',
    'cor_email_texto_botao' => '#151515',
];

$coresEmail = [];

foreach ($coresPadrao as $campo => $padrao) {
    $valor = $configuracao[$campo] ?? $padrao;

    $coresEmail[$campo] = (
        is_string($valor) &&
        preg_match('/^#[0-9a-fA-F]{6}$/', $valor)
    ) ? strtolower($valor) : $padrao;
}

$escapar = static function ($valor): string {
    if (!is_string($valor) && !is_numeric($valor)) {
        $valor = '';
    }

    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

$valorSMTP = static function (
    string $campo,
    $padrao = ''
) use (
    $configuracao,
    $configuracaoPersonalizada,
    $configuracaoLocal
) {
    $local = $configuracaoLocal[$campo] ?? $padrao;
    $personalizado = $configuracaoPersonalizada[$campo] ?? $padrao;

    return [
        'padrao' => $local,
        'personalizado' => $personalizado,
        'atual' => $configuracao[$campo] ?? $local,
    ];
};

$cssEspecifico = BASE_URL . '/assets/css/configuracao-email.css';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

?>

<main class="conteudo config-email-page">

    <div class="config-email-topo">

        <div class="config-email-icone" aria-hidden="true">
            ✉
        </div>

        <div>
            <h1>Configurações de e-mail</h1>
            <p>Configure o envio e a aparência dos e-mails do GymFlow.</p>
        </div>

    </div>

    <?php if ($mensagemSalva): ?>

        <div class="config-email-sucesso" role="status">
            Configurações salvas com sucesso!
        </div>

    <?php endif; ?>

    <form
        method="POST"
        action="<?= $escapar(BASE_URL) ?>/app/controllers/ConfiguracaoEmailController.php?acao=salvar">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= $escapar($_SESSION['csrf_config_email'] ?? '') ?>">

        <!-- SERVIDOR SMTP -->

        <section class="config-email-card">

            <h2>Servidor de e-mail</h2>

            <p class="config-email-descricao">
                Configure o remetente e os dados do servidor SMTP da sua empresa.
            </p>

            <div class="config-email-ativacao">

                <input
                    type="checkbox"
                    id="usar_personalizado"
                    name="usar_personalizado"
                    value="1"
                    <?= $usarPersonalizadoAtual ? 'checked' : '' ?>>

                <label for="usar_personalizado">
                    Usar servidor de e-mail personalizado
                </label>

            </div>

            <div class="config-email-grid">

                <?php

                $camposTexto = [
                    [
                        'id' => 'email_remetente',
                        'label' => 'E-mail remetente',
                        'placeholder' => 'exemplo@empresa.com',
                        'type' => 'email',
                        'maxlength' => 255,
                        'padrao' => $valorSMTP('email_remetente', ''),
                    ],
                    [
                        'id' => 'nome_remetente',
                        'label' => 'Nome do remetente',
                        'placeholder' => 'Ex.: GymFlow',
                        'type' => 'text',
                        'maxlength' => 100,
                        'padrao' => $valorSMTP('nome_remetente', 'GymFlow'),
                    ],
                    [
                        'id' => 'smtp_host',
                        'label' => 'Servidor SMTP',
                        'placeholder' => 'smtp.gmail.com',
                        'type' => 'text',
                        'maxlength' => 255,
                        'padrao' => $valorSMTP('smtp_host', ''),
                    ],
                    [
                        'id' => 'smtp_usuario',
                        'label' => 'Usuário SMTP',
                        'placeholder' => 'Usuário de autenticação',
                        'type' => 'text',
                        'maxlength' => 255,
                        'padrao' => $valorSMTP('smtp_usuario', ''),
                    ],
                ];

                ?>

                <?php foreach ($camposTexto as $campo): ?>

                    <div class="config-email-campo">

                        <label for="<?= $escapar($campo['id']) ?>">
                            <?= $escapar($campo['label']) ?>
                        </label>

                        <input
                            type="<?= $escapar($campo['type']) ?>"
                            id="<?= $escapar($campo['id']) ?>"
                            name="<?= $escapar($campo['id']) ?>"
                            maxlength="<?= (int) $campo['maxlength'] ?>"
                            value="<?= $escapar($campo['padrao']['atual']) ?>"
                            placeholder="<?= $escapar($campo['placeholder']) ?>"
                            data-smtp-field
                            data-smtp-padrao="<?= $escapar($campo['padrao']['padrao']) ?>"
                            data-smtp-personalizado="<?= $escapar($campo['padrao']['personalizado']) ?>"
                            <?= $usarPersonalizadoAtual ? 'required' : 'disabled' ?>>

                    </div>

                <?php endforeach; ?>

                <!-- PORTA SMTP -->

                <?php $portaSMTP = $valorSMTP('smtp_porta', 587); ?>

                <div class="config-email-campo">

                    <label for="smtp_porta">
                        Porta SMTP
                    </label>

                    <input
                        type="number"
                        id="smtp_porta"
                        name="smtp_porta"
                        min="1"
                        max="65535"
                        value="<?= $escapar($portaSMTP['atual']) ?>"
                        data-smtp-field
                        data-smtp-padrao="<?= $escapar($portaSMTP['padrao']) ?>"
                        data-smtp-personalizado="<?= $escapar($portaSMTP['personalizado']) ?>"
                        <?= $usarPersonalizadoAtual ? 'required' : 'disabled' ?>>

                </div>

                <!-- SEGURANÇA SMTP -->

                <?php $segurancaSMTP = $valorSMTP('smtp_seguranca', 'tls'); ?>

                <div class="config-email-campo">

                    <label for="smtp_seguranca">
                        Segurança da conexão
                    </label>

                    <select
                        id="smtp_seguranca"
                        name="smtp_seguranca"
                        data-smtp-field
                        data-smtp-padrao="<?= $escapar($segurancaSMTP['padrao']) ?>"
                        data-smtp-personalizado="<?= $escapar($segurancaSMTP['personalizado']) ?>"
                        <?= $usarPersonalizadoAtual ? 'required' : 'disabled' ?>>

                        <?php foreach ([
                            'tls' => 'TLS / STARTTLS',
                            'ssl' => 'SSL',
                            'none' => 'Sem criptografia',
                        ] as $valor => $rotulo): ?>

                            <option
                                value="<?= $escapar($valor) ?>"
                                <?= $segurancaSMTP['atual'] === $valor ? 'selected' : '' ?>>

                                <?= $escapar($rotulo) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- SENHA SMTP -->

                <div class="config-email-campo">

                    <label for="smtp_senha">
                        Senha SMTP
                    </label>

                    <input
                        type="password"
                        id="smtp_senha"
                        name="smtp_senha"
                        maxlength="1024"
                        autocomplete="new-password"
                        placeholder="Deixe em branco para manter a senha salva"
                        data-smtp-field
                        <?= $usarPersonalizadoAtual ? '' : 'disabled' ?>>

                    <small class="config-email-ajuda">
                        Por segurança, a senha salva não será exibida aqui.
                        Preencha somente para cadastrar ou trocar a senha.
                    </small>

                </div>

            </div>

            <p class="config-email-nota">
                Ao desativar o SMTP personalizado, os campos mostram os dados
                padrão do GymFlow. Suas configurações personalizadas salvas
                permanecem no banco de dados.
            </p>

        </section>

        <!-- APARÊNCIA DOS E-MAILS -->

        <section class="config-email-card">

            <h2>Aparência dos e-mails</h2>

            <p class="config-email-descricao">
                Escolha se os e-mails de recuperação de senha vão acompanhar
                as cores da sua marca ou terão uma identidade visual própria.
            </p>

            <div class="config-email-grid">

                <div class="config-email-campo">

                    <label for="modo_cores_email">
                        Modo de cores
                    </label>

                    <select
                        id="modo_cores_email"
                        name="modo_cores_email">

                        <option
                            value="marca"
                            <?= $modoCores === 'marca' ? 'selected' : '' ?>>

                            Usar cores da minha marca

                        </option>

                        <option
                            value="personalizado"
                            <?= $modoCores === 'personalizado' ? 'selected' : '' ?>>

                            Personalizar cores do e-mail

                        </option>

                    </select>

                </div>

            </div>

            <div
                id="cores_email_personalizadas"
                <?= $modoCores === 'personalizado' ? '' : 'hidden' ?>>

                <hr class="config-email-separador">

                <div class="config-email-grid">

                    <?php

                    $camposCores = [
                        'cor_email_fundo' => 'Fundo do e-mail',
                        'cor_email_cartao' => 'Fundo do cartão',
                        'cor_email_destaque' => 'Cor de destaque',
                        'cor_email_botao' => 'Fundo do botão',
                        'cor_email_texto_botao' => 'Texto do botão',
                    ];

                    ?>

                    <?php foreach ($camposCores as $campo => $rotulo): ?>

                        <div class="config-email-campo">

                            <label for="<?= $escapar($campo) ?>">
                                <?= $escapar($rotulo) ?>
                            </label>

                            <input
                                type="color"
                                id="<?= $escapar($campo) ?>"
                                name="<?= $escapar($campo) ?>"
                                value="<?= $escapar($coresEmail[$campo]) ?>">

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

            <p class="config-email-nota" id="nota_modo_cores">

                <?php if ($modoCores === 'marca'): ?>

                    O modo marca está selecionado.
                    A aplicação das cores da marca será conectada
                    ao envio do e-mail na próxima etapa.

                <?php else: ?>

                    Você pode escolher cores exclusivas para os e-mails.
                    Elas não devem alterar o portfólio público.

                <?php endif; ?>

            </p>

        </section>

        <!-- AÇÕES -->

        <div class="config-email-acoes">

            <button
                type="submit"
                class="config-email-salvar">

                Salvar configurações

            </button>

        </div>

    </form>

</main>

<script src="<?= BASE_URL ?>/assets/js/configuracao-email.js" defer></script>

<?php include __DIR__ . '/../shared/footer.php'; ?>