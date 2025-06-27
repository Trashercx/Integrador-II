 <!-- Footer -->

 <div class="footer">

        <div class="container">

            <div class="row">

                <div class="footer-col-1">

                    <h3>Contactanós</h3>

                    <p>Somos marca peruana!</p>

                        <li>Jr. Cuzco - Abancay</li>

                        <li>zeus@gmail.com</li>

                        <li>+51 984 752 900</li>

                </div>

                <div class="footer-col-2">

                    <img src="../../view/images/logo.png">

                    <p>"Nuestro propósito es hacer que la comodidad y los beneficios de los productos cotidianos sean accesibles para todos de manera confiable y sostenible."

                    </p>

                </div>

                <div class="footer-col-3">

                    <h3>Links útiles</h3>

                    <ul>

                        <li>Inicio</li>

                        <li>Productos</li>

                        <li>Nosotros</li>

                        <li>Contacto</li>

                    </ul>

                </div>

                <div class="footer-col-4">

                    <h3>Siguenos</h3>

                    <ul>

                        <li>Facebook</li>

                        <li>Twitter</li>

                        <li>Instagram</li>

                        <li>Tik Tok</li>

                    </ul>

                </div>

            </div>

            <hr>

            <p class="copyright">Copyright 2024 - Cristian Bacilio</p>

        </div>

    </div>














     <?php if (isset($_GET['logout']) && $_GET['logout'] == 1): ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    Swal.fire({
      icon: 'success',
      title: '¡Sesión cerrada!',
      text: 'Has cerrado sesión correctamente.',
      timer: 2000,
      showConfirmButton: false
    });

    // Elimina el parámetro de la URL para que no se repita el mensaje
    if (window.history.replaceState) {
      window.history.replaceState(null, null, window.location.pathname);
    }
  </script>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  function confirmarLogout(e) {
    e.preventDefault();
    Swal.fire({
      title: '¿Deseas cerrar sesión?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ff523b',
      cancelButtonColor: '#aaa',
      confirmButtonText: 'Sí, salir',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = 'logout.php';
      }
    });
  }
</script>


    <script src="../../../js/script.js"></script>


    



    