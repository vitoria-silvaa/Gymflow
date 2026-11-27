// assets/js/portfolio.js

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

document.addEventListener('DOMContentLoaded', function () {
    const mapaElemento = document.getElementById('mapa-unidades');

    if (!mapaElemento || typeof L === 'undefined') {
        return;
    }

    const unidades = window.GYMFLOW_UNIDADES || [];

    const unidadesComCoordenadas = unidades.filter(function (unidade) {
        const latitude = parseFloat(unidade.latitude);
        const longitude = parseFloat(unidade.longitude);

        return Number.isFinite(latitude) && Number.isFinite(longitude);
    });

    if (unidadesComCoordenadas.length === 0) {
        mapaElemento.textContent = 'Nenhuma unidade possui coordenadas cadastradas.';
        mapaElemento.classList.add('mapa-sem-coordenadas');
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

        const btnMaps = document.createElement('a');
        btnMaps.href = 'https://www.google.com/maps/search/?api=1&query=' + latitude + ',' + longitude;
        btnMaps.target = '_blank';
        btnMaps.rel = 'noopener noreferrer';
        btnMaps.textContent = ' Abrir no Google Maps';
        btnMaps.className = 'mapa-btn-google';
        popup.appendChild(btnMaps);

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

document.addEventListener('DOMContentLoaded', function () {
    const abrirMapa = document.getElementById('abrir-mapa-completo');
    const fecharMapa = document.getElementById('fechar-mapa-completo');
    const modalMapa = document.getElementById('mapa-modal');
    const overlayMapa = modalMapa ? modalMapa.querySelector('[data-fechar-mapa]') : null;

    if (!abrirMapa || !modalMapa || typeof L === 'undefined') {
        return;
    }

    const unidadesModal = window.GYMFLOW_UNIDADES || [];

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

            const btnMaps = document.createElement('a');
            btnMaps.href = 'https://www.google.com/maps/search/?api=1&query=' + latitude + ',' + longitude;
            btnMaps.target = '_blank';
            btnMaps.rel = 'noopener noreferrer';
            btnMaps.textContent = ' Abrir no Google Maps';
            btnMaps.className = 'mapa-btn-google';
            popup.appendChild(btnMaps);

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

        window._limitesMapaCompleto = limitesModal;
    }

    function abrirModalMapa() {
        modalMapa.classList.add('ativo');
        modalMapa.setAttribute('aria-hidden', 'false');
        document.body.classList.add('mapa-modal-aberto');

        criarMapaCompleto();

        window.setTimeout(function () {
            if (mapaCompleto) {
                mapaCompleto.invalidateSize();

                if (window._limitesMapaCompleto) {
                    if (window._limitesMapaCompleto.length === 1) {
                        mapaCompleto.setView(window._limitesMapaCompleto[0], 14);
                    } else if (window._limitesMapaCompleto.length > 1) {
                        mapaCompleto.fitBounds(window._limitesMapaCompleto, {
                            padding: [55, 55],
                            maxZoom: 13
                        });
                    }
                }
            }
        }, 150);
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

/* =========================================
   GYMFLOW - INTERATIVIDADE DO PORTFÓLIO
========================================= */

document.addEventListener('DOMContentLoaded', function () {

    /* =====================================
       BARRA DE PROGRESSO
    ===================================== */

    const barraProgresso = document.createElement('div');
    barraProgresso.className = 'gymflow-scroll-progress';
    barraProgresso.setAttribute('aria-hidden', 'true');

    document.body.appendChild(barraProgresso);


    /* =====================================
       BOTÃO VOLTAR AO TOPO
    ===================================== */

    const botaoTopo = document.createElement('button');

    botaoTopo.type = 'button';
    botaoTopo.className = 'gymflow-voltar-topo';
    botaoTopo.innerHTML = '&#8593;';
    botaoTopo.setAttribute('aria-label', 'Voltar ao topo');

    document.body.appendChild(botaoTopo);

    botaoTopo.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });


    /* =====================================
       ATUALIZAR AO ROLAR
    ===================================== */

    function atualizarRolagem() {

        const alturaDocumento =
            document.documentElement.scrollHeight -
            window.innerHeight;

        const progresso = alturaDocumento > 0
            ? (window.scrollY / alturaDocumento) * 100
            : 0;

        barraProgresso.style.width =
            Math.min(progresso, 100) + '%';

        if (window.scrollY > 400) {
            botaoTopo.classList.add('visivel');
        } else {
            botaoTopo.classList.remove('visivel');
        }
    }

    window.addEventListener(
        'scroll',
        atualizarRolagem,
        { passive: true }
    );

    window.addEventListener(
        'resize',
        atualizarRolagem
    );

    atualizarRolagem();


    /* =====================================
       ANIMAÇÃO DE ENTRADA DAS SEÇÕES
    ===================================== */

    const elementosAnimados = document.querySelectorAll(
        [
            '.modalidades-cabecalho',
            '.modalidade-card',
            '.planos-cabecalho',
            '.plano-card',
            '.unidades-cabecalho',
            '.unidade-card',
            '.sobre-cabecalho',
            '.sobre-card',
            '.contato-cabecalho',
            '.contato-conteudo',
            '.feedback-topo',
            '.feedback-recentes',
            '.portfolio-footer'
        ].join(',')
    );

    const movimentoReduzido = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    if (movimentoReduzido || !('IntersectionObserver' in window)) {
        return;
    }

    const observador = new IntersectionObserver(
        function (entradas, observer) {

            entradas.forEach(function (entrada) {

                if (entrada.isIntersecting) {
                    entrada.target.classList.add('reveal-visivel');
                } else {
                    entrada.target.classList.remove('reveal-visivel');
                }
            });

        },
        {
            threshold: 0.12,
            rootMargin: '0px 0px -40px 0px'
        }
    );

    elementosAnimados.forEach(function (elemento) {
        elemento.classList.add('portfolio-reveal');
        observador.observe(elemento);
    });

    /* Cards aparecem em sequência */
    const gruposDeCards = [
        '.modalidade-card',
        '.plano-card',
        '.unidade-card',
        '.sobre-card'
    ];

    gruposDeCards.forEach(function (seletor) {
        document.querySelectorAll(seletor).forEach(function (card, indice) {
            card.style.transitionDelay = (indice * 180) + 'ms';
        });
    });

});

document.addEventListener('DOMContentLoaded', function () {

    const carrossel = document.querySelector(
        '[data-feedback-carousel]'
    );

    if (!carrossel) return;

    const viewport = carrossel.querySelector(
        '.feedback-carousel-viewport'
    );

    const cards = Array.from(
        carrossel.querySelectorAll('.feedback-card')
    );

    const anterior = carrossel.querySelector(
        '.feedback-carousel-anterior'
    );

    const proxima = carrossel.querySelector(
        '.feedback-carousel-proxima'
    );

    const indicadores = document.querySelector(
        '.feedback-carousel-indicadores'
    );

    if (!viewport || !cards.length || !indicadores) return;

    let paginaAtual = 0;
    let quantidadePaginas = 1;

    function calcularPaginas() {

        const larguraCard = cards[0].getBoundingClientRect().width;
        const gap = parseFloat(
            getComputedStyle(
                carrossel.querySelector('.feedback-carousel-track')
            ).gap
        ) || 0;

        let visiveis = 3;

        if (window.innerWidth <= 600) {
            visiveis = 1;
        } else if (window.innerWidth <= 900) {
            visiveis = 2;
        }

        quantidadePaginas = Math.max(
            1,
            Math.ceil(cards.length / visiveis)
        );

        indicadores.innerHTML = '';

        for (let i = 0; i < quantidadePaginas; i++) {

            const indicador = document.createElement('button');

            indicador.type = 'button';
            indicador.className = 'feedback-carousel-indicador';

            indicador.setAttribute(
                'aria-label',
                'Mostrar página ' + (i + 1)
            );

            indicador.addEventListener('click', function () {
                irParaPagina(i);
            });

            indicadores.appendChild(indicador);
        }

        atualizarIndicadores();

    }

    function irParaPagina(pagina) {

        paginaAtual = Math.max(
            0,
            Math.min(pagina, quantidadePaginas - 1)
        );

        const maxScroll =
            viewport.scrollWidth - viewport.clientWidth;

        const destino = quantidadePaginas > 1
            ? maxScroll * (
                paginaAtual / (quantidadePaginas - 1)
            )
            : 0;

        viewport.scrollTo({
            left: destino,
            behavior: 'smooth'
        });

        atualizarIndicadores();

    }

    function atualizarIndicadores() {

        const botoes = indicadores.querySelectorAll(
            '.feedback-carousel-indicador'
        );

        botoes.forEach(function (botao, indice) {

            botao.classList.toggle(
                'ativo',
                indice === paginaAtual
            );

            botao.setAttribute(
                'aria-current',
                indice === paginaAtual ? 'true' : 'false'
            );

        });

        anterior.disabled = paginaAtual === 0;

        proxima.disabled =
            paginaAtual >= quantidadePaginas - 1;

    }

    anterior.addEventListener('click', function () {
        irParaPagina(paginaAtual - 1);
    });

    proxima.addEventListener('click', function () {
        irParaPagina(paginaAtual + 1);
    });

    viewport.addEventListener('scroll', function () {

        const maxScroll =
            viewport.scrollWidth - viewport.clientWidth;

        if (maxScroll <= 0) {
            paginaAtual = 0;
        } else {
            paginaAtual = Math.round(
                (viewport.scrollLeft / maxScroll) *
                (quantidadePaginas - 1)
            );
        }

        atualizarIndicadores();

    }, { passive: true });

    window.addEventListener('resize', calcularPaginas);

    calcularPaginas();

});

/* =========================================
   FORMULÁRIO DE CONTATO -> WHATSAPP
========================================= */

function enviarWhatsApp(event, telefoneZap) {
    event.preventDefault();

    if (!telefoneZap) {
        alert('O número de WhatsApp da academia ainda não foi configurado.');
        return;
    }

    const nome = document.getElementById('contato-nome').value;
    const email = document.getElementById('contato-email').value;
    const fone = document.getElementById('contato-telefone').value;
    const unidadeSelect = document.getElementById('contato-unidade');

    let unidadeNome = 'Nenhuma específica';

    if (unidadeSelect && unidadeSelect.selectedIndex > 0) {
        unidadeNome = unidadeSelect.options[unidadeSelect.selectedIndex].text.trim();
    }

    const mensagem = document.getElementById('contato-mensagem').value;
    const textoZap = `Olá! Meu nome é *${nome}*.\nE-mail: ${email}\nTelefone de Contato: ${fone}\nUnidade de interesse: *${unidadeNome}*\n\nMensagem: ${mensagem}`;
    const url = `https://wa.me/55${telefoneZap}?text=${encodeURIComponent(textoZap)}`;

    window.open(url, '_blank', 'noopener,noreferrer');
}


