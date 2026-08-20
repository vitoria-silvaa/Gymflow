<?php
if (!isset($tituloPagina)) {
    header("Location: /Gymflow/app/controllers/FluxoCaixaController.php");
    exit;
}

$tituloPagina = "Lançar custo";

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>

<main>

    <h1>Novo Custo</h1>

    <section>

        <form
            method="POST"
            action="/Gymflow/app/controllers/FluxoCaixaController.php?acao=confirmar_lancamento"
        >

            <label for="descricao">
                Descrição
            </label>

            <input
                type="text"
                name="descricao"
                id="descricao"
                required
            >

            <br><br>


            <label for="categoria">
                Categoria
            </label>

            <select
                name="categoria"
                id="categoria"
                required
            >

                <option value="Infraestrutura">
                    Infraestrutura
                </option>

                <option value="Equipamentos">
                    Pessoal 
                </option>

                <option value="Contas de Consumo">
                    Utilidades

                <option value="Pessoal">
                      Marketing
                </option>

                <option value="Marketing">
                   Equipamentos
                </option>

                <option value="Outros">
                    Outros
                </option>

            </select>

            <br><br>


            <label for="valor">
                Valor
            </label>

            <input
                type="number"
                name="valor"
                id="valor"
                step="0.01"
                min="0.01"
                required
            >

            <br><br>


            <label for="data">
                Data
            </label>

            <input
                type="date"
                name="data"
                id="data"
                value="<?= date('Y-m-d') ?>"
                required
            >

            <br><br>


            <button type="submit">
                Lançar
            </button>

            <a
                href="/Gymflow/app/controllers/FluxoCaixaController.php"
            >
                Cancelar
            </a>

        </form>

    </section>

</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>