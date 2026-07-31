<?php
$tituloPagina = "Portal do Aluno";
?>

<?php include '../shared/navbar.php'; ?>

<main>

    <header>
        <h1>Olá, aluno!</h1>
        <p>Pronto para o treino de hoje?</p>
    </header>

    <section>
        <h2>Meu Plano</h2>

        <h3>Plano Pro Trimestral</h3>

        <p>Plano ativo</p>
        <p>Expira em: 31/08/2026</p>

        <a href="treinos.php">Treinar agora</a>
    </section>

    <section>
        <h2>Frequência do mês</h2>
        <p>0 de 31 dias treinados</p>
    </section>

    <section>
        <h2>Minhas Faturas</h2>
        <p>Ver pagamentos e baixar recibos.</p>

        <a href="faturas.php">Ver faturas</a>
    </section>

</main>

<?php include '../shared/footer.php'; ?>