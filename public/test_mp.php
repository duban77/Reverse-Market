<?php
// Test MercadoPago connection
$token = 'APP_USR-4965124325488573-050119-7761f9174ed848e2119c03b58700e3d1-546609103';

echo "<h2>Diagnóstico MercadoPago</h2>";

// Test 1: curl available?
echo "<p>✅ PHP version: " . PHP_VERSION . "</p>";
echo "<p>" . (function_exists('curl_init') ? "✅ cURL disponible" : "❌ cURL NO disponible — necesitas habilitarlo en php.ini") . "</p>";

// Test 2: file_get_contents with stream context (alternative to curl)
$context = stream_context_create([
    'http' => [
        'method'  => 'GET',
        'header'  => "Authorization: Bearer $token\r\nContent-Type: application/json\r\n",
        'timeout' => 10,
    ],
    'ssl' => ['verify_peer' => false],
]);
$result = @file_get_contents('https://api.mercadopago.com/v1/payment_methods', false, $context);
echo "<p>" . ($result ? "✅ file_get_contents funciona con HTTPS" : "❌ file_get_contents falló") . "</p>";

if ($result) {
    $data = json_decode($result, true);
    echo "<p>✅ Conexión a MP exitosa — métodos de pago disponibles: " . count($data) . "</p>";
}

// Test 3: curl test if available
if (function_exists('curl_init')) {
    $ch = curl_init('https://api.mercadopago.com/v1/payment_methods');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Authorization: Bearer $token"],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10,
    ]);
    $r = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    echo "<p>" . ($r && $code === 200 ? "✅ cURL funciona — HTTP $code" : "❌ cURL error: $err (HTTP $code)") . "</p>";
}
?>
