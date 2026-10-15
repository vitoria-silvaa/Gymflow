
<?php
// app/models/Funcionario.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';
$erroModel = '';
$dados = $dados ?? [];
$senha = $senha ?? '';

// =====================================================
// FUNÇÕES AUXILIARES - MULTI-TENANT
// =====================================================

if (!function_exists('empresaFuncionarioLogada')) {
    function empresaFuncionarioLogada(): int
    {
        $empresaId = (int) ($_SESSION['company_id'] ?? 0);
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);

        if ($empresaId <= 0 || $usuarioId <= 0) {
            throw new RuntimeException(
                'Sessão inválida. Faça login novamente.'
            );
        }

        return $empresaId;
    }
}

if (!function_exists('validarFilialFuncionario')) {
    function validarFilialFuncionario(
        PDO $pdo,
        int $filialId,
        int $empresaId
    ): void {

        if ($filialId <= 0) {
            return;
        }

        $stmt = $pdo->prepare("
            SELECT id
            FROM filiais
            WHERE id = :filial_id
              AND company_id = :company_id
              AND ativo = 1
            LIMIT 1
        ");

        $stmt->execute([
            ':filial_id' => $filialId,
            ':company_id' => $empresaId
        ]);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new RuntimeException(
                'A filial selecionada não pertence à sua empresa ou está inativa.'
            );
        }
    }
}

if (!function_exists('validarCargoFuncionario')) {
    function validarCargoFuncionario(string $cargo): void
    {
        $cargosPermitidos = [
            'Admin',
            'Professor',
            'Recepcao'
        ];

        if (!in_array($cargo, $cargosPermitidos, true)) {
            throw new RuntimeException(
                'O cargo selecionado é inválido.'
            );
        }
    }
}

if (!function_exists('validarGestorFuncionarios')) {
    function validarGestorFuncionarios(): void
    {
        if (
            ($_SESSION['usuario_role'] ?? '') !== 'Admin'
        ) {
            throw new RuntimeException(
                'Você não possui permissão para gerenciar funcionários.'
            );
        }
    }
}

// =====================================================
// 1. BUSCAR USUÁRIO POR E-MAIL (LOGIN)
// =====================================================

if ($operacao === 'buscar_por_email') {

    $email = trim($dados['email'] ?? $email ?? '');
    $usuario = null;

    if ($email !== '') {

        $stmt = $pdo->prepare("
            SELECT
                u.id,
                u.company_id,
                u.name,
                u.email,
                u.telefone,
                u.password,
                u.role,
                u.aluno_id
            FROM users u
            INNER JOIN companies c
                ON c.id = u.company_id
            WHERE u.email = :email
            LIMIT 1
        ");

        $stmt->execute([
            ':email' => $email
        ]);

        $usuario = $stmt->fetch(
            PDO::FETCH_ASSOC
        ) ?: null;
    }
}

// =====================================================
// 2. LISTAR FUNCIONÁRIOS DA EMPRESA
// =====================================================

elseif ($operacao === 'listar') {

    $funcionarios = [];

    try {

        $empresaId = empresaFuncionarioLogada();

        $nome_busca = trim($nome_busca ?? '');
        $role_busca = trim($role_busca ?? '');

        $sql = "
            SELECT
                id,
                name,
                email,
                telefone,
                role
            FROM users
            WHERE role != 'Aluno'
              AND company_id = :company_id
        ";

        $parametros = [
            ':company_id' => $empresaId
        ];

        if ($nome_busca !== '') {

            $sql .= " AND name LIKE :nome";

            $parametros[':nome'] =
                '%' . $nome_busca . '%';
        }

        if ($role_busca !== '') {

            $sql .= " AND role = :role";

            $parametros[':role'] = $role_busca;
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $pdo->prepare($sql);

        $stmt->execute($parametros);

        $funcionarios = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    } catch (Throwable $e) {

        $erroModel = $e->getMessage();
    }
}

// =====================================================
// 3. BUSCAR FUNCIONÁRIO E SUA FILIAL
// =====================================================

elseif ($operacao === 'buscar') {

    $funcionario = null;
    $filial_vinculada = null;

    try {

        $empresaId = empresaFuncionarioLogada();
        $id = (int) ($id ?? 0);

        $stmt = $pdo->prepare("
            SELECT
                u.*,
                f.id AS filial_id,
                f.nome AS filial_nome,
                f.cnpj AS filial_cnpj
            FROM users u

            LEFT JOIN user_filiais uf
                ON uf.user_id = u.id
                AND uf.filial_id = (
                    SELECT MIN(f2.id)
                    FROM user_filiais uf2
                    INNER JOIN filiais f2
                        ON f2.id = uf2.filial_id
                    WHERE uf2.user_id = u.id
                      AND f2.company_id = u.company_id
                )

            LEFT JOIN filiais f
                ON f.id = uf.filial_id
                AND f.company_id = u.company_id

            WHERE u.id = :id
              AND u.role != 'Aluno'
              AND u.company_id = :company_id

            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $id,
            ':company_id' => $empresaId
        ]);

        $funcionario = $stmt->fetch(
            PDO::FETCH_ASSOC
        ) ?: null;

        if (
            $funcionario &&
            !empty($funcionario['filial_id'])
        ) {

            $filial_vinculada = [
                'id' => $funcionario['filial_id'],
                'nome' => $funcionario['filial_nome'],
                'cnpj' => $funcionario['filial_cnpj'] ?? ''
            ];
        }
    } catch (Throwable $e) {

        $erroModel = $e->getMessage();
    }
}

// =====================================================
// 4. CADASTRAR FUNCIONÁRIO
// =====================================================

elseif ($operacao === 'cadastrar') {

    try {

        validarGestorFuncionarios();

        $empresaId = empresaFuncionarioLogada();

        $nomeFuncionario = trim(
            $dados['nome'] ?? $nome ?? ''
        );

        $emailFuncionario = trim(
            $dados['email'] ?? $email ?? ''
        );

        $senhaFuncionario =
            $dados['senha'] ?? $senha ?? '';

        $telefoneFuncionario = trim(
            $dados['telefone'] ?? ''
        );

        $enderecoFuncionario = trim(
            $dados['endereco'] ?? ''
        );

        $cargoFuncionario =
            $dados['role'] ?? $role ?? 'Recepcao';

        $filialId = (int) (
            $dados['id_filial'] ?? $id_filial ?? 0
        );

        // Validação dos dados
        if ($nomeFuncionario === '') {
            throw new RuntimeException(
                'Informe o nome do funcionário.'
            );
        }

        if (!filter_var(
            $emailFuncionario,
            FILTER_VALIDATE_EMAIL
        )) {
            throw new RuntimeException(
                'Informe um e-mail válido.'
            );
        }

        if ($senhaFuncionario === '') {
            throw new RuntimeException(
                'A senha é obrigatória.'
            );
        }

        validarCargoFuncionario($cargoFuncionario);

        // Inicia a transação
        $pdo->beginTransaction();

        // Confere se a filial pertence à empresa
        validarFilialFuncionario(
            $pdo,
            $filialId,
            $empresaId
        );

        // Verifica e-mail duplicado
        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute([
            ':email' => $emailFuncionario
        ]);

        if ($stmt->fetch()) {
            throw new RuntimeException(
                'Este e-mail já está cadastrado.'
            );
        }

        // Cria o funcionário
        $senhaHash = password_hash(
            $senhaFuncionario,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare("
            INSERT INTO users (
                company_id,
                name,
                email,
                telefone,
                endereco,
                password,
                role
            )
            VALUES (
                :company_id,
                :name,
                :email,
                :telefone,
                :endereco,
                :password,
                :role
            )
        ");

        $stmt->execute([
            ':company_id' => $empresaId,
            ':name' => $nomeFuncionario,
            ':email' => $emailFuncionario,
            ':telefone' => $telefoneFuncionario,
            ':endereco' => $enderecoFuncionario,
            ':password' => $senhaHash,
            ':role' => $cargoFuncionario
        ]);

        $novoUserId = (int) $pdo->lastInsertId();

        // Vincula o funcionário à filial autorizada
        if ($filialId > 0) {

            $stmt = $pdo->prepare("
                INSERT INTO user_filiais (
                    user_id,
                    filial_id
                )
                VALUES (
                    :user_id,
                    :filial_id
                )
            ");

            $stmt->execute([
                ':user_id' => $novoUserId,
                ':filial_id' => $filialId
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $erroModel = $e->getMessage();
    }
}

// =====================================================
// 5. ATUALIZAR FUNCIONÁRIO
// =====================================================

elseif ($operacao === 'atualizar') {

    try {

        validarGestorFuncionarios();

        $empresaId = empresaFuncionarioLogada();

        $id = (int) ($id ?? 0);

        $nomeFuncionario = trim(
            $dados['nome'] ?? $nome ?? ''
        );

        $emailFuncionario = trim(
            $dados['email'] ?? $email ?? ''
        );

        $novaSenha =
            $dados['senha'] ?? $senha ?? '';

        $telefoneFuncionario = trim(
            $dados['telefone'] ?? ''
        );

        $enderecoFuncionario = trim(
            $dados['endereco'] ?? ''
        );

        $cargoFuncionario =
            $dados['role'] ?? $role ?? 'Recepcao';

        $filialId = (int) (
            $dados['id_filial'] ?? $id_filial ?? 0
        );

        if ($id <= 0) {
            throw new RuntimeException(
                'Funcionário inválido.'
            );
        }

        if ($nomeFuncionario === '') {
            throw new RuntimeException(
                'Informe o nome do funcionário.'
            );
        }

        if (!filter_var(
            $emailFuncionario,
            FILTER_VALIDATE_EMAIL
        )) {
            throw new RuntimeException(
                'Informe um e-mail válido.'
            );
        }

        validarCargoFuncionario($cargoFuncionario);

        $pdo->beginTransaction();

        // Primeiro confirma que o funcionário
        // pertence à empresa autenticada.
        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE id = :id
              AND company_id = :company_id
              AND role != 'Aluno'
            LIMIT 1
            FOR UPDATE
        ");

        $stmt->execute([
            ':id' => $id,
            ':company_id' => $empresaId
        ]);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new RuntimeException(
                'Funcionário não encontrado nesta empresa.'
            );
        }

        // Impede modificar a filial para
        // uma unidade de outra empresa.
        validarFilialFuncionario(
            $pdo,
            $filialId,
            $empresaId
        );

        // Verifica e-mail duplicado.
        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = :email
              AND id != :id
            LIMIT 1
        ");

        $stmt->execute([
            ':email' => $emailFuncionario,
            ':id' => $id
        ]);

        if ($stmt->fetch()) {
            throw new RuntimeException(
                'Este e-mail pertence a outro usuário.'
            );
        }

        // Atualiza os dados básicos
        $sql = "
            UPDATE users
            SET
                name = :name,
                email = :email,
                telefone = :telefone,
                endereco = :endereco,
                role = :role
        ";

        $parametros = [
            ':name' => $nomeFuncionario,
            ':email' => $emailFuncionario,
            ':telefone' => $telefoneFuncionario,
            ':endereco' => $enderecoFuncionario,
            ':role' => $cargoFuncionario,
            ':id' => $id,
            ':company_id' => $empresaId
        ];

        // Só modifica a senha quando
        // uma nova senha for informada.
        if ($novaSenha !== '') {

            $sql .= ", password = :password";

            $parametros[':password'] = password_hash(
                $novaSenha,
                PASSWORD_DEFAULT
            );
        }

        $sql .= "
            WHERE id = :id
              AND company_id = :company_id
              AND role != 'Aluno'
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);

        // Atualiza o vínculo da filial somente
        // depois de confirmar a empresa.
        $stmt = $pdo->prepare("
            DELETE uf
            FROM user_filiais uf
            INNER JOIN users u
                ON u.id = uf.user_id
            WHERE uf.user_id = :user_id
              AND u.company_id = :company_id
        ");

        $stmt->execute([
            ':user_id' => $id,
            ':company_id' => $empresaId
        ]);

        if ($filialId > 0) {

            $stmt = $pdo->prepare("
                INSERT INTO user_filiais (
                    user_id,
                    filial_id
                )
                VALUES (
                    :user_id,
                    :filial_id
                )
            ");

            $stmt->execute([
                ':user_id' => $id,
                ':filial_id' => $filialId
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $erroModel = $e->getMessage();
    }
}
