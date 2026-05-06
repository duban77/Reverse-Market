<?php if(session_status()===PHP_SESSION_NONE)session_start(); ?>
<?php
// Datos de conexión a la base de datos
$host = 'localhost';      // Servidor donde está alojada la base de datos
$db   = 'reverse_market'; // Nombre de la base de datos
$user = 'root';           // Usuario para la conexión
$pass = '';               // Contraseña para la conexión (vacía en este caso)
$charset = 'utf8mb4';     // Conjunto de caracteres para la conexión (soporta emojis y más)

// Data Source Name (DSN) para PDO, indica host, base de datos y charset
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opciones para la conexión PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Reporta errores como excepciones
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve los resultados como array asociativo por defecto
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Desactiva la emulación de prepared statements, para seguridad y eficiencia
];

try {
    // Crear la conexión PDO usando DSN, usuario, contraseña y opciones
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Si la conexión es exitosa, mostrar mensaje
    echo "Conexión exitosa a la base de datos.";
} catch (PDOException $e) {
    // En caso de error en la conexión, detener el script y mostrar el mensaje de error
    die('Error de conexión a la base de datos: ' . $e->getMessage());
}
?>
