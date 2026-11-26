<?php
// app/views/portal_aluno/meus_dados.php

if (!isset($meusDados, $csrfMeusDados, $urlMeusDados)) {

    http_response_code(403);

    exit('Acesso negado.');
}

$tituloPagina = 'Meus Dados';

$cssEspecifico = BASE_URL .
    '/assets/css/meus-dados.css';

$mensagemMeusDados = $mensagemMeusDados ?? null;

$escDados = static function ($valor): string {

    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
};

// =============================================
// INFORMAÇÕES DO ALUNO
// =============================================

$nascimento = 'Não informado';

if (!empty($meusDados['nascimento'])) {

    $timestamp = strtotime(
        $meusDados['nascimento']
    );

    if ($timestamp !== false) {

        $nascimento = date(
            'd/m/Y',
            $timestamp
        );
    }
}

$nomeAlunoPerfil = trim(
    $meusDados['nome'] ?? 'Aluno'
);

$partesNome = preg_split(
    '/\s+/',
    $nomeAlunoPerfil
);

$iniciais = mb_substr(
    $partesNome[0] ?? 'A',
    0,
    1
);

if (count($partesNome) > 1) {

    $iniciais .= mb_substr(
        $partesNome[count($partesNome) - 1],
        0,
        1
    );
}

$iniciais = mb_strtoupper($iniciais);

$cadastroAtivo =
    ($meusDados['status'] ?? '') === 'Ativo';

// Campos protegidos contra edição.

$camposProtegidos = [

    'Nome completo' =>
    $meusDados['nome'] ?? '',

    'CPF' =>
    $meusDados['cpf'] ?? '',

    'RG' =>
    $meusDados['rg'] ?? '',

    'Data de nascimento' =>
    $nascimento,

    'Sexo' =>
    $meusDados['sexo'] ?? '',

    'E-mail de acesso' =>
    $meusDados['email_acesso'] ?? ''
];

require __DIR__ . '/../shared/header.php';

require __DIR__ . '/../shared/sidebar.php';

?>

<main class="meus-dados-page">

    <!-- CABEÇALHO -->

    <header class="md-header">
        <h1>Meus Dados</h1>

        <p>
            Consulte e mantenha suas informações atualizadas.
        </p>

    </header>

    <!-- MENSAGEM -->

    <?php if ($mensagemMeusDados): ?>

        <div
            class="md-mensagem md-mensagem-<?= $mensagemMeusDados['tipo'] === 'sucesso'
                                                ? 'sucesso'
                                                : 'erro' ?>"
            role="status">

            <i data-lucide="<?= $mensagemMeusDados['tipo'] === 'sucesso'
                                ? 'circle-check'
                                : 'circle-alert' ?>"></i>

            <?= $escDados(
                $mensagemMeusDados['texto']
            ) ?>

        </div>

    <?php endif; ?>

    <!-- PERFIL -->

    <section class="md-card md-perfil">

        <div class="md-avatar">

            <?= $escDados($iniciais) ?>

        </div>

        <div class="md-perfil-info">

            <h2>

                <?= $escDados($nomeAlunoPerfil) ?>

            </h2>

            <p>

                <?= $escDados(
                    $meusDados['nome_filial']
                        ?? 'Unidade não informada'
                ) ?>

            </p>

            <span class="md-status <?= $cadastroAtivo
                                        ? 'md-status-ativo'
                                        : 'md-status-inativo' ?>">

                <?= $cadastroAtivo
                    ? 'Cadastro ativo'
                    : $escDados($meusDados['status'] ?? 'Inativo') ?>

            </span>

        </div>

    </section>

    <!-- INFORMAÇÕES PESSOAIS -->

    <section class="md-card">

        <div class="md-card-header">

            <div class="md-card-icon">
                <i data-lucide="user-round"></i>
            </div>

            <div>

                <h2>Informações pessoais</h2>

                <p>Dados registrados pela academia.</p>

            </div>

        </div>

        <div class="md-grid">

            <?php foreach (
                $camposProtegidos as $rotulo => $valor
            ): ?>

                <div class="md-campo">

                    <div class="md-campo-rotulo">

                        <span>
                            <?= $escDados($rotulo) ?>
                        </span>

                        <i data-lucide="lock"></i>

                    </div>

                    <div class="md-readonly">

                        <?= $escDados(
                            $valor !== ''
                                ? $valor
                                : 'Não informado'
                        ) ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <p class="md-aviso">

            <i data-lucide="info"></i>

            Para alterar essas informações,
            entre em contato com a recepção.

        </p>

    </section>

    <!-- INFORMAÇÕES DA ACADEMIA -->

    <section class="md-card">

        <div class="md-card-header">

            <div class="md-card-icon">
                <i data-lucide="building-2"></i>
            </div>

            <div>

                <h2>Minha academia</h2>

                <p>Informações do seu vínculo com a unidade.</p>

            </div>

        </div>

        <div class="md-grid">

            <div class="md-campo">

                <span>Unidade</span>

                <div class="md-readonly">

                    <?= $escDados(
                        $meusDados['nome_filial']
                            ?? 'Não informada'
                    ) ?>

                </div>

            </div>

            <div class="md-campo">

                <span>Professor responsável</span>

                <div class="md-readonly">

                    <?= $escDados(
                        $meusDados['nome_professor']
                            ?? 'Ainda não atribuído'
                    ) ?>

                </div>

            </div>

        </div>

    </section>

    <!-- CONTATO -->

    <section class="md-card">

        <div class="md-card-header">

            <div class="md-card-icon">
                <i data-lucide="contact"></i>
            </div>

            <div>

                <h2>Informações de contato</h2>

                <p>Atualize seu telefone e endereço.</p>

            </div>

        </div>

        <form
            method="POST"
            action="<?= $escDados($urlMeusDados) ?>"
            class="md-form">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= $escDados($csrfMeusDados) ?>">

            <input
                type="hidden"
                name="operacao"
                value="atualizar_contato">

            <div class="md-campo">

                <label for="telefone">Telefone</label>

                <input
                    type="tel"
                    id="telefone"
                    name="telefone"
                    maxlength="15"
                    autocomplete="tel-national"
                    placeholder="(11) 99999-9999"
                    value="<?= $escDados(
                                $meusDados['telefone'] ?? ''
                            ) ?>"
                    required>

            </div>

            <div class="md-campo">

                <label for="endereco">Endereço</label>

                <textarea
                    id="endereco"
                    name="endereco"
                    maxlength="500"
                    autocomplete="street-address"
                    rows="3"
                    placeholder="Rua, número, bairro e cidade"><?= $escDados(
                                                                    $meusDados['endereco'] ?? ''
                                                                ) ?></textarea>

            </div>

            <div class="md-acoes">

                <button type="submit" class="md-btn">

                    <i data-lucide="save"></i>

                    Salvar alterações

                </button>

            </div>

        </form>

    </section>

    <!-- SEGURANÇA -->

    <section class="md-card">

        <div class="md-card-header">

            <div class="md-card-icon">

                <i data-lucide="shield-check"></i>

            </div>

            <div>

                <h2>Segurança da conta</h2>

                <p>Gerencie sua senha de acesso.</p>

            </div>

        </div>

        <details class="md-seguranca">

            <summary>

                <i data-lucide="lock-keyhole"></i>

                Alterar minha senha

                <i
                    class="md-seta"
                    data-lucide="chevron-down">
                </i>

            </summary>

            <form
                method="POST"
                action="<?= $escDados($urlMeusDados) ?>"
                class="md-form">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= $escDados($csrfMeusDados) ?>">

                <input
                    type="hidden"
                    name="operacao"
                    value="alterar_senha">

                <div class="md-campo">

                    <label for="senha_atual">
                        Senha atual
                    </label>

                    <input
                        type="password"
                        id="senha_atual"
                        name="senha_atual"
                        autocomplete="current-password"
                        required>

                </div>

                <div class="md-campo">

                    <label for="nova_senha">
                        Nova senha
                    </label>

                    <input
                        type="password"
                        id="nova_senha"
                        name="nova_senha"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required>

                </div>

                <div class="md-campo">

                    <label for="confirmar_senha">
                        Confirmar nova senha
                    </label>

                    <input
                        type="password"
                        id="confirmar_senha"
                        name="confirmar_senha"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required>

                </div>

                <p class="md-aviso">

                    Utilize uma senha com pelo menos
                    8 caracteres.

                </p>

                <div class="md-acoes">

                    <button
                        type="submit"
                        class="md-btn">

                        <i data-lucide="key-round"></i>

                        Alterar senha

                    </button>

                </div>

            </form>

        </details>

    </section>

</main>

<?php require __DIR__ . '/../shared/footer.php'; ?>