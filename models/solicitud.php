<?php
// Definición de la clase Solicitud
class Solicitud {

    // Método estático para crear una nueva solicitud
    public static function crear($db, $precio, $tiempo, $condiciones, $id_comprador, $id_vendedor) {
        // Preparar la consulta SQL para insertar una solicitud
        $stmt = $db->prepare("INSERT INTO solicitudes (precio, tiempo_entrega, condiciones, id_comprador, id_vendedor) VALUES (?, ?, ?, ?, ?)");
        // Ejecutar la consulta con los valores proporcionados
        $stmt->execute([$precio, $tiempo, $condiciones, $id_comprador, $id_vendedor]);
    }

    // Método estático para listar solicitudes realizadas por un comprador
    public static function listarPorComprador($db, $id_comprador) {
        // Preparar la consulta SQL para obtener solicitudes de un comprador ordenadas por fecha
        $stmt = $db->prepare("SELECT * FROM solicitudes WHERE id_comprador = ? ORDER BY fecha_publicacion DESC");
        // Ejecutar la consulta con el ID del comprador
        $stmt->execute([$id_comprador]);
        // Retornar todos los resultados como un arreglo asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
