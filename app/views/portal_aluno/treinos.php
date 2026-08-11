<?php
if (!isset($fichas)) {
    header("Location: /Gymflow/app/controllers/PortalAlunoController.php?acao=treinos");
    exit;
}
/** @var array $fichas */
/** @var array $itens */
/** @var array|null $ficha_atual */
/** @var mixed $ficha_id_selecionada */

$tituloPagina = "Treinos";
?>

<?php include __DIR__ . '/../shared/navbar.php'; ?>

<main>

    <h1>Treinos</h1>

    <p>Escolha a ficha do dia e execute cada exercício.</p>

    <?php if (empty($fichas)): ?>

        <section>
            <p>Você ainda não possui nenhuma ficha de treino cadastrada.</p>
            <p>Solicite ao seu professor para criar uma ficha personalizada para você.</p>
        </section>

    <?php else: ?>

        <!-- Seleção de Ficha -->
        <nav>
            <?php foreach ($fichas as $ficha): ?>
                <a href="/Gymflow/app/controllers/PortalAlunoController.php?acao=treinos&ficha=<?php echo $ficha['id']; ?>">
                    <?php echo htmlspecialchars($ficha['objetivo']); ?>
                    (v<?php echo $ficha['versao']; ?>)
                </a>
            <?php endforeach; ?>
        </nav>

        <br>

        <?php if ($ficha_atual): ?>

            <span><?php echo htmlspecialchars($ficha_atual['objetivo']); ?></span>
            <span>Versão <?php echo $ficha_atual['versao']; ?></span>
            <span>Prof. <?php echo htmlspecialchars($ficha_atual['nome_professor']); ?></span>
            <span>Criada em: <?php echo date('d/m/Y', strtotime($ficha_atual['criada_em'])); ?></span>

            <hr>

            <?php if (empty($itens)): ?>
                <p>Esta ficha ainda não possui exercícios cadastrados.</p>
            <?php else: ?>
                <?php foreach ($itens as $item): ?>

                    <section>

                        <input type="checkbox">

                        <h2><?php echo htmlspecialchars($item['nome_exercicio']); ?></h2>

                        <p><?php echo htmlspecialchars($item['grupo']); ?></p>

                        <p>
                            <?php echo $item['series']; ?> séries ×
                            <?php echo htmlspecialchars($item['repeticoes']); ?> reps
                            <?php if ($item['intervalo']): ?>
                                • descanso <?php echo htmlspecialchars($item['intervalo']); ?>
                            <?php endif; ?>
                        </p>

                        <label>Carga</label>
                        <br>

                        <input
                            type="text"
                            value="<?php echo htmlspecialchars($item['carga'] ?? '—'); ?>"
                        >

                        <button type="button">Iniciar pausa</button>

                        <hr>

                    </section>

                <?php endforeach; ?>
            <?php endif; ?>

        <?php endif; ?>

    <?php endif; ?>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>