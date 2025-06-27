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

// Consulta para obtener todos los productos

$sql = "SELECT * FROM contacto";

$result = $conn->query($sql);

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

                <h2 class="my-4">Administrar Mensajes</h2>


                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i>
                        Tabla de Mensajes
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>

                                    <th>Nombre</th>

                                    <th>Email</th>

                                    <th>Mensaje</th>

                                    <th>Fecha</th>
                                    <!--<th>Acciones</th> -->

                                  

                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row["id_contacto"] . "</td>";
                                            echo "<td>" . htmlspecialchars($row["nombre"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["mensaje"]) . "</td>";
                                            echo "<td>" . $row["fecha_envio"] . "</td>";
                                            

                                        
                                            

                                            //echo "<td class='action-buttons'>
//<button type='button' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editarProductoModal' data-id='" . $row["id_contacto"] . "'><i class='bi bi-pencil'></i></button>
//<button type='button' class='btn btn-danger btn-sm eliminar-producto' data-bs-toggle='modal' data-bs-target='#eliminarProductoModal' data-id='" . $row["id_contacto"] . "'><i class='bi bi-trash'></i></button>
//</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='11'>No hay mensajes disponibles</td></tr>";
                                    }
                                    ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <h5 class="my-4">© MARY CRIST IMPORT 2024</h2>



            </div>


        </div>


    </div>



</body>

<script src="@@path/vendor/simple-datatables/dist/umd/simple-datatables.js"></script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
</script>

<script src="../../js/scripAdmint.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../../js/alertas.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
    crossorigin="anonymous"></script>
<script src="../../js/datatables-simple-demo.js"></script>





<script>
var dataTableEl = d.getElementById('datatable');
if (dataTableEl) {
    const dataTable = new simpleDatatables.DataTable(dataTableEl);
}
</script>

</html>



<?php

$conn->close();

?>