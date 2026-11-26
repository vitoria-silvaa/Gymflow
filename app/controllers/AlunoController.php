<?php
// app/controllers/AlunoController.php

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin', 'Recepcao']);

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../helpers/validacao-aluno.php';

$baseUrl = BASE_URL . '/app/controllers/AlunoController.php';

$acao = $_GET['acao'] ?? 'listar';

/* VALIDAR VÍNCULOS DO ALUNO */

function gymflowValidarVinculosAluno(
    PDO $pdo,
    array $dados
): string {

    $empresaId = (int) ($_SESSION['company_id'] ?? 0);
    $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
    $role = $_SESSION['usuario_role'] ?? '';

    if (
        $empresaId <= 0 ||
        $usuarioId <= 0 ||
        !in_array($role, ['Admin', 'Recepcao'], true)
    ) {
        return 'Acesso não autorizado.';
    }

    $sql = "
        SELECT f.id

        FROM filiais f

        WHERE f.id = :filial
          AND f.company_id = :empresa
          AND f.ativo = TRUE
    ";

    $parametros = [
        ':filial' => $dados['filial_id'],
        ':empresa' => $empresaId
    ];

    if ($role === 'Recepcao') {

        $sql .= "
            AND EXISTS (
                SELECT 1
                FROM user_filiais uf
                WHERE uf.filial_id = f.id
                  AND uf.user_id = :usuario
            )
        ";

        $parametros[':usuario'] = $usuarioId;
    }

    $stmt = $pdo->prepare($sql);

    $stmt->execute($parametros);

    if (!$stmt->fetchColumn()) {
        return 'A filial selecionada não está autorizada.';
    }

    if ($dados['professor_id'] !== null) {

        $stmt = $pdo->prepare("
            SELECT id

            FROM users

            WHERE id = :professor
              AND company_id = :empresa
              AND role = 'Professor'

            LIMIT 1
        ");

        $stmt->execute([
            ':professor' => $dados['professor_id'],
            ':empresa' => $empresaId
        ]);

        if (!$stmt->fetchColumn()) {

            return 'O professor selecionado não está autorizado.';
        }
    }

    return '';
}

/* 1. LISTAR ALUNOS */

if ($acao === 'listar') {

    $cpf = is_string($_GET['cpf'] ?? '')
        ? trim($_GET['cpf'] ?? '')
        : '';

    $status = is_string($_GET['status'] ?? '')
        ? trim($_GET['status'] ?? '')
        : '';

    $filial_id_filter = (int) (
        $_GET['filial_id'] ?? 0
    );

    $operacao = 'listar';

    require __DIR__ . '/../models/Aluno.php';

    $operacao = 'listar_filiais';

    require __DIR__ . '/../models/Aluno.php';

    require __DIR__ . '/../views/alunos/index.php';
}

/* 2. CADASTRAR ALUNO */ elseif ($acao === 'cadastrar') {

    $erro = '';
    $dados = [];

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

        [$dados, $erro] =
            gymflowValidarFormularioAluno($_POST);

        if ($erro === '') {

            $erro = gymflowValidarVinculosAluno(
                $pdo,
                $dados
            );
        }

        if ($erro === '') {

            $operacao = 'cadastrar';

            require __DIR__ . '/../models/Aluno.php';

            if ($erroModel === '') {

                header(
                    "Location: $baseUrl?acao=visualizar&id=$novoAlunoId&msg=cadastrado"
                );

                exit;
            }

            $erro = $erroModel;
        }

        unset($dados['senha']);
    }

    $operacao = 'listar_filiais';

    require __DIR__ . '/../models/Aluno.php';

    $stmt_professores = $pdo->prepare("
        SELECT id, name

        FROM users

        WHERE role = 'Professor'
          AND company_id = ?

        ORDER BY name
    ");

    $stmt_professores->execute([
        $_SESSION['company_id'] ?? 0
    ]);

    $professores = $stmt_professores->fetchAll();

    require __DIR__ . '/../views/alunos/cadastrar.php';
}

/* 3. EDITAR ALUNO */ elseif ($acao === 'editar') {

    $id = filter_var(
        $_GET['id'] ?? $_POST['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if (!$id) {

        header("Location: $baseUrl?acao=listar");

        exit;
    }

    $operacao = 'buscar';

    require __DIR__ . '/../models/Aluno.php';

    if (!$aluno) {

        header("Location: $baseUrl?acao=listar");

        exit;
    }

    $erro = '';
    $dados = $aluno;

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

        [$dados, $erro] =
            gymflowValidarFormularioAluno(
                $_POST,
                true
            );

        if ($erro === '') {

            $erro = gymflowValidarVinculosAluno(
                $pdo,
                $dados
            );
        }

        if ($erro === '') {

            $operacao = 'atualizar';

            require __DIR__ . '/../models/Aluno.php';

            if ($erroModel === '') {

                header(
                    "Location: $baseUrl?acao=visualizar&id=$id&msg=atualizado"
                );

                exit;
            }

            $erro = $erroModel;
        }

        unset($dados['senha']);
    }

    $operacao = 'listar_filiais';

    require __DIR__ . '/../models/Aluno.php';

    $stmt_professores = $pdo->prepare("
        SELECT id, name

        FROM users

        WHERE role = 'Professor'
          AND company_id = ?

        ORDER BY name
    ");

    $stmt_professores->execute([
        $_SESSION['company_id'] ?? 0
    ]);

    $professores = $stmt_professores->fetchAll();

    require __DIR__ . '/../views/alunos/editar.php';
}

/* 4. VISUALIZAR ALUNO */ elseif ($acao === 'visualizar') {

    $id = (int) ($_GET['id'] ?? 0);

    $operacao = 'visualizar';

    require __DIR__ . '/../models/Aluno.php';

    if (!$aluno) {

        header("Location: $baseUrl?acao=listar");

        exit;
    }

    $erro = $_SESSION['erro_aluno'] ?? '';

    unset($_SESSION['erro_aluno']);

    $sucesso = match ($_GET['msg'] ?? '') {

        'cadastrado' =>
        'Aluno cadastrado com sucesso!',

        'atualizado' =>
        'Aluno atualizado com sucesso!',

        'matriculado' =>
        'Matrícula realizada com sucesso!',

        'pago' =>
        'Pagamento registrado com sucesso!',

        default => ''
    };

    require __DIR__ . '/../views/alunos/visualizar.php';
}

/* 5. MATRICULAR ALUNO */ elseif ($acao === 'matricular') {

    $alunoId = (int) ($_POST['aluno_id'] ?? 0);

    $planoId = (int) ($_POST['plano_id'] ?? 0);

    $dataInicio = $_POST['data_inicio'] ?? '';

    $desconto = (float) str_replace(
        ',',
        '.',
        $_POST['desconto'] ?? '0'
    );

    if (
        $alunoId <= 0 ||
        $planoId <= 0 ||
        empty($dataInicio) ||
        $desconto < 0
    ) {

        $_SESSION['erro_aluno'] =
            'Preencha corretamente os dados da matrícula.';
    } else {

        $operacao = 'matricular';

        require __DIR__ . '/../models/Aluno.php';

        if ($erroModel !== '') {

            $_SESSION['erro_aluno'] = $erroModel;
        } else {

            header(
                "Location: $baseUrl?acao=visualizar&id=$alunoId&msg=matriculado"
            );

            exit;
        }
    }

    header(
        "Location: $baseUrl?acao=visualizar&id=$alunoId"
    );

    exit;
}

/* 6. REGISTRAR PAGAMENTO */ elseif ($acao === 'pagar') {

    $alunoId = (int) ($_POST['aluno_id'] ?? 0);

    $contaId = (int) ($_POST['conta_id'] ?? 0);

    $operacao = 'pagar';

    require __DIR__ . '/../models/Aluno.php';

    if (!empty($pagamentoRealizado)) {

        header(
            "Location: $baseUrl?acao=visualizar&id=$alunoId&msg=pago"
        );

        exit;
    }

    $_SESSION['erro_aluno'] =
        'A mensalidade não foi encontrada ou já está paga.';

    header(
        "Location: $baseUrl?acao=visualizar&id=$alunoId"
    );

    exit;
}

/* REDIRECIONAMENTO PADRÃO */ else {

    header("Location: $baseUrl?acao=listar");

    exit;
}
