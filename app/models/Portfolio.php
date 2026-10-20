
<?php
// ================================================
// GYMFLOW - MODEL DO SITE / PORTFÓLIO
// app/models/Portfolio.php
// ================================================

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? 'buscar_publico';
$company_id = (int) ($company_id ?? 0);
$erroModel = '';

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
    'company_competencies' => '',
    'contact_title'        => 'Entre em contato conosco',
    'contact_subtitle'     => 'Nossa equipe está pronta para ajudar você.',
    'contact_form_title'   => 'Ficaremos felizes em te atender!',
    'contact_email'        => 'contato@gymflow.com',
    'contact_phone'        => '(11) 98765-4321',
    'contact_hours'        => 'Segunda a sexta: 06h às 22h | Sábado: 08h às 18h | Domingo: 08h às 14h',
    'contact_image'        => '',
    'instagram_url'        => '',
    'facebook_url'         => '',
    'tiktok_url'           => '',
    'whatsapp_url'         => '',
    'feedback_title'       => 'Como foi sua experiência?',
    'feedback_subtitle'    => 'Envie seu feedback e nos ajude a melhorar sua experiência.',
    'feedback_image'       => '',
    'accepts_wellhub'      => 1,
    'wellhub_icon'         => '',
    'accepts_totalpass'    => 1,
    'totalpass_icon'       => ''
];

// ================================================
// FUNÇÕES AUXILIARES
// ================================================

if (!function_exists('portfolioConsultar')) {
    function portfolioConsultar(
        PDO $pdo,
        string $sql,
        array $params = []
    ): array {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

if (!function_exists('portfolioExecutar')) {
    function portfolioExecutar(
        PDO $pdo,
        string $sql,
        array $params = []
    ): PDOStatement {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }
}

if (!function_exists('portfolioArquivoLista')) {
    function portfolioArquivoLista(
        array $grupo,
        string $chave
    ): ?array {
        if (!isset($grupo['error'][$chave])) {
            return null;
        }

        return [
            'name'     => $grupo['name'][$chave] ?? '',
            'type'     => $grupo['type'][$chave] ?? '',
            'tmp_name' => $grupo['tmp_name'][$chave] ?? '',
            'error'    => $grupo['error'][$chave],
            'size'     => $grupo['size'][$chave] ?? 0
        ];
    }
}

if (!function_exists('portfolioSalvarImagem')) {
    function portfolioSalvarImagem(
        array $arquivo,
        string $prefixo
    ): ?string {
        $erro = $arquivo['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($erro === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($erro !== UPLOAD_ERR_OK) {
            throw new RuntimeException(
                'Erro no envio da imagem: ' . $prefixo
            );
        }

        $tamanho = (int) ($arquivo['size'] ?? 0);

        if ($tamanho <= 0 || $tamanho > 5 * 1024 * 1024) {
            throw new RuntimeException(
                'A imagem deve ter no máximo 5 MB.'
            );
        }

        $tmp = $arquivo['tmp_name'] ?? '';

        if (
            $tmp === '' ||
            !is_uploaded_file($tmp)
        ) {
            throw new RuntimeException(
                'Arquivo de upload inválido.'
            );
        }

        $info = @getimagesize($tmp);
        $mime = is_array($info) ? ($info['mime'] ?? '') : '';

        $permitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($permitidos[$mime])) {
            throw new RuntimeException(
                'Formato inválido. Utilize JPG, PNG ou WEBP.'
            );
        }

        $raiz = dirname(__DIR__, 2);
        $pasta = $raiz . '/assets/uploads/portfolio/';

        if (
            !is_dir($pasta) &&
            !mkdir($pasta, 0775, true) &&
            !is_dir($pasta)
        ) {
            throw new RuntimeException(
                'Não foi possível criar a pasta de uploads.'
            );
        }

        $nome = $prefixo . '_' .
            bin2hex(random_bytes(8)) . '.' .
            $permitidos[$mime];

        if (!move_uploaded_file($tmp, $pasta . $nome)) {
            throw new RuntimeException(
                'Não foi possível salvar a imagem.'
            );
        }

        return BASE_URL . '/assets/uploads/portfolio/' . $nome;
    }
}

if (!function_exists('portfolioImagemCampo')) {
    function portfolioImagemCampo(
        array $post,
        array $files,
        string $campoUrl,
        string $campoArquivo,
        string $prefixo
    ): string {
        $url = trim((string) ($post[$campoUrl] ?? ''));

        if (
            isset($files[$campoArquivo]) &&
            is_array($files[$campoArquivo])
        ) {
            $nova = portfolioSalvarImagem(
                $files[$campoArquivo],
                $prefixo
            );

            if ($nova !== null) {
                $url = $nova;
            }
        }

        return $url;
    }
}

if (!function_exists('portfolioImagemItem')) {
    function portfolioImagemItem(
        array $dados,
        array $files,
        string $tipo,
        string $id,
        string $prefixo
    ): string {
        $url = trim((string) ($dados['image_url'] ?? ''));

        $campoArquivo = $tipo . '_arquivo';

        if (
            isset($files[$campoArquivo]) &&
            is_array($files[$campoArquivo])
        ) {
            $arquivo = portfolioArquivoLista(
                $files[$campoArquivo],
                $id
            );

            if ($arquivo !== null) {
                $nova = portfolioSalvarImagem(
                    $arquivo,
                    $prefixo
                );

                if ($nova !== null) {
                    $url = $nova;
                }
            }
        }

        return $url;
    }
}

// ================================================
// VALIDAÇÃO MULTI-TENANT
// ================================================

try {
    if ($company_id <= 0) {
        throw new RuntimeException(
            'Academia não identificada.'
        );
    }

    $empresa = portfolioConsultar(
        $pdo,
        'SELECT id FROM companies WHERE id = ? LIMIT 1',
        [$company_id]
    );

    if (empty($empresa)) {
        throw new RuntimeException(
            'Academia não encontrada.'
        );
    }

    // ============================================
    // 1. CONSULTAR SITE PÚBLICO OU ADMINISTRATIVO
    // ============================================

    if (
        $operacao === 'buscar_publico' ||
        $operacao === 'buscar' ||
        $operacao === 'buscar_admin'
    ) {
        $administrativo = $operacao === 'buscar_admin';

        // CONFIGURAÇÕES

        $configBanco = portfolioConsultar(
            $pdo,
            'SELECT * FROM portfolio_config
             WHERE company_id = ?
             LIMIT 1',
            [$company_id]
        );

        $config = array_merge(
            $configPadrao,
            $configBanco[0] ?? []
        );

        // PLANOS - FONTE OFICIAL

        $planos = portfolioConsultar(
            $pdo,
            'SELECT * FROM planos
             WHERE company_id = ?
             ORDER BY id ASC',
            [$company_id]
        );

        // FILIAIS - FONTE OFICIAL

        $filiais = portfolioConsultar(
            $pdo,
            'SELECT
                f.*,
                pfi.image_url
             FROM filiais f
             LEFT JOIN portfolio_filial_images pfi
                ON pfi.filial_id = f.id
                AND pfi.company_id = f.company_id
             WHERE f.company_id = ?
                AND f.ativo = 1
             ORDER BY f.id ASC',
            [$company_id]
        );

        // MODALIDADES - ISOLAMENTO MULTI-TENANT
        //
        // A modalidade pertence a uma filial.
        // A filial pertence a uma academia.
        //
        // Portanto, filtramos pelo company_id
        // através da tabela filiais.

        $modalidades = portfolioConsultar(
            $pdo,
            'SELECT
                pm.id,
                pm.filial_id,
                pm.name,
                pm.description,
                pm.image_url
             FROM portfolio_modalities pm
             INNER JOIN filiais f
                ON f.id = pm.filial_id
             WHERE f.company_id = ?
                AND f.ativo = 1
             ORDER BY pm.id ASC',
            [$company_id]
        );

        // SLIDES

        $sqlSlides = '
            SELECT *
            FROM portfolio_slides
            WHERE company_id = ?
        ';

        if (!$administrativo) {
            $sqlSlides .= ' AND ativo = 1';
        }

        $sqlSlides .= ' ORDER BY ordem ASC, id ASC';

        $slides = portfolioConsultar(
            $pdo,
            $sqlSlides,
            [$company_id]
        );

        // AVALIAÇÕES

        if ($administrativo) {
            $feedbacks = portfolioConsultar(
                $pdo,
                'SELECT *
                 FROM portfolio_feedbacks
                 WHERE company_id = ?
                 ORDER BY criado_em DESC, id DESC',
                [$company_id]
            );
        } else {
            $feedbacks = portfolioConsultar(
                $pdo,
                'SELECT *
                 FROM portfolio_feedbacks
                 WHERE company_id = ?
                    AND ativo = 1
                 ORDER BY criado_em DESC, id DESC
                 LIMIT 3',
                [$company_id]
            );
        }

        $tituloPagina = $administrativo
            ? 'Portfólio'
            : ($config['app_name'] ?? 'GymCore');
    }

    // ============================================
    // 2. SALVAR CONFIGURAÇÕES DO PORTFÓLIO
    // ============================================

    elseif ($operacao === 'salvar') {
        $dadosPost = $dadosPost ?? $_POST;
        $dadosFiles = $dadosFiles ?? $_FILES;

        // Apenas o painel Admin deve executar
        // esta operação. O controller já valida
        // a role e a empresa da sessão.

        // ========================================
        // 2.1 CONFIGURAÇÕES GERAIS
        // ========================================

        $corPrimaria = trim(
            (string) ($dadosPost['corPrimaria'] ?? '#C9A227')
        );

        $corSecundaria = trim(
            (string) ($dadosPost['corSecundaria'] ?? '#000000')
        );

        if (
            !preg_match('/^#[0-9a-fA-F]{6}$/', $corPrimaria) ||
            !preg_match('/^#[0-9a-fA-F]{6}$/', $corSecundaria)
        ) {
            throw new RuntimeException(
                'As cores informadas são inválidas.'
            );
        }

        $urlLogotipo = portfolioImagemCampo(
            $dadosPost,
            $dadosFiles,
            'urlLogotipo',
            'logo_arquivo',
            'logo'
        );

        $urlImagemSobre = portfolioImagemCampo(
            $dadosPost,
            $dadosFiles,
            'urlImagemSobre',
            'sobre_arquivo',
            'sobre'
        );

        $urlImagemContato = portfolioImagemCampo(
            $dadosPost,
            $dadosFiles,
            'urlImagemContato',
            'contato_arquivo',
            'contato'
        );

        $urlImagemFeedback = portfolioImagemCampo(
            $dadosPost,
            $dadosFiles,
            'urlImagemFeedback',
            'feedback_arquivo',
            'feedback'
        );

        $urlIconeWellhub = portfolioImagemCampo(
            $dadosPost,
            $dadosFiles,
            'urlIconeWellhub',
            'wellhub_arquivo',
            'wellhub'
        );

        $urlIconeTotalpass = portfolioImagemCampo(
            $dadosPost,
            $dadosFiles,
            'urlIconeTotalpass',
            'totalpass_arquivo',
            'totalpass'
        );

        $camposConfig = [
            'primary_color'        => $corPrimaria,
            'secondary_color'      => $corSecundaria,
            'logo_url'             => $urlLogotipo,
            'hero_title'           => $dadosPost['tituloHero'] ?? '',
            'hero_subtitle'        => $dadosPost['subtituloHero'] ?? '',
            'hero_cta'             => $dadosPost['textoBotao'] ?? '',
            'about_text'           => $dadosPost['sobreNos'] ?? '',
            'about_image'          => $urlImagemSobre,
            'company_values'       => $dadosPost['nossosValores'] ?? '',
            'company_competencies' => $dadosPost['nossasCompetencias'] ?? '',
            'contact_title'        => $dadosPost['tituloContato'] ?? '',
            'contact_subtitle'     => $dadosPost['subtituloContato'] ?? '',
            'contact_form_title'   => $dadosPost['tituloFormularioContato'] ?? '',
            'contact_email'        => $dadosPost['emailContato'] ?? '',
            'contact_phone'        => $dadosPost['telefoneContato'] ?? '',
            'contact_hours'        => $dadosPost['horarioContato'] ?? '',
            'contact_image'        => $urlImagemContato,
            'instagram_url'        => $dadosPost['instagramUrl'] ?? '',
            'facebook_url'         => $dadosPost['facebookUrl'] ?? '',
            'tiktok_url'           => $dadosPost['tiktokUrl'] ?? '',
            'whatsapp_url'         => $dadosPost['whatsappUrl'] ?? '',
            'feedback_title'       => $dadosPost['tituloFeedback'] ?? '',
            'feedback_subtitle'    => $dadosPost['subtituloFeedback'] ?? '',
            'feedback_image'       => $urlImagemFeedback,
            'accepts_wellhub'      => isset($dadosPost['aceitaWellhub']) ? 1 : 0,
            'wellhub_icon'         => $urlIconeWellhub,
            'accepts_totalpass'    => isset($dadosPost['aceitaTotalpass']) ? 1 : 0,
            'totalpass_icon'       => $urlIconeTotalpass
        ];

        // ========================================
        // 2.2 TRANSAÇÃO DO BANCO
        // ========================================

        $pdo->beginTransaction();

        try {
            // Configurações da academia

            $colunas = array_keys($camposConfig);

            $colunasInsert = implode(', ', $colunas);
            $placeholders = implode(
                ', ',
                array_fill(0, count($colunas), '?')
            );

            $atualizacoes = implode(
                ', ',
                array_map(
                    static fn($coluna) =>
                    "$coluna = VALUES($coluna)",
                    $colunas
                )
            );

            $sqlConfig = "
                INSERT INTO portfolio_config (
                    company_id,
                    $colunasInsert
                ) VALUES (
                    ?,
                    $placeholders
                )
                ON DUPLICATE KEY UPDATE
                    $atualizacoes
            ";

            portfolioExecutar(
                $pdo,
                $sqlConfig,
                array_merge(
                    [$company_id],
                    array_values($camposConfig)
                )
            );

            // ====================================
            // 2.3 PLANOS
            // ====================================
            //
            // Mantidos temporariamente para
            // preservar a compatibilidade com
            // o formulário atual.
            //
            // Na próxima etapa, vamos centralizar
            // o CRUD no módulo Planos.

            if (
                isset($dadosPost['planos_remover']) &&
                is_array($dadosPost['planos_remover'])
            ) {
                $stmt = $pdo->prepare(
                    'DELETE FROM planos
                     WHERE id = ? AND company_id = ?'
                );

                foreach ($dadosPost['planos_remover'] as $id) {
                    try {
                        $stmt->execute([
                            (int) $id,
                            $company_id
                        ]);
                    } catch (PDOException $e) {
                        // Compatibilidade temporária:
                        // FK pode impedir a exclusão.
                        error_log(
                            '[GymFlow][Portfolio] Plano não removido: ' .
                                $e->getMessage()
                        );
                    }
                }
            }

            if (
                isset($dadosPost['planos']) &&
                is_array($dadosPost['planos'])
            ) {
                foreach ($dadosPost['planos'] as $id => $data) {
                    $nome = trim($data['nome'] ?? '');
                    $categoria = trim($data['categoria'] ?? '');
                    $valor = (float) str_replace(
                        ',',
                        '.',
                        (string) ($data['valor'] ?? 0)
                    );
                    $duracao = trim($data['duracao'] ?? '');

                    if (str_starts_with((string) $id, 'new_')) {
                        portfolioExecutar(
                            $pdo,
                            'INSERT INTO planos
                             (company_id, nome, categoria, valor, duracao)
                             VALUES (?, ?, ?, ?, ?)',
                            [
                                $company_id,
                                $nome,
                                $categoria,
                                $valor,
                                $duracao
                            ]
                        );
                    } else {
                        portfolioExecutar(
                            $pdo,
                            'UPDATE planos
                             SET nome = ?, valor = ?,
                                 categoria = ?, duracao = ?
                             WHERE id = ? AND company_id = ?',
                            [
                                $nome,
                                $valor,
                                $categoria,
                                $duracao,
                                (int) $id,
                                $company_id
                            ]
                        );
                    }
                }
            }

            // ====================================
            // 2.4 FILIAIS
            // ====================================
            //
            // Também mantidas temporariamente
            // para não quebrar a view atual.

            if (
                isset($dadosPost['filiais_remover']) &&
                is_array($dadosPost['filiais_remover'])
            ) {
                $stmt = $pdo->prepare(
                    'DELETE FROM filiais
                     WHERE id = ? AND company_id = ?'
                );

                foreach ($dadosPost['filiais_remover'] as $id) {
                    try {
                        $stmt->execute([
                            (int) $id,
                            $company_id
                        ]);
                    } catch (PDOException $e) {
                        error_log(
                            '[GymFlow][Portfolio] Filial não removida: ' .
                                $e->getMessage()
                        );
                    }
                }
            }

            if (
                isset($dadosPost['filiais']) &&
                is_array($dadosPost['filiais'])
            ) {
                foreach ($dadosPost['filiais'] as $id => $data) {
                    $nome = trim($data['nome'] ?? '');
                    $cnpj = trim($data['cnpj'] ?? '');
                    $telefone = trim($data['telefone'] ?? '');
                    $responsavel = trim($data['responsavel'] ?? '');

                    $latitude = (
                        isset($data['latitude']) &&
                        $data['latitude'] !== ''
                    )
                        ? (float) $data['latitude']
                        : null;

                    $longitude = (
                        isset($data['longitude']) &&
                        $data['longitude'] !== ''
                    )
                        ? (float) $data['longitude']
                        : null;

                    $imageUrl = portfolioImagemItem(
                        $data,
                        $dadosFiles,
                        'filiais',
                        (string) $id,
                        'filial'
                    );

                    if (str_starts_with((string) $id, 'new_')) {
                        portfolioExecutar(
                            $pdo,
                            'INSERT INTO filiais
                             (
                                company_id, nome, cnpj,
                                telefone, responsavel,
                                ativo, latitude, longitude
                             )
                             VALUES (?, ?, ?, ?, ?, 1, ?, ?)',
                            [
                                $company_id,
                                $nome,
                                $cnpj,
                                $telefone,
                                $responsavel,
                                $latitude,
                                $longitude
                            ]
                        );

                        $filialId = (int) $pdo->lastInsertId();
                    } else {
                        $filialId = (int) $id;

                        portfolioExecutar(
                            $pdo,
                            'UPDATE filiais
                             SET nome = ?, telefone = ?,
                                 cnpj = ?, responsavel = ?,
                                 latitude = ?, longitude = ?
                             WHERE id = ? AND company_id = ?',
                            [
                                $nome,
                                $telefone,
                                $cnpj,
                                $responsavel,
                                $latitude,
                                $longitude,
                                $filialId,
                                $company_id
                            ]
                        );
                    }

                    // Verifica a propriedade da filial
                    // antes de associar a imagem.

                    if ($imageUrl !== '') {
                        $filialPermitida = portfolioConsultar(
                            $pdo,
                            'SELECT id FROM filiais
                             WHERE id = ? AND company_id = ?
                             LIMIT 1',
                            [$filialId, $company_id]
                        );

                        if (empty($filialPermitida)) {
                            throw new RuntimeException(
                                'Filial inválida para esta academia.'
                            );
                        }

                        portfolioExecutar(
                            $pdo,
                            'INSERT INTO portfolio_filial_images
                             (company_id, filial_id, image_url)
                             VALUES (?, ?, ?)
                             ON DUPLICATE KEY UPDATE
                                image_url = VALUES(image_url)',
                            [
                                $company_id,
                                $filialId,
                                $imageUrl
                            ]
                        );
                    }
                }
            }

            // ====================================
            // 2.5 MODALIDADES - MULTI-TENANT
            // ====================================

            // Exclusão restrita à academia dona
            // da filial vinculada à modalidade.

            if (
                isset($dadosPost['modalidades_remover']) &&
                is_array($dadosPost['modalidades_remover'])
            ) {
                foreach ($dadosPost['modalidades_remover'] as $id) {
                    portfolioExecutar(
                        $pdo,
                        'DELETE pm
                         FROM portfolio_modalities pm
                         INNER JOIN filiais f
                            ON f.id = pm.filial_id
                         WHERE pm.id = ?
                            AND f.company_id = ?',
                        [(int) $id, $company_id]
                    );
                }
            }

            if (
                isset($dadosPost['modalidades']) &&
                is_array($dadosPost['modalidades'])
            ) {
                // Filial padrão provisória:
                // primeira filial ativa da empresa.

                $filialPadrao = portfolioConsultar(
                    $pdo,
                    'SELECT id
                     FROM filiais
                     WHERE company_id = ?
                       AND ativo = 1
                     ORDER BY id ASC
                     LIMIT 1',
                    [$company_id]
                );

                $filialPadraoId = (int) (
                    $filialPadrao[0]['id'] ?? 0
                );

                foreach ($dadosPost['modalidades'] as $id => $data) {
                    $nome = trim($data['name'] ?? '');
                    $descricao = trim(
                        $data['description'] ?? ''
                    );

                    $imagem = portfolioImagemItem(
                        $data,
                        $dadosFiles,
                        'modalidades',
                        (string) $id,
                        'modalidade'
                    );

                    if ($nome === '') {
                        throw new RuntimeException(
                            'Informe o nome da modalidade.'
                        );
                    }

                    if (str_starts_with((string) $id, 'new_')) {
                        if ($filialPadraoId <= 0) {
                            throw new RuntimeException(
                                'Cadastre uma filial ativa antes de adicionar modalidades.'
                            );
                        }

                        portfolioExecutar(
                            $pdo,
                            'INSERT INTO portfolio_modalities
                             (
                                filial_id,
                                name,
                                description,
                                image_url
                             )
                             VALUES (?, ?, ?, ?)',
                            [
                                $filialPadraoId,
                                $nome,
                                $descricao,
                                $imagem
                            ]
                        );
                    } else {
                        // Atualização limitada à
                        // academia proprietária.

                        portfolioExecutar(
                            $pdo,
                            'UPDATE portfolio_modalities pm
                             INNER JOIN filiais f
                                ON f.id = pm.filial_id
                             SET
                                pm.name = ?,
                                pm.description = ?,
                                pm.image_url = ?
                             WHERE pm.id = ?
                               AND f.company_id = ?',
                            [
                                $nome,
                                $descricao,
                                $imagem,
                                (int) $id,
                                $company_id
                            ]
                        );
                    }
                }
            }

            // ====================================
            // 2.6 SLIDES
            // ====================================

            if (
                isset($dadosPost['slides_remover']) &&
                is_array($dadosPost['slides_remover'])
            ) {
                foreach ($dadosPost['slides_remover'] as $id) {
                    portfolioExecutar(
                        $pdo,
                        'DELETE FROM portfolio_slides
                         WHERE id = ? AND company_id = ?',
                        [(int) $id, $company_id]
                    );
                }
            }

            if (
                isset($dadosPost['slides']) &&
                is_array($dadosPost['slides'])
            ) {
                foreach ($dadosPost['slides'] as $id => $data) {
                    $imagem = portfolioImagemItem(
                        $data,
                        $dadosFiles,
                        'slides',
                        (string) $id,
                        'slide'
                    );

                    $ordem = (int) ($data['ordem'] ?? 0);
                    $ativo = isset($data['ativo']) ? 1 : 0;

                    if (str_starts_with((string) $id, 'new_')) {
                        if ($imagem === '') {
                            throw new RuntimeException(
                                'Selecione uma imagem para o slide.'
                            );
                        }

                        portfolioExecutar(
                            $pdo,
                            'INSERT INTO portfolio_slides
                             (
                                company_id,
                                image_url,
                                ordem,
                                ativo
                             )
                             VALUES (?, ?, ?, ?)',
                            [
                                $company_id,
                                $imagem,
                                $ordem,
                                $ativo
                            ]
                        );
                    } else {
                        portfolioExecutar(
                            $pdo,
                            'UPDATE portfolio_slides
                             SET image_url = ?,
                                 ordem = ?,
                                 ativo = ?
                             WHERE id = ?
                               AND company_id = ?',
                            [
                                $imagem,
                                $ordem,
                                $ativo,
                                (int) $id,
                                $company_id
                            ]
                        );
                    }
                }
            }

            // ====================================
            // 2.7 AVALIAÇÕES
            // ====================================

            if (
                isset($dadosPost['feedbacks_remover']) &&
                is_array($dadosPost['feedbacks_remover'])
            ) {
                foreach ($dadosPost['feedbacks_remover'] as $id) {
                    portfolioExecutar(
                        $pdo,
                        'DELETE FROM portfolio_feedbacks
                         WHERE id = ? AND company_id = ?',
                        [(int) $id, $company_id]
                    );
                }
            }

            if (
                isset($dadosPost['feedbacks']) &&
                is_array($dadosPost['feedbacks'])
            ) {
                foreach ($dadosPost['feedbacks'] as $id => $data) {
                    $ativo = isset($data['ativo']) ? 1 : 0;

                    portfolioExecutar(
                        $pdo,
                        'UPDATE portfolio_feedbacks
                         SET ativo = ?
                         WHERE id = ? AND company_id = ?',
                        [
                            $ativo,
                            (int) $id,
                            $company_id
                        ]
                    );
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    } else {
        throw new RuntimeException(
            'Operação do Portfólio desconhecida.'
        );
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $erroModel = $e->getMessage();

    error_log(
        '[GymFlow][Portfolio] Empresa ' .
            $company_id . ': ' .
            $erroModel
    );

    // Evita dados parcialmente carregados.
    if ($operacao !== 'salvar') {
        $config = $configPadrao;
        $planos = [];
        $filiais = [];
        $modalidades = [];
        $slides = [];
        $feedbacks = [];
        $tituloPagina = 'Portfólio';
    }
}
