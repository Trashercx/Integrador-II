<?php
$data = file_get_contents("php://input");
file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] 📦 JS dice: $data\n", FILE_APPEND);
echo json_encode(['status' => 'ok']);
