// =============================================
// GYMFLOW - DOWNLOAD DA FICHA DE TREINO
// assets/js/ficha-treino-pdf.js
// =============================================

(() => {

    'use strict';

    function baixar() {

        const aviso = document.getElementById('tgPdfError');

        if (aviso) {
            aviso.hidden = true;
        }

        try {

            // VALIDAR BIBLIOTECAS

            if (!window.jspdf?.jsPDF) {
                throw new Error('Biblioteca jsPDF não carregada.');
            }

            const elemento = document.getElementById('tgPdfData');

            if (!elemento) {
                throw new Error('Dados da ficha indisponíveis.');
            }

            const dados = JSON.parse(elemento.textContent);

            const { jsPDF } = window.jspdf;

            const doc = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4',
                compress: true
            });

            if (typeof doc.autoTable !== 'function') {
                throw new Error('Biblioteca AutoTable não carregada.');
            }

            // CONFIGURAÇÕES VISUAIS

            const margem = 14;
            const largura = 210;
            const area = largura - margem * 2;

            const escuro = [29, 29, 29];
            const dourado = [201, 162, 39];

            const texto = valor =>
                String(valor ?? '').trim() || '-';

            const totalSeries = dados.exercicios.reduce(
                (total, e) => total + Number(e.series || 0),
                0
            );

            doc.setProperties({
                title: 'Ficha de treinamento',
                subject: 'Prescrição de exercícios',
                creator: 'GymFlow'
            });

            // =====================================
            // INFORMAÇÕES DO ALUNO E DA FICHA
            // =====================================

            doc.autoTable({

                startY: 43,

                margin: {
                    top: 43,
                    bottom: 22,
                    left: margem,
                    right: margem
                },

                theme: 'plain',

                body: [
                    ['Aluno', texto(dados.aluno)],
                    ['Professor', texto(dados.professor)],
                    ['Unidade', texto(dados.unidade)],
                    ['Objetivo', texto(dados.objetivo)],
                    ['Data da ficha', texto(dados.data)],
                    ['Versão', texto(dados.versao)]
                ],

                styles: {
                    font: 'helvetica',
                    fontSize: 10,
                    cellPadding: 3,
                    textColor: [35, 35, 35],
                    overflow: 'linebreak'
                },

                columnStyles: {
                    0: {
                        cellWidth: 38,
                        fontStyle: 'bold',
                        textColor: [100, 100, 100]
                    },

                    1: {
                        cellWidth: 144
                    }
                }
            });

            // =====================================
            // RESUMO DO TREINO
            // =====================================

            let y = doc.lastAutoTable.finalY + 6;

            if (y + 38 > 275) {
                doc.addPage();
                y = 43;
            }

            doc.setFillColor(244, 244, 244);

            doc.roundedRect(
                margem, y, area, 18, 2, 2, 'F'
            );

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(15);
            doc.setTextColor(...escuro);

            doc.text(
                String(dados.exercicios.length),
                59,
                y + 7,
                { align: 'center' }
            );

            doc.text(
                String(totalSeries),
                151,
                y + 7,
                { align: 'center' }
            );

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(9);

            doc.text(
                'Exercícios prescritos',
                59,
                y + 13,
                { align: 'center' }
            );

            doc.text(
                'Total de séries',
                151,
                y + 13,
                { align: 'center' }
            );

            // =====================================
            // TABELA DE EXERCÍCIOS
            // =====================================

            const linhas = dados.exercicios.map(
                (e, indice) => [
                    `${indice + 1}. ${texto(e.nome)}`,
                    texto(e.grupo),
                    texto(e.series),
                    texto(e.repeticoes),
                    texto(e.carga),
                    texto(e.intervalo)
                ]
            );

            if (!linhas.length) {
                linhas.push([
                    'Nenhum exercício cadastrado',
                    '-', '-', '-', '-', '-'
                ]);
            }

            doc.autoTable({

                startY: y + 26,

                margin: {
                    top: 43,
                    bottom: 22,
                    left: margem,
                    right: margem
                },

                head: [[
                    'Exercício',
                    'Grupo',
                    'Séries',
                    'Reps.',
                    'Carga',
                    'Descanso'
                ]],

                body: linhas,

                theme: 'striped',

                showHead: 'everyPage',

                rowPageBreak: 'avoid',

                styles: {
                    font: 'helvetica',
                    fontSize: 9,
                    cellPadding: 3,
                    valign: 'middle',
                    overflow: 'linebreak',
                    textColor: [35, 35, 35]
                },

                headStyles: {
                    fillColor: escuro,
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                },

                alternateRowStyles: {
                    fillColor: [245, 245, 245]
                },

                columnStyles: {
                    0: { cellWidth: 57 },
                    1: { cellWidth: 30 },
                    2: { cellWidth: 15, halign: 'center' },
                    3: { cellWidth: 23, halign: 'center' },
                    4: { cellWidth: 30, halign: 'center' },
                    5: { cellWidth: 27, halign: 'center' }
                }
            });

            // =====================================
            // ORIENTAÇÕES
            // =====================================

            y = doc.lastAutoTable.finalY + 10;

            if (y + 22 > 275) {
                doc.addPage();
                y = 43;
            }

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(11);
            doc.setTextColor(...escuro);

            doc.text('Orientações', margem, y);

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(9);
            doc.setTextColor(100, 100, 100);

            const orientacao = doc.splitTextToSize(
                'Respeite as séries, repetições, cargas e ' +
                'intervalos prescritos. Em caso de dúvidas, ' +
                'consulte o profissional responsável pelo treino.',
                area
            );

            doc.text(
                orientacao,
                margem,
                y + 6
            );

            // =====================================
            // CABEÇALHO E RODAPÉ
            // =====================================

            const paginas = doc.getNumberOfPages();

            for (let p = 1; p <= paginas; p++) {

                doc.setPage(p);

                // CABEÇALHO

                doc.setFillColor(...escuro);
                doc.rect(0, 0, largura, 34, 'F');

                doc.setFillColor(...dourado);
                doc.rect(0, 34, largura, 1.5, 'F');

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(10);
                doc.setTextColor(...dourado);

                doc.text(
                    'FICHA DE TREINAMENTO',
                    margem,
                    10
                );

                doc.setFontSize(21);
                doc.setTextColor(255, 255, 255);

                doc.text(
                    'Meu treino',
                    margem,
                    23
                );

                // RODAPÉ

                doc.setDrawColor(220, 220, 220);

                doc.line(
                    margem,
                    280,
                    largura - margem,
                    280
                );

                doc.setFont('helvetica', 'normal');
                doc.setFontSize(8);
                doc.setTextColor(120, 120, 120);

                doc.text(
                    'Ficha individual de treinamento',
                    margem,
                    286
                );

                doc.text(
                    `${p}/${paginas}`,
                    largura - margem,
                    286,
                    { align: 'right' }
                );
            }

            // =====================================
            // DOWNLOAD DIRETO DO PDF
            // =====================================

            doc.save(
                `Ficha_Treino_${dados.id}_V${dados.versao}.pdf`
            );

        } catch (erro) {

            if (aviso) {
                aviso.textContent =
                    'Não foi possível gerar o PDF. ' +
                    erro.message;

                aviso.hidden = false;
            }

            console.error('[GymFlow - PDF]', erro);
        }
    }

    window.GymFlowFichaPdf = {
        baixar
    };

})();