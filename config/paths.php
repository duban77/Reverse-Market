<?php
/**
 * Rutas base del proyecto - Compatible con XAMPP
 * Detecta automáticamente la ruta del proyecto
 */

// Detectar el subfolder del proyecto en XAMPP
// Ej: http://localhost/reverse_market_mejorado/public/index.php
//     → BASE_URL = /reverse_market_mejorado
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
// Subir hasta encontrar el directorio raíz del proyecto
$parts = explode('/', trim($scriptPath, '/'));
// El primer segmento después de / es el nombre del proyecto en XAMPP
$projectRoot = '/' . $parts[0];

// Si estamos dentro de /public/ o /views/users/, subir correctamente
// Detectar si el proyecto está en la raíz o en subdirectorio
if (isset($_SERVER['DOCUMENT_ROOT'])) {
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $projDir = str_replace('\\', '/', dirname(dirname(__FILE__))); // config/ → project root
    $relative = str_replace($docRoot, '', $projDir);
    define('BASE_URL', rtrim($relative, '/'));
} else {
    define('BASE_URL', '');
}

define('CSS_PATH',    BASE_URL . '/public/css/styles.css');
define('PUBLIC_PATH', BASE_URL . '/public');
