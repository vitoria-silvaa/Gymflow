<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/PortfolioController.php");
    exit;
}

$config = $config ?? [];
$slides = $slides ?? [];
$modalidades = $modalidades ?? [];
$planos = $planos ?? [];
$filiais = $filiais ?? [];

$tituloPagina = "Site / Portfólio";
include __DIR__ . '/../shared/header.php';
?>

<?php include __DIR__ . '/../shared/sidebar.php'; ?>

<main>
    <form method="POST" action="/Gymflow/app/controllers/PortfolioController.php?acao=salvar" id="portfolioForm">
        <header>
            <div>
                <h1>Site / Portfólio Público</h1>
                <p>Edições refletem no preview em tempo real</p>
                <?php if (isset($_GET['status']) && $_GET['status'] == 'sucesso'): ?>
                    <p style="color: green; font-weight: bold;">Configurações salvas com sucesso!</p>
                <?php endif; ?>
            </div>
            <div>
                <button type="submit" style="background-color: #10b981; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px; font-weight: bold;">Salvar Alterações</button>
            </div>
        </header>

        <hr>

        <div style="display: flex; gap: 20px;">
            <section style="flex: 1;">
                <nav>
                    <ul style="display: flex; gap: 10px; list-style: none; padding: 0;">
                        <li><button type="button" class="tab-btn" data-target="tab-design" style="padding: 10px;">Design</button></li>
                        <li><button type="button" class="tab-btn" data-target="tab-conteudo" style="padding: 10px;">Conteúdo</button></li>
                        <li><button type="button" class="tab-btn" data-target="tab-carrossel" style="padding: 10px;">Carrossel</button></li>
                        <li><button type="button" class="tab-btn" data-target="tab-planos" style="padding: 10px;">Planos</button></li>
                        <li><button type="button" class="tab-btn" data-target="tab-unidades" style="padding: 10px;">Unidades</button></li>
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
                        <label>URL do Logótipo</label>
                        <input type="url" name="urlLogotipo" value="<?= htmlspecialchars($config['logo_url'] ?? '') ?>" style="width: 100%;">
                    </div>
                </div>

                <!-- ================= CONTEÚDO ================= -->
                <div id="tab-conteudo" class="tab-content" style="display: none;">
                    <h2>Conteúdo Base</h2>
                    <div>
                        <label>Título do Hero</label>
                        <input type="text" id="tituloHero" name="tituloHero" value="<?= htmlspecialchars($config['hero_title'] ?? '') ?>" style="width: 100%;">
                    </div>
                    <div>
                        <label>Subtítulo do Hero</label>
                        <textarea id="subtituloHero" name="subtituloHero" style="width: 100%; height: 60px;"><?= htmlspecialchars($config['hero_subtitle'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label>Texto do botão (CTA)</label>
                        <input type="text" id="textoBotao" name="textoBotao" value="<?= htmlspecialchars($config['hero_cta'] ?? '') ?>" style="width: 100%;">
                    </div>
                    <div>
                        <label>Sobre Nós</label>
                        <textarea name="sobreNos" style="width: 100%; height: 60px;"><?= htmlspecialchars($config['about_text'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label>URL da imagem (Sobre)</label>
                        <input type="url" name="urlImagemSobre" value="<?= htmlspecialchars($config['about_image'] ?? '') ?>" style="width: 100%;">
                    </div>
                    <div>
                        <label>Nossos Valores</label>
                        <textarea name="nossosValores" style="width: 100%; height: 60px;"><?= htmlspecialchars($config['company_values'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label>Nossas Competências</label>
                        <textarea name="nossasCompetencias" style="width: 100%; height: 60px;"><?= htmlspecialchars($config['company_competencies'] ?? '') ?></textarea>
                    </div>

                    <hr>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h3>Modalidades Oferecidas</h3>
                        <button type="button" onclick="adicionarItem('modalidades')">+ Adicionar</button>
                    </div>
                    <div id="container-modalidades">
                        <?php foreach ($modalidades as $mod): ?>
                            <fieldset style="margin-bottom: 15px; border: 1px solid #ddd; padding: 10px;" id="mod_<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>">
                                <div style="text-align: right;"><button type="button" style="color:red;" onclick="removerItem('mod_<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>', 'modalidades', <?= (int)($mod['id'] ?? 0) ?>)">X Remover</button></div>
                                <input type="hidden" name="modalidades[<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>][id]" value="<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>">
                                <div><label>Nome:</label><input type="text" name="modalidades[<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>][name]" value="<?= htmlspecialchars($mod['name'] ?? '') ?>" style="width: 100%;"></div>
                                <div><label>Descrição:</label><textarea name="modalidades[<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>][description]" style="width: 100%;"><?= htmlspecialchars($mod['description'] ?? '') ?></textarea></div>
                                <div><label>Imagem URL:</label><input type="url" name="modalidades[<?= htmlspecialchars((string)($mod['id'] ?? '')) ?>][image_url]" value="<?= htmlspecialchars($mod['image_url'] ?? '') ?>" style="width: 100%;"></div>
                            </fieldset>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ================= CARROSSEL ================= -->
                <div id="tab-carrossel" class="tab-content" style="display: none;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <h2>Carrossel de Imagens</h2>
                            <p>Adicione as imagens que serão exibidas no início do site.</p>
                        </div>

                        <button type="button" onclick="adicionarItem('slides')">
                            + Adicionar imagem
                        </button>
                    </div>

                    <div id="container-slides">
                        <?php foreach ($slides as $slide): ?>
                            <fieldset
                                style="margin-bottom: 15px; border: 1px solid #ddd; padding: 10px;"
                                id="slide_<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>">

                                <div style="text-align: right;">
                                    <button
                                        type="button"
                                        style="color:red;"
                                        onclick="removerItem('slide_<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>', 'slides', <?= (int)($slide['id'] ?? 0) ?>)">
                                        X Remover
                                    </button>
                                </div>

                                <input
                                    type="hidden"
                                    name="slides[<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>][id]"
                                    value="<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>">

                                <div>
                                    <label>URL da imagem:</label>

                                    <input
                                        type="url"
                                        name="slides[<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>][image_url]"
                                        value="<?= htmlspecialchars($slide['image_url'] ?? '') ?>"
                                        style="width: 100%;"
                                        required>
                                </div>

                                <div>
                                    <label>Ordem:</label>

                                    <input
                                        type="number"
                                        name="slides[<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>][ordem]"
                                        value="<?= (int)($slide['ordem'] ?? 0) ?>"
                                        min="0">
                                </div>

                                <div style="margin-top: 15px;">
                                    <label style="display: flex; align-items: center; gap: 8px; width: fit-content; cursor: pointer;">
                                        <input
                                            type="checkbox"
                                            name="slides[<?= htmlspecialchars((string)($slide['id'] ?? '')) ?>][ativo]"
                                            value="1"
                                            <?= !empty($slide['ativo']) ? 'checked' : '' ?>
                                            style="width: auto; margin: 0;">

                                        <span>Exibir no carrossel</span>
                                    </label>
                                </div>
                            </fieldset>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ================= PLANOS ================= -->
                <div id="tab-planos" class="tab-content" style="display: none;">
                    <header style="display:flex; justify-content:space-between; align-items:center;">
                        <h2>Planos comerciais</h2>
                        <button type="button" onclick="adicionarItem('planos')">+ Adicionar</button>
                    </header>
                    <div id="container-planos">
                        <?php foreach ($planos as $plano): ?>
                            <fieldset style="margin-bottom: 15px; border: 1px solid #ddd; padding: 10px;" id="plano_<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>">
                                <div style="text-align: right;"><button type="button" style="color:red;" onclick="removerItem('plano_<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>', 'planos', <?= (int)($plano['id'] ?? 0) ?>)">X Remover</button></div>
                                <input type="hidden" name="planos[<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>][id]" value="<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>">
                                <div><label>Nome:</label><input type="text" name="planos[<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>][nome]" value="<?= htmlspecialchars($plano['nome'] ?? '') ?>" style="font-weight:bold; width: 100%;"></div>
                                <div><label>Valor (R$):</label><input type="number" step="0.01" name="planos[<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>][valor]" value="<?= htmlspecialchars((string)($plano['valor'] ?? '')) ?>" style="width: 100%;"></div>
                                <div><label>Categoria:</label><input type="text" name="planos[<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>][categoria]" value="<?= htmlspecialchars($plano['categoria'] ?? '') ?>" style="width: 100%;"></div>
                                <div><label>Duração (Ex: 1 Mês):</label><input type="text" name="planos[<?= htmlspecialchars((string)($plano['id'] ?? '')) ?>][duracao]" value="<?= htmlspecialchars($plano['duracao'] ?? '') ?>" style="width: 100%;"></div>
                            </fieldset>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ================= UNIDADES ================= -->
                <div id="tab-unidades" class="tab-content" style="display: none;">
                    <header style="display:flex; justify-content:space-between; align-items:center;">
                        <h2>Unidades / Filiais</h2>
                        <button type="button" onclick="adicionarItem('filiais')">+ Adicionar</button>
                    </header>
                    <div id="container-filiais">
                        <?php foreach ($filiais as $filial): ?>
                            <fieldset style="margin-bottom: 15px; border: 1px solid #ddd; padding: 10px;" id="filial_<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>">
                                <div style="text-align: right;"><button type="button" style="color:red;" onclick="removerItem('filial_<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>', 'filiais', <?= (int)($filial['id'] ?? 0) ?>)">X Remover</button></div>
                                <input type="hidden" name="filiais[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>][id]" value="<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>">
                                <div><label>Nome:</label><input type="text" name="filiais[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>][nome]" value="<?= htmlspecialchars($filial['nome'] ?? '') ?>" style="font-weight:bold; width: 100%;"></div>
                                <div><label>CNPJ:</label><input type="text" name="filiais[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>][cnpj]" value="<?= htmlspecialchars($filial['cnpj'] ?? '') ?>" style="width: 100%;"></div>
                                <div><label>Telefone:</label><input type="text" name="filiais[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>][telefone]" value="<?= htmlspecialchars($filial['telefone'] ?? '') ?>" style="width: 100%;"></div>
                                <div><label>Responsável:</label><input type="text" name="filiais[<?= htmlspecialchars((string)($filial['id'] ?? '')) ?>][responsavel]" value="<?= htmlspecialchars($filial['responsavel'] ?? '') ?>" style="width: 100%;"></div>
                            </fieldset>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- ================= PREVIEW ================= -->
            <aside style="flex: 1; border: 1px solid #ccc; padding: 20px; background: #f9f9f9;">
                <h2>PREVIEW AO VIVO</h2>
                <article>
                    <header style="border-bottom: 2px solid <?= htmlspecialchars($config['primary_color'] ?? '#C9A227') ?>; padding-bottom: 10px;">
                        <div><strong><?= htmlspecialchars($config['app_name'] ?? 'GymCore') ?></strong></div>
                    </header>
                    <section style="margin-top: 20px;">
                        <h1 id="preview-hero-title" style="color: <?= htmlspecialchars($config['secondary_color'] ?? '#000000') ?>;"><?= htmlspecialchars($config['hero_title'] ?? '') ?></h1>
                        <p id="preview-hero-subtitle"><?= htmlspecialchars($config['hero_subtitle'] ?? '') ?></p>
                        <div style="margin-top: 15px;">
                            <button type="button" id="preview-btn-cta" style="background-color: <?= htmlspecialchars($config['primary_color'] ?? '#C9A227') ?>; color: white; padding: 10px 15px; border: none; font-weight: bold;"><?= htmlspecialchars($config['hero_cta'] ?? 'Matricule-se') ?></button>
                        </div>
                    </section>
                </article>
            </aside>
        </div>

        <!-- Recipiente para os IDs removidos -->
        <div id="removidos-container"></div>
    </form>
</main>

<script>
    // JS Básico para funcionamento das abas
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.style.fontWeight = 'normal';
                b.style.textDecoration = 'none';
            });

            document.getElementById(button.getAttribute('data-target')).style.display = 'block';
            button.style.fontWeight = 'bold';
            button.style.textDecoration = 'underline';
        });
    });
    document.querySelector('.tab-btn').click();

    // Preview ao vivo
    ['tituloHero', 'subtituloHero', 'textoBotao', 'corPrimaria', 'corSecundaria'].forEach(id => {
        let el = document.getElementById(id);
        if (el) el.addEventListener('input', (e) => {
            if (id === 'tituloHero') {
                const target = document.getElementById('preview-hero-title');
                if (target) target.textContent = e.target.value;
            }
            if (id === 'subtituloHero') {
                const target = document.getElementById('preview-hero-subtitle');
                if (target) target.textContent = e.target.value;
            }
            if (id === 'textoBotao') {
                const target = document.getElementById('preview-btn-cta');
                if (target) target.textContent = e.target.value;
            }
            if (id === 'corPrimaria') {
                const target = document.getElementById('preview-btn-cta');
                if (target) target.style.backgroundColor = e.target.value;
            }
            if (id === 'corSecundaria') {
                const target = document.getElementById('preview-hero-title');
                if (target) target.style.color = e.target.value;
            }
        });
    });

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
            html = `<fieldset style="margin-bottom: 15px; border: 1px solid #4CAF50; padding: 10px;" id="plano_${newId}">
                <div style="text-align: right;"><button type="button" style="color:red;" onclick="removerItem('plano_${newId}', 'planos', null)">X Cancelar</button></div>
                <div><label>Nome:</label><input type="text" name="planos[${newId}][nome]" required style="width: 100%;"></div>
                <div><label>Valor (R$):</label><input type="number" step="0.01" name="planos[${newId}][valor]" required style="width: 100%;"></div>
                <div><label>Categoria:</label><input type="text" name="planos[${newId}][categoria]" required style="width: 100%;"></div>
                <div><label>Duração (Ex: 1 Mês):</label><input type="text" name="planos[${newId}][duracao]" required style="width: 100%;"></div>
            </fieldset>`;
        } else if (tipo === 'filiais') {
            html = `<fieldset style="margin-bottom: 15px; border: 1px solid #4CAF50; padding: 10px;" id="filial_${newId}">
                <div style="text-align: right;"><button type="button" style="color:red;" onclick="removerItem('filial_${newId}', 'filiais', null)">X Cancelar</button></div>
                <div><label>Nome:</label><input type="text" name="filiais[${newId}][nome]" required style="width: 100%;"></div>
                <div><label>CNPJ:</label><input type="text" name="filiais[${newId}][cnpj]" required style="width: 100%;"></div>
                <div><label>Telefone:</label><input type="text" name="filiais[${newId}][telefone]" required style="width: 100%;"></div>
                <div><label>Responsável:</label><input type="text" name="filiais[${newId}][responsavel]" required style="width: 100%;"></div>
            </fieldset>`;
        } else if (tipo === 'modalidades') {
            html = `<fieldset style="margin-bottom: 15px; border: 1px solid #4CAF50; padding: 10px;" id="mod_${newId}">
                <div style="text-align: right;"><button type="button" style="color:red;" onclick="removerItem('mod_${newId}', 'modalidades', null)">X Cancelar</button></div>
                <div><label>Nome:</label><input type="text" name="modalidades[${newId}][name]" required style="width: 100%;"></div>
                <div><label>Descrição:</label><textarea name="modalidades[${newId}][description]" style="width: 100%;"></textarea></div>
                <div><label>Imagem URL:</label><input type="url" name="modalidades[${newId}][image_url]" style="width: 100%;"></div>
            </fieldset>`;
        } else if (tipo === 'slides') {
            html = `<fieldset style="margin-bottom: 15px; border: 1px solid #C9A227; padding: 10px;" id="slide_${newId}">
                <div style="text-align: right;">
                    <button type="button" style="color:red;" onclick="removerItem('slide_${newId}', 'slides', null)">X Cancelar</button>
                </div>

                <div>
                    <label>URL da imagem:</label>
                    <input type="url" name="slides[${newId}][image_url]" required style="width: 100%;">
                </div>

                <div>
                    <label>Ordem:</label>
                    <input type="number" name="slides[${newId}][ordem]" value="0" min="0">
                </div>

                <div style="margin-top: 15px;">
                    <label style="display: flex; align-items: center; gap: 8px; width: fit-content; cursor: pointer;">
                        <input
                            type="checkbox"
                            name="slides[${newId}][ativo]"
                            value="1"
                            checked
                            style="width: auto; margin: 0;">
                        <span>Exibir no carrossel</span>
                    </label>
                </div>
            </fieldset>`;
        }

        container.insertAdjacentHTML('beforeend', html);
    }

    function removerItem(elementId, tipo, dbId) {
        if (!confirm('Deseja realmente remover este item da tela? Ao salvar, ele será deletado do banco.')) return;

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