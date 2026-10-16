   <?php
    if (!isset($tituloPagina)) {
        header("Location: " . BASE_URL . "/app/controllers/PortfolioController.php");
        exit;
    }

    $config = $config ?? [];
    $slides = $slides ?? [];
    $modalidades = $modalidades ?? [];
    $planos = $planos ?? [];
    $filiais = $filiais ?? [];

    $tituloPagina = "Site / Portfólio";
    $cssEspecifico = BASE_URL . "/assets/css/portfolio-admin.css";
    include __DIR__ . '/../shared/header.php';
    ?>

   <?php include __DIR__ . '/../shared/sidebar.php'; ?>

   <main class="portfolio-admin-page">
       <form class="portfolio-admin-form"
           method="POST"
           action="<?= BASE_URL ?>/app/controllers/PortfolioController.php?acao=salvar"
           id="portfolioForm"
           enctype="multipart/form-data">
           <header class="portfolio-admin-header">
               <div>
                   <span class="portfolio-admin-eyebrow">PRESENÇA DIGITAL</span>
                   <h1>Site / Portfólio Público</h1>
                   <p>Personalize o site da academia e salve suas alterações para publicá-las.</p>
                   <?php if (isset($_GET['status']) && $_GET['status'] === 'sucesso'): ?>

                       <div class="mensagem-status mensagem-sucesso">
                           <div class="mensagem-icone">✓</div>

                           <div>
                               <strong>Alterações salvas!</strong>
                               <p>As configurações do portfólio foram atualizadas com sucesso.</p>
                           </div>
                       </div>

                   <?php elseif (isset($_GET['status']) && $_GET['status'] === 'erro'): ?>

                       <div class="mensagem-status mensagem-erro">
                           <div class="mensagem-icone">!</div>

                           <div>
                               <strong>Não foi possível salvar</strong>
                               <p>Confira os dados e a imagem selecionada e tente novamente.</p>
                           </div>
                       </div>

                   <?php endif; ?>
               </div>
               <div>
                   <div class="portfolio-admin-header-actions">
                       <a class="portfolio-admin-preview-link" href="<?= BASE_URL ?>/index.php" target="_blank" rel="noopener noreferrer">Visualizar site ↗</a>
                       <button type="submit" class="portfolio-admin-save">Salvar alterações</button>
                   </div>
               </div>
           </header>

           <div class="portfolio-admin-editor">
               <section class="portfolio-admin-editor-content">
                   <nav class="portfolio-admin-tabs" aria-label="Seções do portfólio">
                       <ul>
                           <li><button type="button" class="tab-btn" data-target="tab-design">Design</button></li>
                           <li><button type="button" class="tab-btn" data-target="tab-conteudo">Conteúdo</button></li>
                           <li><button type="button" class="tab-btn" data-target="tab-carrossel">Carrossel</button></li>
                           <li><button type="button" class="tab-btn" data-target="tab-planos">Planos</button></li>
                           <li><button type="button" class="tab-btn" data-target="tab-unidades">Unidades</button></li>
                           <li><button type="button" class="tab-btn" data-target="tab-feedbacks">Avaliações</button></li>
                       </ul>
                   </nav>

                   <!-- ================= DESIGN ================= -->
                   <div id="tab-design" class="tab-content">
                       <h2>Design</h2>
                       <div>
                           <label>Cor Primária</label>
                           <input type="color" id="corPrimaria" name="corPrimaria" value="<?= htmlspecialchars($config['primary_color'] ?? '#C9A227') ?>">
                       </div>
                       <div>
                           <label>Cor Secundária</label>
                           <input type="color" id="corSecundaria" name="corSecundaria" value="<?= htmlspecialchars($config['secondary_color'] ?? '#000000') ?>">
                       </div>
                       <div>
                           <label>Logótipo:</label>

                           <input
                               type="file"
                               name="logo_arquivo"
                               accept="image/png, image/jpeg, image/webp">

                           <input
                               type="hidden"
                               name="urlLogotipo"
                               value="<?= htmlspecialchars($config['logo_url'] ?? '') ?>">

                           <?php if (!empty($config['logo_url'])): ?>
                               <div>
                                   <img
                                       src="<?= htmlspecialchars($config['logo_url']) ?>"
                                       alt="Logótipo atual">
                               </div>
                           <?php endif; ?>
                       </div>
                   </div>

                   <!-- ================= CONTEÚDO ================= -->
                   <div id="tab-conteudo" class="tab-content">
                       <h2>Conteúdo Base</h2>
                       <div>
                           <label>Título do Hero</label>
                           <input type="text" id="tituloHero" name="tituloHero" value="<?= htmlspecialchars($config['hero_title'] ?? '') ?>">
                       </div>
                       <div>
                           <label>Subtítulo do Hero</label>
                           <textarea id="subtituloHero" name="subtituloHero"><?= htmlspecialchars($config['hero_subtitle'] ?? '') ?></textarea>
                       </div>
                       <div>
                           <label>Texto do botão (CTA)</label>
                           <input type="text" id="textoBotao" name="textoBotao" value="<?= htmlspecialchars($config['hero_cta'] ?? '') ?>">
                       </div>
                       <div>
                           <label>Sobre Nós</label>
                           <textarea name="sobreNos"><?= htmlspecialchars($config['about_text'] ?? '') ?></textarea>
                       </div>
                       <div>
                           <label>Imagem do Sobre Nós:</label>

                           <input
                               type="file"
                               name="sobre_arquivo"
                               accept="image/png, image/jpeg, image/webp">

                           <input
                               type="hidden"
                               name="urlImagemSobre"
                               value="<?= htmlspecialchars($config['about_image'] ?? '') ?>">

                           <?php if (!empty($config['about_image'])): ?>
                               <div>
                                   <img
                                       src="<?= htmlspecialchars($config['about_image']) ?>"
                                       alt="Imagem atual do Sobre Nós">
                               </div>
                           <?php endif; ?>
                       </div>
                       <div>
                           <label>Nossos Valores</label>
                           <textarea name="nossosValores"><?= htmlspecialchars($config['company_values'] ?? '') ?></textarea>
                       </div>
                       <div>
                           <label>Nossas Competências</label>
                           <textarea name="nossasCompetencias"><?= htmlspecialchars($config['company_competencies'] ?? '') ?></textarea>
                       </div>
                       <h3>Contato</h3>

                       <div>
                           <label>Título da seção de contato</label>
                           <input
                               type="text"
                               name="tituloContato"
                               value="<?= htmlspecialchars($config['contact_title'] ?? '') ?>">
                       </div>

                       <div>
                           <label>Subtítulo da seção de contato</label>
                           <textarea
                               name="subtituloContato"><?= htmlspecialchars($config['contact_subtitle'] ?? '') ?></textarea>
                       </div>

                       <div>
                           <label>E-mail de contato</label>
                           <input
                               type="email"
                               name="emailContato"
                               value="<?= htmlspecialchars($config['contact_email'] ?? '') ?>">
                       </div>

                       <div>
                           <label>Telefone / WhatsApp</label>
                           <input
                               type="text"
                               name="telefoneContato"
                               value="<?= htmlspecialchars($config['contact_phone'] ?? '') ?>">
                       </div>

                       <div>
                           <label>Horário de atendimento</label>
                           <textarea
                               name="horarioContato"><?= htmlspecialchars($config['contact_hours'] ?? '') ?></textarea>
                       </div>

                       <div>
                           <label>Imagem da seção de contato:</label>

                           <input
                               type="file"
                               name="contato_arquivo"
                               accept="image/png, image/jpeg, image/webp">

                           <input
                               type="hidden"
                               name="urlImagemContato"
                               value="<?= htmlspecialchars($config['contact_image'] ?? '') ?>">

                           <?php if (!empty($config['contact_image'])): ?>
                               <div>
                                   <img
                                       src="<?= htmlspecialchars($config['contact_image']) ?>"
                                       alt="Imagem atual do Contato">
                               </div>
                           <?php endif; ?>
                       </div>

                       <h4>Redes Sociais</h4>

                       <div>
                           <label>Instagram</label>
                           <input
                               type="text"
                               name="instagramUrl"
                               value="<?= htmlspecialchars($config['instagram_url'] ?? '') ?>"
                               placeholder="https://instagram.com/...">
                       </div>

                       <div>
                           <label>Facebook</label>
                           <input
                               type="text"
                               name="facebookUrl"
                               value="<?= htmlspecialchars($config['facebook_url'] ?? '') ?>"
                               placeholder="https://facebook.com/...">
                       </div>

                       <div>
                           <label>TikTok</label>
                           <input
                               type="text"
                               name="tiktokUrl"
                               value="<?= htmlspecialchars($config['tiktok_url'] ?? '') ?>"
                               placeholder="https://tiktok.com/@...">
                       </div>

                       <div>
                           <label>WhatsApp</label>
                           <input
                               type="text"
                               name="whatsappUrl"
                               value="<?= htmlspecialchars($config['whatsapp_url'] ?? '') ?>"
                               placeholder="https://wa.me/...">
                       </div>

                       <h3>Feedback</h3>

                       <div>
                           <label>Título da seção de feedback</label>
                           <input
                               type="text"
                               name="tituloFeedback"
                               value="<?= htmlspecialchars($config['feedback_title'] ?? '') ?>">
                       </div>

                       <div>
                           <label>Subtítulo da seção de feedback</label>
                           <textarea
                               name="subtituloFeedback"><?= htmlspecialchars($config['feedback_subtitle'] ?? '') ?></textarea>
                       </div>

                       <div>
                           <label>Imagem da seção de feedback:</label>

                           <input
                               type="file"
                               name="feedback_arquivo"
                               accept="image/png, image/jpeg, image/webp">

                           <input
                               type="hidden"
                               name="urlImagemFeedback"
                               value="<?= htmlspecialchars($config['feedback_image'] ?? '') ?>">

                           <?php if (!empty($config['feedback_image'])): ?>
                               <div>
                                   <img
                                       src="<?= htmlspecialchars($config['feedback_image']) ?>"
                                       alt="Imagem atual do Feedback">
                               </div>
                           <?php endif; ?>
                       </div>

                       <h3>Benefícios aceitos</h3>
                       <p>
                           Marque os benefícios aceitos pela academia e, se quiser, envie a logo que será exibida no site público.
                       </p>

                       <fieldset class="portfolio-admin-item">
                           <legend>Wellhub (Gympass)</legend>

                           <div>
                               <label>
                                   <input
                                       type="checkbox"
                                       name="aceitaWellhub"
                                       value="1"
                                       <?= !empty($config['accepts_wellhub']) ? 'checked' : '' ?>>
                                   <span>A academia aceita Wellhub (Gympass)</span>
                               </label>
                           </div>

                           <div>
                               <label>Logo / ícone do Wellhub:</label>

                               <input
                                   type="file"
                                   name="wellhub_arquivo"
                                   accept="image/png, image/jpeg, image/webp">

                               <input
                                   type="hidden"
                                   name="urlIconeWellhub"
                                   value="<?= htmlspecialchars($config['wellhub_icon'] ?? '') ?>">

                               <?php if (!empty($config['wellhub_icon'])): ?>
                                   <div>
                                       <img
                                           src="<?= htmlspecialchars($config['wellhub_icon']) ?>"
                                           alt="Logo atual do Wellhub">
                                   </div>
                               <?php endif; ?>
                           </div>
                       </fieldset>

                       <fieldset class="portfolio-admin-item">
                           <legend>TotalPass</legend>

                           <div>
                               <label>
                                   <input
                                       type="checkbox"
                                       name="aceitaTotalpass"
                                       value="1"
                                       <?= !empty($config['accepts_totalpass']) ? 'checked' : '' ?>>
                                   <span>A academia aceita TotalPass</span>
                               </label>
                           </div>

                           <div>
                               <label>Logo / ícone do TotalPass:</label>

                               <input
                                   type="file"
                                   name="totalpass_arquivo"
                                   accept="image/png, image/jpeg, image/webp">

                               <input
                                   type="hidden"
                                   name="urlIconeTotalpass"
                                   value="<?= htmlspecialchars($config['totalpass_icon'] ?? '') ?>">

                               <?php if (!empty($config['totalpass_icon'])): ?>
                                   <div>
                                       <img
                                           src="<?= htmlspecialchars($config['totalpass_icon']) ?>"
                                           alt="Logo atual do TotalPass">
                                   </div>
                               <?php endif; ?>
                           </div>
                       </fieldset>

                       <hr>
                       <div>
                           <h3>Modalidades Oferecidas</h3>
                           <button class="portfolio-admin-add" type="button" onclick="adicionarItem('modalidades')">+ Adicionar</button>
                       </div>
                       <div class="portfolio-admin-items" id="container-modalidades">
                           <?php foreach ($modalidades as $mod): ?>
                               <fieldset class="portfolio-admin-item" id="mod_<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>">
                                   <div><button class="portfolio-admin-remove" type="button" onclick="removerItem('mod_<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>', 'modalidades', <?= (int)($mod['id'] ?? 0) ?>)">X Remover</button></div>
                                   <input type="hidden" name="modalidades[<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>][id]" value="<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>">
                                   <div><label>Nome:</label><input type="text" name="modalidades[<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>][name]" value="<?= htmlspecialchars($mod['name'] ?? '') ?>"></div>
                                   <div><label>Descrição:</label><textarea name="modalidades[<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>][description]"><?= htmlspecialchars($mod['description'] ?? '') ?></textarea></div>
                                   <div>
                                       <label>Imagem:</label>

                                       <input
                                           type="file"
                                           name="modalidades_arquivo[<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>]"
                                           accept="image/png, image/jpeg, image/webp">

                                       <input
                                           type="hidden"
                                           name="modalidades[<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>][image_url]"
                                           value="<?= htmlspecialchars($mod['image_url'] ?? '') ?>">

                                       <?php if (!empty($mod['image_url'])): ?>
                                           <div>
                                               <img
                                                   src="<?= htmlspecialchars($mod['image_url']) ?>"
                                                   alt="<?= htmlspecialchars($mod['name'] ?? 'Modalidade') ?>">
                                           </div>
                                       <?php endif; ?>
                                   </div>
                               </fieldset>
                           <?php endforeach; ?>
                       </div>
                   </div>

                   <!-- ================= CARROSSEL ================= -->
                   <div id="tab-carrossel" class="tab-content">
                       <div>
                           <div>
                               <h2>Carrossel de Imagens</h2>
                               <p>Adicione as imagens que serão exibidas no início do site.</p>
                           </div>

                           <button class="portfolio-admin-add" type="button" onclick="adicionarItem('slides')">
                               + Adicionar imagem
                           </button>
                       </div>

                       <div class="portfolio-admin-items" id="container-slides">
                           <?php foreach ($slides as $slide): ?>
                               <fieldset class="portfolio-admin-item"
                                   id="slide_<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>">

                                   <div>
                                       <button class="portfolio-admin-remove"
                                           type="button"
                                           onclick="removerItem('slide_<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>', 'slides', <?= (int)($slide['id'] ?? 0) ?>)">
                                           X Remover
                                       </button>
                                   </div>

                                   <input
                                       type="hidden"
                                       name="slides[<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>][id]"
                                       value="<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>">

                                   <div>
                                       <label>Imagem:</label>

                                       <input
                                           type="file"
                                           name="slides_arquivo[<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>]"
                                           accept="image/png, image/jpeg, image/webp">

                                       <input
                                           type="hidden"
                                           name="slides[<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>][image_url]"
                                           value="<?= htmlspecialchars($slide['image_url'] ?? '') ?>">

                                       <?php if (!empty($slide['image_url'])): ?>
                                           <div>
                                               <img
                                                   src="<?= htmlspecialchars($slide['image_url']) ?>"
                                                   alt="Imagem atual">
                                           </div>
                                       <?php endif; ?>
                                   </div>

                                   <div>
                                       <label>Ordem:</label>

                                       <input
                                           type="number"
                                           name="slides[<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>][ordem]"
                                           value="<?= (int)($slide['ordem'] ?? 0) ?>"
                                           min="0">
                                   </div>

                                   <div>
                                       <label>
                                           <input
                                               type="checkbox"
                                               name="slides[<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>][ativo]"
                                               value="1"
                                               <?= !empty($slide['ativo']) ? 'checked' : '' ?>>

                                           <span>Exibir no carrossel</span>
                                       </label>
                                   </div>
                               </fieldset>
                           <?php endforeach; ?>
                       </div>
                   </div>

                   <!-- ================= PLANOS ================= -->
                   <div id="tab-planos" class="tab-content">
                       <header>
                           <h2>Planos comerciais</h2>
                           <button class="portfolio-admin-add" type="button" onclick="adicionarItem('planos')">+ Adicionar</button>
                       </header>
                       <div class="portfolio-admin-items" id="container-planos">
                           <?php foreach ($planos as $plano): ?>
                               <fieldset class="portfolio-admin-item" id="plano_<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>">
                                   <div><button class="portfolio-admin-remove" type="button" onclick="removerItem('plano_<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>', 'planos', <?= (int)($plano['id'] ?? 0) ?>)">X Remover</button></div>
                                   <input type="hidden" name="planos[<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>][id]" value="<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>">
                                   <div><label>Nome:</label><input type="text" name="planos[<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>][nome]" value="<?= htmlspecialchars($plano['nome'] ?? '') ?>"></div>
                                   <div><label>Valor (R$):</label><input type="number" step="0.01" name="planos[<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>][valor]" value="<?= htmlspecialchars((string)($plano['valor'] ?? '')) ?>"></div>
                                   <div><label>Categoria:</label><input type="text" name="planos[<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>][categoria]" value="<?= htmlspecialchars($plano['categoria'] ?? '') ?>"></div>
                                   <div><label>Duração (Ex: 1 Mês):</label><input type="text" name="planos[<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>][duracao]" value="<?= htmlspecialchars($plano['duracao'] ?? '') ?>"></div>
                               </fieldset>
                           <?php endforeach; ?>
                       </div>
                   </div>

                   <!-- ================= UNIDADES ================= -->
                   <div id="tab-unidades" class="tab-content">
                       <header>
                           <h2>Unidades / Filiais</h2>
                           <button class="portfolio-admin-add" type="button" onclick="adicionarItem('filiais')">+ Adicionar</button>
                       </header>
                       <div class="portfolio-admin-items" id="container-filiais">
                           <?php foreach ($filiais as $filial): ?>
                               <fieldset class="portfolio-admin-item" id="filial_<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>">
                                   <div><button class="portfolio-admin-remove" type="button" onclick="removerItem('filial_<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>', 'filiais', <?= (int)($filial['id'] ?? 0) ?>)">X Remover</button></div>
                                   <input type="hidden" name="filiais[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>][id]" value="<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>">
                                   <div><label>Nome:</label><input type="text" name="filiais[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>][nome]" value="<?= htmlspecialchars($filial['nome'] ?? '') ?>"></div>
                                   <div><label>CNPJ:</label><input type="text" name="filiais[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>][cnpj]" value="<?= htmlspecialchars($filial['cnpj'] ?? '') ?>"></div>
                                   <div><label>Telefone:</label><input type="text" name="filiais[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>][telefone]" value="<?= htmlspecialchars($filial['telefone'] ?? '') ?>"></div>
                                   <div><label>Responsável:</label><input type="text" name="filiais[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>][responsavel]" value="<?= htmlspecialchars($filial['responsavel'] ?? '') ?>"></div>
                                   <div>
                                       <label>Latitude:</label>
                                       <input
                                           type="number"
                                           step="0.00000001"
                                           name="filiais[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>][latitude]"
                                           value="<?= htmlspecialchars((string)($filial['latitude'] ?? '')) ?>">
                                   </div>

                                   <div>
                                       <label>Longitude:</label>
                                       <input
                                           type="number"
                                           step="0.00000001"
                                           name="filiais[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>][longitude]"
                                           value="<?= htmlspecialchars((string)($filial['longitude'] ?? '')) ?>">
                                   </div>

                                   <div>
                                       <label>Imagem da unidade:</label>
                                       <input
                                           type="file"
                                           name="filiais_arquivo[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>]"
                                           accept="image/png, image/jpeg, image/webp">
                                       <input
                                           type="hidden"
                                           name="filiais[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>][image_url]"
                                           value="<?= htmlspecialchars($filial['image_url'] ?? '') ?>">
                                       <?php if (!empty($filial['image_url'])): ?>
                                           <div>
                                               <img
                                                   src="<?= htmlspecialchars($filial['image_url']) ?>"
                                                   alt="<?= htmlspecialchars($filial['nome'] ?? 'Unidade') ?>">
                                           </div>
                                       <?php endif; ?>
                                   </div>
                               </fieldset>
                           <?php endforeach; ?>
                       </div>
                   </div>
                   <!-- ================= FEEDBACKS ================= -->
                   <div id="tab-feedbacks" class="tab-content">
                       <header>
                           <h2>Avaliações dos Alunos</h2>
                           <p>Gerencie os feedbacks exibidos no site público.</p>
                       </header>
                       <div class="portfolio-admin-items" id="container-feedbacks">
                           <?php if (empty($feedbacks)): ?>
                               <p>Nenhuma avaliação recebida ainda.</p>
                           <?php else: ?>
                               <?php foreach ($feedbacks as $fb): ?>
                                   <fieldset class="portfolio-admin-item" id="feedback_<?= htmlspecialchars((string)($fb['id'] ?? '')) ?>">
                                       <div>
                                           <div>
                                               <strong><?= htmlspecialchars($fb['nome']) ?></strong>
                                               <div>
                                                   <?= str_repeat('★', (int)$fb['nota']) ?><?= str_repeat('☆', 5 - (int)$fb['nota']) ?>
                                               </div>
                                               <small><?= date('d/m/Y H:i', strtotime($fb['criado_em'])) ?></small>
                                           </div>
                                           <button class="portfolio-admin-remove" type="button" onclick="removerItem('feedback_<?= htmlspecialchars((string)($fb['id'] ?? '')) ?>', 'feedbacks', <?= (int)($fb['id'] ?? 0) ?>)">
                                               Excluir
                                           </button>
                                       </div>

                                       <p>
                                           "<?= nl2br(htmlspecialchars($fb['mensagem'])) ?>"
                                       </p>

                                       <input type="hidden" name="feedbacks[<?= htmlspecialchars((string)($fb['id'] ?? '')) ?>][id]" value="<?= htmlspecialchars((string)($fb['id'] ?? '')) ?>">

                                       <label>
                                           <div class="toggle-switch">
                                               <input
                                                   type="checkbox"
                                                   name="feedbacks[<?= htmlspecialchars((string)($fb['id'] ?? '')) ?>][ativo]"
                                                   value="1"
                                                   <?= !empty($fb['ativo']) ? 'checked' : '' ?>>
                                               <span class="toggle-slider"></span>
                                           </div>
                                           <span>Exibir no site público</span>
                                       </label>
                                   </fieldset>
                               <?php endforeach; ?>
                           <?php endif; ?>
                       </div>
                   </div>
               </section>

           </div>

           <!-- Recipiente para os IDs removidos -->
           <div id="removidos-container"></div>
       </form>
   </main>

   <script>
       // Alternância de abas sem mexer nos campos do formulário.
       const abasPortfolio = document.querySelectorAll('.portfolio-admin-page .tab-btn');
       const conteudosPortfolio = document.querySelectorAll('.portfolio-admin-page .tab-content');

       function ativarAbaPortfolio(botao) {
           conteudosPortfolio.forEach(painel => painel.classList.remove('is-active'));
           abasPortfolio.forEach(aba => {
               aba.classList.remove('is-active');
               aba.setAttribute('aria-selected', 'false');
           });
           const painel = document.getElementById(botao.dataset.target);
           if (!painel) return;
           botao.classList.add('is-active');
           botao.setAttribute('aria-selected', 'true');
           painel.classList.add('is-active');
       }

       abasPortfolio.forEach(botao => {
           botao.addEventListener('click', () => ativarAbaPortfolio(botao));
       });

       if (abasPortfolio.length) ativarAbaPortfolio(abasPortfolio[0]);

       // Se existir um campo obrigatório inválido em aba oculta,
       // abre a aba correspondente para o navegador mostrar o erro.
       document.getElementById('portfolioForm').addEventListener('invalid', function(event) {
           const painel = event.target.closest('.tab-content');
           if (painel && !painel.classList.contains('is-active')) {
               const aba = Array.from(abasPortfolio).find(b => b.dataset.target === painel.id);
               if (aba) ativarAbaPortfolio(aba);
           }
       }, true);

       // CRUD Dinâmico Front-end
       let counters = {
           planos: 0,
           filiais: 0,
           modalidades: 0,
           slides: 0
       };

       function adicionarItem(tipo) {
           counters[tipo]++;

           const newId = 'new_' + counters[tipo];
           const container = document.getElementById('container-' + tipo);

           let html = '';

           if (tipo === 'planos') {
               html = `<fieldset class="portfolio-admin-item" id="plano_${newId}">
                <div>
                    <button class="portfolio-admin-remove" type="button" onclick="removerItem('plano_${newId}', 'planos', null)">
                        X Cancelar
                    </button>
                </div>

                <div>
                    <label>Nome:</label>
                    <input type="text" name="planos[${newId}][nome]" required>
                </div>

                <div>
                    <label>Valor (R$):</label>
                    <input type="number" step="0.01" name="planos[${newId}][valor]" required>
                </div>

                <div>
                    <label>Categoria:</label>
                    <input type="text" name="planos[${newId}][categoria]" required>
                </div>

                <div>
                    <label>Duração (Ex: 1 Mês):</label>
                    <input type="text" name="planos[${newId}][duracao]" required>
                </div>
            </fieldset>`;

           } else if (tipo === 'filiais') {
               html = `<fieldset class="portfolio-admin-item" id="filial_${newId}">
                <div>
                    <button class="portfolio-admin-remove" type="button" onclick="removerItem('filial_${newId}', 'filiais', null)">
                        X Cancelar
                    </button>
                </div>

                <div>
                    <label>Nome:</label>
                    <input type="text" name="filiais[${newId}][nome]" required>
                </div>

                <div>
                    <label>CNPJ:</label>
                    <input type="text" name="filiais[${newId}][cnpj]" required>
                </div>

                <div>
                    <label>Telefone:</label>
                    <input type="text" name="filiais[${newId}][telefone]" required>
                </div>

                <div>
                    <label>Responsável:</label>
                    <input type="text" name="filiais[${newId}][responsavel]" required>
                </div>

                <div>
                    <label>Latitude:</label>
                    <input
                        type="number"
                        step="0.00000001"
                        name="filiais[${newId}][latitude]"
                    >
                </div>

                <div>
                    <label>Longitude:</label>
                    <input
                        type="number"
                        step="0.00000001"
                        name="filiais[${newId}][longitude]"
                    >
                </div>

                <div>
                    <label>Imagem da unidade:</label>
                    <input
                        type="file"
                        name="filiais_arquivo[${newId}]"
                        accept="image/png, image/jpeg, image/webp"
                    >
                    <input
                        type="hidden"
                        name="filiais[${newId}][image_url]"
                        value=""
                    >
                </div>
            </fieldset>`;

           } else if (tipo === 'modalidades') {
               html = `<fieldset class="portfolio-admin-item" id="mod_${newId}">
                <div>
                    <button class="portfolio-admin-remove" type="button" onclick="removerItem('mod_${newId}', 'modalidades', null)">
                        X Cancelar
                    </button>
                </div>

                <div>
                    <label>Nome:</label>
                    <input type="text" name="modalidades[${newId}][name]" required>
                </div>

                <div>
                    <label>Descrição:</label>
                    <textarea name="modalidades[${newId}][description]"></textarea>
                </div>

                <div>
                    <label>Imagem:</label>

                    <input
                        type="file"
                        name="modalidades_arquivo[${newId}]"
                        accept="image/png, image/jpeg, image/webp"
                    >

                    <input
                        type="hidden"
                        name="modalidades[${newId}][image_url]"
                        value=""
                    >
                </div>
            </fieldset>`;

           } else if (tipo === 'slides') {
               html = `<fieldset class="portfolio-admin-item" id="slide_${newId}">
                <div>
                    <button
                        type="button"
                        onclick="removerItem('slide_${newId}', 'slides', null)"
                    >
                        X Cancelar
                    </button>
                </div>

                <div>
                    <label>Imagem:</label>

                    <input
                        type="file"
                        name="slides_arquivo[${newId}]"
                        accept="image/png, image/jpeg, image/webp"
                        required
                    >

                    <input
                        type="hidden"
                        name="slides[${newId}][image_url]"
                        value=""
                    >
                </div>

                <div>
                    <label>Ordem:</label>

                    <input
                        type="number"
                        name="slides[${newId}][ordem]"
                        value="0"
                        min="0"
                    >
                </div>

                <div>
                    <label>
                        <input
                            type="checkbox"
                            name="slides[${newId}][ativo]"
                            value="1"
                            checked
                        >

                        <span>Exibir no carrossel</span>
                    </label>
                </div>
            </fieldset>`;
           }

           if (container && html) {
               container.insertAdjacentHTML('beforeend', html);
           }
       }

       function removerItem(elementId, tipo, dbId) {
           if (!confirm('Deseja remover este item? A exclusão será aplicada quando você salvar as alterações.')) return;

           document.getElementById(elementId).remove();

           if (dbId !== null) {
               // Se já existe no banco, adiciona no form para deletar
               const input = document.createElement('input');
               input.type = 'hidden';
               input.name = tipo + '_remover[]';
               input.value = dbId;
               document.getElementById('removidos-container').appendChild(input);
           }
       }
   </script>

   <?php include __DIR__ . '/../shared/footer.php'; ?>