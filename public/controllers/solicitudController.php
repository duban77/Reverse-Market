<?php
// Incluye el modelo Solicitud para manejar las solicitudes
require_once __DIR__ . '/../models/Solicitud.php';
// Incluye el modelo Notificacion para manejar notificaciones
require_once __DIR__ . '/../models/notificacion.php';
// Incluye la configuración de la base de datos
require_once __DIR__ . '/../../config/Database.php';

class SolicitudController {
    private $db;

    // Constructor que conecta con la base de datos
    public function __construct() {
        $this->db = Database::connect();
    }

    // Método para crear una nueva solicitud
    public function crear() {
        // Verifica que la petición sea mediante método POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recibe datos enviados desde el formulario, usa null si no están
            $precio = $_POST['precio'] ?? null;
            $tiempo = $_POST['tiempo_entrega'] ?? null;
            $condiciones = $_POST['condiciones'] ?? null;
            $id_comprador = $_POST['id_comprador'] ?? null;
            $id_vendedor = $_POST['id_vendedor'] ?? null;

            // Validación básica para verificar que no falte ningún dato obligatorio
            if (!$precio || !$tiempo || !$condiciones || !$id_comprador || !$id_vendedor) {
                echo "Faltan datos obligatorios.";
                exit; // Detiene la ejecución si faltan datos
            }

            // Llama al método crear del modelo Solicitud para guardar la solicitud en DB
            Solicitud::crear($this->db, $precio, $tiempo, $condiciones, $id_comprador, $id_vendedor);

            // Envía una notificación al vendedor informándole que recibió una solicitud
            Notificacion::enviar($this->db, $id_vendedor, "Has recibido una nueva solicitud de un comprador.");

            // Redirige al comprador a su lista de solicitudes
            header('Location: /Proyecto_Reverse_Market/views/users/lista_solicitudes.php');
            exit;
        } else {
            // Si el método no es POST, muestra mensaje y termina la ejecución
            echo "Método no permitido.";
            exit;
        }
    }

    // Método para listar solicitudes por comprador (se usa para mostrar en vistas)
    public function listarPorComprador($id_comprador) {
        return Solicitud::listarPorComprador($this->db, $id_comprador);
    }
}

// Ejecuta automáticamente el método crear al acceder a este archivo
$solicitud = new SolicitudController();
$solicitud->crear();
