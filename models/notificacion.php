<?php
// Clase para manejar las notificaciones del sistema
class Notificacion {

    // Método estático para enviar una notificación a un usuario específico
    public static function enviar($db, $id_usuario, $mensaje) {
        // Preparar la sentencia SQL para insertar una nueva notificación
        $stmt = $db->prepare("INSERT INTO notificaciones (mensaje, id_usuario_destino) VALUES (?, ?)");
        // Ejecutar la consulta con los parámetros proporcionados
        return $stmt->execute([$mensaje, $id_usuario]);
    }

    // Método estático para obtener todas las notificaciones de un usuario
    public static function obtenerNotificaciones($db, $id_usuario) {
        // Preparar la consulta SQL para obtener las notificaciones del usuario ordenadas por fecha descendente
        $stmt = $db->prepare("SELECT id, mensaje, fecha, leido FROM notificaciones WHERE id_usuario_destino = ? ORDER BY fecha DESC");
        // Ejecutar la consulta con el ID del usuario
        $stmt->execute([$id_usuario]);
        // Retornar los resultados como arreglo asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
