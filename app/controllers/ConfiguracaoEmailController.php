
<?php

require_once __DIR__ . '/../../config/sessao.php';
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/ConfiguracaoEmail.php';

verificarRole(['Admin']);

if (empty($_SESSION['csrf_config_email'])) {
    $_SESSION['csrf_config_email'] = bin2hex(random_bytes(32));
}

$empresaId = obterEmpresaLogada();
$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
$acao = $_GET['acao'] ?? 'index';

$erro = '';
$sucesso = '';

/**
 * Carrega as configurações locais do GymFlow.
 * Este arquivo é somente lido, nunca alterado por este controller.
 */
$carregarEmailLocal = static function (): array {
    $arquivo = __DIR__ . '/../../config/email.local.php';

    if (!is_file($arquivo)) {
        return [];
    }

    try {
        $dados = require $arquivo;

        return is_array($dados) ? $dados : [];
    } catch (\Throwable $e) {
        error_log(
            'GymFlow: não foi possível carregar as configurações locais de e-mail.'
        );

        return [];
    }
};

if ($acao === 'index') {
    $configuracao = buscarConfiguracaoEmail(
        $pdo,
        (int) $empresaId
    ) ?? [];

    // Preserva os valores personalizados salvos no banco.
    $configuracaoPersonalizada = $configuracao;

    // Carrega o SMTP padrão do GymFlow.
    $emailLocal = $carregarEmailLocal();

    $configuracaoLocal = [
        'usar_personalizado' => 0,
        'email_remetente' => $emailLocal['username'] ?? '',
        'nome_remetente' => 'GymFlow',
        'smtp_host' => $emailLocal['host'] ?? '',
        'smtp_porta' => (int) ($emailLocal['port'] ?? 587),
        'smtp_seguranca' => 'tls',
        'smtp_usuario' => $emailLocal['username'] ?? '',
    ];

    // Se o personalizado estiver desativado, exibe os dados locais.
    // Os valores personalizados continuam preservados na variável
    // $configuracaoPersonalizada e no banco de dados.
    if (empty($configuracao['usar_personalizado'])) {
        $configuracao = array_merge(
            $configuracao,
            $configuracaoLocal
        );
    }

    $tituloPagina = 'Configurações de e-mail';

    require __DIR__ . '/../views/configuracao_email/index.php';
    exit;
}

if ($acao === 'salvar') {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        exit('Método não permitido.');
    }

    // Validação CSRF.
    $tokenSessao = $_SESSION['csrf_config_email'] ?? '';
    $tokenFormulario = $_POST['csrf_token'] ?? '';

    if (
        !is_string($tokenSessao) ||
        $tokenSessao === '' ||
        !is_string($tokenFormulario) ||
        !hash_equals($tokenSessao, $tokenFormulario)
    ) {
        http_response_code(403);
        exit('Requisição inválida. Atualize a página e tente novamente.');
    }

    // Recupera os dados atuais para preservar a senha já salva.
    $configAtual = buscarConfiguracaoEmail(
        $pdo,
        (int) $empresaId
    ) ?? [];

    $usarPersonalizado = isset($_POST['usar_personalizado']) ? 1 : 0;

    $senhaSmtp = $usarPersonalizado === 1
        ? ($_POST['smtp_senha'] ?? '')
        : '';

    if (!is_string($senhaSmtp)) {
        http_response_code(422);
        exit('Senha SMTP inválida.');
    }

    $segurancasPermitidas = ['tls', 'ssl', 'none'];

    $email = '';
    $nome = '';
    $host = '';
    $porta = 587;
    $seguranca = 'tls';
    $usuario = '';

    if ($usarPersonalizado === 1) {
        $email = trim((string) ($_POST['email_remetente'] ?? ''));
        $nome = trim((string) ($_POST['nome_remetente'] ?? ''));
        $host = trim((string) ($_POST['smtp_host'] ?? ''));

        $portaValidada = filter_var(
            $_POST['smtp_porta'] ?? null,
            FILTER_VALIDATE_INT
        );

        $seguranca = $_POST['smtp_seguranca'] ?? 'tls';
        $usuario = trim((string) ($_POST['smtp_usuario'] ?? ''));

        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            $nome === '' ||
            $host === '' ||
            $portaValidada === false ||
            $portaValidada === null ||
            $portaValidada < 1 ||
            $portaValidada > 65535 ||
            !is_string($seguranca) ||
            !in_array($seguranca, $segurancasPermitidas, true) ||
            $usuario === ''
        ) {
            http_response_code(422);
            exit('Confira os campos SMTP e tente novamente.');
        }

        $porta = $portaValidada;

        $temSenhaSalva =
            (int) ($configAtual['tem_senha_smtp'] ?? 0) === 1;

        if (trim($senhaSmtp) === '' && !$temSenhaSalva) {
            http_response_code(422);
            exit(
                'Informe a senha SMTP para ativar o envio personalizado.'
            );
        }
    } else {
        /*
         * SMTP personalizado desativado.
         * Mantém os dados personalizados existentes no banco.
         * Não modifica o arquivo email.local.php.
         */
        $emailLocal = $carregarEmailLocal();

        $valorExistente = static function (
            array $config,
            string $campo,
            string $padrao = ''
        ): string {
            $valor = $config[$campo] ?? '';

            if (is_string($valor) || is_numeric($valor)) {
                $valor = trim((string) $valor);

                if ($valor !== '') {
                    return $valor;
                }
            }

            return $padrao;
        };

        $emailLocalUsuario = trim(
            (string) ($emailLocal['username'] ?? '')
        );

        $email = $valorExistente(
            $configAtual,
            'email_remetente',
            $emailLocalUsuario
        );

        $nome = $valorExistente(
            $configAtual,
            'nome_remetente',
            'GymFlow'
        );

        $host = $valorExistente(
            $configAtual,
            'smtp_host',
            trim((string) ($emailLocal['host'] ?? ''))
        );

        $portaAtual = filter_var(
            $configAtual['smtp_porta'] ?? null,
            FILTER_VALIDATE_INT
        );

        $portaLocal = filter_var(
            $emailLocal['port'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $portaAtual !== false &&
            $portaAtual !== null &&
            $portaAtual >= 1 &&
            $portaAtual <= 65535
        ) {
            $porta = $portaAtual;
        } elseif (
            $portaLocal !== false &&
            $portaLocal !== null &&
            $portaLocal >= 1 &&
            $portaLocal <= 65535
        ) {
            $porta = $portaLocal;
        }

        $segurancaAtual = $configAtual['smtp_seguranca'] ?? '';

        $seguranca = is_string($segurancaAtual) &&
            in_array($segurancaAtual, $segurancasPermitidas, true)
                ? $segurancaAtual
                : 'tls';

        $usuario = $valorExistente(
            $configAtual,
            'smtp_usuario',
            $emailLocalUsuario !== '' ? $emailLocalUsuario : $email
        );
    }

    // Validação das cores do e-mail.
    $modoCores = $_POST['modo_cores_email'] ?? 'personalizado';

    $coresPermitidas = [
        'cor_email_fundo' => '#0b0b0b',
        'cor_email_cartao' => '#151515',
        'cor_email_destaque' => '#ffb000',
        'cor_email_botao' => '#ffb000',
        'cor_email_texto_botao' => '#151515',
    ];

    if (
        !is_string($modoCores) ||
        !in_array($modoCores, ['marca', 'personalizado'], true)
    ) {
        http_response_code(422);
        exit('Modo de cores inválido.');
    }

    $cores = [];

    foreach ($coresPermitidas as $campo => $padrao) {
        $cor = $_POST[$campo] ?? $padrao;

        if (
            !is_string($cor) ||
            !preg_match('/^#[0-9a-fA-F]{6}$/', $cor)
        ) {
            http_response_code(422);
            exit('Uma das cores informadas é inválida.');
        }

        $cores[$campo] = strtolower($cor);
    }

    // O modelo é responsável por criptografar a senha SMTP.
    // Uma senha em branco deve preservar a senha já armazenada.
    $dados = [
        'usar_personalizado' => $usarPersonalizado,
        'email_remetente' => $email,
        'nome_remetente' => $nome,
        'smtp_host' => $host,
        'smtp_porta' => (int) $porta,
        'smtp_seguranca' => $seguranca,
        'smtp_usuario' => $usuario,
        'smtp_senha' => $senhaSmtp,
        'modo_cores_email' => $modoCores,
        ...$cores,
    ];

    salvarConfiguracaoEmail(
        $pdo,
        (int) $empresaId,
        $usuarioId,
        $dados
    );

    header(
        'Location: ' . BASE_URL .
        '/app/controllers/ConfiguracaoEmailController.php?msg=salvo'
    );
    exit;
}

http_response_code(404);
echo 'Ação não encontrada.';
