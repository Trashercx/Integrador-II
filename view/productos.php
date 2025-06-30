<?php
session_start();
require_once 'usuario/includes/session_utils.php';
require_once '../controller/ProductoController.php';
require_once '../controller/PromocionController.php';
require_once 'usuario/includes/promocion_helpers.php';
require_once 'usuario/includes/recomendacion_helpers.php';

$productoController = new ProductoController();
$promocionController = new PromocionController();

$categoria = $_GET['categoria'] ?? null;
$productos = $productoController->listarProductos($categoria);

// Recoger productos para procesar promociones en bloque
$idsConPrecios = [];
$productosArray = [];

if ($productos && $productos->num_rows > 0) {
    while ($row = $productos->fetch_assoc()) {
        $productosArray[] = $row;
        $idsConPrecios[$row['id_producto']] = $row['precio'];
    }
}

$promociones = $promocionController->obtenerPromocionesPorProductoIDs($idsConPrecios);
$productosArray = asignarPromocionesALotes($productosArray, $promocionController);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Explora nuestro catálogo completo de productos, desde encendedores hasta pilas, diseñados para satisfacer tus necesidades diarias.">
  <meta name="keywords" content="productos, encendedores, pilas, cintas de embalaje, hisopos, Zeus fósforos, Zeus encendedores, Zeus Importaciones, catálogo, marycrist">
  <meta name="author" content="Zeus Importaciones">
  <title>Productos de calidad - Zeus Importaciones</title>

  <link rel="icon" href="img/ZEUS-removebg-preview.png">
  <link rel="stylesheet" href="../../../css/style.css" />
  <link rel="stylesheet" href="../../../css/estilos.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
  <script src="https://kit.fontawesome.com/05abdbeb44.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://cdn.lineicons.com/2.0/LineIcons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  
  <!-- SOLO ESTILOS VISUALES AGREGADOS -->
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
</head>

<body>
<?php require_once 'usuario/includes/header.php'; ?>

<div class="small-container">
  <div class="row row-2">
    <h2 data-aos="zoom-in-up" data-aos-duration="3000">
        <?php echo isset($_GET['categoria']) && $_GET['categoria'] != 'Predeterminado' ? $_GET['categoria'] : 'Todos los productos'; ?>
    </h2>
    <form action="" method="GET" data-aos="zoom-in-up" data-aos-duration="3000">
        <select name="categoria" onchange="this.form.submit()">
            <option value="Todos los productos" <?php echo (!isset($_GET['categoria']) || $_GET['categoria'] == 'Todos los productos') ? 'selected' : ''; ?>>Todos los productos</option>
            <option value="Higiene" <?php echo ($_GET['categoria'] ?? '') == 'Higiene' ? 'selected' : ''; ?>>Higiene</option>
            <option value="Ferreteria" <?php echo ($_GET['categoria'] ?? '') == 'Ferreteria' ? 'selected' : ''; ?>>Ferreteria</option>
            <option value="Hogar" <?php echo ($_GET['categoria'] ?? '') == 'Hogar' ? 'selected' : ''; ?>>Hogar</option>
        </select>
    </form>
  </div>

  <div class="row">
    <?php if (!empty($productosArray)) {
        foreach ($productosArray as $producto) {
            $imagen_ruta = preg_replace('/^\.\.\//', '', $producto['imagen']);
            $idProducto = $producto['id_producto'];

            $tienePromocion = $producto['tiene_promocion'] ?? false;
            $precioFinal = $producto['precio_promocional'] ?? $producto['precio'];
            $porcentaje = $producto['porcentaje_descuento'] ?? 0;
    ?>
        <div class="col-4 card <?php echo $tienePromocion ? 'producto-promocion' : ''; ?>" data-aos="fade-up" data-aos-duration="3000">
            <?php if ($tienePromocion) { ?>
                <div class="promo-badge">¡PROMOCIÓN!</div>
                <div class="descuento-porcentaje-badge">-<?php echo round($porcentaje); ?>%</div>
            <?php } ?>

            <a href="productosDetalles.php?id=<?php echo $idProducto; ?>">
                <img src="<?php echo htmlspecialchars($imagen_ruta); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" style="background-color: #f0f0f0;">
            </a>
            <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>

            <?php if ($tienePromocion) { ?>
                <div class="precio-container">
                    <span class="precio-original">S/. <?php echo number_format($producto['precio'], 2); ?></span>
                    <span class="precio-promocional">S/. <?php echo number_format($precioFinal, 2); ?></span>
                </div>
            <?php } else { ?>
                <p>S/. <?php echo number_format($producto['precio'], 2); ?></p>
            <?php } ?>
        </div>
    <?php
        }
    } else {
        echo "<p>No se encontraron productos.</p>";
    }
    ?>
  </div>
</div>

<?php
require_once 'usuario/includes/footer.php';
$conn->close();
?>

<script src="../js/app.js"></script>
<script>
    var MenuItems = document.getElementById("MenuItems");
    MenuItems.style.maxHeight = "0px";

    function menutoggle() {
        MenuItems.style.maxHeight = MenuItems.style.maxHeight === "0px" ? "100vh" : "0px";
    }
</script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>
<script src="../js/menu.js"></script>
</body>
</html>