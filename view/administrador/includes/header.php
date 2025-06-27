<aside id="sidebar" class="expand">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="lni lni-list"></i>
                </button>
                <div class="sidebar-logo">
                    <a href="adminDashboard.php">MARY CRIST</a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="adminInicio.php" class="sidebar-link">
                        <i class="lni lni-user"></i>
                        <span>Perfil</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="adminDashboard.php" class="sidebar-link">
                        <i class="lni lni-dashboard"></i>
                        <span>DashBoard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#auth" aria-expanded="false" aria-controls="auth">
                        <i class="lni lni-users"></i>
                        <span>Usuarios</span>
                    </a>
                    <ul id="auth" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                     <?php if($_SESSION['usuario_rol'] === '01'): ?>
                        <li class="sidebar-item">
                            <a href="adminUsuarios.php" class="sidebar-link">Empleados</a>
                        </li>
                    <?php endif; ?>
                        <li class="sidebar-item">
                            <a href="adminCliente.php" class="sidebar-link">Clientes</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-item">
                    <a href="adminProductos.php" class="sidebar-link">
                        <i class="lni lni-archive"></i>
                        <span>Productos</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="adminMensajes.php" class="sidebar-link">
                        <i class="lni lni-envelope"></i>
                        <span>Mensajes</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="" class="sidebar-link" data-bs-toggle="modal" data-bs-target="#logoutModal">

                    <i class="lni lni-exit"></i>
                    <span>Cerrar Sesion</span>
                </a>
            </div>
        </aside>


        <div class="main">
            <nav class="navbar navbar-expand px-4 py-3">
                <form action="#" class="d-none d-sm-inline-block">
                </form>
                <div class="navbar-collapse collapse">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small user"><?php echo $nombre?></span>
                            <a href="#" data-bs-toggle="dropdown" class="nav-icon pe-md-0">
                                <img src="../img/account.png" class="avatar img-fluid" alt="">
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>