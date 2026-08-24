<?php
// app/controllers/ExercicioController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';
require_once __DIR__ . '/../models/Exercicio.php';

$tituloPagina = "Biblioteca de Exercícios";


// =====================================================
// EXCLUSÃO DE EXERCÍCIO
// =====================================================

if (
    isset($_GET['acao']) &&
    $_GET['acao'] === 'excluir'
) {

    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        die('Exercício inválido.');
    }

    excluirExercicio($pdo, $id);

    header(
        'Location: /Gymflow/app/controllers/ExercicioController.php'
    );

    exit;
}


// =====================================================
// EDITAR EXERCÍCIO - ABRIR FORMULÁRIO
// =====================================================

if (
    isset($_GET['acao']) &&
    $_GET['acao'] === 'editar' &&
    $_SERVER['REQUEST_METHOD'] === 'GET'
) {

    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        die('Exercício inválido.');
    }

    $exercicio = buscarExercicioPorId($pdo, $id);

    if (!$exercicio) {
        die('Exercício não encontrado.');
    }

    require __DIR__ . '/../views/exercicios/editar.php';

    exit;
}


// =====================================================
// EDITAR EXERCÍCIO - SALVAR ALTERAÇÕES
// =====================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['acao'] ?? '') === 'editar'
) {

    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        die('Exercício inválido.');
    }

    $exercicio = buscarExercicioPorId($pdo, $id);

    if (!$exercicio) {
        die('Exercício não encontrado.');
    }


    // =====================================================
    // DADOS DO FORMULÁRIO
    // =====================================================

    $nome = trim($_POST['nome'] ?? '');
    $grupo = trim($_POST['grupo_muscular'] ?? '');
    $tipo_midia = trim($_POST['tipo_midia'] ?? '');


    // =====================================================
    // VALIDAÇÃO
    // =====================================================

    if (
        $nome === '' ||
        $grupo === '' ||
        $tipo_midia === ''
    ) {

        die('Preencha todos os campos obrigatórios.');
    }


    // =====================================================
    // MANTÉM A MÍDIA ATUAL
    // =====================================================

    $midia = $exercicio['midia'];


    // =====================================================
    // NOVA MÍDIA
    // =====================================================

    if (
        isset($_FILES['arquivo']) &&
        $_FILES['arquivo']['error'] === UPLOAD_ERR_OK
    ) {

        $arquivo = $_FILES['arquivo'];


        // Descobre a extensão

        $extensao = strtolower(
            pathinfo(
                $arquivo['name'],
                PATHINFO_EXTENSION
            )
        );


        // =====================================================
        // EXTENSÕES PERMITIDAS
        // =====================================================

        if ($tipo_midia === 'imagem') {

            $extensoesPermitidas = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];

        } elseif ($tipo_midia === 'video') {

            $extensoesPermitidas = [
                'mp4',
                'webm'
            ];

        } else {

            die('Tipo de mídia inválido.');
        }


        // =====================================================
        // VERIFICA EXTENSÃO
        // =====================================================

        if (
            !in_array(
                $extensao,
                $extensoesPermitidas,
                true
            )
        ) {

            die('Formato de arquivo não permitido.');
        }


        // =====================================================
        // PASTA DE UPLOAD
        // =====================================================

        $pastaUpload =
            __DIR__ .
            '/../../assets/uploads/exercicios/';


        if (!is_dir($pastaUpload)) {

            mkdir(
                $pastaUpload,
                0777,
                true
            );
        }


        // =====================================================
        // NOVO NOME DO ARQUIVO
        // =====================================================

        $nomeArquivo =
            uniqid(
                'exercicio_',
                true
            ) .
            '.' .
            $extensao;


        $caminhoCompleto =
            $pastaUpload .
            $nomeArquivo;


        // =====================================================
        // SALVA NOVO ARQUIVO
        // =====================================================

        if (
            !move_uploaded_file(
                $arquivo['tmp_name'],
                $caminhoCompleto
            )
        ) {

            die(
                'Não foi possível salvar o novo arquivo.'
            );
        }


        // Caminho salvo no banco

        $midia =
            '/Gymflow/assets/uploads/exercicios/' .
            $nomeArquivo;
    }


    // =====================================================
    // ATUALIZA NO BANCO
    // =====================================================

    atualizarExercicio(
        $pdo,
        $id,
        $nome,
        $grupo,
        $midia,
        $tipo_midia
    );


    // =====================================================
    // VOLTA PARA A BIBLIOTECA
    // =====================================================

    header(
        'Location: /Gymflow/app/controllers/ExercicioController.php'
    );

    exit;
}


// =====================================================
// CADASTRO DE EXERCÍCIO
// =====================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    $nome = trim(
        $_POST['nome'] ?? ''
    );

    $grupo = trim(
        $_POST['grupo_muscular'] ?? ''
    );

    $tipo_midia = trim(
        $_POST['tipo_midia'] ?? ''
    );


    // =====================================================
    // VERIFICA CAMPOS OBRIGATÓRIOS
    // =====================================================

    if (
        $nome === '' ||
        $grupo === '' ||
        $tipo_midia === ''
    ) {

        die(
            'Preencha todos os campos obrigatórios.'
        );
    }


    // =====================================================
    // VERIFICA ARQUIVO
    // =====================================================

    if (
        !isset($_FILES['arquivo']) ||
        $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK
    ) {

        die(
            'Selecione uma imagem ou vídeo.'
        );
    }


    $arquivo = $_FILES['arquivo'];


    // =====================================================
    // EXTENSÃO
    // =====================================================

    $extensao = strtolower(
        pathinfo(
            $arquivo['name'],
            PATHINFO_EXTENSION
        )
    );


    // =====================================================
    // EXTENSÕES PERMITIDAS
    // =====================================================

    if ($tipo_midia === 'imagem') {

        $extensoesPermitidas = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

    } elseif ($tipo_midia === 'video') {

        $extensoesPermitidas = [
            'mp4',
            'webm'
        ];

    } else {

        die(
            'Tipo de mídia inválido.'
        );
    }


    // =====================================================
    // VERIFICA EXTENSÃO
    // =====================================================

    if (
        !in_array(
            $extensao,
            $extensoesPermitidas,
            true
        )
    ) {

        die(
            'Formato de arquivo não permitido.'
        );
    }


    // =====================================================
    // PASTA DE UPLOAD
    // =====================================================

    $pastaUpload =
        __DIR__ .
        '/../../assets/uploads/exercicios/';


    if (!is_dir($pastaUpload)) {

        mkdir(
            $pastaUpload,
            0777,
            true
        );
    }


    // =====================================================
    // NOME DO ARQUIVO
    // =====================================================

    $nomeArquivo =
        uniqid(
            'exercicio_',
            true
        ) .
        '.' .
        $extensao;


    $caminhoCompleto =
        $pastaUpload .
        $nomeArquivo;


    // =====================================================
    // SALVA ARQUIVO
    // =====================================================

    if (
        !move_uploaded_file(
            $arquivo['tmp_name'],
            $caminhoCompleto
        )
    ) {

        die(
            'Não foi possível salvar o arquivo.'
        );
    }


    // =====================================================
    // CAMINHO SALVO NO BANCO
    // =====================================================

    $midia =
        '/Gymflow/assets/uploads/exercicios/' .
        $nomeArquivo;


    // =====================================================
    // SALVA NO BANCO
    // =====================================================

    cadastrarExercicio(
        $pdo,
        $nome,
        $grupo,
        $midia,
        $tipo_midia
    );


    // =====================================================
    // VOLTA PARA A BIBLIOTECA
    // =====================================================

    header(
        'Location: /Gymflow/app/controllers/ExercicioController.php'
    );

    exit;
}


// =====================================================
// PESQUISA E FILTRO
// =====================================================

$pesquisa = $_GET['pesquisa'] ?? '';

$grupo = $_GET['grupo'] ?? '';


// =====================================================
// BUSCA OS EXERCÍCIOS
// =====================================================

if (!empty($pesquisa)) {

    $exercicios = pesquisarExercicios(
        $pdo,
        $pesquisa
    );

} elseif (!empty($grupo)) {

    $exercicios = filtrarExerciciosPorGrupo(
        $pdo,
        $grupo
    );

} else {

    $exercicios = listarExercicios(
        $pdo
    );
}


// =====================================================
// VIEW DA BIBLIOTECA
// =====================================================

require __DIR__ . '/../views/treinos/biblioteca.php';