<?php
if (!isset($todas_contas)) {
    header("Location: /Gymflow/app/controllers/PortalAlunoController.php?acao=faturas");
    exit;
}
/** @var array $todas_contas */
/** @var array $a_vencer */
/** @var array $em_atraso */
/** @var array $pagas */
/** @var string $aba */
/** @var string $hoje */

$tituloPagina = "Faturas";
?>

<?php include __DIR__ . '/../shared/navbar.php'; ?>

<main>

    <h1>Faturas</h1>

    <p>Acompanhe o histórico de pagamentos da sua mensalidade.</p>

    <nav>
        <a href="/Gymflow/app/controllers/PortalAlunoController.php?acao=faturas&aba=vencer">A vencer (<?php echo count($a_vencer); ?>)</a>
        <a href="/Gymflow/app/controllers/PortalAlunoController.php?acao=faturas&aba=atraso">Em atraso (<?php echo count($em_atraso); ?>)</a>
        <a href="/Gymflow/app/controllers/PortalAlunoController.php?acao=faturas&aba=pagas">Pagas (<?php echo count($pagas); ?>)</a>
    </nav>

    <br>

    <?php
    $lista = match ($aba) {
        'atraso' => $em_atraso,
        'pagas'  => $pagas,
        default  => $a_vencer
    };
    ?>

    <?php if (empty($lista)): ?>

        <p>Nenhuma fatura encontrada nesta categoria.</p>

    <?php else: ?>

        <?php foreach ($lista as $conta): ?>

            <section>

                <h2>R$ <?php echo number_format($conta['valor'], 2, ',', '.'); ?></h2>

                <p>Vencimento: <?php echo date('d/m/Y', strtotime($conta['vencimento'])); ?></p>

                <?php if ($conta['status'] === 'Pago'): ?>

                    <p>Pago em: <?php echo date('d/m/Y', strtotime($conta['pago_em'])); ?></p>
                    <p>Forma: <?php echo htmlspecialchars($conta['forma_pagamento'] ?? '—'); ?></p>
                    <button type="button">Baixar recibo</button>

                <?php else: ?>

                    <p><strong>Status:</strong>
                        <?php echo $conta['vencimento'] < $hoje ? 'Em atraso' : 'Aberto'; ?>
                    </p>

                <?php endif; ?>

            </section>

        <?php endforeach; ?>

    <?php endif; ?>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>