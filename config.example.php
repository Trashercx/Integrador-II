<?php
// Detectar automáticamente la URL base
function getBaseUrl() {
    if (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $protocol . '://' . $host;
    }
    return 'http://localhost';
}

define('BASE_URL', getBaseUrl());

$stripe_secret_key = 'sk_test_TU_STRIPE_SECRET_KEY';
$stripe_publishable_key = 'pk_test_TU_STRIPE_PUBLISHABLE_KEY';
$paypal_client_id = 'TU_PAYPAL_CLIENT_ID';
$paypal_secret = 'TU_PAYPAL_SECRET';

?>