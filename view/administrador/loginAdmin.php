<?php
session_start();
// Si el usuario ya está logueado, redirigir al panel de administración
if (isset($_SESSION['usuario_id'])) {
    header("Location: adminInicio.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="icon" href="../img/ZEUS-removebg-preview.png">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="../img/ZEUS-removebg-preview.png">
  <title>Login Administrador</title>
  <!-- Bootstrap Icon  -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
  <!-- Css file  -->
  <link rel="stylesheet" href="../../css/style-login-admin.css" />
</head>

<body>
  <div class="form-container">
    <div class="form">
   
      <form action="metodos/validar_login.php" method="POST">
        <h2>Iniciar Sesion</h2>
        <div class="input-group">
          <input type="email" name="usuario" placeholder="Usuario" required>
          <label class="bi-person-fill"></label>
        </div>
        <div class="input-group">
          <input type="password" name="password" placeholder="Contraseña" required>
          <label class="bi-lock-fill"></label>
        </div>
        <button type="submit">INICIAR</button>
      </form>
      <?php
            if (isset($_GET['error'])) {
                echo '<p style="color: red;">' . htmlspecialchars($_GET['error']) . '</p>';
            }
            ?>
    </div>
    

  </div>

</body>

</html>