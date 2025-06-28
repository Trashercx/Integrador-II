<?php
session_start();
require_once '../config.php';

// Verificar que venimos de PayPal con los parámetros correctos
if (!isset($_GET['paymentId']) || !isset($_GET['PayerID'])) {
    header('Location: ../view/checkout.php?error=pago_cancelado');
    exit;
}

$payment_id = $_GET['paymentId'];
$payer_id = $_GET['PayerID'];

// Verificar que tenemos los datos del checkout en sesión
if (!isset($_SESSION['paypal_checkout_data'])) {
    header('Location: ../view/checkout.php?error=sesion_expirada');
    exit;
}

// Obtener token de acceso de PayPal
function getPayPalAccessToken() {
    global $paypal_client_id, $paypal_secret;
    
    $url = 'https://api.sandbox.paypal.com/v1/oauth2/token';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_USERPWD, $paypal_client_id . ':' . $paypal_secret);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Accept-Language: en_US'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        return $data['access_token'];
    }
    
    return false;
}

// Ejecutar el pago en PayPal
function executePayPalPayment($payment_id, $payer_id, $access_token) {
    $url = "https://api.sandbox.paypal.com/v1/payments/payment/$payment_id/execute";
    
    $data = json_encode([
        'payer_id' => $payer_id
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        return json_decode($response, true);
    }
    
    return false;
}

try {
    // Obtener token de acceso
    $access_token = getPayPalAccessToken();
    if (!$access_token) {
        throw new Exception('Error al obtener token de PayPal');
    }
    
    // Ejecutar el pago
    $payment_result = executePayPalPayment($payment_id, $payer_id, $access_token);
    if (!$payment_result) {
        throw new Exception('Error al ejecutar el pago en PayPal');
    }
    
    // Verificar que el pago fue aprobado
    if ($payment_result['state'] !== 'approved') {
        throw new Exception('El pago no fue aprobado por PayPal');
    }
    
    // Obtener información de la transacción
    $transaction = $payment_result['transactions'][0];
    $related_resources = $transaction['related_resources'][0];
    $sale = $related_resources['sale'];
    
    // Agregar información de PayPal a los datos del checkout
    $_SESSION['paypal_checkout_data']['paypal_transaction_id'] = $sale['id'];
    $_SESSION['paypal_checkout_data']['paypal_order_id'] = $payment_id;
    $_SESSION['paypal_checkout_data']['paypal_payer_id'] = $payer_id;
    $_SESSION['paypal_checkout_data']['metodo_pago'] = 'paypal';
    
    // Procesar la compra llamando al archivo existente
    include '../controller/procesar_compra.php';
    
} catch (Exception $e) {
    error_log("Error en finalizar_pago_paypal.php: " . $e->getMessage());
    header('Location: ../view/checkout.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>