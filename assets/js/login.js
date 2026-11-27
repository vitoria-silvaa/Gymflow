// assets/js/login.js
document.addEventListener('DOMContentLoaded', function () {
    const campoSenha = document.getElementById('senha');
    const botaoSenha = document.getElementById('passwordToggle');
    const iconeSenha = document.getElementById('password-icon');

    if (!campoSenha || !botaoSenha || !iconeSenha) {
        return;
    }

    botaoSenha.addEventListener('click', function () {
        const mostrando = campoSenha.type === 'text';

        campoSenha.type = mostrando ? 'password' : 'text';
        iconeSenha.textContent = mostrando ? '◉' : '○';
        botaoSenha.setAttribute('aria-label', mostrando ? 'Mostrar senha' : 'Ocultar senha');
        botaoSenha.setAttribute('aria-pressed', String(!mostrando));
    });
});
