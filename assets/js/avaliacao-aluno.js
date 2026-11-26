// =============================================
// GYMFLOW - AVALIAR ACADEMIA
// assets/js/avaliacao-aluno.js
// =============================================

document.addEventListener('DOMContentLoaded', function () {

    const formulario = document.getElementById(
        'formMinhaAvaliacao'
    );

    if (!formulario) {
        return;
    }

    const estrelas = formulario.querySelectorAll(
        '.avaliacao-estrela'
    );

    const notaInput = document.getElementById(
        'avaliacaoNota'
    );

    const notaTexto = document.getElementById(
        'avaliacaoNotaTexto'
    );

    const textarea = document.getElementById(
        'avaliacaoMensagem'
    );

    const contador = document.getElementById(
        'avaliacaoCaracteres'
    );

    const botaoSalvar = document.getElementById(
        'avaliacaoBotaoSalvar'
    );

    if (
        !notaInput ||
        !notaTexto ||
        !textarea ||
        !contador ||
        !botaoSalvar
    ) {
        return;
    }

    // =========================================
    // ATUALIZAR ESTRELAS
    // =========================================

    function atualizarEstrelas(nota) {

        estrelas.forEach(function (estrela) {

            const valor = Number(estrela.dataset.nota);

            estrela.classList.toggle(
                'selecionada',
                valor <= nota
            );

            estrela.setAttribute(
                'aria-pressed',
                String(valor === nota)
            );
        });

        notaInput.value = nota;

        notaTexto.textContent = nota > 0
            ? nota + ' de 5 estrelas'
            : 'Nenhuma nota selecionada';
    }

    estrelas.forEach(function (estrela) {

        estrela.addEventListener('click', function () {

            atualizarEstrelas(
                Number(this.dataset.nota)
            );

        });
    });

    // =========================================
    // CONTADOR DE CARACTERES
    // =========================================

    function atualizarContador() {

        contador.textContent =
            textarea.value.length + '/1000';
    }

    textarea.addEventListener(
        'input',
        atualizarContador
    );

    // =========================================
    // VALIDAR FORMULÁRIO
    // =========================================

    formulario.addEventListener('submit', function (event) {

        const nota = Number(notaInput.value);

        const mensagem = textarea.value.trim();

        if (
            !Number.isInteger(nota) ||
            nota < 1 ||
            nota > 5
        ) {

            event.preventDefault();

            alert('Selecione uma nota de 1 a 5 estrelas.');

            estrelas[0]?.focus();

            return;
        }

        if (
            mensagem.length === 0 ||
            mensagem.length > 1000
        ) {

            event.preventDefault();

            alert(
                'Escreva um comentário de até 1000 caracteres.'
            );

            textarea.focus();

            return;
        }

        botaoSalvar.disabled = true;

        botaoSalvar.setAttribute(
            'aria-busy',
            'true'
        );
    });

    // =========================================
    // INICIALIZAÇÃO
    // =========================================

    atualizarEstrelas(
        Number(notaInput.value) || 0
    );

    atualizarContador();

    // Reativar botão ao retornar pelo navegador.

    window.addEventListener('pageshow', function () {

        botaoSalvar.disabled = false;

        botaoSalvar.removeAttribute('aria-busy');
    });

});