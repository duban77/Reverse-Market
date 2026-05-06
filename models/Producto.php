<?php
// Incluir el archivo de conexión a la base de datos usando una ruta relativa
require_once __DIR__ . '/../config/Database.php';

// Definición de la clase Producto
class Producto {

    // Método estático para agregar un nuevo producto a la base de datos
    public static function agregar($db, $id_vendedor, $nombre, $descripcion, $precio, $categoria) {
        // Preparar la consulta SQL para insertar un nuevo producto
        $stmt = $db->prepare("INSERT INTO productos (id_vendedor, nombre, descripcion, precio, categoria) VALUES (?, ?, ?, ?, ?)");
        // Ejecutar la consulta con los valores proporcionados
        return $stmt->execute([$id_vendedor, $nombre, $descripcion, $precio, $categoria]);
    }

    // Método estático para listar todos los productos de un vendedor específico
    public static function listarPorVendedor($db, $id_vendedor) {
        // Preparar la consulta SQL para seleccionar productos por ID de vendedor
        $stmt = $db->prepare("SELECT * FROM productos WHERE id_vendedor = ?");
        // Ejecutar la consulta pasando el ID del vendedor
        $stmt->execute([$id_vendedor]);
        // Retornar los resultados como un arreglo asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
