// ============================================
// GYMFLOW - EXPORTAÇÃO DE CONTRATOS
// assets/js/contrato-pdf.js
// ============================================

(() => {

    'use strict';

    const texto = valor =>
        String(valor ?? '').trim() || 'Não informado';

    const dinheiro = valor =>
        new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(Number(valor || 0));

    function dataBrasileira(valor) {

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
            'contratoPdfMensagem'
        );

        if (!elemento) return;

        elemento.textContent = conteudo;

        elemento.dataset.tipo = tipo;

        elemento.hidden = false;
    }

    // ========================================
    // GERAR DOCUMENTO PDF
    // ========================================

    function gerarPdf(dados) {

        if (!window.jspdf?.jsPDF) {

            throw new Error(
                'Biblioteca jsPDF não carregada.'
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

        const contrato = dados.contrato;

        const clausulas = dados.clausulas;

        if (!contrato || !Array.isArray(clausulas)) {

            throw new Error(
                'Dados do contrato indisponíveis.'
            );
        }

        const margem = 15;

        const largura = 210;

        const area = largura - margem * 2;

        const escuro = [35, 35, 35];

        const cinza = [105, 105, 105];

        let y = 46;

        doc.setProperties({

            title: 'Contrato de prestação de serviços',

            subject: `Contrato nº ${contrato.id}`,

            creator: 'GymFlow'
        });

        // ====================================
        // SEÇÕES DE INFORMAÇÕES
        // ====================================

        function criarSecao(titulo, linhas) {

            if (y + 24 > 275) {

                doc.addPage();

                y = 46;
            }

            doc.autoTable({

                startY: y,

                margin: {

                    top: 46,

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

                    fontSize: 9.5,

                    cellPadding: 3,

                    textColor: escuro,

                    lineColor: [225, 225, 225],

                    lineWidth: 0.15,

                    overflow: 'linebreak',

                    valign: 'middle'
                },

                headStyles: {

                    fillColor: [239, 239, 239],

                    textColor: escuro,

                    fontStyle: 'bold',

                    fontSize: 10
                },

                columnStyles: {

                    0: {

                        cellWidth: 46,

                        fontStyle: 'bold',

                        textColor: cinza
                    },

                    1: {

                        cellWidth: 134
                    }
                }
            });

            y = doc.lastAutoTable.finalY + 7;
        }

        // ====================================
        // 1. IDENTIFICAÇÃO DO ALUNO
        // ====================================

        criarSecao(

            '1. Identificação do Contratante (Aluno)',

            [
                [
                    'Nome completo',
                    texto(contrato.nome_aluno)
                ],

                [
                    'CPF',
                    texto(contrato.cpf_aluno)
                ],

                [
                    'Telefone',
                    texto(contrato.tel_aluno)
                ]
            ]
        );

        // ====================================
        // 2. IDENTIFICAÇÃO DA ACADEMIA
        // ====================================

        criarSecao(

            '2. Identificação da Contratada (Academia)',

            [
                [
                    'Academia / Unidade',
                    texto(contrato.nome_filial)
                ],

                [
                    'CNPJ',
                    texto(contrato.cnpj_filial)
                ],

                [
                    'Endereço',
                    texto(contrato.end_filial)
                ]
            ]
        );

        // ====================================
        // 3. DADOS DO PLANO
        // ====================================

        criarSecao(

            '3. Dados do Plano Escolhido',

            [
                [
                    'Plano',
                    texto(contrato.nome_plano)
                ],

                [
                    'Categoria',
                    texto(contrato.categoria)
                ],

                [
                    'Data de início',
                    dataBrasileira(contrato.inicio)
                ],

                [
                    'Data de término',
                    dataBrasileira(contrato.fim)
                ],

                [
                    'Valor',
                    dinheiro(contrato.valor)
                ],

                [
                    'Desconto aplicado',
                    dinheiro(contrato.desconto)
                ]
            ]
        );

        // ====================================
        // 4. CLÁUSULAS E CONDIÇÕES
        // ====================================

        if (y + 35 > 275) {

            doc.addPage();

            y = 46;
        }

        doc.autoTable({

            startY: y,

            margin: {

                top: 46,

                bottom: 22,

                left: margem,

                right: margem
            },

            head: [[
                '4. Cláusulas e Condições Gerais'
            ]],

            body: clausulas.map(
                clausula => [String(clausula)]
            ),

            theme: 'plain',

            showHead: 'everyPage',

            rowPageBreak: 'avoid',

            styles: {

                font: 'helvetica',

                fontSize: 10,

                cellPadding: {

                    top: 4,

                    bottom: 4,

                    left: 3,

                    right: 3
                },

                textColor: escuro,

                overflow: 'linebreak',

                valign: 'top'
            },

            headStyles: {

                fillColor: [239, 239, 239],

                textColor: escuro,

                fontStyle: 'bold'
            }
        });

        // ====================================
        // 5. CAMPOS DE ASSINATURA
        // ====================================

        y = doc.lastAutoTable.finalY + 12;

        doc.setFont('helvetica', 'bold');

        doc.setFontSize(10);

        const nomeAluno = doc.splitTextToSize(
            texto(contrato.nome_aluno),
            80
        );

        const nomeAcademia = doc.splitTextToSize(
            texto(contrato.nome_filial),
            80
        );

        const totalLinhas = Math.max(
            nomeAluno.length,
            nomeAcademia.length
        );

        const alturaAssinaturas =
            32 + totalLinhas * 4.5;

        if (y + alturaAssinaturas > 275) {

            doc.addPage();

            y = 46;
        }

        doc.setDrawColor(70, 70, 70);

        doc.setLineWidth(.2);

        const linhaAssinatura = y + 18;

        // Aluno

        doc.line(

            margem,

            linhaAssinatura,

            margem + 80,

            linhaAssinatura
        );

        doc.setTextColor(...escuro);

        doc.text(

            nomeAluno,

            margem + 40,

            linhaAssinatura + 6,

            { align: 'center' }
        );

        // Academia

        doc.line(

            115,

            linhaAssinatura,

            195,

            linhaAssinatura
        );

        doc.text(

            nomeAcademia,

            155,

            linhaAssinatura + 6,

            { align: 'center' }
        );

        // Identificação das assinaturas

        doc.setFont('helvetica', 'normal');

        doc.setFontSize(9);

        doc.setTextColor(...cinza);

        const yIdentificacao =
            linhaAssinatura + 8 + totalLinhas * 4.5;

        doc.text(

            'Contratante (Aluno)',

            margem + 40,

            yIdentificacao,

            { align: 'center' }
        );

        doc.text(

            'Contratada (Academia)',

            155,

            yIdentificacao,

            { align: 'center' }
        );

        // ====================================
        // CABEÇALHO E RODAPÉ
        // ====================================

        const paginas = doc.getNumberOfPages();

        for (let pagina = 1; pagina <= paginas; pagina++) {

            doc.setPage(pagina);

            // Cabeçalho

            doc.setFont('helvetica', 'bold');

            doc.setFontSize(14);

            doc.setTextColor(...escuro);

            doc.text(

                'TERMO DE ADESÃO E CONTRATO',

                largura / 2,

                15,

                { align: 'center' }
            );

            doc.setFontSize(11);

            doc.text(

                'DE PRESTAÇÃO DE SERVIÇOS',

                largura / 2,

                21,

                { align: 'center' }
            );

            doc.setFont('helvetica', 'normal');

            doc.setFontSize(9);

            doc.setTextColor(...cinza);

            const unidade = doc.splitTextToSize(

                texto(contrato.nome_filial),

                area
            );

            doc.text(

                unidade,

                largura / 2,

                28,

                { align: 'center' }
            );

            doc.setFontSize(8);

            doc.text(

                `Contrato nº ${contrato.id}`,

                largura / 2,

                38,

                { align: 'center' }
            );

            doc.setDrawColor(60, 60, 60);

            doc.line(

                margem, 41,

                largura - margem, 41
            );

            // Rodapé

            doc.setDrawColor(210, 210, 210);

            doc.line(

                margem, 280,

                largura - margem, 280
            );

            doc.setTextColor(...cinza);

            doc.setFontSize(7.5);

            doc.text(

                `Documento gerado pelo GymFlow em ${texto(contrato.gerado_em)}.`,

                margem,

                285
            );

            doc.text(

                `${pagina}/${paginas}`,

                largura - margem,

                285,

                { align: 'right' }
            );
        }

        // ====================================
        // DOWNLOAD DIRETO
        // ====================================

        doc.save(

            `Contrato_GymFlow_${Number(contrato.id)}.pdf`
        );
    }

    // ========================================
    // SOLICITAR DADOS DO CONTRATO
    // ========================================

    async function baixarContrato(botao) {

        if (botao.disabled) return;

        const label = botao.querySelector(
            '[data-contrato-label]'
        );

        const labelOriginal = label?.textContent;

        botao.disabled = true;

        if (label) {

            label.textContent = 'Gerando PDF...';
        }

        mensagem(

            'Preparando seu documento.',

            'info'
        );

        try {

            const url = new URL(

                botao.dataset.contratoPdf,

                window.location.href
            );

            if (url.origin !== window.location.origin) {

                throw new Error('Endereço inválido.');
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

                    'Não foi possível consultar o contrato.'
                );
            }

            gerarPdf(dados);

            mensagem(

                'PDF gerado. Confira os downloads do navegador.',

                'sucesso'
            );

        } catch (erro) {

            mensagem(

                'Não foi possível gerar o PDF. ' +

                erro.message,

                'erro'
            );

            console.error(

                '[GymFlow][Contrato PDF]',

                erro
            );

        } finally {

            botao.disabled = false;

            if (label) {

                label.textContent = labelOriginal;
            }
        }
    }

    // ========================================
    // EVENTOS
    // ========================================

    document.addEventListener('click', evento => {

        const botao = evento.target.closest(
            '[data-contrato-pdf]'
        );

        if (!botao) return;

        baixarContrato(botao);
    });

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

            const ativo =
                url.searchParams.get('acao') === 'contratos';

            link.classList.toggle('active', ativo);
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