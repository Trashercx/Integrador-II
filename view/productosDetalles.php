<?php
session_start();
require_once 'usuario/includes/session_utils.php';
require_once '../controller/ProductoController.php';
require_once '../controller/PromocionController.php';
require_once '../controller/RecomendacionController.php';

$productoController = new ProductoController();
$promocionController = new PromocionController();
$recomendacionController = new RecomendacionController();

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

$promocionActual = $promocionController->verificarPromocionProducto($idProducto);
$tienePromocion = !empty($promocionActual);

$precioMostrar = $productoDetalle['precio'];
$porcentajeDescuento = 0;
$calculoDescuento = null;

if ($tienePromocion) {
    $calculoDescuento = $promocionController->calcularPrecioConDescuento($productoDetalle['precio'], $promocionActual);
    $precioMostrar = $calculoDescuento['precio_final'];
    $porcentajeDescuento = $calculoDescuento['descuento_porcentaje'];
}

$productosRelacionados = $recomendacionController->obtenerProductosRelacionados($idProducto, 4);

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

  <!-- Estilos para promociones -->
  <style>
    /* Badge de oferta especial */
    /* Badge principal de promoción en producto individual */
.producto-promo-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 10;
    background: linear-gradient(45deg, #ff6b6b, #ff3838);
    color: white;
    padding: 8px 16px;
    border-radius: 25px;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
    transform: rotate(-5deg);
    animation: pulse 2s infinite;
}

.producto-promo-badge .promo-text {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: block;
    margin-bottom: 2px;
}

.producto-promo-badge .descuento-text {
    font-size: 14px;
    font-weight: 800;
    color: #fff200;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

/* Animación pulse unificada */
@keyframes pulse {
    0% { 
        transform: rotate(-5deg) scale(1);
        box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
    }
    50% { 
        transform: rotate(-5deg) scale(1.05);
        box-shadow: 0 6px 30px rgba(46, 204, 113, 0.6);
    }
    100% { 
        transform: rotate(-5deg) scale(1);
        box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
    }
}

/* Contenedor de precios mejorado para vista detalle */
.precio-detalle-container {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border: 2px solid #ff6b6b;
    border-radius: 15px;
    padding: 20px;
    margin: 20px 0;
    position: relative;
    box-shadow: 0 8px 25px rgba(255, 107, 107, 0.1);
}

.precio-detalle-container::before {
    content: "💰";
    position: absolute;
    top: -10px;
    left: 15px;
    background: white;
    padding: 5px 10px;
    border-radius: 50%;
    font-size: 18px;
}

.precio-original-detalle {
    font-size: 18px;
    color: #6c757d;
    text-decoration: line-through;
    text-decoration-color: #dc3545;
    text-decoration-thickness: 2px;
    font-weight: 400;
    display: block;
    margin-bottom: 5px;
    opacity: 0.8;
}

.precio-promocional-detalle {
    font-size: 32px;
    color: #28a745;
    font-weight: 700;
    margin: 0;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    display: inline-block;
}

.ahorro-info {
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    margin-top: 10px;
    display: inline-block;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.ahorro-info i {
    margin-right: 5px;
}

.promocion-descripcion {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 12px 15px;
    border-radius: 10px;
    margin-top: 15px;
    font-size: 14px;
    font-style: italic;
}

.promocion-descripcion i {
    color: #ffc107;
    margin-right: 8px;
}

/* El botón mantiene su estilo original */
.btn-promocion {
    text-align: center !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: auto !important;
}

/* Productos relacionados con promoción */
.con-promocion {
    border: 2px solid #ff6b6b !important;
    box-shadow: 0 8px 25px rgba(255, 107, 107, 0.15) !important;
    transform: scale(1.02);
    transition: all 0.3s ease;
}

/* Badges pequeños para productos en grilla */
.promo-badge-small {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(45deg, #ff6b6b, #ff3838);
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    z-index: 5;
    box-shadow: 0 2px 8px rgba(255, 107, 107, 0.4);
}

.descuento-badge-small {
    position: absolute;
    top: 35px;
    right: 10px;
    background: #fff200;
    color: #333;
    padding: 3px 6px;
    border-radius: 8px;
    font-size: 9px;
    font-weight: 800;
    z-index: 5;
    box-shadow: 0 2px 8px rgba(255, 242, 0, 0.4);
}

.precio-container-small {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}

.precio-original-small {
    font-size: 14px;
    color: #6c757d;
    text-decoration: line-through;
    text-decoration-color: #dc3545;
    text-decoration-thickness: 1px;
}

.precio-promocional-small {
    font-size: 18px;
    color: #28a745;
    font-weight: 700;
}

/* Producto con promoción - Estilo de tarjeta */
.producto-promocion {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    background: linear-gradient(white, white) padding-box,
                linear-gradient(45deg, #ff6b6b, #ee5a24) border-box;
}

.producto-promocion:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(238, 90, 36, 0.15);
}

/* Badge diagonal de promoción */
.promo-badge {
    position: absolute;
    top: 15px;
    left: -35px;
    background: linear-gradient(45deg, #ff6b6b, #ee5a24);
    color: white;
    padding: 8px 45px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    transform: rotate(-45deg);
    z-index: 10;
    box-shadow: 0 4px 15px rgba(238, 90, 36, 0.3);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

/* Badge circular de porcentaje de descuento */
.descuento-porcentaje-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white;
    padding: 8px 12px;
    border-radius: 50%;
    font-size: 14px;
    font-weight: 800;
    min-width: 45px;
    min-height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
    border: 3px solid white;
    animation: pulse 2s infinite;
}

/* Container de precios unificado */
.precio-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    margin-top: 10px;
}

/* Precio original tachado */
.precio-original {
    color: #999;
    text-decoration: line-through;
    font-size: 14px;
    font-weight: 400;
    position: relative;
}

.precio-original::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: #e74c3c;
    transform: translateY(-50%);
}

/* Precio promocional destacado */
.precio-promocional {
    color: #e74c3c;
    font-size: 18px;
    font-weight: 700;
    text-shadow: 0 1px 3px rgba(231, 76, 60, 0.2);
    position: relative;
}

.precio-promocional::before {
    content: '🔥';
    position: absolute;
    left: -25px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    animation: fire 1.5s ease-in-out infinite alternate;
}

@keyframes fire {
    0% {
        transform: translateY(-50%) scale(1);
    }
    100% {
        transform: translateY(-50%) scale(1.1);
    }
}

/* Efecto de brillo al hover */
.producto-promocion::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.5s;
    z-index: 1;
}

.producto-promocion:hover::before {
    left: 100%;
}

/* Responsive Design */
@media (max-width: 768px) {
    .producto-promo-badge {
        top: 5px;
        left: 5px;
        padding: 6px 12px;
        transform: rotate(-3deg);
    }
    
    .precio-detalle-container {
        padding: 15px;
        margin: 15px 0;
    }
    
    .precio-promocional-detalle {
        font-size: 24px;
    }
    
    .btn-promocion {
        padding: 12px 25px !important;
        font-size: 14px !important;
    }
    
    .promo-badge {
        font-size: 10px;
        padding: 6px 35px;
    }
    
    .descuento-porcentaje-badge {
        min-width: 35px;
        min-height: 35px;
        font-size: 12px;
        top: 10px;
        right: 10px;
    }
    
    .precio-promocional::before {
        left: -20px;
        font-size: 14px;
    }
}
  </style>

</head>

<?php
require_once 'usuario/includes/header.php';
?>

<!-- productoDetalle -->
<div class="small-container single-product">
    <div class="row">
        <div class="col-2" style="position: relative;">
            <?php if ($tienePromocion) { ?>
                <div class="producto-promo-badge">
                    <span class="promo-text">¡OFERTA ESPECIAL!</span>
                    <span class="descuento-text">-<?php echo $porcentajeDescuento; ?>% OFF</span>
                </div>
            <?php } ?>

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

            <?php if ($tienePromocion) { ?>
                <div class="precio-detalle-container">
                    <span class="precio-original-detalle">S/. <?php echo number_format($productoDetalle['precio'], 2); ?></span>
                    <h4 class="precio-promocional-detalle">S/. <?php echo number_format($precioMostrar, 2); ?></h4>
                    <div class="ahorro-info">
                        <i class="fa fa-tag"></i>
                        ¡Ahorras S/. <?php echo number_format($productoDetalle['precio'] - $precioMostrar, 2); ?>!
                    </div>
                    <?php if (!empty($promocionActual['descripcion'])) { ?>
                        <div class="promocion-descripcion">
                            <i class="fa fa-info-circle"></i>
                            <?php echo htmlspecialchars($promocionActual['descripcion']); ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <h4>S/. <?php echo number_format($productoDetalle['precio'], 2); ?></h4>
            <?php } ?>
            
            <a href="#" 
                class="product-grid__btn btn-default <?php echo $tienePromocion ? 'btn-promocion' : ''; ?>" 
                data-btn-action="add-btn-cart" 
                data-modal="#jsModalCarrito"
                data-id="<?php echo $productoDetalle['id_producto']; ?>"
                data-nombre="<?php echo htmlspecialchars($productoDetalle['nombre']); ?>"
                data-precio="<?php echo htmlspecialchars($precioMostrar); ?>"
                data-imagen="<?php echo htmlspecialchars($imagen_ruta); ?>"
                onclick="agregarAlCarrito(
                    '<?php echo $productoDetalle['id_producto']; ?>',
                    '<?php echo $productoDetalle['nombre']; ?>',
                    '<?php echo number_format($precioMostrar, 2); ?>',
                    '<?php echo htmlspecialchars($imagen_ruta); ?>'
                )">
                <?php echo $tienePromocion ? '🛒 ¡Aprovecha la Oferta!' : 'Añadir al carrito'; ?>
            </a>

            <h3>Detalles del producto<i class="fa fa-indent"></i></h3>
            <br>
            <p><?php echo htmlspecialchars($productoDetalle['descripcion']); ?></p>
        </div>
    </div>
</div>

<!-- Productos Relacionados -->
<?php if (!empty($productosRelacionados)) { ?>
<div class="small-container">
    <div class="row row-2">
        <h2 data-aos="fade-up" data-aos-duration="2000">
            <i class="fa fa-heart"></i> Productos Relacionados
        </h2>
        <a href="productos.php?categoria=<?php echo urlencode($productoDetalle['nombre_categoria']); ?>">
            <h3>Ver más de <?php echo htmlspecialchars($productoDetalle['nombre_categoria']); ?> &#8594;</h3>
        </a>
    </div>
    
    <div class="productos-relacionados-carrusel" data-aos="fade-up" data-aos-duration="2500">
        <div class="row productos-relacionados-row">
            <?php foreach ($productosRelacionados as $relacionado) { 
                $imagen_relacionado = preg_replace('/^\.\.\//', '', $relacionado['imagen']);
                $promocionRelacionado = $promocionController->verificarPromocionProducto($relacionado['id_producto']);
                $tienePromocionRel = !empty($promocionRelacionado);
                $precioRelacionado = $relacionado['precio'];
                $porcentajeDescuentoRel = 0;
                
                if ($tienePromocionRel) {
                    $calculoDescuentoRel = $promocionController->calcularPrecioConDescuento($relacionado['precio'], $promocionRelacionado);
                    $precioRelacionado = $calculoDescuentoRel['precio_final'];
                    $porcentajeDescuentoRel = $calculoDescuentoRel['descuento_porcentaje'];
                }
            ?>
                <div class="col-4 card producto-relacionado <?php echo $tienePromocionRel ? 'con-promocion' : ''; ?>" data-aos="zoom-in" data-aos-duration="1500" style="position: relative;">
                    <?php if ($tienePromocionRel) { ?>
                        <div class="promo-badge-small">¡OFERTA!</div>
                        <div class="descuento-badge-small">-<?php echo $porcentajeDescuentoRel; ?>%</div>
                    <?php } ?>
                    
                    <a href="productosDetalles.php?id=<?php echo $relacionado['id_producto']; ?>">
                        <img src="<?php echo htmlspecialchars($imagen_relacionado); ?>" alt="<?php echo htmlspecialchars($relacionado['nombre']); ?>" style="background-color: #f0f0f0;">
                    </a>
                    <h4><?php echo htmlspecialchars($relacionado['nombre']); ?></h4>
                    
                    <?php if ($tienePromocionRel) { ?>
                        <div class="precio-container-small">
                            <span class="precio-original-small">S/. <?php echo number_format($relacionado['precio'], 2); ?></span>
                            <span class="precio-promocional-small">S/. <?php echo number_format($precioRelacionado, 2); ?></span>
                        </div>
                    <?php } else { ?>
                        <p>S/. <?php echo number_format($relacionado['precio'], 2); ?></p>
                    <?php } ?>
                    
                    <div class="relacionado-info">
                        <small><i class="fa fa-heart"></i> Te puede interesar</small>
                    </div>
                </div>
            <?php } ?>
        </div>
        
        <!-- Controles del carrusel -->
        <div class="carrusel-controles">
            <button class="carrusel-btn prev-btn" onclick="moverCarrusel(-1)">
                <i class="fa fa-chevron-left"></i>
            </button>
            <button class="carrusel-btn next-btn" onclick="moverCarrusel(1)">
                <i class="fa fa-chevron-right"></i>
            </button>
        </div>
    </div>
</div>
<?php } ?>

<!-- Más Productos -->
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
        $categoria = $_GET['categoria'] ?? null;
        $productos = $productoController->listarProductos($categoria);
        
        if ($productos->num_rows > 0) {
            $count = 0;
            while ($row = $productos->fetch_assoc()) {
                if ($count >= 4) {
                    break;
                }
                
                // Saltar el producto actual
                if ($row['id_producto'] == $idProducto) {
                    continue;
                }

                $imagen_ruta = preg_replace('/^\.\.\//', '', $row['imagen']);
                $promocion = $promocionController->verificarPromocionProducto($row['id_producto']);
                $tienePromocion = !empty($promocion);
                $precioMostrar = $row['precio'];
                
                if ($tienePromocion) {
                    $calculoDescuentoMas = $promocionController->calcularPrecioConDescuento($row['precio'], $promocion);
                    $precioMostrar = $calculoDescuentoMas['precio_final'];                }
        ?>
                <div class="col-4 card" style="position: relative;">
                    <?php if ($tienePromocion) { ?>
                        <div class="promo-badge-small">¡PROMOCIÓN!</div>
                    <?php } ?>
                    
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
                    
                    <?php if ($tienePromocion) { ?>
                        <div class="precio-container-small">
                            <span class="precio-original-small">S/. <?php echo number_format($row['precio'], 2); ?></span>
                            <span class="precio-promocional-small">S/. <?php echo number_format($precioMostrar, 2); ?></span>
                        </div>
                    <?php } else { ?>
                        <p>S/. <?php echo number_format($row['precio'], 2); ?></p>
                    <?php } ?>
                </div>
        <?php
                $count++;
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

<!-- Carrusel de productos relacionados -->
<script>
let currentIndex = 0;
const productosVisibles = 4;

function moverCarrusel(direccion) {
    const productosRelacionados = document.querySelectorAll('.producto-relacionado');
    const totalProductos = productosRelacionados.length;
    const maxIndex = Math.max(0, totalProductos - productosVisibles);
    
    currentIndex += direccion;
    
    if (currentIndex < 0) {
        currentIndex = 0;
    } else if (currentIndex > maxIndex) {
        currentIndex = maxIndex;
    }
    
    const offset = -(currentIndex * (100 / productosVisibles));
    document.querySelector('.productos-relacionados-row').style.transform = `translateX(${offset}%)`;
    
    // Actualizar estado de los botones
    document.querySelector('.prev-btn').style.opacity = currentIndex === 0 ? '0.5' : '1';
    document.querySelector('.next-btn').style.opacity = currentIndex === maxIndex ? '0.5' : '1';
}

// Inicializar carrusel
document.addEventListener('DOMContentLoaded', function() {
    moverCarrusel(0); // Establecer estado inicial
});
</script>

<script src="../js/menu.js"></script>

</body>

</html>