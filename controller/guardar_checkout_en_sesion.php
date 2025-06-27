<?php
session_start();
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($_SESSION['usuario_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$_SESSION['stripe_checkout_data'] = $data;
echo json_encode(['success' => true]);
