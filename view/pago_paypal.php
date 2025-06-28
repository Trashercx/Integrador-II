<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar que tenemos datos del checkout
if (!isset($_SESSION['paypal_checkout_data'])) {
    header('Location: checkout.php?error=datos_no_disponibles');
    exit;
}

$checkout_data = $_SESSION['paypal_checkout_data'];

// Leer el carrito
$carrito = json_decode($_COOKIE['carrito'] ?? '[]', true);
if (empty($carrito)) {
    header('Location: checkout.php?error=carrito_vacio');
    exit;
}

// Calcular totales
$subtotal = array_reduce($carrito, fn($sum, $i) => $sum + ($i['precio'] * $i['cantidad']), 0);
$envio = match($checkout_data['metodo_envio']) {
    'express' => 18.50,
    'standard' => 11.20,
    'pickup' => 0.00,
    default => 0.00
};
$total = $subtotal + $envio;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesando Pago PayPal - MARYCRIST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .loading-container {
            min-height: 50vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .spinner-border-lg {
            width: 3rem;
            height: 3rem;
        }
        .order-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            Procesando Pago con PayPal
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Resumen del pedido -->
                        <div class="order-summary">
                            <h5>Resumen del Pedido</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Dirección:</strong><br>
                                    <?= htmlspecialchars($checkout_data['direccion']) ?><br>
                                    <?= htmlspecialchars($checkout_data['distrito']) ?>, <?= htmlspecialchars($checkout_data['ciudad']) ?><br>
                                    <strong>Teléfono:</strong> <?= htmlspecialchars($checkout_data['telefono']) ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Método de envío:</strong> <?= ucfirst($checkout_data['metodo_envio']) ?><br>
                                    <strong>Subtotal:</strong> S/. <?= number_format($subtotal, 2) ?><br>
                                    <strong>Envío:</strong> S/. <?= number_format($envio, 2) ?><br>
                                    <strong>Total:</strong> S/. <?= number_format($total, 2) ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Indicador de carga -->
                        <div class="loading-container">
                            <div class="spinner-border spinner-border-lg text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <h5 class="mt-3">Conectando con PayPal...</h5>
                            <p class="text-muted">Serás redirigido automáticamente para completar tu pago.</p>
                            
                            <div class="mt-4">
                                <button class="btn btn-primary" onclick="procesarPago()" id="btnProcesar">
                                    <i class="fab fa-paypal me-2"></i>
                                    Continuar con PayPal
                                </button>
                                <a href="checkout.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Volver al Checkout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script>
        // Función para procesar el pago
        function procesarPago() {
            const btn = document.getElementById('btnProcesar');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
            
            // Redirigir a crear_pago_paypal.php
            window.location.href = '../controller/crear_pago_paypal.php';
        }
        
        // Auto-procesar después de 3 segundos si el usuario no hace clic
        setTimeout(function() {
            if (!document.getElementById('btnProcesar').disabled) {
                procesarPago();
            }
        }, 3000);
        
        // Manejar errores de conexión
        window.addEventListener('error', function(e) {
            console.error('Error:', e);
            document.querySelector('.loading-container').innerHTML = `
                <div class="alert alert-danger">
                    <h5>Error de conexión</h5>
                    <p>No se pudo conectar con PayPal. Por favor, intenta nuevamente.</p>
                    <button class="btn btn-primary" onclick="location.reload()">Reintentar</button>
                    <a href="checkout.php" class="btn btn-secondary ms-2">Volver al Checkout</a>
                </div>
            `;
        });
    </script>
</body>
</html>