<?php
// app/controllers/PortfolioController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';
require_once __DIR__ . '/../../config/conexao.php';

// Apenas administradores configuram o portfolio
verificarRole(['Admin', 'Professor', 'Recepcao']);

$tituloPagina = "Portfólio";
$acao = $_GET['acao'] ?? 'index';

// No XAMPP/PHP cru, $_SESSION não guarda diretamente o company_id a menos que o Login faça isso.
// Mas o seed usa company_id = 1 para a GymFlow.
$company_id = 1; 

if ($acao === 'salvar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $corPrimaria = $_POST['corPrimaria'] ?? '#00ff00';
    $corSecundaria = $_POST['corSecundaria'] ?? '#000000';
    $urlLogotipo = $_POST['urlLogotipo'] ?? '';
    $tituloHero = $_POST['tituloHero'] ?? '';
    $subtituloHero = $_POST['subtituloHero'] ?? '';
    $textoBotao = $_POST['textoBotao'] ?? '';
    $sobreNos = $_POST['sobreNos'] ?? '';
    $urlImagemSobre = $_POST['urlImagemSobre'] ?? '';
    $nossosValores = $_POST['nossosValores'] ?? '';
    $nossasCompetencias = $_POST['nossasCompetencias'] ?? '';

    $stmt = $pdo->prepare("
        UPDATE portfolio_config 
        SET primary_color = ?, secondary_color = ?, logo_url = ?, 
            hero_title = ?, hero_subtitle = ?, hero_cta = ?, 
            about_text = ?, about_image = ?, company_values = ?, company_competencies = ?
        WHERE company_id = ?
    ");
    $stmt->execute([
        $corPrimaria, $corSecundaria, $urlLogotipo, 
        $tituloHero, $subtituloHero, $textoBotao, 
        $sobreNos, $urlImagemSobre, $nossosValores, 
        $nossasCompetencias, $company_id
    ]);

    // Deletar
    if (!empty($_POST['planos_remover'])) {
        $stmtDel = $pdo->prepare("DELETE FROM planos WHERE id = ? AND company_id = ?");
        foreach ($_POST['planos_remover'] as $id) {
            try { $stmtDel->execute([$id, $company_id]); } catch (PDOException $e) {}
        }
    }
    if (!empty($_POST['filiais_remover'])) {
        $stmtDel = $pdo->prepare("DELETE FROM filiais WHERE id = ? AND company_id = ?");
        foreach ($_POST['filiais_remover'] as $id) {
            try { $stmtDel->execute([$id, $company_id]); } catch (PDOException $e) {}
        }
    }
    if (!empty($_POST['modalidades_remover'])) {
        $stmtDel = $pdo->prepare("DELETE FROM portfolio_modalities WHERE id = ?");
        foreach ($_POST['modalidades_remover'] as $id) {
            try { $stmtDel->execute([$id]); } catch (PDOException $e) {}
        }
    }

    // Atualizar / Inserir Planos
    if (isset($_POST['planos']) && is_array($_POST['planos'])) {
        $stmtUpdate = $pdo->prepare("UPDATE planos SET nome = ?, valor = ?, categoria = ?, duracao = ? WHERE id = ? AND company_id = ?");
        $stmtInsert = $pdo->prepare("INSERT INTO planos (company_id, nome, categoria, valor, duracao) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($_POST['planos'] as $id => $data) {
            if (strpos((string)$id, 'new_') === 0) {
                $stmtInsert->execute([$company_id, $data['nome'], $data['categoria'], $data['valor'], $data['duracao']]);
            } else {
                $stmtUpdate->execute([$data['nome'], $data['valor'], $data['categoria'], $data['duracao'], $id, $company_id]);
            }
        }
    }

    // Atualizar / Inserir Filiais
    if (isset($_POST['filiais']) && is_array($_POST['filiais'])) {
        $stmtUpdate = $pdo->prepare("UPDATE filiais SET nome = ?, telefone = ?, cnpj = ?, responsavel = ? WHERE id = ? AND company_id = ?");
        $stmtInsert = $pdo->prepare("INSERT INTO filiais (company_id, nome, cnpj, telefone, responsavel, ativo) VALUES (?, ?, ?, ?, ?, 1)");
        
        foreach ($_POST['filiais'] as $id => $data) {
            if (strpos((string)$id, 'new_') === 0) {
                $stmtInsert->execute([$company_id, $data['nome'], $data['cnpj'], $data['telefone'], $data['responsavel']]);
            } else {
                $stmtUpdate->execute([$data['nome'], $data['telefone'], $data['cnpj'], $data['responsavel'], $id, $company_id]);
            }
        }
    }

    // Atualizar / Inserir Modalidades
    if (isset($_POST['modalidades']) && is_array($_POST['modalidades'])) {
        $stmtUpdate = $pdo->prepare("UPDATE portfolio_modalities SET name = ?, description = ?, image_url = ? WHERE id = ?");
        // Filial ID = 1 por padrão se for novo
        $stmtInsert = $pdo->prepare("INSERT INTO portfolio_modalities (filial_id, name, description, image_url) VALUES (1, ?, ?, ?)");
        
        foreach ($_POST['modalidades'] as $id => $data) {
            if (strpos((string)$id, 'new_') === 0) {
                $stmtInsert->execute([$data['name'], $data['description'], $data['image_url']]);
            } else {
                $stmtUpdate->execute([$data['name'], $data['description'], $data['image_url'], $id]);
            }
        }
    }

    header("Location: /Gymflow/app/controllers/PortfolioController.php?status=sucesso");
    exit;
}

// Buscar a configuração atual
$stmt = $pdo->prepare("SELECT * FROM portfolio_config WHERE company_id = ?");
$stmt->execute([$company_id]);
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$config) {
    $config = [
        'primary_color' => '#10b981', 'secondary_color' => '#0f172a', 'logo_url' => '',
        'hero_title' => 'Transforme seu corpo', 'hero_subtitle' => '...',
        'hero_cta' => 'Matricule-se', 'about_text' => '...', 'about_image' => '',
        'company_values' => '...', 'company_competencies' => '...'
    ];
}

// Buscar planos
$stmtPlanos = $pdo->prepare("SELECT * FROM planos WHERE company_id = ?");
$stmtPlanos->execute([$company_id]);
$planos = $stmtPlanos->fetchAll(PDO::FETCH_ASSOC);

// Buscar unidades (filiais)
$stmtFiliais = $pdo->prepare("SELECT * FROM filiais WHERE company_id = ? AND ativo = 1");
$stmtFiliais->execute([$company_id]);
$filiais = $stmtFiliais->fetchAll(PDO::FETCH_ASSOC);

// Buscar modalidades
$stmtModais = $pdo->prepare("SELECT * FROM portfolio_modalities");
$stmtModais->execute();
$modalidades = $stmtModais->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../views/portfolio/index.php';
