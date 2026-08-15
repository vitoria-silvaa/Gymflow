<?php
if (!isset($conta)) {
    header("Location: /Gymflow/app/controllers/FinanceiroController.php");
    exit;
}

$tituloPagina = "Baixar pagamento";

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main>

    <h1>Baixar pagamento</h1>

   

    <section>

        <p>
            <strong>Valor:</strong>
            R$ <?= number_format($conta['valor'], 2, ',', '.') ?>
        </p>

        <p>
            <strong>Vencimento:</strong>
            <?= date('d/m/Y', strtotime($conta['vencimento'])) ?>
        </p>

        <form
            method="POST"
            action="/Gymflow/app/controllers/FinanceiroController.php?acao=confirmar_pagamento"
        >

            <input
                type="hidden"
                name="conta_id"
                value="<?= (int) $conta['id'] ?>"
            >

            <label for="forma_pagamento">
                Forma de pagamento
            </label>

            <select
                name="forma_pagamento"
                id="forma_pagamento"
                required
            >
                <option value="Dinheiro">Dinheiro</option>
                <option value="Pix">Pix</option>
                <option value="Cartão de Crédito">Cartão de Crédito</option>
                <option value="Cartão de Débito">Cartão de Débito</option>
                <option value="Boleto">Boleto</option>
            </select>

            <br><br>

            <button type="submit">
                Confirmar pagamento
            </button>

            <a href="/Gymflow/app/controllers/FinanceiroController.php">
                Cancelar
            </a>

        </form>

    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>