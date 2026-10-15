<?php
$tituloPagina = "Editar Aluno";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

// $dados tem as informações atuais do aluno (vindas do controller).
// Aqui garantimos que todos os campos existem (vazios) para não dar aviso do PHP.
if (!isset($dados)) {
    $dados = [];
}

$campos = ['nome', 'cpf', 'rg', 'sexo', 'nascimento', 'status', 'email', 'telefone', 'endereco', 'filial_id', 'professor_id'];

foreach ($campos as $campo) {
    if (!isset($dados[$campo])) {
        $dados[$campo] = '';
    }
}

if (!isset($professores)) {
    $professores = [];
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jorge-financeiro.css">

<!-- Estilos só desta página -->
<style>
    .caixa-erro {
        background-color: #fee2e2;
        color: #b91c1c;
        padding: 14px 18px;
        border-radius: 8px;
        margin-bottom: 24px;
        font-weight: 600;
        border: 1px solid #fecaca;
    }

    .secao {
        margin-bottom: 28px;
    }

    .titulo-secao {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 16px;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 10px;
    }

    /* Grade que coloca os campos lado a lado e se ajusta ao tamanho da tela */
    .grade {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 18px;
    }

    .linha-cep {
        display: grid;
        grid-template-columns: 160px 1fr;
        gap: 18px;
        margin-bottom: 16px;
    }

    .linha-numero {
        display: grid;
        grid-template-columns: 120px 1fr 1fr;
        gap: 18px;
        margin-bottom: 16px;
    }

    .caixa-condominio {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        background-color: #f8fafc;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .caixa-condominio input {
        width: 18px;
        height: 18px;
        margin: 0 10px 0 0;
        cursor: pointer;
    }

    .caixa-condominio label {
        margin: 0;
        cursor: pointer;
        font-weight: 600;
        color: #334155;
    }

    /* Começa escondido. O JavaScript mostra quando marcar "É prédio / condomínio?" */
    #div_condominio {
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin-bottom: 16px;
    }

    .campo-endereco {
        background-color: #f1f5f9;
        font-weight: 500;
    }

    .botoes-form {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
    }
</style>

<main class="conteudo financeiro-page">

    <!-- Título e botões de navegação -->
    <div class="fin-page-header">
        <div class="fin-header-info">
            <h1>Editar Aluno #<?= $id ?></h1>
            <p>Atualize as informações cadastrais do aluno abaixo.</p>
        </div>
        <div class="fin-header-actions">
            <a href="<?= $baseUrl ?>?acao=visualizar&id=<?= $id ?>" class="fin-btn fin-btn-secondary">← Ver Cadastro</a>
            <a href="<?= $baseUrl ?>?acao=listar" class="fin-btn fin-btn-secondary">Voltar para Lista</a>
        </div>
    </div>

    <!-- Mensagem de erro (se tiver) -->
    <?php if (!empty($erro)): ?>
        <div class="caixa-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <section class="fin-table-card" style="padding: 28px;">

        <form action="<?= $baseUrl ?>?acao=editar&id=<?= $id ?>" method="POST">
            <!-- id escondido para o controller saber qual aluno atualizar -->
            <input type="hidden" name="id" value="<?= $id ?>">

            <!-- PARTE 1: DADOS PESSOAIS -->
            <div class="secao">
                <h3 class="titulo-secao">Dados Pessoais</h3>

                <div class="grade">
                    <div class="fin-form-group">
                        <label>Nome Completo *</label>
                        <input type="text" name="nome" class="fin-input" value="<?= htmlspecialchars($dados['nome']) ?>" required>
                    </div>

                    <div class="fin-form-group">
                        <label>CPF *</label>
                        <input type="text" name="cpf" class="fin-input" value="<?= htmlspecialchars($dados['cpf']) ?>" required>
                    </div>

                    <div class="fin-form-group">
                        <label>RG</label>
                        <input type="text" name="rg" class="fin-input" value="<?= htmlspecialchars($dados['rg']) ?>">
                    </div>

                    <div class="fin-form-group">
                        <label>Sexo *</label>
                        <select name="sexo" class="fin-select" required>
                            <option value="Masculino" <?php if ($dados['sexo'] == 'Masculino') { echo 'selected'; } ?>>Masculino</option>
                            <option value="Feminino" <?php if ($dados['sexo'] == 'Feminino') { echo 'selected'; } ?>>Feminino</option>
                            <option value="Outro" <?php if ($dados['sexo'] == 'Outro') { echo 'selected'; } ?>>Outro</option>
                        </select>
                    </div>

                    <div class="fin-form-group">
                        <label>Data de Nascimento *</label>
                        <input type="date" name="nascimento" class="fin-input" value="<?= htmlspecialchars($dados['nascimento']) ?>" required>
                    </div>

                    <div class="fin-form-group">
                        <label>Status da Conta *</label>
                        <select name="status" class="fin-select" required>
                            <option value="Ativo" <?php if ($dados['status'] == 'Ativo') { echo 'selected'; } ?>>Ativo</option>
                            <option value="Inativo" <?php if ($dados['status'] == 'Inativo') { echo 'selected'; } ?>>Inativo</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- PARTE 2: CONTATO E ACESSO -->
            <div class="secao">
                <h3 class="titulo-secao">Contato &amp; Acesso ao Portal</h3>

                <div class="grade">
                    <div class="fin-form-group">
                        <label>E-mail *</label>
                        <input type="email" name="email" class="fin-input" value="<?= htmlspecialchars($dados['email']) ?>" required>
                    </div>

                    <div class="fin-form-group">
                        <label>Telefone / WhatsApp *</label>
                        <input type="text" name="telefone" class="fin-input" value="<?= htmlspecialchars($dados['telefone']) ?>" required>
                    </div>

                    <div class="fin-form-group">
                        <!-- Se deixar vazio, a senha antiga continua valendo -->
                        <label>Nova Senha (deixe em branco para não alterar)</label>
                        <input type="password" name="senha" class="fin-input" placeholder="Mínimo 6 caracteres se alterar" minlength="6">
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
                        <input type="text" id="rua" class="fin-input" placeholder="Rua, Avenida..." oninput="atualizarEndereco()">
                    </div>
                </div>

                <div class="linha-numero">
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

                <div class="caixa-condominio">
                    <input type="checkbox" id="is_condominio" onchange="toggleCondominio()">
                    <label for="is_condominio">É prédio / condomínio?</label>
                </div>

                <div id="div_condominio" style="display: none;">
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
                    <input type="text" id="endereco" name="endereco" class="fin-input campo-endereco" value="<?= htmlspecialchars($dados['endereco']) ?>">
                </div>
            </div>

            <!-- PARTE 4: FILIAL E PROFESSOR -->
            <div class="secao" style="margin-bottom: 32px;">
                <h3 class="titulo-secao">Vínculo na Academia</h3>

                <div class="grade">
                    <div class="fin-form-group">
                        <label>Filial / Unidade *</label>
                        <select name="filial_id" class="fin-select" required>
                            <option value="">Selecione uma filial...</option>
                            <?php foreach ($filiais as $filial): ?>
                                <option value="<?= $filial['id'] ?>" <?php if ($dados['filial_id'] == $filial['id']) { echo 'selected'; } ?>>
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
                                <option value="<?= $professor['id'] ?>" <?php if ($dados['professor_id'] == $professor['id']) { echo 'selected'; } ?>>
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
                <button type="submit" class="fin-btn fin-btn-primary">Salvar Alterações</button>
            </div>

        </form>
    </section>

</main>

<script>
    // Mostra ou esconde os campos de bloco e apartamento
    function toggleCondominio() {
        var marcado = document.getElementById('is_condominio').checked;

        if (marcado) {
            document.getElementById('div_condominio').style.display = 'grid';
        } else {
            document.getElementById('div_condominio').style.display = 'none';
        }

        atualizarEndereco();
    }

    // Junta rua, número, bloco, apto, bairro e cidade no campo "Endereço Completo"
    // Exemplo: "Rua A, 10 - Bloco 2 - Apto 31 - Centro - São Paulo/SP"
    function atualizarEndereco() {
        var rua = document.getElementById('rua').value;
        var numero = document.getElementById('numero').value;
        var bairro = document.getElementById('bairro').value;
        var cidade = document.getElementById('cidade_uf').value;
        var condominio = document.getElementById('is_condominio').checked;
        var bloco = document.getElementById('bloco').value;
        var apto = document.getElementById('apto').value;

        // Lista com as partes do endereço que foram preenchidas
        var partes = [];

        if (rua != '') {
            if (numero != '') {
                partes.push(rua + ', ' + numero);
            } else {
                partes.push(rua);
            }
        }

        if (condominio) {
            if (bloco != '') {
                partes.push('Bloco ' + bloco);
            }
            if (apto != '') {
                partes.push('Apto ' + apto);
            }
        }

        if (bairro != '') {
            partes.push(bairro);
        }

        if (cidade != '') {
            partes.push(cidade);
        }

        // Só troca o endereço se tiver alguma coisa preenchida
        // (assim o endereço que já estava salvo não é apagado)
        if (partes.length > 0) {
            document.getElementById('endereco').value = partes.join(' - ');
        }
    }

    // Busca o endereço no site ViaCEP e preenche os campos sozinho
    function buscarCep(cep) {
        // Tira tudo que não for número (ex: "01001-000" vira "01001000")
        cep = cep.replace(/\D/g, '');

        // CEP precisa ter 8 números
        if (cep.length != 8) {
            return;
        }

        var campoEndereco = document.getElementById('endereco');
        campoEndereco.value = 'Buscando CEP...';

        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(function (resposta) {
                return resposta.json();
            })
            .then(function (resultado) {
                if (resultado.erro) {
                    alert('CEP não encontrado!');
                    campoEndereco.value = '';
                    return;
                }

                // O "|| ''" usa texto vazio caso o ViaCEP não mande aquela informação
                document.getElementById('rua').value = resultado.logradouro || '';
                document.getElementById('bairro').value = resultado.bairro || '';
                document.getElementById('cidade_uf').value = (resultado.localidade || '') + '/' + (resultado.uf || '');

                atualizarEndereco();
            })
            .catch(function () {
                alert('Erro ao buscar o CEP.');
                campoEndereco.value = '';
            });
    }
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>