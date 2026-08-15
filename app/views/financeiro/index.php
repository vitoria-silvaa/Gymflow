<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/FinanceiroController.php");
    exit;
}

$tituloPagina = "Financeiro";

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main>

    <h1>Contas a Receber</h1>

    <p>Lançamentos gerados pelas matrículas.</p>


    <!-- resumo financeirp -->

    <section>

        <div>
            <p>Total em Aberto</p>
            <h2>
    R$ <?= number_format($totalAberto, 2, ',', '.') ?>
</h2>
        </div>

        <div>
            <p>Total Recebido</p>
         <h2>
    R$ <?= number_format($totalRecebido, 2, ',', '.') ?>
</h2>
        </div>

    </section>


    <br>


    <!-- contas -->

    <section>

        <table>

            <thead>

                <tr>
                    <th>Aluno</th>
                    <th>Matrícula</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>

            </thead>


            <tbody>

                <?php if (empty($contas)): ?>

                    <tr>
                        <td colspan="6">
                            Nenhuma conta encontrada.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($contas as $conta): ?>

                        <tr>

                            <!-- aluno -->
                            <td>
                                <?= htmlspecialchars($conta['aluno_nome']) ?>
                            </td>


                            <!-- matricula -->
                            <td>
                                <?= htmlspecialchars($conta['matricula_id']) ?>
                            </td>


                            <!-- vencimento -->
                            <td>
                                <?= date(
                                    'd/m/Y',
                                    strtotime($conta['vencimento'])
                                ) ?>
                            </td>


                            <!-- valor -->
                            <td>
                                R$
                                <?= number_format(
                                    $conta['valor'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                            </td>


                            <!-- status -->
                            <td>
                                <?= htmlspecialchars($conta['status']) ?>
                            </td>


                            <!-- acoes -->
                            <td>

                                <?php if ($conta['status'] !== 'Pago'): ?>

                                    <a
                                        href="/Gymflow/app/controllers/FinanceiroController.php?acao=baixar&id=<?= $conta['id'] ?>"
                                    >
                                        Baixar
                                    </a>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </section>

</main>


<?php include __DIR__ . '/../shared/footer.php'; ?>