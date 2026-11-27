// assets/js/configuracao-email.js
(function () {
    const checkboxSMTP = document.getElementById('usar_personalizado');
    const camposSMTP = document.querySelectorAll('[data-smtp-field]');
    const seletorModo = document.getElementById('modo_cores_email');
    const camposPersonalizados = document.getElementById('cores_email_personalizadas');
    const nota = document.getElementById('nota_modo_cores');

    let modoSMTPAtual = checkboxSMTP && checkboxSMTP.checked ? 'personalizado' : 'padrao';
    const valoresSMTP = new WeakMap();

    camposSMTP.forEach(function (campo) {
        if (campo.id === 'smtp_senha') {
            return;
        }

        valoresSMTP.set(campo, {
            padrao: campo.dataset.smtpPadrao ?? '',
            personalizado: campo.dataset.smtpPersonalizado ?? ''
        });
    });

    function atualizarCamposSMTP() {
        if (!checkboxSMTP) {
            return;
        }

        const novoModo = checkboxSMTP.checked ? 'personalizado' : 'padrao';

        camposSMTP.forEach(function (campo) {
            if (campo.id === 'smtp_senha') {
                campo.disabled = novoModo !== 'personalizado';
                campo.required = false;
                campo.value = '';
                return;
            }

            const valores = valoresSMTP.get(campo);

            if (novoModo !== modoSMTPAtual && valores) {
                valores[modoSMTPAtual] = campo.value;
                campo.value = valores[novoModo];
            }

            campo.disabled = novoModo !== 'personalizado';
            campo.required = novoModo === 'personalizado';
        });

        modoSMTPAtual = novoModo;
    }

    function atualizarModoCores() {
        if (!seletorModo || !camposPersonalizados || !nota) {
            return;
        }

        const usaMarca = seletorModo.value === 'marca';
        camposPersonalizados.hidden = usaMarca;
        nota.textContent = usaMarca
            ? 'O modo marca está selecionado. A aplicação das cores da marca será conectada ao envio do e-mail na próxima etapa.'
            : 'Você pode escolher cores exclusivas para os e-mails. Elas não devem alterar o portfólio público.';
    }

    if (checkboxSMTP) {
        checkboxSMTP.addEventListener('change', atualizarCamposSMTP);
        atualizarCamposSMTP();
    }

    if (seletorModo) {
        seletorModo.addEventListener('change', atualizarModoCores);
        atualizarModoCores();
    }
})();
