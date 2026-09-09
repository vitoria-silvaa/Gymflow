<?php
// app/views/matricula/identificacao.php

/** @var array|null $planoSelecionado */
/** @var array $planos */
/** @var array $dados */
/** @var string $erro */

$tituloPagina = "Matrícula Online - Identificação";
include __DIR__ . '/../shared/portfolio_header.php';
?>

<main>
    <section class="portfolio-modalidades">
        <div class="modalidades-cabecalho">
            <span class="secao-destaque">Etapa 1 de 3</span>
            <h2>Identificação do Aluno</h2>
            <p>Preencha seus dados cadastrais para iniciar sua matrícula na Gymflow.</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="mensagem-vazia">
                <p><strong>Atenção:</strong> <?= htmlspecialchars($erro) ?></p>
            </div>
            <br>
        <?php endif; ?>

        <div class="modalidades-grid">
            <!-- Coluna 1 e 2: Formulário Cadastral -->
            <article class="modalidade-card">
                <div class="modalidade-conteudo">
                    <h3>Suas Informações</h3>
                    <p>Informe seus dados pessoais e crie sua senha de acesso ao Portal do Aluno.</p>
                    <br>

                    <form action="/Gymflow/app/controllers/MatriculaController.php?acao=identificacao" method="POST">
                        <label for="plano_id">Plano Selecionado *:</label>
                        <select id="plano_id" name="plano_id" required>
                            <option value="">Selecione um plano</option>
                            <?php foreach ($planos as $p): ?>
                                <option value="<?= (int) $p['id'] ?>" <?= ((int) ($planoSelecionado['id'] ?? $dados['plano_id'] ?? 0) === (int) $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nome']) ?> — R$ <?= number_format((float) $p['valor'], 2, ',', '.') ?> (<?= htmlspecialchars($p['duracao']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <br><br>

                        <label for="nome">Nome Completo *:</label>
                        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($dados['nome'] ?? '') ?>" placeholder="Seu nome completo" required>
                        <br><br>

                        <label for="cpf">CPF *:</label>
                        <input type="text" id="cpf" name="cpf" value="<?= htmlspecialchars($dados['cpf'] ?? '') ?>" placeholder="000.000.000-00" required>
                        <br><br>

                        <label for="nascimento">Data de Nascimento *:</label>
                        <input type="date" id="nascimento" name="nascimento" value="<?= htmlspecialchars($dados['nascimento'] ?? '') ?>" required>
                        <br><br>

                        <label for="sexo">Sexo *:</label>
                        <select id="sexo" name="sexo" required>
                            <option value="">Selecione</option>
                            <option value="Masculino" <?= (($dados['sexo'] ?? '') === 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                            <option value="Feminino" <?= (($dados['sexo'] ?? '') === 'Feminino') ? 'selected' : '' ?>>Feminino</option>
                            <option value="Outro" <?= (($dados['sexo'] ?? '') === 'Outro') ? 'selected' : '' ?>>Outro</option>
                        </select>
                        <br><br>

                        <label for="email">E-mail *:</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($dados['email'] ?? '') ?>" placeholder="seu.email@exemplo.com" required>
                        <br><br>

                        <label for="telefone">Celular / WhatsApp *:</label>
                        <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($dados['telefone'] ?? '') ?>" placeholder="(11) 99999-9999" required>
                        <br><br>

                        <label for="senha">Senha para o Portal do Aluno *:</label>
                        <input type="password" id="senha" name="senha" minlength="6" placeholder="Mínimo 6 caracteres" required>
                        <small>Esta senha será usada para você acessar seus treinos e faturas no portal.</small>
                        <br><br>

                        <label for="rg">RG (Opcional):</label>
                        <input type="text" id="rg" name="rg" value="<?= htmlspecialchars($dados['rg'] ?? '') ?>" placeholder="Número do RG">
                        <br><br>

                        <label for="endereco">Endereço (Opcional):</label>
                        <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($dados['endereco'] ?? '') ?>" placeholder="Rua, número, bairro, cidade">
                        <br><br>

                        <div class="hero-acoes">
                            <a href="/Gymflow/index.php" class="hero-btn hero-btn-secundario">← Voltar ao Portfólio</a>
                            <button type="submit" class="hero-btn hero-btn-principal">Continuar para Filial →</button>
                        </div>
                    </form>
                </div>
            </article>

            <!-- Coluna 3: Resumo do Plano -->
            <article class="modalidade-card">
                <div class="modalidade-conteudo">
                    <span class="secao-destaque">Resumo do Plano</span>
                    <?php if ($planoSelecionado): ?>
                        <h3><?= htmlspecialchars($planoSelecionado['nome']) ?></h3>
                        <p><strong>Categoria:</strong> <?= htmlspecialchars($planoSelecionado['categoria']) ?></p>
                        <p><strong>Duração:</strong> <?= htmlspecialchars($planoSelecionado['duracao']) ?></p>
                        <p><strong>Valor:</strong> R$ <?= number_format((float) $planoSelecionado['valor'], 2, ',', '.') ?></p>
                        <br>
                        <div class="mensagem-vazia">
                            <p>✓ Sem taxa de matrícula</p>
                            <p>✓ Acesso a equipamentos modernos</p>
                            <p>✓ Portal do Aluno com ficha de treinos</p>
                        </div>
                    <?php else: ?>
                        <h3>Escolha um plano</h3>
                        <p>Selecione um plano no formulário ao lado para visualizar os detalhes e valores.</p>
                    <?php endif; ?>
                </div>
            </article>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
