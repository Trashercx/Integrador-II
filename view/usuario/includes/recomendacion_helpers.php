<?php
// ZEUS IMPORTACIONES - FUNCIONES AUXILIARES PARA RECOMENDACIONES (adaptado)
require_once 'promocion_helpers.php'; // Asegura que se incluyan funciones de promociones si se usan

function generarHTMLProductoRecomendado($producto, $promocion = null) {
    $producto = procesarProductoConPromocion($producto, $promocion);

    $html = '<div class="col-4 card producto-relacionado" data-aos="zoom-in" data-aos-duration="1500">';
    
    if ($producto['tiene_promocion']) {
        $html .= '<div class="promo-badge-small">¡OFERTA!</div>';
        $html .= '<div class="descuento-badge-small">-' . $producto['porcentaje_descuento'] . '%</div>';
    }

    $html .= '<a href="productosDetalles.php?id=' . htmlspecialchars($producto['id_producto']) . '">';
    $html .= '<img src="' . htmlspecialchars(preg_replace('/^\.\.\//', '', $producto['imagen'])) . '" alt="' . htmlspecialchars($producto['nombre']) . '" style="background-color: #f0f0f0;">';
    $html .= '</a>';
    
    $html .= '<h4>' . htmlspecialchars($producto['nombre']) . '</h4>';

    if ($producto['tiene_promocion']) {
        $html .= '<div class="precio-container-small">';
        $html .= '<span class="precio-original-small">S/. ' . $producto['precio_original'] . '</span>';
        $html .= '<span class="precio-promocional-small">S/. ' . $producto['precio_promocional'] . '</span>';
        $html .= '</div>';
    } else {
        $html .= '<p>S/. ' . number_format($producto['precio'], 2) . '</p>';
    }

    $html .= '<div class="relacionado-info"><small><i class="fa fa-heart"></i> Te puede interesar</small></div>';
    $html .= '</div>';

    return $html;
}
?>
