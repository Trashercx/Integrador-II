<?php
session_start();
require_once 'usuario/includes/session_utils.php';

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

</head>




<?php
require_once 'usuario/includes/header.php';
require_once '../controller/ProductoController.php';

$productoController = new ProductoController();
$categoria = $_GET['categoria'] ?? null;
$productos = $productoController->listarProductos($categoria);


?>



<!-- All Products -->
<div class="small-container">
    <div class="row row-2">
        <h2 data-aos="zoom-in-up" data-aos-duration="3000"><?php echo (isset($_GET['categoria']) && $_GET['categoria'] != 'Predeterminado' ? $_GET['categoria'] : 'Todos los productos'); ?></h2>
        <form action="" method="GET" data-aos="zoom-in-up" data-aos-duration="3000">
            <select name="categoria" onchange="this.form.submit()" >
                <option value="Todos los productos" <?php echo (!isset($_GET['categoria']) || $_GET['categoria'] == 'Todos los productos') ? 'selected' : ''; ?>>Todos los productos</option>
                <option value="Higiene" <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == 'Higiene') ? 'selected' : ''; ?>>Higiene</option>
                <option value="Ferreteria" <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == 'Ferreteria') ? 'selected' : ''; ?>>Ferreteria</option>
                <option value="Hogar" <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == 'Hogar') ? 'selected' : ''; ?>>Hogar</option>
            </select>
        </form>
    </div>
    <div class="row">
        <?php
        if ($productos->num_rows > 0) {
            while ($row = $productos->fetch_assoc()) {
                // Eliminar los '../' del inicio de la ruta de la imagen
                $imagen_ruta = preg_replace('/^\.\.\//', '', $row['imagen']);
        ?>
                <div class="col-4 card" data-aos="fade-up" data-aos-duration="3000">
                    <a href="productosDetalles.php?id=<?php echo $row['id_producto']; ?>">
                        <img src="<?php echo htmlspecialchars($imagen_ruta); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>" style="background-color: #f0f0f0;">
                    </a>
                    <h4><?php echo htmlspecialchars($row['nombre']); ?></h4>
                    <!--   <div class="rating">
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star-o"></i>
                    </div>-->
                  <p>S/. <?php echo number_format($row['precio'], 2); ?></p>
                </div>
        <?php
            }
        } else {
            echo "<p>No se encontraron productos.</p>";
        }
        ?>
    </div>

    <!-- Aquí puedes agregar la paginación si es necesario -->

</div>

<!-- Footer -->
<?php
require_once 'usuario/includes/footer.php';
$conn->close();
?>

<!-- javascript -->
<script src="../js/app.js"></script>    

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
</script>
</script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
    AOS.init();
    </script>
    <script src="../js/menu.js"></script>
   


</body>

</body>

</html>