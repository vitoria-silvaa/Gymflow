<?php
// app/helpers/validacao-aluno.php

require_once __DIR__ . '/validacao.php';

/* 1. VALIDAR CPF */

function gymflowCpfValido(string $cpf): bool
{
    if (!preg_match(
        '/^(?:[0-9]{11}|[0-9]{3}\.[0-9]{3}\.[0-9]{3}-[0-9]{2})$/D',
        $cpf
    )) {
        return false;
    }

    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    // Rejeitar CPFs com todos os números iguais.

    if (preg_match('/^([0-9])\1{10}$/D', $cpf)) {
        return false;
    }

    // Validar os dois dígitos verificadores.

    for ($posicao = 9; $posicao <= 10; $posicao++) {

        $soma = 0;

        for ($i = 0; $i < $posicao; $i++) {

            $soma += (int) $cpf[$i] *
                ($posicao + 1 - $i);
        }

        $resto = $soma % 11;

        $digito = $resto < 2
            ? 0
            : 11 - $resto;

        if ((int) $cpf[$posicao] !== $digito) {
            return false;
        }
    }

    return true;
}

/* 2. VALIDAR LIMITE DE CARACTERES */

function gymflowLimiteAluno(
    string $valor,
    int $limite
): bool {

    $total = preg_match_all('/./us', $valor);

    return $total !== false &&
        $total <= $limite;
}

/* 3. VALIDAR FORMULÁRIO */

function gymflowValidarFormularioAluno(
    array $post,
    bool $edicao = false
): array {

    $dados = [];

    $campos = [
        'nome',
        'cpf',
        'rg',
        'sexo',
        'nascimento',
        'email',
        'telefone',
        'endereco',
        'filial_id',
        'professor_id',
        'status'
    ];

    foreach ($campos as $campo) {

        $valor = $post[$campo] ?? '';

        if (!is_string($valor)) {

            return [
                $dados,
                'Dados inválidos no formulário.'
            ];
        }

        $dados[$campo] = trim($valor);
    }

    /* NOME */

    if (
        !gymflowLimiteAluno($dados['nome'], 100) ||
        !preg_match(
            "/^[\\p{L}\\p{M}]+(?:[ .’'\\-]+[\\p{L}\\p{M}]+)*$/uD",
            $dados['nome']
        )
    ) {

        return [
            $dados,
            'Informe um nome válido, com até 100 caracteres.'
        ];
    }

    /* CPF */

    if (!gymflowCpfValido($dados['cpf'])) {

        return [
            $dados,
            'Informe um CPF válido.'
        ];
    }

    $cpf = preg_replace(
        '/[^0-9]/',
        '',
        $dados['cpf']
    );

    // Padronizar CPF antes de salvar.

    $dados['cpf'] =
        substr($cpf, 0, 3) . '.' .
        substr($cpf, 3, 3) . '.' .
        substr($cpf, 6, 3) . '-' .
        substr($cpf, 9, 2);

    /* RG */

    if (
        $dados['rg'] !== '' &&
        (
            !gymflowLimiteAluno($dados['rg'], 20) ||
            !preg_match(
                '/^[\p{L}\p{N}.\/ -]+$/uD',
                $dados['rg']
            )
        )
    ) {

        return [
            $dados,
            'Informe um RG válido, com até 20 caracteres.'
        ];
    }

    /* SEXO */

    if (!in_array(
        $dados['sexo'],
        ['Masculino', 'Feminino', 'Outro'],
        true
    )) {

        return [
            $dados,
            'Selecione o sexo.'
        ];
    }

    /* DATA DE NASCIMENTO */

    $data = $dados['nascimento'];

    $hoje = (
        new DateTimeImmutable(
            'now',
            new DateTimeZone('America/Sao_Paulo')
        )
    )->format('Y-m-d');

    if (
        !preg_match(
            '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D',
            $data,
            $m
        ) ||
        (int) $m[1] < 1000 ||
        !checkdate(
            (int) $m[2],
            (int) $m[3],
            (int) $m[1]
        ) ||
        $data > $hoje
    ) {

        return [
            $dados,
            'Informe uma data de nascimento válida.'
        ];
    }

    /* E-MAIL */

    if (
        strlen($dados['email']) > 100 ||
        !filter_var(
            $dados['email'],
            FILTER_VALIDATE_EMAIL
        )
    ) {

        return [
            $dados,
            'Informe um e-mail válido, com até 100 caracteres.'
        ];
    }

    /* TELEFONE */

    $telefone = preg_replace(
        '/[^0-9]/',
        '',
        $dados['telefone']
    );

    if (
        !preg_match(
            '/^[0-9() .-]+$/D',
            $dados['telefone']
        ) ||
        !preg_match(
            '/^[1-9][0-9]{9,10}$/D',
            $telefone
        )
    ) {

        return [
            $dados,
            'Informe um telefone com DDD, de 10 ou 11 dígitos.'
        ];
    }

    // Padronizar telefone antes de salvar.

    $dados['telefone'] =
        '(' . substr($telefone, 0, 2) . ') ' .
        substr($telefone, 2, -4) . '-' .
        substr($telefone, -4);

    /* ENDEREÇO */

    if (
        !gymflowLimiteAluno($dados['endereco'], 500) ||
        preg_match(
            '/[\x00-\x1F\x7F]/',
            $dados['endereco']
        )
    ) {

        return [
            $dados,
            'Informe um endereço válido de até 500 caracteres.'
        ];
    }

    /* FILIAL */

    $filial = filter_var(
        $dados['filial_id'],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($filial === false) {

        return [
            $dados,
            'Selecione uma filial válida.'
        ];
    }

    $dados['filial_id'] = $filial;

    /* PROFESSOR */

    if ($dados['professor_id'] === '') {

        $dados['professor_id'] = null;
    } else {

        $professor = filter_var(
            $dados['professor_id'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($professor === false) {

            return [
                $dados,
                'Selecione um professor válido.'
            ];
        }

        $dados['professor_id'] = $professor;
    }

    /* STATUS */

    if (
        $edicao &&
        !in_array(
            $dados['status'],
            ['Ativo', 'Inativo'],
            true
        )
    ) {

        return [
            $dados,
            'Selecione um status válido.'
        ];
    }

    /* SENHA */

    $erroSenha = gymflowErroNovaSenha(
        $post['senha'] ?? '',
        $post['confirmar_senha'] ?? '',
        $edicao
    );

    if ($erroSenha !== null) {

        return [
            $dados,
            $erroSenha
        ];
    }

    $dados['senha'] = $post['senha'] ?? '';

    return [$dados, ''];
}
