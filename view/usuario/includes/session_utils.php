<?php
function usuarioAutenticado() {
    return isset($_SESSION['usuario_id']);
}

function esAdmin() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === '01';
}

function obtenerNombreUsuario() {
    return $_SESSION['usuario_nombre'] ?? 'Invitado';
}
?>

