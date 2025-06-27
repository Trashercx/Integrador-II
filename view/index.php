
<?php
session_start();
require_once 'usuario/includes/session_utils.php';

?>

<?php
//require_once 'usuario/metodos/obtenerProductos.php';  

?>

<?php
require_once '../controller/ProductoController.php';

$productoController = new ProductoController();
$categoria = $_GET['categoria'] ?? null;
$productos = $productoController->listarProductos($categoria);

$idProductoDestacado = 20018; 
$productoDestacado = $productoController->obtenerProductoDestacado($idProductoDestacado);
if ($productoDestacado) {
    $imagen_ruta2 = preg_replace('/^\.\.\//', '', $productoDestacado['imagen']);
} else {
    $imagen_ruta2 = "img/no-disponible.jpg"; // Imagen por defecto
    $productoDestacado = [
        'nombre' => 'Producto no disponible',
        'descripcion' => 'Este producto destacado no se encuentra disponible actualmente.',
        'id_producto' => 0
    ];
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <!-- METAS -->
    <meta name="description" content="Descubre una amplia variedad de productos esenciales para el día a día. Calidad y confiabilidad desde 2010. Ubicados en Lima, Perú.">
    <meta name="keywords" content="Zeus Importaciones, comercio minorista, mayorista, productos diarios, encendedores, pilas, hisopos, cintas, Zeus fósforos, Zeus encendedores, marycrist">
    <meta name="author" content="Zeus Importaciones">
    <title>Zeus Importaciones - Comercio minorista y mayorista en Perú</title>
    
    <link rel="icon" href="img/ZEUS-removebg-preview.png">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://kit.fontawesome.com/05abdbeb44.js" crossorigin="anonymous"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- <script src="//code.tidio.co/hb6dpamuxbrkmsmspebdl8wcamzu1kpc.js" async></script> -->

</head>


<body>
    <div class="overlay2">
        <div class="overlay2Door"></div>
        <div class="overlay2Content">
            <div class="loader">
            <div class="inner"></div>
            </div>
        </div>
    </div>


    
    <div class="header">
        <div class="container" >
            <div class="navbar">
                <div class="logo">
                    <a href="index.php"><img src="img/ZEUS2.png" alt="logo" width="125px"></a>
                </div>

                <div class="barra">
                    <input type="text" name="" id="" placeholder="Buscar...">
                    <a href="#"><i class="fa-solid fa-magnifying-glass"></i></a>
                </div>
                <nav class="menu__link">
                    <ul id="MenuItems">
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="productos.php">Productos</a></li>
                        <li><a href="nosotros.php">Nosotros</a></li>
                        <li><a href="contactanos.php">Contactanos</a></li>
                        <li class="dropdown">
                        <?php if (usuarioAutenticado()): ?>
                            <a href="#" class="dropbtn"><?php echo obtenerNombreUsuario(); ?> <i class="fa fa-caret-down"></i></a>
                            <ul class="dropdown-content">
                               <li> <a href="perfil.php">Mi perfil</a></li>
                              <li>  <a href="compras.php">Mis compras</a></li>
                             <li>   <a href="#" onclick="confirmarLogout(event)">Cerrar sesión</a>
</li>
                            </ul>
                        <?php else: ?>
                            <a href="login.php">Login</a>
                        <?php endif; ?>
                        </li>
                    </ul>
                </nav>
    <!-- Carrito e icono de menú -->
                <div style="display: flex; align-items: center;">
                    <a class="cart-icon">
                        <img src="images/cart.png" alt="Carrito">
                    </a>
                    <img src="images/menu.png" class="menu-icon" onclick="toggleMobileMenu()">
                </div>
            </div>
            <div class="row">
                <div class="col-2" data-aos="fade-right" data-aos-duration="3000">
                    <h1>"Productos cotidianos, <br>calidad constante, <br>siempre contigo"</h1>
                    <p>El éxito no siempre se trata de grandeza. Se trata de coherencia. Coherente
                        el trabajo duro logra el éxito. La grandeza vendrá.</p>

                    <a href="contactanos.php" class="btn">Contáctanos &#8594;</a>
                </div>
                <div class="col-2" data-aos="fade-left" data-aos-duration="3000">
                    <img src="images/image1.png">
                </div>
            </div>
        </div>
    </div>

     <!-- Overlay del menú móvil -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="closeMobileMenu()"></div>

    <!-- Menú móvil -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <div class="logo">
                <img src="img/ZEUS2.png" alt="Zeus Logo">
            </div>
            <button class="mobile-menu-close" onclick="closeMobileMenu()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="mobile-menu-content">
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="productos.php">Productos</a></li>
                <li><a href="nosotros.php">Nosotros</a></li>
                <li><a href="contactanos.php">Contactanos</a></li>
                <li>
                     <?php if (usuarioAutenticado()): ?>
                    <a href="#" onclick="toggleMobileDropdown(event)">
                        <?php echo obtenerNombreUsuario(); ?> <i class="fa fa-caret-down" style="float: right; margin-top: 2px;"></i>
                    </a>
                    <ul class="mobile-dropdown" id="mobileDropdown" style="display: none;">
                        <li><a href="#">Mi perfil</a></li>
                        <li><a href="#">Mis compras</a></li>
                        <li><a href="#" class="logout-link" onclick="confirmarLogout(event)">Cerrar sesión</a>
</li>
                    </ul>
                     <?php else: ?>
                            <a href="login.php">Login</a>
                        <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>

   


    <!-- Featured Products -->

    <div class="small-container">
        <h2 class="title" data-aos="zoom-in-up" data-aos-duration="3000">Productos Destacados</h2>
        <div class="row" data-aos="fade-up" data-aos-duration="3000">
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
                    <!--  <p>S/. <?php echo number_format($row['precio'], 2); ?></p>-->
                </div>
        <?php
                $count++; // Incrementa el contador
            }
        } else {
            echo "<p>No se encontraron productos.</p>";
        }
        ?>
    </div>
        <h2 class="title" data-aos="zoom-in-up" data-aos-duration="3000">Ultimos Productos</h2>
         <div class="row" >
        <?php
        if ($productos->num_rows > 0) {
            $count = 0; // Inicializa el contador
            while ($row = $productos->fetch_assoc()) {
                if ($count >= 8) {
                    break; // Sal del bucle después de 4 registros
                }

                // Eliminar los '../' del inicio de la ruta de la imagen
                $imagen_ruta = preg_replace('/^\.\.\//', '', $row['imagen']);
        ?>
                <div class="col-4 card" data-aos="fade-up" data-aos-duration="3000">
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
                    <!--  <p>S/. <?php echo number_format($row['precio'], 2); ?></p>-->
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

    <!-- Offer -->


    
    <div class="offer" data-aos="zoom-in-up" data-aos-duration="3000" data-aos-delay="300">
        <div class="small-container" >
            <div class="row">
                
                <div class="col-2">
                    <img src="<?php echo htmlspecialchars($imagen_ruta2); ?>" class="offer-img" >
                </div>
                <div class="col-2">
                    <p>Disponible exclusivamente</p>
                    <h1><?php echo htmlspecialchars($productoDestacado['nombre']); ?></h1>
                    <small><?php echo htmlspecialchars($productoDestacado['descripcion']); ?><br></small>
                    <a href="productosDetalles.php?id=<?php echo $productoDestacado['id_producto']; ?>" class="btn" >Comprar ahora &#8594;</a>
                </div>
            </div>
        </div>
    </div>

 
    <!-- Brands -->

    <div class="brands">
        <div class="small-container">
            <div class="row">
                <div class="col-5" data-aos="fade-zoom-in" data-aos-duration="3000"data-aos-offset="300">
                    <img src="images/logo-duracell.png">
                </div>
                
                <div class="col-5" data-aos="fade-zoom-in" data-aos-duration="3000"data-aos-offset="300" >
                    <img src="images/logo.png">
                </div>
                <div class="col-5" data-aos="fade-zoom-in" data-aos-duration="3000"data-aos-offset="300">
                    <img src="images/logo-paypal.png">
                </div>
                <div class="col-5" data-aos="fade-zoom-in" data-aos-duration="3000" data-aos-offset="300">
                    <img src="images/logo-toshiba.png">
                </div>
                <div class="col-5" data-aos="fade-zoom-in" data-aos-duration="3000" data-aos-offset="300">
                    <img src="images/logo-panasonic.png">
                </div>
            </div>
        </div>
    </div>

  <!-- MODAL CARRITO -->
  <div class="modal" id="jsModalCarrito">
    <div class="modal__container">
        <button type="button" class="modal__close fa-solid fa-xmark jsModalClose"></button>
        <div class="modal__info">
            <div class="modal__header">
                <h2><i class="fa-brands fa-opencart"></i> Carrito</h2>
            </div>
            <div class="modal__body">
                <div class="modal__list">
                    
                </div>
            </div>
            <div class="modal__footer">
                <div class="modal__list-price">
                    <ul>
                        <!-- <li>Subtotal: <strong id="subtotal">S/. 0.00</strong></li>
                        <li>Descuento: <strong id="descuento">S/. 0.00</strong></li> -->
                    </ul>
                    <h4 class="modal__total-cart">Total: <strong id="total">S/. 0.00</strong></h4>
                </div>
                <!--<div style="margin: 10px 0;">
                    <button id="vaciarCarritoBtn" class="btn-border btn-danger" style="width: 100%;">
                        <i class="fa-solid fa-trash"></i> Vaciar Carrito
                    </button>
                </div>
                <div class="modal__btns">
                <a href="#" class="btn-border" onclick="procederAlCheckout()">Realizar Compra</a>-->
                 <!-- <a href="#" class="btn-border" onclick="enviarPedidoWhatsApp()">Pedir ahora!</a> -->
                    <!--<a href="#" class="btn-primary">Comprar Ahora</a>-->
                <!--</div>-->
                <div class="modal__btns d-flex justify-content-between gap-2">
                    <a href="#" class="btn-border btn-danger" id="vaciarCarritoBtn">
                        <i class="fa-solid fa-trash"></i> Vaciar Carrito
                    </a>
                    <a href="#" class="btn-border btn-primary" onclick="procederAlCheckout()">Realizar Compra</a>
                </div>
            </div>
        </div>
    </div>
</div>


    
    <?php require_once 'usuario/includes/footer.php'; ?>

   
    <!-- javascript -->
    <script src="../js/app.js"></script>    
    <script>
        var MenuItems = document.getElementById("MenuItems");
        MenuItems.style.maxHeight = "0px";
        function menutoggle() {
            if (MenuItems.style.maxHeight == "0px") {
                MenuItems.style.maxHeight = "100vh"
            }
            else {
                MenuItems.style.maxHeight = "0px"
            }
        }
    </script>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
    AOS.init();
    </script>
     <script src="../js/menu.js"></script>
   

   <script src="../js/script.js"></script>
  


</body>

</html>