<?php 
class Calificacion {
    // Método estático para insertar una calificación en la base de datos
    // Recibe la conexión $db, ids del comprador, vendedor, transacción, puntos y comentario
    public static function calificar($db, $id_comprador, $id_vendedor, $id_transaccion, $puntos, $comentario) {
        // Preparar la consulta para insertar una nueva fila en la tabla 'calificaciones'
        $stmt = $db->prepare("INSERT INTO calificaciones (id_comprador, id_vendedor, id_transaccion, puntuacion, comentario) VALUES (?, ?, ?, ?, ?)");
        
        // Ejecutar la consulta con los valores recibidos y devolver el resultado (true si fue exitoso)
        return $stmt->execute([$id_comprador, $id_vendedor, $id_transaccion, $puntos, $comentario]);
    }
}
?>
