<?php
/**
 * RecomendacionController.php
 * Controlador principal para sistema de recomendaciones inteligentes
 * Zeus Importaciones - Sistema de E-commerce
 * 
 * @author IA Backend Core Developer
 * @version 1.0
 */

require_once '../bd/conexion.php';

class RecomendacionController {
    private $conn;
    
    public function __construct() {
        global $conn; // tomar $conn del archivo incluido
        $this->conn = $conn;
    }

    /**
     * Obtener productos relacionados basados en la misma categoría
     * Prioriza productos con mayor stock disponible
     * 
     * @param int $id_producto ID del producto base
     * @param int $limite Número máximo de productos a retornar
     * @return array|null Array de productos relacionados
     */
    public function obtenerProductosRelacionados($id_producto, $limite = 4) {
        try {
            // Primero obtenemos la categoría del producto base
            $sql_categoria = "SELECT id_categoria FROM productos WHERE id_producto = ?";
            $stmt = $this->conn->prepare($sql_categoria);
            $stmt->bind_param("i", $id_producto);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result || $result->num_rows === 0) {
                return null;
            }
            
            $categoria = $result->fetch_assoc();
            $id_categoria = $categoria['id_categoria'];
            
            // Obtener productos relacionados de la misma categoría
            $sql = "SELECT p.*, c.nombre AS nombre_categoria,
                           COALESCE(ventas.total_vendido, 0) as popularidad
                    FROM productos p
                    INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                    LEFT JOIN (
                        SELECT dc.id_producto, SUM(dc.cantidad) as total_vendido
                        FROM detalle_compra dc
                        INNER JOIN compras co ON dc.id_compra = co.id_compra
                        WHERE co.estado = 'Pendiente'
                        GROUP BY dc.id_producto
                    ) ventas ON p.id_producto = ventas.id_producto
                    WHERE p.id_categoria = ? 
                    AND p.id_producto != ?
                    AND p.stock > 0
                    ORDER BY p.stock DESC, popularidad DESC, p.precio ASC
                    LIMIT ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iii", $id_categoria, $id_producto, $limite);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $productos = [];
                while ($row = $result->fetch_assoc()) {
                    $productos[] = $row;
                }
                return $productos;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error en obtenerProductosRelacionados: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener recomendaciones basadas en el historial de compras del usuario
     * Analiza patrones de compra y categorías preferidas
     * 
     * @param int $id_usuario ID del usuario
     * @param int $limite Número máximo de productos a retornar
     * @return array|null Array de productos recomendados
     */
    public function obtenerRecomendacionesPorHistorial($id_usuario, $limite = 4) {
        try {
            // Obtener categorías más compradas por el usuario
            $sql = "SELECT p.id_categoria, c.nombre as nombre_categoria,
                           COUNT(*) as frecuencia_compra,
                           AVG(p.precio) as precio_promedio_categoria
                    FROM detalle_compra dc
                    INNER JOIN compras co ON dc.id_compra = co.id_compra
                    INNER JOIN productos p ON dc.id_producto = p.id_producto
                    INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                    WHERE co.id_usuario = ? 
                    AND co.estado = 'Pendiente'
                    GROUP BY p.id_categoria, c.nombre
                    ORDER BY frecuencia_compra DESC, precio_promedio_categoria ASC
                    LIMIT 3";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $categorias_result = $stmt->get_result();
            
            if (!$categorias_result || $categorias_result->num_rows === 0) {
                // Si no hay historial, retornar productos populares
                return $this->obtenerProductosPopulares($limite);
            }
            
            $categorias_preferidas = [];
            while ($cat = $categorias_result->fetch_assoc()) {
                $categorias_preferidas[] = $cat['id_categoria'];
            }
            
            // Obtener productos que NO ha comprado en sus categorías preferidas
            $placeholders = str_repeat('?,', count($categorias_preferidas) - 1) . '?';
            $sql_productos = "SELECT p.*, c.nombre AS nombre_categoria,
                                     COALESCE(ventas.total_vendido, 0) as popularidad,
                                     CASE WHEN p.precio <= ? THEN 1 ELSE 0 END as en_rango_precio
                              FROM productos p
                              INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                              LEFT JOIN (
                                  SELECT dc.id_producto, SUM(dc.cantidad) as total_vendido
                                  FROM detalle_compra dc
                                  INNER JOIN compras co ON dc.id_compra = co.id_compra
                                  WHERE co.estado = 'Pendiente'
                                  GROUP BY dc.id_producto
                              ) ventas ON p.id_producto = ventas.id_producto
                              WHERE p.id_categoria IN ($placeholders)
                              AND p.stock > 0
                              AND p.id_producto NOT IN (
                                  SELECT DISTINCT dc2.id_producto
                                  FROM detalle_compra dc2
                                  INNER JOIN compras co2 ON dc2.id_compra = co2.id_compra
                                  WHERE co2.id_usuario = ?
                              )
                              ORDER BY en_rango_precio DESC, popularidad DESC, p.stock DESC
                              LIMIT ?";
            
            // Calcular precio promedio de compras del usuario para filtrar por rango
            $precio_promedio = $this->obtenerPrecioPromedioComprasUsuario($id_usuario);
            
            $params = array_merge([$precio_promedio], $categorias_preferidas, [$id_usuario, $limite]);
            $types = 'd' . str_repeat('i', count($categorias_preferidas)) . 'ii';
            
            $stmt = $this->conn->prepare($sql_productos);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $productos = [];
                while ($row = $result->fetch_assoc()) {
                    $productos[] = $row;
                }
                return $productos;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error en obtenerRecomendacionesPorHistorial: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener productos más populares basados en ventas
     * 
     * @param int $limite Número máximo de productos a retornar
     * @return array|null Array de productos populares
     */
    public function obtenerProductosPopulares($limite = 4) {
        try {
            $sql = "SELECT p.*, c.nombre AS nombre_categoria,
                           COALESCE(ventas.total_vendido, 0) as total_vendido,
                           COALESCE(ventas.veces_comprado, 0) as veces_comprado
                    FROM productos p
                    INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                    LEFT JOIN (
                        SELECT dc.id_producto, 
                               SUM(dc.cantidad) as total_vendido,
                               COUNT(DISTINCT dc.id_compra) as veces_comprado
                        FROM detalle_compra dc
                        INNER JOIN compras co ON dc.id_compra = co.id_compra
                        WHERE co.estado = 'Pendiente'
                        AND co.fecha_compra >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                        GROUP BY dc.id_producto
                    ) ventas ON p.id_producto = ventas.id_producto
                    WHERE p.stock > 0
                    ORDER BY total_vendido DESC, veces_comprado DESC, p.stock DESC
                    LIMIT ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $limite);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $productos = [];
                while ($row = $result->fetch_assoc()) {
                    $productos[] = $row;
                }
                return $productos;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error en obtenerProductosPopulares: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener productos frecuentemente comprados juntos
     * Análisis de patrones de compra en el mismo pedido
     * 
     * @param int $id_producto ID del producto base
     * @param int $limite Número máximo de productos a retornar
     * @return array|null Array de productos frecuentemente comprados juntos
     */
    public function obtenerFrecuentementeCompradosJuntos($id_producto, $limite = 4) {
        try {
            $sql = "SELECT p.*, c.nombre AS nombre_categoria,
                           frecuencia.veces_juntos,
                           frecuencia.porcentaje_frecuencia
                    FROM productos p
                    INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                    INNER JOIN (
                        SELECT dc2.id_producto,
                               COUNT(*) as veces_juntos,
                               ROUND((COUNT(*) * 100.0 / total_compras.total), 2) as porcentaje_frecuencia
                        FROM detalle_compra dc1
                        INNER JOIN detalle_compra dc2 ON dc1.id_compra = dc2.id_compra
                        INNER JOIN compras co ON dc1.id_compra = co.id_compra
                        CROSS JOIN (
                            SELECT COUNT(DISTINCT id_compra) as total
                            FROM detalle_compra
                            WHERE id_producto = ?
                        ) total_compras
                        WHERE dc1.id_producto = ?
                        AND dc2.id_producto != ?
                        AND co.estado = 'Pendiente'
                        GROUP BY dc2.id_producto
                        HAVING veces_juntos >= 2
                        ORDER BY veces_juntos DESC, porcentaje_frecuencia DESC
                    ) frecuencia ON p.id_producto = frecuencia.id_producto
                    WHERE p.stock > 0
                    ORDER BY frecuencia.veces_juntos DESC, frecuencia.porcentaje_frecuencia DESC
                    LIMIT ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iiii", $id_producto, $id_producto, $id_producto, $limite);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $productos = [];
                while ($row = $result->fetch_assoc()) {
                    $productos[] = $row;
                }
                return $productos;
            }
            
            // Si no hay productos frecuentemente comprados juntos, retornar relacionados
            return $this->obtenerProductosRelacionados($id_producto, $limite);
        } catch (Exception $e) {
            error_log("Error en obtenerFrecuentementeCompradosJuntos: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generar recomendaciones personalizadas combinando múltiples algoritmos
     * 
     * @param int $id_usuario ID del usuario
     * @param int $limite Número máximo de productos a retornar
     * @return array Array de productos recomendados con scores
     */
    public function generarRecomendacionesPersonalizadas($id_usuario, $limite = 8) {
        try {
            $recomendaciones = [];
            $productos_vistos = [];
            
            // 1. Obtener recomendaciones por historial (40% del límite)
            $limite_historial = max(1, floor($limite * 0.4));
            $por_historial = $this->obtenerRecomendacionesPorHistorial($id_usuario, $limite_historial);
            
            if ($por_historial) {
                foreach ($por_historial as $producto) {
                    if (!in_array($producto['id_producto'], $productos_vistos)) {
                        $producto['score_recomendacion'] = 90;
                        $producto['razon_recomendacion'] = 'Basado en tu historial de compras';
                        $recomendaciones[] = $producto;
                        $productos_vistos[] = $producto['id_producto'];
                    }
                }
            }
            
            // 2. Obtener productos populares (30% del límite)
            $limite_populares = max(1, floor($limite * 0.3));
            $populares = $this->obtenerProductosPopulares($limite_populares);
            
            if ($populares) {
                foreach ($populares as $producto) {
                    if (!in_array($producto['id_producto'], $productos_vistos) && count($recomendaciones) < $limite) {
                        $producto['score_recomendacion'] = 70;
                        $producto['razon_recomendacion'] = 'Producto popular entre otros usuarios';
                        $recomendaciones[] = $producto;
                        $productos_vistos[] = $producto['id_producto'];
                    }
                }
            }
            
            // 3. Completar con productos aleatorios de categorías diversas (30% restante)
            if (count($recomendaciones) < $limite) {
                $productos_restantes = $limite - count($recomendaciones);
                $productos_diversos = $this->obtenerProductosDiversos($productos_vistos, $productos_restantes);
                
                if ($productos_diversos) {
                    foreach ($productos_diversos as $producto) {
                        if (count($recomendaciones) < $limite) {
                            $producto['score_recomendacion'] = 50;
                            $producto['razon_recomendacion'] = 'Descubre algo nuevo';
                            $recomendaciones[] = $producto;
                        }
                    }
                }
            }
            
            // Ordenar por score de recomendacion
            usort($recomendaciones, function($a, $b) {
                return $b['score_recomendacion'] <=> $a['score_recomendacion'];
            });
            
            return array_slice($recomendaciones, 0, $limite);
        } catch (Exception $e) {
            error_log("Error en generarRecomendacionesPersonalizadas: " . $e->getMessage());
            return $this->obtenerProductosPopulares($limite);
        }
    }

    /**
     * Obtener precio promedio de compras de un usuario
     * Método auxiliar para filtrar recomendaciones por rango de precio
     * 
     * @param int $id_usuario ID del usuario
     * @return float Precio promedio de compras del usuario
     */
    private function obtenerPrecioPromedioComprasUsuario($id_usuario) {
        try {
            $sql = "SELECT AVG(p.precio) as precio_promedio
                    FROM detalle_compra dc
                    INNER JOIN compras co ON dc.id_compra = co.id_compra
                    INNER JOIN productos p ON dc.id_producto = p.id_producto
                    WHERE co.id_usuario = ? 
                    AND co.estado = 'Pendiente";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['precio_promedio'] ? floatval($row['precio_promedio']) : 100.0;
            }
            
            return 100.0; // Precio promedio por defecto
        } catch (Exception $e) {
            error_log("Error en obtenerPrecioPromedioComprasUsuario: " . $e->getMessage());
            return 100.0;
        }
    }

    /**
     * Obtener productos diversos de diferentes categorías
     * Método auxiliar para completar recomendaciones
     * 
     * @param array $productos_excluir IDs de productos a excluir
     * @param int $limite Número de productos a obtener
     * @return array|null Array de productos diversos
     */
    private function obtenerProductosDiversos($productos_excluir, $limite) {
        try {
            $exclusiones = '';
            $params = [];
            $types = '';
            
            if (!empty($productos_excluir)) {
                $placeholders = str_repeat('?,', count($productos_excluir) - 1) . '?';
                $exclusiones = "AND p.id_producto NOT IN ($placeholders)";
                $params = $productos_excluir;
                $types = str_repeat('i', count($productos_excluir));
            }
            
            $sql = "SELECT p.*, c.nombre AS nombre_categoria
                    FROM productos p
                    INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                    WHERE p.stock > 0 $exclusiones
                    ORDER BY RAND(), p.stock DESC
                    LIMIT ?";
            
            $params[] = $limite;
            $types .= 'i';
            
            $stmt = $this->conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $productos = [];
                while ($row = $result->fetch_assoc()) {
                    $productos[] = $row;
                }
                return $productos;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error en obtenerProductosDiversos: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener recomendaciones para usuarios anónimos (sin login)
     * Basado en productos populares y diversidad de categorías
     * 
     * @param int $limite Número máximo de productos a retornar
     * @return array|null Array de productos recomendados
     */
    public function obtenerRecomendacionesAnonimas($limite = 8) {
        try {
            // 60% productos populares, 40% productos diversos
            $limite_populares = max(1, floor($limite * 0.6));
            $limite_diversos = $limite - $limite_populares;
            
            $recomendaciones = [];
            $productos_vistos = [];
            
            // Obtener productos populares
            $populares = $this->obtenerProductosPopulares($limite_populares);
            if ($populares) {
                foreach ($populares as $producto) {
                    $producto['score_recomendacion'] = 80;
                    $producto['razon_recomendacion'] = 'Producto popular';
                    $recomendaciones[] = $producto;
                    $productos_vistos[] = $producto['id_producto'];
                }
            }
            
            // Completar con productos diversos
            $diversos = $this->obtenerProductosDiversos($productos_vistos, $limite_diversos);
            if ($diversos) {
                foreach ($diversos as $producto) {
                    $producto['score_recomendacion'] = 60;
                    $producto['razon_recomendacion'] = 'Recomendado para ti';
                    $recomendaciones[] = $producto;
                }
            }
            
            return array_slice($recomendaciones, 0, $limite);
        } catch (Exception $e) {
            error_log("Error en obtenerRecomendacionesAnonimas: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener productos recomendados por categoría específica
     * Útil para páginas de categoría con productos sugeridos
     * 
     * @param int $id_categoria ID de la categoría
     * @param int $limite Número máximo de productos a retornar
     * @param array $excluir_productos IDs de productos a excluir
     * @return array|null Array de productos recomendados
     */
    public function obtenerRecomendacionesPorCategoria($id_categoria, $limite = 4, $excluir_productos = []) {
        try {
            $exclusiones = '';
            $params = [$id_categoria];
            $types = 'i';
            
            if (!empty($excluir_productos)) {
                $placeholders = str_repeat('?,', count($excluir_productos) - 1) . '?';
                $exclusiones = "AND p.id_producto NOT IN ($placeholders)";
                $params = array_merge($params, $excluir_productos);
                $types .= str_repeat('i', count($excluir_productos));
            }
            
            $sql = "SELECT p.*, c.nombre AS nombre_categoria,
                           COALESCE(ventas.total_vendido, 0) as popularidad
                    FROM productos p
                    INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                    LEFT JOIN (
                        SELECT dc.id_producto, SUM(dc.cantidad) as total_vendido
                        FROM detalle_compra dc
                        INNER JOIN compras co ON dc.id_compra = co.id_compra
                        WHERE co.estado = 'Pendiente'
                        GROUP BY dc.id_producto
                    ) ventas ON p.id_producto = ventas.id_producto
                    WHERE p.id_categoria = ? 
                    AND p.stock > 0
                    $exclusiones
                    ORDER BY popularidad DESC, p.precio ASC, p.stock DESC
                    LIMIT ?";
            
            $params[] = $limite;
            $types .= 'i';
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $productos = [];
                while ($row = $result->fetch_assoc()) {
                    $productos[] = $row;
                }
                return $productos;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error en obtenerRecomendacionesPorCategoria: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener estadísticas de recomendaciones
     * Método auxiliar para análisis y métricas
     * 
     * @return array|null Estadísticas del sistema de recomendaciones
     */
    public function obtenerEstadisticasRecomendaciones() {
        try {
            $sql = "SELECT 
                        COUNT(DISTINCT p.id_producto) as total_productos_activos,
                        COUNT(DISTINCT c.id_categoria) as total_categorias,
                        AVG(p.precio) as precio_promedio,
                        SUM(p.stock) as stock_total,
                        COUNT(DISTINCT co.id_usuario) as usuarios_con_compras
                    FROM productos p
                    INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                    LEFT JOIN detalle_compra dc ON p.id_producto = dc.id_producto
                    LEFT JOIN compras co ON dc.id_compra = co.id_compra
                    WHERE p.stock > 0";
            
            $result = $this->conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error en obtenerEstadisticasRecomendaciones: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registrar interacción de usuario con recomendación
     * Para futuras mejoras del algoritmo de recomendaciones
     * 
     * @param int $id_usuario ID del usuario
     * @param int $id_producto ID del producto recomendado
     * @param string $tipo_interaccion Tipo de interacción (vista, click, compra)
     * @return bool True si se registró correctamente
     */
    public function registrarInteraccionRecomendacion($id_usuario, $id_producto, $tipo_interaccion = 'vista') {
        try {
            // Nota: Este método requeriría una tabla adicional para tracking
            // Por ahora solo logueamos la interacción
            error_log("Interacción registrada - Usuario: $id_usuario, Producto: $id_producto, Tipo: $tipo_interaccion");
            return true;
        } catch (Exception $e) {
            error_log("Error en registrarInteraccionRecomendacion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Destructor - cerrar conexión
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
                error_log("⚠️ mysqli ya cerrada en " . __CLASS__);
            }
        }
    }


}
?>