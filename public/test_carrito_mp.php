<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    die("Necesitas iniciar sesión primero como comprador.");
}

$id = $_SESSION['usuario_id'];
$MP_ACCESS_TOKEN = 'APP_USR-4965124325488573-050119-7761f9174ed848e2119c03b58700e3d1-546609103';

// Build a test preference
$referencia = 'TEST-' . $id . '-' . time();
$ngrok_base = 'https://implode-fastness-raven.ngrok-free.dev';

$preference = [
    "items" => [[
        "id"          => "test-1",
        "title"       => "Producto de prueba",
        "quantity"    => 1,
        "currency_id" => "COP",
        "unit_price"  => 5000.00,
    ]],
    "payer" => ["email" => $_SESSION['correo'] ?? 'test@test.com'],
    "back_urls" => [
        "success" => "$ngrok_base/Proyecto_Reverse_Market/public/views/carrito_exitoso.php?status=approved&ref=$referencia",
        "failure" => "$ngrok_base/Proyecto_Reverse_Market/public/views/carrito.php",
        "pending" => "$ngrok_base/Proyecto_Reverse_Market/public/views/carrito_exitoso.php?status=pending&ref=$referencia",
    ],
    "auto_return"        => "approved",
    "external_reference" => $referencia,
];

$body = json_encode($preference);
$ch = curl_init('https://api.mercadopago.com/checkout/preferences');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $MP_ACCESS_TOKEN,
    ],
    CURLOPT_POSTFIELDS     => $body,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 20,
]);
$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

$data = json_decode($response, true);

echo "<h2>Test MercadoPago Carrito</h2>";
echo "<p>HTTP Code: <strong>$http_code</strong></p>";
if ($curl_err) echo "<p style='color:red'>cURL Error: $curl_err</p>";
if ($http_code === 201 && isset($data['init_point'])) {
    echo "<p style='color:green'>✅ ¡Preferencia creada!</p>";
    echo "<p><a href='{$data['init_point']}' target='_blank' style='background:#009ee3;color:white;padding:10px 20px;border-radius:8px;text-decoration:none'>🛒 Ir a MercadoPago →</a></p>";
    echo "<p style='font-size:12px'>URL: " . htmlspecialchars($data['init_point']) . "</p>";
} else {
    echo "<p style='color:red'>❌ Error:</p>";
    echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
    echo "<p>Request body:</p><pre>" . htmlspecialchars($body) . "</pre>";
}
?>

<?php
// Show what items are in cart
if (isset($_SESSION['usuario_id'])) {
    try {
        require_once __DIR__ . '/../config/db.php';
        $items = $pdo->prepare("SELECT c.cantidad, p.nombre, p.precio FROM carrito c JOIN productos p ON c.id_producto=p.id WHERE c.id_comprador=?");
        $items->execute([$_SESSION['usuario_id']]);
        $items = $items->fetchAll();
        echo "<h3>Items en tu carrito:</h3>";
        foreach ($items as $i) {
            $precio_iva = max(100, (int)round($i['precio'] * 1.19));
            echo "<p>• {$i['nombre']} × {$i['cantidad']} — Precio BD: \${$i['precio']} — Con IVA (entero): \${$precio_iva}</p>";
        }
    } catch(Exception $e) { echo "<p>Error: " . $e->getMessage() . "</p>"; }
}
?>
