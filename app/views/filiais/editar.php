<?php
if (!isset($filial)) {
    $id = $_GET['id'] ?? null;
    header("Location: " . BASE_URL . "/app/controllers/FilialController.php?acao=editar" . ($id ? "&id=" . $id : ""));
    exit;
}

/** @var array $filial */
/** @var string $erro */

$tituloPagina = "Editar Filial";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<div class="modal-overlay">

    <div class="modal-editar-filial">

        <div class="modal-editar-header">
            <h2>Editar Filial</h2>

            <a
                href="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=listar"
                class="modal-fechar"
                aria-label="Fechar">
                ×
            </a>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="mensagem-erro-filial">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form
            class="form-editar-filial"
            action="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=editar&id=<?= $filial['id'] ?>"
            method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= $filial['id'] ?>">

            <div class="campo-editar-filial">
                <label for="nome">Nome</label>

                <input
                    type="text"
                    id="nome"
                    name="nome"
                    value="<?= htmlspecialchars($filial['nome']) ?>"
                    required>
            </div>

            <div class="campo-editar-filial">
                <label for="cnpj">CNPJ</label>

                <input
                    type="text"
                    id="cnpj"
                    name="cnpj"
                    value="<?= htmlspecialchars($filial['cnpj']) ?>"
                    required>
            </div>

            <div class="campo-editar-filial">
                <label for="telefone">Telefone</label>

                <input
                    type="text"
                    id="telefone"
                    name="telefone"
                    value="<?= htmlspecialchars($filial['telefone']) ?>">
            </div>
            
            <div class="campo-editar-filial">
                <label for="email">E-mail</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($filial['email'] ?? '') ?>">
            </div>
            
            <h3 style="margin-top: 24px; margin-bottom: 16px; font-size: 16px; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; width: 100%;">Endereço</h3>

            <div style="display: flex; gap: 16px; width: 100%; align-items: flex-end; margin-bottom: 16px;">
                <div class="campo-editar-filial" style="flex: 1;">
                    <label>CEP</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="cep" maxlength="9" placeholder="00000-000">
                        <button type="button" class="fin-btn fin-btn-secondary" onclick="buscarCep()" style="padding: 8px 12px;">🔎</button>
                    </div>
                </div>
                
                <div class="campo-editar-filial" style="flex: 3;">
                    <label for="endereco">Endereço (Rua, Avenida, etc.)</label>
                    <input
                        type="text"
                        id="endereco"
                        name="endereco"
                        value="<?= htmlspecialchars($filial['endereco'] ?? '') ?>"
                        placeholder="Digite ou busque pelo CEP">
                </div>
            </div>

            <div style="display: flex; gap: 16px; width: 100%; align-items: flex-end;">
                <div class="campo-editar-filial" style="flex: 1;">
                    <label for="numero">Número</label>
                    <input
                        type="text"
                        id="numero"
                        name="numero"
                        value="<?= htmlspecialchars($filial['numero'] ?? '') ?>"
                        placeholder="Ex: 123">
                </div>
                
                <div class="campo-editar-filial" style="flex: 3;">
                    <label for="complemento">Complemento (Bloco, Apto, Sala, etc.)</label>
                    <input
                        type="text"
                        id="complemento"
                        name="complemento"
                        value="<?= htmlspecialchars($filial['complemento'] ?? '') ?>"
                        placeholder="Ex: Sala 42">
                </div>
            </div>

            <div class="campo-editar-filial">
                <label for="responsavel">Responsável</label>

                <input
                    type="text"
                    id="responsavel"
                    name="responsavel"
                    value="<?= htmlspecialchars($filial['responsavel']) ?>"
                    required>
            </div>

            <div class="modal-editar-acoes">

                <a
                    href="<?= BASE_URL ?>/app/controllers/FilialController.php?acao=listar"
                    class="btn-cancelar-editar">
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="btn-salvar-editar">
                    Salvar
                </button>

            </div>

        </form>

    </div>

</div>

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
                    document.getElementById('endereco').value = data.logradouro + ', ' + data.bairro + ', ' + data.localidade + ' - ' + data.uf;
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