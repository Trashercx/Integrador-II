<?php

require_once '../bd/conexion.php';

require_once '../model/ProductoModel.php';


class ProductoController {
    private $productoModel;

    public function __construct() {
        $this->productoModel = new ProductoModel($GLOBALS['conn']); // Usa la conexión global
    }

    public function listarProductos($categoria = null) {
        return $this->productoModel->obtenerProductos($categoria);
    }
    public function obtenerProductoDestacado($idProducto) {
        return $this->productoModel->obtenerProductoDestacado($idProducto);
    }
    public function obtenerProductosDetalles($idProducto) {
        return $this->productoModel->obtenerProductosDetalles($idProducto);
    }

   
}