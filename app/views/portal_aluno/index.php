<?php

if (!isset($aluno)) {
    header("Location: /Gymflow/app/controllers/PortalAlunoController.php?acao=aluno");
    exit;
}

$matricula = $matricula ?? false;
$frequencia = $frequencia ?? 0;
$faturas_abertas = $faturas_abertas ?? 0;

$tituloPagina = "Portal do Aluno";

include __DIR__ . '/../shared/navbar.php';

?>

<main>

    <section>

        <h1>
            Olá, <?= htmlspecialchars($aluno['nome'] ?? 'Aluno'); ?>!
        </h1>

        <p>
            Bem-vindo ao seu portal.
        </p>

    </section>


    <hr>


    <section>

        <h2>Meu Plano</h2>

        <?php if (!empty($matricula)): ?>

            <p>
                <strong>Plano:</strong>
                <?= htmlspecialchars($matricula['nome_plano']); ?>
            </p>

            <p>
                <strong>Status:</strong>
                Ativo
            </p>

            <p>
                <strong>Validade:</strong>
                <?= date('d/m/Y', strtotime($matricula['fim'])); ?>
            </p>

        <?php else: ?>

            <p>
                Você não possui um plano ativo no momento.
            </p>

        <?php endif; ?>

    </section>


    <hr>


    <section>

        <h2>Minha Frequência</h2>

        <p>
            Você treinou
            <strong><?= (int) $frequencia; ?></strong>
            vez<?= $frequencia != 1 ? 'es' : ''; ?>
            neste mês.
        </p>

    </section>


    <hr>


    <section>

        <h2>Minhas Faturas</h2>

        <?php if ($faturas_abertas > 0): ?>

            <p>
                Você possui
                <strong><?= (int) $faturas_abertas; ?></strong>
                fatura<?= $faturas_abertas > 1 ? 's' : ''; ?>
                em aberto.
            </p>

        <?php else: ?>

            <p>
                Nenhuma fatura em aberto.
            </p>

        <?php endif; ?>

        <a href="/Gymflow/app/controllers/PortalAlunoController.php?acao=faturas">
            Ver minhas faturas
        </a>

    </section>


    <hr>


    <section>

        <h2>Meus Treinos</h2>

        <p>
            Consulte sua ficha de treino e os exercícios cadastrados pelo professor.
        </p>

        <a href="/Gymflow/app/controllers/PortalAlunoController.php?acao=treinos">
            Ver meus treinos
        </a>

    </section>

</main>


<?php

include __DIR__ . '/../shared/footer.php';

?>