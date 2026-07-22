<?php
$tituloPagina = "GymCore";
include 'app/views/shared/portfolio_header.php';
?>

<main>
    <section id="inicio">
          <!--hero-->

        <h1>Transforme seu corpo. Eleve sua mente.</h1>

        <p>
          Mais de 15 anos formando atletas, profissionais e pessoas comuns que decidiram 
          viver melhor. Equipamentos premium, professores qualificados e estrutura completa.

        </p>

        <a href="#planos">
            Matricule-se Agora
        </a>

        <a href="#unidades">
           Encontrar unidade
        </a>
    </section>

      <!--planos-->
    <hr>
    <section id="planos">
         <h3>Nossos Planos</h3>
         <h1>Escolha o plano ideal para sua evolução</h1>
        <h2>Sem fidelidade obrigatória. Cancele quando quiser.</h2>

       <article>

        <h3>Essential</h3>

        <h4>R$ 99,90 / mês</h4>

        <ul>
            <li>Musculação livre</li>
            <li>Acesso a 1 unidade</li>
            <li>Avaliação física trimestral</li>
        </ul>

        <a href="app/views/portfolio/checkout.php">Matricule-se agora</a>

    </article>

    <article>

        <h3>Performance</h3>

        <h4>R$ 159,90 / mês</h4>

        <ul>
            <li>Musculação + aulas coletivas</li>
            <li>Acesso a todas as unidades</li>
            <li>Ficha personalizada mensal</li>
            <li>Avaliação física mensal</li>
        </ul>

        <a href="app/views/portfolio/checkout.php">Matricule-se agora</a>

    </article>

    <article>

        <h3>Elite Black</h3>

        <h4>R$ 249,90 / mês</h4>

        <ul>
            <li>Tudo do Performance</li>
            <li>Personal Trainer 4x por semana</li>
            <li>Acompanhamento nutricional</li>
            <li>Espaço relax & sauna</li>
        </ul>

        <a href="app/views/portfolio/checkout.php">Matricule-se agora</a>

    </article>

    </section> 

  <!--unidades-->
    <hr>
    <section id="unidades">
        <h3>Nossas Unidades</h3>
        <h1>Encontre a unidade mais próxima</h1>

         <article>

            <h3>Unidade Centro</h3>

            <p>Av. Paulista, 1200 – Bela Vista, São Paulo/SP</p>

            <a href="#">Saiba mais</a>

        </article>

        <br>

        <article>

            <h3>Unidade Zona Sul</h3>

            <p>R. Vergueiro, 3000 – Vila Mariana, São Paulo/SP</p>

            <a href="#">Saiba mais</a>

        </article>

        <br>

        <article>

            <h3>Unidade Litoral</h3>

            <p>Av. Beira-Mar, 450 – Santos/SP</p>

            <a href="#">Saiba mais</a>

        </article>

        <br><br>

        <section>

            <h3>Mapa das Unidades</h3>

            <p>
                Nesta área será exibido o mapa com a localização das unidades
                disponíveis da GymCore.
            </p>

        </section>

    </section>

</main>

<?php include 'app/views/shared/footer.php'; ?>