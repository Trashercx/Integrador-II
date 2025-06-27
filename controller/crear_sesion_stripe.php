<?php
require_once '../vendor/autoload.php';
require_once '../config.php';

\Stripe\Stripe::setApiKey($stripe_secret_key);

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit();
}

// 🧾 LOG: Ingreso al script
file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] ✅ Entrando a crear_sesion_stripe.php\n", FILE_APPEND);

// Leer carrito
$carrito = json_decode($_COOKIE['carrito'] ?? '[]', true);
if (empty($carrito)) {
    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] ❌ Carrito vacío\n", FILE_APPEND);
    die("Carrito vacío");
}

// Recibir datos desde JS
$datos = json_decode(file_get_contents("php://input"), true);

// LOG: Datos recibidos
file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] 🧾 Datos recibidos: " . print_r($datos, true) . "\n", FILE_APPEND);

// Proteger si $datos es null
if (!$datos) {
    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] ⚠️ Los datos están vacíos o mal formateados.\n", FILE_APPEND);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$_SESSION['stripe_checkout_data'] = $datos;

// Armar line_items
$line_items = [];
foreach ($carrito as $item) {
    $line_items[] = [
        'price_data' => [
            'currency' => 'pen',
            'product_data' => ['name' => $item['nombre']],
            'unit_amount' => intval($item['precio'] * 100),
        ],
        'quantity' => $item['cantidad'],
    ];
}

// Costo de envío
switch ($datos['metodo_envio']) {
    case 'express': $envio = 1850; break;
    case 'standard': $envio = 1120; break;
    case 'pickup': $envio = 0; break;
    default: $envio = 0;
}

if ($envio > 0) {
    $line_items[] = [
        'price_data' => [
            'currency' => 'pen',
            'product_data' => ['name' => 'Costo de Envío'],
            'unit_amount' => $envio,
        ],
        'quantity' => 1,
    ];
}

// LOG: Items finales
file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] 📦 Line items: " . print_r($line_items, true) . "\n", FILE_APPEND);

try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => BASE_URL . '/controller/finalizar_pago_stripe.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => BASE_URL . '/view/checkout.php',
        'metadata' => [
            'nombre'        => $datos['nombre'] ?? '',
            'telefono'      => $datos['telefono'] ?? '',
            'direccion'     => $datos['direccion'] ?? '',
            'ciudad'        => $datos['ciudad'] ?? '',
            'distrito'      => $datos['distrito'] ?? '',
            'metodo_envio'  => $datos['metodo_envio'] ?? 'standard',
        ],
    ]);

    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] ✅ Sesión Stripe creada: " . $session->id . "\n", FILE_APPEND);

    echo json_encode(['id' => $session->id]);

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] ❌ Error Stripe: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['error' => 'No se pudo crear la sesión']);
}
