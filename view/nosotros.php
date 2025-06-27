<?php
session_start();
require_once 'usuario/includes/session_utils.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Zeus Importaciones, líder en comercio minorista y mayorista desde 2010. Conoce nuestra misión, visión y compromiso con la calidad.">
<meta name="keywords" content="nosotros, misión, visión, valores, Zeus Importaciones, comercio en Perú, Zeus fósforos, Zeus encendedores, marycrist">
<meta name="author" content="Zeus Importaciones">
<title>Conoce a Zeus Importaciones - Misión, Visión y Valores</title>
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


?>


<section class="nos-hero">
  <div class="nos-container"  data-aos="fade-up-left"  data-aos-duration="2000"data-aos-offset="300">
    <div class="nos-hero-content">
      <div class="nos-text">
        <h1>Sobre Nosotros</h1>
        <p>Somos <strong>Zeus Importaciones</strong>, una empresa peruana con más de una década de experiencia en el mercado de productos esenciales. Nuestra pasión es brindar soluciones de calidad para el día a día.</p>
        <a href="contactanos.php" class="nos-btn">Contáctanos</a>
      </div>
      <div class="nos-image">
        <img src="img/zeus.png" alt="Imagen Zeus" />
      </div>
    </div>
  </div>
</section>

<section class="nos-valores">
  <div class="nos-container">
    <h2 class="nos-title" data-aos="flip-up" data-aos-duration="3000">Nuestros Pilares</h2>
    <div class="nos-cards">
      <div class="nos-card" data-aos="zoom-in-up" data-aos-duration="2000">
        <i class="fa fa-diamond"></i>
        <h3>Calidad</h3>
        <p>Productos seleccionados cuidadosamente para garantizar su durabilidad y efectividad.</p>
      </div>
      <div class="nos-card" data-aos="zoom-in-up" data-aos-duration="2000">
        <i class="fa fa-smile-o"></i>
        <h3>Servicio</h3>
        <p>Atención cercana y eficiente, con un equipo comprometido a ayudarte siempre.</p>
      </div>
      <div class="nos-card" data-aos="zoom-in-up" data-aos-duration="2000">
        <i class="fa fa-rocket"></i>
        <h3>Entrega</h3>
        <p>Despachos puntuales a nivel nacional con seguimiento en tiempo real.</p>
      </div>
      <div class="nos-card" data-aos="zoom-in-up" data-aos-duration="2000">
        <i class="fa fa-leaf"></i>
        <h3>Responsabilidad</h3>
        <p>Trabajamos con ética y conciencia social, cuidando nuestro entorno.</p>
      </div>
    </div>
  </div>
</section>

<section class="nos-ubicacion">
  <div class="nos-container">
    <div class="nos-ubicacion-content">
      <div class="nos-ubicacion-text" data-aos="zoom-in-right" data-aos-duration="2000">
        <h2>¿Dónde nos encuentras?</h2>
        <p>Jr. Cuzco 669, Int. 131, Lima - Perú</p>
        <a href="https://maps.app.goo.gl/JPGp4N6DQFyVKC8y6" target="_blank" class="nos-btn">Ver en mapa</a>
      </div>
      <div class="nos-ubicacion-mapa" data-aos="zoom-in-left" data-aos-duration="2000">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3901.8678105828944!2d-77.02692139999999!3d-12.052615!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105c8ba107e8ed7%3A0x9a41c0e92be7893c!2s131%2C%20Jr.%20Cusco%20669%2C%20Lima%2015001!5e0!3m2!1ses-419!2spe!4v1721157715511!5m2!1ses-419!2spe" width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    </div>
  </div>
</section>






    <?php require_once 'usuario/includes/footer.php'; ?>

    

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
    <script src="../js/app.js"></script>    
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
    AOS.init();
    </script>
    <script src="../js/menu.js"></script>




</body>    
