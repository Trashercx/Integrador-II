<?php
session_start();
require_once '../bd/conexion.php';
require_once '../fpdf/fpdf.php';
require_once '../config.php';

// Leer datos desde Stripe, PayPal, o JSON directo
$data = json_decode(file_get_contents("php://input"), true);
$isPasarela = false;
$metodo_pago_origen = 'Efectivo'; // por defecto

// Verificar si vienen datos de Stripe
if (!$data && isset($_SESSION['stripe_checkout_data'])) {
    $data = $_SESSION['stripe_checkout_data'];
    $isPasarela = true;
    $metodo_pago_origen = 'Tarjeta';
}

// Verificar si vienen datos de PayPal
if (!$data && isset($_SESSION['paypal_checkout_data'])) {
    $data = $_SESSION['paypal_checkout_data'];
    $isPasarela = true;
    $metodo_pago_origen = 'PayPal';
}

if (!isset($_SESSION['usuario_id'])) {
    if ($isPasarela) {
        header('Location: ../view/login.php');
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
        exit;
    }
}

$id_usuario = $_SESSION['usuario_id'];
$carrito = json_decode($_COOKIE['carrito'] ?? '[]', true);
if (empty($carrito)) {
    if ($isPasarela) {
        header('Location: ../view/checkout.php?error=carrito_vacio');
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Carrito vacío']);
        exit;
    }
}

$subtotal = array_reduce($carrito, fn($sum, $i) => $sum + ($i['precio'] * $i['cantidad']), 0);
$envio = match($data['metodo_envio']) {
    'express' => 18.50,
    'standard' => 11.20,
    'pickup' => 0.00,
    default => 0.00
};
$total = $subtotal + $envio;

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO compras (id_usuario, fecha_compra, estado) VALUES (?, NOW(), 'pendiente')");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $id_compra = $conn->insert_id;

    $stmt = $conn->prepare("INSERT INTO detalle_compra (id_compra, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
    $stmt_stock = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ? AND stock >= ?");

    foreach ($carrito as $item) {
        $id_producto = $item['id_producto'] ?? $item['id'] ?? null;
        if (!$id_producto) throw new Exception("Falta id_producto");

        $stmt->bind_param("iiid", $id_compra, $id_producto, $item['cantidad'], $item['precio']);
        $stmt->execute();

        $cantidad = $item['cantidad'];
        $stmt_stock->bind_param("iii", $cantidad, $id_producto, $cantidad);
        $stmt_stock->execute();

        if ($stmt_stock->affected_rows === 0) {
            throw new Exception("❌ Stock insuficiente para ID: $id_producto");
        }
    }

    $direccion = $data['direccion'];
    $ciudad = $data['ciudad'];
    $distrito = $data['distrito'] ?? $data['ciudad']; // Fallback si no hay distrito
    $telefono = $data['telefono'] ?? 'No proporcionado';
    $codigo_postal = '00000';
    $estado_envio = 'pendiente';

    $stmt = $conn->prepare("INSERT INTO envio (id_compra, direccion, ciudad, codigo_postal, estado) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $id_compra, $direccion, $ciudad, $codigo_postal, $estado_envio);
    $stmt->execute();

    // CORRECCIÓN: Determinar método de pago correctamente
    $metodo_pago_raw = $data['metodo_pago'] ?? $metodo_pago_origen;
    
    // Normalizar el método de pago para que coincida con el ENUM de la BD
    $metodo_pago_final = match(strtolower($metodo_pago_raw)) {
        'tarjeta', 'credit-card', 'stripe' => 'Tarjeta',
        'paypal' => 'PayPal',
        'cash', 'efectivo', 'contraentrega' => 'Efectivo',
        default => 'Efectivo'
    };

    // Debug: Log para verificar qué método se está usando
    error_log("Método de pago recibido: " . $metodo_pago_raw);
    error_log("Método de pago final: " . $metodo_pago_final);

    $paypal_transaction_id = null;
    $paypal_order_id = null;
    $stripe_session_id = null;

    // Si es PayPal, obtener IDs de transacción
    if ($metodo_pago_final === 'PayPal' && isset($data['paypal_transaction_id'])) {
        $paypal_transaction_id = $data['paypal_transaction_id'];
        $paypal_order_id = $data['paypal_order_id'] ?? null;
    }

    // Si es Stripe, obtener session ID
    if ($metodo_pago_final === 'Tarjeta' && isset($data['stripe_session_id'])) {
        $stripe_session_id = $data['stripe_session_id'];
    }

    // Insertar pago con información adicional de pasarelas
    if ($paypal_transaction_id || $stripe_session_id) {
        // Verificar si la tabla pagos tiene las columnas para PayPal/Stripe
        $stmt = $conn->prepare("SHOW COLUMNS FROM pagos LIKE 'paypal_transaction_id'");
        $stmt->execute();
        $result = $stmt->get_result();
        $has_paypal_columns = $result->num_rows > 0;

        if ($has_paypal_columns) {
            $stmt = $conn->prepare("INSERT INTO pagos (id_compra, monto, metodo_pago, fecha_pago, paypal_transaction_id, paypal_order_id, stripe_session_id) VALUES (?, ?, ?, NOW(), ?, ?, ?)");
            $stmt->bind_param("idssss", $id_compra, $total, $metodo_pago_final, $paypal_transaction_id, $paypal_order_id, $stripe_session_id);
        } else {
            // Tabla no tiene columnas de pasarelas, usar inserción básica
            $stmt = $conn->prepare("INSERT INTO pagos (id_compra, monto, metodo_pago, fecha_pago) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("ids", $id_compra, $total, $metodo_pago_final);
        }
    } else {
        // Pago contraentrega o cualquier otro método
        $stmt = $conn->prepare("INSERT INTO pagos (id_compra, monto, metodo_pago, fecha_pago) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("ids", $id_compra, $total, $metodo_pago_final);
    }
    
    $stmt->execute();
    
    // Verificar si el pago se insertó correctamente
    if ($stmt->affected_rows === 0) {
        throw new Exception("Error al insertar el método de pago: " . $metodo_pago_final);
    }

    $stmt = $conn->prepare("SELECT dni FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $dni_usuario = ($row = $result->fetch_assoc()) ? $row['dni'] : '---------------';

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(120, 10, utf8_decode('MARYCRIST'), 0, 0);
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(70, 6, utf8_decode('R.U.C. N° 20600875711'), 1, 1, 'C');
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(120, 6, utf8_decode('MARYCRIST IMPORT SAC'), 0, 0);
    $pdf->Cell(70, 6, utf8_decode('COMPROBANTE DE PAGO'), 1, 1, 'C');
    $pdf->Cell(120, 6, utf8_decode('AV. CASUARINAS 110 - SAN BORJA - LIMA - LIMA'), 0, 0);
    $pdf->Cell(70, 6, '0001-' . rand(1000,9999), 1, 1, 'C');
    $pdf->Cell(120, 6, 'logistica@marycrist.pe', 0, 1);

    $pdf->Ln(5);
    $pdf->Cell(0, 6, utf8_decode('Cliente: ') . utf8_decode($_SESSION['usuario_nombre']), 0, 1);
    $pdf->Cell(0, 6, utf8_decode("DNI: $dni_usuario     Fecha: ") . date('d/m/Y'), 0, 1);
    $pdf->Cell(0, 6, utf8_decode("Dirección: $direccion, $distrito, $ciudad"), 0, 1);
    $pdf->Cell(0, 6, utf8_decode("Teléfono: $telefono"), 0, 1);
    
    // Agregar información del método de pago en el PDF
    if ($metodo_pago_final === 'PayPal' && $paypal_transaction_id) {
        $pdf->Cell(0, 6, utf8_decode("PayPal ID: $paypal_transaction_id"), 0, 1);
    } elseif ($metodo_pago_final === 'Tarjeta' && $stripe_session_id) {
        $pdf->Cell(0, 6, utf8_decode("Stripe Session: " . substr($stripe_session_id, 0, 20) . "..."), 0, 1);
    }
    
    $pdf->Ln(3);

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(25,8,utf8_decode('Código'),1);
    $pdf->Cell(20,8,'Cant.',1);
    $pdf->Cell(75,8,utf8_decode('Descripción'),1);
    $pdf->Cell(35,8,utf8_decode('Precio Unitario'),1);
    $pdf->Cell(35,8,utf8_decode('Precio Venta'),1);
    $pdf->Ln();

    $stmt = $conn->prepare("SELECT dc.cantidad, dc.precio_unitario, p.nombre FROM detalle_compra dc INNER JOIN productos p ON dc.id_producto = p.id_producto WHERE dc.id_compra = ?");
    $stmt->bind_param("i", $id_compra);
    $stmt->execute();
    $res = $stmt->get_result();

    $pdf->SetFont('Arial','',10);
    while ($row = $res->fetch_assoc()) {
        $total_item = $row['cantidad'] * $row['precio_unitario'];
        $pdf->Cell(25,8,'SKU',1);
        $pdf->Cell(20,8,$row['cantidad'],1,0,'C');
        $pdf->Cell(75,8,utf8_decode($row['nombre']),1);
        $pdf->Cell(35,8,'S/. ' . number_format($row['precio_unitario'],2),1,0,'R');
        $pdf->Cell(35,8,'S/. ' . number_format($total_item,2),1,0,'R');
        $pdf->Ln();
    }

    $igv = $subtotal * 0.18;
    $total_final = $subtotal + $igv + $envio;

    $pdf->Cell(145,8,'',0,0);
    $pdf->Cell(35,8,'Subtotal',0,0);
    $pdf->Cell(0,8,'S/. ' . number_format($subtotal,2),0,1,'R');

    $pdf->Cell(145,8,'',0,0);
    $pdf->Cell(35,8,'IGV (18%)',0,0);
    $pdf->Cell(0,8,'S/. ' . number_format($igv,2),0,1,'R');

    $pdf->Cell(145,8,'',0,0);
    $pdf->Cell(35,8,utf8_decode('Envío'),0,0);
    $pdf->Cell(0,8,'S/. ' . number_format($envio,2),0,1,'R');

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(145,8,'',0,0);
    $pdf->Cell(35,8,'TOTAL',0,0);
    $pdf->Cell(0,8,'S/. ' . number_format($total_final,2),0,1,'R');
    $pdf->Ln(5);
    
    // Mostrar estado del pago según el método
    $estado_pago = match($metodo_pago_final) {
        'PayPal', 'Tarjeta' => 'PAGADO',
        'Efectivo' => 'PENDIENTE DE PAGO',
        default => 'CANCELADO'
    };
    $pdf->Cell(0,10,utf8_decode($estado_pago),0,1,'C');

    $archivo_pdf = $pdf->Output('S');
    $stmt = $conn->prepare("INSERT INTO comprobante (id_compra, archivo_pdf, fecha_emision) VALUES (?, ?, NOW())");
    $stmt->bind_param("ib", $id_compra, $archivo_pdf);
    $stmt->send_long_data(1, $archivo_pdf);
    $stmt->execute();

    $conn->commit();
    
    // Limpiar carrito y datos de sesión
    setcookie('carrito', '', time() - 3600, "/");
    unset($_COOKIE['carrito']);
    
    // Limpiar datos específicos de cada pasarela
    if (isset($_SESSION['stripe_checkout_data'])) {
        unset($_SESSION['stripe_checkout_data']);
    }
    if (isset($_SESSION['paypal_checkout_data'])) {
        unset($_SESSION['paypal_checkout_data']);
    }
    if (isset($_SESSION['paypal_order_id'])) {
        unset($_SESSION['paypal_order_id']);
    }

    $_SESSION['ultima_compra'] = [
        'id_compra' => $id_compra,
        'metodo_pago' => $metodo_pago_final,
        'total' => $total,
        'fecha' => date('d/m/Y'),
        'nombre' => $_SESSION['usuario_nombre'] ?? 'Cliente',
        'direccion' => "$direccion, $distrito, $ciudad",
        'paypal_transaction_id' => $paypal_transaction_id
    ];

    if ($isPasarela) {
        header("Location: ../view/confirmacion.php?id_compra=$id_compra");
        exit;
    } else {
        echo json_encode(['success' => true, 'id_compra' => $id_compra]);
        exit;
    }

} catch (Exception $e) {
    $conn->rollback();
    
    // Log del error para debugging
    error_log("Error en procesar_compra.php: " . $e->getMessage());
    
    if ($isPasarela) {
        header("Location: ../view/checkout.php?error=" . urlencode($e->getMessage()));
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>