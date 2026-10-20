
<?php
// ================================================
// GYMFLOW - MODEL DE MATRÍCULA ONLINE
// app/models/Matricula.php
//
// Isolamento multi-tenant por company_id.
// ================================================

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';
$erroModel = '';
$dados = $dados ?? [];

// Inicialização das variáveis de retorno.
$plano = $plano ?? null;
$planos = $planos ?? [];
$filial = $filial ?? null;
$filiais = $filiais ?? [];
$matriculaConcretizada = $matriculaConcretizada ?? null;

// A empresa deve ser definida pelo controller.
// Na matrícula pública, o contexto é identificado
// pela academia acessada pelo visitante.
//
// Não utilizamos uma empresa padrão fixa.

$companyIdValidado = filter_var(
    $company_id ?? null,
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1
        ]
    ]
);

try {

    // ============================================
    // 1. VALIDAR CONTEXTO DA EMPRESA
    // ============================================

    if ($companyIdValidado === false) {
        throw new RuntimeException(
            'Academia não identificada para a matrícula.'
        );
    }

    $company_id = (int) $companyIdValidado;

    // Confirma que a empresa existe.

    $stmtEmpresa = $pdo->prepare("
        SELECT id
        FROM companies
        WHERE id = :company_id
        LIMIT 1
    ");

    $stmtEmpresa->execute([
        ':company_id' => $company_id
    ]);

    if (!$stmtEmpresa->fetch(PDO::FETCH_ASSOC)) {
        throw new RuntimeException(
            'Academia não encontrada.'
        );
    }

    // ============================================
    // 2. BUSCAR PLANO POR ID
    // ============================================

    if ($operacao === 'buscar_plano') {

        $plano_id = (int) (
            $plano_id ?? $id ?? 0
        );

        if ($plano_id <= 0) {
            throw new RuntimeException(
                'Plano inválido.'
            );
        }

        $stmt = $pdo->prepare("
            SELECT *
            FROM planos
            WHERE id = :plano_id
              AND company_id = :company_id
            LIMIT 1
        ");

        $stmt->execute([
            ':plano_id' => $plano_id,
            ':company_id' => $company_id
        ]);

        $plano = $stmt->fetch(PDO::FETCH_ASSOC)
            ?: null;
    }

    // ============================================
    // 3. LISTAR PLANOS DA ACADEMIA
    // ============================================

    elseif ($operacao === 'listar_planos') {

        // A tabela planos atualmente não possui
        // uma coluna 'ativo'.
        //
        // Por isso, mostramos os planos existentes
        // da empresa selecionada.

        $stmt = $pdo->prepare("
            SELECT *
            FROM planos
            WHERE company_id = :company_id
            ORDER BY valor ASC, id ASC
        ");

        $stmt->execute([
            ':company_id' => $company_id
        ]);

        $planos = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    // ============================================
    // 4. LISTAR FILIAIS ATIVAS DA ACADEMIA
    // ============================================

    elseif ($operacao === 'listar_filiais') {

        $stmt = $pdo->prepare("
            SELECT *
            FROM filiais
            WHERE company_id = :company_id
              AND ativo = 1
            ORDER BY nome ASC, id ASC
        ");

        $stmt->execute([
            ':company_id' => $company_id
        ]);

        $filiais = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    // ============================================
    // 5. BUSCAR FILIAL POR ID
    // ============================================

    elseif ($operacao === 'buscar_filial') {

        $filial_id = (int) (
            $filial_id ?? $id ?? 0
        );

        if ($filial_id <= 0) {
            throw new RuntimeException(
                'Filial inválida.'
            );
        }

        $stmt = $pdo->prepare("
            SELECT *
            FROM filiais
            WHERE id = :filial_id
              AND company_id = :company_id
              AND ativo = 1
            LIMIT 1
        ");

        $stmt->execute([
            ':filial_id' => $filial_id,
            ':company_id' => $company_id
        ]);

        $filial = $stmt->fetch(PDO::FETCH_ASSOC)
            ?: null;
    }

    // ============================================
    // 6. VALIDAR DADOS CADASTRAIS
    // ============================================

    elseif ($operacao === 'validar_dados_cadastrais') {

        $cpf = trim(
            (string) (
                $dados['cpf'] ?? $cpf ?? ''
            )
        );

        $email = trim(
            (string) (
                $dados['email'] ?? $email ?? ''
            )
        );

        // Preservamos a regra atual de unicidade
        // global de CPF e e-mail.
        //
        // Não alteramos essa regra neste momento
        // porque o banco ainda possui restrições
        // UNIQUE globais.

        if ($cpf !== '') {

            $stmt = $pdo->prepare("
                SELECT id
                FROM alunos
                WHERE cpf = :cpf
                LIMIT 1
            ");

            $stmt->execute([
                ':cpf' => $cpf
            ]);

            if ($stmt->fetch()) {
                throw new RuntimeException(
                    'Este CPF já está cadastrado no sistema.'
                );
            }
        }

        if ($email !== '') {

            $stmt = $pdo->prepare("
                SELECT id
                FROM users
                WHERE email = :email
                LIMIT 1
            ");

            $stmt->execute([
                ':email' => $email
            ]);

            if ($stmt->fetch()) {
                throw new RuntimeException(
                    'Este e-mail já está sendo utilizado.'
                );
            }
        }
    }

    // ============================================
    // 7. CONCRETIZAR MATRÍCULA
    // ============================================

    elseif ($operacao === 'concretizar_matricula') {

        $dadosAluno = $dados['aluno'] ?? [];
        $dadosPlano = $dados['plano'] ?? [];
        $dadosPagamento = $dados['pagamento'] ?? [];

        // O company_id precisa vir do contexto
        // resolvido pelo controller.
        //
        // Nunca determinamos o tenant apenas
        // a partir dos dados enviados no POST.

        $filialId = (int) (
            $dadosAluno['filial_id'] ?? 0
        );

        $planoId = (int) (
            $dadosPlano['id'] ?? 0
        );

        if ($filialId <= 0 || $planoId <= 0) {
            throw new RuntimeException(
                'Plano ou filial inválidos.'
            );
        }

        // Se o controller enviou também a empresa
        // dentro dos dados do aluno, ela não pode
        // divergir do contexto já validado.

        if (
            isset($dadosAluno['company_id']) &&
            (int) $dadosAluno['company_id'] !==
            $company_id
        ) {
            throw new RuntimeException(
                'A empresa da matrícula é inválida.'
            );
        }

        $nomeAluno = trim(
            (string) ($dadosAluno['nome'] ?? '')
        );

        $cpfAluno = trim(
            (string) ($dadosAluno['cpf'] ?? '')
        );

        $emailAluno = trim(
            (string) ($dadosAluno['email'] ?? '')
        );

        $senhaAluno = (string) (
            $dadosAluno['senha'] ?? ''
        );

        if (
            $nomeAluno === '' ||
            $cpfAluno === '' ||
            $emailAluno === '' ||
            $senhaAluno === ''
        ) {
            throw new RuntimeException(
                'Dados obrigatórios do aluno não informados.'
            );
        }

        // ========================================
        // TRANSAÇÃO
        // ========================================

        $pdo->beginTransaction();

        try {

            // ====================================
            // 7.1 VALIDAR FILIAL DA EMPRESA
            // ====================================

            $stmtFilial = $pdo->prepare("
                SELECT id, company_id
                FROM filiais
                WHERE id = :filial_id
                  AND company_id = :company_id
                  AND ativo = 1
                LIMIT 1
            ");

            $stmtFilial->execute([
                ':filial_id' => $filialId,
                ':company_id' => $company_id
            ]);

            $filialBanco = $stmtFilial->fetch(
                PDO::FETCH_ASSOC
            );

            if (!$filialBanco) {
                throw new RuntimeException(
                    'A filial selecionada não pertence ' .
                        'a esta academia ou está inativa.'
                );
            }

            // ====================================
            // 7.2 VALIDAR PLANO DA EMPRESA
            // ====================================

            $stmtPlano = $pdo->prepare("
                SELECT
                    id,
                    company_id,
                    nome,
                    valor,
                    duracao
                FROM planos
                WHERE id = :plano_id
                  AND company_id = :company_id
                LIMIT 1
            ");

            $stmtPlano->execute([
                ':plano_id' => $planoId,
                ':company_id' => $company_id
            ]);

            $planoBanco = $stmtPlano->fetch(
                PDO::FETCH_ASSOC
            );

            if (!$planoBanco) {
                throw new RuntimeException(
                    'O plano selecionado não pertence ' .
                        'a esta academia.'
                );
            }

            // Valores oficiais vêm do banco.
            // Não confiamos em preço ou duração
            // enviados pelo formulário.

            $valorPlano = (float) (
                $planoBanco['valor']
            );

            $duracao = (string) (
                $planoBanco['duracao']
            );

            $meses = match ($duracao) {
                '1 Mês' => 1,
                '3 Meses' => 3,
                '6 Meses' => 6,
                '1 Ano' => 12,
                default => 0
            };

            if ($meses <= 0 || $valorPlano <= 0) {
                throw new RuntimeException(
                    'O plano possui valor ou duração inválidos.'
                );
            }

            // ====================================
            // 7.3 VERIFICAR DUPLICIDADES
            // ====================================

            $stmtCpf = $pdo->prepare("
                SELECT id
                FROM alunos
                WHERE cpf = :cpf
                LIMIT 1
            ");

            $stmtCpf->execute([
                ':cpf' => $cpfAluno
            ]);

            if ($stmtCpf->fetch()) {
                throw new RuntimeException(
                    'O CPF informado já está cadastrado.'
                );
            }

            $stmtEmail = $pdo->prepare("
                SELECT id
                FROM users
                WHERE email = :email
                LIMIT 1
            ");

            $stmtEmail->execute([
                ':email' => $emailAluno
            ]);

            if ($stmtEmail->fetch()) {
                throw new RuntimeException(
                    'O e-mail informado já está em uso.'
                );
            }

            // ====================================
            // 7.4 CRIAR ALUNO
            // ====================================

            $stmtAluno = $pdo->prepare("
                INSERT INTO alunos (
                    filial_id,
                    nome,
                    cpf,
                    rg,
                    sexo,
                    nascimento,
                    email,
                    telefone,
                    endereco,
                    status
                )
                VALUES (
                    :filial_id,
                    :nome,
                    :cpf,
                    :rg,
                    :sexo,
                    :nascimento,
                    :email,
                    :telefone,
                    :endereco,
                    'Ativo'
                )
            ");

            $stmtAluno->execute([
                ':filial_id' => $filialId,
                ':nome' => $nomeAluno,
                ':cpf' => $cpfAluno,
                ':rg' => !empty($dadosAluno['rg'])
                    ? trim((string) $dadosAluno['rg'])
                    : null,
                ':sexo' => trim(
                    (string) ($dadosAluno['sexo'] ?? '')
                ),
                ':nascimento' => trim(
                    (string) ($dadosAluno['nascimento'] ?? '')
                ),
                ':email' => $emailAluno,
                ':telefone' => trim(
                    (string) ($dadosAluno['telefone'] ?? '')
                ),
                ':endereco' => !empty($dadosAluno['endereco'])
                    ? trim((string) $dadosAluno['endereco'])
                    : null
            ]);

            $novoAlunoId = (int) $pdo->lastInsertId();

            // ====================================
            // 7.5 CRIAR USUÁRIO DO PORTAL
            // ====================================

            $stmtUser = $pdo->prepare("
                INSERT INTO users (
                    company_id,
                    name,
                    email,
                    password,
                    role,
                    aluno_id
                )
                VALUES (
                    :company_id,
                    :name,
                    :email,
                    :password,
                    'Aluno',
                    :aluno_id
                )
            ");

            $stmtUser->execute([
                ':company_id' => $company_id,
                ':name' => $nomeAluno,
                ':email' => $emailAluno,
                ':password' => password_hash(
                    $senhaAluno,
                    PASSWORD_DEFAULT
                ),
                ':aluno_id' => $novoAlunoId
            ]);

            // ====================================
            // 7.6 CRIAR MATRÍCULA
            // ====================================

            $dataInicio = date('Y-m-d');

            $dataFim = date(
                'Y-m-d',
                strtotime("+{$meses} months")
            );

            $statusPagamento = (string) (
                $dadosPagamento['status'] ?? 'pendente'
            );

            $pagamentoAprovado =
                $statusPagamento === 'aprovado';

            $matriculaAtiva =
                $pagamentoAprovado ? 1 : 0;

            $stmtMatricula = $pdo->prepare("
                INSERT INTO matriculas (
                    aluno_id,
                    plano_id,
                    inicio,
                    fim,
                    valor,
                    desconto,
                    ativa
                )
                VALUES (
                    :aluno_id,
                    :plano_id,
                    :inicio,
                    :fim,
                    :valor,
                    0.00,
                    :ativa
                )
            ");

            $stmtMatricula->execute([
                ':aluno_id' => $novoAlunoId,
                ':plano_id' => $planoId,
                ':inicio' => $dataInicio,
                ':fim' => $dataFim,
                ':valor' => $valorPlano,
                ':ativa' => $matriculaAtiva
            ]);

            $novaMatriculaId = (int) (
                $pdo->lastInsertId()
            );

            // ====================================
            // 7.7 GERAR PARCELAS FINANCEIRAS
            // ====================================

            // Mantemos a lógica financeira
            // atual nesta etapa.
            //
            // A revisão de pagamentos será
            // realizada separadamente.

            $valorParcela = floor(
                ($valorPlano / $meses) * 100
            ) / 100;

            $stmtConta = $pdo->prepare("
                INSERT INTO contas (
                    aluno_id,
                    matricula_id,
                    vencimento,
                    valor,
                    status,
                    forma_pagamento,
                    pago_em
                )
                VALUES (
                    :aluno_id,
                    :matricula_id,
                    :vencimento,
                    :valor,
                    :status,
                    :forma_pagamento,
                    :pago_em
                )
            ");

            $metodo = (string) (
                $dadosPagamento['metodo'] ?? 'pix'
            );

            $formaPagamentoNome =
                $metodo === 'pix'
                ? 'PIX'
                : 'Cartão de Crédito';

            for ($i = 0; $i < $meses; $i++) {

                $valorAtual = (
                    $i === $meses - 1
                )
                    ? $valorPlano -
                    ($valorParcela * ($meses - 1))
                    : $valorParcela;

                $valorAtual = round(
                    $valorAtual,
                    2
                );

                $vencimento = date(
                    'Y-m-d',
                    strtotime("+{$i} months")
                );

                if (
                    $i === 0 &&
                    $pagamentoAprovado
                ) {

                    $statusConta = 'Pago';
                    $formaConta = $formaPagamentoNome;
                    $pagoEmConta = date('Y-m-d H:i:s');
                } else {

                    $statusConta = 'Aberto';
                    $formaConta = null;
                    $pagoEmConta = null;
                }

                $stmtConta->execute([
                    ':aluno_id' => $novoAlunoId,
                    ':matricula_id' => $novaMatriculaId,
                    ':vencimento' => $vencimento,
                    ':valor' => $valorAtual,
                    ':status' => $statusConta,
                    ':forma_pagamento' => $formaConta,
                    ':pago_em' => $pagoEmConta
                ]);
            }

            // ====================================
            // 7.8 REGISTRAR PAGAMENTO
            // ====================================

            $stmtPagamento = $pdo->prepare("
                INSERT INTO pagamentos (
                    matricula_id,
                    mercado_pago_id,
                    valor,
                    metodo,
                    status
                )
                VALUES (
                    :matricula_id,
                    :mercado_pago_id,
                    :valor,
                    :metodo,
                    :status
                )
            ");

            $stmtPagamento->execute([
                ':matricula_id' => $novaMatriculaId,
                ':mercado_pago_id' =>
                $dadosPagamento['mercado_pago_id']
                    ?? null,
                ':valor' => $valorPlano,
                ':metodo' => $metodo,
                ':status' => $statusPagamento
            ]);

            // ====================================
            // 7.9 CONFIRMAR TRANSAÇÃO
            // ====================================

            $pdo->commit();

            $matriculaConcretizada = [
                'aluno_id' => $novoAlunoId,
                'matricula_id' => $novaMatriculaId,
                'company_id' => $company_id,
                'status' => $statusPagamento,
                'aluno_nome' => $nomeAluno,
                'aluno_email' => $emailAluno,
                'plano_nome' => $planoBanco['nome'],
                'plano_valor' => $valorPlano,
                'filial_id' => $filialId,
                'metodo' => $metodo,
                'mercado_pago_id' =>
                $dadosPagamento['mercado_pago_id']
                    ?? ''
            ];
        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    // ============================================
    // 8. OPERAÇÃO DESCONHECIDA
    // ============================================

    else {

        throw new RuntimeException(
            'Operação de matrícula desconhecida.'
        );
    }
} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $erroModel = $e->getMessage();

    error_log(
        '[GymFlow][Matricula] ' .
            'Empresa ' . (int) ($company_id ?? 0) .
            ' | Operação: ' . $operacao .
            ' | Erro: ' . $erroModel
    );
}
