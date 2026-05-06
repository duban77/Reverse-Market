<?php
// Incluye el modelo Producto, aunque en este archivo no se usa directamente.
// Podría estar incluido por si el perfil tiene relación con productos del vendedor.
require_once __DIR__ . '/../models/Producto.php';

// Incluye el archivo de configuración de base de datos para poder conectarse mediante PDO.
require_once __DIR__ . '/../../config/Database.php';

// Verifica si la sesión aún no ha sido iniciada.
if (session_status() == PHP_SESSION_NONE) {
    // Si no hay sesión activa, se inicia una nueva.
    session_start();
}

// Se define la clase PerfilController, que maneja la creación de perfiles de vendedores.
class PerfilController {
    // Propiedad privada donde se almacenará la conexión a la base de datos.
    private $db;

    // Constructor que se ejecuta al instanciar la clase.
    public function __construct() {
        // Conexión a la base de datos usando el método estático de la clase Database.
        $this->db = Database::connect();
    }

    // Método público para crear un perfil.
    public function crearPerfil() {
        // Verifica que la solicitud se haya hecho con método POST.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtiene los datos enviados desde el formulario vía POST.
            $nombre = $_POST['nombre'];
            $telefono = $_POST['telefono'];
            $direccion = $_POST['direccion'];
            $descripcion = $_POST['descripcion'];
            $id_vendedor = $_POST['id_vendedor'];

            // Intenta ejecutar la inserción en la base de datos.
            try {
                // Prepara una sentencia SQL para insertar un nuevo perfil.
                $stmt = $this->db->prepare("INSERT INTO perfiles (id_vendedor, nombre, telefono, direccion, descripcion) VALUES (?, ?, ?, ?, ?)");

                // Ejecuta la sentencia con los datos proporcionados.
                $stmt->execute([$id_vendedor, $nombre, $telefono, $direccion, $descripcion]);

                // Si todo va bien, responde con un mensaje de éxito en formato JSON.
                echo json_encode(['success' => true, 'message' => 'Perfil creado exitosamente.']);
            } catch (Exception $e) {
                // Si hay algún error en la ejecución, responde con el mensaje del error.
                echo json_encode(['success' => false, 'message' => 'Error al crear el perfil: ' . $e->getMessage()]);
            }
        } else {
            // Si la solicitud no fue por POST, muestra un mensaje de método no permitido.
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
        }
    }
}

// Si este archivo se accede directamente con una solicitud POST:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se crea una instancia del controlador de perfil.
    $perfilController = new PerfilController();

    // Se ejecuta el método para crear el perfil.
    $perfilController->crearPerfil();
}
?>
