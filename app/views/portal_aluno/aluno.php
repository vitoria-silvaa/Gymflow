<?php
if (!isset($aluno)) {
    header("Location: /Gymflow/app/controllers/PortalAlunoController.php?acao=aluno");
    exit;
}
/** @var array $aluno */
/** @var array|false $matricula */
/** @var int $frequencia */
/** @var int $faturas_abertas */

$tituloPagina = "Portal do Aluno";
?>

<?php include __DIR__ . '/../shared/navbar.php'; ?>

<main>

    <header>
        <h1>Olá, <?php echo htmlspecialchars($aluno['nome'] ?? 'Aluno'); ?>!</h1>
        <p>Pronto para o treino de hoje?</p>
    </header>

    <section>
        <h2>Meu Plano</h2>

        <?php if ($matricula): ?>
            <h3><?php echo htmlspecialchars($matricula['nome_plano']); ?></h3>
            <p>Plano ativo</p>
            <p>Expira em: <?php echo date('d/m/Y', strtotime($matricula['fim'])); ?></p>
        <?php else: ?>
            <p>Nenhum plano ativo no momento.</p>
            <p>Procure a recepção para realizar sua matrícula.</p>
        <?php endif; ?>

        <a href="/Gymflow/app/controllers/PortalAlunoController.php?acao=treinos">Treinar agora</a>
    </section>

    <section>
        <h2>Frequência do mês</h2>
        <p><?php echo (int)$frequencia; ?> dia<?php echo $frequencia != 1 ? 's' : ''; ?> treinado<?php echo $frequencia != 1 ? 's' : ''; ?> em <?php echo date('F'); ?></p>
    </section>

    <section>
        <h2>Minhas Faturas</h2>

        <?php if ($faturas_abertas > 0): ?>
            <p>Você tem <strong><?php echo (int)$faturas_abertas; ?></strong> fatura<?php echo $faturas_abertas > 1 ? 's' : ''; ?> em aberto.</p>
        <?php else: ?>
            <p>Nenhuma fatura pendente. Tudo em dia!</p>
        <?php endif; ?>

        <a href="/Gymflow/app/controllers/PortalAlunoController.php?acao=faturas">Ver faturas</a>
    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>