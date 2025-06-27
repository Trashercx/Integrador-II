<?php
class ContactoModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function guardarMensaje($nombre, $email, $mensaje) {
        $nombre = $this->conn->real_escape_string($nombre);
        $email = $this->conn->real_escape_string($email);
        $mensaje = $this->conn->real_escape_string($mensaje);

        $sql = "INSERT INTO contacto (nombre, email, mensaje) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $nombre, $email, $mensaje);
        
        return $stmt->execute();
    }
}