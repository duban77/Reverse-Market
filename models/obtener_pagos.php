<?php
// Mostrar errores para depuración en el entorno de desarrollo
ini_set('display_errors', 1);                // Habilita la visualización de errores
ini_set('display_startup_errors', 1);        // Habilita errores al iniciar PHP
error_reporting(E_ALL);                      // Reporta todos los errores

// Iniciar sesión para acceder a variables de sesión
session_start();

// Incluir el archivo de conexión a la base de datos (nivel superior del directorio actual)
require_once '../config/Database.php';

// Verificar que el usuario esté autenticado y que su rol sea 'vendedor'
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'vendedor') {
    // Si no es vendedor o no está autenticado, se devuelve error en formato JSON
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

try {
    // Conectar con la base de datos
    $db = Database::connect();
    $id_vendedor = $_SESSION['user_id']; // Obtener el ID del vendedor desde la sesión

    // Preparar y ejecutar la consulta para obtener los métodos de pago del vendedor
    $stmt = $db->prepare("SELECT metodo_pago, numero_cuenta, descripcion FROM medios_pago WHERE id_vendedor = ?");
    $stmt->execute([$id_vendedor]);
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtener resultados como arreglo asociativo

    // Indicar que la respuesta es en formato JSON
    header('Content-Type: application/json');
    // Imprimir los resultados en formato JSON
    echo json_encode($pagos);
} catch (Exception $e) {
    // Capturar cualquier error y devolverlo como JSON
    echo json_encode(['error' => $e->getMessage()]);
}
?>
