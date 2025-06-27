<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <style>
        body {

            font-family: 'Poppins', sans-serif;

        }
    </style>

</head>

</html>

<script>
    function alertExito() {
        console.log("Ejecutando alertExito"); // Mensaje de depuración
        Swal.fire({
            icon: "success",
            title: "Realizado con éxito!",
            timer: 1700,
            padding: "3em",
            showConfirmButton: false,
        }).then(() => {
            window.location.href = '../adminProductos.php';
        });
    }

    function alertError() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El producto ya está registrado.',
        }).then(() => {
            window.location.href = '../adminProductos.php';
        });
    }
    function alertError2(){
        Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error: " . $error . "',
            }).then(() => {
                window.location.href = '../adminProductos.php';
            });
    }
</script>


<?php
session_start();

include '../../../bd/conexion.php'; 


if (!isset($_SESSION['usuario_id'])) {
    die("Acceso no autorizado");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $categoria = $_POST['categoria'];

    // Función para procesar imágenes
    function processImage($imageField) {
        if (isset($_FILES[$imageField]) && $_FILES[$imageField]['error'] == 0) {
            $target_dir = "../../images/";
            $file_name = basename($_FILES[$imageField]["name"]);
            $target_file = $target_dir . $file_name;
            if (move_uploaded_file($_FILES[$imageField]["tmp_name"], $target_file)) {
                // Cambia la ruta que se guarda para que sea relativa al formulario
                return "../images/" . $file_name;
            }
        }
        return '';
    }
    

    $imagen = processImage('imagen');
    $imagen2 = processImage('imagen2');
    $imagen3 = processImage('imagen3');
    $imagen4 = processImage('imagen4');

    if (isset($_POST['id_producto'])) {
        // Actualizar producto existente
        $id = $_POST['id_producto'];
        $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, id_categoria = ?";
        $params = array($nombre, $descripcion, $precio, $stock, $categoria);
        $types = "ssdis";

        $imagenes = array('imagen' => $imagen, 'imagen2' => $imagen2, 'imagen3' => $imagen3, 'imagen4' => $imagen4);
        foreach ($imagenes as $campo => $valor) {
            if ($valor != '') {
                $sql .= ", $campo = ?";
                $params[] = $valor;
                $types .= "s";
            }
        }

        $sql .= " WHERE id_producto = ?";
        $params[] = $id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
    } else {
        // Insertar nuevo producto
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, id_categoria, imagen, imagen2, imagen3, imagen4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdisssss", $nombre, $descripcion, $precio, $stock, $categoria, $imagen, $imagen2, $imagen3, $imagen4);
    }

    if ($stmt->execute()) {
        echo "<script>alertExito();</script>";
        
    } else {
        echo "<script>
            alertError2()
        </script>";
    }

    $stmt->close();
}

$conn->close();
?>