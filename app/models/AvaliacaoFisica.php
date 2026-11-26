<?php
// ================================================
// GYMFLOW - MODEL DE AVALIAÇÕES FÍSICAS
// app/models/AvaliacaoFisica.php
// ================================================

// ================================================
// 1. DEFINIÇÃO DOS CAMPOS
// ================================================

function camposAvaliacaoFisica(): array
{
    return [

        'peso' => [
            'rotulo' => 'Peso (kg)',
            'min' => 0.01,
            'max' => 999.99,
            'casas' => 2,
            'obrigatorio' => true
        ],

        'altura' => [
            'rotulo' => 'Altura (m)',
            'min' => 0.01,
            'max' => 9.99,
            'casas' => 2,
            'obrigatorio' => true
        ],

        'gordura' => [
            'rotulo' => 'Gordura corporal (%)',
            'min' => 0,
            'max' => 99.99,
            'casas' => 2,
            'obrigatorio' => true
        ],

        'massa_magra' => [
            'rotulo' => 'Massa magra (kg)',
            'min' => 0.01,
            'max' => 999.99,
            'casas' => 2,
            'obrigatorio' => true
        ],

        'braco' => [
            'rotulo' => 'Braço (cm)',
            'min' => 0.1,
            'max' => 999.9,
            'casas' => 1,
            'obrigatorio' => false
        ],

        'peitoral' => [
            'rotulo' => 'Peitoral (cm)',
            'min' => 0.1,
            'max' => 999.9,
            'casas' => 1,
            'obrigatorio' => false
        ],

        'abdomen' => [
            'rotulo' => 'Abdômen (cm)',
            'min' => 0.1,
            'max' => 999.9,
            'casas' => 1,
            'obrigatorio' => false
        ],

        'cintura' => [
            'rotulo' => 'Cintura (cm)',
            'min' => 0.1,
            'max' => 999.9,
            'casas' => 1,
            'obrigatorio' => false
        ],

        'quadril' => [
            'rotulo' => 'Quadril (cm)',
            'min' => 0.1,
            'max' => 999.9,
            'casas' => 1,
            'obrigatorio' => false
        ],

        'coxa' => [
            'rotulo' => 'Coxa (cm)',
            'min' => 0.1,
            'max' => 999.9,
            'casas' => 1,
            'obrigatorio' => false
        ],

        'panturrilha' => [
            'rotulo' => 'Panturrilha (cm)',
            'min' => 0.1,
            'max' => 999.9,
            'casas' => 1,
            'obrigatorio' => false
        ]
    ];
}

// ================================================
// 2. LISTAR ALUNOS DO PROFESSOR
// ================================================

function listarAlunosAvaliacaoFisica(
    PDO $pdo,
    int $professorId,
    int $empresaId
): array {

    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.nome,
            a.status,
            f.nome AS nome_filial

        FROM alunos a

        INNER JOIN filiais f
            ON f.id = a.filial_id

        INNER JOIN users u
            ON u.id = a.professor_id
            AND u.company_id = f.company_id

        INNER JOIN user_filiais uf
            ON uf.user_id = u.id
            AND uf.filial_id = f.id

        WHERE u.id = :professor_id
          AND u.role = 'Professor'
          AND f.company_id = :empresa_id

        ORDER BY a.nome ASC
    ");

    $stmt->execute([
        ':professor_id' => $professorId,
        ':empresa_id' => $empresaId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ================================================
// 3. VALIDAR DADOS DA AVALIAÇÃO
// ================================================

function validarDadosAvaliacaoFisica(
    array $post,
    string $hoje
): array {

    $data = $post['data'] ?? '';

    if (
        !is_string($data) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)
    ) {
        throw new DomainException(
            'Informe uma data válida.'
        );
    }

    $partes = explode('-', $data);

    if (
        !checkdate(
            (int) $partes[1],
            (int) $partes[2],
            (int) $partes[0]
        ) ||
        $data > $hoje ||
        $data < '1000-01-01'
    ) {
        throw new DomainException(
            'A data da avaliação é inválida.'
        );
    }

    $dados = [
        'data' => $data
    ];

    foreach (camposAvaliacaoFisica() as $campo => $config) {

        $valor = $post[$campo] ?? '';

        if (!is_string($valor)) {
            throw new DomainException(
                'Valor inválido em ' . $config['rotulo'] . '.'
            );
        }

        $valor = trim($valor);

        if ($valor === '') {

            if ($config['obrigatorio']) {

                throw new DomainException(
                    'Preencha o campo ' . $config['rotulo'] . '.'
                );
            }

            $dados[$campo] = null;

            continue;
        }

        // Aceita vírgula ou ponto decimal.

        $valor = str_replace(',', '.', $valor);

        $padrao = '/^\d+(?:\.\d{1,' .
            $config['casas'] .
            '})?$/D';

        if (!preg_match($padrao, $valor)) {

            throw new DomainException(
                'Número inválido em ' . $config['rotulo'] . '.'
            );
        }

        $numero = (float) $valor;

        if (
            $numero < $config['min'] ||
            $numero > $config['max']
        ) {

            throw new DomainException(
                'Valor fora do limite em ' .
                    $config['rotulo'] . '.'
            );
        }

        $dados[$campo] = $valor;
    }

    if (
        (float) $dados['massa_magra'] >
        (float) $dados['peso']
    ) {

        throw new DomainException(
            'A massa magra não pode ser maior que o peso total.'
        );
    }

    return $dados;
}

// ================================================
// 4. CADASTRAR AVALIAÇÃO FÍSICA
// ================================================

function cadastrarAvaliacaoFisica(
    PDO $pdo,
    int $professorId,
    int $empresaId,
    int $alunoId,
    array $dados
): void {

    /*
     * INSERT SELECT:
     *
     * A avaliação somente será inserida
     * se o aluno estiver vinculado ao professor,
     * à empresa e à filial autorizada.
     */

    $stmt = $pdo->prepare("
        INSERT INTO avaliacoes (
            aluno_id,
            data,
            peso,
            altura,
            gordura,
            massa_magra,
            braco,
            peitoral,
            abdomen,
            cintura,
            quadril,
            coxa,
            panturrilha
        )

        SELECT
            a.id,
            :data,
            :peso,
            :altura,
            :gordura,
            :massa_magra,
            :braco,
            :peitoral,
            :abdomen,
            :cintura,
            :quadril,
            :coxa,
            :panturrilha

        FROM alunos a

        INNER JOIN filiais f
            ON f.id = a.filial_id

        INNER JOIN users u
            ON u.id = a.professor_id
            AND u.company_id = f.company_id

        INNER JOIN user_filiais uf
            ON uf.user_id = u.id
            AND uf.filial_id = f.id

        WHERE a.id = :aluno_id
          AND u.id = :professor_id
          AND u.role = 'Professor'
          AND f.company_id = :empresa_id
    ");

    $stmt->execute([

        ':data' => $dados['data'],
        ':peso' => $dados['peso'],
        ':altura' => $dados['altura'],
        ':gordura' => $dados['gordura'],
        ':massa_magra' => $dados['massa_magra'],
        ':braco' => $dados['braco'],
        ':peitoral' => $dados['peitoral'],
        ':abdomen' => $dados['abdomen'],
        ':cintura' => $dados['cintura'],
        ':quadril' => $dados['quadril'],
        ':coxa' => $dados['coxa'],
        ':panturrilha' => $dados['panturrilha'],

        ':aluno_id' => $alunoId,
        ':professor_id' => $professorId,
        ':empresa_id' => $empresaId
    ]);

    if ($stmt->rowCount() !== 1) {

        throw new DomainException(
            'Você não possui autorização para avaliar este aluno.'
        );
    }
}

// ================================================
// 5. CONSULTAR HISTÓRICO
// ================================================

function listarAvaliacoesFisicasProfessor(
    PDO $pdo,
    int $professorId,
    int $empresaId,
    int $alunoId
): array {

    $stmt = $pdo->prepare("
        SELECT av.*

        FROM avaliacoes av

        INNER JOIN alunos a
            ON a.id = av.aluno_id

        INNER JOIN filiais f
            ON f.id = a.filial_id

        INNER JOIN users u
            ON u.id = a.professor_id
            AND u.company_id = f.company_id

        INNER JOIN user_filiais uf
            ON uf.user_id = u.id
            AND uf.filial_id = f.id

        WHERE av.aluno_id = :aluno_id
          AND u.id = :professor_id
          AND u.role = 'Professor'
          AND f.company_id = :empresa_id

        ORDER BY av.data DESC, av.id DESC
    ");

    $stmt->execute([
        ':aluno_id' => $alunoId,
        ':professor_id' => $professorId,
        ':empresa_id' => $empresaId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
