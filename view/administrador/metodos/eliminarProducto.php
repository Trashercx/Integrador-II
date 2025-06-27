<?php
session_start();
include '../../../bd/conexion.php'; 


// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    die(json_encode(['success' => false, 'error' => 'No autorizado']));
}

// Verificar si se recibió un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die(json_encode(['success' => false, 'error' => 'ID de producto no válido']));
}

$id = intval($_GET['id']);

// Primero, obtenemos la información del producto para conocer las rutas de las imágenes
$sql_select = "SELECT imagen, imagen2, imagen3, imagen4 FROM productos WHERE id_producto = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $id);
$stmt_select->execute();
$result = $stmt_select->get_result();

if ($row = $result->fetch_assoc()) {
    // Eliminar las imágenes si existen
    $imagenes = [$row['imagen'], $row['imagen2'], $row['imagen3'], $row['imagen4']];
    foreach ($imagenes as $imagen) {
        if (!empty($imagen) && file_exists($imagen)) {
            unlink($imagen);
        }
    }
}

$stmt_select->close();

// Ahora procedemos a eliminar el registro de la base de datos
$sql_delete = "DELETE FROM productos WHERE id_producto = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $id);

// Ejecutar la consulta
if ($stmt_delete->execute()) {
    // Si la eliminación fue exitosa
    echo json_encode(['success' => true]);
} else {
    // Si hubo un error
    echo json_encode(['success' => false, 'error' => $stmt_delete->error]);
}

$stmt_delete->close();
$conn->close();
?>