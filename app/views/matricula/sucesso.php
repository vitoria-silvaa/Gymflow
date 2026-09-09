<?php
// app/views/matricula/sucesso.php

/** @var array $resultado */

$tituloPagina = "Matrícula Realizada com Sucesso!";
include __DIR__ . '/../shared/portfolio_header.php';
?>

<main>
    <section class="portfolio-modalidades">
        <div class="modalidades-cabecalho">
            <span class="secao-destaque">Parabéns!</span>
            <h2>Matrícula Realizada com Sucesso!</h2>
            <p>Seja bem-vindo(a) à Gymflow. Seus dados foram cadastrados e seu acesso está pronto.</p>
        </div>

        <div class="modalidades-grid">
            <article class="modalidade-card">
                <div class="modalidade-conteudo">
                    <h3>Dados da Matrícula</h3>
                    <p><strong>Número da Matrícula:</strong> #<?= (int) ($resultado['matricula_id'] ?? 0) ?></p>
                    <p><strong>Aluno:</strong> <?= htmlspecialchars($resultado['aluno_nome'] ?? '') ?></p>
                    <p><strong>E-mail de Acesso:</strong> <?= htmlspecialchars($resultado['aluno_email'] ?? '') ?></p>
                    <p><strong>Plano Contratado:</strong> <?= htmlspecialchars($resultado['plano_nome'] ?? '') ?></p>
                    <p><strong>Valor:</strong> R$ <?= number_format((float) ($resultado['plano_valor'] ?? 0), 2, ',', '.') ?></p>
                    <p><strong>Forma de Pagamento:</strong> <?= strtoupper(htmlspecialchars($resultado['metodo'] ?? 'PIX')) ?></p>
                    <p><strong>Status do Pagamento:</strong> 
                        <?= (($resultado['status'] ?? '') === 'aprovado') ? '✓ Aprovado e Confirmado' : '⏳ Aguardando Confirmação' ?>
                    </p>
                    <br>

                    <?php if (!empty($resultado['pix_copia_cola'])): ?>
                        <div class="mensagem-vazia">
                            <h4>Código PIX Copia e Cola</h4>
                            <p>Utilize o código abaixo no seu aplicativo de banco para concluir o pagamento:</p>
                            <textarea readonly rows="3" style="width: 100%; font-size: 11px;"><?= htmlspecialchars($resultado['pix_copia_cola']) ?></textarea>
                        </div>
                        <br>
                    <?php endif; ?>

                    <div class="mensagem-vazia">
                        <p><strong>Próximo passo:</strong> Acesse o Portal do Aluno com seu e-mail e a senha que você acabou de criar para visualizar seus treinos, horários e histórico financeiro.</p>
                    </div>
                    <br><br>

                    <div class="hero-acoes">
                        <a href="/Gymflow/app/controllers/LoginController.php?acao=login" class="hero-btn hero-btn-principal">Entrar no Portal do Aluno →</a>
                        <a href="/Gymflow/index.php" class="hero-btn hero-btn-secundario">Voltar para a Página Inicial</a>
                    </div>
                </div>
            </article>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
