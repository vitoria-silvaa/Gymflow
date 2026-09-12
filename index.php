<?php
// index.php - Portfólio Público (View)

$company_id = 1;
$operacao = 'buscar_publico';
require_once __DIR__ . '/app/models/Portfolio.php';

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
    <div class="portfolio-parceiros" aria-labelledby="parceiros-titulo">
        <div class="parceiros-conteudo">
            <div class="parceiros-texto">
                <span class="secao-destaque">Benefícios</span>
                <h2 id="parceiros-titulo">Nossas unidades aceitam</h2>
                <p>Use seu benefício fitness e treine com a gente.</p>
            </div>

            <div class="parceiros-marcas">
                <div class="parceiro-marca">
                    <span class="parceiro-icone parceiro-icone-wellhub" aria-hidden="true">✦</span>
                    <div>
                        <strong>Wellhub</strong>
                        <small>(Gympass)</small>
                    </div>
                </div>

                <span class="parceiro-divisor" aria-hidden="true"></span>

                <div class="parceiro-marca">
                    <span class="parceiro-icone parceiro-icone-totalpass" aria-hidden="true">TP</span>
                    <div>
                        <strong>TotalPass</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

<?php include __DIR__ . '/app/views/shared/footer.php'; ?>