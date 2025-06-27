<?php
session_start();
require_once 'includes/sessions_utils.php';
soloAdmin(); // Restringe solo a Admin
include '../../bd/conexion.php';  // Asegúrate de que este archivo contiene tu conexión a la base de datos

// Verifica si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: loginAdmin.php");
    exit();
}
$nombre = $_SESSION['usuario_nombre'];
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/ZEUS-removebg-preview.png">
    <title>MARYCRIST ADMINISTRADOR</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <link rel="stylesheet" href="../../css/styleAdmin.css">
    <style>
        .center-image {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 70px); /* Ajusta esto según la altura de tu navbar */
        }
        .center-image img {
            max-width: 60%;
            max-height: 60%;
            object-fit: contain;
        }
    </style>
</head>

<body>
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirmar cierre de sesión</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas cerrar sesión?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="metodos/salir.php" class="btn btn-primary">Sí, cerrar sesión</a>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper">
    <?php require_once 'includes/header.php'; ?>

            <!-- Imagen centrada -->
            <div class="center-image">
                <img src="../img/ZEUS2.png" alt="Logo Grande">
            </div>

        </div>
    </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<script src="../../js/scripAdmint.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var editarProductoModal = document.getElementById('editarProductoModal')
        editarProductoModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget
            var id = button.getAttribute('data-id')
            var modal = this
            // Aquí deberías hacer una petición AJAX para obtener los datos del producto
            // y luego llenar los campos del formulario
            // Por ahora, solo llenamos el campo id como ejemplo
            modal.querySelector('#edit_id_administrador').value = id
        })
    })
</script>
</html>