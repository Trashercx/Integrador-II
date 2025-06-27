<?php
session_start();
require_once '../config.php';

// 🔍 Log inicial al cargar el archivo
file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] ✅ pago_tarjeta.php cargado\n", FILE_APPEND);

// Verificación de sesión
if (!isset($_SESSION['usuario_id'])) {
    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] ⚠️ Usuario no logueado\n", FILE_APPEND);
    header("Location: ../index.php");
    exit();
}

// Si llega un POST (desde fetch JS)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputJSON = file_get_contents('php://input');
    file_put_contents(__DIR__ . '/../log_stripe.txt', "[" . date('Y-m-d H:i:s') . "] 📥 POST recibido con datos: $inputJSON\n", FILE_APPEND);
    $_SESSION['stripe_checkout_data'] = json_decode($inputJSON, true);
    echo json_encode(['status' => 'ok']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Procesando pago...</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body {
            margin: 0;
            background: #f9f9f9;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .fallback-message {
            display: none;
            text-align: center;
            color: #555;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="fallback-message" id="fallback">
        <p>Estamos redirigiéndote a la pasarela de pago...</p>
        <p>Si no sucede nada en unos segundos, <a href="checkout.php">haz clic aquí</a>.</p>
    </div>

<script>
    // Configuración desde PHP de forma segura
    const BASE_URL = "<?= BASE_URL ?>";
    const stripe = Stripe("<?php echo $stripe_publishable_key; ?>");

    (async () => {
        const datosCheckout = sessionStorage.getItem('datos_checkout');

        if (!datosCheckout) {
            await Swal.fire({
                icon: 'error',
                title: 'Datos incompletos',
                text: 'No se encontró información del pedido. Por favor, regresa al checkout.',
                confirmButtonText: 'Volver al Checkout',
            });
            window.location.href = 'checkout.php';
            return;
        }

        Swal.fire({
            title: 'Redirigiéndote a Stripe...',
            html: `
                <div style="font-size:15px; margin-top:10px; color: #444;">
                    Estamos conectando con la pasarela de pago segura.
                </div>
            `,
            timer: 6000,
            timerProgressBar: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            await fetch(`${BASE_URL}/controller/guardar_checkout_en_sesion.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: datosCheckout
            });

            const res = await fetch(`${BASE_URL}/controller/crear_sesion_stripe.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: datosCheckout
            });

            const respuesta = await res.json();

            if (respuesta.id) {
                Swal.close();
                stripe.redirectToCheckout({ sessionId: respuesta.id });
            } else {
                throw new Error('No se pudo crear la sesión de Stripe');
            }

        } catch (error) {
            console.error(error);
            Swal.close();
            document.getElementById('fallback').style.display = 'block';
        }
    })();
</script>
</body>
</html>