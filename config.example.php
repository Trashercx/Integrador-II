<?php
// Configuración de URL base
// Opción 1: Detección automática (recomendado para desarrollo)
function getBaseUrl() {
    if (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $protocol . '://' . $host;
    }
    return 'http://localhost';
}

// Opción 2: URL fija (recomendado para producción)
// Descomenta y usa esta línea para producción en Vercel:
// define('BASE_URL', 'https://tu-proyecto.app');

// Para desarrollo (ngrok, localhost, etc.) usar detección automática:
define('BASE_URL', getBaseUrl());

// API Keys de Stripe (modo test)
$stripe_secret_key = 'sk_test_TU_STRIPE_SECRET_KEY_AQUI';
$stripe_publishable_key = 'pk_test_TU_STRIPE_PUBLISHABLE_KEY_AQUI';

// API Keys de PayPal (modo sandbox)
$paypal_client_id = 'TU_PAYPAL_CLIENT_ID_AQUI';
$paypal_secret = 'TU_PAYPAL_SECRET_AQUI';

// URL base de PayPal
define('PAYPAL_BASE_URL', 'https://api.sandbox.paypal.com'); // Para pruebas
// Para producción usar: define('PAYPAL_BASE_URL', 'https://api.paypal.com');

// Configuración de entorno
define('ENVIRONMENT', 'development'); // Cambiar a 'production' en producción

?>