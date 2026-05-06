<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') {
    header("Location: ../views/login.php"); exit;
}
$id = $_SESSION['usuario_id'];

$MP_ACCESS_TOKEN = 'APP_USR-4965124325488573-050119-7761f9174ed848e2119c03b58700e3d1-546609103';
$NGROK_BASE      = 'https://implode-fastness-raven.ngrok-free.dev';

// Auto-create carrito table
try { $pdo->exec("CREATE TABLE IF NOT EXISTS carrito (id INT AUTO_INCREMENT PRIMARY KEY, id_comprador INT NOT NULL, id_producto INT NOT NULL, cantidad INT NOT NULL DEFAULT 1, fecha DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY unique_item (id_comprador, id_producto))"); } catch(PDOException $e){}

// Get cart items
try {
    $stmt = $pdo->prepare("
        SELECT c.cantidad, p.id AS pid, p.nombre, p.precio, p.id_vendedor, u.nombre AS vendedor
        FROM carrito c
        JOIN productos p ON c.id_producto = p.id
        JOIN usuarios u ON p.id_vendedor = u.id
        WHERE c.id_comprador = ?
    ");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['flash_error'] = 'Error al leer el carrito.';
    header("Location: ../views/carrito.php"); exit;
}

if (empty($items)) {
    $_SESSION['flash_error'] = 'Tu carrito está vacío.';
    header("Location: ../views/carrito.php"); exit;
}

// Build MP items
$mp_items = [];
$subtotal = 0;
foreach ($items as $item) {
    // MP Colombia requires integer >= 100 COP
    $precio_base    = max(100, (float)$item['precio']);
    $precio_con_iva = (int)round($precio_base * 1.19);
    $precio_con_iva = max(100, $precio_con_iva); // minimum 100 COP
    
    $mp_items[] = [
        "id"          => "prod-" . $item['pid'],
        "title"       => substr($item['nombre'], 0, 256),
        "description" => "Vendedor: " . $item['vendedor'],
        "quantity"    => (int)$item['cantidad'],
        "currency_id" => "COP",
        "unit_price"  => $precio_con_iva,
    ];
    $subtotal += $item['precio'] * $item['cantidad'];
}
$total = max(100, (int)round($subtotal * 1.19));
// $total already calculated above
$referencia = 'CART-' . $id . '-' . time();

// Always use ngrok base for MP redirects (works for localhost + production)
$redirect_base = $NGROK_BASE; // 'https://implode-fastness-raven.ngrok-free.dev'

// Save to session for when MP returns
$_SESSION['carrito_pago'] = [
    'referencia' => $referencia,
    'items'      => $items,
    'subtotal'   => $subtotal,
    'total'      => $total,
];

// Build preference
$preference = [
    "items"       => $mp_items,
    "payer"       => [
        "email" => $_SESSION['correo'] ?? 'comprador@test.com',
        "name"  => explode(' ', $_SESSION['nombre'] ?? 'Comprador')[0],
    ],
    "back_urls"   => [
        "success" => "$redirect_base/Proyecto_Reverse_Market/public/views/carrito_exitoso.php?status=approved&ref=$referencia",
        "failure" => "$redirect_base/Proyecto_Reverse_Market/public/views/carrito.php?error=pago_fallido",
        "pending" => "$redirect_base/Proyecto_Reverse_Market/public/views/carrito_exitoso.php?status=pending&ref=$referencia",
    ],
    "auto_return"        => "approved",
    "external_reference" => $referencia,
    "statement_descriptor" => "REVERSE MKT",
];

// Call MP API
$body = json_encode($preference);
$ch   = curl_init('https://api.mercadopago.com/checkout/preferences');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $MP_ACCESS_TOKEN,
    ],
    CURLOPT_POSTFIELDS     => $body,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT        => 20,
]);
$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

// Fallback with file_get_contents
if (!$response || $curl_err) {
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\nAuthorization: Bearer $MP_ACCESS_TOKEN",
            'content' => $body,
            'timeout' => 20,
            'ignore_errors' => true,
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    $response = @file_get_contents('https://api.mercadopago.com/checkout/preferences', false, $ctx);
    if ($response) {
        foreach ($http_response_header ?? [] as $h) {
            if (preg_match('/HTTP\/\d+\.\d+\s+(\d+)/', $h, $m)) $http_code = (int)$m[1];
        }
    }
}

$data = json_decode($response, true);

if ($http_code === 201 && isset($data['init_point'])) {
    // ✅ Redirect directly to MercadoPago
    header("Location: " . $data['init_point']);
    exit;
} else {
    // Show error
    $error_msg = $data['message'] ?? $data['error'] ?? "Error HTTP $http_code";
    $_SESSION['flash_error'] = "Error al conectar con MercadoPago: $error_msg";
    header("Location: ../views/carrito.php");
    exit;
}
