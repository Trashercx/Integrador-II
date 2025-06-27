<?php
require_once '../bd/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($nombre) && !empty($email) && !empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Puedes usar 'Cliente' o '03' dependiendo de cómo esté tu tabla rol
        $sql = "INSERT INTO usuarios (nombre, email, password, id_rol) VALUES (?, ?, ?, '03')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nombre, $email, $passwordHash);

        if ($stmt->execute()) {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                window.onload = function() {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Registro exitoso!',
                        text: 'Ya puedes iniciar sesión',
                        confirmButtonText: 'Iniciar sesión'
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                };
            </script>";
        } else {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                window.onload = function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al registrar',
                        text: 'Es posible que el correo ya esté en uso',
                        confirmButtonText: 'Intentar de nuevo'
                    }).then(() => {
                        window.history.back();
                    });
                };
            </script>";
        }
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos vacíos',
                    text: 'Por favor completa todos los campos',
                    confirmButtonText: 'Volver'
                }).then(() => {
                    window.history.back();
                });
            };
        </script>";
    }
}
?>
