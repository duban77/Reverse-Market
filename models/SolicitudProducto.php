<?php
// models/SolicitudProducto.php

// Definición de la clase SolicitudProducto
class SolicitudProducto {
    // Método estático para guardar una nueva solicitud en la base de datos
    public static function guardarSolicitud($producto, $comprador, $vendedor) {
        // Aquí iría el código que guarda la solicitud en la base de datos

        // Obtener la conexión PDO a la base de datos
        $pdo = Database::getConnection();

        // Preparar la consulta SQL para insertar una nueva solicitud
        $sql = "INSERT INTO solicitudes (producto, comprador, vendedor) VALUES (?, ?, ?)";

        // Preparar la sentencia
        $stmt = $pdo->prepare($sql);

        // Ejecutar la sentencia con los valores recibidos y devolver el resultado (true o false)
        return $stmt->execute([$producto, $comprador, $vendedor]);
    }
}
?>
