<?php
session_start();

if (!isset($_SESSION['ultima_compra'])) {
    header('Location: ../index.php');
    exit;
}

$compra = $_SESSION['ultima_compra'];
unset($_SESSION['ultima_compra']); // evita que se recargue la misma compra

$id_compra = $compra['id_compra'];
$nombre = htmlspecialchars($compra['nombre'] ?? 'Cliente');
$direccion = htmlspecialchars($compra['direccion'] ?? '---');
$fecha = htmlspecialchars($compra['fecha'] ?? date('d/m/Y'));
$metodo = ucfirst($compra['metodo_pago']);
$codigo_venta = '0001-' . str_pad($id_compra, 4, '0', STR_PAD_LEFT);
$raw_total = floatval($compra['total']);

$metodo_envio = $compra['metodo_envio'] ?? 'standard';
switch ($metodo_envio) {
    case 'express': $envio = 18.50; break;
    case 'standard': $envio = 11.20; break;
    case 'pickup': $envio = 0.00; break;
    default: $envio = 0.00;
}

$subtotal_con_envio = $raw_total - $envio;
$igv = $subtotal_con_envio * 0.18 / 1.18;
$subtotal_sin_igv = $subtotal_con_envio - $igv;

// Consultar productos de esta compra
require_once '../bd/conexion.php';
$sql = "SELECT p.nombre, dc.cantidad, dc.precio_unitario
        FROM detalle_compra dc
        INNER JOIN productos p ON p.id_producto = dc.id_producto
        WHERE dc.id_compra = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_compra);
$stmt->execute();
$res = $stmt->get_result();
$productos = $res->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Compra</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f0f0;
            margin: 0; padding: 0;
        }
        .voucher-container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .voucher-header {
            text-align: center;
            border-bottom: 2px dashed #ccc;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .voucher-header img {
            width: 100px;
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-top: 10px;
        }
        .voucher-body {
            font-size: 16px;
            color: #333;
        }
        .voucher-body p {
            margin: 4px 0;
        }
        .voucher-body .info {
            margin-bottom: 20px;
        }
        table.voucher-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.voucher-items th, table.voucher-items td {
            border: 1px solid #ccc;
            padding: 10px;
        }
        table.voucher-items th {
            background-color: #f5f5f5;
        }
        .voucher-total {
            text-align: right;
            font-size: 16px;
            font-weight: normal;
            margin-top: 20px;
            color: #333;
        }
        .voucher-total p {
            margin: 6px 0;
        }
        .voucher-total strong {
            font-weight: 600;
        }
        .voucher-footer {
            text-align: center;
            margin-top: 30px;
        }
        .voucher-footer a {
            display: inline-block;
            margin: 10px 8px;
            padding: 12px 20px;
            background: #0078D7;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 15px;
        }
        .voucher-footer a:hover {
            background: #005bb5;
        }
    </style>
    <script>
        // Limpiar carrito del localStorage
        window.onload = () => {
            localStorage.removeItem('carrito');
        };
    </script>
</head>
<body>
    <div class="voucher-container">
        <div class="voucher-header">
            <img src="../view/img/ZEUS2.png" alt="ZEUS Logo">
            <h2 class="success-icon"><i class="fas fa-check-circle"></i></h2>
            <h2>¡Pago realizado con éxito!</h2>
        </div>

        <div class="voucher-body">
            <div class="info">
                <p><strong>Cliente:</strong> <?= $nombre ?></p>
                <p><strong>Fecha:</strong> <?= $fecha ?></p>
                <p><strong>Dirección:</strong> <?= $direccion ?></p>
                <p><strong>Método de Pago:</strong> <?= $metodo ?></p>
                <p><strong>Código de Venta:</strong> <?= $codigo_venta ?></p>
                <p><strong>ID de Compra:</strong> <?= $id_compra ?></p>
            </div>

            <table class="voucher-items">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($productos as $p): 
                    $subtotal = $p['cantidad'] * $p['precio_unitario'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= $p['cantidad'] ?></td>
                        <td>S/. <?= number_format($p['precio_unitario'], 2) ?></td>
                        <td>S/. <?= number_format($subtotal, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="voucher-total">
                <p>Subtotal sin IGV: <strong>S/. <?= number_format($subtotal_sin_igv, 2) ?></strong></p>
                <p>IGV (18%): <strong>S/. <?= number_format($igv, 2) ?></strong></p>
                <p>Costo de envío: <strong>S/. <?= number_format($envio, 2) ?></strong></p>
                <p>Total pagado: <strong>S/. <?= number_format($raw_total, 2) ?></strong></p>
            </div>
        </div>

        <div class="voucher-footer">
            <a href="../controller/mostrar_comprobante.php?id_compra=<?= $id_compra ?>" target="_blank">
                <i class="fa-solid fa-download"></i> Descargar Comprobante
            </a>
            <a href="../index.php"><i class="fa-solid fa-house"></i> Seguir comprando</a>
        </div>
    </div>
</body>
</html>
