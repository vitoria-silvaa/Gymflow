<?php

$tituloPagina = "Treinos";

include '../shared/navbar.php';

$treinos = [
    "A" => [
        "versao" => "v1",
        "exercicios" => [
            [
                "nome" => "Supino Reto",
                "series" => 4,
                "reps" => 10,
                "descanso" => 60,
                "carga" => "60kg"
            ],
            [
                "nome" => "Crucifixo Inclinado",
                "series" => 3,
                "reps" => 12,
                "descanso" => 45,
                "carga" => "20kg"
            ],
            [
                "nome" => "Tríceps Pulley",
                "series" => 3,
                "reps" => 12,
                "descanso" => 45,
                "carga" => "25kg"
            ]
        ]
    ],

    "B" => [
        "versao" => "v2",
        "exercicios" => [
            [
                "nome" => "Puxada Frontal",
                "series" => 4,
                "reps" => 10,
                "descanso" => 60,
                "carga" => "55kg"
            ],
            [
                "nome" => "Remada Curvada",
                "series" => 4,
                "reps" => 10,
                "descanso" => 60,
                "carga" => "45kg"
            ],
            [
                "nome" => "Rosca Direta",
                "series" => 3,
                "reps" => 12,
                "descanso" => 45,
                "carga" => "14kg"
            ]
        ]
    ],

    "C" => [
        "versao" => "v3",
        "exercicios" => [
            [
                "nome" => "Agachamento Livre",
                "series" => 4,
                "reps" => 8,
                "descanso" => 90,
                "carga" => "80kg"
            ],
            [
                "nome" => "Leg Press 45°",
                "series" => 4,
                "reps" => 12,
                "descanso" => 60,
                "carga" => "120kg"
            ],
            [
                "nome" => "Desenvolvimento Militar",
                "series" => 3,
                "reps" => 10,
                "descanso" => 60,
                "carga" => "30kg"
            ]
        ]
    ]
];

$treinoAtual = $_GET['treino'] ?? 'A';

?>

<main>

    <h1>Treinos</h1>

    <p>Escolha o treino do dia e marque cada exercício.</p>

    <nav>
        <a href="?treino=A">Treino A</a>
        <a href="?treino=B">Treino B</a>
        <a href="?treino=C">Treino C</a>
    </nav>

    <br>

    <span>Hipertrofia</span>
    <span><?php echo $treinos[$treinoAtual]['versao']; ?></span>

    <hr>

    <?php foreach ($treinos[$treinoAtual]['exercicios'] as $exercicio): ?>

        <section>

            <input type="checkbox">

            <h2>
                <?php echo $exercicio['nome']; ?>
            </h2>

            <p>
                <?php echo $exercicio['series']; ?> séries ×
                <?php echo $exercicio['reps']; ?> reps •
                descanso <?php echo $exercicio['descanso']; ?>s
            </p>

            <label>Carga</label>
            <br>

            <input
                type="text"
                value="<?php echo $exercicio['carga']; ?>"
            >

            <button type="button">Iniciar pausa</button>

            <hr>

        </section>

    <?php endforeach; ?>

</main>

<?php include '../shared/footer.php'; ?>