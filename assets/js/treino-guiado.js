function renderizar() {

    if (!sessao) return;

    atualizarOverview();

    if (guide.hidden) return;

    const concluido =
        sessao.fase === 'concluido';

    mostrar(running, !concluido);
    mostrar(finishPanel, concluido);
    mostrar($('tgEndRequest'), !concluido);

    if (concluido) {
        renderizarResumo();
        return;
    }

    const exercicio = exercicioAtual();

    const feitas =
        registrosAtuais().length;

    const timerActions =
        restPanel.querySelector('.tg-timer-actions');

    atualizarProgresso();
    renderizarSeries();

    exibirMidiaGuiada(
        sessao.fila[sessao.posicao]
    );

    texto(
        'tgGuideTitle',
        sessao.fila.length === 1
            ? 'Execução do exercício'
            : 'Treino em andamento'
    );

    texto('tgCurrentName', exercicio.nome);
    texto('tgCurrentGroup', exercicio.grupo);

    texto('tgPrescribedSets', exercicio.series);

    texto(
        'tgPrescribedReps',
        exercicio.repeticoes || 'Não definida'
    );

    texto(
        'tgPrescribedLoad',
        exercicio.carga || 'Não definida'
    );

    texto(
        'tgPrescribedRest',
        exercicio.intervalo || 'Não definido'
    );

    mostrar(workPanel, false);
    mostrar(restPanel, false);
    mostrar(nextPanel, false);

    mostrar(timerActions, false);
    mostrar($('tgWorkError'), false);

    switch (sessao.fase) {

        // =====================================
        // EXECUÇÃO DA SÉRIE
        // =====================================

        case 'execucao': {

            mostrar(workPanel, true);
            mostrar(restPanel, true);

            texto(
                'tgExecutionTitle',
                `Série ${feitas + 1} de ${exercicio.series}`
            );

            texto(
                'tgExecutionText',
                'Realize a série e registre sua execução.'
            );

            // Exibe o descanso previsto,
            // sem iniciar a contagem.

            const intervalo =
                interpretarIntervalo(
                    exercicio.intervalo
                );

            texto(
                'tgTimerValue',
                intervalo === null
                    ? '—'
                    : formatarTempo(intervalo)
            );

            texto(
                'tgTimerStatus',
                intervalo === null
                    ? 'Descanso não definido'
                    : intervalo === 0
                        ? 'Sem intervalo'
                        : feitas + 1 >= exercicio.series
                            ? 'Última série'
                            : 'Aguardando conclusão'
            );

            const circulo = $('tgTimerCircle');

            circulo.style.strokeDasharray =
                String(circunferencia);

            circulo.style.strokeDashoffset =
                intervalo !== null && intervalo > 0
                    ? '0'
                    : String(circunferencia);

            // Controles ficam ocultos enquanto
            // o descanso não estiver em andamento.

            mostrar(timerActions, false);

            const ultima =
                registrosAtuais().at(-1);

            const repsPrescritas =
                /^\d+$/.test(exercicio.repeticoes)
                    ? Number(exercicio.repeticoes)
                    : '';

            $('tgActualReps').value =
                ultima?.repeticoes ??
                (
                    repsPrescritas <= 999
                        ? repsPrescritas
                        : ''
                );

            $('tgActualLoad').value =
                ultima?.carga ??
                exercicio.carga;

            break;
        }

        // =====================================
        // DESCANSO EM ANDAMENTO
        // =====================================

        case 'descanso':

            mostrar(restPanel, true);
            mostrar(timerActions, true);

            texto(
                'tgExecutionTitle',
                'Tempo de descanso'
            );

            texto(
                'tgExecutionText',
                `Série ${feitas} concluída.`
            );

            desenharTimer();

            break;

        // =====================================
        // PRÓXIMA SÉRIE
        // =====================================

        case 'proxima-serie':

            mostrar(nextPanel, true);

            texto(
                'tgExecutionTitle',
                'Próxima série'
            );

            texto(
                'tgExecutionText',
                sessao.mensagem ||
                'Continue quando estiver pronto.'
            );

            texto(
                'tgNextText',
                `Iniciar série ${feitas + 1}`
            );

            break;

        // =====================================
        // PRÓXIMO EXERCÍCIO
        // =====================================

        case 'proximo-exercicio':

            mostrar(nextPanel, true);

            texto(
                'tgExecutionTitle',
                'Exercício concluído'
            );

            texto(
                'tgExecutionText',
                'Todas as séries foram realizadas.'
            );

            texto(
                'tgNextText',
                'Próximo exercício'
            );

            break;
    }
}