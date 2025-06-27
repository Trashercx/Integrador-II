<?php
class UsuarioModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

  
    public function obtenerUsuarios() {
    $sql = "
        SELECT u.*, r.permiso 
        FROM usuarios u
        INNER JOIN rol r ON u.id_rol = r.id_rol
    ";
    $result = $this->conn->query($sql);

    if ($result->num_rows > 0) {
        return $result;
    } else {
        return null;
    }
}
    public function obtenerUsuarioPorId($id) {
    $stmt = $this->conn->prepare("
        SELECT u.*, r.permiso
        FROM usuarios u
        INNER JOIN rol r ON u.id_rol = r.id_rol
        WHERE u.id_usuario = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_assoc(); // Devuelve array asociativo con los datos del usuario
}


    

}