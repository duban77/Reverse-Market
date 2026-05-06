<?php
// Incluye el archivo de configuración de la base de datos para establecer conexión mediante PDO.
require_once __DIR__ . '/../../config/Database.php';

// Incluye el modelo Notificacion que contiene la lógica relacionada con las notificaciones.
require_once __DIR__ . '/../models/notificacion.php';

// Verifica si aún no se ha iniciado una sesión.
if (session_status() == PHP_SESSION_NONE) {
    // Si no hay sesión activa, se inicia una nueva sesión.
    session_start();
}

// Se define la clase NotificacionController, encargada de manejar la lógica de notificaciones.
class NotificacionController {
    // Propiedad privada que almacenará la conexión a la base de datos.
    private $db;

    // Constructor de la clase, se ejecuta al instanciar el controlador.
    public function __construct() {
        // Se establece la conexión a la base de datos usando el método estático connect() de la clase Database.
        $this->db = Database::connect();
    }

    // Método que obtiene las notificaciones del usuario actual.
    public function obtenerNotificaciones() {
        // Obtiene el ID del usuario desde la sesión.
        $id_usuario = $_SESSION['usuario_id'];

        // Llama al método estático del modelo Notificacion para obtener las notificaciones desde la base de datos.
        return Notificacion::obtenerNotificaciones($this->db, $id_usuario);
    }
}

// Si la solicitud al archivo es por método GET, se ejecuta el bloque siguiente.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Se crea una instancia del controlador de notificaciones.
    $controller = new NotificacionController();

    // Se obtienen las notificaciones del usuario actual.
    $notificaciones = $controller->obtenerNotificaciones();

    // Se indica que la respuesta será en formato JSON.
    header('Content-Type: application/json');

    // Se imprime la respuesta como un JSON codificado.
    echo json_encode($notificaciones);

    // Finaliza la ejecución del script.
    exit;
}
?>
