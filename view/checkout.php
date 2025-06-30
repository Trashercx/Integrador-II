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
    <style>
        /* Estilos específicos para las opciones de pago mejoradas */
        .payment-option {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
            position: relative;
            overflow: hidden;
        }
        
        .payment-option:hover {
            border-color: #007bff;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .payment-option.selected {
            border-color: #007bff;
            background: linear-gradient(135deg, #f8f9ff 0%, #e8f2ff 100%);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.2);
        }
        
        .payment-option.selected::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #0056b3);
        }
        
        .payment-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .payment-icon {
            flex-shrink: 0;
            width: 60px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px;
        }
        
        .payment-icon img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .payment-icon .cash-icon {
            font-size: 24px;
            color: #28a745;
        }
        
        .payment-details {
            flex: 1;
        }
        
        .payment-title {
            font-weight: 600;
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 4px;
        }
        
        .payment-description {
            font-size: 14px;
            color: #6c757d;
            margin: 0;
        }
        
        .payment-option .form-check-input {
            width: 20px;
            height: 20px;
            margin-left: auto;
            flex-shrink: 0;
        }
        
        .payment-option .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .payment-section-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .payment-section-title::before {
            content: '💳';
            font-size: 24px;
        }
        
        .secure-payment-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e8f5e8;
            color: #155724;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            margin-top: 15px;
        }
        
        .secure-payment-badge::before {
            content: '🔒';
        }
    </style>
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

            <!-- Paso 3: Pago - SECCIÓN MEJORADA -->
            <div id="payment-section" class="profile-card checkout-section">
                <h4 class="payment-section-title">Método de pago</h4>
                
                <div class="payment-option selected" onclick="selectPayment('Tarjeta')">
                    <div class="payment-content">
                        <div class="payment-icon">
                            <img src="img/Stripe.png" alt="Tarjeta" />
                        </div>
                        <div class="payment-details">
                            <div class="payment-title">Tarjeta de crédito/débito</div>
                            <p class="payment-description">Visa, Mastercard, American Express</p>
                        </div>
                        <input class="form-check-input" type="radio" name="payment" id="credit-card" value="Tarjeta" checked>
                    </div>
                </div>
                
                <div class="payment-option" onclick="selectPayment('PayPal')">
                    <div class="payment-content">
                        <div class="payment-icon">
                            <img src="img/PayPal.png" alt="PayPal" />
                        </div>
                        <div class="payment-details">
                            <div class="payment-title">PayPal</div>
                            <p class="payment-description">Paga de forma segura con tu cuenta PayPal</p>
                        </div>
                        <input class="form-check-input" type="radio" name="payment" id="paypal" value="PayPal">
                    </div>
                </div>
                
                <div class="payment-option" onclick="selectPayment('Efectivo')">
                    <div class="payment-content">
                        <div class="payment-icon">
                            <div class="cash-icon">💵</div>
                        </div>
                        <div class="payment-details">
                            <div class="payment-title">Pago contra entrega</div>
                            <p class="payment-description">Paga en efectivo al recibir tu pedido</p>
                        </div>
                        <input class="form-check-input" type="radio" name="payment" id="cash" value="Efectivo">
                    </div>
                </div>
                
                <div class="secure-payment-badge">
                    Transacciones 100% seguras
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
                $subtotalProductos = 0;
                foreach ($carrito as $item) {
                    $itemTotal = $item['precio'] * $item['cantidad'];
                    $subtotalProductos += $itemTotal;
                    echo "<div class='order-summary-item'>
                            <span>" . htmlspecialchars($item['nombre']) . " × " . $item['cantidad'] . "</span>
                            <span>S/. " . number_format($itemTotal, 2) . "</span>
                        </div>";
                }
                ?>

                <div class="order-summary-item">
                    <span>Subtotal productos</span>
                    <span id="order-subtotal-productos">S/. <?= number_format($subtotalProductos, 2) ?></span>
                </div>
                
                <div class="order-summary-item">
                    <span>Envío</span>
                    <span id="order-shipping">S/. 11.20</span>
                </div>
                
                <?php 
                $envioInicial = 11.20;
                $subtotalTotal = $subtotalProductos + $envioInicial;
                $igvIncluido = $subtotalTotal * 18 / 118; // IGV incluido en el total
                ?>
                
                <div class="order-summary-item">
                    <span>Subtotal</span>
                    <span id="order-subtotal">S/. <?= number_format($subtotalTotal, 2) ?></span>
                </div>
                
                <div class="order-summary-item text-muted">
                    <span>IGV incluido (18%)</span>
                    <span id="order-igv">S/. <?= number_format($igvIncluido, 2) ?></span>
                </div>
                
                <div class="order-summary-item order-summary-total">
                    <span>Total</span>
                    <span id="order-total">S/. <?= number_format($subtotalTotal, 2) ?></span>
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

    // Obtener subtotal de productos
    const subtotalProductosElement = document.getElementById('order-subtotal-productos');
    const subtotalProductos = parseFloat(subtotalProductosElement.textContent.replace('S/. ', '').replace(',', '')) || 0;
    
    // Calcular nuevos totales
    const subtotalTotal = subtotalProductos + costoEnvio;
    const igvIncluido = subtotalTotal * 18 / 118; // IGV incluido

    // Actualizar elementos del resumen
    document.getElementById('order-shipping').textContent = `S/. ${costoEnvio.toFixed(2)}`;
    document.getElementById('order-subtotal').textContent = `S/. ${subtotalTotal.toFixed(2)}`;
    document.getElementById('order-igv').textContent = `S/. ${igvIncluido.toFixed(2)}`;
    document.getElementById('order-total').textContent = `S/. ${subtotalTotal.toFixed(2)}`;
}

// Agregar event listeners para los radio buttons también
document.querySelectorAll('input[name="shipping"]').forEach(radio => {
    radio.addEventListener('change', () => selectShipping(radio.value));
});

function selectPayment(option) {
    document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
    document.querySelector(`.payment-option[onclick="selectPayment('${option}')"]`).classList.add('selected');
    
    // Actualizar el radio button correspondiente
    const radioButtons = {
        'Tarjeta': 'credit-card',
        'PayPal': 'paypal', 
        'Efectivo': 'cash'
    };
    document.getElementById(radioButtons[option]).checked = true;
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
    if (paymentMethod.value === 'Tarjeta') paymentText = 'Tarjeta de crédito/débito';
    else if (paymentMethod.value === 'PayPal') paymentText = 'PayPal';
    else paymentText = 'Pago contra entrega';
    document.getElementById('review-payment').innerHTML = `<p>${paymentText}</p>`;
}

function confirmOrder() {
    if (!document.getElementById('terms').checked) {
        Swal.fire({
            icon: 'warning',
            title: 'Términos y condiciones',
            text: 'Por favor acepta los términos y condiciones'
        });
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
    
    console.log('Datos enviados:', data); // Debug
    
    if (metodo_pago === 'Tarjeta') {
        // Para tarjeta: guardar en sessionStorage y redirigir
        sessionStorage.setItem('datos_checkout', JSON.stringify(data));
        window.location.href = '/view/pago_tarjeta.php';
    } else if (metodo_pago === 'PayPal') {
        // Para PayPal: enviar datos al servidor ANTES de redirigir
        fetch('../controller/guardar_checkout_en_sesion_P.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                window.location.href = '/view/pago_paypal.php';
            } else {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error', 
                    text: response.message || 'Error al procesar los datos'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({ 
                icon: 'error', 
                title: 'Error', 
                text: 'Error de conexión. Intenta de nuevo.'
            });
        });
    } else if (metodo_pago === 'Efectivo') {
        // Mostrar loading
        Swal.fire({
            title: 'Procesando pedido...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Para contraentrega: procesar directamente
        fetch('../controller/procesar_compra.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(response => {
            console.log('Respuesta del servidor:', response); // Debug
            
            // Cerrar el loading
            Swal.close();
            
            if (response.success) {
                // Mostrar mensaje de éxito y luego redirigir
                Swal.fire({
                    icon: 'success',
                    title: '¡Pedido confirmado!',
                    text: 'Tu pedido ha sido procesado correctamente',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    // AQUÍ ESTÁ LA CORRECCIÓN: Manejar la redirección manualmente
                    window.location.href = '/view/confirmacion.php?id_compra=' + response.id_compra;
                });
            } else {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error al procesar el pedido', 
                    text: response.message || 'Ocurrió un error inesperado'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.close();
            Swal.fire({ 
                icon: 'error', 
                title: 'Error de conexión', 
                text: 'No se pudo procesar el pedido. Intenta de nuevo.'
            });
        });
    }
}

function applyPromoCode() {
    alert('Código promocional aplicado');
}
</script>

<?php require_once 'usuario/includes/footer.php'; ?>