<?php
if (!isset($filiais)) {
    header("Location: " . BASE_URL . "/app/controllers/FuncionarioController.php?acao=cadastrar");
    exit;
}
/** @var array $filiais */
/** @var string $erro */

$tituloPagina = "Cadastrar Funcionário";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<main class="conteudo financeiro-page">
    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Cadastrar Funcionário</h1>
            <p>Adicione um novo membro à equipe da academia.</p>
        </div>
        <div class="fin-header-actions">
            <a href="<?= BASE_URL ?>/app/controllers/FuncionarioController.php?acao=listar" class="fin-btn fin-btn-secondary">Voltar para Lista</a>
        </div>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="fin-alert fin-alert-error ui-alerta-erro-compacto">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <section class="fin-table-card ui-card-pad-24">
        <form action="<?= BASE_URL ?>/app/controllers/FuncionarioController.php?acao=cadastrar" method="POST">

            <h3 class="ui-secao-titulo">Dados Básicos</h3>

            <div class="fin-form-grid ui-grid-2col">
                <div class="fin-form-group">
                    <label>Nome Completo *</label>
                    <input type="text" name="nome" class="fin-input" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                </div>
                <div class="fin-form-group">
                    <label>Cargo / Perfil (Role) *</label>
                    <select name="role" class="fin-select" required>
                        <option value="">Selecione...</option>
                        <option value="Admin" <?= ($_POST['role'] ?? '') === 'Admin' ? 'selected' : '' ?>>Administrador</option>
                        <option value="Professor" <?= ($_POST['role'] ?? '') === 'Professor' ? 'selected' : '' ?>>Instrutor / Professor</option>
                        <option value="Recepcao" <?= ($_POST['role'] ?? '') === 'Recepcao' ? 'selected' : '' ?>>Recepção</option>
                    </select>
                </div>
            </div>

            <h3 class="ui-secao-titulo">Contato e Acesso</h3>

            <div class="fin-form-grid ui-grid-2col">
                <div class="fin-form-group">
                    <label>E-mail (Login) *</label>
                    <input type="email" name="email" class="fin-input" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="fin-form-group">
                    <label>Telefone / WhatsApp</label>
                    <input type="text" name="telefone" class="fin-input" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
                </div>
                <div class="fin-form-group">
                    <label for="senha">Senha *</label>

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
                    <label for="confirmar_senha">Confirmar senha *</label>

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

            <h3 class="ui-secao-titulo">Endereço</h3>

            <div class="fin-form-grid ui-grid-cep">
                <div class="fin-form-group">
                    <label>CEP</label>
                    <div class="ui-cep-grupo">
                        <input type="text" id="cep" class="fin-input" maxlength="9" placeholder="00000-000">
                        <button type="button" class="fin-btn fin-btn-secondary ui-btn-cep" onclick="buscarCep()">Buscar</button>
                    </div>
                </div>
                <div class="fin-form-group">
                    <label>Endereço Completo (Rua, Número, Bairro, Cidade - UF)</label>
                    <input type="text" name="endereco" id="endereco" class="fin-input" value="<?= htmlspecialchars($_POST['endereco'] ?? '') ?>" placeholder="Digite ou busque pelo CEP">
                </div>
            </div>

            <h3 class="ui-secao-titulo">Vínculo de Unidade</h3>

            <div class="fin-form-grid ui-grid-1col">
                <div class="fin-form-group">
                    <label>Selecione a Unidade (Filial) *</label>
                    <select name="id_filial" class="fin-select" required>
                        <option value="">Selecione uma filial...</option>
                        <?php foreach ($filiais as $filial): ?>
                            <option value="<?= $filial['id'] ?>" <?= ($_POST['id_filial'] ?? '') == $filial['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($filial['nome']) ?> (ID: <?= $filial['id'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="ui-acoes-direita">
                <a href="<?= BASE_URL ?>/app/controllers/FuncionarioController.php?acao=listar" class="fin-btn fin-btn-secondary">Cancelar</a>
                <button type="submit" class="fin-btn fin-btn-primary">Salvar Cadastro</button>
            </div>
        </form>
    </section>
</main>

<script src="<?= BASE_URL ?>/assets/js/cep-busca.js" defer></script>

<?php include __DIR__ . '/../shared/footer.php'; ?>