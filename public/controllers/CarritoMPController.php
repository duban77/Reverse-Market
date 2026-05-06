<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') {
    echo json_encode(['error' => 'No autorizado']); exit;
}
$id = $_SESSION['usuario_id'];

// MP Credentials
$MP_ACCESS_TOKEN = 'APP_USR-4965124325488573-050119-7761f9174ed848e2119c03b58700e3d1-546609103';

// Handle POST: manual confirmation from localhost flow
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'confirmar_mp') {
        $ref   = trim($_POST['referencia'] ?? '');
        $comp  = trim($_POST['comprobante'] ?? '');
        $datos = $_SESSION['carrito_pago'] ?? null;
        if (!$datos) { echo json_encode(['error'=>'Sesión expirada']); exit; }
        try {
            $pdo->beginTransaction();
            foreach ($datos['items'] as $item) {
                $monto = round($item['precio'] * $item['cantidad'] * 1.19, 2);
                $pdo->prepare("INSERT INTO transacciones (id_comprador,id_vendedor,producto_id,monto,estado) VALUES (?,?,?,?,'completada')")
                    ->execute([$id, $item['id_vendedor'], $item['pid'], $monto]);
                try { $pdo->prepare("INSERT INTO notificaciones (mensaje,id_usuario_destino,tipo) VALUES (?,?,'oferta')")
                    ->execute(["✅ Pago MP: {$_SESSION['nombre']} compró "{$item['nombre']}" — Ref: $comp", $item['id_vendedor']]); } catch(PDOException $ignored){}
            }
            $pdo->prepare("DELETE FROM carrito WHERE id_comprador=?")->execute([$id]);
            $pdo->commit();
            unset($_SESSION['carrito_pago']);
            echo json_encode(['ok'=>true]);
        } catch(PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['error'=>$e->getMessage()]);
        }
        exit;
    }
}

// Auto-create carrito table
try { $pdo->exec("CREATE TABLE IF NOT EXISTS carrito (id INT AUTO_INCREMENT PRIMARY KEY, id_comprador INT NOT NULL, id_producto INT NOT NULL, cantidad INT NOT NULL DEFAULT 1, fecha DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY unique_item (id_comprador, id_producto))"); } catch(PDOException $e){}

// Get cart items
try {
    $items = $pdo->prepare("
        SELECT c.cantidad, p.id AS pid, p.nombre, p.precio, p.descripcion, p.id_vendedor,
               u.nombre AS vendedor
        FROM carrito c
        JOIN productos p ON c.id_producto = p.id
        JOIN usuarios u ON p.id_vendedor = u.id
        WHERE c.id_comprador = ?
    ");
    $items->execute([$id]);
    $items = $items->fetchAll();
} catch(PDOException $e) {
    echo json_encode(['error' => 'Error al leer el carrito: ' . $e->getMessage()]); exit;
}

if (empty($items)) {
    echo json_encode(['error' => 'El carrito está vacío']); exit;
}

// Calculate totals
$subtotal = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $items));
$iva      = round($subtotal * 0.19, 2);
$total    = $subtotal + $iva;

$referencia = 'CART-' . $id . '-' . time();

// Store cart reference in session for when MP returns
$_SESSION['carrito_pago'] = [
    'referencia' => $referencia,
    'items'      => $items,
    'subtotal'   => $subtotal,
    'iva'        => $iva,
    'total'      => $total,
    'comprador'  => $_SESSION['nombre'],
];

// Build base URL dynamically
$protocolo = isset($_SERVER['HTTPS']) ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'];
$carpeta   = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
$base_url  = "$protocolo://$host$carpeta";

// Build MP items array
$mp_items = [];
foreach ($items as $item) {
    $mp_items[] = [
        "id"          => "prod-" . $item['pid'],
        "title"       => substr($item['nombre'], 0, 256),
        "description" => "Vendedor: " . $item['vendedor'],
        "quantity"    => (int)$item['cantidad'],
        "currency_id" => "COP",
        "unit_price"  => (float)round($item['precio'] * 1.19, 2), // Price with IVA per item
    ];
}

// Use ngrok URL for MP redirects (works from localhost)
$ngrok_base = 'https://implode-fastness-raven.ngrok-free.dev';
$is_localhost = in_array($host, ['localhost', '127.0.0.1']) || str_starts_with($host, '192.168.');
$redirect_base = $is_localhost ? $ngrok_base : $base_url;
$is_localhost = false; // Always use back_urls with ngrok

$preference = [
    "items"       => $mp_items,
    "payer"       => [
        "email" => $_SESSION['correo'] ?? 'comprador@test.com',
        "name"  => explode(' ', $_SESSION['nombre'] ?? 'Comprador')[0],
    ],
    "back_urls" => [
        "success" => "$redirect_base/Proyecto_Reverse_Market/public/views/carrito_exitoso.php?status=approved&ref=$referencia",
        "failure" => "$redirect_base/Proyecto_Reverse_Market/public/views/carrito.php?error=pago_fallido",
        "pending" => "$redirect_base/Proyecto_Reverse_Market/public/views/carrito_exitoso.php?status=pending&ref=$referencia",
    ],
    "auto_return"        => "approved",
    "external_reference" => $referencia,
    "statement_descriptor" => "REVERSE MARKET",
];

// Call MP API - try curl first, fallback to file_get_contents
$body     = json_encode($preference);
$headers  = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $MP_ACCESS_TOKEN,
    'Content-Length: ' . strlen($body),
];
$response  = null;
$http_code = 0;
$conn_error = null;

if (function_exists('curl_init')) {
    $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $response   = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $conn_error = curl_error($ch);
    curl_close($ch);
}

// Fallback: file_get_contents
if (!$response || $conn_error) {
    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => implode("\r\n", $headers),
            'content' => $body,
            'timeout' => 20,
            'ignore_errors' => true,
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ],
    ]);
    $response  = @file_get_contents('https://api.mercadopago.com/checkout/preferences', false, $context);
    $http_code = 0;
    if ($response !== false) {
        foreach ($http_response_header ?? [] as $h) {
            if (preg_match('/HTTP\/\d+\.\d+\s+(\d+)/', $h, $m)) {
                $http_code = (int)$m[1];
            }
        }
    } else {
        echo json_encode(['error' => 'No se pudo conectar con MercadoPago. Verifica tu conexión a internet y que cURL esté habilitado en XAMPP.']); exit;
    }
}

$data = json_decode($response, true);

if ($http_code === 201 && isset($data['init_point'])) {
    echo json_encode([
        'ok'          => true,
        'init_point'  => $data['init_point'],
        'referencia'  => $referencia,
        'total'       => $total,
        'items_count' => count($items),
        'is_localhost' => false,
    ]);
} else {
    $msg = $data['message'] ?? $data['error'] ?? "Error HTTP $http_code";
    echo json_encode(['error' => 'MercadoPago: ' . $msg, 'detail' => $data]);
}
