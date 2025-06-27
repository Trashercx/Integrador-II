<?php
require_once '../bd/conexion.php';

if (!isset($_GET['id_compra'])) {
    http_response_code(400);
    echo "ID de compra no especificado.";
    exit;
}

$id_compra = intval($_GET['id_compra']);

$sql = "SELECT archivo_pdf FROM comprobante WHERE id_compra = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_compra);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(404);
    echo "Comprobante no encontrado.";
    exit;
}

$stmt->bind_result($archivo_pdf);
$stmt->fetch();

// Importante: limpiar cualquier posible salida previa
ob_clean();
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=boleta_{$id_compra}.pdf");
header("Content-Length: " . strlen($archivo_pdf));

echo $archivo_pdf;
exit;
