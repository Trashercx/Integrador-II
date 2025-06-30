<?php

class ProductoModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    /**
     * Método principal optimizado - obtiene productos con promociones en UNA sola consulta
     */
    public function obtenerProductosConPromociones($categoria = null, $soloEnPromocion = false) {
        $sql = "SELECT p.*, 
                       c.nombre AS nombre_categoria,
                       pr.id_promocion, 
                       pr.descuento, 
                       pr.descripcion as descripcion_promocion,
                       pr.fecha_inicio, 
                       pr.fecha_fin,
                       CASE WHEN pr.id_promocion IS NOT NULL 
                            AND pr.fecha_inicio <= NOW() 
                            AND pr.fecha_fin >= NOW() 
                       THEN 1 ELSE 0 END as tiene_promocion_activa,
                       CASE WHEN pr.id_promocion IS NOT NULL 
                            AND pr.fecha_inicio <= NOW() 
                            AND pr.fecha_fin >= NOW() 
                       THEN ROUND(p.precio * (1 - pr.descuento/100), 2)
                       ELSE p.precio END as precio_final
                FROM productos p 
                INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN producto_promocion pp ON p.id_producto = pp.id_producto
                LEFT JOIN promocion pr ON pp.id_promocion = pr.id_promocion";

        $params = [];
        $types = "";

        // Filtro por categoría
        if ($categoria && $categoria !== 'Todos los productos') {
            $sql .= " WHERE c.nombre = ?";
            $params[] = $categoria;
            $types .= "s";
        }

        // Filtro solo productos en promoción
        if ($soloEnPromocion) {
            $whereClause = $categoria ? " AND" : " WHERE";
            $sql .= $whereClause . " pr.id_promocion IS NOT NULL 
                     AND pr.fecha_inicio <= NOW() 
                     AND pr.fecha_fin >= NOW()";
        }

        $sql .= " ORDER BY tiene_promocion_activa DESC, p.id_producto ASC";

        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            return $stmt->get_result();
        } else {
            return $this->conn->query($sql);
        }
    }

    /**
     * Método optimizado para obtener UN producto con promoción
     */
    public function obtenerProductoConPromocion($idProducto) {
        $sql = "SELECT p.*, 
                       c.nombre AS nombre_categoria,
                       pr.id_promocion, 
                       pr.descuento, 
                       pr.descripcion as descripcion_promocion,
                       pr.fecha_inicio, 
                       pr.fecha_fin,
                       CASE WHEN pr.id_promocion IS NOT NULL 
                            AND pr.fecha_inicio <= NOW() 
                            AND pr.fecha_fin >= NOW() 
                       THEN 1 ELSE 0 END as tiene_promocion_activa,
                       CASE WHEN pr.id_promocion IS NOT NULL 
                            AND pr.fecha_inicio <= NOW() 
                            AND pr.fecha_fin >= NOW() 
                       THEN ROUND(p.precio * (1 - pr.descuento/100), 2)
                       ELSE p.precio END as precio_final
                FROM productos p 
                INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN producto_promocion pp ON p.id_producto = pp.id_producto
                LEFT JOIN promocion pr ON pp.id_promocion = pr.id_promocion
                WHERE p.id_producto = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idProducto);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Método simple para productos sin promociones (backward compatibility)
     */
    public function obtenerProductos($categoria = null) {
        $sql = "SELECT p.*, c.nombre AS nombre_categoria 
                FROM productos p 
                INNER JOIN categoria c ON p.id_categoria = c.id_categoria";

        if ($categoria && $categoria !== 'Todos los productos') {
            $sql .= " WHERE c.nombre = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $categoria);
            $stmt->execute();
            return $stmt->get_result();
        }

        $sql .= " ORDER BY p.id_producto ASC";
        return $this->conn->query($sql);
    }

    /**
     * Buscar productos con promociones incluidas
     */
    public function buscarProductosConPromociones($termino, $categoria = null) {
        $sql = "SELECT p.*, 
                       c.nombre AS nombre_categoria,
                       pr.id_promocion, 
                       pr.descuento, 
                       pr.descripcion as descripcion_promocion,
                       CASE WHEN pr.id_promocion IS NOT NULL 
                            AND pr.fecha_inicio <= NOW() 
                            AND pr.fecha_fin >= NOW() 
                       THEN 1 ELSE 0 END as tiene_promocion_activa,
                       CASE WHEN pr.id_promocion IS NOT NULL 
                            AND pr.fecha_inicio <= NOW() 
                            AND pr.fecha_fin >= NOW() 
                       THEN ROUND(p.precio * (1 - pr.descuento/100), 2)
                       ELSE p.precio END as precio_final
                FROM productos p 
                INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN producto_promocion pp ON p.id_producto = pp.id_producto
                LEFT JOIN promocion pr ON pp.id_promocion = pr.id_promocion
                WHERE (p.nombre LIKE ? OR p.descripcion LIKE ?)";

        $params = ["%$termino%", "%$termino%"];
        $types = "ss";

        if ($categoria && $categoria !== 'Todos los productos') {
            $sql .= " AND c.nombre = ?";
            $params[] = $categoria;
            $types .= "s";
        }

        $sql .= " ORDER BY tiene_promocion_activa DESC, p.id_producto ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt->get_result();
    }

    /**
     * Verificar existencia de producto
     */
    public function existeProducto($idProducto) {
        $sql = "SELECT COUNT(*) as total FROM productos WHERE id_producto = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idProducto);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'] > 0;
    }

    /**
     * Obtener productos por IDs (optimizado para carritos)
     */
    public function obtenerProductosPorIds($ids) {
        if (empty($ids)) {
            return null;
        }

        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "SELECT p.*, 
                       c.nombre AS nombre_categoria,
                       pr.id_promocion, 
                       pr.descuento,
                       CASE WHEN pr.id_promocion IS NOT NULL 
                            AND pr.fecha_inicio <= NOW() 
                            AND pr.fecha_fin >= NOW() 
                       THEN ROUND(p.precio * (1 - pr.descuento/100), 2)
                       ELSE p.precio END as precio_final
                FROM productos p 
                INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN producto_promocion pp ON p.id_producto = pp.id_producto
                LEFT JOIN promocion pr ON pp.id_promocion = pr.id_promocion
                WHERE p.id_producto IN ($placeholders)
                ORDER BY p.id_producto ASC";

        $stmt = $this->conn->prepare($sql);
        $types = str_repeat('i', count($ids));
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();

        return $stmt->get_result();
    }
}