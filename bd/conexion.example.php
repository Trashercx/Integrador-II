<?php
$servername = "your-aiven-host.aivencloud.com";
$username = "your-username";
$password = "your-password";
$dbname = "your-database-name";
$port = 19208; // Puerto típico de Aiven MySQL

// Crear conexión con SSL requerido para Aiven
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Configurar SSL para Aiven (requerido)
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} else {
    echo "Conexión exitosa a Aiven MySQL!";
}

// Opcional: Configurar charset para caracteres especiales
$conn->set_charset("utf8mb4");
?>