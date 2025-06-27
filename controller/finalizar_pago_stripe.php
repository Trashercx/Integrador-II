<?php
session_start();
require_once '../config.php';
require_once '../bd/conexion.php';
require_once '../vendor/autoload.php'; // Asegúrate de tener Stripe cargado

\Stripe\Stripe::setApiKey($stripe_secret_key);


if (!isset($_GET['session_id'])) {
    echo "Falta session_id.";
    exit;
}

$session_id = $_GET['session_id'];


try {
    $checkout_session = \Stripe\Checkout\Session::retrieve($session_id);
   
} catch (\Exception $e) {
    
    echo "Error al verificar el pago.";
    exit;
}

// Validar pago
if ($checkout_session->payment_status !== 'paid') {
    
    echo "El pago no fue completado.";
    exit;
}

// Recuperar metadata enviada desde la creación de sesión
$nombre   = $checkout_session->metadata->nombre ?? '';
$telefono = $checkout_session->metadata->telefono ?? '';
$direccion = $checkout_session->metadata->direccion ?? '';
$ciudad   = $checkout_session->metadata->ciudad ?? '';
$distrito = $checkout_session->metadata->distrito ?? '';
$metodo_envio = $checkout_session->metadata->metodo_envio ?? 'standard';
$metodo_pago  = 'credit-card';

// Guardar en sesión (para procesar_compra.php)
$_SESSION['stripe_checkout_data'] = [
    'nombre' => $nombre,
    'telefono' => $telefono,
    'direccion' => $direccion,
    'ciudad' => $ciudad,
    'distrito' => $distrito,
    'metodo_envio' => $metodo_envio,
    'metodo_pago' => $metodo_pago
];



// Redirigir a procesar_compra.php (flujo normal como contraentrega)

header("Location: procesar_compra.php");
exit;
