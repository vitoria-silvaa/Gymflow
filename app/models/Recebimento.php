<?php
// app/models/Recebimento.php

require_once __DIR__ . '/CaixaFisico.php';

final class RecebimentoErro extends DomainException {}

final class RecebimentoModel
{
    public function __construct(
        private PDO $pdo,
        private int $empresa,
        private int $usuario
    ) {
        $this->autorizar();
    }

    private function executar(
        string $sql,
        array $params = []
    ): PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    private function autorizar(bool $lock = false): void
    {
        if (
            ($_SESSION['usuario_role'] ?? '') !== 'Recepcao' ||
            $this->empresa <= 0 ||
            $this->usuario <= 0 ||
            (int) ($_SESSION['company_id'] ?? 0) !== $this->empresa ||
            (int) ($_SESSION['usuario_id'] ?? 0) !== $this->usuario
        ) {
            throw new RecebimentoErro('Acesso não autorizado.');
        }

        $id = $this->executar(
            "SELECT id FROM users
             WHERE id = ?
               AND company_id = ?
               AND role = 'Recepcao'" .
            ($lock ? ' FOR UPDATE' : ''),
            [$this->usuario, $this->empresa]
        )->fetchColumn();

        if (!$id) {
            throw new RecebimentoErro(
                'Sua sessão não está autorizada.'
            );
        }
    }

    public function buscarAluno(
        string $tipo,
        string $busca
    ): array {

        $n = preg_match_all('/./us', $busca);

        if ($n === false || $n === 0 || $n > 100) {
            throw new RecebimentoErro(
                'Informe os dados do aluno corretamente.'
            );
        }

        $sql = '
            SELECT a.id, a.nome
            FROM alunos a
            INNER JOIN filiais f
                ON f.id = a.filial_id
            INNER JOIN user_filiais uf
                ON uf.filial_id = f.id
            WHERE uf.user_id = ?
              AND f.company_id = ?
        ';

        $params = [$this->usuario, $this->empresa];

        $matricula = 0;

        if ($tipo === 'cpf') {

            $cpf = preg_replace('/\D/', '', $busca);

            if (
                !preg_match('/^[0-9. -]+$/', $busca) ||
                strlen($cpf) !== 11
            ) {
                throw new RecebimentoErro(
                    'Informe um CPF com 11 dígitos.'
                );
            }

            $sql .= "
                AND REPLACE(
                    REPLACE(
                        REPLACE(a.cpf, '.', ''),
                        '-', ''
                    ),
                    ' ', ''
                ) = ?
            ";

            $params[] = $cpf;

        } elseif ($tipo === 'matricula') {

            $matricula = (int) filter_var(
                ltrim($busca, '#'),
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if (!$matricula) {
                throw new RecebimentoErro(
                    'Informe um número de matrícula válido.'
                );
            }

            $sql .= '
                AND EXISTS (
                    SELECT 1
                    FROM matriculas m
                    INNER JOIN planos p
                        ON p.id = m.plano_id
                        AND p.company_id = f.company_id
                    INNER JOIN filiais fm
                        ON fm.id = COALESCE(m.filial_id, a.filial_id)
                        AND fm.company_id = f.company_id
                    INNER JOIN user_filiais um
                        ON um.filial_id = fm.id
                        AND um.user_id = uf.user_id
                    WHERE m.id = ?
                      AND m.aluno_id = a.id
                )
            ';

            $params[] = $matricula;

        } elseif ($tipo === 'nome') {

            $sql .= ' AND a.nome = ?';

            $params[] = $busca;

        } else {

            throw new RecebimentoErro(
                'Selecione uma forma de pesquisa válida.'
            );
        }

        $alunos = $this->executar(
            $sql . ' LIMIT 2',
            $params
        )->fetchAll(PDO::FETCH_ASSOC);

        if (!$alunos) {
            throw new RecebimentoErro(
                'Aluno não encontrado nas unidades autorizadas.'
            );
        }

        if (count($alunos) > 1) {
            throw new RecebimentoErro(
                'Mais de um aluno possui esses dados. Pesquise pelo CPF.'
            );
        }

        return [
            'aluno_id' => (int) $alunos[0]['id'],
            'matricula_id' => $matricula
        ];
    }

    public function aluno(int $id): ?array
    {
        return $this->executar(
            'SELECT
                a.id,
                a.nome,
                f.nome AS filial_nome
             FROM alunos a
             INNER JOIN filiais f
                ON f.id = a.filial_id
             INNER JOIN user_filiais uf
                ON uf.filial_id = f.id
             WHERE a.id = ?
               AND f.company_id = ?
               AND uf.user_id = ?',
            [$id, $this->empresa, $this->usuario]
        )->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function baseContas(): string
    {
        return '
            FROM contas c

            INNER JOIN alunos a
                ON a.id = c.aluno_id

            INNER JOIN matriculas m
                ON m.id = c.matricula_id
                AND m.aluno_id = a.id

            INNER JOIN filiais f
                ON f.id = COALESCE(m.filial_id, a.filial_id)

            INNER JOIN filiais fa
                ON fa.id = a.filial_id
                AND fa.company_id = f.company_id

            INNER JOIN planos p
                ON p.id = m.plano_id
                AND p.company_id = f.company_id

            INNER JOIN user_filiais uf
                ON uf.filial_id = f.id
                AND uf.user_id = ?

            INNER JOIN user_filiais ua
                ON ua.filial_id = fa.id
                AND ua.user_id = ?

            WHERE f.company_id = ?
              AND a.id = ?
        ';
    }

    private function camposContas(): string
    {
        return '
            SELECT
                c.id,
                c.aluno_id,
                c.matricula_id,
                c.valor,
                c.vencimento,
                c.status,
                c.pago_em,

                a.nome AS aluno_nome,
                a.status AS aluno_status,
                a.filial_id AS aluno_filial_id,

                f.id AS unidade_id,
                f.nome AS filial_nome,

                p.nome AS plano_nome,

                m.origem,
                m.status_matricula,
                m.ativa,
                m.inicio,
                m.fim,
                m.lead_id,

                CURRENT_DATE() AS hoje
        ';
    }

    private function parametros(int $aluno): array
    {
        return [
            $this->usuario,
            $this->usuario,
            $this->empresa,
            $aluno
        ];
    }

    public function listarContas(
        int $aluno,
        int $matricula = 0
    ): array {

        $sql = $this->camposContas() .
            $this->baseContas() .
            "
                AND c.status IN (
                    'Aberto', 'Vencido', 'Atrasado'
                )
                AND c.pago_em IS NULL
            ";

        $params = $this->parametros($aluno);

        if ($matricula > 0) {
            $sql .= ' AND m.id = ?';
            $params[] = $matricula;
        }

        return $this->executar(
            $sql . ' ORDER BY c.vencimento, c.id LIMIT 100',
            $params
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    private function prepararConta(
        int $aluno,
        int $matricula,
        int $contaId,
        bool $bloquear = false
    ): array {

        $lock = $bloquear ? ' FOR UPDATE' : '';

        $sql = $this->camposContas() .
            $this->baseContas() .
            ' AND c.id = ?';

        $params = $this->parametros($aluno);
        $params[] = $contaId;

        if ($matricula > 0) {
            $sql .= ' AND m.id = ?';
            $params[] = $matricula;
        }

        $conta = $this->executar(
            $sql . $lock,
            $params
        )->fetch(PDO::FETCH_ASSOC);

        if (!$conta) {
            throw new RecebimentoErro(
                'Cobrança não encontrada ou não autorizada.'
            );
        }

        if (
            !in_array(
                $conta['status'],
                ['Aberto', 'Vencido', 'Atrasado'],
                true
            ) ||
            $conta['pago_em'] !== null ||
            CaixaFisicoModel::centavos(
                (string) $conta['valor']
            ) <= 0
        ) {
            throw new RecebimentoErro(
                'Esta cobrança não está disponível para recebimento.'
            );
        }

        $primeiraConta = (int) $this->executar(
            'SELECT id FROM contas
             WHERE matricula_id = ?
               AND aluno_id = ?
             ORDER BY vencimento, id
             LIMIT 1' . $lock,
            [$conta['matricula_id'], $conta['aluno_id']]
        )->fetchColumn();

        $ativar = $conta['status_matricula'] === 'pendente';

        $leadId = (int) ($conta['lead_id'] ?? 0);

        if ($ativar) {

            if ($primeiraConta !== $contaId) {
                throw new RecebimentoErro(
                    'Confirme primeiro a cobrança inicial da matrícula.'
                );
            }

            if (
                (int) $conta['ativa'] !== 0 ||
                (int) $conta['aluno_filial_id'] !==
                    (int) $conta['unidade_id'] ||
                !in_array(
                    $conta['aluno_status'],
                    ['Ativo', 'Inativo'],
                    true
                ) ||
                $conta['fim'] < $conta['hoje'] ||
                $conta['inicio'] > $conta['hoje']
            ) {
                throw new RecebimentoErro(
                    'A situação da matrícula precisa ser revisada.'
                );
            }

            if ($leadId > 0 || $conta['origem'] === 'crm') {

                if ($leadId <= 0 || $conta['origem'] !== 'crm') {
                    throw new RecebimentoErro(
                        'O vínculo com o CRM precisa ser conferido.'
                    );
                }

                $lead = $this->executar(
                    "SELECT l.id
                     FROM leads l
                     INNER JOIN filiais f
                        ON f.id = l.filial_id
                     WHERE l.id = ?
                       AND l.filial_id = ?
                       AND f.company_id = ?
                       AND l.status = 'Aguardando pagamento'" .
                    $lock,
                    [
                        $leadId,
                        $conta['unidade_id'],
                        $this->empresa
                    ]
                )->fetchColumn();

                if (!$lead) {
                    throw new RecebimentoErro(
                        'O vínculo com o CRM precisa ser conferido.'
                    );
                }
            }

        } elseif (
            (int) $conta['ativa'] !== 1 ||
            !in_array(
                $conta['status_matricula'],
                [null, 'ativa'],
                true
            )
        ) {
            throw new RecebimentoErro(
                'A matrícula não está disponível para recebimento.'
            );
        }

        $pg = $this->executar(
            'SELECT id, matricula_id, valor, metodo, status
             FROM pagamentos
             WHERE conta_id = ?' . $lock,
            [$contaId]
        )->fetch(PDO::FETCH_ASSOC) ?: null;

        if (
            $pg &&
            (
                (int) $pg['matricula_id'] !==
                    (int) $conta['matricula_id'] ||
                CaixaFisicoModel::centavos((string) $pg['valor']) !==
                    CaixaFisicoModel::centavos((string) $conta['valor']) ||
                $pg['status'] !== 'pendente'
            )
        ) {
            throw new RecebimentoErro(
                'Existe uma inconsistência nesta cobrança.'
            );
        }

        $antigo = $this->executar(
            "SELECT id FROM pagamentos
             WHERE matricula_id = ?
               AND conta_id IS NULL
               AND status IN ('pendente', 'aprovado')
             LIMIT 1" . $lock,
            [$conta['matricula_id']]
        )->fetchColumn();

        if ($antigo) {
            throw new RecebimentoErro(
                'Existe um pagamento antigo sem vínculo. Solicite a conferência do administrador.'
            );
        }

        $metodos = [
            'pix' => 'Pix',
            'debito' => 'Cartão de Débito',
            'credito' => 'Cartão de Crédito',
            'dinheiro' => 'Dinheiro'
        ];

        if (
            $conta['origem'] === 'portfolio' &&
            $primeiraConta === $contaId
        ) {
            $metodos = ['pix' => 'Pix'];
        }

        $preferido = $pg['metodo'] ?? '';

        if (isset($metodos[$preferido])) {
            $metodos = [
                $preferido => $metodos[$preferido]
            ] + $metodos;
        }

        $conta['metodos'] = $metodos;
        $conta['ativar'] = $ativar;
        $conta['pagamento_id'] = (int) ($pg['id'] ?? 0);

        $conta['caixa_aberto'] = false;

        if (isset($metodos['dinheiro'])) {

            $caixa = new CaixaFisicoModel(
                $this->pdo,
                $this->empresa,
                $this->usuario,
                'Recepcao'
            );

            $conta['caixa_aberto'] = (bool) $caixa->caixaAberto(
                (int) $conta['unidade_id'],
                $bloquear
            );
        }

        return $conta;
    }

    public function consultarConta(
        int $aluno,
        int $matricula,
        int $conta
    ): array {

        return $this->prepararConta(
            $aluno,
            $matricula,
            $conta
        );
    }

    public function confirmar(
        int $aluno,
        int $matricula,
        int $contaId,
        string $metodo,
        string $referencia
    ): void {

        $referencia = trim($referencia);

        $tamanho = preg_match_all('/./us', $referencia);

        if ($tamanho === false || $tamanho > 100) {
            throw new RecebimentoErro(
                'A referência deve ter até 100 caracteres.'
            );
        }

        if ($metodo === 'pix' && $referencia === '') {
            throw new RecebimentoErro(
                'Informe a referência da transação Pix.'
            );
        }

        if ($metodo === 'dinheiro') {
            $referencia = '';
        }

        $this->pdo->beginTransaction();

        try {

            $empresa = $this->executar(
                'SELECT id FROM companies
                 WHERE id = ? FOR UPDATE',
                [$this->empresa]
            )->fetchColumn();

            if (!$empresa) {
                throw new RecebimentoErro(
                    'Empresa indisponível.'
                );
            }

            $this->autorizar(true);

            $conta = $this->prepararConta(
                $aluno,
                $matricula,
                $contaId,
                true
            );

            if (!isset($conta['metodos'][$metodo])) {
                throw new RecebimentoErro(
                    'Forma de pagamento não autorizada.'
                );
            }

            if (
                $metodo === 'dinheiro' &&
                !$conta['caixa_aberto']
            ) {
                throw new RecebimentoErro(
                    'Abra o caixa da unidade antes de receber em dinheiro.'
                );
            }

            if ($metodo === 'pix') {

                $duplicado = $this->executar(
                    "SELECT pg.id
                     FROM pagamentos pg
                     INNER JOIN matriculas m
                        ON m.id = pg.matricula_id
                     INNER JOIN planos p
                        ON p.id = m.plano_id
                     WHERE p.company_id = ?
                       AND pg.metodo = 'pix'
                       AND pg.status = 'aprovado'
                       AND pg.referencia = ?
                       AND pg.id <> ?
                     LIMIT 1",
                    [
                        $this->empresa,
                        $referencia,
                        $conta['pagamento_id']
                    ]
                )->fetchColumn();

                if ($duplicado) {
                    throw new RecebimentoErro(
                        'Esta referência Pix já foi utilizada.'
                    );
                }
            }

            $agora = $this->executar(
                'SELECT CURRENT_TIMESTAMP'
            )->fetchColumn();

            $referenciaBanco = $referencia !== ''
                ? $referencia
                : null;

            if ($conta['pagamento_id'] > 0) {

                $alterou = $this->executar(
                    "UPDATE pagamentos
                     SET
                        metodo = ?,
                        status = 'aprovado',
                        recebido_por = ?,
                        confirmado_em = ?,
                        referencia = ?
                     WHERE id = ?
                       AND status = 'pendente'",
                    [
                        $metodo,
                        $this->usuario,
                        $agora,
                        $referenciaBanco,
                        $conta['pagamento_id']
                    ]
                )->rowCount();

                if ($alterou !== 1) {
                    throw new RecebimentoErro(
                        'O pagamento já foi processado.'
                    );
                }

            } else {

                $this->executar(
                    "INSERT INTO pagamentos (
                        matricula_id,
                        conta_id,
                        valor,
                        metodo,
                        status,
                        recebido_por,
                        confirmado_em,
                        referencia
                    ) VALUES (?, ?, ?, ?, 'aprovado', ?, ?, ?)",
                    [
                        $conta['matricula_id'],
                        $contaId,
                        $conta['valor'],
                        $metodo,
                        $this->usuario,
                        $agora,
                        $referenciaBanco
                    ]
                );
            }

            $alterou = $this->executar(
                "UPDATE contas
                 SET
                    status = 'Pago',
                    forma_pagamento = ?,
                    pago_em = ?
                 WHERE id = ?
                   AND status IN ('Aberto', 'Vencido', 'Atrasado')
                   AND pago_em IS NULL",
                [
                    $conta['metodos'][$metodo],
                    $agora,
                    $contaId
                ]
            )->rowCount();

            if ($alterou !== 1) {
                throw new RecebimentoErro(
                    'A cobrança já foi processada.'
                );
            }

            // Registrar a entrada de dinheiro no caixa.
            // A mesma transação protege as duas operações.

            if ($metodo === 'dinheiro') {

                $caixa = new CaixaFisicoModel(
                    $this->pdo,
                    $this->empresa,
                    $this->usuario,
                    'Recepcao'
                );

                $caixa->registrarRecebimento(
                    (int) $conta['unidade_id'],
                    $contaId
                );
            }

            if ($conta['ativar']) {

                $alterou = $this->executar(
                    "UPDATE matriculas
                     SET ativa = 1,
                         status_matricula = 'ativa'
                     WHERE id = ?
                       AND ativa = 0
                       AND status_matricula = 'pendente'",
                    [$conta['matricula_id']]
                )->rowCount();

                if ($alterou !== 1) {
                    throw new RecebimentoErro(
                        'A situação da matrícula foi alterada.'
                    );
                }

                $this->executar(
                    "UPDATE alunos SET status = 'Ativo'
                     WHERE id = ?
                       AND status = 'Inativo'",
                    [$aluno]
                );

                if (
                    $conta['origem'] === 'crm' &&
                    (int) ($conta['lead_id'] ?? 0) > 0
                ) {

                    $alterou = $this->executar(
                        "UPDATE leads SET status = 'Convertido'
                         WHERE id = ?
                           AND filial_id = ?
                           AND status = 'Aguardando pagamento'",
                        [$conta['lead_id'], $conta['unidade_id']]
                    )->rowCount();

                    if ($alterou !== 1) {
                        throw new RecebimentoErro(
                            'Não foi possível confirmar a conversão do lead.'
                        );
                    }
                }
            }

            $this->pdo->commit();

        } catch (Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if ($e instanceof CaixaFisicoErro) {
                throw new RecebimentoErro($e->getMessage());
            }

            throw $e;
        }
    }
}