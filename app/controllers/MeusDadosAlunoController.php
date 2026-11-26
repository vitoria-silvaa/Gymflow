<?php
// app/controllers/MeusDadosAlunoController.php

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Aluno']);

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/MeusDadosAluno.php';

header('Cache-Control: no-store, private');

// =============================================
// DADOS DA SESSÃO
// =============================================

$perfilUsuarioId = (int) (
    $_SESSION['usuario_id'] ?? 0
);

$perfilAlunoId = (int) (
    $_SESSION['aluno_id'] ?? 0
);

$perfilEmpresaId = (int) (
    $_SESSION['company_id'] ?? 0
);

if (
    $perfilUsuarioId <= 0 ||
    $perfilAlunoId <= 0 ||
    $perfilEmpresaId <= 0
) {
    http_response_code(403);
    exit('Sessão inválida.');
}

$urlMeusDados = BASE_URL .
    '/app/controllers/PortalAlunoController.php?acao=meus_dados';

// =============================================
// PROTEÇÃO CSRF
// =============================================

if (
    empty($_SESSION['meus_dados_csrf']) ||
    !is_string($_SESSION['meus_dados_csrf'])
) {

    $_SESSION['meus_dados_csrf'] =
        bin2hex(random_bytes(32));
}

$csrfMeusDados = $_SESSION['meus_dados_csrf'];

// =============================================
// VALIDAR ACESSO
// =============================================

try {

    $meusDados = consultarMeusDadosAluno(
        $pdo,
        $perfilUsuarioId,
        $perfilAlunoId,
        $perfilEmpresaId
    );

    if (!$meusDados) {

        http_response_code(403);

        exit('Não foi possível validar seu cadastro.');
    }
} catch (Throwable $e) {

    error_log(
        '[GymFlow][MeusDados] ' .
            $e->getMessage()
    );

    http_response_code(500);

    exit('Não foi possível carregar seus dados.');
}

// =============================================
// PROCESSAR FORMULÁRIOS
// =============================================

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($metodo === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($csrfMeusDados, $token)
    ) {

        http_response_code(403);

        exit('Solicitação inválida. Atualize a página.');
    }

    $operacaoPerfil = $_POST['operacao'] ?? '';

    // Ler apenas valores textuais.

    $lerCampo = static function (string $campo): string {

        $valor = $_POST[$campo] ?? '';

        if (!is_string($valor)) {

            throw new DomainException(
                'Informações inválidas.'
            );
        }

        return $valor;
    };

    try {

        // =====================================
        // ATUALIZAR CONTATO
        // =====================================

        if ($operacaoPerfil === 'atualizar_contato') {

            $telefone = trim(
                $lerCampo('telefone')
            );

            $endereco = trim(
                $lerCampo('endereco')
            );

            // Manter somente os números do telefone.

            $telefone = preg_replace(
                '/\D/',
                '',
                $telefone
            );

            $quantidadeDigitos = strlen($telefone);

            if (
                $quantidadeDigitos !== 10 &&
                $quantidadeDigitos !== 11
            ) {

                throw new DomainException(
                    'Informe um telefone válido com DDD.'
                );
            }

            if ($quantidadeDigitos === 11) {

                $telefone = sprintf(
                    '(%s) %s-%s',
                    substr($telefone, 0, 2),
                    substr($telefone, 2, 5),
                    substr($telefone, 7)
                );
            } else {

                $telefone = sprintf(
                    '(%s) %s-%s',
                    substr($telefone, 0, 2),
                    substr($telefone, 2, 4),
                    substr($telefone, 6)
                );
            }

            if (mb_strlen($endereco) > 500) {

                throw new DomainException(
                    'O endereço deve possuir até 500 caracteres.'
                );
            }

            atualizarContatoAluno(
                $pdo,
                $perfilUsuarioId,
                $perfilAlunoId,
                $perfilEmpresaId,
                $telefone,
                $endereco
            );

            $_SESSION['meus_dados_mensagem'] = [
                'tipo' => 'sucesso',
                'texto' => 'Seus dados foram atualizados!'
            ];
        }

        // =====================================
        // ALTERAR SENHA
        // =====================================

        elseif ($operacaoPerfil === 'alterar_senha') {

            $senhaAtual = $lerCampo('senha_atual');

            $novaSenha = $lerCampo('nova_senha');

            $confirmacao = $lerCampo('confirmar_senha');

            if ($senhaAtual === '') {

                throw new DomainException(
                    'Informe sua senha atual.'
                );
            }

            if (
                mb_strlen($novaSenha) < 8 ||
                strlen($novaSenha) > 72 ||
                strpos($novaSenha, "\0") !== false
            ) {

                throw new DomainException(
                    'A nova senha deve ter pelo menos 8 caracteres ' .
                        'e respeitar o limite de 72 bytes.'
                );
            }

            if ($novaSenha !== $confirmacao) {

                throw new DomainException(
                    'As novas senhas não coincidem.'
                );
            }

            alterarSenhaMeusDados(
                $pdo,
                $perfilUsuarioId,
                $perfilAlunoId,
                $perfilEmpresaId,
                $senhaAtual,
                $novaSenha
            );

            // Invalidar login automático anterior.

            $https = !empty($_SERVER['HTTPS'])
                && $_SERVER['HTTPS'] !== 'off';

            setcookie('gymflow_remember', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => $https,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            unset($_COOKIE['gymflow_remember']);

            session_regenerate_id(true);

            $_SESSION['meus_dados_csrf'] =
                bin2hex(random_bytes(32));

            $_SESSION['meus_dados_mensagem'] = [
                'tipo' => 'sucesso',
                'texto' => 'Sua senha foi alterada com sucesso!'
            ];
        } else {

            throw new DomainException(
                'Operação inválida.'
            );
        }
    } catch (DomainException $e) {

        $_SESSION['meus_dados_mensagem'] = [
            'tipo' => 'erro',
            'texto' => $e->getMessage()
        ];
    } catch (Throwable $e) {

        error_log(
            '[GymFlow][MeusDados] ' .
                $e->getMessage()
        );

        $_SESSION['meus_dados_mensagem'] = [
            'tipo' => 'erro',
            'texto' => 'Não foi possível salvar as alterações.'
        ];
    }

    // Evita reenvio do formulário ao atualizar a página.

    header('Location: ' . $urlMeusDados, true, 303);

    exit;
}

// =============================================
// PERMITIR SOMENTE GET E POST
// =============================================

if ($metodo !== 'GET') {

    header('Allow: GET, POST');

    http_response_code(405);

    exit('Método não permitido.');
}

// =============================================
// MENSAGEM TEMPORÁRIA
// =============================================

$mensagemMeusDados =
    $_SESSION['meus_dados_mensagem'] ?? null;

unset($_SESSION['meus_dados_mensagem']);

// =============================================
// EXIBIR PÁGINA
// =============================================

require __DIR__ . '/../views/portal_aluno/meus_dados.php';

exit;
