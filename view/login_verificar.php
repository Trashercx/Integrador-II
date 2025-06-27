<?php
session_start();
require_once '../bd/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['usuario_id'] = $user['id_usuario'];
            $_SESSION['usuario_nombre'] = $user['nombre'];
            $_SESSION['usuario_rol'] = $user['id_rol'];

            $rol = $user['id_rol'];
            $redirect = '../index.php'; // valor por defecto

            if ($rol === '01') {
                $redirect = '../view/administrador/adminDashboard.php';
            } elseif ($rol === '03') {
                // Si vino del checkout, redirigir ahí
                if (isset($_SESSION['redirect_to'])) {
                    $redirect = $_SESSION['redirect_to'];
                    unset($_SESSION['redirect_to']);
                }
            }

            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                window.onload = function() {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Bienvenido!',
                        text: 'Redirigiendo...',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '$redirect';
                    });
                };
            </script>";
            exit;
        }
    }

    // Error login
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        window.onload = function() {
            Swal.fire({
                icon: 'error',
                title: 'Error de inicio de sesión',
                text: 'Usuario o contraseña incorrectos',
                confirmButtonText: 'Intentar de nuevo'
            }).then(() => {
                window.location.href = 'login.php';
            });
        };
    </script>";
    exit;
}
?>
