<?php
// app/models/CaixaFisico.php

final class CaixaFisicoErro extends DomainException {}

final class CaixaFisicoModel
{
    public function __construct(
        private PDO $pdo,
        private int $empresa,
        private int $usuario,
        private string $role
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
            $this->empresa <= 0 ||
            $this->usuario <= 0 ||
            !in_array($this->role, ['Admin', 'Recepcao'], true) ||
            (int) ($_SESSION['company_id'] ?? 0) !== $this->empresa ||
            (int) ($_SESSION['usuario_id'] ?? 0) !== $this->usuario ||
            ($_SESSION['usuario_role'] ?? '') !== $this->role
        ) {
            throw new CaixaFisicoErro('Acesso não autorizado.');
        }

        $id = $this->executar(
            'SELECT id FROM users
             WHERE id = ?
               AND company_id = ?
               AND role = ?' .
            ($lock ? ' FOR UPDATE' : ''),
            [$this->usuario, $this->empresa, $this->role]
        )->fetchColumn();

        if (!$id) {
            throw new CaixaFisicoErro('Sessão não autorizada.');
        }
    }

    private function exigirRecepcao(): void
    {
        if ($this->role !== 'Recepcao') {
            throw new CaixaFisicoErro(
                'Esta operação é exclusiva da recepção.'
            );
        }
    }

    public static function centavos($valor): int
    {
        if (!is_string($valor) && !is_int($valor)) {
            throw new CaixaFisicoErro('Informe um valor válido.');
        }

        $valor = trim((string) $valor);

        if (!preg_match(
            '/^(\d{1,8})(?:[.,](\d{1,2}))?$/',
            $valor,
            $partes
        )) {
            throw new CaixaFisicoErro(
                'Informe um valor válido, com até duas casas decimais.'
            );
        }

        return (int) $partes[1] * 100 +
            (int) str_pad($partes[2] ?? '', 2, '0');
    }

    public static function decimal(int $centavos): string
    {
        $sinal = $centavos < 0 ? '-' : '';

        $centavos = abs($centavos);

        return $sinal .
            intdiv($centavos, 100) . '.' .
            str_pad(
                (string) ($centavos % 100),
                2,
                '0',
                STR_PAD_LEFT
            );
    }

    private static function validarDescricao(
        string $descricao
    ): string {
        $descricao = trim($descricao);

        $tamanho = preg_match_all('/./us', $descricao);

        if (
            $descricao === '' ||
            $tamanho === false ||
            $tamanho > 255
        ) {
            throw new CaixaFisicoErro(
                'Informe uma justificativa com até 255 caracteres.'
            );
        }

        return $descricao;
    }

    public function filiais(): array
    {
        $sql = '
            SELECT f.id, f.nome, f.ativo
            FROM filiais f
            WHERE f.company_id = ?
        ';

        $params = [$this->empresa];

        if ($this->role === 'Recepcao') {
            $sql .= '
                AND EXISTS (
                    SELECT 1
                    FROM user_filiais uf
                    WHERE uf.filial_id = f.id
                      AND uf.user_id = ?
                )
            ';

            $params[] = $this->usuario;
        }

        return $this->executar(
            $sql . ' ORDER BY f.nome',
            $params
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    private function filial(
        int $id,
        bool $lock = false
    ): array {
        $filial = $this->executar(
            'SELECT id, nome, ativo
             FROM filiais
             WHERE id = ?
               AND company_id = ?' .
            ($lock ? ' FOR UPDATE' : ''),
            [$id, $this->empresa]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$filial) {
            throw new CaixaFisicoErro('Filial não autorizada.');
        }

        if ($this->role === 'Recepcao') {
            $vinculo = $this->executar(
                'SELECT filial_id
                 FROM user_filiais
                 WHERE user_id = ?
                   AND filial_id = ?' .
                ($lock ? ' FOR UPDATE' : ''),
                [$this->usuario, $id]
            )->fetchColumn();

            if (!$vinculo) {
                throw new CaixaFisicoErro(
                    'Você não possui acesso a esta unidade.'
                );
            }
        }

        return $filial;
    }

    private function transacao(callable $operacao)
    {
        $this->pdo->beginTransaction();

        try {
            $empresa = $this->executar(
                'SELECT id FROM companies
                 WHERE id = ? FOR UPDATE',
                [$this->empresa]
            )->fetchColumn();

            if (!$empresa) {
                throw new CaixaFisicoErro(
                    'Empresa indisponível.'
                );
            }

            $this->autorizar(true);

            $resultado = $operacao();

            $this->pdo->commit();

            return $resultado;

        } catch (Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function caixaAberto(
        int $filial,
        bool $lock = false
    ): ?array {

        $this->filial($filial, $lock);

        return $this->executar(
            'SELECT *
             FROM caixas_fisicos
             WHERE filial_id = ?
               AND fechado_em IS NULL
             LIMIT 1' .
            ($lock ? ' FOR UPDATE' : ''),
            [$filial]
        )->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function obterCaixa(
        int $id,
        bool $lock = false
    ): array {

        $caixa = $this->executar(
            'SELECT
                c.*,
                f.nome AS filial_nome,
                ua.name AS abertura_nome,
                uf.name AS fechamento_nome,
                uc.name AS conferencia_nome

             FROM caixas_fisicos c

             INNER JOIN filiais f
                ON f.id = c.filial_id

             INNER JOIN users ua
                ON ua.id = c.aberto_por

             LEFT JOIN users uf
                ON uf.id = c.fechado_por

             LEFT JOIN users uc
                ON uc.id = c.conferido_por

             WHERE c.id = ?
               AND f.company_id = ?' .
            ($lock ? ' FOR UPDATE' : ''),
            [$id, $this->empresa]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$caixa) {
            throw new CaixaFisicoErro('Caixa não encontrado.');
        }

        $this->filial(
            (int) $caixa['filial_id'],
            $lock
        );

        return $caixa;
    }

    private function calcularSaldo(array $caixa): array
    {
        $totais = $this->executar(
            "SELECT
                COALESCE(SUM(
                    CASE WHEN tipo = 'recebimento'
                    THEN valor ELSE 0 END
                ), 0) AS recebimentos,

                COALESCE(SUM(
                    CASE WHEN tipo = 'sangria'
                    THEN valor ELSE 0 END
                ), 0) AS sangrias

             FROM caixa_movimentos
             WHERE caixa_id = ?",
            [$caixa['id']]
        )->fetch(PDO::FETCH_ASSOC);

        // Somar em centavos, evitando imprecisão monetária.

        $inicial = self::centavos(
            (string) $caixa['saldo_inicial']
        );

        $recebimentos = self::centavos(
            (string) $totais['recebimentos']
        );

        $sangrias = self::centavos(
            (string) $totais['sangrias']
        );

        return [
            'inicial' => $inicial,
            'recebimentos' => $recebimentos,
            'sangrias' => $sangrias,
            'esperado' => $inicial + $recebimentos - $sangrias
        ];
    }

    public function detalhes(int $id): array
    {
        $caixa = $this->obterCaixa($id);

        // A recepção não consulta o histórico de caixas encerrados.

        if (
            $this->role === 'Recepcao' &&
            $caixa['fechado_em'] !== null
        ) {
            throw new CaixaFisicoErro(
                'O histórico de fechamentos é administrativo.'
            );
        }

        $caixa['totais'] = $this->calcularSaldo($caixa);

        $caixa['movimentos'] = $this->executar(
            'SELECT
                m.*,
                u.name AS responsavel
             FROM caixa_movimentos m
             INNER JOIN users u
                ON u.id = m.usuario_id
             WHERE m.caixa_id = ?
             ORDER BY m.criado_em DESC, m.id DESC',
            [$id]
        )->fetchAll(PDO::FETCH_ASSOC);

        return $caixa;
    }

    public function abrir(
        int $filialId,
        string $saldoInicial
    ): int {

        $this->exigirRecepcao();

        $valor = self::centavos($saldoInicial);

        return $this->transacao(function () use (
            $filialId,
            $valor
        ) {

            $filial = $this->filial($filialId, true);

            if ((int) $filial['ativo'] !== 1) {
                throw new CaixaFisicoErro(
                    'A unidade está inativa.'
                );
            }

            if ($this->caixaAberto($filialId, true)) {
                throw new CaixaFisicoErro(
                    'Já existe um caixa aberto nesta unidade.'
                );
            }

            $this->executar(
                'INSERT INTO caixas_fisicos (
                    filial_id,
                    aberto_por,
                    saldo_inicial
                ) VALUES (?, ?, ?)',
                [
                    $filialId,
                    $this->usuario,
                    self::decimal($valor)
                ]
            );

            return (int) $this->pdo->lastInsertId();
        });
    }

    public function sangria(
        int $caixaId,
        string $valor,
        string $justificativa
    ): void {

        $this->exigirRecepcao();

        $centavos = self::centavos($valor);

        $justificativa = self::validarDescricao(
            $justificativa
        );

        if ($centavos <= 0) {
            throw new CaixaFisicoErro(
                'O valor da sangria deve ser maior que zero.'
            );
        }

        $this->transacao(function () use (
            $caixaId,
            $centavos,
            $justificativa
        ) {

            $caixa = $this->obterCaixa($caixaId, true);

            if ($caixa['fechado_em'] !== null) {
                throw new CaixaFisicoErro(
                    'Este caixa já foi fechado.'
                );
            }

            $totais = $this->calcularSaldo($caixa);

            if (
                $centavos > $totais['esperado'] ||
                $totais['sangrias'] + $centavos > 9999999999
            ) {
                throw new CaixaFisicoErro(
                    'O valor da sangria excede o limite permitido.'
                );
            }

            $this->executar(
                "INSERT INTO caixa_movimentos (
                    caixa_id,
                    usuario_id,
                    tipo,
                    valor,
                    descricao
                ) VALUES (?, ?, 'sangria', ?, ?)",
                [
                    $caixaId,
                    $this->usuario,
                    self::decimal($centavos),
                    $justificativa
                ]
            );
        });
    }

    public function fechar(
        int $caixaId,
        string $saldoContado
    ): array {

        $this->exigirRecepcao();

        $contado = self::centavos($saldoContado);

        return $this->transacao(function () use (
            $caixaId,
            $contado
        ) {

            $caixa = $this->obterCaixa($caixaId, true);

            if ($caixa['fechado_em'] !== null) {
                throw new CaixaFisicoErro(
                    'Este caixa já foi fechado.'
                );
            }

            $totais = $this->calcularSaldo($caixa);

            $esperado = $totais['esperado'];

            $diferenca = $contado - $esperado;

            $alterou = $this->executar(
                'UPDATE caixas_fisicos
                 SET
                    fechado_por = ?,
                    fechado_em = CURRENT_TIMESTAMP,
                    saldo_esperado = ?,
                    saldo_contado = ?,
                    diferenca = ?
                 WHERE id = ?
                   AND fechado_em IS NULL',
                [
                    $this->usuario,
                    self::decimal($esperado),
                    self::decimal($contado),
                    self::decimal($diferenca),
                    $caixaId
                ]
            )->rowCount();

            if ($alterou !== 1) {
                throw new CaixaFisicoErro(
                    'O caixa já foi processado.'
                );
            }

            return [
                'esperado' => $esperado,
                'contado' => $contado,
                'diferenca' => $diferenca
            ];
        });
    }

    // Chamada exclusiva pelo recebimento de dinheiro.
    // Não inicia nem confirma outra transação.

    public function registrarRecebimento(
        int $filialId,
        int $contaId
    ): void {

        $this->exigirRecepcao();

        if (!$this->pdo->inTransaction()) {
            throw new CaixaFisicoErro(
                'Recebimento fora da transação autorizada.'
            );
        }

        $this->autorizar(true);

        $caixa = $this->caixaAberto(
            $filialId,
            true
        );

        if (!$caixa) {
            throw new CaixaFisicoErro(
                'Abra o caixa da unidade antes de receber em dinheiro.'
            );
        }

        $conta = $this->executar(
            "SELECT
                c.id,
                c.valor,
                a.nome AS aluno_nome

             FROM contas c

             INNER JOIN alunos a
                ON a.id = c.aluno_id

             INNER JOIN matriculas m
                ON m.id = c.matricula_id
                AND m.aluno_id = a.id

             INNER JOIN filiais f
                ON f.id = COALESCE(
                    m.filial_id,
                    a.filial_id
                )

             INNER JOIN filiais fa
                ON fa.id = a.filial_id
                AND fa.company_id = f.company_id

             INNER JOIN planos pl
                ON pl.id = m.plano_id
                AND pl.company_id = f.company_id

             INNER JOIN pagamentos p
                ON p.conta_id = c.id
                AND p.matricula_id = m.id
                AND p.valor = c.valor

             WHERE c.id = ?
               AND f.id = ?
               AND f.company_id = ?
               AND c.status = 'Pago'
               AND c.forma_pagamento = 'Dinheiro'
               AND c.pago_em IS NOT NULL
               AND p.status = 'aprovado'
               AND p.metodo = 'dinheiro'
               AND p.recebido_por = ?

             FOR UPDATE",
            [
                $contaId,
                $filialId,
                $this->empresa,
                $this->usuario
            ]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$conta) {
            throw new CaixaFisicoErro(
                'O recebimento em dinheiro não foi autorizado.'
            );
        }

        $valor = self::centavos(
            (string) $conta['valor']
        );

        $totais = $this->calcularSaldo($caixa);

        if (
            $valor <= 0 ||
            $totais['recebimentos'] + $valor > 9999999999 ||
            $totais['esperado'] + $valor > 9999999999
        ) {
            throw new CaixaFisicoErro(
                'O valor excede o limite permitido para este caixa.'
            );
        }

        $this->executar(
            "INSERT INTO caixa_movimentos (
                caixa_id,
                conta_id,
                usuario_id,
                tipo,
                valor,
                descricao
            ) VALUES (?, ?, ?, 'recebimento', ?, ?)",
            [
                $caixa['id'],
                $contaId,
                $this->usuario,
                self::decimal($valor),
                'Mensalidade - ' . $conta['aluno_nome']
            ]
        );
    }

    // Histórico exclusivo do administrador.

    public function historico(
        int $filialId = 0,
        string $inicio = '',
        string $fim = ''
    ): array {

        if ($this->role !== 'Admin') {
            throw new CaixaFisicoErro(
                'Histórico exclusivamente administrativo.'
            );
        }

        $sql = '
            SELECT
                c.*,
                f.nome AS filial_nome,
                ua.name AS abertura_nome,
                uf.name AS fechamento_nome,
                uc.name AS conferencia_nome

            FROM caixas_fisicos c

            INNER JOIN filiais f
                ON f.id = c.filial_id

            INNER JOIN users ua
                ON ua.id = c.aberto_por

            LEFT JOIN users uf
                ON uf.id = c.fechado_por

            LEFT JOIN users uc
                ON uc.id = c.conferido_por

            WHERE f.company_id = ?
        ';

        $params = [$this->empresa];

        if ($filialId > 0) {
            $this->filial($filialId);

            $sql .= ' AND f.id = ?';

            $params[] = $filialId;
        }

        if ($inicio !== '') {
            $sql .= ' AND c.aberto_em >= ?';

            $params[] = $inicio;
        }

        if ($fim !== '') {
            $sql .= ' AND c.aberto_em < DATE_ADD(?, INTERVAL 1 DAY)';

            $params[] = $fim;
        }

        return $this->executar(
            $sql . ' ORDER BY c.aberto_em DESC, c.id DESC LIMIT 200',
            $params
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function conferir(
        int $caixaId,
        string $observacao
    ): void {

        if ($this->role !== 'Admin') {
            throw new CaixaFisicoErro(
                'Somente o administrador pode conferir o caixa.'
            );
        }

        $observacao = self::validarDescricao(
            $observacao
        );

        $this->transacao(function () use (
            $caixaId,
            $observacao
        ) {

            $caixa = $this->obterCaixa($caixaId, true);

            if ($caixa['fechado_em'] === null) {
                throw new CaixaFisicoErro(
                    'O caixa precisa estar fechado.'
                );
            }

            if ($caixa['conferido_em'] !== null) {
                throw new CaixaFisicoErro(
                    'Este fechamento já foi conferido.'
                );
            }

            $alterou = $this->executar(
                'UPDATE caixas_fisicos
                 SET
                    conferido_por = ?,
                    conferido_em = CURRENT_TIMESTAMP,
                    observacao_conferencia = ?
                 WHERE id = ?
                   AND fechado_em IS NOT NULL
                   AND conferido_em IS NULL',
                [
                    $this->usuario,
                    $observacao,
                    $caixaId
                ]
            )->rowCount();

            if ($alterou !== 1) {
                throw new CaixaFisicoErro(
                    'O fechamento já foi processado.'
                );
            }
        });
    }
}