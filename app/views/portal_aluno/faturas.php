<?php

$tituloPagina = "Faturas";

include '../shared/navbar.php';

$aba = $_GET['aba'] ?? 'vencer';

?>

<main>

    <h1>Faturas</h1>

    <p>Pague suas mensalidades direto pelo app.</p>

    <nav>

        <a href="?aba=vencer">A vencer (1)</a>

        <a href="?aba=atraso">Em atraso (1)</a>

        <a href="?aba=pagas">Pagas (1)</a>

    </nav>

    <br>

    <?php if ($aba == "vencer"): ?>

        <section>

            <h2>R$ 269,90</h2>

            <p>Vence: 2026-08-05</p>

            <button type="button">Boleto</button>
            <button type="button">Pix</button>
            <button type="button">Cartão</button>

            <p><strong>Status:</strong> Aberto</p>

        </section>

    <?php elseif ($aba == "atraso"): ?>

        <section>

            <h2>R$ 269,90</h2>

            <p>Vence: 2026-07-21</p>

            <button type="button">Boleto</button>
            <button type="button">Pix</button>
            <button type="button">Cartão</button>

            <p><strong>Status:</strong> Atrasado</p>

        </section>

    <?php elseif ($aba == "pagas"): ?>

        <section>

            <h2>R$ 269,90</h2>

            <p>Vence: 2026-07-01</p>

            <p>Pago em: 2026-07-01</p>

            <button type="button">Baixar recibo</button>

            <p><strong>Status:</strong> Pago</p>

        </section>

    <?php endif; ?>

</main>

<?php include '../shared/footer.php'; ?>