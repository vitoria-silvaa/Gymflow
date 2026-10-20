
<?php
// ================================================
// GYMFLOW - CONTROLLER DE TREINOS
// app/controllers/TreinoController.php
// ================================================

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin', 'Professor']);

$company_id = (int) ($_SESSION['company_id'] ?? 0);
$usuario_id = (int) ($_SESSION['usuario_id'] ?? 0);
$usuario_role = $_SESSION['usuario_role'] ?? '';

if ($company_id <= 0 || $usuario_id <= 0) {
    http_response_code(403);
    exit('Acesso não autorizado.');
}

$tituloPagina = "Treinos";

$cssEspecifico = BASE_URL
    . '/assets/css/fichatreino.css?v='
    . time();

// ================================================
// LISTAGEM DE ALUNOS
// ================================================

$operacao = 'listar';

require __DIR__ . '/../models/Aluno.php';

// ================================================
// HISTÓRICO DE TREINOS - AJAX
// ================================================

if (
    isset($_GET['acao']) &&
    $_GET['acao'] === 'historico'
) {

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    $aluno_id = filter_var(
        $_GET['aluno_id'] ?? null,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ]
        ]
    );

    if ($aluno_id === false) {
        http_response_code(400);

        echo json_encode([
            'erro' => 'Aluno inválido.'
        ]);

        exit;
    }

    // Verifica se o aluno pertence à empresa.
    $stmtAluno = $pdo->prepare("
        SELECT a.id
        FROM alunos a
        INNER JOIN filiais f
            ON f.id = a.filial_id
        WHERE a.id = :aluno_id
          AND f.company_id = :company_id
        LIMIT 1
    ");

    $stmtAluno->execute([
        ':aluno_id' => $aluno_id,
        ':company_id' => $company_id
    ]);

    if (!$stmtAluno->fetch(PDO::FETCH_ASSOC)) {

        http_response_code(404);

        echo json_encode([
            'erro' => 'Aluno não encontrado.'
        ]);

        exit;
    }

    // Recupera o histórico somente de alunos
    // e professores vinculados à empresa atual.

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

        INNER JOIN alunos a
            ON a.id = ft.aluno_id

        INNER JOIN filiais f
            ON f.id = a.filial_id

        INNER JOIN users u
            ON u.id = ft.professor_id
           AND u.company_id = f.company_id

        LEFT JOIN ficha_itens fi
            ON fi.ficha_id = ft.id

        LEFT JOIN exercicios e
            ON e.id = fi.exercicio_id

        WHERE ft.aluno_id = :aluno_id
          AND f.company_id = :company_id

        ORDER BY ft.versao DESC, fi.ordem ASC
    ");

    $stmt->execute([
        ':aluno_id' => $aluno_id,
        ':company_id' => $company_id
    ]);

    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $historico = [];

    foreach ($resultado as $item) {

        $ficha_id = (int) $item['ficha_id'];

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

    echo json_encode(
        array_values($historico),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

// ================================================
// MENSAGENS DA TELA
// ================================================

$mensagemSucesso = '';
$mensagemErro = '';

// ================================================
// SALVAR FICHA DE TREINO
// ================================================

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $professor_id = (int) (
        $_POST['professor_id'] ?? 0
    );

    $aluno_id = (int) (
        $_POST['aluno'] ?? 0
    );

    $objetivo = trim(
        $_POST['objetivo'] ?? ''
    );

    $itens = $_POST['itens'] ?? [];

    if (
        $professor_id <= 0 ||
        $aluno_id <= 0 ||
        $objetivo === '' ||
        !is_array($itens) ||
        empty($itens)
    ) {

        $mensagemErro =
            'Preencha todos os campos obrigatórios '
            . 'e adicione pelo menos um exercício.';
    } else {

        // O professor logado não pode atribuir
        // a ficha a outro professor.

        if (
            $usuario_role === 'Professor' &&
            $professor_id !== $usuario_id
        ) {

            $mensagemErro =
                'Você não pode cadastrar uma ficha '
                . 'em nome de outro professor.';
        } else {

            // Confirma professor pertencente à empresa.

            $stmtProfessor = $pdo->prepare("
                SELECT id
                FROM users
                WHERE id = :professor_id
                  AND company_id = :company_id
                  AND role = 'Professor'
                LIMIT 1
            ");

            $stmtProfessor->execute([
                ':professor_id' => $professor_id,
                ':company_id' => $company_id
            ]);

            $professorValido = (bool) $stmtProfessor->fetch(
                PDO::FETCH_ASSOC
            );

            // Confirma aluno pertencente à empresa.

            $stmtAluno = $pdo->prepare("
                SELECT a.id
                FROM alunos a
                INNER JOIN filiais f
                    ON f.id = a.filial_id
                WHERE a.id = :aluno_id
                  AND f.company_id = :company_id
                LIMIT 1
            ");

            $stmtAluno->execute([
                ':aluno_id' => $aluno_id,
                ':company_id' => $company_id
            ]);

            $alunoValido = (bool) $stmtAluno->fetch(
                PDO::FETCH_ASSOC
            );

            if (!$professorValido || !$alunoValido) {

                $mensagemErro =
                    'Professor ou aluno inválido '
                    . 'para esta academia.';
            } else {

                $operacao = 'salvar_ficha';

                try {

                    $ficha_id = require __DIR__
                        . '/../models/Treino.php';

                    $mensagemSucesso =
                        'Ficha de treino salva com sucesso!';
                } catch (Throwable $e) {

                    error_log(
                        '[GymFlow][TreinoController] ' .
                            $e->getMessage()
                    );

                    $mensagemErro =
                        'Não foi possível salvar a ficha. '
                        . 'Tente novamente.';
                }
            }
        }
    }
}

// ================================================
// LISTAR EXERCÍCIOS
// ================================================

require __DIR__ . '/../models/Exercicio.php';

$exercicios = listarExercicios($pdo);

// ================================================
// LISTAR PROFESSORES
// ================================================

if ($usuario_role === 'Professor') {

    $stmt_professores = $pdo->prepare("
        SELECT id, name
        FROM users
        WHERE id = :usuario_id
          AND company_id = :company_id
          AND role = 'Professor'
        LIMIT 1
    ");

    $stmt_professores->execute([
        ':usuario_id' => $usuario_id,
        ':company_id' => $company_id
    ]);
} else {

    $stmt_professores = $pdo->prepare("
        SELECT id, name
        FROM users
        WHERE role = 'Professor'
          AND company_id = :company_id
        ORDER BY name ASC
    ");

    $stmt_professores->execute([
        ':company_id' => $company_id
    ]);
}

$professores = $stmt_professores->fetchAll(
    PDO::FETCH_ASSOC
);

// ================================================
// VIEW
// ================================================

require __DIR__ . '/../views/treinos/index.php';
