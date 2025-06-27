

<body>
  


    <div class="overlay2">
        <div class="overlay2Door"></div>
        <div class="overlay2Content">
            <div class="loader">
            <div class="inner"></div>
            </div>
        </div>
    </div>







  <div class="container">
    <div class="navbar">
      <div class="logo">
        <a href="index.php"><img src="img/ZEUS2.png" alt="logo" width="125px" /></a>
      </div>

      <!--<div class="barra" style="background: #f1f1f1">
        <input type="text" name="" id="" placeholder="Buscar..." />
        <a href="#"><i class="fa-solid fa-magnifying-glass"></i></a>
      </div>

      -->
      <nav class="menu__link">
        <ul id="MenuItems"  >
          <li><a href="index.php" style="text-decoration: none; " >Inicio</a></li>
          <li><a href="productos.php" style="text-decoration: none; ">Productos</a></li>
          <li><a href="nosotros.php" style="text-decoration: none; ">Nosotros</a></li>
          <li><a href="contactanos.php" style="text-decoration: none; ">Contactanos</a></li>
          <?php if (usuarioAutenticado()): ?>
                            <a href="#"><?php echo obtenerNombreUsuario(); ?></a>
                        <?php else: ?>
                            <a href="login.php">Login</a>
                        <?php endif; ?>
                        </li>
        </ul>
      </nav>
      
      <a class="cart-icon" style="cursor: pointer;">
    <img src="images/cart.png" width="30px" height="30px" />
</a>
      <img src="images/menu.png" class="menu-icon" onclick="menutoggle()" />
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
               <!--  <div class="modal__list-price">
                    <ul>
                        <li>Subtotal: <strong id="subtotal">S/. 0.00</strong></li>
                        <li>Descuento: <strong id="descuento">S/. 0.00</strong></li>
                    </ul> 
                    <h4 class="modal__total-cart">Total: <strong id="total">S/. 0.00</strong></h4>
                </div> -->
                <div class="modal__btns">
                 <a href="#" class="btn-border" onclick="enviarPedidoWhatsApp()">Pedir ahora!</a>
                    <!--<a href="#" class="btn-primary">Comprar Ahora</a>-->
                </div>
            </div>
        </div>
    </div>
</div>

