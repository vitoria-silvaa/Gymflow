<?php
// app/models/Matricula.php

final class MatriculaErro extends DomainException {}

final class MatriculaModel
{
    public function __construct(
        private PDO $pdo,
        private int $empresa,
        private int $usuario = 0,
        private string $role = ''
    ) {
        if ($empresa <= 0) {
            throw new MatriculaErro('Academia indisponível.');
        }

        $this->autorizar();
    }

    private function consultar(
        string $sql,
        array $params = []
    ): PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    private function interno(): bool
    {
        return $this->usuario > 0;
    }

    private function autorizar(bool $lock = false): void
    {
        if (!$this->interno()) {
            return;
        }

        if (
            !in_array($this->role, ['Admin', 'Recepcao'], true) ||
            (int) ($_SESSION['usuario_id'] ?? 0) !== $this->usuario ||
            (int) ($_SESSION['company_id'] ?? 0) !== $this->empresa ||
            ($_SESSION['usuario_role'] ?? '') !== $this->role
        ) {
            throw new MatriculaErro('Acesso não autorizado.');
        }

        $id = $this->consultar(
            'SELECT id FROM users
             WHERE id = ?
               AND company_id = ?
               AND role = ?' .
            ($lock ? ' FOR UPDATE' : ''),
            [$this->usuario, $this->empresa, $this->role]
        )->fetchColumn();

        if (!$id) {
            throw new MatriculaErro('Acesso não autorizado.');
        }
    }

    public function empresa(): ?array
    {
        return $this->consultar(
            'SELECT id, nome FROM companies WHERE id = ?',
            [$this->empresa]
        )->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function configuracao(): array
    {
        $empresa = $this->empresa();

        if (!$empresa) {
            throw new MatriculaErro('Academia indisponível.');
        }

        $padrao = [
            'app_name' => $empresa['nome'],
            'theme_mode' => 'dark',
            'primary_color' => '#C9A227',
            'secondary_color' => '#000000',
            'logo_url' => '',
            'contact_email' => '',
            'contact_phone' => '',
            'contact_hours' => '',
            'instagram_url' => '',
            'facebook_url' => '',
            'tiktok_url' => '',
            'whatsapp_url' => ''
        ];

        $config = $this->consultar(
            'SELECT * FROM portfolio_config
             WHERE company_id = ? LIMIT 1',
            [$this->empresa]
        )->fetch(PDO::FETCH_ASSOC) ?: [];

        $config = array_merge($padrao, $config);

        $config['app_name'] =
            $config['app_name'] ?: $empresa['nome'];

        return $config;
    }

    public function planos(): array
    {
        $planos = $this->consultar(
            'SELECT * FROM planos
             WHERE company_id = ?
               AND valor > 0
             ORDER BY valor, id',
            [$this->empresa]
        )->fetchAll(PDO::FETCH_ASSOC);

        $disponiveis = [];

        foreach ($planos as $plano) {
            try {
                self::cobrancas($plano);
                $disponiveis[] = $plano;
            } catch (MatriculaErro $e) {
                continue;
            }
        }

        return $disponiveis;
    }

    public function plano(
        int $id,
        bool $lock = false
    ): ?array {

        return $this->consultar(
            'SELECT * FROM planos
             WHERE id = ?
               AND company_id = ?' .
            ($lock ? ' FOR UPDATE' : ''),
            [$id, $this->empresa]
        )->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function escopoFiliais(): array
    {
        $sql = '
            WHERE f.company_id = ?
              AND f.ativo = 1
        ';

        $params = [$this->empresa];

        if ($this->role === 'Recepcao') {

            $sql .= '
                AND EXISTS (
                    SELECT 1 FROM user_filiais uf
                    WHERE uf.filial_id = f.id
                      AND uf.user_id = ?
                )
            ';

            $params[] = $this->usuario;
        }

        return [$sql, $params];
    }

    public function filiais(): array
    {
        [$sql, $params] = $this->escopoFiliais();

        return $this->consultar(
            'SELECT f.* FROM filiais f' .
            $sql . ' ORDER BY f.nome, f.id',
            $params
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filial(
        int $id,
        bool $lock = false
    ): ?array {

        [$sql, $params] = $this->escopoFiliais();

        $params[] = $id;

        $filial = $this->consultar(
            'SELECT f.* FROM filiais f' .
            $sql . ' AND f.id = ?' .
            ($lock ? ' FOR UPDATE' : ''),
            $params
        )->fetch(PDO::FETCH_ASSOC) ?: null;

        if (
            $filial &&
            $lock &&
            $this->role === 'Recepcao'
        ) {

            $vinculo = $this->consultar(
                'SELECT filial_id FROM user_filiais
                 WHERE user_id = ?
                   AND filial_id = ?
                 FOR UPDATE',
                [$this->usuario, $id]
            )->fetchColumn();

            if (!$vinculo) {
                return null;
            }
        }

        return $filial;
    }

    public function metodos(): array
    {
        return $this->interno()
            ? [
                'pix' => 'Pix',
                'credito' => 'Cartão de crédito',
                'debito' => 'Cartão de débito',
                'dinheiro' => 'Dinheiro'
            ]
            : ['pix' => 'Pix'];
    }

    public function lead(
        int $id,
        bool $lock = false
    ): array {

        if (!$this->interno() || $id <= 0) {
            throw new MatriculaErro('Lead não autorizado.');
        }

        $this->autorizar();

        $lead = $this->consultar(
            'SELECT l.*
             FROM leads l
             INNER JOIN filiais f
                ON f.id = l.filial_id
             WHERE l.id = ?
               AND f.company_id = ?' .
            ($lock ? ' FOR UPDATE' : ''),
            [$id, $this->empresa]
        )->fetch(PDO::FETCH_ASSOC);

        if (
            !$lead ||
            !$this->filial((int) $lead['filial_id'], $lock)
        ) {
            throw new MatriculaErro(
                'Lead indisponível nas unidades autorizadas.'
            );
        }

        if (!in_array(
            $lead['status'],
            ['Novo', 'Contato Agendado', 'Experimental'],
            true
        )) {
            throw new MatriculaErro(
                'Este lead não está disponível para matrícula.'
            );
        }

        $vinculo = $this->consultar(
            'SELECT id FROM matriculas
             WHERE lead_id = ?
             LIMIT 1' .
            ($lock ? ' FOR UPDATE' : ''),
            [$id]
        )->fetchColumn();

        if ($vinculo) {
            throw new MatriculaErro(
                'Este lead já possui uma matrícula vinculada.'
            );
        }

        return $lead;
    }

    public static function texto($v): string
    {
        return is_string($v) ? trim($v) : '';
    }

    private static function tamanho(string $v): int
    {
        $n = preg_match_all('/./us', $v);

        return $n === false ? PHP_INT_MAX : $n;
    }

    private static function cpfValido(string $cpf): bool
    {
        if (
            strlen($cpf) !== 11 ||
            preg_match('/^(\d)\1{10}$/', $cpf)
        ) {
            return false;
        }

        for ($i = 9; $i <= 10; $i++) {

            $soma = 0;

            for ($j = 0; $j < $i; $j++) {
                $soma += (int) $cpf[$j] * ($i + 1 - $j);
            }

            $digito = ($soma * 10) % 11;

            if (
                ($digito === 10 ? 0 : $digito)
                !== (int) $cpf[$i]
            ) {
                return false;
            }
        }

        return true;
    }

    public static function normalizarCpf(string $cpf): string
    {
        $n = preg_replace('/\D/', '', $cpf);

        if (
            !preg_match('/^[0-9.\- ]+$/', $cpf) ||
            strlen($n) !== 11
        ) {
            throw new MatriculaErro(
                'Informe um CPF com 11 dígitos.'
            );
        }

        return substr($n, 0, 3) . '.' .
            substr($n, 3, 3) . '.' .
            substr($n, 6, 3) . '-' .
            substr($n, 9, 2);
    }

    public static function validarIdentificacao(
        array $entrada,
        array &$erros
    ): array {

        $d = [];

        foreach ([
            'nome', 'cpf', 'rg', 'sexo',
            'nascimento', 'email', 'telefone', 'endereco'
        ] as $campo) {

            $d[$campo] = self::texto(
                $entrada[$campo] ?? ''
            );
        }

        if (
            $d['nome'] === '' ||
            self::tamanho($d['nome']) > 100
        ) {
            $erros['nome'] =
                'Informe o nome completo com até 100 caracteres.';
        }

        try {

            $d['cpf'] = self::normalizarCpf($d['cpf']);

            if (!self::cpfValido(
                preg_replace('/\D/', '', $d['cpf'])
            )) {
                $erros['cpf'] = 'Informe um CPF válido.';
            }

        } catch (MatriculaErro $e) {
            $erros['cpf'] = 'Informe um CPF válido.';
        }

        if (
            self::tamanho($d['rg']) > 20 ||
            (
                $d['rg'] !== '' &&
                !preg_match('/^[0-9A-Za-z.\/ -]+$/', $d['rg'])
            )
        ) {
            $erros['rg'] =
                'Informe um RG válido com até 20 caracteres.';
        }

        if (!in_array(
            $d['sexo'],
            ['Masculino', 'Feminino', 'Outro'],
            true
        )) {
            $erros['sexo'] = 'Selecione uma opção.';
        }

        $data = preg_match(
            '/^\d{4}-\d{2}-\d{2}$/',
            $d['nascimento']
        )
            ? DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $d['nascimento']
            )
            : false;

        if (
            !$data ||
            $data->format('Y-m-d') !== $d['nascimento'] ||
            $d['nascimento'] < '1000-01-01' ||
            $d['nascimento'] > date('Y-m-d')
        ) {
            $erros['nascimento'] =
                'Informe uma data de nascimento válida.';
        }

        if (
            strlen($d['email']) > 100 ||
            !filter_var($d['email'], FILTER_VALIDATE_EMAIL)
        ) {
            $erros['email'] = 'Informe um e-mail válido.';
        }

        $tel = preg_replace('/\D/', '', $d['telefone']);

        if (
            !preg_match('/^[0-9() .\-]+$/', $d['telefone']) ||
            !in_array(strlen($tel), [10, 11], true)
        ) {
            $erros['telefone'] =
                'Informe um telefone válido com DDD.';
        } else {
            $d['telefone'] =
                '(' . substr($tel, 0, 2) . ') ' .
                substr($tel, 2, -4) . '-' .
                substr($tel, -4);
        }

        if (self::tamanho($d['endereco']) > 1000) {
            $erros['endereco'] =
                'O endereço deve ter até 1.000 caracteres.';
        }

        return $d;
    }

    public function buscarAlunoCpf(string $cpf): ?array
    {
        $this->autorizar();

        if (!$this->interno()) {
            throw new MatriculaErro('Acesso não autorizado.');
        }

        $cpf = self::normalizarCpf($cpf);

        [$sql, $params] = $this->escopoFiliais();

        $params[] = preg_replace('/\D/', '', $cpf);

        $registros = $this->consultar(
            "SELECT
                a.id, a.nome, a.cpf,
                a.filial_id, a.status,
                f.nome AS nome_filial
             FROM alunos a
             INNER JOIN filiais f
                ON f.id = a.filial_id" .
            $sql .
            " AND REPLACE(
                    REPLACE(
                        REPLACE(a.cpf, '.', ''),
                        '-', ''
                    ),
                    ' ', ''
                ) = ?
                LIMIT 2",
            $params
        )->fetchAll(PDO::FETCH_ASSOC);

        if (count($registros) > 1) {
            throw new MatriculaErro(
                'Existe uma inconsistência cadastral. Procure o administrador.'
            );
        }

        if (
            !$registros &&
            !self::cpfValido(preg_replace('/\D/', '', $cpf))
        ) {
            throw new MatriculaErro('Informe um CPF válido.');
        }

        return $registros[0] ?? null;
    }

    public function alunoExistente(
        int $id,
        bool $lock = false
    ): array {

        if (!$this->interno() || $id <= 0) {
            throw new MatriculaErro('Aluno não autorizado.');
        }

        $this->autorizar();

        $aluno = $this->consultar(
            'SELECT
                a.id, a.nome, a.cpf,
                a.filial_id, a.status,
                f.nome AS nome_filial
             FROM alunos a
             INNER JOIN filiais f
                ON f.id = a.filial_id
             WHERE a.id = ?
               AND f.company_id = ?' .
            ($lock ? ' FOR UPDATE' : ''),
            [$id, $this->empresa]
        )->fetch(PDO::FETCH_ASSOC);

        if (
            !$aluno ||
            !$this->filial((int) $aluno['filial_id'], $lock)
        ) {
            throw new MatriculaErro(
                'Aluno indisponível nas unidades autorizadas.'
            );
        }

        if (!in_array(
            $aluno['status'],
            ['Ativo', 'Inativo'],
            true
        )) {
            throw new MatriculaErro(
                'Revise a situação do aluno antes de matricular.'
            );
        }

        return $aluno;
    }

    public function validarAlunoExistente(
        int $id,
        bool $lock = false
    ): array {

        $aluno = $this->alunoExistente($id, $lock);

        $matricula = $this->consultar(
            "SELECT id FROM matriculas
             WHERE aluno_id = ?
               AND (
                   ativa = 1
                   OR status_matricula = 'pendente'
               )
             LIMIT 1" .
            ($lock ? ' FOR UPDATE' : ''),
            [$id]
        )->fetchColumn();

        if ($matricula) {
            throw new MatriculaErro(
                'Este aluno já possui uma matrícula ativa ou pendente.'
            );
        }

        $usuarios = $this->consultar(
            "SELECT id FROM users
             WHERE aluno_id = ?
               AND company_id = ?
               AND role = 'Aluno'
             LIMIT 2" .
            ($lock ? ' FOR UPDATE' : ''),
            [$id, $this->empresa]
        )->fetchAll(PDO::FETCH_ASSOC);

        if (count($usuarios) !== 1) {
            throw new MatriculaErro(
                'O acesso do aluno precisa ser regularizado pelo administrador.'
            );
        }

        return $aluno;
    }

    public function validarCadastroNovo(array $d): void
    {
        $cpf = preg_replace('/\D/', '', $d['cpf']);

        $encontrouCpf = $this->consultar(
            "SELECT id FROM alunos
             WHERE REPLACE(
                 REPLACE(
                     REPLACE(cpf, '.', ''),
                     '-', ''
                 ),
                 ' ', ''
             ) = ?
             LIMIT 1",
            [$cpf]
        )->fetchColumn();

        $encontrouEmail = $this->consultar(
            'SELECT id FROM users
             WHERE email = ? LIMIT 1',
            [$d['email']]
        )->fetchColumn();

        if ($encontrouCpf || $encontrouEmail) {
            throw new MatriculaErro(
                'Não foi possível utilizar os dados informados. ' .
                'Confira o cadastro existente com o responsável.'
            );
        }
    }

    public static function cobrancas(array $plano): array
    {
        $duracao = strtolower(strtr(
            trim((string) ($plano['duracao'] ?? '')),
            [
                'ê' => 'e', 'Ê' => 'E',
                'é' => 'e', 'É' => 'E',
                'ã' => 'a', 'Ã' => 'A'
            ]
        ));

        $duracao = preg_replace('/\s+/u', ' ', $duracao);

        $meses = match ($duracao) {
            '1 mes', 'mensal' => 1,
            '3 meses', 'trimestral' => 3,
            '6 meses', 'semestral' => 6,
            '1 ano', '12 meses', 'anual' => 12,
            default => 0
        };

        $valor = (string) ($plano['valor'] ?? '');

        if (
            $meses === 0 ||
            !preg_match(
                '/^(\d{1,8})(?:\.(\d{1,2}))?$/',
                $valor,
                $m
            )
        ) {
            throw new MatriculaErro('O plano está indisponível.');
        }

        $total = (int) $m[1] * 100 +
            (int) str_pad($m[2] ?? '', 2, '0');

        if ($total < $meses) {
            throw new MatriculaErro(
                'O valor do plano está indisponível.'
            );
        }

        $base = intdiv($total, $meses);

        $parcelas = array_fill(0, $meses, $base);

        $parcelas[$meses - 1] += $total - $base * $meses;

        return [
            'meses' => $meses,
            'total' => $total,
            'parcelas' => $parcelas
        ];
    }

    public static function decimal(int $centavos): string
    {
        return intdiv($centavos, 100) . '.' .
            str_pad(
                (string) ($centavos % 100),
                2, '0', STR_PAD_LEFT
            );
    }

    public static function moeda(int $centavos): string
    {
        return 'R$ ' .
            number_format(
                intdiv($centavos, 100),
                0, ',', '.'
            ) . ',' .
            str_pad(
                (string) ($centavos % 100),
                2, '0', STR_PAD_LEFT
            );
    }

    private static function somarMeses(
        DateTimeImmutable $data,
        int $meses
    ): string {

        $destino = $data
            ->modify('first day of this month')
            ->modify('+' . $meses . ' months');

        $dia = min(
            (int) $data->format('d'),
            (int) $destino->format('t')
        );

        return $destino->setDate(
            (int) $destino->format('Y'),
            (int) $destino->format('m'),
            $dia
        )->format('Y-m-d');
    }

    public function criarPendente(
        array $dados,
        array $oferta
    ): array {

        $existenteId =
            (int) ($dados['aluno_existente_id'] ?? 0);

        $leadId = (int) ($dados['lead_id'] ?? 0);

        $metodo = self::texto($dados['metodo'] ?? 'pix');

        if (
            !$this->interno() &&
            ($existenteId || $leadId)
        ) {
            throw new MatriculaErro('Operação não autorizada.');
        }

        if (!isset($this->metodos()[$metodo])) {
            throw new MatriculaErro(
                'Forma de pagamento não autorizada.'
            );
        }

        $d = [];
        $hash = '';

        if (!$existenteId) {

            $erros = [];

            $d = self::validarIdentificacao($dados, $erros);

            $hash = $dados['senha_hash'] ?? '';

            if (
                $erros ||
                !is_string($hash) ||
                password_get_info($hash)['algoName'] === 'unknown'
            ) {
                throw new MatriculaErro(
                    'Revise os dados pessoais antes de continuar.'
                );
            }
        }

        $this->pdo->beginTransaction();

        try {

            $empresa = $this->consultar(
                'SELECT id FROM companies
                 WHERE id = ? FOR UPDATE',
                [$this->empresa]
            )->fetchColumn();

            if (!$empresa) {
                throw new MatriculaErro('Academia indisponível.');
            }

            $this->autorizar(true);

            $lead = $leadId
                ? $this->lead($leadId, true)
                : null;

            $plano = $this->plano(
                (int) ($dados['plano_id'] ?? 0),
                true
            );

            $filial = $this->filial(
                (int) ($dados['filial_id'] ?? 0),
                true
            );

            if (!$plano || !$filial) {
                throw new MatriculaErro(
                    'O plano ou a unidade não está disponível.'
                );
            }

            if (
                $lead &&
                (int) $lead['filial_id'] !== (int) $filial['id']
            ) {
                throw new MatriculaErro(
                    'A matrícula deve utilizar a unidade do lead.'
                );
            }

            $c = self::cobrancas($plano);

            if ($oferta !== [
                'plano_id' => (int) $plano['id'],
                'filial_id' => (int) $filial['id'],
                'duracao' => $plano['duracao'],
                'total' => $c['total']
            ]) {
                throw new MatriculaErro(
                    'As condições foram atualizadas. Confira os dados novamente.'
                );
            }

            if ($existenteId) {

                $existente = $this->validarAlunoExistente(
                    $existenteId,
                    true
                );

                if (
                    (int) $existente['filial_id'] !==
                    (int) $filial['id']
                ) {
                    throw new MatriculaErro(
                        'A matrícula deve utilizar a unidade atual do aluno.'
                    );
                }

                $aluno = $existenteId;
                $nomeAluno = $existente['nome'];

            } else {

                $this->validarCadastroNovo($d);

                $this->consultar(
                    "INSERT INTO alunos (
                        filial_id, nome, cpf, rg, sexo,
                        nascimento, email, telefone,
                        endereco, status
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Inativo'
                    )",
                    [
                        $filial['id'],
                        $d['nome'],
                        $d['cpf'],
                        $d['rg'] ?: null,
                        $d['sexo'],
                        $d['nascimento'],
                        $d['email'],
                        $d['telefone'],
                        $d['endereco'] ?: null
                    ]
                );

                $aluno = (int) $this->pdo->lastInsertId();

                $nomeAluno = $d['nome'];

                $this->consultar(
                    "INSERT INTO users (
                        company_id, name, email,
                        telefone, endereco, password,
                        role, aluno_id
                    ) VALUES (?, ?, ?, ?, ?, ?, 'Aluno', ?)",
                    [
                        $this->empresa,
                        $d['nome'],
                        $d['email'],
                        $d['telefone'],
                        $d['endereco'] ?: null,
                        $hash,
                        $aluno
                    ]
                );
            }

            $inicio = new DateTimeImmutable(
                $this->consultar(
                    'SELECT CURRENT_DATE()'
                )->fetchColumn()
            );

            $origem = $lead
                ? 'crm'
                : ($this->interno() ? 'recepcao' : 'portfolio');

            $this->consultar(
                "INSERT INTO matriculas (
                    aluno_id, plano_id, inicio, fim,
                    valor, desconto, ativa, origem,
                    status_matricula, filial_id,
                    criado_por, lead_id, criado_em
                ) VALUES (
                    ?, ?, ?, ?, ?, 0, 0, ?, 'pendente',
                    ?, ?, ?, CURRENT_TIMESTAMP
                )",
                [
                    $aluno,
                    $plano['id'],
                    $inicio->format('Y-m-d'),
                    self::somarMeses($inicio, $c['meses']),
                    self::decimal($c['total']),
                    $origem,
                    $filial['id'],
                    $this->usuario ?: null,
                    $leadId ?: null
                ]
            );

            $matricula = (int) $this->pdo->lastInsertId();

            $primeira = 0;

            foreach ($c['parcelas'] as $i => $parcela) {

                $this->consultar(
                    "INSERT INTO contas (
                        aluno_id, matricula_id,
                        vencimento, valor, status
                    ) VALUES (?, ?, ?, ?, 'Aberto')",
                    [
                        $aluno,
                        $matricula,
                        self::somarMeses($inicio, $i),
                        self::decimal($parcela)
                    ]
                );

                if ($i === 0) {
                    $primeira =
                        (int) $this->pdo->lastInsertId();
                }
            }

            $this->consultar(
                "INSERT INTO pagamentos (
                    matricula_id, conta_id,
                    valor, metodo, status
                ) VALUES (?, ?, ?, ?, 'pendente')",
                [
                    $matricula,
                    $primeira,
                    self::decimal($c['parcelas'][0]),
                    $metodo
                ]
            );

            if ($lead) {

                $alterou = $this->consultar(
                    "UPDATE leads
                     SET status = 'Aguardando pagamento'
                     WHERE id = ?
                       AND status IN (
                           'Novo',
                           'Contato Agendado',
                           'Experimental'
                       )",
                    [$leadId]
                )->rowCount();

                if ($alterou !== 1) {
                    throw new MatriculaErro(
                        'A situação do lead foi alterada.'
                    );
                }
            }

            $resultado = [
                'company_id' => $this->empresa,
                'aluno_id' => $aluno,
                'matricula_id' => $matricula,
                'filial_id' => (int) $filial['id'],
                'lead_id' => $leadId,
                'origem' => $origem,
                'aluno_nome' => $nomeAluno,
                'plano_nome' => $plano['nome'],
                'filial_nome' => $filial['nome'],
                'filial_telefone' => $filial['telefone'],
                'total' => $c['total'],
                'primeira_parcela' => $c['parcelas'][0],
                'metodo' => $this->metodos()[$metodo],
                'status' => 'pendente'
            ];

            $this->pdo->commit();

            return $resultado;

        } catch (Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if (
                $e instanceof PDOException &&
                (string) $e->getCode() === '23000'
            ) {
                error_log(
                    '[GymFlow][Matricula] Falha de integridade cadastral.'
                );

                throw new MatriculaErro(
                    'Não foi possível concluir o cadastro. Confira se a matrícula já existe.'
                );
            }

            throw $e;
        }
    }
}