<?php

class ProductoModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerProductos($categoria = null) {
        $sql = "SELECT p.*, c.nombre AS nombre_categoria 
                FROM productos p 
                INNER JOIN categoria c ON p.id_categoria = c.id_categoria";

        // Filtro por categoría (si se envía por GET o argumento)
        if (isset($_GET['categoria']) && $_GET['categoria'] !== 'Todos los productos') {
            $categoria = $this->conn->real_escape_string($_GET['categoria']);
            $sql .= " WHERE c.nombre = '$categoria'";
        }

        $sql .= " ORDER BY p.id_producto ASC";

        return $this->conn->query($sql);
    }

    public function obtenerProductoDestacado($idProducto) {
        $sql = "SELECT p.*, c.nombre AS nombre_categoria 
                FROM productos p 
                INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                WHERE p.id_producto = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idProducto);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    public function obtenerProductosDetalles($idProducto) {
        $sql = "SELECT p.*, c.nombre AS nombre_categoria 
                FROM productos p 
                INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                WHERE p.id_producto = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idProducto);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }
}
