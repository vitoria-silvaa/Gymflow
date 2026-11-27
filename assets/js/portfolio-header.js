// assets/js/portfolio-header.js
document.addEventListener('DOMContentLoaded', function () {
    const header = document.querySelector('.portfolio-header');
    const botaoMenu = document.querySelector('.portfolio-menu-toggle');
    const linksMenu = document.querySelectorAll('.portfolio-nav a');

    if (!header || !botaoMenu) {
        return;
    }

    function fecharMenu() {
        header.classList.remove('menu-aberto');
        botaoMenu.setAttribute('aria-expanded', 'false');
        botaoMenu.setAttribute('aria-label', 'Abrir menu');
    }

    botaoMenu.addEventListener('click', function () {
        const menuAberto = header.classList.toggle('menu-aberto');
        botaoMenu.setAttribute('aria-expanded', menuAberto ? 'true' : 'false');
        botaoMenu.setAttribute('aria-label', menuAberto ? 'Fechar menu' : 'Abrir menu');
    });

    linksMenu.forEach(function (link) {
        link.addEventListener('click', function () {
            fecharMenu();
        });
    });

    document.addEventListener('click', function (evento) {
        if (!header.contains(evento.target)) {
            fecharMenu();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 600) {
            fecharMenu();
        }
    });
});
