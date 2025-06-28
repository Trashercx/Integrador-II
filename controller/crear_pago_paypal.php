<?php
session_start();
require_once '../config.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../view/login.php');
    exit;
}

// Verificar que tenemos datos del checkout
if (!isset($_SESSION['paypal_checkout_data'])) {
    header('Location: ../view/checkout.php?error=datos_no_disponibles');
    exit;
}

$checkout_data = $_SESSION['paypal_checkout_data'];

// Leer el carrito
$carrito = json_decode($_COOKIE['carrito'] ?? '[]', true);
if (empty($carrito)) {
    header('Location: ../view/checkout.php?error=carrito_vacio');
    exit;
}

// Calcular totales
$subtotal = array_reduce($carrito, fn($sum, $i) => $sum + ($i['precio'] * $i['cantidad']), 0);
$envio = match($checkout_data['metodo_envio']) {
    'express' => 18.50,
    'standard' => 11.20,
    'pickup' => 0.00,
    default => 0.00
};
$total = $subtotal + $envio;

// Función para obtener token de acceso de PayPal
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

// Función para crear el pago en PayPal
function createPayPalPayment($access_token, $total, $carrito, $checkout_data) {
    $url = 'https://api.sandbox.paypal.com/v1/payments/payment';
    
    // Crear items del carrito para PayPal
    $items = [];
    foreach ($carrito as $item) {
        $items[] = [
            'name' => $item['nombre'],
            'sku' => 'SKU-' . ($item['id_producto'] ?? $item['id']),
            'price' => number_format($item['precio'], 2, '.', ''),
            'currency' => 'USD',
            'quantity' => $item['cantidad']
        ];
    }
    
    // Si hay envío, agregarlo como item
    $envio = match($checkout_data['metodo_envio']) {
        'express' => 18.50,
        'standard' => 11.20,
        'pickup' => 0.00,
        default => 0.00
    };
    
    if ($envio > 0) {
        $items[] = [
            'name' => 'Envío - ' . ucfirst($checkout_data['metodo_envio']),
            'sku' => 'SHIPPING',
            'price' => number_format($envio, 2, '.', ''),
            'currency' => 'USD',
            'quantity' => 1
        ];
    }
    
    $data = [
        'intent' => 'sale',
        'payer' => [
            'payment_method' => 'paypal'
        ],
        'transactions' => [
            [
                'amount' => [
                    'total' => number_format($total, 2, '.', ''),
                    'currency' => 'USD'
                ],
                'description' => 'Compra en MARYCRIST',
                'item_list' => [
                    'items' => $items
                ]
            ]
        ],
        'redirect_urls' => [
            'return_url' => BASE_URL . '/controller/finalizar_pago_paypal.php',
            'cancel_url' => BASE_URL . '/view/checkout.php?error=pago_cancelado'
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 201) {
        return json_decode($response, true);
    }
    
    return false;
}

try {
    // Obtener token de acceso
    $access_token = getPayPalAccessToken();
    if (!$access_token) {
        throw new Exception('Error al conectar con PayPal');
    }
    
    // Crear el pago
    $payment = createPayPalPayment($access_token, $total, $carrito, $checkout_data);
    if (!$payment) {
        throw new Exception('Error al crear el pago en PayPal');
    }
    
    // Buscar la URL de aprobación
    $approval_url = null;
    foreach ($payment['links'] as $link) {
        if ($link['rel'] === 'approval_url') {
            $approval_url = $link['href'];
            break;
        }
    }
    
    if (!$approval_url) {
        throw new Exception('No se pudo obtener la URL de PayPal');
    }
    
    // Guardar el ID del pago en sesión para verificación posterior
    $_SESSION['paypal_payment_id'] = $payment['id'];
    
    // Redirigir a PayPal
    header('Location: ' . $approval_url);
    exit;
    
} catch (Exception $e) {
    error_log("Error en crear_pago_paypal.php: " . $e->getMessage());
    header('Location: ../view/checkout.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>