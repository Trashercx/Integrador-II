<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Verificar que es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Leer los datos JSON del checkout
$data = json_decode(file_get_contents("php://input"), true);

// Validar que tenemos todos los datos necesarios
$required_fields = ['direccion', 'ciudad', 'telefono', 'metodo_envio'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Campo requerido: $field"]);
        exit;
    }
}

// Verificar que hay un carrito válido
$carrito = json_decode($_COOKIE['carrito'] ?? '[]', true);
if (empty($carrito)) {
    echo json_encode(['success' => false, 'message' => 'Carrito vacío']);
    exit;
}

try {
    // Guardar los datos del checkout en la sesión específicamente para PayPal
    $_SESSION['paypal_checkout_data'] = [
        'direccion' => trim($data['direccion']),
        'ciudad' => trim($data['ciudad']),
        'distrito' => trim($data['distrito'] ?? $data['ciudad']),
        'telefono' => trim($data['telefono']),
        'metodo_envio' => $data['metodo_envio'],
        'metodo_pago' => 'paypal',
        'timestamp' => time() // Para verificar que los datos no sean muy antiguos
    ];
    
    echo json_encode(['success' => true, 'message' => 'Datos guardados correctamente']);
    
} catch (Exception $e) {
    error_log("Error en guardar_checkout_en_sesion_P.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>