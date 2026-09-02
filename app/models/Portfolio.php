<?php
// app/models/Portfolio.php

require_once __DIR__ . '/Database.php';

/** @var PDO $pdo */
$operacao = $operacao ?? 'buscar_publico';
$company_id = (int) ($company_id ?? 1);
$erroModel = '';

// Definições padrão para evitar variáveis e índices indefinidos
$configPadrao = [
    'app_name'             => 'GymCore',
    'theme_mode'           => 'dark',
    'primary_color'        => '#C9A227',
    'secondary_color'      => '#000000',
    'logo_url'             => '',
    'hero_title'           => 'Transforme seu corpo.',
    'hero_subtitle'        => '...',
    'hero_cta'             => 'Matricule-se',
    'about_text'           => '...',
    'about_image'          => '',
    'company_values'       => '',
    'company_competencies' => ''
];

// ======================================================
// 1. BUSCAR DADOS PARA O SITE PÚBLICO (index.php)
// ======================================================
if ($operacao === 'buscar_publico' || $operacao === 'buscar') {
    try {
        // Configurações do Portfólio
        $stmt = $pdo->prepare("SELECT * FROM portfolio_config WHERE company_id = ?");
        $stmt->execute([$company_id]);
        $configBanco = $stmt->fetch(PDO::FETCH_ASSOC);
        $config = array_merge($configPadrao, is_array($configBanco) ? $configBanco : []);

        // Planos
        $stmtPlanos = $pdo->prepare("SELECT * FROM planos WHERE company_id = ? ORDER BY id ASC");
        $stmtPlanos->execute([$company_id]);
        $planos = $stmtPlanos->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Filiais ativas
        $stmtFiliais = $pdo->prepare("SELECT * FROM filiais WHERE company_id = ? AND ativo = 1 ORDER BY id ASC");
        $stmtFiliais->execute([$company_id]);
        $filiais = $stmtFiliais->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Modalidades
        $stmtModais = $pdo->prepare("SELECT * FROM portfolio_modalities ORDER BY id ASC");
        $stmtModais->execute();
        $modalidades = $stmtModais->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Slides ativos do carrossel
        $stmtSlides = $pdo->prepare("
            SELECT *
            FROM portfolio_slides
            WHERE company_id = ?
            AND ativo = 1
            ORDER BY ordem ASC, id ASC
        ");
        $stmtSlides->execute([$company_id]);
        $slides = $stmtSlides->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $tituloPagina = $config['app_name'] ?? 'GymCore';
    } catch (Throwable $e) {
        $erroModel = $e->getMessage();
        $config = $configPadrao;
        $planos = [];
        $filiais = [];
        $modalidades = [];
        $slides = [];
        $tituloPagina = 'GymCore';
    }
}

// ======================================================
// 2. BUSCAR DADOS COMPLETOS PARA O ADMIN (PortfolioController.php)
// ======================================================
elseif ($operacao === 'buscar_admin') {
    try {
        // Configurações do Portfólio
        $stmt = $pdo->prepare("SELECT * FROM portfolio_config WHERE company_id = ?");
        $stmt->execute([$company_id]);
        $configBanco = $stmt->fetch(PDO::FETCH_ASSOC);
        $config = array_merge($configPadrao, is_array($configBanco) ? $configBanco : []);

        // Planos
        $stmtPlanos = $pdo->prepare("SELECT * FROM planos WHERE company_id = ? ORDER BY id ASC");
        $stmtPlanos->execute([$company_id]);
        $planos = $stmtPlanos->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Filiais
        $stmtFiliais = $pdo->prepare("SELECT * FROM filiais WHERE company_id = ? AND ativo = 1 ORDER BY id ASC");
        $stmtFiliais->execute([$company_id]);
        $filiais = $stmtFiliais->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Modalidades
        $stmtModais = $pdo->prepare("SELECT * FROM portfolio_modalities ORDER BY id ASC");
        $stmtModais->execute();
        $modalidades = $stmtModais->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Todos os slides (incluindo inativos para edição)
        $stmtSlides = $pdo->prepare("
            SELECT *
            FROM portfolio_slides
            WHERE company_id = ?
            ORDER BY ordem ASC, id ASC
        ");
        $stmtSlides->execute([$company_id]);
        $slides = $stmtSlides->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $tituloPagina = "Portfólio";
    } catch (Throwable $e) {
        $erroModel = $e->getMessage();
        $config = $configPadrao;
        $planos = [];
        $filiais = [];
        $modalidades = [];
        $slides = [];
        $tituloPagina = "Portfólio";
    }
}

// ======================================================
// 3. SALVAR ALTERAÇÕES DO PORTFÓLIO (Admin)
// ======================================================
elseif ($operacao === 'salvar') {
    $dadosPost = $dadosPost ?? $_POST;

    try {
        // 3.1 Configurações gerais
        $corPrimaria        = $dadosPost['corPrimaria'] ?? '#C9A227';
        $corSecundaria      = $dadosPost['corSecundaria'] ?? '#000000';
        $urlLogotipo        = $dadosPost['urlLogotipo'] ?? '';

        $tituloHero         = $dadosPost['tituloHero'] ?? '';
        $subtituloHero      = $dadosPost['subtituloHero'] ?? '';
        $textoBotao         = $dadosPost['textoBotao'] ?? '';

        $sobreNos           = $dadosPost['sobreNos'] ?? '';
        $urlImagemSobre     = $dadosPost['urlImagemSobre'] ?? '';
        $nossosValores      = $dadosPost['nossosValores'] ?? '';
        $nossasCompetencias = $dadosPost['nossasCompetencias'] ?? '';

        $stmt = $pdo->prepare("
            INSERT INTO portfolio_config (
                company_id, primary_color, secondary_color, logo_url,
                hero_title, hero_subtitle, hero_cta,
                about_text, about_image, company_values, company_competencies
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                primary_color = VALUES(primary_color),
                secondary_color = VALUES(secondary_color),
                logo_url = VALUES(logo_url),
                hero_title = VALUES(hero_title),
                hero_subtitle = VALUES(hero_subtitle),
                hero_cta = VALUES(hero_cta),
                about_text = VALUES(about_text),
                about_image = VALUES(about_image),
                company_values = VALUES(company_values),
                company_competencies = VALUES(company_competencies)
        ");

        $stmt->execute([
            $company_id,
            $corPrimaria,
            $corSecundaria,
            $urlLogotipo,
            $tituloHero,
            $subtituloHero,
            $textoBotao,
            $sobreNos,
            $urlImagemSobre,
            $nossosValores,
            $nossasCompetencias
        ]);

        // 3.2 Remover Planos
        if (!empty($dadosPost['planos_remover']) && is_array($dadosPost['planos_remover'])) {
            $stmtDel = $pdo->prepare("DELETE FROM planos WHERE id = ? AND company_id = ?");
            foreach ($dadosPost['planos_remover'] as $id) {
                try {
                    $stmtDel->execute([(int)$id, $company_id]);
                } catch (PDOException $e) {
                }
            }
        }

        // 3.3 Remover Filiais
        if (!empty($dadosPost['filiais_remover']) && is_array($dadosPost['filiais_remover'])) {
            $stmtDel = $pdo->prepare("DELETE FROM filiais WHERE id = ? AND company_id = ?");
            foreach ($dadosPost['filiais_remover'] as $id) {
                try {
                    $stmtDel->execute([(int)$id, $company_id]);
                } catch (PDOException $e) {
                }
            }
        }

        // 3.4 Remover Modalidades
        if (!empty($dadosPost['modalidades_remover']) && is_array($dadosPost['modalidades_remover'])) {
            $stmtDel = $pdo->prepare("DELETE FROM portfolio_modalities WHERE id = ?");
            foreach ($dadosPost['modalidades_remover'] as $id) {
                try {
                    $stmtDel->execute([(int)$id]);
                } catch (PDOException $e) {
                }
            }
        }

        // 3.5 Remover Slides
        if (!empty($dadosPost['slides_remover']) && is_array($dadosPost['slides_remover'])) {
            $stmtDel = $pdo->prepare("DELETE FROM portfolio_slides WHERE id = ? AND company_id = ?");
            foreach ($dadosPost['slides_remover'] as $id) {
                try {
                    $stmtDel->execute([(int)$id, $company_id]);
                } catch (PDOException $e) {
                }
            }
        }

        // 3.6 Atualizar / Inserir Planos
        if (isset($dadosPost['planos']) && is_array($dadosPost['planos'])) {
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

            foreach ($dadosPost['planos'] as $id => $data) {
                $nome = trim($data['nome'] ?? '');
                $categoria = trim($data['categoria'] ?? '');
                $valor = (float) str_replace(',', '.', (string)($data['valor'] ?? 0));
                $duracao = trim($data['duracao'] ?? '');

                if (strpos((string)$id, 'new_') === 0) {
                    $stmtInsert->execute([$company_id, $nome, $categoria, $valor, $duracao]);
                } else {
                    $stmtUpdate->execute([$nome, $valor, $categoria, $duracao, (int)$id, $company_id]);
                }
            }
        }

        // 3.7 Atualizar / Inserir Filiais
        if (isset($dadosPost['filiais']) && is_array($dadosPost['filiais'])) {
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

            foreach ($dadosPost['filiais'] as $id => $data) {
                $nome = trim($data['nome'] ?? '');
                $cnpj = trim($data['cnpj'] ?? '');
                $telefone = trim($data['telefone'] ?? '');
                $responsavel = trim($data['responsavel'] ?? '');

                if (strpos((string)$id, 'new_') === 0) {
                    $stmtInsert->execute([$company_id, $nome, $cnpj, $telefone, $responsavel]);
                } else {
                    $stmtUpdate->execute([$nome, $telefone, $cnpj, $responsavel, (int)$id, $company_id]);
                }
            }
        }

        // 3.8 Atualizar / Inserir Modalidades
        if (isset($dadosPost['modalidades']) && is_array($dadosPost['modalidades'])) {
            $stmtUpdate = $pdo->prepare("
                UPDATE portfolio_modalities
                SET name = ?, description = ?, image_url = ?
                WHERE id = ?
            ");

            $stmtInsert = $pdo->prepare("
                INSERT INTO portfolio_modalities
                (filial_id, name, description, image_url)
                VALUES (1, ?, ?, ?)
            ");

            foreach ($dadosPost['modalidades'] as $id => $data) {
                $name = trim($data['name'] ?? '');
                $description = trim($data['description'] ?? '');
                $imageUrl = trim($data['image_url'] ?? '');

                if (strpos((string)$id, 'new_') === 0) {
                    $stmtInsert->execute([$name, $description, $imageUrl]);
                } else {
                    $stmtUpdate->execute([$name, $description, $imageUrl, (int)$id]);
                }
            }
        }

        // 3.9 Atualizar / Inserir Slides
        if (isset($dadosPost['slides']) && is_array($dadosPost['slides'])) {
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

            foreach ($dadosPost['slides'] as $id => $data) {
                $imageUrl = trim($data['image_url'] ?? '');
                $ordem = (int) ($data['ordem'] ?? 0);
                $ativo = isset($data['ativo']) ? 1 : 0;

                if (strpos((string)$id, 'new_') === 0) {
                    $stmtInsert->execute([$company_id, $imageUrl, $ordem, $ativo]);
                } else {
                    $stmtUpdate->execute([$imageUrl, $ordem, $ativo, (int)$id, $company_id]);
                }
            }
        }
    } catch (Throwable $e) {
        $erroModel = $e->getMessage();
    }
}
