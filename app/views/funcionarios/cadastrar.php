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
        <div class="fin-alert fin-alert-error" style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 500;">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <section class="fin-table-card" style="padding: 24px;">
        <form action="<?= BASE_URL ?>/app/controllers/FuncionarioController.php?acao=cadastrar" method="POST">

            <h3 style="margin-bottom: 16px; font-size: 16px; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Dados Básicos</h3>

            <div class="fin-form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
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

            <h3 style="margin-bottom: 16px; font-size: 16px; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Contato e Acesso</h3>

            <div class="fin-form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div class="fin-form-group">
                    <label>E-mail (Login) *</label>
                    <input type="email" name="email" class="fin-input" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="fin-form-group">
                    <label>Telefone / WhatsApp</label>
                    <input type="text" name="telefone" class="fin-input" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
                </div>
                <div class="fin-form-group">
                    <label>Senha *</label>
                    <input type="password" name="senha" class="fin-input" required>
                </div>
            </div>

            <h3 style="margin-bottom: 16px; font-size: 16px; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Endereço</h3>
            
            <div class="fin-form-grid" style="display: grid; grid-template-columns: 150px 1fr; gap: 16px; margin-bottom: 24px;">
                <div class="fin-form-group">
                    <label>CEP</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="cep" class="fin-input" maxlength="9" placeholder="00000-000">
                        <button type="button" class="fin-btn fin-btn-secondary" onclick="buscarCep()" style="padding: 8px 12px;">🔎</button>
                    </div>
                </div>
                <div class="fin-form-group">
                    <label>Endereço Completo (Rua, Número, Bairro, Cidade - UF)</label>
                    <input type="text" name="endereco" id="endereco" class="fin-input" value="<?= htmlspecialchars($_POST['endereco'] ?? '') ?>" placeholder="Digite ou busque pelo CEP">
                </div>
            </div>

            <h3 style="margin-bottom: 16px; font-size: 16px; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Vínculo de Unidade</h3>

            <div class="fin-form-grid" style="display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 32px;">
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

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <a href="<?= BASE_URL ?>/app/controllers/FuncionarioController.php?acao=listar" class="fin-btn fin-btn-secondary">Cancelar</a>
                <button type="submit" class="fin-btn fin-btn-primary">Salvar Cadastro</button>
            </div>
        </form>
    </section>
</main>

<script>
function buscarCep() {
    var cep = document.getElementById('cep').value.replace(/\D/g, '');
    if (cep !== "") {
        var validacep = /^[0-9]{8}$/;
        if(validacep.test(cep)) {
            document.getElementById('endereco').value = "...";
            fetch('https://viacep.com.br/ws/'+ cep +'/json/')
            .then(response => response.json())
            .then(data => {
                if (!("erro" in data)) {
                    document.getElementById('endereco').value = data.logradouro + ',  - ' + data.bairro + ', ' + data.localidade + ' - ' + data.uf;
                } else {
                    alert("CEP não encontrado.");
                    document.getElementById('endereco').value = "";
                }
            })
            .catch(error => {
                alert("Erro ao buscar CEP.");
                document.getElementById('endereco').value = "";
            });
        } else {
            alert("Formato de CEP inválido.");
        }
    }
}
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>