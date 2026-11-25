// ============================================
// GYMFLOW - RECIBO DE PAGAMENTO
// assets/js/recibo-pdf.js
// ============================================

(() => {

    'use strict';

    const texto = valor =>
        String(valor ?? '').trim() || 'Não informado';

    const dinheiro = valor =>
        new Intl.NumberFormat('pt-BR', {

            style: 'currency',

            currency: 'BRL'

        }).format(Number(valor));

    // ========================================
    // FORMATAR DATA
    // ========================================

    function formatarData(valor) {

        const partes = String(valor ?? '')
            .match(/^(\d{4})-(\d{2})-(\d{2})/);

        if (!partes) {

            return 'Não informado';
        }

        return `${partes[3]}/${partes[2]}/${partes[1]}`;
    }

    // ========================================
    // MENSAGENS DA INTERFACE
    // ========================================

    function mensagem(conteudo, tipo) {

        const elemento = document.getElementById(
            'reciboMensagem'
        );

        if (!elemento) return;

        elemento.textContent = conteudo;

        elemento.dataset.tipo = tipo;

        elemento.hidden = false;
    }

    // ========================================
    // GERAR RECIBO
    // ========================================

    function gerarRecibo(recibo) {

        if (!window.jspdf?.jsPDF) {

            throw new Error(
                'Biblioteca jsPDF não carregada.'
            );
        }

        if (
            !recibo ||
            recibo.status !== 'Pago'
        ) {

            throw new Error(
                'Esta fatura não possui pagamento confirmado.'
            );
        }

        const valor = Number(recibo.valor);

        if (
            !Number.isFinite(valor) ||
            valor < 0 ||
            recibo.valor == null
        ) {

            throw new Error(
                'Valor da fatura inválido.'
            );
        }

        const { jsPDF } = window.jspdf;

        const doc = new jsPDF({

            orientation: 'portrait',

            unit: 'mm',

            format: 'a4',

            compress: true
        });

        if (typeof doc.autoTable !== 'function') {

            throw new Error(
                'Biblioteca AutoTable não carregada.'
            );
        }

        const largura = 210;

        const margem = 16;

        const area = largura - margem * 2;

        const escuro = [30, 30, 30];

        const cinza = [105, 105, 105];

        const verde = [21, 128, 61];

        doc.setProperties({

            title: 'Recibo de pagamento',

            subject: `Fatura nº ${recibo.id}`,

            creator: 'GymFlow'
        });

        // ====================================
        // VALOR DO PAGAMENTO
        // ====================================

        doc.setFillColor(240, 253, 244);

        doc.roundedRect(

            margem,

            40,

            area,

            34,

            2,

            2,

            'F'
        );

        doc.setFont('helvetica', 'bold');

        doc.setFontSize(10);

        doc.setTextColor(...verde);

        doc.text(

            'PAGAMENTO REGISTRADO',

            margem + 6,

            50
        );

        doc.setFontSize(23);

        doc.text(

            dinheiro(valor).replace(/\u00a0/g, ' '),

            margem + 6,

            65
        );

        // ====================================
        // FUNÇÃO DE SEÇÕES
        // ====================================

        let y = 83;

        function secao(titulo, linhas) {

            if (y + 24 > 275) {

                doc.addPage();

                y = 40;
            }

            doc.autoTable({

                startY: y,

                margin: {

                    top: 40,

                    bottom: 22,

                    left: margem,

                    right: margem
                },

                head: [[

                    {
                        content: titulo,
                        colSpan: 2
                    }

                ]],

                body: linhas,

                theme: 'grid',

                showHead: 'everyPage',

                rowPageBreak: 'avoid',

                styles: {

                    font: 'helvetica',

                    fontSize: 10,

                    cellPadding: 3,

                    overflow: 'linebreak',

                    textColor: escuro,

                    lineColor: [225, 225, 225],

                    lineWidth: .15
                },

                headStyles: {

                    fillColor: [239, 239, 239],

                    textColor: escuro,

                    fontStyle: 'bold'
                },

                columnStyles: {

                    0: {

                        cellWidth: 49,

                        fontStyle: 'bold',

                        textColor: cinza
                    },

                    1: {

                        cellWidth: 129
                    }
                }
            });

            y = doc.lastAutoTable.finalY + 7;
        }

        // ====================================
        // IDENTIFICAÇÃO DA ACADEMIA
        // ====================================

        secao(

            '1. Identificação da academia',

            [

                [
                    'Unidade',
                    texto(recibo.nome_filial)
                ],

                [
                    'CNPJ',
                    texto(recibo.cnpj_filial)
                ],

                [
                    'Endereço',
                    texto(recibo.endereco_filial)
                ]
            ]
        );

        // ====================================
        // IDENTIFICAÇÃO DO ALUNO
        // ====================================

        secao(

            '2. Identificação do aluno',

            [

                [
                    'Nome completo',
                    texto(recibo.nome_aluno)
                ],

                [
                    'Plano',
                    texto(recibo.nome_plano)
                ],

                [
                    'Matrícula nº',
                    texto(recibo.matricula_id)
                ]
            ]
        );

        // ====================================
        // DADOS DO PAGAMENTO
        // ====================================

        secao(

            '3. Informações do pagamento',

            [

                [
                    'Fatura nº',
                    texto(recibo.id)
                ],

                [
                    'Vencimento',
                    formatarData(recibo.vencimento)
                ],

                [
                    'Data do pagamento',
                    formatarData(recibo.pago_em)
                ],

                [
                    'Forma de pagamento',
                    texto(recibo.forma_pagamento)
                ],

                [
                    'Situação',
                    'Pago'
                ]
            ]
        );

        // ====================================
        // DECLARAÇÃO DO DOCUMENTO
        // ====================================

        if (y + 25 > 275) {

            doc.addPage();

            y = 40;
        }

        doc.setFont('helvetica', 'normal');

        doc.setFontSize(9);

        doc.setTextColor(...cinza);

        const declaracao = doc.splitTextToSize(

            'Este documento registra o pagamento da fatura ' +
            'identificada acima, conforme as informações ' +
            'cadastradas no sistema financeiro da academia. ' +
            'Não substitui documento fiscal.',

            area
        );

        doc.text(

            declaracao,

            margem,

            y + 3
        );

        // ====================================
        // CABEÇALHO E RODAPÉ
        // ====================================

        const paginas = doc.getNumberOfPages();

        for (
            let pagina = 1;
            pagina <= paginas;
            pagina++
        ) {

            doc.setPage(pagina);

            // Cabeçalho

            doc.setFont('helvetica', 'bold');

            doc.setFontSize(17);

            doc.setTextColor(...escuro);

            doc.text(

                'RECIBO DE PAGAMENTO',

                margem,

                16
            );

            doc.setFont('helvetica', 'normal');

            doc.setFontSize(10);

            doc.setTextColor(...cinza);

            doc.text(

                `Mensalidade - Fatura nº ${recibo.id}`,

                margem,

                24
            );

            doc.setDrawColor(60, 60, 60);

            doc.line(

                margem,

                32,

                largura - margem,

                32
            );

            // Rodapé

            doc.setDrawColor(210, 210, 210);

            doc.line(

                margem,

                280,

                largura - margem,

                280
            );

            doc.setFontSize(8);

            doc.setTextColor(...cinza);

            doc.text(

                `Gerado pelo GymFlow em ${texto(recibo.gerado_em)}`,

                margem,

                286
            );

            doc.text(

                `${pagina}/${paginas}`,

                largura - margem,

                286,

                { align: 'right' }
            );
        }

        // ====================================
        // DOWNLOAD DIRETO
        // ====================================

        doc.save(

            `Recibo_Fatura_${Number(recibo.id)}.pdf`
        );
    }

    // ========================================
    // CONSULTAR RECIBO NO SERVIDOR
    // ========================================

    async function baixarRecibo(botao) {

        if (botao.disabled) return;

        const label = botao.querySelector(
            '[data-recibo-label]'
        );

        const original = label?.textContent;

        botao.disabled = true;

        if (label) {

            label.textContent = 'Gerando PDF...';
        }

        mensagem(

            'Preparando seu recibo.',

            'info'
        );

        try {

            const url = new URL(

                botao.dataset.reciboUrl,

                window.location.href
            );

            if (url.origin !== window.location.origin) {

                throw new Error(
                    'Endereço inválido.'
                );
            }

            const resposta = await fetch(url.href, {

                method: 'GET',

                credentials: 'same-origin',

                cache: 'no-store',

                headers: {

                    Accept: 'application/json'
                }
            });

            const tipo = resposta.headers.get(
                'content-type'
            ) || '';

            if (!tipo.includes('application/json')) {

                throw new Error(

                    'Resposta inválida. Confira sua sessão.'
                );
            }

            const dados = await resposta.json();

            if (!resposta.ok || !dados.sucesso) {

                throw new Error(

                    dados.mensagem ||

                    'Não foi possível consultar o recibo.'
                );
            }

            gerarRecibo(dados.recibo);

            mensagem(

                'Recibo gerado. Confira os downloads do navegador.',

                'sucesso'
            );

        } catch (erro) {

            mensagem(

                'Não foi possível gerar o PDF. ' +

                erro.message,

                'erro'
            );

            console.error(

                '[GymFlow][Recibo PDF]',

                erro
            );

        } finally {

            botao.disabled = false;

            if (label) {

                label.textContent = original;
            }
        }
    }

    // ========================================
    // EVENTOS
    // ========================================

    document.addEventListener('click', evento => {

        const botao = evento.target.closest(
            '[data-recibo-url]'
        );

        if (!botao) return;

        baixarRecibo(botao);
    });

    // ========================================
    // MENU ATIVO
    // ========================================

    function marcarMenuAtivo() {

        document.querySelectorAll(
            '.sidebar-nav a'
        ).forEach(link => {

            const href = link.getAttribute('href');

            if (!href) return;

            const url = new URL(

                href,

                window.location.href
            );

            link.classList.toggle(

                'active',

                url.searchParams.get('acao') === 'faturas'
            );
        });
    }

    if (document.readyState === 'loading') {

        document.addEventListener(

            'DOMContentLoaded',

            marcarMenuAtivo
        );

    } else {

        marcarMenuAtivo();
    }

})();