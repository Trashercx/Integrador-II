<?php
session_start();
require_once 'usuario/includes/session_utils.php';
require_once '../controller/ContactoController.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contactoController = new ContactoController();
    $contactoController->guardar(); // Esto hará el redirect correctamente
}
?>



<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Ponte en contacto con Zeus Importaciones para tus necesidades de productos de uso cotidiano. Estamos aquí para ayudarte.">
    <meta name="keywords" content="contacto, formulario, productos diarios, Zeus Importaciones, Zeus fósforos, Zeus encendedores, Lima, Perú, marycrist">
    <meta name="author" content="Zeus Importaciones">
    <title>Contáctanos - Zeus Importaciones</title>
  <link rel="icon" href="img/ZEUS-removebg-preview.png">

  <link rel="stylesheet" href="../../../css/style.css" />
  <link rel="stylesheet" href="../../../css/estilos.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
  <script src="https://kit.fontawesome.com/05abdbeb44.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://cdn.lineicons.com/2.0/LineIcons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

</head>

<?php require_once 'usuario/includes/header.php'; ?>



  

<main>

<section class="contact-wrapper">
  <div class="contact-container">
    <div class="contact-row">
      <!-- Información de contacto -->
      <div class="contact-info" data-aos="fade-right" data-aos-duration="1500">
        <h2>Contáctanos</h2>
        <p>En Zeus Importaciones valoramos la comunicación. Si tienes preguntas sobre productos, ventas al por mayor o servicios, estamos listos para ayudarte.</p>

        <a href="https://wa.link/ziya4i" target="_blank"><i class="fa fa-phone"></i> 984 752 900</a>
        <a href="mailto:zeus@gmail.com" target="_blank"><i class="fa fa-envelope"></i> zeus@gmail.com</a>
        <a href="https://maps.app.goo.gl/Q8WUDrkwAcXYPZsD6" target="_blank"><i class="fa fa-map-marker"></i> Lima, Perú</a>
      </div>

      <!-- Formulario -->
      <form action="" method="post" class="contact-form" data-aos="fade-left" data-aos-duration="1500">
        <input type="text" name="nombre" class="contact-input" placeholder="Tu nombre" required />
        <input type="email" name="email" class="contact-input" placeholder="Tu correo" required />
        <textarea name="mensaje" class="contact-textarea" placeholder="Escribe tu mensaje..." required></textarea>
        <button type="submit" name="enviar" class="contact-btn">Enviar mensaje</button>
      </form>
    </div>
  </div>
</section>

</main>
<!-- Footer -->

<?php require_once 'usuario/includes/footer.php'; ?>

<!-- javascript -->



<script>

  var MenuItems = document.getElementById("MenuItems");

  MenuItems.style.maxHeight = "0px";



  function menutoggle() {

    if (MenuItems.style.maxHeight == "0px") {

      MenuItems.style.maxHeight = "100vh";

    } else {

      MenuItems.style.maxHeight = "0px";

    }

  }

</script>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
    AOS.init();
    </script>
<script src="../js/app.js"></script>  
<script src="../js/menu.js"></script>  
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_GET['success'])): ?>
  <script>
    Swal.fire({
      icon: 'success',
      title: '¡Mensaje enviado!',
      text: 'Tu mensaje fue recibido correctamente.',
      confirmButtonColor: '#ff523b'
    });

    // Elimina ?success=1 de la URL
    if (window.history.replaceState) {
      window.history.replaceState(null, null, window.location.pathname);
    }
  </script>
<?php elseif (isset($_GET['error'])): ?>
  <script>
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Hubo un problema al enviar tu mensaje. Inténtalo nuevamente.',
      confirmButtonColor: '#ff523b'
    });

    // Elimina ?error=1 de la URL
    if (window.history.replaceState) {
      window.history.replaceState(null, null, window.location.pathname);
    }
  </script>
<?php endif; ?>




</body>



</html>