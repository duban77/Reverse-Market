<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') {
    header("Location: login.php"); exit;
}
if (!isset($_SESSION['pago_oferta'])) {
    header("Location: ofertas_recibidas.php"); exit;
}

$id   = $_SESSION['usuario_id'];
$pago = $_SESSION['pago_oferta'];

$monto      = (float)$pago['monto'];
$iva        = round($monto * 0.19);
$total      = (int)round($monto + $iva);
$total      = max(100, $total);
$referencia = 'OFR-' . $id . '-' . time();

$MP_ACCESS_TOKEN = 'APP_USR-4965124325488573-050119-7761f9174ed848e2119c03b58700e3d1-546609103';
$NGROK_BASE      = 'https://implode-fastness-raven.ngrok-free.dev';

// Build MP preference and redirect
$preference = [
    "items" => [[
        "id"          => "oferta-" . ($pago['oferta_id'] ?? 1),
        "title"       => substr($pago['producto'], 0, 256),
        "description" => "Vendedor: " . ($pago['vendedor'] ?? ''),
        "quantity"    => 1,
        "currency_id" => "COP",
        "unit_price"  => $total,
    ]],
    "payer" => [
        "email" => $_SESSION['correo'] ?? "comprador@test.com",
        "name"  => explode(' ', $_SESSION['nombre'] ?? 'Comprador')[0],
    ],
    "back_urls" => [
        "success" => "$NGROK_BASE/Proyecto_Reverse_Market/public/views/pago_exitoso.php?status=approved&ref=$referencia",
        "failure" => "$NGROK_BASE/Proyecto_Reverse_Market/public/views/pago_oferta.php?error=1",
        "pending" => "$NGROK_BASE/Proyecto_Reverse_Market/public/views/pago_exitoso.php?status=pending&ref=$referencia",
    ],
    "auto_return"        => "approved",
    "external_reference" => $referencia,
    "statement_descriptor" => "REVERSE MKT",
];

$_SESSION['pago_referencia'] = $referencia;
$_SESSION['pago_datos']      = $pago;

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
curl_close($ch);

$data = json_decode($response, true);

if ($http_code === 201 && !empty($data['init_point'])) {
    // ✅ Redirect to MercadoPago
    header("Location: " . $data['init_point']);
    exit;
}

// Error: show it
$mp_error = $data['message'] ?? $data['error'] ?? "Error HTTP $http_code";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Error de Pago — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_comprador.php'; ?>
<div class="main-content">
  <div style="max-width:540px;margin:3rem auto;text-align:center">
    <div style="font-size:3rem;margin-bottom:1rem">⚠️</div>
    <h2 style="font-family:var(--font-display);font-weight:800;margin-bottom:.75rem;color:var(--danger)">Error al conectar con MercadoPago</h2>
    <p style="color:var(--text-muted);margin-bottom:1.5rem"><?= htmlspecialchars($mp_error) ?></p>

    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;text-align:left;margin-bottom:1.5rem">
      <div style="font-size:.78rem;font-weight:700;color:var(--text-dim);margin-bottom:.75rem">Detalles del pago pendiente</div>
      <div style="font-size:.9rem;margin-bottom:.35rem">Producto: <strong><?= htmlspecialchars($pago['producto']) ?></strong></div>
      <div style="font-size:.9rem;margin-bottom:.35rem">Monto: <strong style="color:var(--accent)">$<?= number_format($total,0,',','.') ?> COP</strong></div>
    </div>

    <!-- Alternative payment methods -->
    <p style="color:var(--text-muted);font-size:.88rem;margin-bottom:1rem">Puedes pagar directamente con el vendedor:</p>
    <div style="display:grid;gap:.65rem">
      <?php foreach (['nequi'=>['📱','Nequi'],'daviplata'=>['💜','Daviplata'],'transferencia'=>['🏦','Transferencia bancaria'],'efectivo'=>['💵','Efectivo']] as $m=>[$icon,$label]): ?>
      <form method="POST" action="../controllers/PagoController.php">
        <input type="hidden" name="metodo" value="<?= $m ?>">
        <button type="submit" style="width:100%;padding:.8rem 1.25rem;background:var(--bg-card);border:1px solid var(--border);border-radius:10px;color:var(--text-primary);cursor:pointer;font-family:inherit;font-size:.9rem;display:flex;align-items:center;gap:.75rem;transition:all .2s" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
          <span style="font-size:1.3rem"><?= $icon ?></span> <?= $label ?>
        </button>
      </form>
      <?php endforeach; ?>
    </div>

    <a href="ofertas_recibidas.php" class="btn btn-outline" style="margin-top:1.5rem;display:inline-block">← Volver a mis ofertas</a>
  </div>
</div>
</div>
</body>
</html>
