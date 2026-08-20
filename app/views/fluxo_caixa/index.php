<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/FluxoCaixaController.php");
    exit;
}

$tituloPagina = "Fluxo de Caixa";

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main>

    <h1>Fluxo de Caixa & DRE</h1>

    <p>Receitas, custos e resultado consolidado.</p>


    <!-- diário / mensal -->

    <section>

        <a href="/Gymflow/app/controllers/FluxoCaixaController.php?periodo=diario">
            Diário
        </a>

        <a href="/Gymflow/app/controllers/FluxoCaixaController.php?periodo=mensal">
            Mensal
        </a>

    </section>


    <br>


    <!-- botão lançar custo -->

    <section>

        <a
            href="/Gymflow/app/controllers/FluxoCaixaController.php?acao=lancar"
        >
            + Lançar Custo
        </a>

    </section>


    <br>


    <!-- resumo -->

    <section>

        <div>

            <p>Receitas</p>

            <h2>
                R$
                <?= number_format(
                    $totalReceitas,
                    2,
                    ',',
                    '.'
                ) ?>
            </h2>

            <p>
                <?= (int) $quantidadeReceitas ?>
                pagamentos
            </p>

        </div>


        <div>

            <p>Custos</p>

            <h2>
                R$
                <?= number_format(
                    $totalCustos,
                    2,
                    ',',
                    '.'
                ) ?>
            </h2>

            <p>
                <?= (int) $quantidadeCustos ?>
                lançamentos
            </p>

        </div>


        <div>

            <p>Resultado</p>

            <h2>
                R$
                <?= number_format(
                    $resultado,
                    2,
                    ',',
                    '.'
                ) ?>
            </h2>

            <p>
                Receitas - Custos
            </p>

        </div>

    </section>


    <br>


    <!-- DRE -->

    <section>

        <h2>DRE Simplificada</h2>


        <p>

            (+) Receita Bruta de Mensalidades

            <span>
                R$
                <?= number_format(
                    $totalReceitas,
                    2,
                    ',',
                    '.'
                ) ?>
            </span>

        </p>


        <p>

            (=) Total de Custos

            <span>
                R$
                <?= number_format(
                    $totalCustos,
                    2,
                    ',',
                    '.'
                ) ?>
            </span>

        </p>


        <p>

            (=) Resultado Líquido

            <strong>
                R$
                <?= number_format(
                    $resultado,
                    2,
                    ',',
                    '.'
                ) ?>
            </strong>

        </p>

    </section>


    <br>


    <!-- lançamentos -->

    <section>

        <h2>Lançamentos de Custos</h2>


        <table>

            <thead>

                <tr>

                    <th>Data</th>

                    <th>Descrição</th>

                    <th>Categoria</th>

                    <th>Valor</th>

                </tr>

            </thead>


            <tbody>

                <?php if (empty($custos)): ?>

                    <tr>

                        <td colspan="4">
                            Nenhum custo no período
                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($custos as $custo): ?>

                        <tr>

                            <td>
                                <?= date(
                                    'd/m/Y',
                                    strtotime($custo['data'])
                                ) ?>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $custo['descricao']
                                ) ?>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $custo['categoria']
                                ) ?>
                            </td>


                            <td>
                                R$
                                <?= number_format(
                                    $custo['valor'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </section>

</main>


<?php include __DIR__ . '/../shared/footer.php'; ?>