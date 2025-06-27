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
if ($_SESSION['usuario_rol'] !== '01') {
    $_SESSION['error_mensaje'] = "No tienes permiso para acceder a esta página.";
    header("Location: adminInicio.php");
    exit();
}
$nombre = $_SESSION['usuario_nombre'];


require_once '../../controller/UsuarioController.php';
$usuarioController = new UsuarioController();
$usuarios = $usuarioController->listarUsuarios();

//$sql = "SELECT * FROM administrador";
//$result = $conn->query($sql);
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

    <div class="modal fade" id="eliminarUsuarioModal" tabindex="-1" aria-labelledby="eliminarUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eliminarUsuarioModalLabel">Confirmar eliminación de Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro que deseas eliminar el usuario?
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
     
            <div class="container">
                <h2 class="my-4">Administrar Usuarios</h2>
                
                <div class="d-flex justify-content-between mb-3">
                    <!-- Botón Agregar Nuevo Usuario -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarProductoModal">
                        Agregar Nuevo Usuario
                    </button>

                    <!-- Botón Generar Reporte -->
                    <button type="button" class="btn btn-primary" id="generarReporte">
                        <i class="fas fa-download fa-sm text-white-50"></i> Generar Reporte
                    </button>
                </div>

                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <!--<th>Password</th>-->
                                <th>Rol</th>
                                <th>Dni</th>
                                <th>Telefono</th>
                                <th>Acciones</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($usuarios->num_rows > 0) {
                                while ($row = $usuarios->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row["id_usuario"] . "</td>";
                                    echo "<td>" . htmlspecialchars($row["nombre"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                    //echo "<td>" . htmlspecialchars($row["password"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["permiso"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["dni"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["telefono"]) . "</td>";
                                    echo "<td class='action-buttons'>
                                    <button type='button' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editarProductoModal' data-id='" . $row["id_usuario"] . "'><i class='bi bi-pencil'></i></button>
                                   <button type='button' class='btn btn-danger btn-sm eliminar-usuario' data-bs-toggle='modal' data-bs-target='#eliminarUsuarioModal' data-id='" . $row["id_usuario"] . "'><i class='bi bi-trash'></i></button>
</button>
                                 
                                   </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>No hay usuarios disponibles</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Usuario -->
    <div class="modal fade" id="agregarProductoModal" tabindex="-1" aria-labelledby="agregarProductoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="agregarProductoModalLabel">Agregar Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="metodos/procesarUsuario.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required>
                            <option value="01">Admin</option>
                            <option value="02">Empleado</option>
                            <option value="03">Cliente</option>
                        </select>

                        </div>
                        <div class="mb-3">
                            <label for="dni" class="form-label">Dni</label>
                            <input type="text" class="form-control" id="dni" name="dni" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Telefono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Producto -->
    <div class="modal fade" id="editarProductoModal" tabindex="-1" aria-labelledby="editarProductoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarProductoModalLabel">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="metodos/procesarUsuario.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="edit_id_administrador" name="id_administrador">
                        <div class="mb-3">
                            <label for="edit_nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="edit_rol" class="form-label">Rol</label>
                            <select class="form-select" id="edit_rol" name="rol" required>
                                <option value="01">admin</option>
                                <option value="02">empleado</option>
                                <option value="03">cliente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_dni" class="form-label">Dni</label>
                            <input type="text" class="form-control" id="edit_dni" name="dni" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_telefono" class="form-label">Telefono</label>
                            <input type="text" class="form-control" id="edit_telefono" name="telefono" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<script src="../../js/scripAdmint.js"></script>
<script src="../../js/alertas.js"></script>
<script>
    document.getElementById('generarReporte').addEventListener('click', function() {
        // Realizar la solicitud al controlador para generar el reporte
        window.open('../../controller/ReportController.php','_blank');
        //window.location.href = '../../controller/ReportController.php'; // Aquí se dirige al controlador que genera el reporte
    });
</script>

<script>
    let usuarioIdAEliminar;

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('eliminarUsuarioModal');
        const botonesEliminar = document.querySelectorAll('.eliminar-usuario');
        const botonConfirmarEliminar = document.getElementById('confirmarEliminar');

        botonesEliminar.forEach(boton => {
            boton.addEventListener('click', function() {
                usuarioIdAEliminar = this.getAttribute('data-id');
            });
        });

        botonConfirmarEliminar.addEventListener('click', function() {
            if (usuarioIdAEliminar) {
                fetch('metodos/eliminarUsuario.php?id=' + usuarioIdAEliminar)
                    .then(response => response.json())
                    .then(data => {
                        alertExito();
                    })
                    .catch(error => {
                        alert('Ocurrió un error: ' + error);
                    });
            } else {
                alert('No se ha seleccionado ningún usuario para eliminar');
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var editarProductoModal = document.getElementById('editarProductoModal');

        editarProductoModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var modal = this;

            // Realizar petición AJAX
            fetch('metodos/obtenerUsuario.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        // Llenar los campos del formulario con los datos obtenidos
                        modal.querySelector('#edit_id_administrador').value = data.id_administrador;
                        modal.querySelector('#edit_nombre').value = data.nombre;
                        modal.querySelector('#edit_email').value = data.email;
                        //*modal.querySelector('#edit_password').value = data.password;
                        modal.querySelector('#edit_rol').value = data.rol;
                        modal.querySelector('#edit_dni').value = data.dni;
                        modal.querySelector('#edit_telefono').value = data.telefono;

                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los datos del producto');
                });
        });
    });
</script>


</html>

<?php
$conn->close();
?>