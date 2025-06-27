<?php
session_start();
require_once '../../bd/conexion.php';
require_once 'includes/session_utils.php';

if (!usuarioAutenticado()) {
    header("Location: ../usuario/login.php");
    exit;
}

$idUsuario = $_SESSION['usuario_id'];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $dni = trim($_POST['dni'] ?? '');

    if ($nombre === '' || $email === '') {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Nombre y correo son obligatorios'];
        header("Location: ../perfil.php");
        exit;
    }

    // Verificar si ya existe ese email en otro usuario
    $verificar = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?");
    $verificar->bind_param("si", $email, $idUsuario);
    $verificar->execute();
    $verificar->store_result();

    if ($verificar->num_rows > 0) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Este correo ya está en uso por otro usuario'];
        header("Location: ../perfil.php");
        exit;
    }

    $sql = "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, direccion = ?, dni = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nombre, $email, $telefono, $direccion, $dni, $idUsuario);

    if ($stmt->execute()) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Perfil actualizado correctamente'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Error al actualizar los datos'];
    }

    header("Location: ../perfil.php");
    exit;
} else {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Acceso no válido'];
    header("Location: ../perfil.php");
    exit;
}