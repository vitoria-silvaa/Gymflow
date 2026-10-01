<?php

/** @var int $id */
/** @var array<string, mixed> $dados */
/** @var array<int, array<string, mixed>> $filiais */
/** @var string $erro */
/** @var string $baseUrl */
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<h1>Editar Aluno</h1>

<?php if ($erro !== ''): ?>
    <p style="color: red; font-weight: bold;">
        <?= htmlspecialchars($erro) ?>
    </p>
<?php endif; ?>

<form
    action="<?= $baseUrl ?>?acao=editar&id=<?= $id ?>"
    method="POST">
    <input type="hidden" name="id" value="<?= $id ?>">

    <label>Nome *:</label>
    <input
        type="text"
        name="nome"
        value="<?= htmlspecialchars($dados['nome']) ?>"
        required>
    <br><br>

    <label>CPF *:</label>
    <input
        type="text"
        name="cpf"
        value="<?= htmlspecialchars($dados['cpf']) ?>"
        required>
    <br><br>

    <label>RG:</label>
    <input
        type="text"
        name="rg"
        value="<?= htmlspecialchars($dados['rg'] ?? '') ?>">
    <br><br>

    <label>Sexo *:</label>
    <select name="sexo" required>
        <option
            value="Masculino"
            <?= $dados['sexo'] === 'Masculino' ? 'selected' : '' ?>>
            Masculino
        </option>
        <option
            value="Feminino"
            <?= $dados['sexo'] === 'Feminino' ? 'selected' : '' ?>>
            Feminino
        </option>
        <option
            value="Outro"
            <?= $dados['sexo'] === 'Outro' ? 'selected' : '' ?>>
            Outro
        </option>
    </select>
    <br><br>

    <label>Data de Nascimento *:</label>
    <input
        type="date"
        name="nascimento"
        value="<?= htmlspecialchars($dados['nascimento']) ?>"
        required>
    <br><br>

    <label>E-mail *:</label>
    <input
        type="email"
        name="email"
        value="<?= htmlspecialchars($dados['email']) ?>"
        required>
    <br><br>

    <label>Telefone *:</label>
    <input
        type="text"
        name="telefone"
        value="<?= htmlspecialchars($dados['telefone']) ?>"
        required>
    <br><br>

    <fieldset style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px;">
        <legend>Endereço</legend>

        <label>CEP:</label>
        <input type="text" id="cep" placeholder="00000-000" maxlength="9" onblur="buscarCep(this.value)">
        <br><br>

        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
            <div style="flex: 2;">
                <label>Logradouro/Rua:</label>
                <input type="text" id="rua" style="width: 100%;" oninput="atualizarEndereco()">
            </div>
            <div style="flex: 1;">
                <label>Número:</label>
                <input type="text" id="numero" style="width: 100%;" oninput="atualizarEndereco()">
            </div>
        </div>

        <div style="display: flex; align-items: center; margin-bottom: 15px;">
            <input type="checkbox" id="is_condominio" onchange="toggleCondominio()" style="width: 18px; height: 18px; margin: 0 8px 0 0; cursor: pointer;">
            <label for="is_condominio" style="margin: 0; cursor: pointer; font-weight: bold; display: inline;">É prédio / condomínio?</label>
        </div>
        
        <div id="div_condominio" style="display: none; gap: 10px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label>Bloco/Torre:</label>
                <input type="text" id="bloco" style="width: 100%;" oninput="atualizarEndereco()">
            </div>
            <div style="flex: 1;">
                <label>Apto/Sala:</label>
                <input type="text" id="apto" style="width: 100%;" oninput="atualizarEndereco()">
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label>Bairro:</label>
                <input type="text" id="bairro" style="width: 100%;" oninput="atualizarEndereco()">
            </div>
            <div style="flex: 1;">
                <label>Cidade/UF:</label>
                <input type="text" id="cidade_uf" style="width: 100%;" oninput="atualizarEndereco()">
            </div>
        </div>

        <label>Endereço Completo (Salvo no sistema):</label>
        <input
            type="text"
            id="endereco"
            name="endereco"
            value="<?= htmlspecialchars($dados['endereco'] ?? '') ?>"
            placeholder="Ex: Rua, Bairro - Cidade/UF"
            style="width: 100%; background: #f9f9f9;"
        >
    </fieldset>

    <script>
        function toggleCondominio() {
            document.getElementById('div_condominio').style.display = document.getElementById('is_condominio').checked ? 'flex' : 'none';
            atualizarEndereco();
        }

        function atualizarEndereco() {
            let rua = document.getElementById('rua').value;
            let numero = document.getElementById('numero').value;
            let bairro = document.getElementById('bairro').value;
            let cidade_uf = document.getElementById('cidade_uf').value;
            let is_cond = document.getElementById('is_condominio').checked;
            let bloco = document.getElementById('bloco').value;
            let apto = document.getElementById('apto').value;

            let partes = [];
            if (rua) {
                let logradouro = rua;
                if (numero) logradouro += ", " + numero;
                partes.push(logradouro);
            }
            if (is_cond) {
                if (bloco) partes.push("Bloco " + bloco);
                if (apto) partes.push("Apto " + apto);
            }
            if (bairro) partes.push(bairro);
            if (cidade_uf) partes.push(cidade_uf);

            if (partes.length > 0) {
                document.getElementById('endereco').value = partes.join(' - ');
            }
        }

        function buscarCep(cep) {
            cep = cep.replace(/\D/g, '');
            if (cep.length !== 8) return;

            document.getElementById('endereco').value = 'Buscando...';

            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(res => res.json())
                .then(data => {
                    if (data.erro) {
                        alert('CEP não encontrado!');
                        document.getElementById('endereco').value = '';
                        return;
                    }
                    document.getElementById('rua').value = data.logradouro || '';
                    document.getElementById('bairro').value = data.bairro || '';
                    document.getElementById('cidade_uf').value = `${data.localidade || ''}/${data.uf || ''}`;
                    atualizarEndereco();
                })
                .catch(() => {
                    alert('Erro ao buscar o CEP.');
                    document.getElementById('endereco').value = '';
                });
        }
    </script>

    <label>Filial *:</label>
    <select name="filial_id" required>
        <?php foreach ($filiais as $filial): ?>
            <option
                value="<?= $filial['id'] ?>"
                <?= $dados['filial_id'] == $filial['id']
                    ? 'selected'
                    : '' ?>>
                <?= htmlspecialchars($filial['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label>Professor Responsável:</label>
    <select name="professor_id">
        <option value="">Nenhum</option>

        <?php foreach ($professores as $professor): ?>
            <option
                value="<?= $professor['id'] ?>"
                <?= ($dados['professor_id'] ?? '') == $professor['id']
                    ? 'selected'
                    : '' ?>>
                <?= htmlspecialchars($professor['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label>Status *:</label>
    <select name="status" required>
        <option
            value="Ativo"
            <?= $dados['status'] === 'Ativo' ? 'selected' : '' ?>>
            Ativo
        </option>
        <option
            value="Inativo"
            <?= $dados['status'] === 'Inativo' ? 'selected' : '' ?>>
            Inativo
        </option>
    </select>
    <br><br>

    <label>Nova Senha:</label>
    <input
        type="password"
        name="senha"
        minlength="6"
        placeholder="Deixe vazio para manter a senha">
    <br><br>

    <button type="submit">Salvar</button>

    <a
        href="<?= $baseUrl ?>?acao=visualizar&id=<?= $id ?>">
        Cancelar
    </a>
</form>

<?php include __DIR__ . '/../shared/footer.php'; ?>