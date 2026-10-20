<?php
// =============================================
// GYMFLOW - MINHA AVALIAÇÃO
// app/views/portal_aluno/avaliacao.php
// =============================================

if (!isset($dadosAlunoAvaliacao)) {
    header(
        "Location: " . BASE_URL .
            "/app/controllers/PortalAlunoController.php?acao=aluno"
    );
    exit;
}

// Token de segurança utilizado pelo FeedbackController.
if (
    empty($_SESSION['feedback_csrf_token']) ||
    !is_string($_SESSION['feedback_csrf_token'])
) {
    $_SESSION['feedback_csrf_token'] = bin2hex(random_bytes(32));
}

$tituloPagina = "Minha Avaliação";
$cssEspecifico = BASE_URL . '/assets/css/avaliacao-aluno.css';

$minhaAvaliacao = $minhaAvaliacao ?? null;
$possuiAvaliacao = is_array($minhaAvaliacao);

$notaAtual = $possuiAvaliacao
    ? max(1, min(5, (int) ($minhaAvaliacao['nota'] ?? 0)))
    : 0;

$mensagemAtual = $possuiAvaliacao
    ? (string) ($minhaAvaliacao['mensagem'] ?? '')
    : '';

$publicada = $possuiAvaliacao &&
    (int) ($minhaAvaliacao['ativo'] ?? 0) === 1;

$nomeFilial = $nomeFilial ?? 'Sua academia';

$status = $_GET['avaliacao'] ?? '';

$mensagemSistema = '';
$tipoMensagem = '';

switch ($status) {
    case 'sucesso':
        $mensagemSistema =
            'Sua avaliação foi salva com sucesso! Ela está aguardando aprovação para aparecer no site.';
        $tipoMensagem = 'sucesso';
        break;

    case 'dados_invalidos':
        $mensagemSistema =
            'Selecione uma nota de 1 a 5 estrelas e escreva um comentário válido.';
        $tipoMensagem = 'erro';
        break;

    case 'erro':
        $mensagemSistema =
            'Não foi possível salvar sua avaliação. Tente novamente.';
        $tipoMensagem = 'erro';
        break;
}

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main class="portal-aluno avaliacao-aluno-page">

    <header class="portal-header avaliacao-cabecalho">
        <div>
            <span class="portal-eyebrow">PORTAL DO ALUNO</span>
            <h1>Minha Avaliação</h1>
            <p>
                Sua opinião nos ajuda a melhorar
                a experiência de todos.
            </p>
        </div>

        <a
            href="<?= BASE_URL ?>/app/controllers/PortalAlunoController.php?acao=aluno"
            class="avaliacao-voltar">
            ← Voltar ao portal
        </a>
    </header>

    <?php if ($mensagemSistema !== ''): ?>
        <div
            role="<?= $tipoMensagem === 'erro' ? 'alert' : 'status' ?>"
            style="
                padding: 15px 18px;
                margin-bottom: 22px;
                border-radius: 12px;
                border: 1px solid <?= $tipoMensagem === 'sucesso'
                                        ? '#bbf7d0'
                                        : '#fecaca' ?>;
                background: <?= $tipoMensagem === 'sucesso'
                                ? '#f0fdf4'
                                : '#fef2f2' ?>;
                color: <?= $tipoMensagem === 'sucesso'
                            ? '#166534'
                            : '#991b1b' ?>;
                font-size: 14px;
                font-weight: 600;
            ">
            <?= htmlspecialchars(
                $mensagemSistema,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>
    <?php endif; ?>

    <section class="avaliacao-conteudo">

        <div class="avaliacao-principal">

            <article class="portal-card avaliacao-card">

                <div class="avaliacao-card-topo">
                    <div>
                        <span class="card-label">
                            SUA EXPERIÊNCIA
                        </span>

                        <h2>
                            <?= $possuiAvaliacao
                                ? 'Sua avaliação'
                                : 'Avalie sua academia' ?>
                        </h2>

                        <p>
                            <?= $possuiAvaliacao
                                ? 'Confira ou atualize sua avaliação.'
                                : 'Conte o que você está achando da academia.' ?>
                        </p>
                    </div>

                    <?php if ($possuiAvaliacao): ?>
                        <span class="avaliacao-status <?= $publicada
                                                            ? 'avaliacao-status-publicada'
                                                            : 'avaliacao-status-pendente' ?>">
                            <?= $publicada
                                ? 'Publicada'
                                : 'Aguardando aprovação' ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="avaliacao-unidade">
                    <span class="avaliacao-unidade-icone">
                        ⌂
                    </span>

                    <div>
                        <span>Sua unidade</span>
                        <strong>
                            <?= htmlspecialchars(
                                $nomeFilial,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>
                    </div>
                </div>

                <form
                    id="formMinhaAvaliacao"
                    class="avaliacao-formulario"
                    method="POST"
                    action="<?= BASE_URL ?>/app/controllers/FeedbackController.php">
                    <!-- O controller compartilha o processamento
                         entre portal e site público. -->
                    <input
                        type="hidden"
                        name="origem"
                        value="portal">

                    <!-- Proteção CSRF -->
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                                    $_SESSION['feedback_csrf_token'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>">

                    <!-- NOTA -->
                    <div class="avaliacao-campo">
                        <label
                            class="avaliacao-label"
                            id="rotuloNota">
                            Como você avalia sua experiência?
                        </label>

                        <p class="avaliacao-ajuda">
                            Selecione de 1 a 5 estrelas.
                        </p>

                        <div
                            class="avaliacao-estrelas"
                            role="group"
                            aria-labelledby="rotuloNota">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button
                                    type="button"
                                    class="avaliacao-estrela <?= $i <= $notaAtual
                                                                    ? 'selecionada'
                                                                    : '' ?>"
                                    data-nota="<?= $i ?>"
                                    aria-label="<?= $i ?> <?= $i === 1
                                                                ? 'estrela'
                                                                : 'estrelas' ?>"
                                    aria-pressed="<?= $i === $notaAtual
                                                        ? 'true'
                                                        : 'false' ?>">
                                    ★
                                </button>
                            <?php endfor; ?>
                        </div>

                        <input
                            type="hidden"
                            name="nota"
                            id="avaliacaoNota"
                            value="<?= $notaAtual ?>">

                        <span
                            class="avaliacao-nota-texto"
                            id="avaliacaoNotaTexto"
                            aria-live="polite">
                            <?= $notaAtual > 0
                                ? $notaAtual . ' de 5 estrelas'
                                : 'Nenhuma nota selecionada' ?>
                        </span>
                    </div>

                    <!-- COMENTÁRIO -->
                    <div class="avaliacao-campo">
                        <label
                            class="avaliacao-label"
                            for="avaliacaoMensagem">
                            Seu comentário
                        </label>

                        <textarea
                            id="avaliacaoMensagem"
                            name="mensagem"
                            rows="6"
                            maxlength="1000"
                            required
                            placeholder="Conte como está sendo sua experiência com a estrutura, o atendimento e os treinos..."><?= htmlspecialchars(
                                                                                                                                    $mensagemAtual,
                                                                                                                                    ENT_QUOTES,
                                                                                                                                    'UTF-8'
                                                                                                                                ) ?></textarea>

                        <div class="avaliacao-contador">
                            <span>
                                Compartilhe uma opinião sincera e respeitosa.
                            </span>

                            <span id="avaliacaoCaracteres">
                                0/1000
                            </span>
                        </div>
                    </div>

                    <!-- MODERAÇÃO -->
                    <div class="avaliacao-aviso">
                        <span class="avaliacao-aviso-icone">
                            i
                        </span>

                        <div>
                            <strong>
                                Publicação moderada
                            </strong>

                            <p>
                                Sua avaliação será analisada
                                antes de aparecer no site público.
                                Se você atualizar seu comentário,
                                ele voltará para aprovação.
                            </p>
                        </div>
                    </div>

                    <!-- ENVIO -->
                    <div class="avaliacao-acoes">
                        <button
                            type="submit"
                            class="portal-btn avaliacao-btn-salvar"
                            id="avaliacaoBotaoSalvar">
                            <?= $possuiAvaliacao
                                ? 'Atualizar avaliação'
                                : 'Enviar avaliação' ?>
                            <span>→</span>
                        </button>
                    </div>

                </form>

            </article>

        </div>

        <!-- INFORMAÇÕES LATERAIS -->
        <aside class="avaliacao-lateral">

            <article class="portal-card avaliacao-info-card">
                <div class="avaliacao-info-icone">
                    ★
                </div>

                <span class="card-label">
                    SUA OPINIÃO IMPORTA
                </span>

                <h3>
                    Ajude a construir uma academia melhor.
                </h3>

                <p>
                    Seu feedback nos ajuda a identificar
                    pontos positivos e oportunidades
                    de melhoria.
                </p>
            </article>

            <?php if ($possuiAvaliacao): ?>

                <article class="portal-card avaliacao-resumo-card">

                    <span class="card-label">
                        HISTÓRICO DA AVALIAÇÃO
                    </span>

                    <div class="avaliacao-resumo-linha">
                        <span>Nota registrada</span>
                        <strong><?= $notaAtual ?>/5</strong>
                    </div>

                    <?php if (!empty($minhaAvaliacao['criado_em'])): ?>
                        <div class="avaliacao-resumo-linha">
                            <span>Enviada em</span>
                            <strong>
                                <?= date(
                                    'd/m/Y',
                                    strtotime(
                                        $minhaAvaliacao['criado_em']
                                    )
                                ) ?>
                            </strong>
                        </div>
                    <?php endif; ?>

                    <div class="avaliacao-resumo-linha">
                        <span>Publicação</span>
                        <strong>
                            <?= $publicada
                                ? 'Publicada'
                                : 'Pendente' ?>
                        </strong>
                    </div>

                </article>

            <?php endif; ?>

        </aside>
    </section>

</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formulario = document.getElementById('formMinhaAvaliacao');
        const estrelas = document.querySelectorAll('.avaliacao-estrela');
        const notaInput = document.getElementById('avaliacaoNota');
        const notaTexto = document.getElementById('avaliacaoNotaTexto');
        const textarea = document.getElementById('avaliacaoMensagem');
        const contador = document.getElementById('avaliacaoCaracteres');
        const botaoSalvar = document.getElementById('avaliacaoBotaoSalvar');

        function atualizarEstrelas(nota) {
            estrelas.forEach(function(estrela) {
                const valor = Number(estrela.dataset.nota);

                estrela.classList.toggle(
                    'selecionada',
                    valor <= nota
                );

                estrela.setAttribute(
                    'aria-pressed',
                    String(valor === nota)
                );
            });

            notaInput.value = nota;

            notaTexto.textContent = nota > 0 ?
                nota + ' de 5 estrelas' :
                'Nenhuma nota selecionada';
        }

        estrelas.forEach(function(estrela) {
            estrela.addEventListener('click', function() {
                atualizarEstrelas(Number(this.dataset.nota));
            });
        });

        function atualizarContador() {
            contador.textContent =
                textarea.value.length + '/1000';
        }

        textarea.addEventListener('input', atualizarContador);

        formulario.addEventListener('submit', function(event) {
            const nota = Number(notaInput.value);
            const mensagem = textarea.value.trim();

            if (nota < 1 || nota > 5) {
                event.preventDefault();
                alert('Selecione uma nota de 1 a 5 estrelas.');
                return;
            }

            if (mensagem.length === 0 || mensagem.length > 1000) {
                event.preventDefault();
                alert('Escreva um comentário de até 1000 caracteres.');
                textarea.focus();
                return;
            }

            botaoSalvar.disabled = true;
            botaoSalvar.setAttribute('aria-busy', 'true');
        });

        atualizarEstrelas(Number(notaInput.value) || 0);
        atualizarContador();
    });
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>