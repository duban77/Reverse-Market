<?php
// Clase para gestionar las necesidades de los compradores
class Necesidad {

    // Método para crear una nueva necesidad asociada a un comprador
    public static function crear($db, $titulo, $descripcion, $id_comprador) {
        // Se prepara una sentencia SQL para insertar la necesidad con la fecha actual
        $stmt = $db->prepare("INSERT INTO necesidades (titulo, descripcion, id_comprador, fecha_creacion) VALUES (?, ?, ?, NOW())");
        // Se ejecuta la consulta con los valores proporcionados
        return $stmt->execute([$titulo, $descripcion, $id_comprador]);
    }

    // Método para obtener todas las necesidades de un comprador específico
    public static function obtenerPorComprador($db, $id_comprador) {
        // Preparar la consulta SQL para seleccionar necesidades según el ID del comprador
        $stmt = $db->prepare("SELECT * FROM necesidades WHERE id_comprador = ?");
        // Ejecutar la consulta con el ID del comprador
        $stmt->execute([$id_comprador]);
        // Devolver los resultados como un arreglo asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para obtener todas las necesidades junto con el nombre del comprador que las creó
    public static function obtenerTodas($db) {
        // Realizar una consulta SQL que une las tablas necesidades y usuarios para obtener también el nombre del comprador
        $stmt = $db->query("SELECT n.*, u.nombre AS comprador FROM necesidades n JOIN usuarios u ON n.id_comprador = u.id");
        // Devolver todos los resultados como un arreglo asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para obtener todas las respuestas a las necesidades de un comprador específico
    public static function obtenerRespuestas($db, $id_comprador) {
        // Preparar la consulta SQL para obtener los productos ofrecidos como respuesta a las necesidades de un comprador
        $stmt = $db->prepare("
            SELECT n.titulo, n.id AS id_necesidad, p.*
            FROM necesidades n
            JOIN respuesta_necesidad r ON n.id = r.id_necesidad
            JOIN productos p ON r.id_producto = p.id
            WHERE n.id_comprador = ?
        ");
        // Ejecutar la consulta con el ID del comprador
        $stmt->execute([$id_comprador]);
        // Devolver los resultados como un arreglo asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
