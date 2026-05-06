<?php
// Incluye el modelo Necesidad, que probablemente contiene la lógica para interactuar con la tabla de necesidades.
require_once __DIR__ . '/../models/Necesidad.php';

// Incluye la clase Database para poder conectarse a la base de datos.
require_once __DIR__ . '/../../config/Database.php';

// Se define la clase NecesidadController que controlará la lógica para crear nuevas necesidades (publicaciones del comprador).
class NecesidadController {
    // Propiedad privada para almacenar la conexión a la base de datos.
    private $db;

    // Constructor que se ejecuta al crear una instancia de NecesidadController.
    public function __construct() {
        // Se conecta a la base de datos utilizando el método estático de la clase Database.
        $this->db = Database::connect();
    }

    // Método público para crear una nueva necesidad.
    public function crear() {
        // Inicia la sesión para poder acceder a los datos del usuario (como el ID).
        session_start();

        // Verifica que la solicitud fue enviada por método POST y que el usuario ha iniciado sesión.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_id'])) {
            // Obtiene el título desde el formulario o deja un string vacío si no existe.
            $titulo = $_POST['titulo'] ?? '';

            // Obtiene la descripción del formulario o deja un string vacío si no existe.
            $descripcion = $_POST['descripcion'] ?? '';

            // Obtiene el ID del comprador desde la sesión.
            $id_comprador = $_SESSION['usuario_id'];

            // Verifica que tanto el título como la descripción fueron proporcionados.
            if ($titulo && $descripcion) {
                // Llama al método estático crear del modelo Necesidad, pasándole la conexión y los datos.
                Necesidad::crear($this->db, $titulo, $descripcion, $id_comprador);

                // Redirige al usuario a la vista con un mensaje de éxito en la URL.
                header('Location: ../views/necesidades_comprador.php?status=ok');
                exit; // Termina la ejecución del script después de redirigir.
            } else {
                // Si faltan datos, muestra un mensaje de error simple.
                echo "Faltan datos.";
            }
        }
    }
}

// Esta parte permite que el controlador se ejecute directamente si el archivo no es llamado desde un router o framework.
$controller = new NecesidadController(); // Se crea una instancia del controlador.
$controller->crear(); // Se ejecuta el método crear() automáticamente.
