<?php
// ZEUS IMPORTACIONES - FUNCIONES AUXILIARES PARA PROMOCIONES (adaptado)
function formatearPrecioPromocional($precio_original, $descuento, $tipo_descuento = 'porcentaje') {
    $precio_promocional = 0;

    if ($tipo_descuento === 'porcentaje') {
        $precio_promocional = $precio_original - ($precio_original * ($descuento / 100));
    } else if ($tipo_descuento === 'fijo') {
        $precio_promocional = max(0, $precio_original - $descuento);
    }

    return [
        'precio_original' => number_format($precio_original, 2),
        'precio_promocional' => number_format($precio_promocional, 2),
        'ahorro' => number_format($precio_original - $precio_promocional, 2),
        'precio_original_raw' => $precio_original,
        'precio_promocional_raw' => $precio_promocional
    ];
}
function asignarPromocionesALotes($productos, $promocionController) {
    if (empty($productos)) return [];

    $productosConPrecio = [];
    foreach ($productos as $p) {
        $productosConPrecio[$p['id_producto']] = $p['precio'];
    }

    $promos = $promocionController->obtenerPromocionesPorProductoIDs($productosConPrecio);

    foreach ($productos as &$producto) {
        $idProducto = $producto['id_producto'];
        if (isset($promos[$idProducto])) {
            $promo = $promos[$idProducto];
            $producto['tiene_promocion'] = true;
            $producto['precio_original'] = $promo['precio_original'];
            $producto['precio_promocional'] = $promo['precio_final'];
            $producto['porcentaje_descuento'] = $promo['descuento_porcentaje'];
            $producto['ahorro'] = $promo['ahorro'];
            $producto['promocion'] = $promo;
        } else {
            $producto['tiene_promocion'] = false;
        }
    }

    return $productos;
}

function calcularPorcentajeDescuento($precio_original, $precio_promocional) {
    if ($precio_original <= 0) return 0;

    $descuento = (($precio_original - $precio_promocional) / $precio_original) * 100;
    return max(0, round($descuento));
}

function procesarProductoConPromocion($producto, $promocion = null) {
    $producto_procesado = $producto;
    $producto_procesado['tiene_promocion'] = false;
    $producto_procesado['precio_mostrar'] = number_format($producto['precio'], 2);

    if ($promocion && isset($promocion['descuento']) && isset($promocion['fecha_inicio']) && isset($promocion['fecha_fin'])) {
        $fecha_actual = new DateTime();
        $inicio = new DateTime($promocion['fecha_inicio']);
        $fin = new DateTime($promocion['fecha_fin']);

        if ($fecha_actual >= $inicio && $fecha_actual <= $fin) {
            $precios = formatearPrecioPromocional(
                floatval($producto['precio']),
                floatval($promocion['descuento']),
                $promocion['tipo_descuento'] ?? 'porcentaje'
            );

            $producto_procesado['tiene_promocion'] = true;
            $producto_procesado['precio_original'] = $precios['precio_original'];
            $producto_procesado['precio_promocional'] = $precios['precio_promocional'];
            $producto_procesado['precio_mostrar'] = $precios['precio_promocional'];
            $producto_procesado['ahorro'] = $precios['ahorro'];
            $producto_procesado['porcentaje_descuento'] = calcularPorcentajeDescuento($precios['precio_original_raw'], $precios['precio_promocional_raw']);
        }
    }

    return $producto_procesado;
}
?>
