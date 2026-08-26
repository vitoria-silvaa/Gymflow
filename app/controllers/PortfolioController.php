<?php
// app/controllers/PortfolioController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';
require_once __DIR__ . '/../../config/conexao.php';

// Permissões para configurar o portfólio
verificarRole(['Admin']);

$tituloPagina = "Portfólio";
$acao = $_GET['acao'] ?? 'index';

// Company_id = 1 temporário
$company_id = 1;


// ======================================================
// SALVAR ALTERAÇÕES
// ======================================================

if ($acao === 'salvar' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    // Configurações gerais
    $corPrimaria = $_POST['corPrimaria'] ?? '#C9A227';
    $corSecundaria = $_POST['corSecundaria'] ?? '#000000';
    $urlLogotipo = $_POST['urlLogotipo'] ?? '';

    $tituloHero = $_POST['tituloHero'] ?? '';
    $subtituloHero = $_POST['subtituloHero'] ?? '';
    $textoBotao = $_POST['textoBotao'] ?? '';

    $sobreNos = $_POST['sobreNos'] ?? '';
    $urlImagemSobre = $_POST['urlImagemSobre'] ?? '';
    $nossosValores = $_POST['nossosValores'] ?? '';
    $nossasCompetencias = $_POST['nossasCompetencias'] ?? '';


    // ======================================================
    // ATUALIZAR CONFIGURAÇÕES DO PORTFÓLIO
    // ======================================================

    $stmt = $pdo->prepare("
        UPDATE portfolio_config
        SET
            primary_color = ?,
            secondary_color = ?,
            logo_url = ?,
            hero_title = ?,
            hero_subtitle = ?,
            hero_cta = ?,
            about_text = ?,
            about_image = ?,
            company_values = ?,
            company_competencies = ?
        WHERE company_id = ?
    ");

    $stmt->execute([
        $corPrimaria,
        $corSecundaria,
        $urlLogotipo,
        $tituloHero,
        $subtituloHero,
        $textoBotao,
        $sobreNos,
        $urlImagemSobre,
        $nossosValores,
        $nossasCompetencias,
        $company_id
    ]);


    // ======================================================
    // REMOVER PLANOS
    // ======================================================

    if (!empty($_POST['planos_remover'])) {

        $stmtDel = $pdo->prepare("
            DELETE FROM planos
            WHERE id = ? AND company_id = ?
        ");

        foreach ($_POST['planos_remover'] as $id) {
            try {
                $stmtDel->execute([$id, $company_id]);
            } catch (PDOException $e) {
            }
        }
    }


    // ======================================================
    // REMOVER FILIAIS
    // ======================================================

    if (!empty($_POST['filiais_remover'])) {

        $stmtDel = $pdo->prepare("
            DELETE FROM filiais
            WHERE id = ? AND company_id = ?
        ");

        foreach ($_POST['filiais_remover'] as $id) {
            try {
                $stmtDel->execute([$id, $company_id]);
            } catch (PDOException $e) {
            }
        }
    }


    // ======================================================
    // REMOVER MODALIDADES
    // ======================================================

    if (!empty($_POST['modalidades_remover'])) {

        $stmtDel = $pdo->prepare("
            DELETE FROM portfolio_modalities
            WHERE id = ?
        ");

        foreach ($_POST['modalidades_remover'] as $id) {
            try {
                $stmtDel->execute([$id]);
            } catch (PDOException $e) {
            }
        }
    }


    // ======================================================
    // REMOVER SLIDES DO CARROSSEL
    // ======================================================

    if (!empty($_POST['slides_remover'])) {

        $stmtDel = $pdo->prepare("
            DELETE FROM portfolio_slides
            WHERE id = ? AND company_id = ?
        ");

        foreach ($_POST['slides_remover'] as $id) {
            try {
                $stmtDel->execute([$id, $company_id]);
            } catch (PDOException $e) {
            }
        }
    }


    // ======================================================
    // ATUALIZAR / INSERIR PLANOS
    // ======================================================

    if (isset($_POST['planos']) && is_array($_POST['planos'])) {

        $stmtUpdate = $pdo->prepare("
            UPDATE planos
            SET nome = ?, valor = ?, categoria = ?, duracao = ?
            WHERE id = ? AND company_id = ?
        ");

        $stmtInsert = $pdo->prepare("
            INSERT INTO planos
            (company_id, nome, categoria, valor, duracao)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($_POST['planos'] as $id => $data) {

            if (strpos((string)$id, 'new_') === 0) {

                $stmtInsert->execute([
                    $company_id,
                    $data['nome'],
                    $data['categoria'],
                    $data['valor'],
                    $data['duracao']
                ]);

            } else {

                $stmtUpdate->execute([
                    $data['nome'],
                    $data['valor'],
                    $data['categoria'],
                    $data['duracao'],
                    $id,
                    $company_id
                ]);
            }
        }
    }


    // ======================================================
    // ATUALIZAR / INSERIR FILIAIS
    // ======================================================

    if (isset($_POST['filiais']) && is_array($_POST['filiais'])) {

        $stmtUpdate = $pdo->prepare("
            UPDATE filiais
            SET nome = ?, telefone = ?, cnpj = ?, responsavel = ?
            WHERE id = ? AND company_id = ?
        ");

        $stmtInsert = $pdo->prepare("
            INSERT INTO filiais
            (company_id, nome, cnpj, telefone, responsavel, ativo)
            VALUES (?, ?, ?, ?, ?, 1)
        ");

        foreach ($_POST['filiais'] as $id => $data) {

            if (strpos((string)$id, 'new_') === 0) {

                $stmtInsert->execute([
                    $company_id,
                    $data['nome'],
                    $data['cnpj'],
                    $data['telefone'],
                    $data['responsavel']
                ]);

            } else {

                $stmtUpdate->execute([
                    $data['nome'],
                    $data['telefone'],
                    $data['cnpj'],
                    $data['responsavel'],
                    $id,
                    $company_id
                ]);
            }
        }
    }


    // ======================================================
    // ATUALIZAR / INSERIR MODALIDADES
    // ======================================================

    if (isset($_POST['modalidades']) && is_array($_POST['modalidades'])) {

        $stmtUpdate = $pdo->prepare("
            UPDATE portfolio_modalities
            SET name = ?, description = ?, image_url = ?
            WHERE id = ?
        ");

        // Filial ID = 1 por padrão para novas modalidades
        $stmtInsert = $pdo->prepare("
            INSERT INTO portfolio_modalities
            (filial_id, name, description, image_url)
            VALUES (1, ?, ?, ?)
        ");

        foreach ($_POST['modalidades'] as $id => $data) {

            if (strpos((string)$id, 'new_') === 0) {

                $stmtInsert->execute([
                    $data['name'],
                    $data['description'],
                    $data['image_url']
                ]);

            } else {

                $stmtUpdate->execute([
                    $data['name'],
                    $data['description'],
                    $data['image_url'],
                    $id
                ]);
            }
        }
    }


    // ======================================================
    // ATUALIZAR / INSERIR SLIDES DO CARROSSEL
    // ======================================================

    if (isset($_POST['slides']) && is_array($_POST['slides'])) {

        $stmtUpdate = $pdo->prepare("
            UPDATE portfolio_slides
            SET image_url = ?, ordem = ?, ativo = ?
            WHERE id = ? AND company_id = ?
        ");

        $stmtInsert = $pdo->prepare("
            INSERT INTO portfolio_slides
            (company_id, image_url, ordem, ativo)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($_POST['slides'] as $id => $data) {

            $imageUrl = $data['image_url'] ?? '';
            $ordem = (int) ($data['ordem'] ?? 0);
            $ativo = isset($data['ativo']) ? 1 : 0;

            if (strpos((string)$id, 'new_') === 0) {

                $stmtInsert->execute([
                    $company_id,
                    $imageUrl,
                    $ordem,
                    $ativo
                ]);

            } else {

                $stmtUpdate->execute([
                    $imageUrl,
                    $ordem,
                    $ativo,
                    $id,
                    $company_id
                ]);
            }
        }
    }


    // Depois de salvar, volta para a página do portfólio
    header("Location: /Gymflow/app/controllers/PortfolioController.php?status=sucesso");
    exit;
}


// ======================================================
// BUSCAR CONFIGURAÇÕES
// ======================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM portfolio_config
    WHERE company_id = ?
");

$stmt->execute([$company_id]);

$config = $stmt->fetch(PDO::FETCH_ASSOC);


// Configuração padrão caso não exista no banco
if (!$config) {

    $config = [
        'app_name' => 'GymCore',
        'theme_mode' => 'dark',

        'primary_color' => '#C9A227',
        'secondary_color' => '#000000',

        'logo_url' => '',

        'hero_title' => 'Transforme seu corpo',
        'hero_subtitle' => '...',
        'hero_cta' => 'Matricule-se',

        'about_text' => '...',
        'about_image' => '',

        'company_values' => '...',
        'company_competencies' => '...'
    ];
}


// ======================================================
// BUSCAR PLANOS
// ======================================================

$stmtPlanos = $pdo->prepare("
    SELECT *
    FROM planos
    WHERE company_id = ?
");

$stmtPlanos->execute([$company_id]);

$planos = $stmtPlanos->fetchAll(PDO::FETCH_ASSOC);


// ======================================================
// BUSCAR UNIDADES / FILIAIS
// ======================================================

$stmtFiliais = $pdo->prepare("
    SELECT *
    FROM filiais
    WHERE company_id = ?
    AND ativo = 1
");

$stmtFiliais->execute([$company_id]);

$filiais = $stmtFiliais->fetchAll(PDO::FETCH_ASSOC);


// ======================================================
// BUSCAR MODALIDADES
// ======================================================

$stmtModais = $pdo->prepare("
    SELECT *
    FROM portfolio_modalities
");

$stmtModais->execute();

$modalidades = $stmtModais->fetchAll(PDO::FETCH_ASSOC);


// ======================================================
// BUSCAR SLIDES DO CARROSSEL
// ======================================================

$stmtSlides = $pdo->prepare("
    SELECT *
    FROM portfolio_slides
    WHERE company_id = ?
    ORDER BY ordem ASC, id ASC
");

$stmtSlides->execute([$company_id]);

$slides = $stmtSlides->fetchAll(PDO::FETCH_ASSOC);


// ======================================================
// ABRIR VIEW
// ======================================================

require __DIR__ . '/../views/portfolio/index.php';