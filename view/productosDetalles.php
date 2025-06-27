
<?php
session_start();
require_once 'usuario/includes/session_utils.php';

?>

<?php
require_once '../controller/ProductoController.php';

$productoController = new ProductoController();
$categoria = $_GET['categoria'] ?? null;
$productos = $productoController->listarProductos($categoria);



//productoDetalle
// Verificar si el ID de producto está presente y es válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de producto no válido.";
    exit;
}

$idProducto = intval($_GET['id']);
$productoDetalle = $productoController->obtenerProductosDetalles($idProducto);

if (!$productoDetalle) {
    echo "Producto no encontrado.";
    exit;
}

// Eliminar '../' del inicio de las rutas de las imágenes
// Asegurarse de que las claves existen antes de procesarlas
$imagen_ruta = preg_replace('/^\.\.\//', '', $productoDetalle['imagen'] ?? '');
$imagen2_ruta = preg_replace('/^\.\.\//', '', $productoDetalle['imagen2'] ?? '');
$imagen3_ruta = preg_replace('/^\.\.\//', '', $productoDetalle['imagen3'] ?? '');
$imagen4_ruta = preg_replace('/^\.\.\//', '', $productoDetalle['imagen4'] ?? '');


?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Compra <?php echo htmlspecialchars($productoDetalle['nombre']); ?> con la calidad que caracteriza a Zeus Importaciones. ¡Explora más detalles ahora!">
  <meta name="keywords" content="<?php echo htmlspecialchars($productoDetalle['nombre']); ?>, Zeus Importaciones, Zeus fósforos, Zeus encendedores, calidad, productos diarios, <?php echo htmlspecialchars($productoDetalle['id_categoria']); ?>, marycrist">
<meta name="author" content="Zeus Importaciones">
<title>Detalles de <?php echo htmlspecialchars($productoDetalle['nombre']); ?> - Zeus Importaciones</title>

  <link rel="icon" href="img/ZEUS-removebg-preview.png">

  <link rel="stylesheet" href="../../../css/style.css" />
  <link rel="stylesheet" href="../../../css/estilos.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
  <script src="https://kit.fontawesome.com/05abdbeb44.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://cdn.lineicons.com/2.0/LineIcons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

</head>

<?php

require_once 'usuario/includes/header.php';


//require_once 'usuario/metodos/obtenerProductosDetalles.php';



?>

<!-- productoDetalle -->





<div class="small-container single-product">

    <div class="row">

        <div class="col-2">

            <img src="<?php echo htmlspecialchars($imagen_ruta); ?>" width="100%" id="ProductImg" style="background-color: #f0f0f0;">



            <div class="small-img-row">

                <div class="small-img-col">

                    <img src="<?php echo htmlspecialchars($imagen_ruta); ?>" width="100%" class="small-img" style="background-color: #f0f0f0;" >

                </div>
                <div class="small-img-col">

                    <img src="<?php echo htmlspecialchars($imagen2_ruta); ?>" width="100%" class="small-img" style="background-color: #f0f0f0;">

                </div>
                <div class="small-img-col">

                    <img src="<?php echo htmlspecialchars($imagen3_ruta); ?>" width="100%" class="small-img" style="background-color: #f0f0f0;">

                </div>
                <div class="small-img-col">

                    <img src="<?php echo htmlspecialchars($imagen4_ruta); ?>" width="100%" class="small-img" style="background-color: #f0f0f0;">

                </div>

            </div>
           
            




        </div>

        <div class="col-2">

            <p>Categoria: <?php echo htmlspecialchars($productoDetalle['nombre_categoria']); ?></p>

            <h1><?php echo htmlspecialchars($productoDetalle['nombre']); ?></h1>

            

        <h4>S/. <?php echo number_format($productoDetalle['precio'], 2); ?></h4> 



            <!-- <input type="number" value="1"> -->

           
            
            <a href="#" 
                class="product-grid__btn btn-default" 
                data-btn-action="add-btn-cart" 
                data-modal="#jsModalCarrito"
                
                
                data-id="<?php echo $productoDetalle['id_producto']; ?>"
                data-nombre="<?php echo htmlspecialchars($productoDetalle['nombre']); ?>"
                data-precio="<?php echo htmlspecialchars($productoDetalle['precio']); ?>"
                data-imagen="<?php echo htmlspecialchars($imagen_ruta); ?>"
                
                onclick="agregarAlCarrito(
                    '<?php echo $productoDetalle['id_producto']; ?>',
                    '<?php echo $productoDetalle['nombre']; ?>',
                    '<?php echo number_format($productoDetalle['precio'], 2); ?>',
                    '<?php echo htmlspecialchars($imagen_ruta); ?>'
                )"
                >
                Añadir al carrito
            </a>

             <!--<a href="" class="btn">Añadir al carrito</a> -->



            

            <h3>Detalles del producto<i class="fa fa-indent"></i></h3>

            <br>

            <p><?php echo htmlspecialchars($productoDetalle['descripcion']); ?></p>

        </div>

    </div>

</div>






<!-- obtenerProductos -->

<div class="small-container">

    <div class="row row-2">

       

        <a href="productos.php">

            <h2>Más Productos &#8594;</h2>

        </a>

    </div>

</div>

<!-- Products -->

<div class="small-container">





    <div class="row">

        <?php

        if ($productos->num_rows > 0) {

            $count = 0; // Inicializa el contador

            while ($row = $productos->fetch_assoc()) {

                if ($count >= 4) {

                    break; // Sal del bucle después de 4 registros

                }



                // Eliminar los '../' del inicio de la ruta de la imagen

                $imagen_ruta = preg_replace('/^\.\.\//', '', $row['imagen']);

        ?>

                <div class="col-4 card">

                    <a href="productosDetalles.php?id=<?php echo $row['id_producto']; ?>">

                        <img src="<?php echo htmlspecialchars($imagen_ruta); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>" style="background-color: #f0f0f0;">

                    </a>

                    <h4><?php echo htmlspecialchars($row['nombre']); ?></h4>

                    <div class="rating">

                        <i class="fa fa-star"></i>

                        <i class="fa fa-star"></i>

                        <i class="fa fa-star"></i>

                        <i class="fa fa-star"></i>

                        <i class="fa fa-star-o"></i>

                    </div>

                   <p>S/. <?php echo number_format($row['precio'], 2); ?></p>

                </div>

        <?php

                $count++; // Incrementa el contador

            }
        } else {

            echo "<p>No se encontraron productos.</p>";
        }

        ?>

    </div>



</div>















<script src="../js/app.js"></script>    



<!-- Footer -->

<?php require_once 'usuario/includes/footer.php'; ?>

<!-- javascript -->



<script>
    var MenuItems = document.getElementById("MenuItems");

    MenuItems.style.maxHeight = "0px";



    function menutoggle() {

        if (MenuItems.style.maxHeight == "0px") {

            MenuItems.style.maxHeight = "100vh"

        } else {

            MenuItems.style.maxHeight = "0px"

        }

    }
</script>



<!-- product gallery -->

<script>
    var ProductImg = document.getElementById("ProductImg");

    var SmallImg = document.getElementsByClassName("small-img");



    SmallImg[0].onclick = function() {

        ProductImg.src = SmallImg[0].src;

    }

    SmallImg[1].onclick = function() {

        ProductImg.src = SmallImg[1].src;

    }

    SmallImg[2].onclick = function() {

        ProductImg.src = SmallImg[2].src;

    }

    SmallImg[3].onclick = function() {

        ProductImg.src = SmallImg[3].src;

    }
</script>
<script src="../js/menu.js"></script>

</body>



</html>