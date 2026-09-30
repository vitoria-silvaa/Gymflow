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
