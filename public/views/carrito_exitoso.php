<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') {
    header("Location: login.php"); exit;
}

$id     = $_SESSION['usuario_id'];
$status = $_GET['status'] ?? 'failure';
$ref    = $_GET['reference'] ?? $_GET['ref'] ?? '';
$mp_id  = $_GET['payment_id'] ?? $_GET['collection_id'] ?? '';
$datos  = $_SESSION['carrito_pago'] ?? null;

if (!$datos) {
    header("Location: carrito.php"); exit;
}

$transacciones = [];
$total_pagado  = 0;

if (in_array($status, ['approved', 'pending'])) {
    $estado_tx = $status === 'approved' ? 'completada' : 'pendiente';

    try {
        $pdo->beginTransaction();

        foreach ($datos['items'] as $item) {
            $subtotal_item = round($item['precio'] * $item['cantidad'] * 1.19, 2);
            $pdo->prepare("INSERT INTO transacciones (id_comprador, id_vendedor, producto_id, monto, estado)
                           VALUES (?,?,?,?,?)")
                ->execute([$id, $item['id_vendedor'], $item['pid'], $subtotal_item, $estado_tx]);

            // Notify each vendor
            try {
                $pdo->prepare("INSERT INTO notificaciones (mensaje, id_usuario_destino, tipo) VALUES (?,?,'oferta')")
                    ->execute([
                        "🛒 {$_SESSION['nombre']} compró \"{$item['nombre']}\" x{$item['cantidad']} — $"
                        . number_format($subtotal_item, 2) . " COP",
                        $item['id_vendedor']
                    ]);
            } catch(PDOException $ignored){}

            $transacciones[] = [
                'nombre'    => $item['nombre'],
                'cantidad'  => $item['cantidad'],
                'precio'    => $item['precio'],
                'vendedor'  => $item['vendedor'],
                'subtotal'  => $subtotal_item,
            ];
            $total_pagado += $subtotal_item;
        }

        // Clear cart
        $pdo->prepare("DELETE FROM carrito WHERE id_comprador = ?")->execute([$id]);

        $pdo->commit();

    } catch(PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['flash_error'] = 'Error al registrar: ' . $e->getMessage();
        header("Location: carrito.php"); exit;
    }
}

unset($_SESSION['carrito_pago']);
$es_pendiente = $status === 'pending';
$es_exitoso   = $status === 'approved';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $es_exitoso ? '¡Compra exitosa!' : 'Pago registrado' ?> — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
<style>
.wrap{max-width:580px;margin:2rem auto;text-align:center}
.icon-circle{width:90px;height:90px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2.5rem;margin:0 auto 1.5rem;animation:pop .5s cubic-bezier(.175,.885,.32,1.275)}
@keyframes pop{0%{transform:scale(.3);opacity:0}70%{transform:scale(1.12)}100%{transform:scale(1);opacity:1}}
.receipt{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.75rem;text-align:left;margin:1.5rem 0}
.r-row{display:flex;justify-content:space-between;align-items:center;padding:.55rem 0;border-bottom:1px solid rgba(0,255,200,.05);font-size:.88rem;gap:.5rem}
.r-row:last-child{border-bottom:none}
.item-row{display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid rgba(0,255,200,.04);font-size:.86rem}
.item-row:last-child{border-bottom:none}
.tx-code{font-family:monospace;background:var(--bg-panel);border:1px solid var(--border);padding:.3rem .75rem;border-radius:8px;font-size:.78rem;color:var(--accent)}
</style>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_comprador.php'; ?>
<div class="main-content">
  <div class="wrap">

    <div class="icon-circle" style="background:<?= $es_exitoso?'rgba(6,214,160,.12)':'rgba(255,190,11,.12)' ?>;border:2px solid <?= $es_exitoso?'var(--success)':'var(--warning)' ?>">
      <?= $es_exitoso ? '✅' : '⏳' ?>
    </div>

    <h1 style="font-family:var(--font-display);font-size:1.75rem;font-weight:800;margin-bottom:.5rem;color:<?= $es_exitoso?'var(--success)':'var(--warning)' ?>">
      <?= $es_exitoso ? '¡Compra realizada!' : 'Pago en proceso' ?>
    </h1>
    <p style="color:var(--text-muted);font-size:.92rem;line-height:1.65;margin-bottom:0">
      <?= $es_exitoso
        ? 'Tu pago fue procesado por MercadoPago. Los vendedores han sido notificados.'
        : 'Tu pago está siendo confirmado por MercadoPago. Te notificaremos cuando sea aprobado.' ?>
    </p>

    <?php if ($es_exitoso || $es_pendiente): ?>
    <div class="receipt">
      <div style="font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-dim);margin-bottom:1rem">Comprobante de compra</div>

      <?php if ($mp_id): ?>
      <div class="r-row"><span style="color:var(--text-muted)">ID MercadoPago</span><span class="tx-code"><?= htmlspecialchars($mp_id) ?></span></div>
      <?php endif; ?>
      <div class="r-row"><span style="color:var(--text-muted)">Referencia</span><span style="font-size:.8rem;color:var(--text-muted)"><?= htmlspecialchars($ref) ?></span></div>
      <div class="r-row"><span style="color:var(--text-muted)">Método</span><span>MercadoPago</span></div>
      <div class="r-row"><span style="color:var(--text-muted)">Fecha</span><span><?= date('d M Y, H:i') ?></span></div>
      <div class="r-row"><span style="color:var(--text-muted)">Estado</span>
        <span style="font-weight:700;color:<?= $es_exitoso?'var(--success)':'var(--warning)' ?>">
          <?= $es_exitoso ? '✅ Completado' : '⏳ Pendiente' ?>
        </span>
      </div>

      <!-- Items -->
      <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid rgba(0,255,200,.08)">
        <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-dim);margin-bottom:.75rem">Productos comprados</div>
        <?php foreach ($transacciones as $t): ?>
        <div class="item-row">
          <div>
            <div style="font-weight:600"><?= htmlspecialchars($t['nombre']) ?></div>
            <div style="font-size:.76rem;color:var(--text-muted)">por <?= htmlspecialchars($t['vendedor']) ?> · x<?= $t['cantidad'] ?></div>
          </div>
          <span style="font-weight:700;color:var(--accent);font-family:var(--font-display)">$<?= number_format($t['subtotal'],2) ?></span>
        </div>
        <?php endforeach; ?>
        <div style="display:flex;justify-content:space-between;padding-top:1rem;margin-top:.5rem;border-top:1px solid rgba(0,255,200,.1)">
          <span style="font-family:var(--font-display);font-weight:800">Total pagado</span>
          <span style="font-family:var(--font-display);font-weight:800;font-size:1.3rem;color:var(--accent)">$<?= number_format($total_pagado,2) ?> COP</span>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
      <a href="pagos.php" class="btn btn-primary" style="padding:.75rem 1.75rem">Ver mis pagos</a>
      <a href="calificaciones_comprador.php" class="btn btn-outline" style="padding:.75rem 1.5rem">⭐ Calificar</a>
      <a href="home_comprador.php" class="btn btn-outline" style="padding:.75rem 1.5rem">Seguir comprando</a>
    </div>

  </div>
</div>
</div>
</body>
</html>
