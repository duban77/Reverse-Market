<?php 
session_start(); // Inicia la sesión para acceder a las variables de sesión
require_once '../config/Database.php'; // Incluye la configuración de la base de datos

// Verifica que el usuario esté logueado y sea vendedor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'vendedor') {
    echo "acceso_denegado"; // Mensaje si no tiene permiso
    exit;
}

// Obtiene datos del formulario, con valores por defecto en caso de no existir
$metodo = $_POST['metodo_pago'] ?? '';
$cuenta = $_POST['numero_cuenta'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$id_vendedor = $_SESSION['user_id'] ?? 0; // ID del vendedor desde sesión

// Valida que el método no esté vacío
if ($metodo === '') {
    echo "faltan_datos"; // Indica que faltan datos obligatorios
    exit;
}

// Si el método es "Efectivo", no se necesitan número de cuenta ni descripción
if ($metodo === 'Efectivo') {
    $cuenta = null;
    $descripcion = null;
}

try {
    $db = Database::connect(); // Conecta a la base de datos
    // Prepara la consulta para insertar el medio de pago
    $stmt = $db->prepare("INSERT INTO medios_pago (id_vendedor, metodo_pago, numero_cuenta, descripcion) VALUES (?, ?, ?, ?)");
    // Ejecuta la consulta con los datos recibidos
    $stmt->execute([$id_vendedor, $metodo, $cuenta, $descripcion]);

    echo "ok"; // Indica éxito
} catch (Exception $e) {
    // En caso de error, muestra mensaje con detalle
    echo "error: " . $e->getMessage();
}
?>
