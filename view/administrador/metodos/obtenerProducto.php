<?php
include '../../../bd/conexion.php';// Asegúrate de incluir tu archivo de conexión a la base de datos


if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM productos WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Producto no encontrado"]);
    }
    $stmt->close();
} else {
    echo json_encode(["error" => "ID no proporcionado"]);
}
$conn->close();
?>