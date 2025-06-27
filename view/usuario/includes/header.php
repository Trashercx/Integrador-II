

<body>
  


    <div class="overlay2">
        <div class="overlay2Door"></div>
        <div class="overlay2Content">
            <div class="loader">
            <div class="inner"></div>
            </div>
        </div>
    </div>






<div class="">
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
                             <li>  <a href="#" onclick="confirmarLogout(event)">Cerrar sesión</a></li>
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
                        <li><a href="perfil.php">Mi perfil</a></li>
                        <li><a href="compras.php">Mis compras</a></li>
                        <li><a href="#" class="logout-link" onclick="confirmarLogout(event)">Cerrar sesión</a></li>
                    </ul>
                     <?php else: ?>
                            <a href="login.php">Login</a>
                        <?php endif; ?>
                </li>
            </ul>
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
                <!--<a href="#" class="btn-border" onclick="enviarPedidoWhatsApp()">Pedir ahora!</a>-->
                    <!--<a href="#" class="btn-primary">Comprar Ahora</a>-->
                <!--</div> -->
                
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









