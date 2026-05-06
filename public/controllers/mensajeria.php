<?php
// Incluye el archivo de configuración de la base de datos para establecer la conexión mediante PDO.
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

// Verifica si la solicitud se realizó mediante el método POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene el ID del comprador enviado por POST desde el formulario o la solicitud AJAX.
    $buyerId = $_POST['buyer_id']; // ID del comprador

    // Obtiene el ID del vendedor desde la sesión activa.
    $sellerId = $_SESSION['usuario_id']; // ID del vendedor

    // Llama a la función initiateChat, pasándole la conexión PDO y los IDs del vendedor y comprador.
    initiateChat($pdo, $sellerId, $buyerId);
}
?>
