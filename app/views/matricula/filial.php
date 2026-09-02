<?php
// app/views/matricula/filial.php

/** @var array $filiais */
/** @var array $plano */
/** @var array $matriculaTemp */
/** @var string $erro */

$tituloPagina = "Matrícula Online - Escolha da Filial";
include __DIR__ . '/../shared/portfolio_header.php';
?>

<main>
    <section class="portfolio-modalidades">
        <div class="modalidades-cabecalho">
            <span class="secao-destaque">Etapa 2 de 3</span>
            <h2>Escolha sua Filial</h2>
            <p>Selecione a unidade onde você realizará seus treinos.</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="mensagem-vazia">
                <p><strong>Atenção:</strong> <?= htmlspecialchars($erro) ?></p>
            </div>
            <br>
        <?php endif; ?>

        <form action="/Gymflow/app/controllers/MatriculaController.php?acao=filial" method="POST">
            <div class="modalidades-grid">
                <?php if (empty($filiais)): ?>
                    <article class="modalidade-card">
                        <div class="modalidade-conteudo">
                            <p class="mensagem-vazia">Nenhuma unidade disponível no momento.</p>
                        </div>
                    </article>
                <?php else: ?>
                    <?php foreach ($filiais as $f): ?>
                        <article class="modalidade-card">
                            <div class="modalidade-conteudo">
                                <h3><?= htmlspecialchars($f['nome']) ?></h3>
                                <p><strong>Telefone:</strong> <?= htmlspecialchars($f['telefone'] ?? 'Não informado') ?></p>
                                <p><strong>Responsável:</strong> <?= htmlspecialchars($f['responsavel'] ?? 'Coordenação') ?></p>
                                <p><strong>CNPJ:</strong> <?= htmlspecialchars($f['cnpj'] ?? '') ?></p>
                                <br>
                                <label>
                                    <input 
                                        type="radio" 
                                        name="filial_id" 
                                        value="<?= (int) $f['id'] ?>" 
                                        <?= ((int) ($matriculaTemp['filial_id'] ?? 0) === (int) $f['id']) ? 'checked' : '' ?> 
                                        required
                                    >
                                    Treinar nesta unidade
                                </label>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Card de Resumo -->
                <article class="modalidade-card">
                    <div class="modalidade-conteudo">
                        <span class="secao-destaque">Sua Escolha</span>
                        <h3><?= htmlspecialchars($plano['nome'] ?? 'Plano') ?></h3>
                        <p><strong>Aluno:</strong> <?= htmlspecialchars($matriculaTemp['nome'] ?? '') ?></p>
                        <p><strong>E-mail:</strong> <?= htmlspecialchars($matriculaTemp['email'] ?? '') ?></p>
                        <p><strong>Valor:</strong> R$ <?= number_format((float) ($plano['valor'] ?? 0), 2, ',', '.') ?></p>
                        <p><strong>Duração:</strong> <?= htmlspecialchars($plano['duracao'] ?? '') ?></p>
                    </div>
                </article>
            </div>

            <br><br>
            <div class="hero-acoes">
                <a href="/Gymflow/app/controllers/MatriculaController.php?acao=identificacao" class="hero-btn hero-btn-secundario">← Voltar para Identificação</a>
                <button type="submit" class="hero-btn hero-btn-principal">Continuar para Pagamento →</button>
            </div>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
