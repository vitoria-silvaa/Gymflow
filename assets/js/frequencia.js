// =============================================
// GYMFLOW - FREQUÊNCIA DE TREINOS
// =============================================

(() => {

    'use strict';

    const root = document.getElementById(
        'gf-frequencia'
    );

    if (!root) return;

    const q = selector =>
        root.querySelector(selector);

    const META = 4;

    let hoje = root.dataset.hoje;

    let mes = hoje.slice(0, 7);

    let selecionado = hoje;

    let dias = {};

    let requisicao = 0;

    // =========================================
    // FORMATADORES DE DATA
    // =========================================

    const dataUTC = iso =>
        new Date(iso + 'T12:00:00Z');

    const formatoMes = new Intl.DateTimeFormat(
        'pt-BR',
        {
            month: 'long',
            year: 'numeric',
            timeZone: 'UTC'
        }
    );

    const formatoDia = new Intl.DateTimeFormat(
        'pt-BR',
        {
            dateStyle: 'full',
            timeZone: 'UTC'
        }
    );

    // =========================================
    // CLASSIFICAR FREQUÊNCIA
    // =========================================

    function status(n) {

        if (n >= META) {

            return {
                texto: 'Treino concluído',
                classe: 'gf-completo'
            };
        }

        if (n > 0) {

            return {
                texto: 'Treino parcialmente concluído',
                classe: 'gf-parcial'
            };
        }

        return {
            texto: 'Sem treino registrado',
            classe: 'gf-vazio'
        };
    }

    // =========================================
    // NAVEGAÇÃO MENSAL
    // =========================================

    function atualizarNav() {

        q('[data-mes]').textContent =
            formatoMes.format(
                dataUTC(mes + '-01')
            );

        q('[data-nav="1"]').disabled =
            mes >= hoje.slice(0, 7);

        q('[data-nav="-1"]').disabled =
            mes <= '2000-01';
    }

    // =========================================
    // INDICADORES MENSAIS
    // =========================================

    function atualizarIndicadores() {

        const registros = Object.values(dias);

        const completos = registros.filter(
            itens => itens.length >= META
        ).length;

        const parciais = registros.filter(
            itens =>
                itens.length > 0 &&
                itens.length < META
        ).length;

        q('[data-total="completo"]')
            .textContent = completos;

        q('[data-total="parcial"]')
            .textContent = parciais;

        q('[data-total="ativos"]')
            .textContent = completos + parciais;
    }

    // =========================================
    // DETALHES DA DATA SELECIONADA
    // =========================================

    function detalhes() {

        const itens =
            dias[selecionado] || [];

        const n = itens.length;

        const estado = status(n);

        q('[data-detalhes]').hidden = false;

        q('[data-data]').textContent =
            formatoDia.format(
                dataUTC(selecionado)
            );

        const elementoStatus =
            q('[data-status]');

        elementoStatus.textContent =
            estado.texto;

        elementoStatus.className =
            `gf-status ${estado.classe}`;

        q('[data-contagem]').textContent =
            `${n}/${META}`;

        q('[data-quantidade]').textContent =
            n >= META

                ? 'Meta diária atingida!'

                : `Faltam ${META - n} exercícios para atingir a meta diária.`;

        const progresso =
            q('[data-progresso]');

        progresso.value =
            Math.min(n, META);

        progresso.classList.toggle(
            'gf-completo',
            n >= META
        );

        // EXERCÍCIOS REALIZADOS

        const lista =
            q('[data-exercicios]');

        lista.replaceChildren();

        q('[data-vazio]').hidden =
            itens.length > 0;

        itens.forEach(item => {

            const li =
                document.createElement('li');

            const nome =
                document.createElement('span');

            nome.textContent =
                item.nome;

            li.append(nome);

            if (item.execucoes > 1) {

                const execucoes =
                    document.createElement('span');

                execucoes.className =
                    'gf-exercise-executions';

                execucoes.textContent =
                    `${item.execucoes} execuções`;

                li.append(execucoes);
            }

            lista.append(li);
        });

        // DESTACAR DATA SELECIONADA

        root.querySelectorAll(
            '[data-dia]'
        ).forEach(botao => {

            botao.setAttribute(

                'aria-pressed',

                String(
                    botao.dataset.dia === selecionado
                )
            );
        });
    }

    // =========================================
    // RENDERIZAR CALENDÁRIO
    // =========================================

    function render() {

        atualizarIndicadores();

        const grid =
            q('[data-calendario]');

        grid.replaceChildren();

        const semana = [

            'Dom',
            'Seg',
            'Ter',
            'Qua',
            'Qui',
            'Sex',
            'Sáb'
        ];

        semana.forEach(nome => {

            const elemento =
                document.createElement('span');

            elemento.className =
                'gf-week';

            elemento.textContent =
                nome;

            grid.append(elemento);
        });

        const [ano, m] =
            mes.split('-').map(Number);

        const primeiroDia = new Date(
            Date.UTC(ano, m - 1, 1)
        ).getUTCDay();

        const totalDias = new Date(
            Date.UTC(ano, m, 0)
        ).getUTCDate();

        // Espaços anteriores ao primeiro dia.

        for (
            let i = 0;
            i < primeiroDia;
            i++
        ) {

            grid.append(
                document.createElement('span')
            );
        }

        // Criar os dias do mês.

        for (
            let d = 1;
            d <= totalDias;
            d++
        ) {

            const iso =
                `${mes}-${String(d).padStart(2, '0')}`;

            const quantidade =
                (dias[iso] || []).length;

            const estado =
                status(quantidade);

            const botao =
                document.createElement('button');

            botao.type = 'button';

            botao.className = 'gf-day';

            botao.textContent = d;

            botao.dataset.dia = iso;

            botao.disabled = iso > hoje;

            if (quantidade > 0) {

                botao.classList.add(
                    estado.classe
                );
            }

            if (iso === hoje) {

                botao.classList.add(
                    'gf-hoje'
                );
            }

            const descricao = botao.disabled

                ? 'Data futura'

                : estado.texto;

            botao.setAttribute(

                'aria-label',

                `${formatoDia.format(dataUTC(iso))}: ${descricao}`
            );

            grid.append(botao);
        }

        detalhes();
    }

    // =========================================
    // CONSULTAR FREQUÊNCIA NO BANCO
    // =========================================

    async function carregar() {

        const id = ++requisicao;

        atualizarNav();

        q('[data-aviso]').textContent =
            'Carregando frequência...';

        q('[data-retry]').hidden = true;

        q('[data-detalhes]').hidden = true;

        q('[data-calendario]').replaceChildren();

        root.querySelectorAll(
            '[data-total]'
        ).forEach(elemento => {

            elemento.textContent = '—';
        });

        try {

            const url = new URL(
                root.dataset.url,
                window.location.href
            );

            url.searchParams.set(
                'mes',
                mes
            );

            const resposta = await fetch(
                url,
                {
                    method: 'GET',

                    credentials: 'same-origin',

                    cache: 'no-store',

                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            const resultado =
                await resposta.json();

            if (
                !resposta.ok ||
                !resultado.sucesso
            ) {

                throw new Error(
                    resultado.mensagem ||
                    'Consulta indisponível.'
                );
            }

            if (id !== requisicao) return;

            hoje = resultado.hoje;

            dias = resultado.dias || {};

            atualizarNav();

            render();

            q('[data-aviso]').textContent = '';

        } catch (erro) {

            if (id !== requisicao) return;

            q('[data-aviso]').textContent =
                'Não foi possível carregar a frequência.';

            q('[data-retry]').hidden = false;
        }
    }

    // =========================================
    // ALTERAR MÊS
    // =========================================

    function mudarMes(delta) {

        const [ano, m] =
            mes.split('-').map(Number);

        const novo = new Date(

            Date.UTC(
                ano,
                m - 1 + delta,
                1
            )

        ).toISOString().slice(0, 7);

        if (
            novo > hoje.slice(0, 7) ||
            novo < '2000-01'
        ) {

            return;
        }

        mes = novo;

        selecionado =
            mes === hoje.slice(0, 7)

                ? hoje

                : mes + '-01';

        carregar();
    }

    // =========================================
    // EVENTOS
    // =========================================

    root.addEventListener('click', evento => {

        const botao = evento.target.closest(
            'button'
        );

        if (
            !botao ||
            botao.disabled
        ) {

            return;
        }

        if (botao.hasAttribute('data-nav')) {

            mudarMes(
                Number(botao.dataset.nav)
            );

            return;
        }

        if (botao.hasAttribute('data-dia')) {

            selecionado = botao.dataset.dia;

            detalhes();

            return;
        }

        if (botao.hasAttribute('data-retry')) {

            carregar();
        }
    });

    // Atualizar ao retornar para a aba.

    document.addEventListener(
        'visibilitychange',
        () => {

            if (!document.hidden) {

                carregar();
            }
        }
    );

    // Atualizar ao retornar pelo navegador.

    window.addEventListener(
        'pageshow',
        evento => {

            if (evento.persisted) {

                carregar();
            }
        }
    );

    // =========================================
    // INICIALIZAÇÃO
    // =========================================

    carregar();

})();