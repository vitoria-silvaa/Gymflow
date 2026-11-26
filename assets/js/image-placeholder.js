/* =============================================
   GYMFLOW - TRATAMENTO DE IMAGENS
============================================= */

(function () {

    'use strict';

    /* =========================================
       1. CONTROLE DE VISIBILIDADE
    ========================================= */

    function ocultarImagem(imagem, ocultar) {

        if (!imagem) return;

        imagem.classList.toggle(
            'gf-image-hidden',
            ocultar
        );
    }

    function configurarPlaceholder(
        elemento,
        mensagem,
        vazio
    ) {

        elemento.classList.add(
            'gf-image-placeholder'
        );

        elemento.classList.toggle(
            'is-empty',
            vazio
        );

        elemento.textContent = mensagem;
    }

    /* =========================================
       2. PORTFÓLIO PÚBLICO
    ========================================= */

    function configurarImagemPublica(
        container,
        classeVazia = ''
    ) {

        const imagem =
            container.querySelector('img');

        let placeholder = classeVazia
            ? container.querySelector(
                '.' + classeVazia
            )
            : null;

        // Reaproveitar o espaço vazio existente.

        if (!placeholder) {

            placeholder =
                document.createElement('span');

            if (classeVazia) {

                placeholder.classList.add(
                    classeVazia
                );
            }

            container.appendChild(placeholder);
        }

        configurarPlaceholder(
            placeholder,
            '',
            false
        );

        placeholder.setAttribute(
            'role',
            'img'
        );

        placeholder.setAttribute(
            'aria-label',
            'Imagem indisponível'
        );

        function mostrarFallback() {

            ocultarImagem(imagem, true);

            placeholder.hidden = false;
        }

        function mostrarImagem() {

            ocultarImagem(imagem, false);

            placeholder.hidden = true;
        }

        if (
            !imagem ||
            !imagem.getAttribute('src')?.trim()
        ) {

            mostrarFallback();

            return;
        }

        imagem.addEventListener(
            'error',
            mostrarFallback
        );

        imagem.addEventListener(
            'load',
            mostrarImagem
        );

        // Verificar imagens carregadas antes
        // da execução deste JavaScript.

        if (
            imagem.complete &&
            imagem.naturalWidth === 0
        ) {

            mostrarFallback();

        } else {

            mostrarImagem();
        }
    }

    function iniciarPortfolioPublico() {

        if (
            !document.querySelector(
                '.portfolio-hero'
            )
        ) {

            return;
        }

        const secoes = [
            ['.hero-slide', ''],
            ['.modalidade-imagem', ''],
            ['.unidade-imagem', ''],
            ['.parceiro-icone-imagem', ''],

            [
                '.sobre-imagem-wrap',
                'sobre-imagem-vazia'
            ],

            [
                '.contato-imagem-wrap',
                'contato-imagem-vazia'
            ],

            [
                '.feedback-imagem-wrap',
                'feedback-imagem-vazia'
            ]
        ];

        secoes.forEach(
            ([seletor, classeVazia]) => {

                document
                    .querySelectorAll(seletor)
                    .forEach(container => {

                        configurarImagemPublica(
                            container,
                            classeVazia
                        );
                    });
            }
        );
    }

    /* =========================================
       3. PRÉVIA DO PAINEL ADMINISTRATIVO
    ========================================= */

    const inputsConfigurados = new WeakSet();

    function configurarUpload(input) {

        if (inputsConfigurados.has(input)) {
            return;
        }

        const grupo = input.parentElement;

        if (!grupo) return;

        inputsConfigurados.add(input);

        let imagem = grupo.querySelector('img');

        const imagemSalva = imagem
            ? imagem.getAttribute('src') || ''
            : '';

        let frame;

        // Reaproveitar o container existente.

        if (imagem) {

            frame = imagem.parentElement;

        } else {

            frame = document.createElement('div');

            imagem = document.createElement('img');

            imagem.alt = 'Prévia da imagem';

            frame.appendChild(imagem);

            grupo.appendChild(frame);
        }

        frame.classList.add('gf-image-preview');

        const placeholder =
            document.createElement('div');

        frame.appendChild(placeholder);

        let urlTemporaria = null;

        function liberarUrlTemporaria() {

            if (urlTemporaria) {

                URL.revokeObjectURL(
                    urlTemporaria
                );

                urlTemporaria = null;
            }
        }

        function mostrarFallback(
            mensagem,
            vazio = false
        ) {

            ocultarImagem(imagem, true);

            configurarPlaceholder(
                placeholder,
                mensagem,
                vazio
            );

            placeholder.hidden = false;
        }

        function mostrarImagem() {

            if (!imagem.getAttribute('src')) {
                return;
            }

            ocultarImagem(imagem, false);

            placeholder.hidden = true;
        }

        function verificarImagem() {

            if (!imagem.getAttribute('src')) {

                mostrarFallback(
                    'Nenhuma imagem selecionada',
                    true
                );

                return;
            }

            if (imagem.complete) {

                if (imagem.naturalWidth > 0) {

                    mostrarImagem();

                } else {

                    mostrarFallback(
                        'Imagem indisponível'
                    );
                }
            }
        }

        imagem.addEventListener(
            'load',
            mostrarImagem
        );

        imagem.addEventListener(
            'error',
            function () {

                if (!imagem.getAttribute('src')) {
                    return;
                }

                mostrarFallback(
                    'Não foi possível carregar a imagem'
                );
            }
        );

        input.addEventListener(
            'change',
            function () {

                liberarUrlTemporaria();

                const arquivo = input.files?.[0];

                if (!arquivo) {

                    if (imagemSalva) {

                        imagem.src = imagemSalva;

                    } else {

                        imagem.removeAttribute('src');
                    }

                    verificarImagem();

                    return;
                }

                // A prévia não modifica
                // os dados enviados ao servidor.

                if (
                    arquivo.type &&
                    !arquivo.type.startsWith('image/')
                ) {

                    imagem.removeAttribute('src');

                    mostrarFallback(
                        'Formato de imagem inválido'
                    );

                    return;
                }

                mostrarFallback(
                    'Carregando imagem...'
                );

                try {

                    urlTemporaria =
                        URL.createObjectURL(arquivo);

                    imagem.src = urlTemporaria;

                    verificarImagem();

                } catch (erro) {

                    mostrarFallback(
                        'Não foi possível gerar a prévia'
                    );
                }
            }
        );

        verificarImagem();
    }

    function iniciarPortfolioAdmin() {

        const formulario =
            document.getElementById('portfolioForm');

        if (!formulario) return;

        formulario
            .querySelectorAll('input[type="file"]')
            .forEach(configurarUpload);

        // Detectar novos slides, modalidades
        // e unidades adicionados dinamicamente.

        const observador = new MutationObserver(
            registros => {

                registros.forEach(registro => {

                    registro.addedNodes.forEach(no => {

                        if (
                            no.nodeType !== Node.ELEMENT_NODE
                        ) {

                            return;
                        }

                        if (
                            no.matches(
                                'input[type="file"]'
                            )
                        ) {

                            configurarUpload(no);
                        }

                        no.querySelectorAll(
                            'input[type="file"]'
                        ).forEach(configurarUpload);
                    });
                });
            }
        );

        observador.observe(
            formulario,
            {
                childList: true,
                subtree: true
            }
        );
    }

    /* =========================================
       4. PROTEGER LOGOTIPOS DA SIDEBAR
    ========================================= */

    function iniciarLogotipos() {

        document
            .querySelectorAll('.sidebar-logo-imagem')
            .forEach(imagem => {

                function ocultarLogo() {

                    ocultarImagem(imagem, true);
                }

                imagem.addEventListener(
                    'error',
                    ocultarLogo
                );

                if (
                    imagem.complete &&
                    imagem.naturalWidth === 0
                ) {

                    ocultarLogo();
                }
            });
    }

    /* =========================================
       5. INICIALIZAÇÃO
    ========================================= */

    function iniciar() {

        iniciarPortfolioPublico();

        iniciarPortfolioAdmin();

        iniciarLogotipos();
    }

    if (document.readyState === 'loading') {

        document.addEventListener(
            'DOMContentLoaded',
            iniciar,
            { once: true }
        );

    } else {

        iniciar();
    }

})();