<?php

if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/MinhaMarcaController.php");
    exit;
}

$preferencias = $preferencias ?? [
    'nome_painel' => 'Gymflow',
    'tema' => 'dark',
    'cor_primaria' => '#ffb000',
    'cor_secundaria' => '#000000',
    'tema_predefinido' => 'padrao',
    'logo_url' => null
];

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';

?>

<main>

    <h2>Minha Marca</h2>

    <p>
        Personalize a aparência do seu painel.
    </p>


    <?php if (($_GET['msg'] ?? '') === 'salvo'): ?>

        <p>
            Preferências salvas com sucesso.
        </p>

    <?php endif; ?>


    <form
        action="/Gymflow/app/controllers/MinhaMarcaController.php?acao=salvar"
        method="POST">


        <!-- NOME DO PAINEL -->

        <section>

            <h3>Nome do painel</h3>

            <label for="nome_painel">
                Nome exibido no sistema
            </label>

            <input
                type="text"
                name="nome_painel"
                id="nome_painel"
                maxlength="100"
                value="<?= htmlspecialchars($preferencias['nome_painel'] ?? 'Gymflow'); ?>">

        </section>


        <hr>


        <!-- TEMA CLARO / ESCURO -->

        <section>

            <h3>Aparência</h3>

            <label>
                <input
                    type="radio"
                    name="tema"
                    value="dark"
                    id="tema_dark"
                    <?= ($preferencias['tema'] ?? 'dark') === 'dark' ? 'checked' : ''; ?>>

                Tema escuro
            </label>


            <label>
                <input
                    type="radio"
                    name="tema"
                    value="light"
                    id="tema_light"
                    <?= ($preferencias['tema'] ?? 'dark') === 'light' ? 'checked' : ''; ?>>

                Tema claro
            </label>

        </section>


        <hr>


        <!-- TEMAS PREDEFINIDOS -->

        <section>

            <h3>Temas predefinidos</h3>

            <label for="tema_predefinido">
                Escolha um tema
            </label>

            <select
                name="tema_predefinido"
                id="tema_predefinido">

                <option
                    value="padrao"
                    <?= ($preferencias['tema_predefinido'] ?? '') === 'padrao' ? 'selected' : ''; ?>>
                    Gymflow Padrão
                </option>

                <option
                    value="dark"
                    <?= ($preferencias['tema_predefinido'] ?? '') === 'dark' ? 'selected' : ''; ?>>
                    Dark
                </option>

                <option
                    value="forest"
                    <?= ($preferencias['tema_predefinido'] ?? '') === 'forest' ? 'selected' : ''; ?>>
                    Forest
                </option>

                <option
                    value="sunset"
                    <?= ($preferencias['tema_predefinido'] ?? '') === 'sunset' ? 'selected' : ''; ?>>
                    Sunset
                </option>

                <option
                    value="ocean"
                    <?= ($preferencias['tema_predefinido'] ?? '') === 'ocean' ? 'selected' : ''; ?>>
                    Ocean
                </option>

            </select>

        </section>


        <hr>


        <!-- CORES PERSONALIZADAS -->

        <section>

            <h3>Paleta personalizada</h3>


            <div>

                <label for="cor_primaria">
                    Cor primária
                </label>

                <input
                    type="color"
                    name="cor_primaria"
                    id="cor_primaria"
                    value="<?= htmlspecialchars($preferencias['cor_primaria'] ?? '#ffb000'); ?>">

                <span id="valor_cor_primaria">
                    <?= htmlspecialchars($preferencias['cor_primaria'] ?? '#ffb000'); ?>
                </span>

            </div>


            <br>


            <div>

                <label for="cor_secundaria">
                    Cor secundária
                </label>

                <input
                    type="color"
                    name="cor_secundaria"
                    id="cor_secundaria"
                    value="<?= htmlspecialchars($preferencias['cor_secundaria'] ?? '#000000'); ?>">

                <span id="valor_cor_secundaria">
                    <?= htmlspecialchars($preferencias['cor_secundaria'] ?? '#000000'); ?>
                </span>

            </div>

        </section>


        <hr>


        <!-- LOGOTIPO -->

        <section>

            <h3>Logotipo</h3>

            <p>
                O upload e as opções de logotipo serão adicionados na próxima etapa.
            </p>

            <input
                type="hidden"
                name="logo_url"
                value="<?= htmlspecialchars($preferencias['logo_url'] ?? ''); ?>">

        </section>


        <hr>


        <!-- BOTÃO SALVAR -->

        <button type="submit">
            Salvar alterações
        </button>

    </form>

</main>


<script>
    // CAMPOS DA TELA

    const nomePainel =
        document.getElementById('nome_painel');

    const corPrimaria =
        document.getElementById('cor_primaria');

    const corSecundaria =
        document.getElementById('cor_secundaria');

    const temaPredefinido =
        document.getElementById('tema_predefinido');

    const temaDark =
        document.getElementById('tema_dark');

    const temaLight =
        document.getElementById('tema_light');

    const valorCorPrimaria =
        document.getElementById('valor_cor_primaria');

    const valorCorSecundaria =
        document.getElementById('valor_cor_secundaria');


    // ELEMENTOS DO SISTEMA

    const header =
        document.querySelector('header');

    const tituloPainel =
        document.querySelector('header h1');


    // TEMAS PRONTOS

    const temas = {

        padrao: {
            primaria: '#ffb000',
            secundaria: '#000000',
            modo: 'dark'
        },

        dark: {
            primaria: '#8b5cf6',
            secundaria: '#111827',
            modo: 'dark'
        },

        forest: {
            primaria: '#22c55e',
            secundaria: '#14532d',
            modo: 'dark'
        },

        sunset: {
            primaria: '#f97316',
            secundaria: '#7c2d12',
            modo: 'dark'
        },

        ocean: {
            primaria: '#0ea5e9',
            secundaria: '#0c4a6e',
            modo: 'dark'
        }

    };


    // ATUALIZA A APARÊNCIA DA PÁGINA

    function atualizarAparencia() {

        const primaria =
            corPrimaria.value;

        const secundaria =
            corSecundaria.value;

        const modo =
            temaLight.checked ? 'light' : 'dark';


        // MOSTRA CÓDIGO DA COR

        valorCorPrimaria.textContent =
            primaria;

        valorCorSecundaria.textContent =
            secundaria;


        // NOME DO PAINEL

        if (tituloPainel) {

            tituloPainel.textContent =
                nomePainel.value || 'Gymflow';

            tituloPainel.style.color =
                primaria;
        }


        // TEMA CLARO

        if (modo === 'light') {

            document.body.style.backgroundColor =
                '#ffffff';

            document.body.style.color =
                '#000000';

            if (header) {

                header.style.backgroundColor =
                    '#ffffff';

                header.style.color =
                    '#000000';
            }

        }

        // TEMA ESCURO
        else {

            document.body.style.backgroundColor =
                secundaria;

            document.body.style.color =
                '#ffffff';

            if (header) {

                header.style.backgroundColor =
                    secundaria;

                header.style.color =
                    '#ffffff';
            }
        }


        // LINKS

        document
            .querySelectorAll('a')
            .forEach(function(link) {

                link.style.color =
                    primaria;

            });


        // BOTÕES

        document
            .querySelectorAll('button')
            .forEach(function(botao) {

                botao.style.backgroundColor =
                    primaria;

            });

    }


    // SELECIONAR UM TEMA PREDEFINIDO

    temaPredefinido.addEventListener(
        'change',
        function() {

            const tema =
                temas[this.value];

            if (!tema) {
                return;
            }


            corPrimaria.value =
                tema.primaria;

            corSecundaria.value =
                tema.secundaria;


            if (tema.modo === 'light') {

                temaLight.checked =
                    true;

            } else {

                temaDark.checked =
                    true;
            }


            atualizarAparencia();

        }
    );


    // ALTERAÇÃO MANUAL DA COR PRIMÁRIA

    corPrimaria.addEventListener(
        'input',
        function() {

            /*
             * Ao alterar manualmente a cor,
             * continuamos permitindo que
             * o usuário personalize o tema.
             */

            atualizarAparencia();

        }
    );


    // ALTERAÇÃO MANUAL DA COR SECUNDÁRIA

    corSecundaria.addEventListener(
        'input',
        function() {

            atualizarAparencia();

        }
    );


    // TROCA CLARO / ESCURO

    temaDark.addEventListener(
        'change',
        atualizarAparencia
    );

    temaLight.addEventListener(
        'change',
        atualizarAparencia
    );


    // ALTERAÇÃO DO NOME EM TEMPO REAL

    nomePainel.addEventListener(
        'input',
        atualizarAparencia
    );


    // CARREGA A APARÊNCIA INICIAL

    atualizarAparencia();
</script>


<?php

include __DIR__ . '/../shared/footer.php';

?>