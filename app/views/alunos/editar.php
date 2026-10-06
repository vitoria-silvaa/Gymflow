<?php

/** @var int $id */
/** @var array<string, mixed> $dados */
/** @var array<int, array<string, mixed>> $filiais */
/** @var array<int, array<string, mixed>> $professores */
/** @var string $erro */
/** @var string $baseUrl */

$tituloPagina = "Editar Aluno";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<main class="conteudo financeiro-page">

    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Editar Aluno #<?= htmlspecialchars((string)$id) ?></h1>
            <p>Atualize as informações cadastrais do aluno abaixo.</p>
        </div>
        <div class="fin-header-actions">
            <a href="<?= $baseUrl ?>?acao=visualizar&id=<?= $id ?>" class="fin-btn fin-btn-secondary">
                ← Ver Cadastro
            </a>
            <a href="<?= $baseUrl ?>?acao=listar" class="fin-btn fin-btn-secondary">
                Voltar para Lista
            </a>
        </div>
    </div>

    <?php if (!empty($erro)): ?>
        <div style="background-color: #fee2e2; color: #b91c1c; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; font-weight: 600; border: 1px solid #fecaca;">
            ⚠️ <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <!-- CARD DO FORMULÁRIO -->
    <section class="fin-table-card" style="padding: 28px;">

        <form action="<?= $baseUrl ?>?acao=editar&id=<?= $id ?>" method="POST">
            <input type="hidden" name="id" value="<?= $id ?>">

            <!-- SEÇÃO 1: DADOS PESSOAIS -->
            <div style="margin-bottom: 28px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                    👤 Dados Pessoais
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px;">

                    <div class="fin-form-group">
                        <label>Nome Completo *</label>
                        <input
                            type="text"
                            name="nome"
                            class="fin-input"
                            value="<?= htmlspecialchars($dados['nome'] ?? '') ?>"
                            required>
                    </div>

                    <div class="fin-form-group">
                        <label>CPF *</label>
                        <input
                            type="text"
                            name="cpf"
                            class="fin-input"
                            value="<?= htmlspecialchars($dados['cpf'] ?? '') ?>"
                            required>
                    </div>

                    <div class="fin-form-group">
                        <label>RG</label>
                        <input
                            type="text"
                            name="rg"
                            class="fin-input"
                            value="<?= htmlspecialchars($dados['rg'] ?? '') ?>">
                    </div>

                    <div class="fin-form-group">
                        <label>Sexo *</label>
                        <select name="sexo" class="fin-select" required>
                            <option value="Masculino" <?= ($dados['sexo'] ?? '') === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                            <option value="Feminino" <?= ($dados['sexo'] ?? '') === 'Feminino' ? 'selected' : '' ?>>Feminino</option>
                            <option value="Outro" <?= ($dados['sexo'] ?? '') === 'Outro' ? 'selected' : '' ?>>Outro</option>
                        </select>
                    </div>

                    <div class="fin-form-group">
                        <label>Data de Nascimento *</label>
                        <input
                            type="date"
                            name="nascimento"
                            class="fin-input"
                            value="<?= htmlspecialchars($dados['nascimento'] ?? '') ?>"
                            required>
                    </div>

                    <div class="fin-form-group">
                        <label>Status da Conta *</label>
                        <select name="status" class="fin-select" required>
                            <option value="Ativo" <?= ($dados['status'] ?? '') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="Inativo" <?= ($dados['status'] ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>

                </div>
            </div>

            <!-- SEÇÃO 2: CONTATO E ACESSO -->
            <div style="margin-bottom: 28px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                    📱 Contato & Acesso ao Portal
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px;">

                    <div class="fin-form-group">
                        <label>E-mail *</label>
                        <input
                            type="email"
                            name="email"
                            class="fin-input"
                            value="<?= htmlspecialchars($dados['email'] ?? '') ?>"
                            required>
                    </div>

                    <div class="fin-form-group">
                        <label>Telefone / WhatsApp *</label>
                        <input
                            type="text"
                            name="telefone"
                            class="fin-input"
                            value="<?= htmlspecialchars($dados['telefone'] ?? '') ?>"
                            required>
                    </div>

                    <div class="fin-form-group">
                        <label>Nova Senha (deixe em branco para não alterar)</label>
                        <input
                            type="password"
                            name="senha"
                            class="fin-input"
                            placeholder="Mínimo 6 caracteres se alterar"
                            minlength="6">
                    </div>

                </div>
            </div>

            <!-- SEÇÃO 3: ENDEREÇO -->
            <div style="margin-bottom: 28px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                    📍 Endereço
                </h3>

                <div style="display: grid; grid-template-columns: 160px 1fr; gap: 18px; margin-bottom: 16px;">
                    <div class="fin-form-group">
                        <label>CEP</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" id="cep" class="fin-input" placeholder="00000-000" maxlength="9" onblur="buscarCep(this.value)">
                        </div>
                    </div>

                    <div class="fin-form-group">
                        <label>Logradouro / Rua</label>
                        <input type="text" id="rua" class="fin-input" placeholder="Rua, Avenida..." oninput="atualizarEndereco()">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 120px 1fr 1fr; gap: 18px; margin-bottom: 16px;">
                    <div class="fin-form-group">
                        <label>Número</label>
                        <input type="text" id="numero" class="fin-input" placeholder="123" oninput="atualizarEndereco()">
                    </div>

                    <div class="fin-form-group">
                        <label>Bairro</label>
                        <input type="text" id="bairro" class="fin-input" placeholder="Bairro" oninput="atualizarEndereco()">
                    </div>

                    <div class="fin-form-group">
                        <label>Cidade / UF</label>
                        <input type="text" id="cidade_uf" class="fin-input" placeholder="São Paulo/SP" oninput="atualizarEndereco()">
                    </div>
                </div>

                <div style="display: flex; align-items: center; margin-bottom: 16px; background-color: #f8fafc; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <input type="checkbox" id="is_condominio" onchange="toggleCondominio()" style="width: 18px; height: 18px; margin: 0 10px 0 0; cursor: pointer;">
                    <label for="is_condominio" style="margin: 0; cursor: pointer; font-weight: 600; color: #334155;">É prédio / condomínio?</label>
                </div>

                <div id="div_condominio" style="display: none; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 16px;">
                    <div class="fin-form-group">
                        <label>Bloco / Torre</label>
                        <input type="text" id="bloco" class="fin-input" placeholder="Bloco A" oninput="atualizarEndereco()">
                    </div>
                    <div class="fin-form-group">
                        <label>Apto / Sala</label>
                        <input type="text" id="apto" class="fin-input" placeholder="Apto 42" oninput="atualizarEndereco()">
                    </div>
                </div>

                <div class="fin-form-group">
                    <label>Endereço Completo (Salvo no Sistema)</label>
                    <input
                        type="text"
                        id="endereco"
                        name="endereco"
                        class="fin-input"
                        value="<?= htmlspecialchars($dados['endereco'] ?? '') ?>"
                        style="background-color: #f1f5f9; font-weight: 500;"
                    >
                </div>
            </div>

            <!-- SEÇÃO 4: UNIDADE & PROFESSOR -->
            <div style="margin-bottom: 32px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                    🏢 Vínculo na Academia
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px;">

                    <div class="fin-form-group">
                        <label>Filial / Unidade *</label>
                        <select name="filial_id" class="fin-select" required>
                            <option value="">Selecione uma filial...</option>
                            <?php foreach ($filiais as $filial): ?>
                                <option
                                    value="<?= $filial['id'] ?>"
                                    <?= ($dados['filial_id'] ?? '') == $filial['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($filial['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fin-form-group">
                        <label>Professor Responsável</label>
                        <select name="professor_id" class="fin-select">
                            <option value="">Nenhum (Sem instrutor fixo)</option>
                            <?php foreach ($professores ?? [] as $professor): ?>
                                <option
                                    value="<?= $professor['id'] ?>"
                                    <?= ($dados['professor_id'] ?? '') == $professor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($professor['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>
            </div>

            <!-- AÇÕES DO FORMULÁRIO -->
            <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                <a href="<?= $baseUrl ?>?acao=listar" class="fin-btn fin-btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="fin-btn fin-btn-primary">
                    ✓ Salvar Alterações
                </button>
            </div>

        </form>

    </section>

</main>

<script>
    function toggleCondominio() {
        const isChecked = document.getElementById('is_condominio').checked;
        document.getElementById('div_condominio').style.display = isChecked ? 'grid' : 'none';
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

        document.getElementById('endereco').value = 'Buscando CEP...';

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

<?php include __DIR__ . '/../shared/footer.php'; ?>