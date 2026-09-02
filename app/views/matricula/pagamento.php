<?php
// app/views/matricula/pagamento.php

/** @var array $plano */
/** @var array $filial */
/** @var array $matriculaTemp */
/** @var string $publicKeyMP */
/** @var bool $isConfiguredMP */
/** @var string $erro */

$tituloPagina = "Matrícula Online - Pagamento";
include __DIR__ . '/../shared/portfolio_header.php';
?>

<main>
    <section class="portfolio-modalidades">
        <div class="modalidades-cabecalho">
            <span class="secao-destaque">Etapa 3 de 3</span>
            <h2>Forma de Pagamento</h2>
            <p>Revise os detalhes da sua matrícula e escolha como deseja realizar o pagamento seguro.</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="mensagem-vazia">
                <p><strong>Atenção:</strong> <?= htmlspecialchars($erro) ?></p>
            </div>
            <br>
        <?php endif; ?>

        <div class="modalidades-grid">
            <!-- Coluna 1 e 2: Opções de Pagamento -->
            <article class="modalidade-card">
                <div class="modalidade-conteudo">
                    <h3>Escolha o Método de Pagamento</h3>
                    <p>Processamento protegido e seguro via <strong>Mercado Pago</strong>.</p>
                    <br>

                    <form action="/Gymflow/app/controllers/MatriculaController.php?acao=pagamento" method="POST" id="form-pagamento">
                        <label>Método de Pagamento *:</label>
                        <select name="metodo" id="metodo_pagamento" required>
                            <option value="pix" selected>PIX (Aprovação Instantânea)</option>
                            <option value="cartao">Cartão de Crédito (Até 12x)</option>
                        </select>
                        <br><br>

                        <!-- Painel Informativo PIX -->
                        <div id="painel-pix" class="mensagem-vazia">
                            <h4>Pagamento via PIX</h4>
                            <p>Ao confirmar, o QR Code e o código Pix Copia e Cola serão gerados para você efetuar o pagamento diretamente no app do seu banco.</p>
                            <small>A liberação da matrícula ocorre automaticamente após a confirmação bancária.</small>
                        </div>

                        <!-- Painel Informativo / Container Mercado Pago Cartão -->
                        <div id="painel-cartao" class="mensagem-vazia" style="display: none;">
                            <h4>Pagamento com Cartão de Crédito</h4>
                            <p>Área preparada para o <strong>Mercado Pago Payment Brick</strong>.</p>
                            <small>Nenhum dado sensível do cartão é processado ou armazenado pela Gymflow.</small>
                            <br><br>

                            <div id="paymentBrick_container">
                                <p>🔒 Ambiente de pagamento criptografado pelo Mercado Pago.</p>
                            </div>

                            <label for="parcelas">Número de Parcelas:</label>
                            <select name="parcelas" id="parcelas">
                                <option value="1">1x de R$ <?= number_format((float) $plano['valor'], 2, ',', '.') ?> (sem juros)</option>
                                <option value="2">2x de R$ <?= number_format((float) ($plano['valor'] / 2), 2, ',', '.') ?></option>
                                <option value="3">3x de R$ <?= number_format((float) ($plano['valor'] / 3), 2, ',', '.') ?></option>
                            </select>
                        </div>
                        <br><br>

                        <div class="hero-acoes">
                            <a href="/Gymflow/app/controllers/MatriculaController.php?acao=filial" class="hero-btn hero-btn-secundario">← Voltar para Filial</a>
                            <button type="submit" class="hero-btn hero-btn-principal">Confirmar e Pagar R$ <?= number_format((float) $plano['valor'], 2, ',', '.') ?></button>
                        </div>
                    </form>
                </div>
            </article>

            <!-- Coluna 3: Resumo Completo da Matrícula -->
            <article class="modalidade-card">
                <div class="modalidade-conteudo">
                    <span class="secao-destaque">Resumo da Contratação</span>
                    <h3><?= htmlspecialchars($plano['nome']) ?></h3>
                    <p><strong>Valor:</strong> R$ <?= number_format((float) $plano['valor'], 2, ',', '.') ?></p>
                    <p><strong>Duração:</strong> <?= htmlspecialchars($plano['duracao']) ?></p>
                    <p><strong>Categoria:</strong> <?= htmlspecialchars($plano['categoria']) ?></p>
                    <hr>
                    <p><strong>Aluno:</strong> <?= htmlspecialchars($matriculaTemp['nome']) ?></p>
                    <p><strong>CPF:</strong> <?= htmlspecialchars($matriculaTemp['cpf']) ?></p>
                    <p><strong>E-mail:</strong> <?= htmlspecialchars($matriculaTemp['email']) ?></p>
                    <p><strong>Unidade:</strong> <?= htmlspecialchars($filial['nome']) ?></p>
                    <p><strong>Telefone da Unidade:</strong> <?= htmlspecialchars($filial['telefone'] ?? '') ?></p>
                </div>
            </article>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectMetodo = document.getElementById('metodo_pagamento');
    const painelPix    = document.getElementById('painel-pix');
    const painelCartao = document.getElementById('painel-cartao');

    if (selectMetodo && painelPix && painelCartao) {
        selectMetodo.addEventListener('change', function () {
            if (this.value === 'cartao') {
                painelPix.style.display = 'none';
                painelCartao.style.display = 'block';
            } else {
                painelPix.style.display = 'block';
                painelCartao.style.display = 'none';
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>
