
<?php
// ===============================================
// GYMFLOW - MODEL DE TREINOS
// app/models/Treino.php
//
// Isolamento por empresa e usuário.
// Mantém as operações e retornos existentes.
// ===============================================

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';

$company_id = (int) ($_SESSION['company_id'] ?? 0);
$usuario_id = (int) ($_SESSION['usuario_id'] ?? 0);
$usuario_role = $_SESSION['usuario_role'] ?? '';

$aluno_id = (int) ($aluno_id ?? 0);

// ===============================================
// 1. LISTAR FICHAS DE UM ALUNO
// ===============================================

if ($operacao === 'listar_fichas_aluno') {

    $fichas = [];

    $acessoPermitido = (
        $company_id > 0 &&
        $usuario_id > 0 &&
        $aluno_id > 0
    );

    // Aluno só pode consultar suas próprias fichas.
    if ($usuario_role === 'Aluno') {

        $alunoLogado = (int) (
            $_SESSION['aluno_id'] ?? 0
        );

        $acessoPermitido = (
            $acessoPermitido &&
            $alunoLogado > 0 &&
            $alunoLogado === $aluno_id
        );
    } elseif (!in_array(
        $usuario_role,
        ['Admin', 'Professor'],
        true
    )) {

        $acessoPermitido = false;
    }

    if ($acessoPermitido) {

        $stmt_fichas = $pdo->prepare("
            SELECT
                ft.id,
                ft.objetivo,
                ft.versao,
                ft.criada_em,
                u.name AS nome_professor

            FROM fichas_treino ft

            INNER JOIN alunos a
                ON a.id = ft.aluno_id

            INNER JOIN filiais f
                ON f.id = a.filial_id

            INNER JOIN users u
                ON u.id = ft.professor_id
               AND u.company_id = f.company_id

            WHERE ft.aluno_id = :aluno_id
              AND f.company_id = :company_id

            ORDER BY ft.criada_em DESC
        ");

        $stmt_fichas->execute([
            ':aluno_id' => $aluno_id,
            ':company_id' => $company_id
        ]);

        $fichas = $stmt_fichas->fetchAll(
            PDO::FETCH_ASSOC
        );
    }
}

// ===============================================
// 2. BUSCAR ITENS DE UMA FICHA
// ===============================================

elseif ($operacao === 'buscar_itens_ficha') {

    $itens = [];

    $ficha_id = (int) ($ficha_id ?? 0);

    $acessoPermitido = (
        $company_id > 0 &&
        $usuario_id > 0 &&
        $aluno_id > 0 &&
        $ficha_id > 0
    );

    if ($usuario_role === 'Aluno') {

        $alunoLogado = (int) (
            $_SESSION['aluno_id'] ?? 0
        );

        $acessoPermitido = (
            $acessoPermitido &&
            $alunoLogado > 0 &&
            $alunoLogado === $aluno_id
        );
    } elseif (!in_array(
        $usuario_role,
        ['Admin', 'Professor'],
        true
    )) {

        $acessoPermitido = false;
    }

    if ($acessoPermitido) {

        $stmt_itens = $pdo->prepare("
            SELECT
                fi.ordem,
                fi.series,
                fi.repeticoes,
                fi.carga,
                fi.intervalo,
                e.nome AS nome_exercicio,
                e.grupo

            FROM ficha_itens fi

            INNER JOIN exercicios e
                ON e.id = fi.exercicio_id

            INNER JOIN fichas_treino ft
                ON ft.id = fi.ficha_id

            INNER JOIN alunos a
                ON a.id = ft.aluno_id

            INNER JOIN filiais f
                ON f.id = a.filial_id

            INNER JOIN users u
                ON u.id = ft.professor_id
               AND u.company_id = f.company_id

            WHERE fi.ficha_id = :ficha_id
            AND ft.aluno_id = :aluno_id
            AND f.company_id = :company_id
            AND e.company_id = :empresa_exercicio

            ORDER BY fi.ordem ASC
        ");

        $stmt_itens->execute([
            ':ficha_id' => $ficha_id,
            ':aluno_id' => $aluno_id,
            ':company_id' => $company_id,
            ':empresa_exercicio' => $company_id
        ]);

        $itens = $stmt_itens->fetchAll(
            PDO::FETCH_ASSOC
        );
    }
}

// ===============================================
// 3. SALVAR NOVA FICHA DE TREINO
// ===============================================

elseif ($operacao === 'salvar_ficha') {

    $professor_id = (int) (
        $professor_id ?? 0
    );

    $objetivo = trim(
        (string) ($objetivo ?? '')
    );

    $itens = $itens ?? [];

    // -------------------------------------------
    // Validações iniciais
    // -------------------------------------------

    if (
        $company_id <= 0 ||
        $usuario_id <= 0 ||
        !in_array(
            $usuario_role,
            ['Admin', 'Professor'],
            true
        )
    ) {
        throw new RuntimeException(
            'Você não possui permissão para salvar fichas.'
        );
    }

    if (
        $aluno_id <= 0 ||
        $professor_id <= 0 ||
        $objetivo === '' ||
        !is_array($itens) ||
        empty($itens)
    ) {
        throw new RuntimeException(
            'Preencha corretamente os dados da ficha.'
        );
    }

    // Professor não pode cadastrar uma ficha
    // utilizando a identidade de outro professor.

    if (
        $usuario_role === 'Professor' &&
        $professor_id !== $usuario_id
    ) {
        throw new RuntimeException(
            'Você não pode cadastrar fichas em nome de outro professor.'
        );
    }

    // -------------------------------------------
    // Transação SQL
    // -------------------------------------------

    try {

        $pdo->beginTransaction();

        // ---------------------------------------
        // Confirmar aluno e empresa
        //
        // FOR UPDATE bloqueia o registro do aluno
        // durante a geração da próxima versão.
        // ---------------------------------------

        $stmtAluno = $pdo->prepare("
            SELECT a.id
            FROM alunos a

            INNER JOIN filiais f
                ON f.id = a.filial_id

            WHERE a.id = :aluno_id
              AND f.company_id = :company_id

            LIMIT 1
            FOR UPDATE
        ");

        $stmtAluno->execute([
            ':aluno_id' => $aluno_id,
            ':company_id' => $company_id
        ]);

        if (!$stmtAluno->fetch(PDO::FETCH_ASSOC)) {
            throw new RuntimeException(
                'Aluno não encontrado nesta academia.'
            );
        }

        // ---------------------------------------
        // Confirmar professor e empresa
        // ---------------------------------------

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

        if (!$stmtProfessor->fetch(PDO::FETCH_ASSOC)) {
            throw new RuntimeException(
                'Professor inválido para esta academia.'
            );
        }

        // ---------------------------------------
        // Validar todos os exercícios
        // antes de inserir a ficha.
        // ---------------------------------------

        $stmtExercicio = $pdo->prepare("
            SELECT id
            FROM exercicios
            WHERE id = :exercicio_id
              AND company_id = :company_id
              AND ativo = 1
            LIMIT 1
        ");

        $itensValidados = [];

        foreach ($itens as $item) {

            if (!is_array($item)) {
                throw new RuntimeException(
                    'Item de treino inválido.'
                );
            }

            $exercicio_id = (int) (
                $item['exercicio_id'] ?? 0
            );

            $series = (int) (
                $item['series'] ?? 0
            );

            $repeticoes = (int) (
                $item['repeticoes'] ?? 0
            );

            if (
                $exercicio_id <= 0 ||
                $series <= 0 ||
                $repeticoes <= 0
            ) {
                throw new RuntimeException(
                    'Exercício, séries ou repetições inválidos.'
                );
            }

            $stmtExercicio->execute([
                ':exercicio_id' => $exercicio_id,
                ':company_id' => $company_id
            ]);

            if (!$stmtExercicio->fetch(PDO::FETCH_ASSOC)) {
                throw new RuntimeException(
                    'Um dos exercícios não está disponível nesta academia.'
                );
            }

            $carga = $item['carga'] ?? null;
            $intervalo = $item['intervalo'] ?? null;

            $carga = (
                $carga === '' || $carga === null
            ) ? null : $carga;

            $intervalo = (
                $intervalo === '' || $intervalo === null
            ) ? null : $intervalo;

            $itensValidados[] = [
                'exercicio_id' => $exercicio_id,
                'series' => $series,
                'repeticoes' => $repeticoes,
                'carga' => $carga,
                'intervalo' => $intervalo
            ];
        }

        // ---------------------------------------
        // Próxima versão da ficha
        // ---------------------------------------

        $stmtVersao = $pdo->prepare("
            SELECT
                COALESCE(MAX(versao), 0) + 1
            FROM fichas_treino
            WHERE aluno_id = :aluno_id
        ");

        $stmtVersao->execute([
            ':aluno_id' => $aluno_id
        ]);

        $versao = (int) (
            $stmtVersao->fetchColumn()
        );

        // ---------------------------------------
        // Criar ficha
        // ---------------------------------------

        $stmtFicha = $pdo->prepare("
            INSERT INTO fichas_treino (
                aluno_id,
                professor_id,
                objetivo,
                criada_em,
                versao
            )
            VALUES (
                :aluno_id,
                :professor_id,
                :objetivo,
                NOW(),
                :versao
            )
        ");

        $stmtFicha->execute([
            ':aluno_id' => $aluno_id,
            ':professor_id' => $professor_id,
            ':objetivo' => $objetivo,
            ':versao' => $versao
        ]);

        $ficha_id = (int) $pdo->lastInsertId();

        // ---------------------------------------
        // Inserir itens da ficha
        // ---------------------------------------

        $stmtItem = $pdo->prepare("
            INSERT INTO ficha_itens (
                ficha_id,
                exercicio_id,
                ordem,
                series,
                repeticoes,
                carga,
                intervalo
            )
            VALUES (
                :ficha_id,
                :exercicio_id,
                :ordem,
                :series,
                :repeticoes,
                :carga,
                :intervalo
            )
        ");

        foreach ($itensValidados as $indice => $item) {

            $stmtItem->execute([
                ':ficha_id' => $ficha_id,
                ':exercicio_id' => $item['exercicio_id'],
                ':ordem' => $indice + 1,
                ':series' => $item['series'],
                ':repeticoes' => $item['repeticoes'],
                ':carga' => $item['carga'],
                ':intervalo' => $item['intervalo']
            ]);
        }

        $pdo->commit();

        // Mantém o retorno esperado pelo controller.
        return $ficha_id;
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
