<?php
// app/controllers/TreinoController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin', 'Professor', 'Recepcao']);

$tituloPagina = "Treinos";

$operacao = 'listar';
require __DIR__ . '/../models/Aluno.php';

/* BUSCAR HISTÓRICO DO ALUNO */
if (isset($_GET['acao']) && $_GET['acao'] === 'historico') {

    $aluno_id = (int) ($_GET['aluno_id'] ?? 0);

    if ($aluno_id <= 0) {
        header('Content-Type: application/json');
        echo json_encode([]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT
            ft.id AS ficha_id,
            ft.objetivo,
            ft.versao,
            ft.criada_em,
            u.name AS nome_professor,
            fi.ordem,
            fi.series,
            fi.repeticoes,
            fi.carga,
            fi.intervalo,
            e.nome AS nome_exercicio
        FROM fichas_treino ft
        INNER JOIN users u
            ON u.id = ft.professor_id
        LEFT JOIN ficha_itens fi
            ON fi.ficha_id = ft.id
        LEFT JOIN exercicios e
            ON e.id = fi.exercicio_id
        WHERE ft.aluno_id = :aluno_id
        ORDER BY ft.versao DESC, fi.ordem ASC
    ");

    $stmt->execute([
        ':aluno_id' => $aluno_id
    ]);

    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $historico = [];

    foreach ($resultado as $item) {

        $ficha_id = $item['ficha_id'];

        if (!isset($historico[$ficha_id])) {
            $historico[$ficha_id] = [
                'ficha_id' => $ficha_id,
                'objetivo' => $item['objetivo'],
                'versao' => $item['versao'],
                'criada_em' => $item['criada_em'],
                'nome_professor' => $item['nome_professor'],
                'itens' => []
            ];
        }

        if ($item['nome_exercicio'] !== null) {
            $historico[$ficha_id]['itens'][] = [
                'nome_exercicio' => $item['nome_exercicio'],
                'series' => $item['series'],
                'repeticoes' => $item['repeticoes'],
                'carga' => $item['carga'],
                'intervalo' => $item['intervalo']
            ];
        }
    }

    header('Content-Type: application/json');

    echo json_encode(array_values($historico));

    exit;
}

$mensagemSucesso = '';
$mensagemErro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $professor_id = (int) ($_POST['professor_id'] ?? 0);
    $aluno_id = (int) ($_POST['aluno'] ?? 0);
    $objetivo = trim($_POST['objetivo'] ?? '');
    $itens = $_POST['itens'] ?? [];

    if ($professor_id <= 0 || $aluno_id <= 0 || $objetivo === '' || empty($itens)) {

        $mensagemErro = 'Preencha todos os campos obrigatórios e adicione pelo menos um exercício.';
    } else {

        $operacao = 'salvar_ficha';

        $ficha_id = require __DIR__ . '/../models/Treino.php';

        $mensagemSucesso = 'Ficha de treino salva com sucesso!';
    }

    $operacao = 'salvar_ficha';

    $ficha_id = require __DIR__ . '/../models/Treino.php';

    $mensagemSucesso = 'Ficha de treino salva com sucesso!';
}

require __DIR__ . '/../models/Exercicio.php';

$exercicios = listarExercicios($pdo);

/* LISTAR PROFESSORES */
$stmt_professores = $pdo->prepare("
    SELECT id, name
    FROM users
    WHERE role = 'Professor'
    ORDER BY name ASC
");

$stmt_professores->execute();

$professores = $stmt_professores->fetchAll();

require __DIR__ . '/../views/treinos/index.php';
