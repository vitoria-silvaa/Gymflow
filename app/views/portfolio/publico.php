<?php
// app/views/portfolio/publico.php - Portfólio Público (View)

if (!defined('BASE_URL')) {
    http_response_code(403);
    exit('Acesso negado.');
}

include __DIR__ . '/../shared/portfolio_header.php';
?>

<main>

    <!-- ==========================================
         INÍCIO / CARROSSEL
    =========================================== -->

    <section id="inicio" class="portfolio-hero">

        <!-- IMAGENS DO CARROSSEL -->

        <div class="hero-carrossel">

            <?php if (!empty($slides)): ?>

                <?php foreach ($slides as $index => $slide): ?>

                    <div class="hero-slide <?= $index === 0 ? 'ativo' : '' ?>">

                        <?php if (!empty($slide['image_url'])): ?>

                            <img
                                src="<?= htmlspecialchars(
                                            $slide['image_url'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                alt="Imagem do portfólio">

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="hero-slide ativo hero-slide-vazio"></div>

            <?php endif; ?>

        </div>

        <div class="hero-overlay"></div>

        <div class="hero-conteudo">

            <h1>
                <?= htmlspecialchars($config['hero_title'] ?? '') ?>
            </h1>

            <p class="hero-subtitulo">
                <?= htmlspecialchars($config['hero_subtitle'] ?? '') ?>
            </p>

            <div class="hero-acoes">

                <a
                    class="hero-btn hero-btn-principal"
                    href="#planos">

                    <?= htmlspecialchars($config['hero_cta'] ?? 'Matricule-se') ?>

                </a>

                <a
                    class="hero-btn hero-btn-secundario"
                    href="#unidades">

                    Encontrar unidade

                </a>

            </div>

        </div>

        <?php if (!empty($slides) && count($slides) > 1): ?>

            <button
                type="button"
                class="hero-seta hero-seta-anterior"
                aria-label="Imagem anterior">

                &#10094;

            </button>

            <button
                type="button"
                class="hero-seta hero-seta-proxima"
                aria-label="Próxima imagem">

                &#10095;

            </button>

            <div class="hero-indicadores">

                <?php foreach ($slides as $index => $slide): ?>

                    <button
                        type="button"
                        class="hero-indicador <?= $index === 0 ? 'ativo' : '' ?>"
                        data-slide="<?= $index ?>"
                        aria-label="Ir para imagem <?= $index + 1 ?>">
                    </button>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

    <!-- ==========================================
         MODALIDADES
    =========================================== -->

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

                        <div class="modalidade-imagem">

                            <?php if (!empty($mod['image_url'])): ?>

                                <img
                                    src="<?= htmlspecialchars(
                                                $mod['image_url'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                    alt="<?= htmlspecialchars(
                                                $mod['name'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>">

                            <?php endif; ?>

                        </div>

                        <div class="modalidade-conteudo">

                            <h3>
                                <?= htmlspecialchars($mod['name'] ?? '') ?>
                            </h3>

                            <p>
                                <?= htmlspecialchars($mod['description'] ?? '') ?>
                            </p>

                            <a
                                href="#"
                                class="modalidade-saiba-mais">

                                Saiba mais →

                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

    <!-- ==========================================
         PLANOS
    =========================================== -->

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

                            <span class="plano-destaque">
                                Mais escolhido
                            </span>

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
                                    (float) ($plano['valor'] ?? 0),
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
                            href="<?= BASE_URL ?>/app/controllers/MatriculaController.php?acao=identificacao&amp;company_id=<?= (int) $company_id ?>&amp;plano_id=<?= (int) ($plano['id'] ?? 0) ?>">

                            Matricule-se agora

                        </a>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <!-- ==========================================
             PARCEIROS E BENEFÍCIOS
        =========================================== -->

        <?php

        $aceitaWellhub = !empty($config['accepts_wellhub']);
        $aceitaTotalpass = !empty($config['accepts_totalpass']);

        ?>

        <?php if ($aceitaWellhub || $aceitaTotalpass): ?>

            <div
                class="portfolio-parceiros"
                aria-labelledby="parceiros-titulo">

                <div class="parceiros-conteudo">

                    <div class="parceiros-texto">

                        <span class="secao-destaque">
                            Benefícios
                        </span>

                        <h2 id="parceiros-titulo">
                            Nossas unidades aceitam
                        </h2>

                        <p>
                            Use seu benefício fitness e treine com a gente.
                        </p>

                    </div>

                    <div class="parceiros-marcas">

                        <?php if ($aceitaWellhub): ?>

                            <div class="parceiro-marca">

                                <?php if (!empty($config['wellhub_icon'])): ?>

                                    <span class="parceiro-icone parceiro-icone-imagem">

                                        <img
                                            src="<?= htmlspecialchars($config['wellhub_icon']) ?>"
                                            alt="Wellhub">

                                    </span>

                                <?php else: ?>

                                    <span
                                        class="parceiro-icone parceiro-icone-wellhub"
                                        aria-hidden="true">

                                        ✦

                                    </span>

                                <?php endif; ?>

                                <div>

                                    <strong>Wellhub</strong>

                                    <small>(Gympass)</small>

                                </div>

                            </div>

                        <?php endif; ?>

                        <?php if ($aceitaWellhub && $aceitaTotalpass): ?>

                            <span
                                class="parceiro-divisor"
                                aria-hidden="true">
                            </span>

                        <?php endif; ?>

                        <?php if ($aceitaTotalpass): ?>

                            <div class="parceiro-marca">

                                <?php if (!empty($config['totalpass_icon'])): ?>

                                    <span class="parceiro-icone parceiro-icone-imagem">

                                        <img
                                            src="<?= htmlspecialchars($config['totalpass_icon']) ?>"
                                            alt="TotalPass">

                                    </span>

                                <?php else: ?>

                                    <span
                                        class="parceiro-icone parceiro-icone-totalpass"
                                        aria-hidden="true">

                                        TP

                                    </span>

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

    <!-- ==========================================
         UNIDADES
    =========================================== -->

    <section id="unidades" class="portfolio-unidades">

        <div class="unidades-cabecalho">

            <span class="secao-destaque">
                Nossas Unidades
            </span>

            <h2>
                Conheça nossas unidades
            </h2>

            <p>
                Encontre a unidade ideal para treinar com conforto,
                estrutura e qualidade.
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

                        <!-- IMAGEM DA UNIDADE -->

                        <div class="unidade-imagem">

                            <?php if (!empty($filial['image_url'])): ?>

                                <img
                                    src="<?= htmlspecialchars($filial['image_url']) ?>"
                                    alt="<?= htmlspecialchars($filial['nome'] ?? 'Unidade') ?>">

                            <?php endif; ?>

                        </div>

                        <!-- INFORMAÇÕES -->

                        <div class="unidade-conteudo">

                            <h3>
                                <?= htmlspecialchars($filial['nome'] ?? '') ?>
                            </h3>

                            <ul class="unidade-info">

                                <!-- LOCALIZAÇÃO -->

                                <li>

                                    <svg
                                        class="unidade-icone"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                        focusable="false">

                                        <path
                                            d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />

                                        <circle
                                            cx="12"
                                            cy="10"
                                            r="2.5" />

                                    </svg>

                                    <span>

                                        <?= !empty($filial['endereco'])
                                            ? htmlspecialchars($filial['endereco'])
                                            : 'Endereço não cadastrado' ?>

                                        <?= !empty($filial['numero'])
                                            ? ', ' . htmlspecialchars($filial['numero'])
                                            : '' ?>

                                        <?= !empty($filial['complemento'])
                                            ? ' (' . htmlspecialchars($filial['complemento']) . ')'
                                            : '' ?>

                                    </span>

                                </li>

                                <!-- HORÁRIO DE FUNCIONAMENTO -->

                                <li>

                                    <svg
                                        class="unidade-icone"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                        focusable="false">

                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="9" />

                                        <path d="M12 7v5l3.5 2" />

                                    </svg>

                                    <span>24 Horas</span>

                                </li>

                                <!-- RESPONSÁVEL PELA UNIDADE -->

                                <li>

                                    <svg
                                        class="unidade-icone"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                        focusable="false">

                                        <circle
                                            cx="12"
                                            cy="7"
                                            r="4" />

                                        <path
                                            d="M4 21v-2a8 8 0 0 1 16 0v2" />

                                    </svg>

                                    <span>

                                        Responsável:
                                        <?= htmlspecialchars($filial['responsavel'] ?? '') ?>

                                    </span>

                                </li>

                            </ul>

                            <!-- BOTÃO GOOGLE MAPS -->

                            <a
                                class="unidade-btn"
                                href="https://www.google.com/maps/search/?api=1&query=<?= urlencode(($filial['latitude'] ?? '') . ',' . ($filial['longitude'] ?? '')) ?>"
                                target="_blank"
                                rel="noopener noreferrer">
                                <svg
                                    class="unidade-icone"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="18"
                                    height="18"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                    focusable="false">

                                    <path
                                        d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />

                                    <circle
                                        cx="12"
                                        cy="10"
                                        r="2.5" />

                                </svg>

                                Abrir no Google Maps

                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

            <!-- TODAS AS UNIDADES -->

            <div class="unidades-acao">

                <span class="unidades-texto-geral">
                    Conheça todas as nossas unidades
                </span>

            </div>

            <!-- MAPA DAS UNIDADES -->

            <div class="unidades-mapa">

                <div class="unidades-mapa-conteudo">

                    <div class="unidades-mapa-texto">

                        <h3>
                            Veja a mais próxima de você
                        </h3>

                        <p>
                            Use nosso mapa e descubra a unidade
                            mais perto de você.
                        </p>

                        <button
                            type="button"
                            class="unidades-mapa-btn"
                            id="abrir-mapa-completo">

                            <span
                                class="unidades-mapa-btn-icone"
                                aria-hidden="true">

                                ⌖

                            </span>

                            Ver mapa completo

                        </button>

                    </div>

                    <div
                        class="unidades-mapa-box"
                        id="mapa-unidades"
                        aria-label="Mapa com as unidades da GymFlow">
                    </div>

                </div>

            </div>

        <?php endif; ?>

    </section>

    <!-- ==========================================
         MODAL DO MAPA
    =========================================== -->

    <div
        class="mapa-modal"
        id="mapa-modal"
        aria-hidden="true">

        <div
            class="mapa-modal-overlay"
            data-fechar-mapa>
        </div>

        <div
            class="mapa-modal-conteudo"
            role="dialog"
            aria-modal="true"
            aria-labelledby="mapa-modal-titulo">

            <div class="mapa-modal-topo">

                <div>

                    <span class="mapa-modal-destaque">
                        Nossas Unidades
                    </span>

                    <h2 id="mapa-modal-titulo">
                        Mapa completo
                    </h2>

                </div>

                <button
                    type="button"
                    class="mapa-modal-fechar"
                    id="fechar-mapa-completo"
                    aria-label="Fechar mapa">

                    ×

                </button>

            </div>

            <div
                id="mapa-unidades-completo"
                aria-label="Mapa completo com as unidades da GymFlow">
            </div>

        </div>

    </div>

    <!-- ==========================================
         SOBRE NÓS
    =========================================== -->

    <section id="sobre" class="portfolio-sobre">

        <div class="sobre-principal">

            <div class="sobre-texto">

                <span class="secao-destaque">
                    Sobre nós
                </span>

                <h2>
                    Mais que uma academia.<br>
                    <span>Um espaço para evoluir.</span>
                </h2>

                <p class="sobre-descricao">

                    <?= nl2br(htmlspecialchars(
                        $config['about_text'] ?? ''
                    )) ?>

                </p>

                <div class="sobre-linha"></div>

                <p class="sobre-frase">
                    Movimento, estrutura e acompanhamento
                    para transformar cada etapa da sua jornada.
                </p>

            </div>

            <div class="sobre-imagem-wrap">

                <?php if (!empty($config['about_image'])): ?>

                    <img
                        src="<?= htmlspecialchars($config['about_image']) ?>"
                        alt="Ambiente da academia"
                        class="sobre-imagem">

                <?php else: ?>

                    <div class="sobre-imagem-vazia">
                    </div>

                <?php endif; ?>

            </div>

        </div>

        <div class="sobre-essencia">

            <div class="sobre-essencia-cabecalho">

                <span class="secao-destaque">
                    Nossa essência
                </span>

                <h3>
                    O que guia a GymFlow todos os dias
                </h3>

            </div>

            <div class="sobre-cards">

                <article class="sobre-card">

                    <div
                        class="sobre-card-icone"
                        aria-hidden="true">

                        ♡

                    </div>

                    <h4>Nossos Valores</h4>

                    <p>

                        <?= nl2br(htmlspecialchars(
                            $config['company_values'] ?? ''
                        )) ?>

                    </p>

                </article>

                <article class="sobre-card">

                    <div
                        class="sobre-card-icone"
                        aria-hidden="true">

                        ✦

                    </div>

                    <h4>Nossas Competências</h4>

                    <p>

                        <?= nl2br(htmlspecialchars(
                            $config['company_competencies'] ?? ''
                        )) ?>

                    </p>

                </article>

            </div>

        </div>

    </section>

    <!-- ==========================================
         CONTATO
    =========================================== -->

    <section id="contato" class="portfolio-contato">

        <div class="contato-cabecalho">

            <span class="secao-destaque">
                Fale conosco
            </span>

            <h2>
                <?= htmlspecialchars($config['contact_title'] ?? '') ?>
            </h2>

            <p>

                <?= nl2br(htmlspecialchars(
                    $config['contact_subtitle'] ?? ''
                )) ?>

            </p>

        </div>

        <div class="contato-conteudo">

            <div class="contato-informacoes">

                <div class="contato-imagem-wrap">

                    <?php if (!empty($config['contact_image'])): ?>

                        <img
                            src="<?= htmlspecialchars($config['contact_image']) ?>"
                            alt="Atendimento GymFlow"
                            class="contato-imagem">

                    <?php else: ?>

                        <div class="contato-imagem-vazia">
                        </div>

                    <?php endif; ?>

                </div>

                <div class="contato-dados">

                    <!-- E-MAIL -->

                    <?php if (!empty($config['contact_email'])): ?>

                        <article class="contato-dado">

                            <span
                                class="contato-dado-icone"
                                aria-hidden="true">

                                ✉

                            </span>

                            <div>

                                <strong>E-mail</strong>

                                <p>
                                    <?= htmlspecialchars($config['contact_email']) ?>
                                </p>

                            </div>

                        </article>

                    <?php endif; ?>

                    <!-- TELEFONE -->

                    <?php if (!empty($config['contact_phone'])): ?>

                        <article class="contato-dado">

                            <span
                                class="contato-dado-icone"
                                aria-hidden="true">

                                ☎

                            </span>

                            <div>

                                <strong>Telefone / WhatsApp</strong>

                                <p>
                                    <?= htmlspecialchars($config['contact_phone']) ?>
                                </p>

                            </div>

                        </article>

                    <?php endif; ?>

                    <!-- HORÁRIO -->

                    <?php if (!empty($config['contact_hours'])): ?>

                        <article class="contato-dado">

                            <span
                                class="contato-dado-icone"
                                aria-hidden="true">

                                ◷

                            </span>

                            <div>

                                <strong>Horário de atendimento</strong>

                                <p>

                                    <?= nl2br(htmlspecialchars(
                                        $config['contact_hours']
                                    )) ?>

                                </p>

                            </div>

                        </article>

                    <?php endif; ?>

                </div>

            </div>

            <!-- FORMULÁRIO DE CONTATO -->

            <div class="contato-formulario-wrap">

                <h3>

                    <?= htmlspecialchars(
                        $config['contact_form_title'] ?? ''
                    ) ?>

                </h3>

                <?php

                $wpPort = preg_replace(
                    '/\D/',
                    '',
                    $config['contact_phone'] ?? ''
                );

                ?>

                <form
                    class="contato-formulario"
                    onsubmit="enviarWhatsApp(event, '<?= $wpPort ?>')">

                    <div class="contato-campo">

                        <label for="contato-nome">
                            Nome
                        </label>

                        <input
                            type="text"
                            id="contato-nome"
                            name="nome"
                            placeholder="Digite seu nome"
                            required>

                    </div>

                    <div class="contato-campo">

                        <label for="contato-email">
                            E-mail
                        </label>

                        <input
                            type="email"
                            id="contato-email"
                            name="email"
                            placeholder="Digite seu e-mail"
                            required>

                    </div>

                    <div class="contato-campo">

                        <label for="contato-telefone">
                            Telefone
                        </label>

                        <input
                            type="text"
                            id="contato-telefone"
                            name="telefone"
                            placeholder="(11) 99999-9999"
                            required>

                    </div>

                    <div class="contato-campo">

                        <label for="contato-unidade">
                            Unidade desejada
                        </label>

                        <select
                            id="contato-unidade"
                            name="filial_id"
                            required>

                            <option value="">
                                Selecione uma unidade
                            </option>

                            <?php foreach (($filiais ?? []) as $filial): ?>

                                <option value="<?= (int) ($filial['id'] ?? 0) ?>">

                                    <?= htmlspecialchars(
                                        $filial['nome'] ?? ''
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="contato-campo">

                        <label for="contato-mensagem">
                            Mensagem
                        </label>

                        <textarea
                            id="contato-mensagem"
                            name="mensagem"
                            rows="5"
                            placeholder="Como podemos ajudar?"
                            required></textarea>

                    </div>

                    <button
                        type="submit"
                        class="contato-btn">

                        Enviar mensagem

                    </button>

                </form>

            </div>

        </div>

    </section>

    <!-- ==========================================
         AVALIAÇÕES DO SITE PÚBLICO
    =========================================== -->

    <section id="feedback" class="portfolio-feedback">

        <div class="feedback-topo">

            <div class="feedback-formulario-area">

                <span class="secao-destaque">
                    Sua opinião importa
                </span>

                <h2>

                    <?= htmlspecialchars(
                        $config['feedback_title'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </h2>

                <p>

                    <?= nl2br(htmlspecialchars(
                        $config['feedback_subtitle'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    )) ?>

                </p>

                <!-- MENSAGENS DE RETORNO -->

                <?php if ($statusFeedback === 'sucesso'): ?>

                    <div
                        class="feedback-status feedback-status-sucesso"
                        role="status">

                        Sua avaliação foi salva com sucesso!
                        Ela está aguardando aprovação para
                        aparecer no site.

                    </div>

                <?php elseif ($statusFeedback === 'dados_invalidos'): ?>

                    <div
                        class="feedback-status feedback-status-erro"
                        role="alert">

                        Escolha uma nota de 1 a 5 estrelas
                        e escreva um comentário válido.

                    </div>

                <?php elseif ($statusFeedback === 'erro'): ?>

                    <div
                        class="feedback-status feedback-status-erro"
                        role="alert">

                        Não foi possível salvar sua avaliação.
                        Tente novamente.

                    </div>

                <?php endif; ?>

                <!-- ALUNO DA ACADEMIA -->

                <?php if ($alunoDaAcademia): ?>

                    <form
                        class="feedback-formulario"
                        method="POST"
                        action="<?= BASE_URL ?>/app/controllers/FeedbackController.php"
                        id="feedbackFormularioPublico">

                        <!-- ORIGEM DO ENVIO -->

                        <input
                            type="hidden"
                            name="origem"
                            value="site">

                        <!-- PROTEÇÃO CSRF -->

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(
                                        $_SESSION['feedback_csrf_token'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>">

                        <?php if ($nomeExibicao !== ''): ?>

                            <div class="feedback-usuario-logado">

                                Avaliando como

                                <strong>

                                    <?= htmlspecialchars(
                                        $nomeExibicao,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>

                            </div>

                        <?php endif; ?>

                        <!-- NOTA -->

                        <div class="feedback-avaliacao">

                            <span
                                class="feedback-avaliacao-label"
                                id="feedbackRotuloNota">

                                Sua avaliação

                            </span>

                            <div
                                class="feedback-estrelas"
                                role="group"
                                aria-labelledby="feedbackRotuloNota">

                                <?php for ($estrela = 1; $estrela <= 5; $estrela++): ?>

                                    <button
                                        type="button"
                                        class="feedback-estrela"
                                        data-nota="<?= $estrela ?>"
                                        aria-label="<?= $estrela ?> estrela<?= $estrela > 1 ? 's' : '' ?>"
                                        aria-pressed="false">

                                        ☆

                                    </button>

                                <?php endfor; ?>

                            </div>

                            <input
                                type="hidden"
                                name="nota"
                                id="feedback-nota"
                                value="">

                        </div>

                        <!-- COMENTÁRIO -->

                        <div class="feedback-campo">

                            <label for="feedback-mensagem">
                                Mensagem
                            </label>

                            <textarea
                                id="feedback-mensagem"
                                name="mensagem"
                                rows="5"
                                maxlength="1000"
                                required
                                placeholder="Conte pra gente como foi sua experiência..."></textarea>

                        </div>

                        <!-- ENVIO -->

                        <button
                            type="submit"
                            class="feedback-btn"
                            id="feedbackBotaoEnviar">

                            Enviar avaliação

                        </button>

                        <p class="feedback-aviso-moderacao">

                            Seu comentário será analisado
                            antes de aparecer no site.
                            Se você já avaliou, este envio
                            atualizará sua avaliação anterior.

                        </p>

                    </form>

                    <!-- ALUNO DE OUTRA ACADEMIA -->

                <?php elseif ($usuarioDeOutraAcademia): ?>

                    <div class="feedback-login-aviso">

                        <strong>
                            Avaliação disponível para alunos desta academia
                        </strong>

                        <p>
                            Sua conta está vinculada a outra academia.
                            Somente alunos desta unidade empresarial
                            podem enviar avaliações aqui.
                        </p>

                    </div>

                    <!-- USUÁRIO LOGADO, MAS NÃO ALUNO -->

                <?php elseif ($usuarioIdFeedback > 0): ?>

                    <div class="feedback-login-aviso">

                        <strong>
                            Avaliações dos alunos
                        </strong>

                        <p>
                            Apenas alunos cadastrados nesta academia
                            podem enviar avaliações.
                        </p>

                    </div>

                    <!-- VISITANTE -->

                <?php else: ?>

                    <div class="feedback-login-aviso">

                        <strong>
                            Quer deixar sua avaliação?
                        </strong>

                        <p>
                            Entre na sua conta de aluno
                            para enviar seu feedback.
                        </p>

                        <a
                            href="<?= BASE_URL ?>/app/controllers/LoginController.php?acao=login&amp;destino=avaliacao&amp;empresa=<?= (int) $empresaDoSite ?>"
                            class="feedback-btn feedback-btn-link">

                            Entrar para avaliar

                        </a>

                    </div>

                <?php endif; ?>

            </div>

            <!-- IMAGEM DA SEÇÃO -->

            <div class="feedback-imagem-wrap">

                <?php if (!empty($config['feedback_image'])): ?>

                    <img
                        src="<?= htmlspecialchars(
                                    $config['feedback_image'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                        alt="Experiência na academia"
                        class="feedback-imagem">

                <?php else: ?>

                    <div class="feedback-imagem-vazia">
                    </div>

                <?php endif; ?>

            </div>

        </div>

        <!-- ==========================================
             AVALIAÇÕES PUBLICADAS
        =========================================== -->

        <div class="feedback-recentes">

            <div class="feedback-lista-cabecalho">

                <span class="secao-destaque">
                    Feedbacks recentes
                </span>

                <h3>
                    O que nossos alunos estão dizendo
                </h3>

            </div>

            <?php if (empty($feedbacks)): ?>

                <div class="feedback-vazio">
                    Ainda não há avaliações publicadas.
                </div>

            <?php else: ?>

                <div class="feedback-carousel" data-feedback-carousel>

                    <button
                        type="button"
                        class="feedback-carousel-seta feedback-carousel-anterior"
                        aria-label="Feedbacks anteriores">

                        &#10094;

                    </button>

                    <div
                        class="feedback-carousel-viewport"
                        aria-label="Avaliações dos alunos">

                        <div class="feedback-lista feedback-carousel-track">

                            <?php foreach ($feedbacks as $feedback): ?>

                                <?php

                                $nomeFeedback = trim(
                                    (string) ($feedback['nome'] ?? 'Aluno')
                                );

                                $notaFeedback = max(
                                    1,
                                    min(
                                        5,
                                        (int) ($feedback['nota'] ?? 0)
                                    )
                                );

                                $dataFeedback = !empty($feedback['criado_em'])

                                    ? date(
                                        'd/m/Y',
                                        strtotime($feedback['criado_em'])
                                    )

                                    : '';

                                $inicialFeedback = function_exists('mb_substr')

                                    ? mb_strtoupper(
                                        mb_substr(
                                            $nomeFeedback,
                                            0,
                                            1,
                                            'UTF-8'
                                        ),
                                        'UTF-8'
                                    )

                                    : strtoupper(
                                        substr($nomeFeedback, 0, 1)
                                    );

                                ?>

                                <article class="feedback-card">

                                    <div class="feedback-autor">

                                        <div class="feedback-avatar">

                                            <?= htmlspecialchars(
                                                $inicialFeedback,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </div>

                                        <div class="feedback-autor-info">

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $nomeFeedback,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </strong>

                                            <?php if ($dataFeedback !== ''): ?>

                                                <span>

                                                    <?= htmlspecialchars(
                                                        $dataFeedback,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>

                                                </span>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                    <div
                                        class="feedback-card-estrelas"
                                        aria-label="<?= $notaFeedback ?> de 5 estrelas">

                                        <?php for ($estrela = 1; $estrela <= 5; $estrela++): ?>

                                            <span
                                                class="<?= $estrela <= $notaFeedback
                                                            ? 'ativa'
                                                            : '' ?>">

                                                ★

                                            </span>

                                        <?php endfor; ?>

                                    </div>

                                    <p>

                                        <?= nl2br(htmlspecialchars(
                                            $feedback['mensagem'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        )) ?>

                                    </p>

                                </article>

                            <?php endforeach; ?>

                        </div>
                    </div>

                    <button
                        type="button"
                        class="feedback-carousel-seta feedback-carousel-proxima"
                        aria-label="Próximos feedbacks">

                        &#10095;

                    </button>

                </div>

                <div
                    class="feedback-carousel-indicadores"
                    aria-label="Navegação dos feedbacks">
                </div>

            <?php endif; ?>

        </div>

    </section>

</main>

<!-- ==========================================
     DADOS DAS UNIDADES PARA O MAPA
=========================================== -->

<script>
    window.GYMFLOW_UNIDADES = <?= json_encode(
                                    $filiais ?? [],
                                    JSON_UNESCAPED_UNICODE |
                                        JSON_UNESCAPED_SLASHES |
                                        JSON_HEX_TAG |
                                        JSON_HEX_AMP |
                                        JSON_HEX_APOS |
                                        JSON_HEX_QUOT
                                ) ?>;
</script>

<!-- JAVASCRIPT DO PORTFÓLIO -->

<script src="<?= BASE_URL ?>/assets/js/portfolio.js?v=2"></script>

<?php include __DIR__ . '/../shared/portfolio_footer.php'; ?>