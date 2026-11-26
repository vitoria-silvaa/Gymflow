<?php
// app/models/Crm.php

require_once __DIR__ . '/Database.php';

$operacao = $operacao ?? '';
$erroModel = '';

$crmEmpresa = (int) ($_SESSION['company_id'] ?? 0);
$crmUsuario = (int) ($_SESSION['usuario_id'] ?? 0);
$crmRole = $_SESSION['usuario_role'] ?? '';

$crmExecutar = static function (
    string $sql,
    array $params = []
) use ($pdo): PDOStatement {

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt;
};

$crmStatusManuais = [
    'Novo',
    'Contato Agendado',
    'Experimental',
    'Perdido'
];

$crmMensagem = '';
$crmTransacao = false;

$crmFalhar = static function (
    string $mensagem
) use (&$crmMensagem): void {

    $crmMensagem = $mensagem;

    throw new DomainException(
        'Operação CRM não autorizada.'
    );
};

try {

    if (
        $crmEmpresa <= 0 ||
        $crmUsuario <= 0 ||
        !in_array(
            $crmRole,
            ['Admin', 'Recepcao'],
            true
        )
    ) {
        $crmFalhar('Acesso não autorizado.');
    }

    $crmUsuarioValido = $crmExecutar(
        'SELECT id FROM users
         WHERE id = ?
           AND company_id = ?
           AND role = ?',
        [$crmUsuario, $crmEmpresa, $crmRole]
    )->fetchColumn();

    if (!$crmUsuarioValido) {
        $crmFalhar('Sua sessão não está autorizada.');
    }

    $crmEscopo = "
        WHERE f.company_id = ?
    ";

    $crmParams = [$crmEmpresa];

    if ($crmRole === 'Recepcao') {

        $crmEscopo .= "
            AND EXISTS (
                SELECT 1 FROM user_filiais uf
                WHERE uf.user_id = ?
                  AND uf.filial_id = f.id
            )
        ";

        $crmParams[] = $crmUsuario;
    }

    // Listar leads autorizados.

    if ($operacao === 'listar') {

        $leads = $crmExecutar(
            "SELECT
                l.*,
                f.nome AS nome_filial,

                m.id AS matricula_id,
                m.status_matricula,
                m.ativa AS matricula_ativa

             FROM leads l

             INNER JOIN filiais f
                ON f.id = l.filial_id

             LEFT JOIN matriculas m
                ON m.lead_id = l.id

             " . $crmEscopo . "

             ORDER BY l.id DESC",
            $crmParams
        )->fetchAll(PDO::FETCH_ASSOC);

        return;
    }

    // Filiais disponíveis.

    if ($operacao === 'listar_filiais') {

        $filiais = $crmExecutar(
            'SELECT f.id, f.nome
             FROM filiais f' .
            $crmEscopo .
            ' AND f.ativo = 1
              ORDER BY f.nome',
            $crmParams
        )->fetchAll(PDO::FETCH_ASSOC);

        return;
    }

    // Buscar um lead.

    $id = (int) ($id ?? 0);

    if ($operacao === 'buscar') {

        $lead = $crmExecutar(
            "SELECT
                l.*,
                m.id AS matricula_id

             FROM leads l

             INNER JOIN filiais f
                ON f.id = l.filial_id

             LEFT JOIN matriculas m
                ON m.lead_id = l.id
            " .
            $crmEscopo .
            ' AND l.id = ? LIMIT 1',
            array_merge($crmParams, [$id])
        )->fetch(PDO::FETCH_ASSOC) ?: null;

        return;
    }

    if (!in_array(
        $operacao,
        [
            'cadastrar',
            'atualizar',
            'atualizar_status',
            'excluir'
        ],
        true
    )) {
        $crmFalhar('Operação não autorizada.');
    }

    // Serializar alterações do CRM com matrículas
    // e confirmações de recebimento da mesma empresa.

    $pdo->beginTransaction();

    $crmTransacao = true;

    $empresaBloqueada = $crmExecutar(
        'SELECT id FROM companies
         WHERE id = ? FOR UPDATE',
        [$crmEmpresa]
    )->fetchColumn();

    if (!$empresaBloqueada) {
        $crmFalhar('Empresa indisponível.');
    }

    $usuarioBloqueado = $crmExecutar(
        'SELECT id FROM users
         WHERE id = ?
           AND company_id = ?
           AND role = ?
         FOR UPDATE',
        [$crmUsuario, $crmEmpresa, $crmRole]
    )->fetchColumn();

    if (!$usuarioBloqueado) {
        $crmFalhar('Sua sessão não está autorizada.');
    }

    $crmValidarFilial = static function (
        int $filialId,
        bool $exigirAtiva = true
    ) use (
        $crmExecutar,
        $crmEmpresa,
        $crmUsuario,
        $crmRole,
        $crmFalhar
    ): void {

        $filial = $crmExecutar(
            'SELECT id, ativo
             FROM filiais
             WHERE id = ?
               AND company_id = ?
             FOR UPDATE',
            [$filialId, $crmEmpresa]
        )->fetch(PDO::FETCH_ASSOC);

        if (
            !$filial ||
            ($exigirAtiva && (int) $filial['ativo'] !== 1)
        ) {
            $crmFalhar('Selecione uma filial disponível.');
        }

        if ($crmRole === 'Recepcao') {

            $vinculo = $crmExecutar(
                'SELECT filial_id FROM user_filiais
                 WHERE user_id = ?
                   AND filial_id = ?
                 FOR UPDATE',
                [$crmUsuario, $filialId]
            )->fetchColumn();

            if (!$vinculo) {
                $crmFalhar('Filial não autorizada.');
            }
        }
    };

    if ($operacao !== 'cadastrar') {

        $registroLead = $crmExecutar(
            'SELECT l.*
             FROM leads l
             INNER JOIN filiais f
                ON f.id = l.filial_id' .
            $crmEscopo .
            ' AND l.id = ? FOR UPDATE',
            array_merge($crmParams, [$id])
        )->fetch(PDO::FETCH_ASSOC);

        if (!$registroLead) {
            $crmFalhar(
                'Lead não encontrado ou não autorizado.'
            );
        }

        $crmValidarFilial(
            (int) $registroLead['filial_id'],
            false
        );

        $vinculado = $crmExecutar(
            'SELECT id FROM matriculas
             WHERE lead_id = ?
             LIMIT 1 FOR UPDATE',
            [$id]
        )->fetchColumn();

        if (
            $vinculado ||
            in_array(
                $registroLead['status'],
                ['Aguardando pagamento', 'Convertido'],
                true
            )
        ) {
            $crmFalhar(
                'Este lead possui histórico protegido e não pode ser alterado por esta operação.'
            );
        }
    }

    // Cadastrar ou editar dados de contato.

    if (in_array(
        $operacao,
        ['cadastrar', 'atualizar'],
        true
    )) {

        $textoCrm = static fn($v) =>
            is_string($v) ? trim($v) : '';

        $nomeCrm = $textoCrm($dados['nome'] ?? '');
        $telefoneCrm = $textoCrm($dados['telefone'] ?? '');
        $objetivoCrm = $textoCrm($dados['objetivo'] ?? '');
        $campanhaCrm = $textoCrm($dados['campanha'] ?? '');

        $filialCrm = (int) ($dados['filial_id'] ?? 0);

        $statusCrm = $operacao === 'cadastrar'
            ? 'Novo'
            : $textoCrm($dados['status'] ?? '');

        $tamanhoCrm = static function (string $v): int {

            $n = preg_match_all('/./us', $v);

            return $n === false ? PHP_INT_MAX : $n;
        };

        if (
            $nomeCrm === '' ||
            $tamanhoCrm($nomeCrm) > 100
        ) {
            $crmFalhar(
                'Informe o nome completo com até 100 caracteres.'
            );
        }

        if (
            $tamanhoCrm($objetivoCrm) > 100 ||
            $tamanhoCrm($campanhaCrm) > 100
        ) {
            $crmFalhar(
                'Objetivo e campanha devem ter até 100 caracteres.'
            );
        }

        $numeroCrm = preg_replace(
            '/\D/',
            '',
            $telefoneCrm
        );

        if (
            !preg_match('/^[0-9() .-]+$/', $telefoneCrm) ||
            !in_array(strlen($numeroCrm), [10, 11], true)
        ) {
            $crmFalhar('Informe um telefone válido com DDD.');
        }

        $telefoneCrm =
            '(' . substr($numeroCrm, 0, 2) . ') ' .
            substr($numeroCrm, 2, -4) . '-' .
            substr($numeroCrm, -4);

        if (!in_array(
            $statusCrm,
            $crmStatusManuais,
            true
        )) {
            $crmFalhar('Selecione uma etapa autorizada.');
        }

        $crmValidarFilial($filialCrm);

        $params = [
            $filialCrm,
            $nomeCrm,
            $telefoneCrm,
            $objetivoCrm ?: null,
            $campanhaCrm ?: null,
            $statusCrm
        ];

        if ($operacao === 'cadastrar') {

            $crmExecutar(
                "INSERT INTO leads (
                    filial_id,
                    nome,
                    telefone,
                    objetivo,
                    campanha,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?)",
                $params
            );

        } else {

            $params[] = $id;

            $crmExecutar(
                "UPDATE leads
                 SET
                    filial_id = ?,
                    nome = ?,
                    telefone = ?,
                    objetivo = ?,
                    campanha = ?,
                    status = ?
                 WHERE id = ?",
                $params
            );
        }

    } elseif ($operacao === 'atualizar_status') {

        if (!in_array(
            $status ?? '',
            $crmStatusManuais,
            true
        )) {
            $crmFalhar(
                'A conversão depende da confirmação do pagamento.'
            );
        }

        $crmExecutar(
            'UPDATE leads SET status = ?
             WHERE id = ?',
            [$status, $id]
        );

    } elseif ($operacao === 'excluir') {

        if ($crmRole !== 'Admin') {
            $crmFalhar(
                'Somente o administrador pode excluir leads.'
            );
        }

        $crmExecutar(
            'DELETE FROM leads WHERE id = ?',
            [$id]
        );
    }

    $pdo->commit();

    $crmTransacao = false;

} catch (Throwable $e) {

    if (
        $crmTransacao &&
        $pdo->inTransaction()
    ) {
        $pdo->rollBack();
    }

    if ($crmMensagem === '') {

        error_log(
            '[GymFlow][CRM] ' .
            get_class($e) .
            ' | código ' . $e->getCode() .
            ' | linha ' . $e->getLine()
        );
    }

    $erroModel = $crmMensagem ?:
        'Não foi possível concluir a operação. Tente novamente.';

    if ($operacao === 'listar') {
        $leads = [];
    }

    if ($operacao === 'buscar') {
        $lead = null;
    }

    if ($operacao === 'listar_filiais') {
        $filiais = [];
    }
}