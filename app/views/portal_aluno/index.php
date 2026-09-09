<?php

if (!isset($aluno)) {

    header(
        "Location: /Gymflow/app/controllers/PortalAlunoController.php?acao=aluno"
    );

    exit;
}

$matricula = $matricula ?? false;
$frequencia = $frequencia ?? 0;
$faturas_abertas = $faturas_abertas ?? 0;

$tituloPagina = "Portal do Aluno";

include __DIR__ . '/../shared/navbar.php';

?>


<main class="portal-conteudo">


    <section class="portal-boas-vindas">

        <h1>
            Olá,
            <?= htmlspecialchars($aluno['nome'] ?? 'Aluno'); ?>!
        </h1>

        <p>
            Bem-vindo ao seu portal.
        </p>

    </section>


    <section class="portal-cards">


        <article class="portal-card">

            <h2>Meu Plano</h2>


            <?php if (!empty($matricula)): ?>

                <p class="portal-destaque">
                    <?= htmlspecialchars($matricula['nome_plano']); ?>
                </p>

                <p>
                    <strong>Status:</strong>
                    Ativo
                </p>

                <p>
                    <strong>Validade:</strong>

                    <?= date(
                        'd/m/Y',
                        strtotime($matricula['fim'])
                    ); ?>
                </p>


            <?php else: ?>

                <p>
                    Você não possui um plano ativo no momento.
                </p>

            <?php endif; ?>

        </article>


        <article class="portal-card">

            <h2>Minha Frequência</h2>

            <p class="portal-destaque">
                <?= (int) $frequencia; ?>
            </p>

            <p>
                dia<?= $frequencia != 1 ? 's' : ''; ?>
                treinado<?= $frequencia != 1 ? 's' : ''; ?>
                neste mês.
            </p>

        </article>


        <article class="portal-card">

            <h2>Minhas Faturas</h2>

            <p class="portal-destaque">
                <?= (int) $faturas_abertas; ?>
            </p>


            <?php if ($faturas_abertas > 0): ?>

                <p>
                    Fatura<?= $faturas_abertas > 1 ? 's' : ''; ?>
                    em aberto.
                </p>

            <?php else: ?>

                <p>
                    Nenhuma fatura em aberto.
                </p>

            <?php endif; ?>


            <a
                class="portal-link"
                href="/Gymflow/app/controllers/PortalAlunoController.php?acao=faturas">
                Ver minhas faturas
            </a>

        </article>


        <article class="portal-card">

            <h2>Meus Treinos</h2>

            <p>
                Consulte sua ficha de treino e os exercícios
                cadastrados pelo professor.
            </p>

            <a
                class="portal-link"
                href="/Gymflow/app/controllers/PortalAlunoController.php?acao=treinos">
                Ver meus treinos
            </a>

        </article>


    </section>


</main>


<?php

include __DIR__ . '/../shared/footer.php';

?>