<?php
// app/helpers/validacao.php

function gymflowErroNovaSenha(
    $senha,
    $confirmacao,
    bool $opcional = false
): ?string {

    if (
        !is_string($senha) ||
        !is_string($confirmacao)
    ) {
        return 'Senha inválida.';
    }

    // Na edição, permite manter a senha atual.

    if (
        $opcional &&
        $senha === '' &&
        $confirmacao === ''
    ) {
        return null;
    }

    // Validar tamanho e caracteres.

    if (
        strlen($senha) > 72 ||
        preg_match('//u', $senha) !== 1 ||
        strpbrk($senha, "\0\r\n") !== false ||
        preg_match_all('/./us', $senha) < 8
    ) {
        return 'Informe uma senha de pelo menos 8 caracteres e até 72 bytes.';
    }

    // Validar confirmação.

    if ($senha !== $confirmacao) {
        return 'As senhas não coincidem.';
    }

    return null;
}
