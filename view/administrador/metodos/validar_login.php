<?php
session_start();
include '../../../bd/conexion.php';  // Asegúrate de que este archivo contiene tu código de conexión

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Consulta para verificar las credenciales
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Inicio de sesión exitoso
            $_SESSION['usuario_id'] = $row['id_usuario'];
            $_SESSION['usuario_nombre'] = $row['nombre'];
            $_SESSION['usuario_rol'] = $row['id_rol'];
            
            // Redirigir a la página de inicio del panel de administración
            header("Location: ../adminInicio.php");
            exit();
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Usuario no encontrado";
    }

    if (isset($error)) {
        // Redirigir de vuelta al formulario de inicio de sesión con un mensaje de error
        header("Location: ../loginAdmin.php?error=" . urlencode($error));
        exit();
    }
}

$conn->close();
?>