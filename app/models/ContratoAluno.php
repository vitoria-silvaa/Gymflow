<?php

// ============================================
// GYMFLOW - MODEL DE CONTRATOS DO ALUNO
// app/models/ContratoAluno.php
// ============================================

function consultarContratosPortal(
    PDO $pdo,
    int $usuarioId,
    int $alunoId,
    int $empresaId,
    ?int $contratoId = null
): array {

    $sql = "
        SELECT
            m.id,
            m.aluno_id,
            m.inicio,
            m.fim,
            m.valor,
            m.desconto,
            m.ativa,

            a.nome AS nome_aluno,
            a.cpf AS cpf_aluno,
            a.telefone AS tel_aluno,

            p.nome AS nome_plano,
            p.categoria,
            p.duracao,

            f.nome AS nome_filial,
            f.cnpj AS cnpj_filial,

            CONCAT_WS(
                ', ',
                NULLIF(TRIM(f.endereco), ''),
                NULLIF(TRIM(f.numero), ''),
                NULLIF(TRIM(f.complemento), '')
            ) AS end_filial

        FROM matriculas m

        INNER JOIN alunos a
            ON a.id = m.aluno_id

        INNER JOIN filiais f
            ON f.id = a.filial_id

        INNER JOIN planos p
            ON p.id = m.plano_id
            AND p.company_id = f.company_id

        INNER JOIN users u
            ON u.aluno_id = a.id
            AND u.company_id = f.company_id

        WHERE u.id = :usuario_id
          AND u.role = 'Aluno'
          AND a.id = :aluno_id
          AND f.company_id = :empresa_id
    ";

    $parametros = [

        ':usuario_id' => $usuarioId,

        ':aluno_id' => $alunoId,

        ':empresa_id' => $empresaId
    ];

    if ($contratoId !== null) {

        $sql .= " AND m.id = :contrato_id ";

        $parametros[':contrato_id'] = $contratoId;
    }

    $sql .= " ORDER BY m.inicio DESC, m.id DESC ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute($parametros);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================
// CLÁUSULAS ORIGINAIS DO CONTRATO
// ============================================

function obterClausulasContratoAluno(): array
{
    return [

        'Cláusula 1: O CONTRATANTE terá direito a utilizar as dependências da CONTRATADA durante o horário de funcionamento, respeitando as regras internas e os limites do plano escolhido.',

        'Cláusula 2: É obrigatória a apresentação de atestado médico confirmando que o CONTRATANTE está apto à prática de atividades físicas, isentando a CONTRATADA de responsabilidades sobre lesões preexistentes.',

        'Cláusula 3: Em caso de atraso no pagamento da mensalidade, será cobrada multa de 2% sobre o valor da parcela, acrescida de juros de mora de 1% ao mês.',

        'Cláusula 4: O presente contrato poderá ser rescindido por qualquer uma das partes, mediante aviso prévio de 30 dias. Em caso de planos com fidelidade, será cobrada multa de 20% sobre o saldo remanescente.',

        'Cláusula 5: O acesso às instalações é pessoal e intransferível, sendo estritamente proibido o compartilhamento de senhas, biometria ou cartões de acesso.'
    ];
}
