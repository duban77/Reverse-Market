<?php 
// Incluye el modelo Producto que contiene la lógica relacionada con productos.
require_once __DIR__ . '/../models/Producto.php';

// Incluye la configuración para la conexión a la base de datos.
require_once __DIR__ . '/../../config/Database.php';

// Verifica si la sesión no está iniciada.
if (session_status() == PHP_SESSION_NONE) {
    // Inicia la sesión si no está activa.
    session_start();
}

// Definición de la clase ProductoController que maneja operaciones relacionadas con productos.
class ProductoController {
    // Propiedad privada para almacenar la conexión a la base de datos.
    private $db;

    // Constructor que crea la conexión a la base de datos.
    public function __construct() {
        $this->db = Database::connect();
    }

    // Método para listar todos los productos.
    public function listarProductos() {
        // Prepara la consulta SQL para obtener todos los productos.
        $stmt = $this->db->prepare("SELECT * FROM productos");
        // Ejecuta la consulta.
        $stmt->execute();
        // Retorna todos los resultados como un arreglo asociativo.
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para buscar productos por nombre o descripción según una consulta.
    public function buscarProductos($query) {
        // Prepara la consulta con parámetros para búsqueda usando LIKE.
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE nombre LIKE ? OR descripcion LIKE ?");
        // Formatea la búsqueda con comodines % para coincidencia parcial.
        $searchTerm = "%$query%";
        // Ejecuta la consulta con los parámetros.
        $stmt->execute([$searchTerm, $searchTerm]);
        // Retorna los resultados como un arreglo asociativo.
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para agregar un producto nuevo con sus datos y manejo de imagen.
    public function agregarProducto($id_vendedor, $nombre, $descripcion, $precio, $categoria) {
        try {
            // Inicializa la variable para almacenar el nombre de la imagen.
            $nombreImagen = null;

            // Verifica si se ha enviado una imagen y no hay error en la carga.
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                // Define el directorio donde se guardarán las imágenes.
                $directorio = __DIR__ . '/../public/uploads/';

                // Si el directorio no existe, lo crea con permisos 0777.
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                // Genera un nombre único para la imagen usando uniqid para evitar colisiones.
                $nombreImagen = uniqid() . "_" . basename($_FILES['imagen']['name']);
                // Define la ruta destino completa para la imagen.
                $rutaDestino = $directorio . $nombreImagen;

                // Mueve la imagen desde la ubicación temporal al directorio destino.
                move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino);
            }

            // Prepara la sentencia SQL para insertar el nuevo producto.
            $stmt = $this->db->prepare("INSERT INTO productos (id_vendedor, nombre, descripcion, precio, categoria, imagen) VALUES (?, ?, ?, ?, ?, ?)");

            // Ejecuta la inserción con los datos proporcionados y el nombre de la imagen si existe.
            $stmt->execute([$id_vendedor, $nombre, $descripcion, $precio, $categoria, $nombreImagen]);

            // Obtiene el ID del producto recién insertado.
            $id_producto = $this->db->lastInsertId();

            // Verifica si el producto responde a una necesidad (ya sea por POST o por sesión).
            if (isset($_POST['id_necesidad_relacionada']) || isset($_SESSION['id_necesidad'])) {
                // Obtiene el id_necesidad desde POST o desde la sesión.
                $id_necesidad = $_POST['id_necesidad_relacionada'] ?? $_SESSION['id_necesidad'];

                // Prepara la inserción en la tabla intermedia que relaciona necesidades con productos.
                $stmtRelacion = $this->db->prepare("INSERT INTO respuesta_necesidad (id_necesidad, id_producto) VALUES (?, ?)");

                // Ejecuta la inserción de la relación.
                $stmtRelacion->execute([$id_necesidad, $id_producto]);

                // Elimina el id_necesidad guardado en sesión para evitar reutilización.
                unset($_SESSION['id_necesidad']);
            }

            // Retorna un arreglo indicando éxito.
            return ['success' => true, 'message' => 'Producto agregado exitosamente.'];
        } catch (Exception $e) {
            // Si ocurre un error, retorna el mensaje de error.
            return ['success' => false, 'message' => 'Error al agregar el producto: ' . $e->getMessage()];
        }
    }
}

// Si la solicitud es POST y se recibe el campo 'nombre', se procesa el formulario para agregar producto.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    // Obtiene el id del vendedor desde la sesión.
    $id_vendedor = $_SESSION['usuario_id'];
    // Obtiene los demás datos del producto enviados vía POST.
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $categoria = $_POST['categoria'];

    // Crea una instancia del controlador de productos.
    $productoController = new ProductoController();

    // Llama al método para agregar el producto con los datos proporcionados.
    $response = $productoController->agregarProducto($id_vendedor, $nombre, $descripcion, $precio, $categoria);

    // Verifica si la solicitud fue enviada por AJAX (cabecera HTTP específica).
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // Define que la respuesta será JSON.
        header('Content-Type: application/json');
        // Envía la respuesta codificada en JSON.
        echo json_encode($response);
    } else {
        // Si no es AJAX, redirige al usuario a la página principal de vendedores.
        header("Location: ../views/home_vendedor.php");
    }
    // Termina la ejecución del script.
    exit;
}

// Si la solicitud es GET y contiene el parámetro 'query', se procesa una búsqueda.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['query'])) {
    // Obtiene la cadena de búsqueda.
    $query = $_GET['query'];

    // Crea una instancia del controlador de productos.
    $productoController = new ProductoController();

    // Obtiene los productos que coinciden con la búsqueda.
    $productos = $productoController->buscarProductos($query);

    // Define que la respuesta será JSON.
    header('Content-Type: application/json');

    // Envía los productos encontrados en formato JSON.
    echo json_encode($productos);

    // Termina la ejecución del script.
    exit;
}

// Si la solicitud es POST y se envía el campo 'responder_necesidad'.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder_necesidad'])) {
    // Guarda en la sesión el id de la necesidad relacionada.
    $_SESSION['id_necesidad'] = $_POST['id_necesidad'];

    // Redirige a la página para agregar un producto.
    header('Location: /Proyecto_Reverse_Market/views/users/agregar_producto.php');

    // Termina la ejecución del script.
    exit;
}
