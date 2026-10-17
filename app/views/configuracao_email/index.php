
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

/**
 * Escapa valores para uso seguro no HTML.
 */
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

/**
 * Obtém os valores de cada modo.
 * O controller fornece os valores locais e os personalizados.
 */
$valorSMTP = static function (
    string $campo,
    $padrao = ''
) use ($configuracao, $configuracaoPersonalizada, $configuracaoLocal) {
    $local = $configuracaoLocal[$campo] ?? $padrao;
    $personalizado = $configuracaoPersonalizada[$campo] ?? $padrao;

    return [
        'padrao' => $local,
        'personalizado' => $personalizado,
        'atual' => $configuracao[$campo] ?? $local,
    ];
};

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

?>

<style>
.config-email-page {
    padding: 30px;
    max-width: 1200px;
    margin: 0 auto;
    color: #243047;
}

.config-email-topo {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 26px;
}

.config-email-icone {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    border-radius: 14px;
    background: #e8f5ed;
    color: #21834a;
    font-size: 26px;
    flex-shrink: 0;
}

.config-email-topo h1 {
    margin: 0 0 6px;
    font-size: 26px;
    font-weight: 700;
}

.config-email-topo p {
    margin: 0;
    color: #6b7280;
}

.config-email-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 28px;
    margin-bottom: 22px;
    box-shadow: 0 4px 18px rgba(20, 35, 50, .05);
}

.config-email-card h2 {
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 8px;
}

.config-email-descricao {
    color: #6b7280;
    margin-bottom: 24px;
    line-height: 1.6;
}

.config-email-ativacao {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    margin-bottom: 26px;
    background: #f7faf8;
    border: 1px solid #dcebe1;
    border-radius: 10px;
}

.config-email-ativacao input {
    width: 18px;
    height: 18px;
    accent-color: #21834a;
}

.config-email-ativacao label {
    margin: 0;
    font-weight: 600;
    cursor: pointer;
}

.config-email-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.config-email-campo {
    min-width: 0;
}

.config-email-campo label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    font-size: 14px;
}

.config-email-campo input:not([type="color"]),
.config-email-campo select {
    display: block;
    width: 100%;
    min-height: 44px;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
    font: inherit;
    box-sizing: border-box;
}

.config-email-campo input[type="color"] {
    display: block;
    width: 100%;
    height: 48px;
    padding: 4px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    box-sizing: border-box;
}

.config-email-campo input:focus,
.config-email-campo select:focus {
    outline: none;
    border-color: #21834a;
    box-shadow: 0 0 0 3px rgba(33, 131, 74, .12);
}

.config-email-campo input:disabled,
.config-email-campo select:disabled {
    background: #f3f4f6;
    color: #6b7280;
    cursor: not-allowed;
}

.config-email-separador {
    margin: 28px 0 22px;
    border: 0;
    border-top: 1px solid #e5e7eb;
}

.config-email-nota {
    padding: 14px 16px;
    margin: 18px 0 0;
    background: #f7faf8;
    border: 1px solid #dcebe1;
    border-radius: 8px;
    color: #4b5563;
    font-size: 14px;
    line-height: 1.6;
}

.config-email-acoes {
    display: flex;
    justify-content: flex-end;
    margin-top: 28px;
    padding-top: 22px;
    border-top: 1px solid #e5e7eb;
}

.config-email-salvar {
    border: none;
    border-radius: 8px;
    background: #21834a;
    color: #fff;
    padding: 12px 22px;
    font-weight: 600;
    cursor: pointer;
}

.config-email-salvar:hover {
    background: #196b3b;
}

@media (max-width: 700px) {
    .config-email-page {
        padding: 18px;
    }

    .config-email-card {
        padding: 20px;
    }

    .config-email-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .config-email-topo h1 {
        font-size: 22px;
    }

    .config-email-acoes {
        justify-content: stretch;
    }

    .config-email-salvar {
        width: 100%;
    }
}
</style>

<main class="conteudo config-email-page">

    <div class="config-email-topo">
        <div class="config-email-icone" aria-hidden="true">✉</div>

        <div>
            <h1>Configurações de e-mail</h1>
            <p>Configure o envio e a aparência dos e-mails do GymFlow.</p>
        </div>
    </div>

    <?php if ($mensagemSalva): ?>
        <div class="alert alert-success" role="status">
            Configurações salvas com sucesso!
        </div>
    <?php endif; ?>

    <form
        method="POST"
        action="<?= $escapar(BASE_URL) ?>/app/controllers/ConfiguracaoEmailController.php?acao=salvar"
    >
        <input
            type="hidden"
            name="csrf_token"
            value="<?= $escapar($_SESSION['csrf_config_email'] ?? '') ?>"
        >

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
                    <?= $usarPersonalizadoAtual ? 'checked' : '' ?>
                >

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
                            <?= $usarPersonalizadoAtual ? 'required' : 'disabled' ?>
                        >
                    </div>
                <?php endforeach; ?>

                <?php $portaSMTP = $valorSMTP('smtp_porta', 587); ?>

                <div class="config-email-campo">
                    <label for="smtp_porta">Porta SMTP</label>

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
                        <?= $usarPersonalizadoAtual ? 'required' : 'disabled' ?>
                    >
                </div>

                <?php $segurancaSMTP = $valorSMTP('smtp_seguranca', 'tls'); ?>

                <div class="config-email-campo">
                    <label for="smtp_seguranca">Segurança da conexão</label>

                    <select
                        id="smtp_seguranca"
                        name="smtp_seguranca"
                        data-smtp-field
                        data-smtp-padrao="<?= $escapar($segurancaSMTP['padrao']) ?>"
                        data-smtp-personalizado="<?= $escapar($segurancaSMTP['personalizado']) ?>"
                        <?= $usarPersonalizadoAtual ? 'required' : 'disabled' ?>
                    >
                        <?php foreach ([
                            'tls' => 'TLS / STARTTLS',
                            'ssl' => 'SSL',
                            'none' => 'Sem criptografia',
                        ] as $valor => $rotulo): ?>
                            <option
                                value="<?= $escapar($valor) ?>"
                                <?= $segurancaSMTP['atual'] === $valor ? 'selected' : '' ?>
                            >
                                <?= $escapar($rotulo) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="config-email-campo">
                    <label for="smtp_senha">Senha SMTP</label>

                    <input
                        type="password"
                        id="smtp_senha"
                        name="smtp_senha"
                        maxlength="1024"
                        autocomplete="new-password"
                        placeholder="Deixe em branco para manter a senha salva"
                        data-smtp-field
                        <?= $usarPersonalizadoAtual ? '' : 'disabled' ?>
                    >

                    <small style="display:block; margin-top:6px; color:#6b7280;">
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

        <section class="config-email-card">

            <h2>Aparência dos e-mails</h2>

            <p class="config-email-descricao">
                Escolha se os e-mails de recuperação de senha vão acompanhar
                as cores da sua marca ou terão uma identidade visual própria.
            </p>

            <div class="config-email-grid">
                <div class="config-email-campo">
                    <label for="modo_cores_email">Modo de cores</label>

                    <select id="modo_cores_email" name="modo_cores_email">
                        <option
                            value="marca"
                            <?= $modoCores === 'marca' ? 'selected' : '' ?>
                        >
                            Usar cores da minha marca
                        </option>

                        <option
                            value="personalizado"
                            <?= $modoCores === 'personalizado' ? 'selected' : '' ?>
                        >
                            Personalizar cores do e-mail
                        </option>
                    </select>
                </div>
            </div>

            <div
                id="cores_email_personalizadas"
                <?= $modoCores === 'personalizado' ? '' : 'hidden' ?>
            >
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
                                value="<?= $escapar($coresEmail[$campo]) ?>"
                            >
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>

            <p class="config-email-nota" id="nota_modo_cores">
                <?php if ($modoCores === 'marca'): ?>
                    O modo marca está selecionado. A aplicação das cores da marca
                    será conectada ao envio do e-mail na próxima etapa.
                <?php else: ?>
                    Você pode escolher cores exclusivas para os e-mails.
                    Elas não devem alterar o portfólio público.
                <?php endif; ?>
            </p>

        </section>

        <div class="config-email-acoes">
            <button type="submit" class="config-email-salvar">
                Salvar configurações
            </button>
        </div>

    </form>
</main>

<script>
(function () {
    const checkboxSMTP = document.getElementById('usar_personalizado');
    const camposSMTP = document.querySelectorAll('[data-smtp-field]');

    const seletorModo = document.getElementById('modo_cores_email');
    const camposPersonalizados = document.getElementById(
        'cores_email_personalizadas'
    );
    const nota = document.getElementById('nota_modo_cores');

    let modoSMTPAtual = checkboxSMTP && checkboxSMTP.checked
        ? 'personalizado'
        : 'padrao';

    // Mantém os valores de cada modo durante a alternância.
    const valoresSMTP = new WeakMap();

    camposSMTP.forEach(function (campo) {
        if (campo.id === 'smtp_senha') {
            return;
        }

        valoresSMTP.set(campo, {
            padrao: campo.dataset.smtpPadrao ?? '',
            personalizado: campo.dataset.smtpPersonalizado ?? ''
        });
    });

    function atualizarCamposSMTP() {
        if (!checkboxSMTP) {
            return;
        }

        const novoModo = checkboxSMTP.checked
            ? 'personalizado'
            : 'padrao';

        camposSMTP.forEach(function (campo) {
            if (campo.id === 'smtp_senha') {
                campo.disabled = novoModo !== 'personalizado';
                campo.required = false;

                // Nunca mostrar nem reutilizar visualmente uma senha.
                campo.value = '';
                return;
            }

            const valores = valoresSMTP.get(campo);

            if (novoModo !== modoSMTPAtual && valores) {
                // Guarda o que foi digitado no modo anterior.
                valores[modoSMTPAtual] = campo.value;

                // Mostra os valores do modo selecionado.
                campo.value = valores[novoModo];
            }

            campo.disabled = novoModo !== 'personalizado';
            campo.required = novoModo === 'personalizado';
        });

        modoSMTPAtual = novoModo;
    }

    function atualizarModoCores() {
        if (!seletorModo || !camposPersonalizados || !nota) {
            return;
        }

        const usaMarca = seletorModo.value === 'marca';

        camposPersonalizados.hidden = usaMarca;

        nota.textContent = usaMarca
            ? 'O modo marca está selecionado. A aplicação das cores da marca será conectada ao envio do e-mail na próxima etapa.'
            : 'Você pode escolher cores exclusivas para os e-mails. Elas não devem alterar o portfólio público.';
    }

    if (checkboxSMTP) {
        checkboxSMTP.addEventListener('change', atualizarCamposSMTP);
        atualizarCamposSMTP();
    }

    if (seletorModo) {
        seletorModo.addEventListener('change', atualizarModoCores);
        atualizarModoCores();
    }
})();
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>
