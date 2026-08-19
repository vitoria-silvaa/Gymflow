<?php
require_once __DIR__ . '/config/conexao.php';

// Company_id = 1 temporário
$company_id = 1;

// Configurações do Portfólio
$stmt = $pdo->prepare("SELECT * FROM portfolio_config WHERE company_id = ?");
$stmt->execute([$company_id]);
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$config) {
    $config = [
        'app_name' => 'GymCore',
        'hero_title' => 'Transforme seu corpo.',
        'hero_subtitle' => '...',
        'hero_cta' => 'Matricule-se',
        'about_text' => '...',
        'about_image' => '',
        'company_values' => '',
        'company_competencies' => ''
    ];
}

// Planos
$stmtPlanos = $pdo->prepare("SELECT * FROM planos WHERE company_id = ?");
$stmtPlanos->execute([$company_id]);
$planos = $stmtPlanos->fetchAll(PDO::FETCH_ASSOC);

// Filiais
$stmtFiliais = $pdo->prepare("SELECT * FROM filiais WHERE company_id = ? AND ativo = 1");
$stmtFiliais->execute([$company_id]);
$filiais = $stmtFiliais->fetchAll(PDO::FETCH_ASSOC);

// Modalidades
$stmtModais = $pdo->prepare("SELECT * FROM portfolio_modalities");
$stmtModais->execute();
$modalidades = $stmtModais->fetchAll(PDO::FETCH_ASSOC);

$tituloPagina = $config['app_name'] ?? "GymCore";
include 'app/views/shared/portfolio_header.php';
?>

<main>
    <section id="inicio">
        <!--hero-->
        <h1><?= htmlspecialchars($config['hero_title']) ?></h1>
        <p><?= htmlspecialchars($config['hero_subtitle']) ?></p>

        <a href="#planos">
            <?= htmlspecialchars($config['hero_cta']) ?>
        </a>
        <a href="#unidades">
           Encontrar unidade
        </a>
    </section>

    <!-- Modalidades -->
    <hr>
    <section id="modalidades">
        <h3>Nossas Modalidades</h3>
        <h1>Tudo o que você precisa em um só lugar</h1>
        
        <?php if(empty($modalidades)): ?>
            <p>Nenhuma modalidade cadastrada no momento.</p>
        <?php else: ?>
            <?php foreach($modalidades as $mod): ?>
            <article>
                <h3><?= htmlspecialchars($mod['name']) ?></h3>
                <?php if(!empty($mod['image_url'])): ?>
                    <img src="<?= htmlspecialchars($mod['image_url']) ?>" alt="<?= htmlspecialchars($mod['name']) ?>" width="300">
                <?php endif; ?>
                <p><?= htmlspecialchars($mod['description']) ?></p>
            </article>
            <br>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <!--planos-->
    <hr>
    <section id="planos">
        <h3>Nossos Planos</h3>
        <h1>Escolha o plano ideal para sua evolução</h1>
        <h2>Sem fidelidade obrigatória. Cancele quando quiser.</h2>

        <?php if(empty($planos)): ?>
            <p>Nenhum plano disponível no momento.</p>
        <?php else: ?>
            <?php foreach($planos as $plano): ?>
            <article>
                <h3><?= htmlspecialchars($plano['nome']) ?></h3>
                <h4>R$ <?= number_format($plano['valor'], 2, ',', '.') ?> / <?= htmlspecialchars($plano['duracao']) ?></h4>
                <p>Categoria: <?= htmlspecialchars($plano['categoria']) ?></p>
                <a href="app/views/portfolio/checkout.php?plano_id=<?= $plano['id'] ?>">Matricule-se agora</a>
            </article>
            <br>
            <?php endforeach; ?>
        <?php endif; ?>
    </section> 

  <!--unidades-->
    <hr>
    <section id="unidades">
        <h3>Nossas Unidades</h3>
        <h1>Encontre a unidade mais próxima</h1>

        <?php if(empty($filiais)): ?>
            <p>Nenhuma unidade cadastrada no momento.</p>
        <?php else: ?>
            <?php foreach($filiais as $filial): ?>
            <article>
                <h3><?= htmlspecialchars($filial['nome']) ?></h3>
                <p>Telefone: <?= htmlspecialchars($filial['telefone']) ?></p>
                <p>Responsável: <?= htmlspecialchars($filial['responsavel']) ?></p>
                <a href="#">Saiba mais</a>
            </article>
            <br>
            <?php endforeach; ?>
        <?php endif; ?>

        <br><br>

        <section>
            <h3>Mapa das Unidades</h3>
            <p>
                Nesta área será exibido o mapa com a localização das unidades
                disponíveis.
            </p>
        </section>

    </section>

    <!-- Sobre Nos -->
    <hr>
    <section id="sobre">
        <h3>Sobre Nós</h3>
        <p><?= htmlspecialchars($config['about_text']) ?></p>
        <?php if(!empty($config['about_image'])): ?>
            <img src="<?= htmlspecialchars($config['about_image']) ?>" alt="Sobre nós" width="400">
        <?php endif; ?>

        <h4>Nossos Valores</h4>
        <p><?= htmlspecialchars($config['company_values']) ?></p>

        <h4>Nossas Competências</h4>
        <p><?= htmlspecialchars($config['company_competencies']) ?></p>
    </section>

</main>

<?php include 'app/views/shared/footer.php'; ?>