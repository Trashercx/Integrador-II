<?php
session_start();
require_once 'usuario/includes/session_utils.php';
require_once '../bd/conexion.php';

if (!usuarioAutenticado()) {
    header("Location: usuario/login.php");
    exit;
}

$idUsuario = $_SESSION['usuario_id'];

$sql = "SELECT nombre, email, telefono, direccion, dni FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Perfil de usuario Zeus Importaciones - Edita tus datos personales y gestiona tu cuenta.">
  <meta name="keywords" content="perfil usuario, Zeus Importaciones, editar perfil, cambiar contraseña">
  <meta name="author" content="Zeus Importaciones">
  <title>Mi Perfil - Zeus Importaciones</title>
  <link rel="icon" href="img/ZEUS-removebg-preview.png">

    <link rel="stylesheet" href="../../../css/style.css" />
  <link rel="stylesheet" href="../../../css/estilos.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
  <script src="https://kit.fontawesome.com/05abdbeb44.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://cdn.lineicons.com/2.0/LineIcons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

 

  <style>
   
  </style>

  </head>

<?php require_once 'usuario/includes/header.php'; ?>

<body>
<div class="container dashboard-container" data-aos="fade-up">
  <div class="sidebar">
    <h4>Mi Cuenta</h4>
    <ul>
      <li><a class="active" onclick="mostrarSeccion('perfil')">General</a></li>
      <li><a onclick="mostrarSeccion('pedidos')">Pedidos</a></li>
      <li><a onclick="mostrarSeccion('pago')">Pago</a></li>
      <li><a onclick="mostrarSeccion('direccion')">Dirección de envío</a></li>
      <li><a onclick="mostrarSeccion('ajustes')">Ajustes</a></li>
      <li><a onclick="mostrarSeccion('cambiar_pass')">Cambiar contraseña</a></li>
      <li><a href="logout.php" class="text-danger">Cerrar sesión</a></li>
    </ul>
  </div>
  <div class="content-area">
    <div id="perfil" class="profile-card seccion">
      <h3>Información Personal</h3>
      <form action="usuario/update_perfil.php" method="POST">
        <input type="text" class="form-control" name="nombre" placeholder="Nombre completo" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
        <input type="email" class="form-control" name="email" placeholder="Correo electrónico" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        <input type="text" class="form-control" name="telefono" placeholder="Teléfono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>">
        <input type="text" class="form-control" name="direccion" placeholder="Dirección" value="<?php echo htmlspecialchars($usuario['direccion']); ?>">
        <input type="text" class="form-control" name="dni" placeholder="DNI" value="<?php echo htmlspecialchars($usuario['dni']); ?>">
        <button type="submit" class="btn btn-custom w-100">Actualizar Perfil</button>
      </form>
    </div>

    <div id="pedidos" class="profile-card seccion" style="display:none">
      <h3>Mis Pedidos</h3>
      <p>Funcionalidad en construcción...</p>
    </div>

    <div id="pago" class="profile-card seccion" style="display:none">
      <h3>Pago</h3>
      <p>Funcionalidad en construcción...</p>
    </div>

    <div id="direccion" class="profile-card seccion" style="display:none">
      <h3>Dirección</h3>
      <p>Funcionalidad en construcción...</p>
    </div>

    <div id="ajustes" class="profile-card seccion" style="display:none">
      <h3>Ajustes</h3>
      <p>Funcionalidad en construcción...</p>
    </div>

    <div id="cambiar_pass" class="profile-card seccion" style="display:none">
      <h3>Cambiar Contraseña</h3>
      <form action="usuario/cambiar_password.php" method="POST">
        <input type="password" class="form-control" name="actual" placeholder="Contraseña actual" required>
        <input type="password" class="form-control" name="nueva" placeholder="Nueva contraseña" required>
        <input type="password" class="form-control" name="confirmar" placeholder="Confirmar nueva contraseña" required>
        <button type="submit" class="btn btn-custom w-100">Cambiar Contraseña</button>
      </form>
    </div>
  </div>
</div>

<?php require_once 'usuario/includes/footer.php'; ?>

<script>
  function mostrarSeccion(id) {
    const secciones = document.querySelectorAll('.seccion');
    secciones.forEach(s => s.style.display = 'none');
    document.getElementById(id).style.display = 'block';

    const enlaces = document.querySelectorAll('.sidebar ul li a');
    enlaces.forEach(el => el.classList.remove('active'));
    event.target.classList.add('active');
  }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/app.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>
<script src="../js/menu.js"></script>

<?php if (isset($_GET['success'])): ?>
<script>
Swal.fire({ icon: 'success', title: '¡Éxito!', text: '<?php echo $_GET['success']; ?>', confirmButtonColor: '#ff523b' });
</script>
<?php elseif (isset($_GET['error'])): ?>
<script>
Swal.fire({ icon: 'error', title: 'Error', text: '<?php echo $_GET['error']; ?>', confirmButtonColor: '#ff523b' });
</script>
<?php endif; ?>

<?php if (isset($_SESSION['flash'])): ?>
  <script>
    Swal.fire({
      icon: '<?php echo $_SESSION['flash']['type']; ?>',
      title: '<?php echo ($_SESSION["flash"]["type"] === "success") ? "¡Éxito!" : "Error"; ?>',
      text: '<?php echo $_SESSION["flash"]["msg"]; ?>',
      confirmButtonColor: '#ff523b'
    });
  </script>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

</body>
</html>
