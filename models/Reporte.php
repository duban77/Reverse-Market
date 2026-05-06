<?php 
// Incluir archivo de configuración y conexión a la base de datos
require_once 'config/db.php';

// Definición de la clase Reporte
class Reporte {
    // Propiedad privada para la conexión a la base de datos
    private $conn;

    // Constructor de la clase que establece la conexión
    public function __construct() {
        // Llama al método estático conectar() de la clase Database
        $this->conn = Database::conectar();
    }

    // Método para crear un nuevo reporte
    public function crearReporte($id_usuario, $id_producto, $motivo) {
        // Consulta SQL para insertar un nuevo registro en la tabla 'reportes'
        $sql = "INSERT INTO reportes (id_usuario, id_producto, motivo) VALUES (?, ?, ?)";
        // Preparar la consulta para prevenir inyección SQL
        $stmt = $this->conn->prepare($sql);
        // Ejecutar la consulta con los parámetros recibidos
        return $stmt->execute([$id_usuario, $id_producto, $motivo]);
    }

    // Método para obtener todos los reportes realizados por un usuario específico
    public function obtenerReportesPorUsuario($id_usuario) {
        // Consulta SQL que une la tabla 'reportes' con 'productos' para obtener el nombre del producto
        $sql = "SELECT r.*, p.nombre AS producto 
                FROM reportes r 
                JOIN productos p ON r.id_producto = p.id 
                WHERE r.id_usuario = ?";
        // Preparar la consulta
        $stmt = $this->conn->prepare($sql);
        // Ejecutar la consulta pasando el ID del usuario
        $stmt->execute([$id_usuario]);
        // Retornar todos los resultados como arreglo asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
