<?php

require_once '../bd/conexion.php';
require_once '../model/ProductoModel.php';

class ProductoController {
    private $productoModel;

    public function __construct() {
        $this->productoModel = new ProductoModel($GLOBALS['conn']);
    }

    /**
     * Método principal optimizado - devuelve productos con promociones procesadas
     * UNA sola consulta para todo
     */
    public function listarProductosConPromociones($categoria = null, $soloEnPromocion = false) {
        $result = $this->productoModel->obtenerProductosConPromociones($categoria, $soloEnPromocion);
        
        $productos = [];
        while ($producto = $result->fetch_assoc()) {
            // Procesar la información de promoción ya obtenida en la consulta
            $productos[] = $this->procesarDatosPromocion($producto);
        }
        
        return $productos;
    }

    /**
     * Obtener un producto con promoción (optimizado)
     */
    public function obtenerProductoConPromocion($idProducto) {
        $producto = $this->productoModel->obtenerProductoConPromocion($idProducto);
        
        if ($producto) {
            return $this->procesarDatosPromocion($producto);
        }
        
        return null;
    }

    /**
     * Método privado para procesar datos de promoción consistentemente
     */
    private function procesarDatosPromocion($producto) {
        if ($producto['tiene_promocion_activa']) {
            $producto['tiene_promocion'] = true;
            $producto['precio_original'] = $producto['precio'];
            $producto['precio_promocional'] = $producto['precio_final'];
        } else {
            $producto['tiene_promocion'] = false;
            $producto['precio_original'] = $producto['precio'];
            $producto['precio_promocional'] = $producto['precio'];
        }
        
        return $producto;
    }

    /**
     * Buscar productos optimizado
     */
    public function buscarProductos($termino, $categoria = null) {
        $result = $this->productoModel->buscarProductosConPromociones($termino, $categoria);
        
        $productos = [];
        while ($producto = $result->fetch_assoc()) {
            $productos[] = $this->procesarDatosPromocion($producto);
        }
        
        return $productos;
    }

    /**
     * Solo productos en promoción
     */
    public function listarProductosEnPromocion($categoria = null) {
        return $this->listarProductosConPromociones($categoria, true);
    }

    /**
     * Verificar si un producto tiene promoción (optimizado)
     */
    public function tienePromocionActiva($idProducto) {
        $producto = $this->productoModel->obtenerProductoConPromocion($idProducto);
        return $producto && $producto['tiene_promocion_activa'];
    }

    // MÉTODOS DE COMPATIBILIDAD (para no romper código existente)
    
    public function listarProductos($categoria = null) {
        return $this->productoModel->obtenerProductos($categoria);
    }

    public function obtenerProductoDestacado($idProducto) {
        return $this->obtenerProductoConPromocion($idProducto);
    }

    public function obtenerProductosDetalles($idProducto) {
        return $this->obtenerProductoConPromocion($idProducto);
    }

    /**
     * Obtener productos por IDs (para carritos, etc.)
     */
    public function obtenerProductosPorIds($ids) {
        if (empty($ids)) {
            return [];
        }

        $result = $this->productoModel->obtenerProductosPorIds($ids);
        
        $productos = [];
        while ($producto = $result->fetch_assoc()) {
            $productos[] = $this->procesarDatosPromocion($producto);
        }
        
        return $productos;
    }
}