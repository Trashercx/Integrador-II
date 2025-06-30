<?php
session_start();
require_once 'usuario/includes/session_utils.php';
require_once '../controller/ProductoController.php';
require_once '../controller/PromocionController.php';
require_once '../controller/RecomendacionController.php';

file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] ▶ Entrando a index.php\n", FILE_APPEND);

try {
    $productoController = new ProductoController();
    $promocionController = new PromocionController();
    $recomendacionController = new RecomendacionController();
    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] ✅ Controladores instanciados\n", FILE_APPEND);

    $categoria = $_GET['categoria'] ?? null;
    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] 🔄 Listando productos para categoría: " . ($categoria ?? 'todas') . "\n", FILE_APPEND);
    
    $productos = $productoController->listarProductos($categoria);
    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] 🧾 Productos listados: " . ($productos?->num_rows ?? 0) . "\n", FILE_APPEND);

    $productosConPromociones = $promocionController->obtenerProductosEnPromocion(4);
    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] 🎯 Promociones activas cargadas: " . count($productosConPromociones) . "\n", FILE_APPEND);

    if (usuarioAutenticado()) {
        $id_usuario = obtenerIdUsuario();
        file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] 👤 Usuario logueado (ID: $id_usuario)\n", FILE_APPEND);
        $recomendacionesPersonalizadas = $recomendacionController->generarRecomendacionesPersonalizadas($id_usuario, 4);
    } else {
        file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] 👥 Usuario anónimo\n", FILE_APPEND);
        $recomendacionesPersonalizadas = $recomendacionController->obtenerRecomendacionesAnonimas(4);
    }
    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] 📦 Recomendaciones generadas: " . count($recomendacionesPersonalizadas) . "\n", FILE_APPEND);

    $idProductoDestacado = 20018; 
    $productoDestacado = $productoController->obtenerProductoDestacado($idProductoDestacado);
    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] ⭐ Producto destacado cargado (ID: $idProductoDestacado)\n", FILE_APPEND);

    if ($productoDestacado) {
        $imagen_ruta2 = preg_replace('/^\.\.\//', '', $productoDestacado['imagen']);
        $promocionDestacado = $promocionController->verificarPromocionProducto($idProductoDestacado);
    } else {
        $imagen_ruta2 = "img/no-disponible.jpg";
        $productoDestacado = [
            'nombre' => 'Producto no disponible',
            'descripcion' => 'Este producto destacado no se encuentra disponible actualmente.',
            'id_producto' => 0
        ];
        $promocionDestacado = null;
    }

    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] ✅ Render listo para comenzar\n", FILE_APPEND);
} catch (Throwable $e) {
    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] ❌ Error fatal: " . $e->getMessage() . "\n", FILE_APPEND);
    die("Error interno. Revisa los logs.");
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

    <!-- <script src="//code.tidio.co/hb6dpamuxbrkmsmspebdl8wcamuxbrkmsmspebdl8wcamzu1kpc.js" async></script> -->

</head>
<style>
    /* Estilos para productos en promoción */
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

    /* Badge de promoción principal */
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

    /* Badge de porcentaje de descuento */
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

    @keyframes pulse {
        0% {
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
        }
        50% {
            box-shadow: 0 6px 30px rgba(46, 204, 113, 0.6);
            transform: scale(1.05);
        }
        100% {
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
        }
    }

    /* Container de precios mejorado */
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

    /* Efectos adicionales para productos en promoción */
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

    /* Mejoras responsivas */
    @media (max-width: 768px) {
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

    <!-- Productos en Oferta -->
    <?php if (!empty($productosConPromociones)): ?>
<div class="small-container">
    <h2 class="title" data-aos="zoom-in-up" data-aos-duration="3000">🔥 Productos en Oferta</h2>
    
    <!-- Carousel Container -->
    <div class="ofertas-carousel-container" data-aos="fade-up" data-aos-duration="3000">
        <div class="carousel-nav">
            <button class="carousel-btn carousel-prev" id="carouselPrev">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="carousel-btn carousel-next" id="carouselNext">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <div class="ofertas-carousel" id="ofertasCarousel">
            <div class="carousel-track" id="carouselTrack">
                <?php foreach ($productosConPromociones as $producto): 
                    $imagen_ruta = preg_replace('/^\.\.\//', '', $producto['imagen']);
                    $descuentoCalculado = $promocionController->calcularPrecioConDescuento($producto['precio'], $producto);
                    $precioConDescuento = $descuentoCalculado['precio_final'];
                    $porcentajeDescuento = round($descuentoCalculado['descuento_porcentaje']);
                ?>
                <div class="carousel-item">
                    <div class="oferta-card">
                        <!-- Discount Badge -->
                        <div class="discount-badge">
                            <span class="discount-percentage">-<?php echo $porcentajeDescuento; ?>%</span>
                            <span class="discount-text">OFF</span>
                        </div>
                        
                        <!-- Product Image -->
                        <div class="product-image-container">
                            <a href="productosDetalles.php?id=<?php echo $producto['id_producto']; ?>">
                                <img src="<?php echo htmlspecialchars($imagen_ruta); ?>" 
                                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                                     class="product-image">
                            </a>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="product-info">
                            <h4 class="product-title"><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                            
                            <!-- Rating -->
                            <div class="product-rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="far fa-star"></i>
                                </div>
                                <span class="rating-text">(4.0)</span>
                            </div>
                            
                            <!-- Price Section with Before/After -->
                            <div class="price-section">
                                <div class="price-comparison">
                                    <div class="price-before">
                                        <span class="label">Antes</span>
                                        <span class="old-price">S/. <?php echo number_format($producto['precio'], 2); ?></span>
                                    </div>
                                    <div class="price-arrow">
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                    <div class="price-after">
                                        <span class="label">Ahora</span>
                                        <span class="new-price">S/. <?php echo number_format($precioConDescuento, 2); ?></span>
                                    </div>
                                </div>
                                
                                <div class="savings">
                                    Ahorras: <span class="savings-amount">S/. <?php echo number_format($producto['precio'] - $precioConDescuento, 2); ?></span>
                                </div>
                            </div>
                            
                            <!-- Promotion Timer (si tienes campos de fecha) -->
                            <?php if (isset($producto['fecha_fin_promocion'])): ?>
                            <div class="promotion-timer">
                                <div class="timer-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="timer-text">
                                    <span class="timer-label">Oferta válida hasta:</span>
                                    <span class="timer-date"><?php echo date('d/m/Y', strtotime($producto['fecha_fin_promocion'])); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Action Button -->
                            <div class="product-actions">
                                <a href="productosDetalles.php?id=<?php echo $producto['id_producto']; ?>" class="btn-oferta">
                                    <i class="fas fa-shopping-cart"></i>
                                    Ver Oferta
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Carousel Indicators -->
        <div class="carousel-indicators" id="carouselIndicators">
            <!-- Se generan dinámicamente con JavaScript -->
        </div>
    </div>
</div>

<style>
/* Estilos para el Carousel de Ofertas */
.ofertas-carousel-container {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 0;
}

.carousel-nav {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    z-index: 10;
    pointer-events: none;
    transform: translateY(-50%);
}

.carousel-btn {
    position: absolute;
    width: 50px;
    height: 50px;
    border: none;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white;
    font-size: 18px;
    cursor: pointer;
    pointer-events: all;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.carousel-btn:hover {
    background: linear-gradient(135deg, #ee5a24, #ff6b6b);
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
}

.carousel-prev {
    left: -25px;
}

.carousel-next {
    right: -25px;
}

.ofertas-carousel {
    overflow: hidden;
    border-radius: 15px;
}

.carousel-track {
    display: flex;
    transition: transform 0.5s ease;
    gap: 20px;
}

.carousel-item {
    flex: 0 0 300px;
    min-width: 300px;
}

.oferta-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.oferta-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.discount-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    z-index: 5;
    text-align: center;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
}

.discount-percentage {
    font-weight: bold;
    font-size: 16px;
    display: block;
    line-height: 1;
}

.discount-text {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1px;
}

.product-image-container {
    position: relative;
    height: 200px;
    background: #f8f9fa;
    overflow: hidden;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.oferta-card:hover .product-image {
    transform: scale(1.05);
}

.product-info {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-title {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    line-height: 1.4;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 15px;
}

.stars {
    color: #ff6b6b;
    font-size: 14px;
}

.rating-text {
    color: #7f8c8d;
    font-size: 12px;
}

.price-section {
    margin-bottom: 15px;
}

.price-comparison {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 10px;
    border-left: 4px solid #ff6b6b;
}

.price-before, .price-after {
    text-align: center;
}

.price-before .label, .price-after .label {
    display: block;
    font-size: 11px;
    color: #7f8c8d;
    font-weight: 500;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.old-price {
    color: #95a5a6;
    text-decoration: line-through;
    font-size: 14px;
    font-weight: 500;
}

.new-price {
    color: #27ae60;
    font-size: 18px;
    font-weight: bold;
}

.price-arrow {
    color: #ff6b6b;
    font-size: 16px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.savings {
    text-align: center;
    font-size: 12px;
    color: #27ae60;
    font-weight: 600;
}

.savings-amount {
    font-weight: bold;
}

.promotion-timer {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    border-radius: 8px;
    margin-bottom: 15px;
    border-left: 3px solid #fdcb6e;
}

.timer-icon {
    color: #e17055;
    font-size: 14px;
}

.timer-text {
    flex: 1;
}

.timer-label {
    display: block;
    font-size: 10px;
    color: #8b6914;
    font-weight: 500;
    margin-bottom: 2px;
}

.timer-date {
    font-size: 12px;
    font-weight: bold;
    color: #8b6914;
}

.product-actions {
    margin-top: auto;
}

.btn-oferta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-oferta:hover {
    background: linear-gradient(135deg, #0984e3, #74b9ff);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(116, 185, 255, 0.4);
    color: white;
    text-decoration: none;
}

.carousel-indicators {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 20px;
}

.carousel-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #bdc3c7;
    cursor: pointer;
    transition: all 0.3s ease;
}

.carousel-indicator.active {
    background: #ff6b6b;
    transform: scale(1.2);
}

/* Responsive Design */
@media (max-width: 768px) {
    .carousel-item {
        flex: 0 0 280px;
        min-width: 280px;
    }
    
    .carousel-btn {
        width: 40px;
        height: 40px;
        font-size: 14px;
    }
    
    .carousel-prev {
        left: -20px;
    }
    
    .carousel-next {
        right: -20px;
    }
    
    .price-comparison {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .price-arrow {
        transform: rotate(90deg);
    }
}

@media (max-width: 480px) {
    .carousel-item {
        flex: 0 0 250px;
        min-width: 250px;
    }
    
    .oferta-card {
        margin: 0 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('ofertasCarousel');
    const track = document.getElementById('carouselTrack');
    const prevBtn = document.getElementById('carouselPrev');
    const nextBtn = document.getElementById('carouselNext');
    const indicatorsContainer = document.getElementById('carouselIndicators');
    
    if (!track) return;
    
    const items = track.querySelectorAll('.carousel-item');
    const itemWidth = items[0].offsetWidth + 20; // incluye el gap
    const visibleItems = Math.floor(carousel.offsetWidth / itemWidth);
    const maxScroll = Math.max(0, items.length - visibleItems);
    
    let currentIndex = 0;
    
    // Crear indicadores
    for (let i = 0; i <= maxScroll; i++) {
        const indicator = document.createElement('div');
        indicator.className = 'carousel-indicator';
        if (i === 0) indicator.classList.add('active');
        indicator.addEventListener('click', () => goToSlide(i));
        indicatorsContainer.appendChild(indicator);
    }
    
    const indicators = indicatorsContainer.querySelectorAll('.carousel-indicator');
    
    function updateCarousel() {
        const translateX = -currentIndex * itemWidth;
        track.style.transform = `translateX(${translateX}px)`;
        
        // Actualizar indicadores
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentIndex);
        });
        
        // Actualizar botones
        prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
        nextBtn.style.opacity = currentIndex === maxScroll ? '0.5' : '1';
    }
    
    function goToSlide(index) {
        currentIndex = Math.max(0, Math.min(maxScroll, index));
        updateCarousel();
    }
    
    function nextSlide() {
        if (currentIndex < maxScroll) {
            currentIndex++;
            updateCarousel();
        }
    }
    
    function prevSlide() {
        if (currentIndex > 0) {
            currentIndex--;
            updateCarousel();
        }
    }
    
    // Event listeners
    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);
    
    // Auto-scroll (opcional)
    let autoScrollInterval = setInterval(nextSlide, 5000);
    
    // Pausar auto-scroll al hacer hover
    carousel.addEventListener('mouseenter', () => {
        clearInterval(autoScrollInterval);
    });
    
    carousel.addEventListener('mouseleave', () => {
        autoScrollInterval = setInterval(nextSlide, 5000);
    });
    
    // Touch/swipe support para móviles
    let startX = 0;
    let currentX = 0;
    let isDragging = false;
    
    carousel.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
        clearInterval(autoScrollInterval);
    });
    
    carousel.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        currentX = e.touches[0].clientX;
        e.preventDefault();
    });
    
    carousel.addEventListener('touchend', () => {
        if (!isDragging) return;
        isDragging = false;
        
        const diffX = startX - currentX;
        if (Math.abs(diffX) > 50) {
            if (diffX > 0) {
                nextSlide();
            } else {
                prevSlide();
            }
        }
        
        autoScrollInterval = setInterval(nextSlide, 5000);
    });
    
    // Responsive handling
    window.addEventListener('resize', () => {
        const newVisibleItems = Math.floor(carousel.offsetWidth / itemWidth);
        const newMaxScroll = Math.max(0, items.length - newVisibleItems);
        
        if (currentIndex > newMaxScroll) {
            currentIndex = newMaxScroll;
        }
        
        updateCarousel();
    });
    
    // Inicializar
    updateCarousel();
});
</script>
<?php endif; ?>

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
                
                // Verificar si el producto tiene promoción activa
                $promocion = $promocionController->verificarPromocionProducto($row['id_producto']);
        ?>
                <div class="col-4 card <?php echo $promocion ? 'producto-promocion' : ''; ?>">
                    <?php if ($promocion): 
                        $descuentoCalculado = $promocionController->calcularPrecioConDescuento($row['precio'], $promocion);
                        $precioConDescuento = $descuentoCalculado['precio_final'];
                        $porcentajeDescuento = round($descuentoCalculado['descuento_porcentaje']);

                    ?>
                        <div class="promo-badge">-<?php echo $porcentajeDescuento; ?>%</div>
                    <?php endif; ?>
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
                    <?php if ($promocion): ?>
                        <div class="precio-container">
                            <span class="precio-original">S/. <?php echo number_format($row['precio'], 2); ?></span>
                            <span class="precio-promocional">S/. <?php echo number_format($precioConDescuento, 2); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
        <?php
                $count++; // Incrementa el contador
            }
        } else {
            echo "<p>No se encontraron productos.</p>";
        }
        ?>
    </div>

    <!-- Recomendaciones Personalizadas -->
    <?php if (!empty($recomendacionesPersonalizadas)): ?>
        <h2 class="title" data-aos="zoom-in-up" data-aos-duration="3000">
            <?php echo usuarioAutenticado() ? 'Recomendado para ti' : 'Productos Populares'; ?>
        </h2>
        <div class="row">
        <?php foreach ($recomendacionesPersonalizadas as $producto): 
            $imagen_ruta = preg_replace('/^\.\.\//', '', $producto['imagen']);
            // Verificar si el producto recomendado tiene promoción
            $promocion = $promocionController->verificarPromocionProducto($producto['id_producto']);
        ?>
            <div class="col-4 card <?php echo $promocion ? 'producto-promocion' : ''; ?>" data-aos="fade-up" data-aos-duration="3000">
                <?php if ($promocion): 
                    $descuentoCalculado = $promocionController->calcularPrecioConDescuento($producto['precio'], $promocion);
                    $precioConDescuento = $descuentoCalculado['precio_final'];
                    $porcentajeDescuento = round($descuentoCalculado['descuento_porcentaje']);

                ?>
                    <div class="promo-badge">-<?php echo $porcentajeDescuento; ?>%</div>
                <?php endif; ?>
                <a href="productosDetalles.php?id=<?php echo $producto['id_producto']; ?>">
                    <img src="<?php echo htmlspecialchars($imagen_ruta); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" style="background-color: #f0f0f0;">
                </a>
                <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                <div class="rating">
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star-o"></i>
                </div>
                <?php if ($promocion): ?>
                    <div class="precio-container">
                        <span class="precio-original">S/. <?php echo number_format($producto['precio'], 2); ?></span>
                        <span class="precio-promocional">S/. <?php echo number_format($precioConDescuento, 2); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>

        <h2 class="title" data-aos="zoom-in-up" data-aos-duration="3000">Ultimos Productos</h2>
         <div class="row" >
        <?php
        if ($productos->num_rows > 0) {
            $count = 0; // Inicializa el contador
            while ($row = $productos->fetch_assoc()) {
                if ($count >= 8) {
                    break; // Sal del bucle después de 8 registros
                }

                // Eliminar los '../' del inicio de la ruta de la imagen
                $imagen_ruta = preg_replace('/^\.\.\//', '', $row['imagen']);
                
                // Verificar si el producto tiene promoción activa
                $promocion = $promocionController->verificarPromocionProducto($row['id_producto']);
                echo "<!-- Producto ID: " . $row['id_producto'] . " - Promoción: " . ($promocion ? 'SÍ' : 'NO') . " -->";

        ?>
                <div class="col-4 card <?php echo $promocion ? 'producto-promocion' : ''; ?>" data-aos="fade-up" data-aos-duration="3000">
                    <?php if ($promocion): 
                        $descuentoCalculado = $promocionController->calcularPrecioConDescuento($row['precio'], $promocion);
                        $precioConDescuento = $descuentoCalculado['precio_final'];
                        $porcentajeDescuento = round($descuentoCalculado['descuento_porcentaje']);

                    ?>
                        <div class="promo-badge">-<?php echo $porcentajeDescuento; ?>%</div>
                    <?php endif; ?>
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
                    <?php if ($promocion): ?>
                        <div class="precio-container">
                            <span class="precio-original">S/. <?php echo number_format($row['precio'], 2); ?></span>
                            <span class="precio-promocional">S/. <?php echo number_format($precioConDescuento, 2); ?></span>
                        </div>
                    <?php endif; ?>
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
                    <?php if ($promocionDestacado): 
                        $precioConDescuentoDestacado = $promocionController->calcularPrecioConDescuento($productoDestacado['precio'], $promocionDestacado);
                        $porcentajeDescuentoDestacado = round((($productoDestacado['precio'] - $precioConDescuentoDestacado) / $productoDestacado['precio']) * 100);
                    ?>
                        <div class="precio-oferta">
                            <span class="precio-original-oferta">S/. <?php echo number_format($productoDestacado['precio'], 2); ?></span>
                            <span class="precio-promocional-oferta">S/. <?php echo number_format($precioConDescuentoDestacado, 2); ?></span>
                            <span class="descuento-oferta">¡<?php echo $porcentajeDescuentoDestacado; ?>% OFF!</span>
                        </div>
                    <?php endif; ?>
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