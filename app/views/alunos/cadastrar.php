<?php
// app/views/alunos/cadastrar.php

if (!defined('BASE_URL')) {
    http_response_code(403);
    exit('Acesso negado.');
}

// Inicializar variáveis recebidas do Controller.

$baseUrl = BASE_URL . '/app/controllers/AlunoController.php';

$erro = $erro ?? '';

$dados = $dados ?? [];

$filiais = $filiais ?? [];

$professores = $professores ?? [];

// Inicializar os campos do formulário.

$campos = [
    'nome',
    'cpf',
    'rg',
    'sexo',
    'nascimento',
    'email',
    'telefone',
    'endereco',
    'filial_id',
    'professor_id'
];

foreach ($campos as $campo) {
    $dados[$campo] = $dados[$campo] ?? '';
}

$tituloPagina = "Cadastrar Aluno";

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/alunos-form.css">

<main class="conteudo financeiro-page">

    <!-- Título e botão de voltar -->
    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Cadastrar Novo Aluno</h1>
            <p>Preencha os dados abaixo para registrar um novo aluno no sistema.</p>
        </div>
        <div class="fin-header-actions">
            <a href="<?= $baseUrl ?>?acao=listar" class="fin-btn fin-btn-secondary">← Voltar para Lista</a>
        </div>
    </div>

    <!-- Mensagem de erro (se tiver) -->
    <?php if (!empty($erro)): ?>
        <div class="caixa-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <section class="fin-table-card aluno-form-card">

        <form action="<?= $baseUrl ?>?acao=cadastrar" method="POST">

            <!-- PARTE 1: DADOS PESSOAIS -->
            <div class="secao">
                <h3 class="titulo-secao">Dados Pessoais</h3>

                <div class="grade">
                    <div class="fin-form-group">
                        <label>Nome Completo *</label>
                        <input type="text" name="nome" class="fin-input" maxlength="100" placeholder="Ex: João da Silva" value="<?= htmlspecialchars($dados['nome']) ?>" required>
                    </div>

                    <div class="fin-form-group">
                        <label>CPF *</label>
                        <input type="text" name="cpf" class="fin-input" maxlength="14" inputmode="numeric" placeholder="000.000.000-00" value="<?= htmlspecialchars($dados['cpf']) ?>" required>
                    </div>

                    <div class="fin-form-group">
                        <label>RG</label>
                        <input type="text" name="rg" class="fin-input" maxlength="20" placeholder="0.000.000" value="<?= htmlspecialchars($dados['rg']) ?>">
                    </div>

                    <div class="fin-form-group">
                        <label>Sexo *</label>
                        <select name="sexo" class="fin-select" required>
                            <option value="">Selecione...</option>
                            <option value="Masculino" <?php if ($dados['sexo'] == 'Masculino') {
                                                            echo 'selected';
                                                        } ?>>Masculino</option>
                            <option value="Feminino" <?php if ($dados['sexo'] == 'Feminino') {
                                                            echo 'selected';
                                                        } ?>>Feminino</option>
                            <option value="Outro" <?php if ($dados['sexo'] == 'Outro') {
                                                        echo 'selected';
                                                    } ?>>Outro</option>
                        </select>
                    </div>

                    <div class="fin-form-group">
                        <label>Data de Nascimento *</label>
                        <input type="date" name="nascimento" class="fin-input" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($dados['nascimento']) ?>" required>
                    </div>
                </div>
            </div>

            <!-- PARTE 2: CONTATO E ACESSO -->
            <!-- PARTE 2: CONTATO E ACESSO -->

            <div class="secao">

                <h3 class="titulo-secao">
                    Contato &amp; Acesso ao Portal
                </h3>

                <div class="grade">

                    <div class="fin-form-group">

                        <label for="email">E-mail *</label>

                        <input
                            type="email"
                            id="email"
                            name="email" maxlength="100"
                            class="fin-input"
                            placeholder="exemplo@email.com"
                            value="<?= htmlspecialchars($dados['email']) ?>"
                            autocomplete="email"
                            required>

                    </div>

                    <div class="fin-form-group">

                        <label for="telefone">
                            Telefone / WhatsApp *
                        </label>

                        <input
                            type="tel"
                            id="telefone"
                            name="telefone" maxlength="15"
                            class="fin-input"
                            placeholder="(11) 99999-9999"
                            value="<?= htmlspecialchars($dados['telefone']) ?>"
                            autocomplete="tel"
                            required>

                    </div>

                    <div class="fin-form-group">

                        <label for="senha">Senha de acesso *</label>

                        <input
                            type="password"
                            id="senha"
                            name="senha"
                            class="fin-input"
                            placeholder="Mínimo de 8 caracteres"
                            minlength="8"
                            maxlength="72"
                            autocomplete="new-password"
                            required>

                    </div>

                    <div class="fin-form-group">

                        <label for="confirmar_senha">
                            Confirmar senha *
                        </label>

                        <input
                            type="password"
                            id="confirmar_senha"
                            name="confirmar_senha"
                            class="fin-input"
                            placeholder="Repita a senha"
                            minlength="8"
                            maxlength="72"
                            autocomplete="new-password"
                            required>

                    </div>

                </div>

            </div>

            <!-- PARTE 3: ENDEREÇO -->
            <!-- Os campos de CEP, rua, número etc. só ajudam a montar o "Endereço Completo".
                 Só o campo "endereco" é enviado e salvo no banco. -->
            <div class="secao">
                <h3 class="titulo-secao">Endereço</h3>

                <div class="linha-cep">
                    <div class="fin-form-group">
                        <label>CEP</label>
                        <!-- Quando sai do campo, busca o endereço pelo CEP -->
                        <input type="text" id="cep" class="fin-input" placeholder="00000-000" maxlength="9" onblur="buscarCep(this.value)">
                    </div>

                    <div class="fin-form-group">
                        <label>Logradouro / Rua</label>
                        <input type="text" id="rua" class="fin-input" maxlength="150" placeholder="Rua, Avenida..." oninput="atualizarEndereco()">
                    </div>
                </div>

                <div class="linha-numero">
                    <div class="fin-form-group">
                        <label>Número</label>
                        <input type="text" id="numero" class="fin-input" maxlength="20" placeholder="123" oninput="atualizarEndereco()">
                    </div>

                    <div class="fin-form-group">
                        <label>Bairro</label>
                        <input type="text" id="bairro" class="fin-input" maxlength="100" placeholder="Bairro" oninput="atualizarEndereco()">
                    </div>

                    <div class="fin-form-group">
                        <label>Cidade / UF</label>
                        <input type="text" id="cidade_uf" class="fin-input" maxlength="100" placeholder="São Paulo/SP" oninput="atualizarEndereco()">
                    </div>
                </div>

                <div class="caixa-condominio">
                    <input type="checkbox" id="is_condominio" onchange="toggleCondominio()">
                    <label for="is_condominio">É prédio / condomínio?</label>
                </div>

                <div id="div_condominio">
                    <div class="fin-form-group">
                        <label>Bloco / Torre</label>
                        <input type="text" id="bloco" class="fin-input" maxlength="50" placeholder="Bloco A" oninput="atualizarEndereco()">
                    </div>
                    <div class="fin-form-group">
                        <label>Apto / Sala</label>
                        <input type="text" id="apto" class="fin-input" maxlength="50" placeholder="Apto 42" oninput="atualizarEndereco()">
                    </div>
                </div>

                <div class="fin-form-group">
                    <label>Endereço Completo (Formatado para o Sistema)</label>
                    <input type="text" id="endereco" name="endereco" maxlength="500" class="fin-input campo-endereco" value="<?= htmlspecialchars($dados['endereco']) ?>" placeholder="Ex: Rua Exemplo, 123 - Bairro - Cidade/UF">
                </div>
            </div>

            <!-- PARTE 4: FILIAL E PROFESSOR -->
            <div class="secao secao-vinculo">
                <h3 class="titulo-secao">Vínculo na Academia</h3>

                <div class="grade">
                    <div class="fin-form-group">
                        <label>Filial / Unidade *</label>
                        <select name="filial_id" class="fin-select" required>
                            <option value="">Selecione uma filial...</option>
                            <?php foreach ($filiais as $filial): ?>
                                <option value="<?= $filial['id'] ?>" <?php if ($dados['filial_id'] == $filial['id']) {
                                                                            echo 'selected';
                                                                        } ?>>
                                    <?= htmlspecialchars($filial['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fin-form-group">
                        <label>Professor Responsável</label>
                        <select name="professor_id" class="fin-select">
                            <option value="">Nenhum (Sem instrutor fixo)</option>
                            <?php foreach ($professores as $professor): ?>
                                <option value="<?= $professor['id'] ?>" <?php if ($dados['professor_id'] == $professor['id']) {
                                                                            echo 'selected';
                                                                        } ?>>
                                    <?= htmlspecialchars($professor['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="botoes-form">
                <a href="<?= $baseUrl ?>?acao=listar" class="fin-btn fin-btn-secondary">Cancelar</a>
                <button type="submit" class="fin-btn fin-btn-primary">Salvar Aluno</button>
            </div>

        </form>
    </section>

</main>

<script src="<?= BASE_URL ?>/assets/js/alunos-form.js" defer></script>

<?php include __DIR__ . '/../shared/footer.php'; ?>