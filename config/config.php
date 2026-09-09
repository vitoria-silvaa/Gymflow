<?php
// config/config.php
// Configurações globais da aplicação Gymflow

// =====================================================================
// CREDENCIAIS MERCADO PAGO
// =====================================================================
// Obtenha suas credenciais no Painel de Desenvolvedores do Mercado Pago:
// https://www.mercadopago.com.br/developers/panel/app
// =====================================================================

// Chave Pública (utilizada pelo frontend / Checkout Bricks)
if (!defined('MP_PUBLIC_KEY')) {
    define('MP_PUBLIC_KEY', getenv('MP_PUBLIC_KEY') ?: 'TEST-SEU-PUBLIC-KEY-AQUI');
}

// Access Token (utilizado EXCLUSIVAMENTE pelo backend; NUNCA expor no frontend)
if (!defined('MP_ACCESS_TOKEN')) {
    define('MP_ACCESS_TOKEN', getenv('MP_ACCESS_TOKEN') ?: 'TEST-SEU-ACCESS-TOKEN-AQUI');
}

// Modo de simulação/teste quando credenciais reais não estiverem configuradas
if (!defined('MP_SANDBOX_MODE')) {
    define('MP_SANDBOX_MODE', true);
}
