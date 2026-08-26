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

        'primary_color' => '#C9A227',
        'secondary_color' => '#000000',

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

// Slides do carrossel
$stmtSlides = $pdo->prepare("
    SELECT *
    FROM portfolio_slides
    WHERE company_id = ?
    AND ativo = 1
    ORDER BY ordem ASC, id ASC
");

$stmtSlides->execute([$company_id]);
$slides = $stmtSlides->fetchAll(PDO::FETCH_ASSOC);

$tituloPagina = $config['app_name'] ?? "GymCore";
include 'app/views/shared/portfolio_header.php';
?>

<main>
   <section id="inicio" class="portfolio-hero">

    <!-- IMAGENS DO CARROSSEL -->
    <div class="hero-carrossel">

        <?php if (!empty($slides)): ?>

            <?php foreach ($slides as $index => $slide): ?>

                <div class="hero-slide <?= $index === 0 ? 'ativo' : '' ?>">

                    <img
                        src="<?= htmlspecialchars($slide['image_url']) ?>"
                        alt="Imagem do portfólio"
                    >

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <!-- caso o Admin ainda não tenha cadastrado imagens -->
            <div class="hero-slide ativo hero-slide-vazio"></div>

        <?php endif; ?>

    </div>


    <!-- escurecimento da imagem carrossel -->
    <div class="hero-overlay"></div>


    <!-- conteudo -->
    <div class="hero-conteudo">

        <h1>
            <?= htmlspecialchars($config['hero_title']) ?>
        </h1>

        <p class="hero-subtitulo">
            <?= htmlspecialchars($config['hero_subtitle']) ?>
        </p>

        <div class="hero-acoes">

            <a class="hero-btn hero-btn-principal" href="#planos">
                <?= htmlspecialchars($config['hero_cta']) ?>
            </a>

            <a class="hero-btn hero-btn-secundario" href="#unidades">
                Encontrar unidade
            </a>

        </div>

    </div>


    <?php if (count($slides) > 1): ?>

        <!-- setas -->
        <button
            type="button"
            class="hero-seta hero-seta-anterior"
            aria-label="Imagem anterior"
        >
            &#10094;
        </button>

        <button
            type="button"
            class="hero-seta hero-seta-proxima"
            aria-label="Próxima imagem"
        >
            &#10095;
        </button>


        <!-- bolinhas -->
        <div class="hero-indicadores">

            <?php foreach ($slides as $index => $slide): ?>

                <button
                    type="button"
                    class="hero-indicador <?= $index === 0 ? 'ativo' : '' ?>"
                    data-slide="<?= $index ?>"
                    aria-label="Ir para imagem <?= $index + 1 ?>"
                ></button>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>

   <!-- modalidades -->

<section id="modalidades" class="portfolio-modalidades">

    <div class="modalidades-cabecalho">

        <span class="secao-destaque">
            Nossas Modalidades
        </span>

        <h2>
            Tudo o que você precisa em um só lugar
        </h2>

        <p>
            Encontre a modalidade ideal para você
        </p>

    </div>


    <?php if (empty($modalidades)): ?>

        <p class="mensagem-vazia">
            Nenhuma modalidade cadastrada no momento.
        </p>

    <?php else: ?>

        <div class="modalidades-grid">

            <?php foreach ($modalidades as $mod): ?>

                <article class="modalidade-card">

                    <?php if (!empty($mod['image_url'])): ?>

                        <div class="modalidade-imagem">

                            <img
                                src="<?= htmlspecialchars($mod['image_url']) ?>"
                                alt="<?= htmlspecialchars($mod['name']) ?>"
                            >

                        </div>

                    <?php endif; ?>


                    <div class="modalidade-conteudo">

                        <h3>
                            <?= htmlspecialchars($mod['name']) ?>
                        </h3>

                        <p>
                            <?= htmlspecialchars($mod['description']) ?>
                        </p>
                         <a href="#" class="modalidade-saiba-mais">
                                                      Saiba mais →
                                                              </a>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

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

    <!-- sobre Nos -->
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
<script>
document.addEventListener('DOMContentLoaded', function () {

    const slides = document.querySelectorAll('.hero-slide');
    const indicadores = document.querySelectorAll('.hero-indicador');
    const btnAnterior = document.querySelector('.hero-seta-anterior');
    const btnProxima = document.querySelector('.hero-seta-proxima');

   if (slides.length <= 1) {
    return;
}

let slideAtual = 0;
let intervalo;

function mostrarSlide(index) {
    slides.forEach(slide => slide.classList.remove('ativo'));
    indicadores.forEach(indicador => indicador.classList.remove('ativo'));

    if (index >= slides.length) {
        slideAtual = 0;
    } else if (index < 0) {
        slideAtual = slides.length - 1;
    } else {
        slideAtual = index;
    }

    slides[slideAtual].classList.add('ativo');

    if (indicadores[slideAtual]) {
        indicadores[slideAtual].classList.add('ativo');
    }
}

function iniciarAutomatico() {
    intervalo = setInterval(function () {
        mostrarSlide(slideAtual + 1);
    }, 5000);
}

if (btnProxima) {
    btnProxima.addEventListener('click', function () {
        mostrarSlide(slideAtual + 1);
    });
}

if (btnAnterior) {
    btnAnterior.addEventListener('click', function () {
        mostrarSlide(slideAtual - 1);
    });
}

indicadores.forEach((indicador, index) => {
    indicador.addEventListener('click', function () {
        mostrarSlide(index);
    });
});

iniciarAutomatico();

});

</script>

<?php include 'app/views/shared/footer.php'; ?>