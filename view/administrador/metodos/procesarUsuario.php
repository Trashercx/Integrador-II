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
            window.location.href = '../adminUsuarios.php';
        });
    }

    function alertError() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El usuario ya está registrado.',
        }).then(() => {
            window.location.href = '../adminUsuarios.php';
        });
    }
    function alertError2(){
        Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error: " . $error . "',
            }).then(() => {
                window.location.href = '../adminUsuarios.php';
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
    $email = $_POST['email'];
    $password = $_POST['password'];
    $rol = $_POST['rol'];
    $dni = $_POST['dni'];
    $telefono = $_POST['telefono'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if (isset($_POST['id_administrador'])) {
        // Actualizar usuario
        $id = $_POST['id_administrador'];
        $sql = "UPDATE usuarios SET nombre = ?, email = ?, id_rol = ?, dni = ?, telefono = ? WHERE id_administrador = ?";
        if ($password != '') {
            $sql = "UPDATE usuarios SET nombre = ?, email = ?, password = ?, id_rol = ?, dni = ?, telefono = ? WHERE id_administrador = ?";
        }
        $stmt = $conn->prepare($sql);
        if ($password != '') {
            $stmt->bind_param("ssssssi", $nombre, $email, $hashed_password, $rol, $dni, $telefono, $id);
        } else {
            $stmt->bind_param("sssssi", $nombre, $email, $rol, $dni, $telefono, $id);
        }
    } else {
        // Insertar nuevo usuario
        $sql = "INSERT INTO usuarios (nombre, email, password, id_rol, dni, telefono) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $nombre, $email, $hashed_password, $rol, $dni, $telefono);
    }

    if ($stmt->execute()) {

        echo "<script>console.log('Ejecutando alertExito desde PHP'); alertExito();</script>";
    } else {
        $error = $stmt->error;
        if (strpos($error, 'Duplicate entry') !== false) {

            echo "<script>
            alertError();
        </script>";
        } else {
            echo "<script>
            alertError2()
        </script>";
        }
    }

    $stmt->close();
}

$conn->close();

?>