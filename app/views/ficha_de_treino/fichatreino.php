<?php
if (!isset($filiais)) {
    header("Location: /Gymflow/app/controllers/FilialController.php?acao=listar");
    exit;
}
/** @var array $filiais */
/** @var string $statusFiltro */

$tituloPagina = "Filiais";
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/sidebar.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<body>

    <main>

        <div>


            <div>

                <a href="/Gymflow/index.php">
                    ← Voltar para o site
                </a>

                <div>
                    <h1>
                        Fichas de treino
                    </h1>

                    <p>
                        Monte o treino do aluno.
                    </p>
                </div>

            </div>


        </div>

    </main>

</body>

</html>