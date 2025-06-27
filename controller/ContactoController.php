<?php
require_once '../bd/conexion.php';
require_once '../model/ContactoModel.php';

class ContactoController {
    private $contactoModel;

    public function __construct() {
        $this->contactoModel = new ContactoModel($GLOBALS['conn']);
    }

    public function guardar() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $resultado = $this->contactoModel->guardarMensaje(
            $_POST['nombre'],
            $_POST['email'],
            $_POST['mensaje']
        );

        if ($resultado) {
            header("Location: contactanos.php?success=1");
        } else {
            header("Location: contactanos.php?error=1");
        }
        exit; // Muy importante
    }
}
}