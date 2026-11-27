// assets/js/filiais.js
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
        const mensagem = document.querySelector('.mensagem-sucesso');
        if (mensagem) {
            mensagem.classList.add('ocultando');
            setTimeout(function () {
                mensagem.remove();
            }, 300);
        }
    }, 3000);
});
