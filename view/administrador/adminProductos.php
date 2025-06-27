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

$productos_bajo_stock = [];

$sql_bajo_stock = "SELECT nombre, stock FROM productos WHERE stock <= 5";
$result_bajo_stock = $conn->query($sql_bajo_stock);

if ($result_bajo_stock && $result_bajo_stock->num_rows > 0) {
    while ($row = $result_bajo_stock->fetch_assoc()) {
        $productos_bajo_stock[] = $row;
    }
}

// Consulta para obtener todos los productos

$sql = "
    SELECT p.*, c.nombre AS nombre_categoria
    FROM productos p
    INNER JOIN categoria c ON p.id_categoria = c.id_categoria
    ORDER BY p.id_producto ASC
";

$result = $conn->query($sql);

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

    <div class="modal fade" id="eliminarProductoModal" tabindex="-1" aria-labelledby="eliminarProductoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eliminarProductoModalLabel">Confirmar eliminación de Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro que deseas eliminar el producto?
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

                <h2 class="my-4">Administrar Productos</h2>

                

                <div class="d-flex justify-content-between mb-3">
                    <!-- Botón Agregar Nuevo Usuario -->
                    <button type="button" class="btn btn-primary " data-bs-toggle="modal" data-bs-target="#agregarProductoModal">

                    Agregar Nuevo Producto

                     </button>

                    <!-- Botón Generar Reporte -->
                    <button type="button" class="btn btn-primary" id="generarReporteProducto">
                        <i class="fas fa-download fa-sm text-white-50"></i> Generar Reporte
                    </button>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i>
                        Tabla de Productos
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                <th>ID</th>

                                <th>Nombre</th>

                                <th>Descripción</th>

                                <th>Precio</th>

                                <th>Stock</th>

                                <th>Categoría</th>

                                <th>Imagen</th>
                                <th>Imagen2</th>
                                <th>Imagen3</th>
                                <th>Imagen4</th>

                                <th>Acciones</th>

                                </tr>
                            </thead>
                            <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row["id_producto"] . "</td>";
                                            echo "<td>" . htmlspecialchars($row["nombre"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["descripcion"]) . "</td>";
                                            echo "<td>S/." . number_format($row["precio"], 2) . "</td>";
                                            echo "<td>" . $row["stock"] . "</td>";
                                            echo "<td>" . $row["nombre_categoria"] . "</td>";

                                            // For each image column, check if it's empty or null
                                            $imagen = empty($row["imagen"]) ? "None" : "<img src='" . htmlspecialchars($row["imagen"]) . "' alt='Imagen del producto' style='width: 50px; height: auto;'>";
                                            $imagen2 = empty($row["imagen2"]) ? "None" : "<img src='" . htmlspecialchars($row["imagen2"]) . "' alt='Imagen del producto' style='width: 50px; height: auto;'>";
                                            $imagen3 = empty($row["imagen3"]) ? "None" : "<img src='" . htmlspecialchars($row["imagen3"]) . "' alt='Imagen del producto' style='width: 50px; height: auto;'>";
                                            $imagen4 = empty($row["imagen4"]) ? "None" : "<img src='" . htmlspecialchars($row["imagen4"]) . "' alt='Imagen del producto' style='width: 50px; height: auto;'>";

                                            echo "<td>" . $imagen . "</td>";
                                            echo "<td>" . $imagen2 . "</td>";
                                            echo "<td>" . $imagen3 . "</td>";
                                            echo "<td>" . $imagen4 . "</td>";

                                            echo "<td class='action-buttons'>
<button type='button' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editarProductoModal' data-id='" . $row["id_producto"] . "'><i class='bi bi-pencil'></i></button>


<button type='button' class='btn btn-danger btn-sm eliminar-producto' data-bs-toggle='modal' data-bs-target='#eliminarProductoModal' data-id='" . $row["id_producto"] . "'><i class='bi bi-trash'></i></button>
</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='11'>No hay productos disponibles</td></tr>";
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



    <!-- Modal Agregar Producto -->

    <div class="modal fade" id="agregarProductoModal" tabindex="-1" aria-labelledby="agregarProductoModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title" id="agregarProductoModalLabel">Agregar Nuevo Producto</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>

                <div class="modal-body">

                    <form action="metodos/procesarProducto.php" method="POST" enctype="multipart/form-data">

                        <div class="mb-3">

                            <label for="nombre" class="form-label">Nombre</label>

                            <input type="text" class="form-control" id="nombre" name="nombre" required>

                        </div>

                        <div class="mb-3">

                            <label for="descripcion" class="form-label">Descripción</label>

                            <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>

                        </div>

                        <div class="mb-3">

                            <label for="precio" class="form-label">Precio</label>

                            <input type="number" class="form-control" id="precio" name="precio" step="0.01" required>

                        </div>

                        <div class="mb-3">

                            <label for="stock" class="form-label">Stock</label>

                            <input type="number" class="form-control" id="stock" name="stock" required>

                        </div>

                        <div class="mb-3">

                            <label for="categoria" class="form-label">Categoría</label>

                            <select class="form-select" id="categoria" name="categoria" required>

                                <option value="1">Higiene</option>

                                <option value="3">Ferreteria</option>

                                <option value="2">Hogar</option>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label for="imagen" class="form-label">Imagen</label>

                            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" required>

                        </div>

                        <div class="mb-3">

                            <label for="imagen2" class="form-label">Imagen2</label>

                            <input type="file" class="form-control" id="imagen2" name="imagen2" accept="image/*">

                        </div>
                        <div class="mb-3">

                            <label for="imagen3" class="form-label">Imagen3</label>

                            <input type="file" class="form-control" id="imagen3" name="imagen3" accept="image/*">

                        </div>
                        <div class="mb-3">

                            <label for="imagen4" class="form-label">Imagen4</label>

                            <input type="file" class="form-control" id="imagen4" name="imagen4" accept="image/*">

                        </div>


                        <button type="submit" class="btn btn-primary">Guardar Producto</button>

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

                    <h5 class="modal-title" id="editarProductoModalLabel">Editar Producto</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>

                <div class="modal-body">

                    <form action="metodos/procesarProducto.php" method="POST" enctype="multipart/form-data">

                        <input type="hidden" id="edit_id_producto" name="id_producto">

                        <div class="mb-3">

                            <label for="edit_nombre" class="form-label">Nombre</label>

                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>

                        </div>

                        <div class="mb-3">

                            <label for="edit_descripcion" class="form-label">Descripción</label>

                            <textarea class="form-control" id="edit_descripcion" name="descripcion" required></textarea>

                        </div>

                        <div class="mb-3">

                            <label for="edit_precio" class="form-label">Precio</label>

                            <input type="number" class="form-control" id="edit_precio" name="precio" step="0.01" required>

                        </div>

                        <div class="mb-3">

                            <label for="edit_stock" class="form-label">Stock</label>

                            <input type="number" class="form-control" id="edit_stock" name="stock" required>

                        </div>

                        <div class="mb-3">

                            <label for="edit_categoria" class="form-label">Categoría</label>

                            <select class="form-select" id="edit_categoria" name="categoria" required>

                                <option value="1">Higiene</option>

                                <option value="3">Ferreteria</option>

                                <option value="2">Hogar</option>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label for="edit_imagen" class="form-label">Nueva Imagen (dejar en blanco para mantener la actual)</label>

                            <input type="file" class="form-control" id="edit_imagen" name="imagen" accept="image/*">

                        </div>

                        <div class="mb-3">

                            <label for="edit_imagen" class="form-label">Nueva Imagen2 </label>

                            <input type="file" class="form-control" id="edit_imagen" name="imagen2" accept="image/*">

                        </div>

                        <div class="mb-3">

                            <label for="edit_imagen" class="form-label">Nueva Imagen3</label>

                            <input type="file" class="form-control" id="edit_imagen" name="imagen3" accept="image/*">

                        </div>

                        <div class="mb-3">

                            <label for="edit_imagen" class="form-label">Nueva Imagen4 </label>

                            <input type="file" class="form-control" id="edit_imagen" name="imagen4" accept="image/*">

                        </div>

                        <button type="submit" class="btn btn-primary">Actualizar Producto</button>

                    </form>

                </div>

            </div>

        </div>

    </div>






</body>

<script src="@@path/vendor/simple-datatables/dist/umd/simple-datatables.js"></script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<script src="../../js/scripAdmint.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../../js/alertas.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="../../js/datatables-simple-demo.js"></script>
    <script>
    document.getElementById('generarReporteProducto').addEventListener('click', function() {
        // Realizar la solicitud al controlador para generar el reporte
        window.open('../../controller/ReportProductoController.php','_blank');
        //window.location.href = '../../controller/ReportController.php'; // Aquí se dirige al controlador que genera el reporte
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
            fetch('metodos/obtenerProducto.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        // Llenar los campos del formulario con los datos obtenidos
                        modal.querySelector('#edit_id_producto').value = data.id_producto;
                        modal.querySelector('#edit_nombre').value = data.nombre;
                        modal.querySelector('#edit_descripcion').value = data.descripcion;
                        modal.querySelector('#edit_precio').value = data.precio;
                        modal.querySelector('#edit_stock').value = data.stock;
                        modal.querySelector('#edit_categoria').value = data.id_categoria;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los datos del producto');
                });
        });
    });
</script>

<script>
    let productoIdAEliminar;

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('eliminarProductoModal');
        const botonesEliminar = document.querySelectorAll('.eliminar-producto');
        const botonConfirmarEliminar = document.getElementById('confirmarEliminar');

        botonesEliminar.forEach(boton => {
            boton.addEventListener('click', function() {
                productoIdAEliminar = this.getAttribute('data-id');
            });
        });

        botonConfirmarEliminar.addEventListener('click', function() {
            if (productoIdAEliminar) {
                fetch('metodos/eliminarProducto.php?id=' + productoIdAEliminar)
                    .then(response => response.json())
                    .then(data => {
                        alertExito();
                    })
                    .catch(error => {
                        alert('Ocurrió un error: ' + error);
                    });
            } else {
                alert('No se ha seleccionado ningún producto para eliminar');
            }
        });
    });
</script>
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

<script>
    const productosBajoStock = <?php echo json_encode($productos_bajo_stock); ?>;

    if (productosBajoStock.length > 0) {
        let mensaje = "Los siguientes productos están por agotarse:\n\n";
        productosBajoStock.forEach(p => {
            mensaje += `• ${p.nombre} (Stock: ${p.stock})\n`;
        });

        Swal.fire({
            icon: 'warning',
            title: '¡Atención!',
            html: `<pre style="text-align: left;">${mensaje}</pre>`,
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'text-start'
            }
        });
    }
</script>