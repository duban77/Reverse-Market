<?php
date_default_timezone_set('America/Bogota');
$host    = 'localhost';
$db      = 'reverse_market';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ));
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:2rem;background:#1a0010;color:#ff4d6d;border:1px solid #ff4d6d;border-radius:8px;margin:2rem">
        <strong>Error de conexión a la base de datos</strong><br><br>
        Verifica que:<br>
        • MySQL esté activo en XAMPP<br>
        • La base de datos <code>reverse_market</code> exista (importa el archivo .sql)<br>
        • Las credenciales en <code>config/db.php</code> sean correctas<br><br>
        <em>Detalle técnico: ' . htmlspecialchars($e->getMessage()) . '</em>
    </div>');
}

require_once __DIR__ . '/helpers.php';

