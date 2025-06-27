<?php
session_start();
require_once '../bd/conexion.php';
require_once '../fpdf/fpdf.php';
require_once '../config.php';



// Leer datos desde Stripe (sesión) o desde JSON
$data = json_decode(file_get_contents("php://input"), true);
$isStripe = false;

if (!$data && isset($_SESSION['stripe_checkout_data'])) {
    $data = $_SESSION['stripe_checkout_data']; // ya es array
    $isStripe = true;
    
} else {
    
}


if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$carrito = json_decode($_COOKIE['carrito'] ?? '[]', true);

if (empty($carrito)) {
    echo json_encode(['success' => false, 'message' => 'Carrito vacío']);
    exit;
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
    // Insertar compra
    $stmt = $conn->prepare("INSERT INTO compras (id_usuario, fecha_compra, estado) VALUES (?, NOW(), 'pendiente')");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $id_compra = $conn->insert_id;
   

    // Insertar detalle y actualizar stock
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

    // Insertar envío
    $direccion = $data['direccion'];
    $ciudad = $data['ciudad'];
    $distrito = $data['distrito'];
    $telefono = $data['telefono'] ?? 'No proporcionado';
    $codigo_postal = '00000';
    $estado_envio = 'pendiente';

    $stmt = $conn->prepare("INSERT INTO envio (id_compra, direccion, ciudad, codigo_postal, estado) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $id_compra, $direccion, $ciudad, $codigo_postal, $estado_envio);
    $stmt->execute();
    

    // Insertar pago
    $metodo = $data['metodo_pago'] ?? 'desconocido';
    $stmt = $conn->prepare("INSERT INTO pagos (id_compra, monto, metodo_pago, fecha_pago) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("ids", $id_compra, $total, $metodo);
    $stmt->execute();
    

    // Obtener DNI
    $stmt = $conn->prepare("SELECT dni FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $dni_usuario = ($row = $result->fetch_assoc()) ? $row['dni'] : '---------------';

    // Generar PDF 
    
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
    $pdf->Ln(3);

    // Tabla productos
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

    // Totales
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
    $pdf->Cell(0,10,utf8_decode('CANCELADO'),0,1,'C');

    $archivo_pdf = $pdf->Output('S');
    $stmt = $conn->prepare("INSERT INTO comprobante (id_compra, archivo_pdf, fecha_emision) VALUES (?, ?, NOW())");
    $stmt->bind_param("ib", $id_compra, $archivo_pdf);
    $stmt->send_long_data(1, $archivo_pdf);
    $stmt->execute();
    

    $conn->commit();
    setcookie('carrito', '', time() - 3600, "/");
    unset($_COOKIE['carrito']);
    unset($_SESSION['stripe_checkout_data']);

    $_SESSION['ultima_compra'] = [
        'id_compra' => $id_compra,
        'metodo_pago' => $metodo,
        'total' => $total,
        'fecha' => date('d/m/Y'),
        'nombre' => $_SESSION['usuario_nombre'] ?? 'Cliente',
        'direccion' => "$direccion, $distrito, $ciudad"
    ];

    

    // Redirigir si viene de Stripe
    if ($isStripe) {
        header("Location: ../view/confirmacion.php?id_compra=$id_compra");
        exit;
    } else {
        echo json_encode(['success' => true, 'id_compra' => $id_compra]);
        exit;
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
