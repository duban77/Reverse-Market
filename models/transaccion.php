<?php 

// Definición de la clase Transaccion
class Transaccion {
    // Método estático para crear una nueva transacción
    public static function crear($db, $id_comprador, $id_vendedor, $id_oferta) {
        // Preparar la consulta SQL para insertar una nueva fila en la tabla transacciones
        // El campo 'estado' se establece por defecto como 'pendiente'
        $stmt = $db->prepare("INSERT INTO transacciones (id_comprador, id_vendedor, id_oferta, estado) VALUES (?, ?, ?, 'pendiente')");
        
        // Ejecutar la consulta con los parámetros recibidos y retornar el resultado (true o false)
        return $stmt->execute([$id_comprador, $id_vendedor, $id_oferta]);
    }
}

?>
