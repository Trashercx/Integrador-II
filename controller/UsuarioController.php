<?php

require_once __DIR__ . '/../bd/conexion.php';
require_once __DIR__ . '/../model/UsuarioModel.php';


class UsuarioController {

    private $usuarioModel;
    public function __construct() {
        $this->usuarioModel = new UsuarioModel($GLOBALS['conn']); // Usa la conexión global
    }

    
    public function listarUsuarios() {
        return $this->usuarioModel->obtenerUsuarios();
    }
    
    public function obtenerUsuarioPorId($id) {
    return $this->usuarioModel->obtenerUsuarioPorId($id);
    }


}