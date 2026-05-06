<?php
$host      = "localhost";
$usuario   = "root";
$contrasena = "";
$base_datos = "reverse_market";

$conn = new mysqli($host, $usuario, $contrasena, $base_datos);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
