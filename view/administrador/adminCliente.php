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

<head>

    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/ZEUS-removebg-preview.png">
    <title>MARYCRIST ADMINISTRADOR</title>

    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="../../css/styleAdmin.css">



    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>


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

    <div class="modal fade" id="eliminarProductoModal" tabindex="-1" aria-labelledby="eliminarProductoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eliminarProductoModalLabel">Confirmar eliminación de Mensaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro que deseas eliminar el mensaje?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmarEliminar">Sí, eliminar</button>
                </div>
            </div>
        </div>
    </div>








    <div class="wrapper">

        <?php require_once 'includes/header.php'; ?>

            <div class="custom-container">




                <div id="layoutError_content">
                    <main>
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-6">
                                    <div class="text-center mt-4">
                                        <img class="mb-4 img-error" src="../img/error-404-monochrome.svg"
                                            style="max-width: 20rem;" />
                                        <p class="lead">No se encontro la pagina</p>
                                        <a href="adminDashboard.php">
                                            <i class="fas fa-arrow-left me-1"></i>
                                            Volver al Dashboard
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>






                <footer class="sticky-footer ">
                    <div class="container my-auto">
                        <div class="copyright text-center my-auto">
                            <span>© MARY CRIST IMPORT 2024</span>
                        </div>
                    </div>
                </footer>



            </div>


        </div>


    </div>



</body>

<script src="@@path/vendor/simple-datatables/dist/umd/simple-datatables.js"></script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
</script>

<script src="../js/scripAdmint.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../js/alertas.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
    crossorigin="anonymous"></script>
<script src="../js/datatables-simple-demo.js"></script>







</html>



<?php

$conn->close();

?>