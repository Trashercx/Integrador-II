<?php
session_start();
include '../../../bd/conexion.php'; 


// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    die(json_encode(['success' => false, 'error' => 'No autorizado']));
}

// Verificar si se recibió un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die(json_encode(['success' => false, 'error' => 'ID de usuario no válido']));
}

$id = intval($_GET['id']);

// Preparar la consulta SQL
$sql = "DELETE FROM administrador WHERE id_administrador = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

// Ejecutar la consulta
if ($stmt->execute()) {
    // Si la eliminación fue exitosa
    echo json_encode(['success' => true]);
} else {
    // Si hubo un error
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>