<?php
/**
 * Controlador de Promociones - Zeus Importaciones
 * Sistema de gestión de promociones y descuentos
 * Autor: Backend Core Developer
 * Fecha: 2025
 */

require_once '../bd/conexion.php';

class PromocionController {
    private $conn;
    
    public function __construct() {
        // Obtener conexión a la base de datos
        global $conn; // tomar $conn del archivo incluido
        $this->conn = $conn;

    }
    

    public function obtenerPromocionesPorProductoIDs(array $idsConPrecios) {
    if (empty($idsConPrecios)) return [];

    $ids = array_keys($idsConPrecios);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $sql = "
        SELECT p.id_producto, pr.id_promocion, pr.descuento, pr.descripcion as promo_descripcion
        FROM producto_promocion p
        INNER JOIN promocion pr ON p.id_promocion = pr.id_promocion
        WHERE p.id_producto IN ($placeholders)
        AND pr.fecha_inicio <= NOW()
        AND pr.fecha_fin >= NOW()
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $promociones = [];
    while ($row = $result->fetch_assoc()) {
        $id = $row['id_producto'];
        $precioOriginal = floatval($idsConPrecios[$id]);
        $calculo = $this->calcularPrecioConDescuento($precioOriginal, $row);

        $promociones[$id] = array_merge($calculo, [
            'descripcion' => $row['promo_descripcion'],
            'id_promocion' => $row['id_promocion']
        ]);
    }

    return $promociones;
}



    /**
     * Obtener todas las promociones activas (vigentes en fecha actual)
     * @return array Array de promociones activas
     */
    public function obtenerPromocionesActivas() {
        try {
            $sql = "SELECT p.*, 
                           COUNT(pp.id_producto) as productos_incluidos
                    FROM promocion p
                    LEFT JOIN producto_promocion pp ON p.id_promocion = pp.id_promocion
                    WHERE p.fecha_inicio <= NOW() 
                    AND p.fecha_fin >= NOW()
                    GROUP BY p.id_promocion
                    ORDER BY p.descuento DESC";
            
            $result = $this->conn->query($sql);
            $promociones = [];
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $promociones[] = $row;
                }
            }
            
            return $promociones;
        } catch (Exception $e) {
            error_log("Error en obtenerPromocionesActivas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si un producto específico tiene promoción activa
     * @param int $id_producto ID del producto a verificar
     * @return array|null Datos de la promoción si existe, null si no tiene promoción
     */
    public function verificarPromocionProducto($id_producto) {
        try {
            $sql = "SELECT p.*, pr.id_promocion, pr.descuento, pr.descripcion as promo_descripcion,
                           pr.fecha_inicio, pr.fecha_fin
                    FROM productos p
                    INNER JOIN producto_promocion pp ON p.id_producto = pp.id_producto
                    INNER JOIN promocion pr ON pp.id_promocion = pr.id_promocion
                    WHERE p.id_producto = ? 
                    AND pr.fecha_inicio <= NOW() 
                    AND pr.fecha_fin >= NOW()
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id_producto);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error en verificarPromocionProducto: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calcular precio con descuento aplicado
     * @param float $precio_original Precio original del producto
     * @param array $promocion Datos de la promoción
     * @return array Array con precio_original, descuento, precio_final
     */
    public function calcularPrecioConDescuento($precio_original, $promocion) {
        try {
            $descuento = isset($promocion['descuento']) ? floatval($promocion['descuento']) : 0.0;
            $precio_original = floatval($precio_original);
            
            // El descuento en BD está como porcentaje (ej: 15.00 = 15%)
            $monto_descuento = ($precio_original * $descuento) / 100;
            $precio_final = $precio_original - $monto_descuento;
            
            // Asegurar que el precio final no sea negativo
            $precio_final = max(0, $precio_final);
            
            return [
                'precio_original' => round($precio_original, 2),
                'descuento_porcentaje' => $descuento,
                'monto_descuento' => round($monto_descuento, 2),
                'precio_final' => round($precio_final, 2),
                'ahorro' => round($monto_descuento, 2)
            ];
        } catch (Exception $e) {
            error_log("Error en calcularPrecioConDescuento: " . $e->getMessage());
            return [
                'precio_original' => $precio_original,
                'descuento_porcentaje' => 0,
                'monto_descuento' => 0,
                'precio_final' => $precio_original,
                'ahorro' => 0
            ];
        }
    }

    /**
     * Aplicar descuentos automáticos a productos del carrito
     * @param array $productos_carrito Array de productos en el carrito
     * @return array Array con productos actualizados con descuentos aplicados
     */
    public function aplicarDescuentoCarrito($productos_carrito) {
        try {
            $productos_actualizados = [];
            $total_ahorro = 0;
            
            foreach ($productos_carrito as $producto) {
                $id_producto = $producto['id_producto'];
                $cantidad = isset($producto['cantidad']) ? intval($producto['cantidad']) : 1;
                
                // Verificar si el producto tiene promoción
                $promocion = $this->verificarPromocionProducto($id_producto);
                
                if ($promocion) {
                    // Aplicar descuento
                    $calculo = $this->calcularPrecioConDescuento($producto['precio'], $promocion);
                    
                    $producto['tiene_promocion'] = true;
                    $producto['promocion_id'] = $promocion['id_promocion'];
                    $producto['promocion_descripcion'] = $promocion['promo_descripcion'];
                    $producto['precio_original'] = $calculo['precio_original'];
                    $producto['precio_con_descuento'] = $calculo['precio_final'];
                    $producto['descuento_porcentaje'] = $calculo['descuento_porcentaje'];
                    $producto['ahorro_unitario'] = $calculo['ahorro'];
                    $producto['ahorro_total'] = $calculo['ahorro'] * $cantidad;
                    
                    // Usar precio con descuento para el subtotal
                    $producto['subtotal'] = $calculo['precio_final'] * $cantidad;
                    
                    $total_ahorro += $producto['ahorro_total'];
                } else {
                    // Sin promoción
                    $producto['tiene_promocion'] = false;
                    $producto['precio_con_descuento'] = $producto['precio'];
                    $producto['subtotal'] = $producto['precio'] * $cantidad;
                    $producto['ahorro_total'] = 0;
                }
                
                $productos_actualizados[] = $producto;
            }
            
            return [
                'productos' => $productos_actualizados,
                'total_ahorro' => round($total_ahorro, 2),
                'productos_con_descuento' => count(array_filter($productos_actualizados, function($p) {
                    return $p['tiene_promocion'];
                }))
            ];
        } catch (Exception $e) {
            error_log("Error en aplicarDescuentoCarrito: " . $e->getMessage());
            return [
                'productos' => $productos_carrito,
                'total_ahorro' => 0,
                'productos_con_descuento' => 0
            ];
        }
    }

    /**
     * Obtener promoción por código promocional (para futuras implementaciones)
     * @param string $codigo Código promocional
     * @return array|null Datos de la promoción si existe
     */
    public function obtenerPromocionPorCodigo($codigo) {
        try {
            // Nota: Esta funcionalidad requeriría agregar campo 'codigo' a la tabla promocion
            // Por ahora retornamos null ya que no existe en el esquema actual
            $sql = "SELECT * FROM promocion 
                    WHERE fecha_inicio <= NOW() 
                    AND fecha_fin >= NOW()
                    ORDER BY descuento DESC
                    LIMIT 1";
            
            $result = $this->conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $promocion = $result->fetch_assoc();
                // Simular validación de código para compatibilidad futura
                if (strtoupper($codigo) === 'ZEUS2025' || strtoupper($codigo) === 'DESCUENTO10') {
                    return $promocion;
                }
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error en obtenerPromocionPorCodigo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Validar código promocional y verificar si puede ser usado por el usuario
     * @param string $codigo Código promocional
     * @param int|null $id_usuario ID del usuario (opcional)
     * @return array Resultado de la validación
     */
    public function validarCodigoPromocional($codigo, $id_usuario = null) {
        try {
            $promocion = $this->obtenerPromocionPorCodigo($codigo);
            
            if (!$promocion) {
                return [
                    'valido' => false,
                    'mensaje' => 'Código promocional no válido o expirado',
                    'promocion' => null
                ];
            }
            
            // Verificar fechas de vigencia
            $fecha_actual = date('Y-m-d H:i:s');
            if ($fecha_actual < $promocion['fecha_inicio'] || $fecha_actual > $promocion['fecha_fin']) {
                return [
                    'valido' => false,
                    'mensaje' => 'El código promocional ha expirado',
                    'promocion' => null
                ];
            }
            
            // Código válido
            return [
                'valido' => true,
                'mensaje' => 'Código promocional aplicado correctamente',
                'promocion' => $promocion
            ];
        } catch (Exception $e) {
            error_log("Error en validarCodigoPromocional: " . $e->getMessage());
            return [
                'valido' => false,
                'mensaje' => 'Error al validar el código promocional',
                'promocion' => null
            ];
        }
    }

    /**
     * Obtener productos que tienen promociones activas
     * @param int $limite Número máximo de productos a retornar
     * @return array Array de productos en promoción
     */
    public function obtenerProductosEnPromocion($limite = 8) {
        try {
            $sql = "SELECT p.*, c.nombre AS nombre_categoria,
                           pr.descuento, pr.descripcion as promo_descripcion,
                           pr.fecha_fin
                    FROM productos p
                    INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                    INNER JOIN producto_promocion pp ON p.id_producto = pp.id_producto
                    INNER JOIN promocion pr ON pp.id_promocion = pr.id_promocion
                    WHERE pr.fecha_inicio <= NOW() 
                    AND pr.fecha_fin >= NOW()
                    AND p.stock > 0
                    ORDER BY pr.descuento DESC, p.nombre ASC
                    LIMIT ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $limite);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $productos = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Calcular precio con descuento
                    $calculo = $this->calcularPrecioConDescuento($row['precio'], $row);
                    $row['precio_original'] = $calculo['precio_original'];
                    $row['precio_con_descuento'] = $calculo['precio_final'];
                    $row['ahorro'] = $calculo['ahorro'];
                    
                    $productos[] = $row;
                }
            }
            
            return $productos;
        } catch (Exception $e) {
            error_log("Error en obtenerProductosEnPromocion: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de promociones para dashboard administrativo
     * @return array Estadísticas de promociones
     */
    public function obtenerEstadisticasPromociones() {
        try {
            $stats = [];
            
            // Promociones activas
            $sql = "SELECT COUNT(*) as total FROM promocion 
                    WHERE fecha_inicio <= NOW() AND fecha_fin >= NOW()";
            $result = $this->conn->query($sql);
            $stats['promociones_activas'] = $result->fetch_assoc()['total'];
            
            // Productos en promoción
            $sql = "SELECT COUNT(DISTINCT pp.id_producto) as total 
                    FROM producto_promocion pp
                    INNER JOIN promocion p ON pp.id_promocion = p.id_promocion
                    WHERE p.fecha_inicio <= NOW() AND p.fecha_fin >= NOW()";
            $result = $this->conn->query($sql);
            $stats['productos_en_promocion'] = $result->fetch_assoc()['total'];
            
            // Próximas a vencer (en 7 días)
            $sql = "SELECT COUNT(*) as total FROM promocion 
                    WHERE fecha_fin BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
                    AND fecha_inicio <= NOW()";
            $result = $this->conn->query($sql);
            $stats['promociones_por_vencer'] = $result->fetch_assoc()['total'];
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error en obtenerEstadisticasPromociones: " . $e->getMessage());
            return [
                'promociones_activas' => 0,
                'productos_en_promocion' => 0,
                'promociones_por_vencer' => 0
            ];
        }
    }

    /**
     * Cerrar conexión a la base de datos
     */
    public function __destruct() {
        if ($this->conn instanceof mysqli) {
            try {
                // Suprime error con @ por si ya está cerrada
                if (@$this->conn->ping()) {
                    @$this->conn->close();
                }
            } catch (Throwable $e) {
                // Evita que se caiga si la conexión ya no es válida
            }
        }
    }


}
?>