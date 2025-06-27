
<?php


function redirigirNoAutorizado($rutaRedireccion = '../login.php') {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        window.onload = function() {
            Swal.fire({
                icon: 'error',
                title: 'Acceso denegado',
                text: 'No tienes permisos para acceder aquí'
            }).then(() => {
                window.location.href = '$rutaRedireccion';
            });
        };
    </script>";
    exit;
}

function soloAdmin() {
    if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] !== '01' && $_SESSION['usuario_rol'] !== 'Admin')) {
        redirigirNoAutorizado('../login.php');
    }
}

function soloCliente() {
    if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] !== '03' && $_SESSION['usuario_rol'] !== 'Cliente')) {
        redirigirNoAutorizado('../login.php');
    }
}


?>
