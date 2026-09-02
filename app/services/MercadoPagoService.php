<?php
// app/services/MercadoPagoService.php

require_once __DIR__ . '/../../config/config.php';

class MercadoPagoService
{
    private string $accessToken;
    private string $publicKey;
    private bool $isConfigured;

    public function __construct()
    {
        $this->accessToken = defined('MP_ACCESS_TOKEN') ? MP_ACCESS_TOKEN : '';
        $this->publicKey   = defined('MP_PUBLIC_KEY') ? MP_PUBLIC_KEY : '';
        
        // Verifica se as credenciais foram preenchidas com valores reais diferentes do padrão
        $this->isConfigured = !empty($this->accessToken) && 
                              strpos($this->accessToken, 'TEST-SEU-ACCESS-TOKEN') === false;
    }

    /**
     * Retorna a chave pública para utilização no frontend (ex: Checkout Bricks)
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Indica se a API está configurada com credenciais reais
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Processa a cobrança via PIX ou Cartão de Crédito
     *
     * @param array $params [
     *     'matricula_id' => int,
     *     'metodo'       => string ('pix' | 'cartao'),
     *     'valor'        => float,
     *     'aluno'        => ['nome' => ..., 'email' => ..., 'cpf' => ...],
     *     'token_cartao' => string (opcional, gerado pelo Payment Brick no frontend)
     * ]
     * @return array [
     *     'sucesso'         => bool,
     *     'status'          => string ('aprovado' | 'pendente' | 'recusado'),
     *     'mercado_pago_id' => string,
     *     'metodo'          => string,
     *     'pix_copia_cola'  => string|null,
     *     'pix_qr_base64'   => string|null,
     *     'mensagem'        => string
     * ]
     */
    public function processarPagamento(array $params): array
    {
        $metodo = strtolower($params['metodo'] ?? 'pix');
        $valor  = (float) ($params['valor'] ?? 0.0);
        $aluno  = $params['aluno'] ?? [];
        $matriculaId = (int) ($params['matricula_id'] ?? 0);

        // Se houver credenciais reais do Mercado Pago configuradas, consome a API oficial
        if ($this->isConfigured) {
            return $this->executarApiMercadoPago($metodo, $valor, $aluno, $matriculaId, $params);
        }

        // Modo Simulação Educacional / Demonstração KISS
        // Permite testar o fluxo completo de matrícula sem bloquear o desenvolvimento acadêmico
        return $this->executarModoSimulacao($metodo, $valor, $matriculaId);
    }

    /**
     * Comunicação real com a API v1/payments do Mercado Pago
     */
    private function executarApiMercadoPago(string $metodo, float $valor, array $aluno, int $matriculaId, array $params): array
    {
        $endpoint = 'https://api.mercadopago.com/v1/payments';

        $nomePartes = explode(' ', trim($aluno['nome'] ?? 'Aluno Gymflow'), 2);
        $primeiroNome = $nomePartes[0];
        $sobrenome = $nomePartes[1] ?? 'Silva';
        $cpfLimpo = preg_replace('/\D/', '', $aluno['cpf'] ?? '');

        $payload = [
            'transaction_amount' => round($valor, 2),
            'description'        => "Matrícula Gymflow #{$matriculaId}",
            'external_reference' => (string) $matriculaId,
            'payer'              => [
                'email'          => $aluno['email'] ?? 'aluno@gymflow.com',
                'first_name'     => $primeiroNome,
                'last_name'      => $sobrenome,
                'identification' => [
                    'type'   => 'CPF',
                    'number' => $cpfLimpo,
                ],
            ],
        ];

        if ($metodo === 'pix') {
            $payload['payment_method_id'] = 'pix';
        } else {
            // Cartão de crédito: utiliza o token gerado com segurança pelo Mercado Pago Bricks no frontend
            $payload['token']             = $params['token_cartao'] ?? '';
            $payload['installments']      = (int) ($params['parcelas'] ?? 1);
            $payload['payment_method_id'] = $params['payment_method_id'] ?? 'credit_card';
        }

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken,
            'X-Idempotency-Key: gymflow-' . $matriculaId . '-' . time()
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $data = json_decode($response, true);
            $statusMP = $data['status'] ?? 'pending';

            $statusFinal = match ($statusMP) {
                'approved' => 'aprovado',
                'rejected' => 'recusado',
                default    => 'pendente'
            };

            return [
                'sucesso'         => true,
                'status'          => $statusFinal,
                'mercado_pago_id' => (string) ($data['id'] ?? uniqid('mp_')),
                'metodo'          => $metodo,
                'pix_copia_cola'  => $data['point_of_interaction']['transaction_data']['qr_code'] ?? null,
                'pix_qr_base64'   => $data['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
                'mensagem'        => 'Pagamento processado com sucesso via Mercado Pago.'
            ];
        }

        // Caso a chamada falhe por credencial inválida, faz fallback amigável
        return [
            'sucesso'         => false,
            'status'          => 'pendente',
            'mercado_pago_id' => 'ERRO-API-' . time(),
            'metodo'          => $metodo,
            'pix_copia_cola'  => null,
            'pix_qr_base64'   => null,
            'mensagem'        => 'Não foi possível conectar à API do Mercado Pago. Verifique as credenciais.'
        ];
    }

    /**
     * Modo de simulação para testes e apresentação acadêmica
     */
    private function executarModoSimulacao(string $metodo, float $valor, int $matriculaId): array
    {
        $fakeId = 'MP-SIM-' . strtoupper(bin2hex(random_bytes(4)));

        if ($metodo === 'pix') {
            return [
                'sucesso'         => true,
                'status'          => 'aprovado', // Em simulação de demonstração, aprova para fluxo completo
                'mercado_pago_id' => $fakeId,
                'metodo'          => 'pix',
                'pix_copia_cola'  => '00020126580014br.gov.bcb.pix0136' . uniqid() . '520400005303986540' . number_format($valor, 2, '.', '') . '5802BR5915GYMFLOW REDE6009SAO PAULO62070503***6304ABCD',
                'pix_qr_base64'   => null,
                'mensagem'        => 'Pagamento PIX simulado com sucesso (Modo Demonstração).'
            ];
        }

        return [
            'sucesso'         => true,
            'status'          => 'aprovado',
            'mercado_pago_id' => $fakeId,
            'metodo'          => 'cartao',
            'pix_copia_cola'  => null,
            'pix_qr_base64'   => null,
            'mensagem'        => 'Pagamento via Cartão de Crédito simulado com sucesso (Modo Demonstração).'
        ];
    }
}
