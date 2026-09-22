<?php
// index.php - Portfólio Público (View)

require_once __DIR__ . '/config/sessao.php';

$company_id = 1;
$operacao = 'buscar_publico';
require_once __DIR__ . '/app/models/Portfolio.php';

// Controle de acesso ao formulário de feedback
$alunoLogado =
    isset($_SESSION['usuario_id']) &&
    ($_SESSION['usuario_role'] ?? '') === 'Aluno';

$nomeAlunoLogado = $alunoLogado
    ? trim($_SESSION['usuario_nome'] ?? '')
    : '';

$tituloPagina = $config['app_name'] ?? "GymCore";
include __DIR__ . '/app/views/shared/portfolio_header.php';
?>

<main>
    <section id="inicio" class="portfolio-hero">

        <!-- IMAGENS DO CARROSSEL -->
        <div class="hero-carrossel">
            <?php if (!empty($slides)): ?>
                <?php foreach ($slides as $index => $slide): ?>
                    <div class="hero-slide <?= $index === 0 ? 'ativo' : '' ?>">
                        <img
                            src="<?= htmlspecialchars($slide['image_url'] ?? '') ?>"
                            alt="Imagem do portfólio"
                        >
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- caso o Admin ainda não tenha cadastrado imagens -->
                <div class="hero-slide ativo hero-slide-vazio"></div>
            <?php endif; ?>
        </div>

        <!-- escurecimento da imagem carrossel -->
        <div class="hero-overlay"></div>

        <!-- conteudo -->
        <div class="hero-conteudo">
            <h1>
                <?= htmlspecialchars($config['hero_title'] ?? '') ?>
            </h1>

            <p class="hero-subtitulo">
                <?= htmlspecialchars($config['hero_subtitle'] ?? '') ?>
            </p>

            <div class="hero-acoes">
                <a class="hero-btn hero-btn-principal" href="#planos">
                    <?= htmlspecialchars($config['hero_cta'] ?? 'Matricule-se') ?>
                </a>

                <a class="hero-btn hero-btn-secundario" href="#unidades">
                    Encontrar unidade
                </a>
            </div>
        </div>

        <?php if (!empty($slides) && count($slides) > 1): ?>
            <!-- setas -->
            <button
                type="button"
                class="hero-seta hero-seta-anterior"
                aria-label="Imagem anterior"
            >
                &#10094;
            </button>

            <button
                type="button"
                class="hero-seta hero-seta-proxima"
                aria-label="Próxima imagem"
            >
                &#10095;
            </button>

            <!-- bolinhas -->
            <div class="hero-indicadores">
                <?php foreach ($slides as $index => $slide): ?>
                    <button
                        type="button"
                        class="hero-indicador <?= $index === 0 ? 'ativo' : '' ?>"
                        data-slide="<?= $index ?>"
                        aria-label="Ir para imagem <?= $index + 1 ?>"
                    ></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </section>

    <!-- modalidades -->
    <section id="modalidades" class="portfolio-modalidades">
        <div class="modalidades-cabecalho">
            <span class="secao-destaque">
                Nossas Modalidades
            </span>

            <h2>
                Tudo o que você precisa em um só lugar
            </h2>

            <p>
                Encontre a modalidade ideal para você
            </p>
        </div>

        <?php if (empty($modalidades)): ?>
            <p class="mensagem-vazia">
                Nenhuma modalidade cadastrada no momento.
            </p>
        <?php else: ?>
            <div class="modalidades-grid">
                <?php foreach ($modalidades as $mod): ?>
                    <article class="modalidade-card">
                        <?php if (!empty($mod['image_url'])): ?>
                            <div class="modalidade-imagem">
                                <img
                                    src="<?= htmlspecialchars($mod['image_url']) ?>"
                                    alt="<?= htmlspecialchars($mod['name'] ?? '') ?>"
                                >
                            </div>
                        <?php endif; ?>

                        <div class="modalidade-conteudo">
                            <h3>
                                <?= htmlspecialchars($mod['name'] ?? '') ?>
                            </h3>

                            <p>
                                <?= htmlspecialchars($mod['description'] ?? '') ?>
                            </p>
                            <a href="#" class="modalidade-saiba-mais">
                                Saiba mais →
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

   
    <!-- planos -->
<section id="planos" class="portfolio-planos">

    <div class="planos-cabecalho">

        <span class="secao-destaque">
            Nossos Planos
        </span>

        <h2>
            Escolha o plano ideal para sua evolução
        </h2>

        <p>
            Encontre a opção que mais combina com seus objetivos.
        </p>

    </div>

    <?php if (empty($planos)): ?>

        <p class="mensagem-vazia">
            Nenhum plano disponível no momento.
        </p>

    <?php else: ?>

        <div class="planos-grid">

            <?php foreach ($planos as $plano): ?>

                <article class="plano-card">
                    <?php if (($plano['id'] ?? 0) == ($planos[1]['id'] ?? 0)): ?>
                    <span class="plano-destaque">Mais escolhido</span>
                    <?php endif; ?>

                    <span class="plano-categoria">
                        <?= htmlspecialchars($plano['categoria'] ?? '') ?>
                    </span>

                    <h3>
                        <?= htmlspecialchars($plano['nome'] ?? '') ?>
                    </h3>

                    <div class="plano-preco">
                        <span>R$</span>

                        <strong>
                            <?= number_format(
                                (float)($plano['valor'] ?? 0),
                                2,
                                ',',
                                '.'
                            ) ?>
                        </strong>
                    </div>

                    <p class="plano-duracao">
                        <?= htmlspecialchars($plano['duracao'] ?? '') ?>
                    </p>

                    <div class="plano-separador"></div>

                    <a
                        class="plano-btn"
                        href="/Gymflow/app/controllers/MatriculaController.php?plano_id=<?= (int)($plano['id'] ?? 0) ?>"
                    >
                        Matricule-se agora
                    </a>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <!-- parceiros dos planos -->
    <?php
        $aceitaWellhub = !empty($config['accepts_wellhub']);
        $aceitaTotalpass = !empty($config['accepts_totalpass']);
    ?>

    <?php if ($aceitaWellhub || $aceitaTotalpass): ?>
        <div class="portfolio-parceiros" aria-labelledby="parceiros-titulo">
            <div class="parceiros-conteudo">
                <div class="parceiros-texto">
                    <span class="secao-destaque">Benefícios</span>
                    <h2 id="parceiros-titulo">Nossas unidades aceitam</h2>
                    <p>Use seu benefício fitness e treine com a gente.</p>
                </div>

                <div class="parceiros-marcas">
                    <?php if ($aceitaWellhub): ?>
                        <div class="parceiro-marca">
                            <?php if (!empty($config['wellhub_icon'])): ?>
                                <span class="parceiro-icone parceiro-icone-imagem">
                                    <img
                                        src="<?= htmlspecialchars($config['wellhub_icon']) ?>"
                                        alt="Wellhub"
                                        style="width: 100%; height: 100%; object-fit: contain;"
                                    >
                                </span>
                            <?php else: ?>
                                <span class="parceiro-icone parceiro-icone-wellhub" aria-hidden="true">✦</span>
                            <?php endif; ?>

                            <div>
                                <strong>Wellhub</strong>
                                <small>(Gympass)</small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($aceitaWellhub && $aceitaTotalpass): ?>
                        <span class="parceiro-divisor" aria-hidden="true"></span>
                    <?php endif; ?>

                    <?php if ($aceitaTotalpass): ?>
                        <div class="parceiro-marca">
                            <?php if (!empty($config['totalpass_icon'])): ?>
                                <span class="parceiro-icone parceiro-icone-imagem">
                                    <img
                                        src="<?= htmlspecialchars($config['totalpass_icon']) ?>"
                                        alt="TotalPass"
                                        style="width: 100%; height: 100%; object-fit: contain;"
                                    >
                                </span>
                            <?php else: ?>
                                <span class="parceiro-icone parceiro-icone-totalpass" aria-hidden="true">TP</span>
                            <?php endif; ?>

                            <div>
                                <strong>TotalPass</strong>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

</section>

    <!-- unidades -->
<section id="unidades" class="portfolio-unidades">

    <div class="unidades-cabecalho">
        <span class="secao-destaque">Nossas Unidades</span>

        <h2>
            Conheça nossas unidades
        </h2>

        <p>
            Encontre a unidade ideal para treinar com conforto, estrutura e qualidade.
        </p>
    </div>

    <?php if (empty($filiais)): ?>

        <p class="mensagem-vazia">
            Nenhuma unidade cadastrada no momento.
        </p>

    <?php else: ?>

        <div class="unidades-grid">

            <?php foreach ($filiais as $filial): ?>

                <article class="unidade-card">

                    <div class="unidade-imagem">
                        <?php if (!empty($filial['image_url'])): ?>
                            <img
                                src="<?= htmlspecialchars($filial['image_url']) ?>"
                                alt="<?= htmlspecialchars($filial['nome'] ?? 'Unidade') ?>"
                                style="width: 100%; height: 100%; object-fit: cover; display: block;"
                            >
                        <?php endif; ?>
                    </div>

                    <div class="unidade-conteudo">

                        <h3>
                            <?= htmlspecialchars($filial['nome'] ?? '') ?>
                        </h3>

                        <ul class="unidade-info">
                            <li>
                                <span>📍</span>
                                <span>São Paulo, SP</span>
                            </li>

                            <li>
                                <span>🕒</span>
                                <span>24 Horas</span>
                            </li>

                            <li>
                                <span>🏋</span>
                                <span>Responsável: <?= htmlspecialchars($filial['responsavel'] ?? '') ?></span>
                            </li>
                        </ul>

                        <a class="unidade-btn" href="#">
                            Saiba mais
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

        <div class="unidades-acao">
            <span class="unidades-texto-geral">
                Conheça todas as nossas unidades
            </span>
        </div>

        <div class="unidades-mapa">
            <div class="unidades-mapa-conteudo">
                <div class="unidades-mapa-texto">
                    <h3>Veja a mais próxima de você</h3>
                    <p>
                        Use nosso mapa e descubra a unidade mais perto de você.
                    </p>

                    <button type="button" class="unidades-mapa-btn" id="abrir-mapa-completo">
                        <span class="unidades-mapa-btn-icone" aria-hidden="true">⌖</span>
                        Ver mapa completo
                    </button>
                </div>

                <div
                    class="unidades-mapa-box"
                    id="mapa-unidades"
                    aria-label="Mapa com as unidades da GymFlow"
                ></div>
            </div>
        </div>

    <?php endif; ?>

</section>

<div class="mapa-modal" id="mapa-modal" aria-hidden="true">
    <div class="mapa-modal-overlay" data-fechar-mapa></div>

    <div class="mapa-modal-conteudo" role="dialog" aria-modal="true" aria-labelledby="mapa-modal-titulo">
        <div class="mapa-modal-topo">
            <div>
                <span class="mapa-modal-destaque">Nossas Unidades</span>
                <h2 id="mapa-modal-titulo">Mapa completo</h2>
            </div>

            <button
                type="button"
                class="mapa-modal-fechar"
                id="fechar-mapa-completo"
                aria-label="Fechar mapa"
            >
                ×
            </button>
        </div>

        <div id="mapa-unidades-completo" aria-label="Mapa completo com as unidades da GymFlow"></div>
    </div>
</div>

<style>
.mapa-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 30px;
}
.mapa-modal.ativo {
    display: flex;
}
.mapa-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.84);
    backdrop-filter: blur(7px);
}
.mapa-modal-conteudo {
    width: min(1180px, 96vw);
    height: min(760px, 88vh);
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #070707;
    border: 1px solid rgba(255,255,255,.28);
    border-radius: 22px;
    box-shadow: 0 28px 80px rgba(0,0,0,.58);
}
.mapa-modal-topo {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 22px 26px;
    background: #090909;
    border-bottom: 1px solid rgba(255,255,255,.1);
}
.mapa-modal-destaque {
    display: block;
    margin-bottom: 4px;
    color: var(--portfolio-primary);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .11em;
}
.mapa-modal-topo h2 {
    margin: 0;
    color: #fff;
    font-size: clamp(22px, 3vw, 34px);
    line-height: 1.1;
}
.mapa-modal-fechar {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    background: transparent;
    border: 1px solid rgba(255,255,255,.28);
    border-radius: 50%;
    font-size: 27px;
    line-height: 1;
    cursor: pointer;
    transition: .25s ease;
}
.mapa-modal-fechar:hover {
    color: #000;
    background: var(--portfolio-primary);
    border-color: var(--portfolio-primary);
    transform: rotate(90deg);
}
#mapa-unidades-completo {
    width: 100%;
    flex: 1;
    min-height: 0;
    background: #050505;
}
#mapa-unidades-completo .leaflet-tile-pane {
    filter: grayscale(1) invert(1) sepia(.18) saturate(.75) brightness(.38) contrast(1.42);
}
#mapa-unidades-completo .leaflet-popup-content-wrapper,
#mapa-unidades-completo .leaflet-popup-tip {
    background: #111;
    color: #fff;
}
#mapa-unidades-completo .leaflet-popup-content strong {
    display: block;
    margin-bottom: 6px;
    color: var(--portfolio-primary);
}
#mapa-unidades-completo .leaflet-control-attribution {
    padding: 1px 5px;
    color: #8f8f8f;
    background: rgba(0,0,0,.72);
    font-size: 8px;
}
#mapa-unidades-completo .leaflet-control-attribution a {
    color: #b7b7b7;
}
body.mapa-modal-aberto {
    overflow: hidden;
}
@media (max-width: 700px) {
    .mapa-modal {
        padding: 14px;
    }
    .mapa-modal-conteudo {
        width: 100%;
        height: 88vh;
        border-radius: 16px;
    }
    .mapa-modal-topo {
        padding: 18px;
    }
}
</style>


    <!-- sobre Nós -->
    <section id="sobre" class="portfolio-sobre">
        <div class="sobre-principal">
            <div class="sobre-texto">
                <span class="secao-destaque">Sobre nós</span>

                <h2>
                    Mais que uma academia.<br>
                    <span>Um espaço para evoluir.</span>
                </h2>

                <p class="sobre-descricao">
                    <?= nl2br(htmlspecialchars($config['about_text'] ?? '')) ?>
                </p>

                <div class="sobre-linha"></div>

                <p class="sobre-frase">
                    Movimento, estrutura e acompanhamento para transformar cada etapa da sua jornada.
                </p>
            </div>

            <div class="sobre-imagem-wrap">
                <?php if (!empty($config['about_image'])): ?>
                    <img
                        src="<?= htmlspecialchars($config['about_image']) ?>"
                        alt="Ambiente da academia"
                        class="sobre-imagem"
                    >
                <?php else: ?>
                    <div class="sobre-imagem-vazia">
                        Adicione uma imagem no Admin
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="sobre-essencia">
            <div class="sobre-essencia-cabecalho">
                <span class="secao-destaque">Nossa essência</span>
                <h3>O que guia a GymFlow todos os dias</h3>
            </div>

            <div class="sobre-cards">
                <article class="sobre-card">
                    <div class="sobre-card-icone" aria-hidden="true">♡</div>
                    <h4>Nossos Valores</h4>
                    <p><?= nl2br(htmlspecialchars($config['company_values'] ?? '')) ?></p>
                </article>

                <article class="sobre-card">
                    <div class="sobre-card-icone" aria-hidden="true">✦</div>
                    <h4>Nossas Competências</h4>
                    <p><?= nl2br(htmlspecialchars($config['company_competencies'] ?? '')) ?></p>
                </article>
            </div>
        </div>
    </section>

    <!-- contato -->
    <section id="contato" class="portfolio-contato">
        <div class="contato-cabecalho">
            <span class="secao-destaque">Fale conosco</span>

            <h2>
                <?= htmlspecialchars($config['contact_title'] ?? '') ?>
            </h2>

            <p>
                <?= nl2br(htmlspecialchars($config['contact_subtitle'] ?? '')) ?>
            </p>
        </div>

        <div class="contato-conteudo">
            <div class="contato-informacoes">
                <div class="contato-imagem-wrap">
                    <?php if (!empty($config['contact_image'])): ?>
                        <img
                            src="<?= htmlspecialchars($config['contact_image']) ?>"
                            alt="Atendimento GymFlow"
                            class="contato-imagem"
                        >
                    <?php else: ?>
                        <div class="contato-imagem-vazia">
                            Adicione uma imagem no Admin
                        </div>
                    <?php endif; ?>
                </div>

                <div class="contato-dados">
                    <?php if (!empty($config['contact_email'])): ?>
                        <article class="contato-dado">
                            <span class="contato-dado-icone" aria-hidden="true">✉</span>
                            <div>
                                <strong>E-mail</strong>
                                <p><?= htmlspecialchars($config['contact_email']) ?></p>
                            </div>
                        </article>
                    <?php endif; ?>

                    <?php if (!empty($config['contact_phone'])): ?>
                        <article class="contato-dado">
                            <span class="contato-dado-icone" aria-hidden="true">☎</span>
                            <div>
                                <strong>Telefone / WhatsApp</strong>
                                <p><?= htmlspecialchars($config['contact_phone']) ?></p>
                            </div>
                        </article>
                    <?php endif; ?>

                    <?php if (!empty($config['contact_hours'])): ?>
                        <article class="contato-dado">
                            <span class="contato-dado-icone" aria-hidden="true">◷</span>
                            <div>
                                <strong>Horário de atendimento</strong>
                                <p><?= nl2br(htmlspecialchars($config['contact_hours'])) ?></p>
                            </div>
                        </article>
                    <?php endif; ?>
                </div>
            </div>

            <div class="contato-formulario-wrap">
                <h3>
                    <?= htmlspecialchars($config['contact_form_title'] ?? '') ?>
                </h3>

                <form class="contato-formulario" onsubmit="return false;">
                    <div class="contato-campo">
                        <label for="contato-nome">Nome</label>
                        <input
                            type="text"
                            id="contato-nome"
                            name="nome"
                            placeholder="Digite seu nome"
                            required
                        >
                    </div>

                    <div class="contato-campo">
                        <label for="contato-email">E-mail</label>
                        <input
                            type="email"
                            id="contato-email"
                            name="email"
                            placeholder="Digite seu e-mail"
                            required
                        >
                    </div>

                    <div class="contato-campo">
                        <label for="contato-telefone">Telefone</label>
                        <input
                            type="text"
                            id="contato-telefone"
                            name="telefone"
                            placeholder="(11) 99999-9999"
                            required
                        >
                    </div>

                    <div class="contato-campo">
                        <label for="contato-unidade">Unidade desejada</label>
                        <select id="contato-unidade" name="filial_id" required>
                            <option value="">Selecione uma unidade</option>

                            <?php foreach (($filiais ?? []) as $filial): ?>
                                <option value="<?= (int)($filial['id'] ?? 0) ?>">
                                    <?= htmlspecialchars($filial['nome'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="contato-campo">
                        <label for="contato-mensagem">Mensagem</label>
                        <textarea
                            id="contato-mensagem"
                            name="mensagem"
                            rows="5"
                            placeholder="Como podemos ajudar?"
                            required
                        ></textarea>
                    </div>

                    <button type="submit" class="contato-btn">
                        Enviar mensagem
                    </button>
                </form>
            </div>
        </div>
    </section>


    <!-- feedback -->
    <section id="feedback" class="portfolio-feedback">
        <div class="feedback-topo">
            <div class="feedback-formulario-area">
                <span class="secao-destaque">Sua opinião importa</span>

                <h2>
                    <?= htmlspecialchars($config['feedback_title'] ?? '') ?>
                </h2>

                <p>
                    <?= nl2br(htmlspecialchars($config['feedback_subtitle'] ?? '')) ?>
                </p>

                <?php if (($_GET['feedback'] ?? '') === 'sucesso'): ?>
                    <div class="feedback-status feedback-status-sucesso">
                        Feedback enviado com sucesso! Obrigado pela sua avaliação.
                    </div>
                <?php elseif (($_GET['feedback'] ?? '') === 'erro'): ?>
                    <div class="feedback-status feedback-status-erro">
                        Escolha uma nota de 1 a 5 estrelas e escreva uma mensagem.
                    </div>
                <?php endif; ?>

                <?php if ($alunoLogado): ?>
                    <form
                        class="feedback-formulario"
                        method="POST"
                        action="/Gymflow/app/controllers/FeedbackController.php"
                    >
                        <?php if ($nomeAlunoLogado !== ''): ?>
                            <div class="feedback-usuario-logado">
                                Avaliando como
                                <strong><?= htmlspecialchars($nomeAlunoLogado) ?></strong>
                            </div>
                        <?php endif; ?>

                        <div class="feedback-avaliacao">
                            <span class="feedback-avaliacao-label">Sua avaliação</span>

                            <div class="feedback-estrelas" role="radiogroup" aria-label="Escolha uma nota de 1 a 5 estrelas">
                                <?php for ($estrela = 1; $estrela <= 5; $estrela++): ?>
                                    <button
                                        type="button"
                                        class="feedback-estrela"
                                        data-nota="<?= $estrela ?>"
                                        aria-label="<?= $estrela ?> estrela<?= $estrela > 1 ? 's' : '' ?>"
                                    >☆</button>
                                <?php endfor; ?>
                            </div>

                            <input type="hidden" name="nota" id="feedback-nota" value="">
                        </div>

                        <div class="feedback-campo">
                            <label for="feedback-mensagem">Mensagem</label>
                            <textarea
                                id="feedback-mensagem"
                                name="mensagem"
                                rows="5"
                                placeholder="Conte pra gente como foi sua experiência..."
                            ></textarea>
                        </div>

                        <button type="submit" class="feedback-btn">
                            Enviar feedback
                        </button>
                    </form>
                <?php else: ?>
                    <div class="feedback-login-aviso">
                        <strong>Quer deixar sua avaliação?</strong>
                        <p>Entre como aluno para enviar seu feedback.</p>

                        <a
                            href="/Gymflow/app/controllers/LoginController.php?acao=login"
                            class="feedback-btn"
                            style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none;"
                        >
                            Entrar para avaliar
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="feedback-imagem-wrap">
                <?php if (!empty($config['feedback_image'])): ?>
                    <img
                        src="<?= htmlspecialchars($config['feedback_image']) ?>"
                        alt="Experiência na GymFlow"
                        class="feedback-imagem"
                    >
                <?php else: ?>
                    <div class="feedback-imagem-vazia">
                        Adicione uma imagem no Admin
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="feedback-recentes">
            <div class="feedback-lista-cabecalho">
                <span class="secao-destaque">Feedbacks recentes</span>
                <h3>O que estão dizendo sobre a GymFlow</h3>
            </div>

            <?php if (empty($feedbacks)): ?>
                <div class="feedback-vazio">
                    Ainda não há feedbacks publicados.
                </div>
            <?php else: ?>
                <div class="feedback-lista">
                    <?php foreach ($feedbacks as $feedback): ?>
                        <?php
                            $nomeFeedback = trim($feedback['nome'] ?? 'Visitante');
                            $notaFeedback = max(1, min(5, (int)($feedback['nota'] ?? 0)));
                            $dataFeedback = !empty($feedback['criado_em'])
                                ? date('d/m/Y', strtotime($feedback['criado_em']))
                                : '';
                            $inicialFeedback = function_exists('mb_substr')
                                ? mb_strtoupper(mb_substr($nomeFeedback, 0, 1, 'UTF-8'), 'UTF-8')
                                : strtoupper(substr($nomeFeedback, 0, 1));
                        ?>

                        <article class="feedback-card">
                            <div class="feedback-autor">
                                <div class="feedback-avatar">
                                    <?= htmlspecialchars($inicialFeedback) ?>
                                </div>

                                <div class="feedback-autor-info">
                                    <strong><?= htmlspecialchars($nomeFeedback) ?></strong>

                                    <?php if ($dataFeedback !== ''): ?>
                                        <span><?= htmlspecialchars($dataFeedback) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div
                                class="feedback-card-estrelas"
                                aria-label="<?= $notaFeedback ?> de 5 estrelas"
                            >
                                <?php for ($estrela = 1; $estrela <= 5; $estrela++): ?>
                                    <span class="<?= $estrela <= $notaFeedback ? 'ativa' : '' ?>">★</span>
                                <?php endfor; ?>
                            </div>

                            <p>
                                <?= nl2br(htmlspecialchars($feedback['mensagem'] ?? '')) ?>
                            </p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const slides = document.querySelectorAll('.hero-slide');
    const indicadores = document.querySelectorAll('.hero-indicador');
    const btnAnterior = document.querySelector('.hero-seta-anterior');
    const btnProxima = document.querySelector('.hero-seta-proxima');

    if (slides.length <= 1) {
        return;
    }

    let slideAtual = 0;
    let intervalo;

    function mostrarSlide(index) {
        slides.forEach(slide => slide.classList.remove('ativo'));
        indicadores.forEach(indicador => indicador.classList.remove('ativo'));

        if (index >= slides.length) {
            slideAtual = 0;
        } else if (index < 0) {
            slideAtual = slides.length - 1;
        } else {
            slideAtual = index;
        }

        slides[slideAtual].classList.add('ativo');

        if (indicadores[slideAtual]) {
            indicadores[slideAtual].classList.add('ativo');
        }
    }

    function iniciarAutomatico() {
        intervalo = setInterval(function () {
            mostrarSlide(slideAtual + 1);
        }, 5000);
    }

    if (btnProxima) {
        btnProxima.addEventListener('click', function () {
            mostrarSlide(slideAtual + 1);
        });
    }

    if (btnAnterior) {
        btnAnterior.addEventListener('click', function () {
            mostrarSlide(slideAtual - 1);
        });
    }

    indicadores.forEach((indicador, index) => {
        indicador.addEventListener('click', function () {
            mostrarSlide(index);
        });
    });

    iniciarAutomatico();
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapaElemento = document.getElementById('mapa-unidades');

    if (!mapaElemento || typeof L === 'undefined') {
        return;
    }

    const unidades = <?= json_encode(
        $filiais ?? [],
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES |
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT
    ) ?>;

    const unidadesComCoordenadas = unidades.filter(function (unidade) {
        const latitude = parseFloat(unidade.latitude);
        const longitude = parseFloat(unidade.longitude);

        return Number.isFinite(latitude) && Number.isFinite(longitude);
    });

    if (unidadesComCoordenadas.length === 0) {
        mapaElemento.textContent = 'Nenhuma unidade possui coordenadas cadastradas.';
        mapaElemento.style.display = 'flex';
        mapaElemento.style.alignItems = 'center';
        mapaElemento.style.justifyContent = 'center';
        return;
    }

    const mapa = L.map('mapa-unidades', {
        scrollWheelZoom: false,
        zoomControl: false
    });

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }
    ).addTo(mapa);

    const corPrimaria =
        getComputedStyle(document.documentElement)
            .getPropertyValue('--portfolio-primary')
            .trim() || '#C9A227';

    const limites = [];

    unidadesComCoordenadas.forEach(function (unidade) {
        const latitude = parseFloat(unidade.latitude);
        const longitude = parseFloat(unidade.longitude);

        const iconeUnidade = L.divIcon({
            className: 'gymflow-marker-wrap',
            html: '<div class="gymflow-pin" style="--pin-color:' + corPrimaria + '"></div>',
            iconSize: [34, 40],
            iconAnchor: [17, 38],
            popupAnchor: [0, -34]
        });

        const marcador = L.marker(
            [latitude, longitude],
            { icon: iconeUnidade }
        ).addTo(mapa);

        const popup = document.createElement('div');

        const titulo = document.createElement('strong');
        titulo.textContent = unidade.nome || 'Unidade';
        popup.appendChild(titulo);

        if (unidade.responsavel) {
            const responsavel = document.createElement('div');
            responsavel.textContent = 'Responsável: ' + unidade.responsavel;
            popup.appendChild(responsavel);
        }

        if (unidade.telefone) {
            const telefone = document.createElement('div');
            telefone.textContent = 'Telefone: ' + unidade.telefone;
            popup.appendChild(telefone);
        }

        marcador.bindPopup(popup);

        marcador.on('mouseover', function () {
            this.openPopup();
        });

        marcador.on('mouseout', function () {
            this.closePopup();
        });

        limites.push([latitude, longitude]);
    });

    if (limites.length > 1) {
        L.polyline(limites, {
            color: corPrimaria,
            weight: 1.4,
            opacity: 0.42,
            dashArray: '4, 7',
            interactive: false
        }).addTo(mapa);
    }

    if (limites.length === 1) {
        mapa.setView(limites[0], 13);
    } else {
        mapa.fitBounds(limites, {
            padding: [28, 28],
            maxZoom: 12
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const abrirMapa = document.getElementById('abrir-mapa-completo');
    const fecharMapa = document.getElementById('fechar-mapa-completo');
    const modalMapa = document.getElementById('mapa-modal');
    const overlayMapa = modalMapa ? modalMapa.querySelector('[data-fechar-mapa]') : null;

    if (!abrirMapa || !modalMapa || typeof L === 'undefined') {
        return;
    }

    const unidadesModal = <?= json_encode(
        $filiais ?? [],
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES |
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT
    ) ?>;

    const unidadesModalComCoordenadas = unidadesModal.filter(function (unidade) {
        const latitude = parseFloat(unidade.latitude);
        const longitude = parseFloat(unidade.longitude);

        return Number.isFinite(latitude) && Number.isFinite(longitude);
    });

    let mapaCompleto = null;

    function criarMapaCompleto() {
        if (mapaCompleto || unidadesModalComCoordenadas.length === 0) {
            return;
        }

        mapaCompleto = L.map('mapa-unidades-completo', {
            scrollWheelZoom: true,
            zoomControl: true
        });

        L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }
        ).addTo(mapaCompleto);

        const corPrimaria =
            getComputedStyle(document.documentElement)
                .getPropertyValue('--portfolio-primary')
                .trim() || '#C9A227';

        const limitesModal = [];

        unidadesModalComCoordenadas.forEach(function (unidade) {
            const latitude = parseFloat(unidade.latitude);
            const longitude = parseFloat(unidade.longitude);

            const iconeUnidade = L.divIcon({
                className: 'gymflow-marker-wrap',
                html: '<div class="gymflow-pin" style="--pin-color:' + corPrimaria + '"></div>',
                iconSize: [34, 40],
                iconAnchor: [17, 38],
                popupAnchor: [0, -34]
            });

            const marcador = L.marker(
                [latitude, longitude],
                { icon: iconeUnidade }
            ).addTo(mapaCompleto);

            const popup = document.createElement('div');

            const titulo = document.createElement('strong');
            titulo.textContent = unidade.nome || 'Unidade';
            popup.appendChild(titulo);

            if (unidade.responsavel) {
                const responsavel = document.createElement('div');
                responsavel.textContent = 'Responsável: ' + unidade.responsavel;
                popup.appendChild(responsavel);
            }

            if (unidade.telefone) {
                const telefone = document.createElement('div');
                telefone.textContent = 'Telefone: ' + unidade.telefone;
                popup.appendChild(telefone);
            }

            marcador.bindPopup(popup);

            marcador.on('mouseover', function () {
                this.openPopup();
            });

            marcador.on('mouseout', function () {
                this.closePopup();
            });

            limitesModal.push([latitude, longitude]);
        });

        if (limitesModal.length > 1) {
            L.polyline(limitesModal, {
                color: corPrimaria,
                weight: 1.5,
                opacity: 0.42,
                dashArray: '4, 7',
                interactive: false
            }).addTo(mapaCompleto);
        }

        if (limitesModal.length === 1) {
            mapaCompleto.setView(limitesModal[0], 14);
        } else if (limitesModal.length > 1) {
            mapaCompleto.fitBounds(limitesModal, {
                padding: [55, 55],
                maxZoom: 13
            });
        }
    }

    function abrirModalMapa() {
        modalMapa.classList.add('ativo');
        modalMapa.setAttribute('aria-hidden', 'false');
        document.body.classList.add('mapa-modal-aberto');

        criarMapaCompleto();

        window.setTimeout(function () {
            if (mapaCompleto) {
                mapaCompleto.invalidateSize();
            }
        }, 80);
    }

    function fecharModalMapa() {
        modalMapa.classList.remove('ativo');
        modalMapa.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('mapa-modal-aberto');
    }

    abrirMapa.addEventListener('click', abrirModalMapa);

    if (fecharMapa) {
        fecharMapa.addEventListener('click', fecharModalMapa);
    }

    if (overlayMapa) {
        overlayMapa.addEventListener('click', fecharModalMapa);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modalMapa.classList.contains('ativo')) {
            fecharModalMapa();
        }
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const estrelas = document.querySelectorAll('.feedback-estrela');
    const campoNota = document.getElementById('feedback-nota');

    if (!estrelas.length || !campoNota) {
        return;
    }

    let notaSelecionada = 0;

    function atualizarEstrelas(nota) {
        estrelas.forEach(function (estrela) {
            const valor = Number(estrela.dataset.nota);
            const ativa = valor <= nota;

            estrela.textContent = ativa ? '★' : '☆';
            estrela.classList.toggle('ativa', ativa);
        });
    }

    estrelas.forEach(function (estrela) {
        estrela.addEventListener('mouseenter', function () {
            atualizarEstrelas(Number(estrela.dataset.nota));
        });

        estrela.addEventListener('focus', function () {
            atualizarEstrelas(Number(estrela.dataset.nota));
        });

        estrela.addEventListener('click', function () {
            notaSelecionada = Number(estrela.dataset.nota);
            campoNota.value = String(notaSelecionada);
            atualizarEstrelas(notaSelecionada);
        });
    });

    const grupoEstrelas = document.querySelector('.feedback-estrelas');

    if (grupoEstrelas) {
        grupoEstrelas.addEventListener('mouseleave', function () {
            atualizarEstrelas(notaSelecionada);
        });

        grupoEstrelas.addEventListener('focusout', function () {
            window.setTimeout(function () {
                if (!grupoEstrelas.contains(document.activeElement)) {
                    atualizarEstrelas(notaSelecionada);
                }
            }, 0);
        });
    }
});
</script>


<?php include __DIR__ . '/app/views/shared/portfolio_footer.php'; ?>