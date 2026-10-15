<?php
// Título da página
$tituloPagina = "Fichas de Treino";

// CSS específico do módulo
$cssEspecifico = BASE_URL . '/assets/css/fichatreino.css';

// Componentes compartilhados
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main class="conteudo fichatreino-page">

    <div class="fichatreino-header">

        <div class="fichatreino-header-info">

            <span class="fichatreino-etiqueta">
                GESTÃO DE TREINOS
            </span>

            <h1>Fichas de Treino</h1>

            <p>
                Monte, organize e gerencie
                os treinos dos alunos.
            </p>

        </div>

    </div>

    <!--
        O formulário real de criação e edição
        das fichas será mantido aqui quando
        analisarmos sua view completa.
    -->

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>