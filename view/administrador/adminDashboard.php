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


//consulta a la bd
$sql = "SELECT COUNT(*) FROM usuarios WHERE id_rol != 3;";
$resultado = $conn->query($sql);
$fila = $resultado->fetch_array();
$usuarios = $fila[0];


$sql2 = "SELECT COUNT(*) FROM contacto;";
$resultado2 = $conn->query($sql2);
$fila2 = $resultado2->fetch_array();
$mensajes = $fila2[0];

$sql3 = "SELECT COUNT(*) FROM productos;";
$resultado3 = $conn->query($sql3);
$fila3 = $resultado3->fetch_array();
$productos = $fila3[0];

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <link rel="stylesheet" href="../../css/styleAdmin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    
    
    
</head>

<body>
    <style>

        .border-left-primary {
            border-left: .25rem solid #4e73df !important;
        }
        .border-left-success {
            border-left: .25rem solid #1cc88a !important;
        }
        .text-success {
            color: #1cc88a !important;
        }
        .border-left-info {
            border-left: .25rem solid #36b9cc !important;
        }
        .border-left-warning {
            border-left: .25rem solid #f6c23e !important;
        }
        .pb-2, .py-2 {
            padding-bottom: .5rem !important;
        }
        .pt-2, .py-2 {
            padding-top: .5rem !important;
        }
        .text-gray-300 {
            color: #dddfeb !important;
        }
        .h-100 {
            height: 100% !important;
        }

    </style>
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
        

          

            <!-- Begin Page Content -->
            <div class="custom-container">

                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h2 class="my-4">Dashboard</h2>
                    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                            class="fas fa-download fa-sm text-white-50"></i> Generar Reporte</a>
                </div>

                <!-- Content Row -->
                <div class="row">
                <?php if($_SESSION['usuario_rol'] === '01'): ?>
                    <!-- Earnings (Monthly) Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">

                        <a href="adminUsuarios.php" style="text-decoration: none;">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Usuarios</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $usuarios?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Earnings (Monthly) Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="adminProductos.php" style="text-decoration: none;">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Productos</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $productos?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-bag-shopping fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </a>
                        
                    </div>

                    <!-- Earnings (Monthly) Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Clientes
                                        </div>
                                        <div class="row no-gutters align-items-center">
                                            <div class="col-auto">
                                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">1%</div>
                                            </div>
                                            <div class="col">
                                                <div class="progress progress-sm mr-2">
                                                    <div class="progress-bar bg-info" role="progressbar"
                                                        style="width: 1%" aria-valuenow="50" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Requests Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="adminMensajes.php" style="text-decoration: none;">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Mensajes</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $mensajes?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-comments fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </a>
                    </div>
                    
                </div>

                


            </div>
            <!-- /.container-fluid -->


        </div>
    </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
</script>
<script src="../../js/scripAdmint.js"></script>

</html>