<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: ../view/checkout.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style-login.css">
    <title>MaryCris Import | Ecommerce</title>
</head>

<body>

    <div class="container" id="container">
        <div class="form-container sign-up">
            <form action="registro_usuario.php" method="POST">
                <h1>Crear Cuenta</h1>
             <!--     <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                </div>
                <span>o usa tu correo para registrarte</span> -->
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password"  placeholder="Contraseña" required>
                <button type="submit">Crear</button>
            </form>
        </div>
        <div class="form-container sign-in">
            <form action="login_verificar.php" method="POST">
                <h1>Iniciar Sesion</h1>
              <!--   <div class="social-icons">
                   <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                </div> 
                <span>o usa tu correo</span>-->
                <input type="email" name="usuario" placeholder="Email" required">
                <input type="password" name="password" placeholder="Contraseña" required">
               <!--  <a href="#">Olvidastes tu contraseña?</a> -->
                <button type="submit">Entrar</button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Bienvenido devuelta!</h1>
                    <p>Ingrese sus datos personales</p>
                    <button class="hidden" id="login">Iniciar Sesion</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Hola, Amigo!</h1>
                    <p>Regístrese con sus datos personales </p>
                    <button class="hidden" id="register">Crear Cuenta</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/scriptLogin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   


</body>

</html>