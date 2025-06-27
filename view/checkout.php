<?php
session_start();
require_once '../controller/UsuarioController.php';
require_once 'usuario/includes/session_utils.php';

$usuarioNoLogueado = !usuarioAutenticado();
$carrito = json_decode($_COOKIE['carrito'] ?? '[]', true);
$carritoVacio = empty($carrito);

// Si no está logueado, guardar redirección
if ($usuarioNoLogueado) {
    $_SESSION['redirect_to'] = 'checkout.php';
}

// Redirigir si el carrito está vacío
if ($carritoVacio) {
    header('Location: productos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checkout - MaryCris Import</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background-color: white;">
<?php
if ($usuarioNoLogueado) {
    echo "<script>
        Swal.fire({
            icon: 'info',
            title: 'Debes iniciar sesi\u00f3n',
            text: 'Para continuar con tu compra, inicia sesi\u00f3n.',
            confirmButtonText: 'Iniciar sesi\u00f3n'
        }).then(() => {
            window.location.href = 'login.php?showLoginAlert=1';
        });
    </script>";
    exit;
}

require_once 'usuario/includes/header.php';

$usuario = null;
if (isset($_SESSION['usuario_id'])) {
    $_SESSION['id_usuario'] = $_SESSION['usuario_id'];
    $usuarioController = new UsuarioController();
    $usuario = $usuarioController->obtenerUsuarioPorId($_SESSION['usuario_id']);
}
?>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/estilos.css">
<div class="container dashboard-container mt-4">
    <div class="row">
        <div class="col-lg-8">
            <!-- Barra de progreso -->
            <div class="checkout-progress mb-4">
                <div class="checkout-progress-step active" id="progress-address">
                    <div class="step-number">1</div>
                    <div class="step-label">Dirección</div>
                </div>
                <div class="checkout-progress-step" id="progress-shipping">
                    <div class="step-number">2</div>
                    <div class="step-label">Envío</div>
                </div>
                <div class="checkout-progress-step" id="progress-payment">
                    <div class="step-number">3</div>
                    <div class="step-label">Pago</div>
                </div>
                <div class="checkout-progress-step" id="progress-review">
                    <div class="step-number">4</div>
                    <div class="step-label">Confirmar</div>
                </div>
            </div>

            <!-- Paso 1: Dirección de envío -->
            <div id="address-section" class="profile-card checkout-section active">
                <h4 class="mb-4">Información de envío</h4>
                <form id="address-form">
                    <div class="form-group">
                        <label for="nombre">Nombre completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="ciudad">Ciudad</label>
                            <input type="text" class="form-control" id="ciudad" name="ciudad" value="Lima" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="distrito">Distrito</label>
                            <input type="text" class="form-control" id="distrito" name="distrito" value="San Juan de Lurigancho" required>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-checkout" onclick="nextStep('shipping-section')">Continuar al envío</button>
                </form>
            </div>

            <!-- Paso 2: Envío -->
            <div id="shipping-section" class="profile-card checkout-section">
                <h4 class="mb-4">Método de envío</h4>
                
                <div class="shipping-option selected" onclick="selectShipping('standard')">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="shipping" id="standard" value="standard" checked>
                        <label class="form-check-label" for="standard">
                            <strong>Envío Estándar</strong><br>
                            <small>Entrega en 3-5 días hábiles</small><br>
                            <span class="text-primary">S/. 11.20</span>
                        </label>
                    </div>
                </div>
                
                <div class="shipping-option" onclick="selectShipping('express')">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="shipping" id="express" value="express">
                        <label class="form-check-label" for="express">
                            <strong>Envío Express</strong><br>
                            <small>Entrega en 1-2 días hábiles</small><br>
                            <span class="text-primary">S/. 18.50</span>
                        </label>
                    </div>
                </div>
                
                <div class="shipping-option" onclick="selectShipping('pickup')">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="shipping" id="pickup" value="pickup">
                        <label class="form-check-label" for="pickup">
                            <strong>Recojo en tienda</strong><br>
                            <small>Av. Principal 123, Lima</small><br>
                            <span class="text-success">Gratis</span>
                        </label>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <button class="btn btn-outline-secondary" onclick="prevStep('address-section')">Regresar</button>
                    <button class="btn btn-primary" onclick="nextStep('payment-section')">Continuar al pago</button>
                </div>
            </div>

            <!-- Paso 3: Pago -->
            <div id="payment-section" class="profile-card checkout-section">
                <h4 class="mb-4">Método de pago</h4>
                
                <div class="payment-option selected" onclick="selectPayment('credit-card')">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment" id="credit-card" value="credit-card" checked>
                        <label class="form-check-label" for="credit-card">
                            <strong>Tarjeta de crédito/débito</strong>
                        </label>
                    </div>
                </div>
                
                <div class="payment-option" onclick="selectPayment('paypal')">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment" id="paypal" value="paypal">
                        <label class="form-check-label" for="paypal">
                            <strong>PayPal</strong>
                        </label>
                    </div>
                </div>
                
                <div class="payment-option" onclick="selectPayment('cash')">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment" id="cash" value="cash">
                        <label class="form-check-label" for="cash">
                            <strong>Pago contra entrega</strong>
                        </label>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <button class="btn btn-outline-secondary" onclick="prevStep('shipping-section')">Regresar</button>
                    <button class="btn btn-primary" onclick="nextStep('review-section')">Revisar pedido</button>
                </div>
            </div>

            <!-- Paso 4: Revisión -->
            <div id="review-section" class="profile-card checkout-section">
                <h4 class="mb-4">Revisar tu pedido</h4>
                
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Dirección de envío</h5>
                        <div id="review-address"></div>
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Método de envío</h5>
                        <div id="review-shipping"></div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Método de pago</h5>
                        <div id="review-payment"></div>
                    </div>
                </div>
                
                <div class="form-check mb-4">
                    <input type="checkbox" class="form-check-input" id="terms" required>
                    <label for="terms" class="form-check-label">He leído y acepto los <a href="#" class="text-primary">términos y condiciones</a></label>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button class="btn btn-outline-secondary" onclick="prevStep('payment-section')">Regresar</button>
                    <button class="btn btn-success" onclick="confirmOrder()">Confirmar pedido</button>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="profile-card sticky-top" style="top: 20px;">
                <h4 class="mb-3">Resumen del pedido</h4>

                <?php
                $subtotal = 0;
                foreach ($carrito as $item) {
                    $itemTotal = $item['precio'] * $item['cantidad'];
                    $subtotal += $itemTotal;
                    echo "<div class='order-summary-item'>
                            <span>" . htmlspecialchars($item['nombre']) . " × " . $item['cantidad'] . "</span>
                            <span>S/. " . number_format($itemTotal, 2) . "</span>
                        </div>";
                }
                $igv = $subtotal * 0.18;
                $subtotalSinIGV = $subtotal - $igv;
                ?>

                <div class="order-summary-item">
                    <span>Subtotal sin IGV</span>
                    <span id="order-subtotal-sin-igv">S/. <?= number_format($subtotalSinIGV, 2) ?></span>
                </div>
                <div class="order-summary-item">
                    <span>IGV (18%)</span>
                    <span id="order-igv">S/. <?= number_format($igv, 2) ?></span>
                </div>
                <!-- ELEMENTO SUBTOTAL AGREGADO PARA COMPATIBILIDAD -->
                <div class="order-summary-item">
                    <span>Subtotal</span>
                    <span id="order-subtotal">S/. <?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="order-summary-item">
                    <span>Envío</span>
                    <span id="order-shipping">S/. 11.20</span>
                </div>
                <div class="order-summary-item order-summary-total">
                    <span>Total</span>
                    <span id="order-total">S/. <?= number_format($subtotal + 11.20, 2) ?></span>
                </div>

                <div class="input-group mb-3 mt-3">
                    <input type="text" class="form-control" id="promo-code" placeholder="Código promocional">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="applyPromoCode()">Aplicar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// FUNCIÓN CORREGIDA PARA SELECCIONAR ENVÍO Y ACTUALIZAR RESUMEN
function selectShipping(option) {
    // Actualizar selección visual
    document.querySelectorAll('.shipping-option').forEach(el => el.classList.remove('selected'));
    document.querySelector(`.shipping-option[onclick="selectShipping('${option}')"]`).classList.add('selected');
    document.getElementById(option).checked = true;

    // Calcular nuevo costo de envío
    let costoEnvio = 0;
    if (option === 'standard') costoEnvio = 11.20;
    else if (option === 'express') costoEnvio = 18.50;
    else costoEnvio = 0; // pickup es gratis

    // Obtener subtotal actual (con IGV incluido)
    const subtotalElement = document.getElementById('order-subtotal');
    const subtotal = parseFloat(subtotalElement.textContent.replace('S/. ', '').replace(',', '')) || 0;

    // Actualizar elementos del resumen
    document.getElementById('order-shipping').textContent = `S/. ${costoEnvio.toFixed(2)}`;
    document.getElementById('order-total').textContent = `S/. ${(subtotal + costoEnvio).toFixed(2)}`;
}

// Agregar event listeners para los radio buttons también
document.querySelectorAll('input[name="shipping"]').forEach(radio => {
    radio.addEventListener('change', () => selectShipping(radio.value));
});

function selectPayment(option) {
    document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
    document.querySelector(`.payment-option[onclick="selectPayment('${option}')"]`).classList.add('selected');
    document.getElementById(option).checked = true;
}

function nextStep(nextSectionId) {
    const currentSection = document.querySelector('.checkout-section.active');
    const nextSection = document.getElementById(nextSectionId);
    if (validateStep(currentSection.id)) {
        currentSection.classList.remove('active');
        nextSection.classList.add('active');
        updateProgressBar(nextSectionId);
        if (nextSectionId === 'review-section') updateReviewInfo();
    }
}

function prevStep(prevSectionId) {
    document.querySelector('.checkout-section.active').classList.remove('active');
    document.getElementById(prevSectionId).classList.add('active');
    updateProgressBar(prevSectionId);
}

function updateProgressBar(currentSectionId) {
    const steps = ['address', 'shipping', 'payment', 'review'];
    const currentIndex = steps.indexOf(currentSectionId.split('-')[0]);
    document.querySelectorAll('.checkout-progress-step').forEach((step, index) => {
        step.classList.remove('active', 'completed');
        if (index < currentIndex) step.classList.add('completed');
        else if (index === currentIndex) step.classList.add('active');
    });
}

function validateStep(stepId) {
    return true;
}

function updateReviewInfo() {
    const nombre = document.getElementById('nombre').value;
    const direccion = document.getElementById('direccion').value;
    const ciudad = document.getElementById('ciudad').value;
    const distrito = document.getElementById('distrito').value;
    document.getElementById('review-address').innerHTML = `<p>${nombre}<br>${direccion}<br>${distrito}, ${ciudad}</p>`;
    
    const shippingMethod = document.querySelector('input[name="shipping"]:checked');
    let shippingText = '';
    if (shippingMethod.id === 'standard') shippingText = 'Envío Estándar - S/. 11.20';
    else if (shippingMethod.id === 'express') shippingText = 'Envío Express - S/. 18.50';
    else shippingText = 'Recojo en tienda - Gratis';
    document.getElementById('review-shipping').innerHTML = `<p>${shippingText}</p>`;
    
    const paymentMethod = document.querySelector('input[name="payment"]:checked');
    let paymentText = '';
    if (paymentMethod.id === 'credit-card') paymentText = 'Tarjeta de crédito/débito';
    else if (paymentMethod.id === 'paypal') paymentText = 'PayPal';
    else paymentText = 'Pago contra entrega';
    document.getElementById('review-payment').innerHTML = `<p>${paymentText}</p>`;
}

function confirmOrder() {
    if (!document.getElementById('terms').checked) {
        alert('Por favor acepta los términos y condiciones');
        return;
    }
    
    const metodo_pago = document.querySelector('input[name="payment"]:checked').value;
    const data = {
        nombre: document.getElementById('nombre').value,
        telefono: document.getElementById('telefono').value,
        direccion: document.getElementById('direccion').value,
        ciudad: document.getElementById('ciudad').value,
        distrito: document.getElementById('distrito').value,
        metodo_envio: document.querySelector('input[name="shipping"]:checked').value,
        metodo_pago: metodo_pago
    };
    
    sessionStorage.setItem('datos_checkout', JSON.stringify(data));
    
    if (metodo_pago === 'credit-card') {
        window.location.href = '/view/pago_tarjeta.php';
    } else if (metodo_pago === 'paypal') {
        window.location.href = '/view/pago_paypal.php';
    } else if (metodo_pago === 'cash') {
        fetch('../controller/procesar_compra.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                window.location.href = '/view/confirmacion.php?id_compra=' + response.id_compra;
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: response.message });
            }
        });
    }
}

function applyPromoCode() {
    alert('Código promocional aplicado');
}
</script>

<?php require_once 'usuario/includes/footer.php'; ?>