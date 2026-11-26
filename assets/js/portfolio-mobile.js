/* GYMFLOW - MENU MOBILE PREMIUM */

(function () {
    'use strict';

    function iniciarMenuMobile() {

        const header = document.querySelector(
            '.portfolio-header'
        );

        const hero = document.querySelector(
            '.portfolio-hero'
        );

        if (
            !header ||
            !hero ||
            header.querySelector('.gf-mobile-bar') ||
            typeof HTMLDialogElement === 'undefined'
        ) {
            return;
        }

        const logoOriginal = header.querySelector(
            '.portfolio-logo'
        );

        const navegacaoOriginal = header.querySelector(
            '.portfolio-nav'
        );

        const acoesOriginais = header.querySelector(
            '.portfolio-acoes'
        );

        if (
            !logoOriginal ||
            !navegacaoOriginal ||
            !acoesOriginais
        ) {
            return;
        }

        const linksNavegacao = Array.from(
            navegacaoOriginal.querySelectorAll('a[href]')
        );

        const linksAcoes = Array.from(
            acoesOriginais.querySelectorAll('a[href]')
        );

        if (!linksNavegacao.length) {
            return;
        }

        const mobile = window.matchMedia(
            '(max-width: 900px)'
        );

        /* CORES PERSONALIZADAS */

        const estilos = getComputedStyle(header);

        const primaria = estilos
            .getPropertyValue('--portfolio-primary')
            .trim() || '#ffb000';

        const secundaria = estilos
            .getPropertyValue('--portfolio-secondary')
            .trim() || '#0b0b0b';

        function corContraste(cor) {

            let hexadecimal = cor.trim().replace('#', '');

            if (/^[0-9a-f]{3}$/i.test(hexadecimal)) {
                hexadecimal = hexadecimal
                    .split('')
                    .map(c => c + c)
                    .join('');
            }

            if (!/^[0-9a-f]{6}$/i.test(hexadecimal)) {
                return '#ffffff';
            }

            const canais = [
                parseInt(hexadecimal.slice(0, 2), 16),
                parseInt(hexadecimal.slice(2, 4), 16),
                parseInt(hexadecimal.slice(4, 6), 16)
            ].map(valor => {

                const canal = valor / 255;

                return canal <= 0.04045
                    ? canal / 12.92
                    : Math.pow(
                        (canal + 0.055) / 1.055,
                        2.4
                    );
            });

            const luminancia =
                0.2126 * canais[0] +
                0.7152 * canais[1] +
                0.0722 * canais[2];

            return (luminancia + 0.05) / 0.05 >=
                1.05 / (luminancia + 0.05)
                    ? '#000000'
                    : '#ffffff';
        }

        document.body.style.setProperty(
            '--gf-mobile-text',
            corContraste(secundaria)
        );

        document.body.style.setProperty(
            '--gf-on-primary',
            corContraste(primaria)
        );

        /* CRIAR ELEMENTOS */

        function criarElemento(tag, classe) {

            const elemento = document.createElement(tag);

            if (classe) {
                elemento.className = classe;
            }

            return elemento;
        }

        function copiarLink(original, classe, texto) {

            const link = criarElemento('a', classe);

            link.setAttribute(
                'href',
                original.getAttribute('href')
            );

            link.textContent = texto ||
                original.textContent.trim();

            if (original.hasAttribute('target')) {
                link.target = original.target;
            }

            if (original.hasAttribute('rel')) {
                link.rel = original.rel;
            }

            return link;
        }

        /* LOGO E NOME DA ACADEMIA */

        function criarMarca() {

            const linkOriginal =
                logoOriginal.matches('a[href]')
                    ? logoOriginal
                    : logoOriginal.querySelector('a[href]');

            const marca = criarElemento(
                linkOriginal ? 'a' : 'div',
                'gf-mobile-brand'
            );

            if (linkOriginal) {

                marca.setAttribute(
                    'href',
                    linkOriginal.getAttribute('href')
                );
            }

            const imagemOriginal =
                logoOriginal.querySelector('img');

            if (imagemOriginal) {

                const imagem = document.createElement('img');

                imagem.src =
                    imagemOriginal.currentSrc ||
                    imagemOriginal.src;

                imagem.alt = '';
                imagem.decoding = 'async';

                marca.appendChild(imagem);
            }

            const titulo = logoOriginal.querySelector(
                'h1, h2'
            );

            const nome = document.createElement('span');

            nome.textContent = (
                titulo
                    ? titulo.textContent
                    : logoOriginal.textContent
            ).trim() || 'GymFlow';

            marca.appendChild(nome);

            return marca;
        }

        /* ÍCONES SVG */

        function criarIcone(tipo) {

            const ns = 'http://www.w3.org/2000/svg';

            const svg = document.createElementNS(
                ns,
                'svg'
            );

            svg.setAttribute('viewBox', '0 0 24 24');
            svg.setAttribute('aria-hidden', 'true');

            const linhas = tipo === 'menu'
                ? [
                    'M4 6H20',
                    'M4 12H20',
                    'M4 18H20'
                ]
                : [
                    'M6 6L18 18',
                    'M18 6L6 18'
                ];

            linhas.forEach(d => {

                const path = document.createElementNS(
                    ns,
                    'path'
                );

                path.setAttribute('d', d);

                svg.appendChild(path);
            });

            return svg;
        }

        /* CABEÇALHO MOBILE */

        const barra = criarElemento(
            'div',
            'gf-mobile-bar'
        );

        barra.appendChild(criarMarca());

        const acoes = criarElemento(
            'div',
            'gf-mobile-actions'
        );

        const acesso = linksAcoes.find(link => {

            const href = link.getAttribute('href') || '';

            return (
                href.includes('acao=login') ||
                href.includes('PortalAlunoController.php') ||
                href.includes('DashboardController.php')
            );
        });

        if (acesso) {

            const href = acesso.getAttribute('href');

            let rotulo = 'Entrar';

            if (href.includes('PortalAlunoController.php')) {
                rotulo = 'Minha área';
            } else if (href.includes('DashboardController.php')) {
                rotulo = 'Painel';
            }

            const linkAcesso = copiarLink(
                acesso,
                'gf-mobile-login',
                rotulo
            );

            linkAcesso.setAttribute(
                'aria-label',
                acesso.textContent.trim()
            );

            acoes.appendChild(linkAcesso);
        }

        const botaoMenu = criarElemento(
            'button',
            'gf-mobile-toggle'
        );

        botaoMenu.type = 'button';

        botaoMenu.setAttribute(
            'aria-label',
            'Abrir menu'
        );

        botaoMenu.setAttribute(
            'aria-expanded',
            'false'
        );

        botaoMenu.setAttribute(
            'aria-controls',
            'gf-mobile-menu'
        );

        botaoMenu.setAttribute(
            'aria-haspopup',
            'dialog'
        );

        botaoMenu.appendChild(criarIcone('menu'));

        acoes.appendChild(botaoMenu);
        barra.appendChild(acoes);

        /* PAINEL LATERAL */

        const menu = criarElemento(
            'dialog',
            'gf-mobile-menu'
        );

        menu.id = 'gf-mobile-menu';

        menu.setAttribute(
            'aria-label',
            'Menu de navegação'
        );

        menu.style.setProperty(
            '--portfolio-primary',
            primaria
        );

        menu.style.setProperty(
            '--portfolio-secondary',
            secundaria
        );

        const conteudo = criarElemento(
            'div',
            'gf-mobile-menu-content'
        );

        const topo = criarElemento(
            'div',
            'gf-mobile-menu-header'
        );

        topo.appendChild(criarMarca());

        const botaoFechar = criarElemento(
            'button',
            'gf-mobile-close'
        );

        botaoFechar.type = 'button';

        botaoFechar.setAttribute(
            'aria-label',
            'Fechar menu'
        );

        botaoFechar.appendChild(
            criarIcone('fechar')
        );

        topo.appendChild(botaoFechar);

        conteudo.appendChild(topo);

        /* NAVEGAÇÃO */

        const navegacao = criarElemento(
            'nav',
            'gf-mobile-menu-nav'
        );

        navegacao.setAttribute(
            'aria-label',
            'Seções do site'
        );

        linksNavegacao.forEach(original => {

            navegacao.appendChild(
                copiarLink(original, '')
            );
        });

        conteudo.appendChild(navegacao);

        /* LOGIN, MATRÍCULA E SAÍDA */

        const menuAcoes = criarElemento(
            'div',
            'gf-mobile-menu-actions'
        );

        linksAcoes.forEach(original => {

            menuAcoes.appendChild(
                copiarLink(original, '')
            );
        });

        if (menuAcoes.children.length) {
            conteudo.appendChild(menuAcoes);
        }

        menu.appendChild(conteudo);

        header.appendChild(barra);
        document.body.appendChild(menu);

        /* ABRIR E FECHAR */

        function abrirMenu() {

            if (!mobile.matches || menu.open) {
                return;
            }

            menu.showModal();

            document.documentElement.classList.add(
                'gf-menu-aberto'
            );

            botaoMenu.setAttribute(
                'aria-expanded',
                'true'
            );

            botaoFechar.focus({
                preventScroll: true
            });
        }

        function fecharMenu() {

            if (menu.open) {
                menu.close();
            }
        }

        botaoMenu.addEventListener(
            'click',
            abrirMenu
        );

        botaoFechar.addEventListener(
            'click',
            fecharMenu
        );

        menu.addEventListener('close', function () {

            document.documentElement.classList.remove(
                'gf-menu-aberto'
            );

            botaoMenu.setAttribute(
                'aria-expanded',
                'false'
            );

            if (mobile.matches) {

                botaoMenu.focus({
                    preventScroll: true
                });
            }
        });

        /* FECHAR AO TOCAR FORA */

        menu.addEventListener('click', function (event) {

            if (event.target !== menu) {
                return;
            }

            const limites = menu.getBoundingClientRect();

            const fora =
                event.clientX < limites.left ||
                event.clientX > limites.right ||
                event.clientY < limites.top ||
                event.clientY > limites.bottom;

            if (fora) {
                fecharMenu();
            }
        });

        /* FECHAR AO SELECIONAR UM LINK */

        menu.querySelectorAll('a').forEach(link => {

            link.addEventListener(
                'click',
                fecharMenu
            );
        });

        /* RESTAURAR DESKTOP */

        mobile.addEventListener('change', function (event) {

            if (!event.matches) {
                fecharMenu();
            }
        });

        document.body.classList.add('gf-premium');
    }

    if (document.readyState === 'loading') {

        document.addEventListener(
            'DOMContentLoaded',
            iniciarMenuMobile
        );

    } else {

        iniciarMenuMobile();
    }

})();