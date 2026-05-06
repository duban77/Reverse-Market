<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') {
    header("Location: login.php"); exit;
}

$id   = $_SESSION['usuario_id'];
$pago = null;

// ── From MercadoPago redirect ──
if (isset($_GET['status'])) {
    $status    = $_GET['status'];     // approved / pending / failure
    $ref       = $_GET['ref'] ?? '';
    $mp_id     = $_GET['payment_id'] ?? $_GET['collection_id'] ?? '';
    $pago_datos = $_SESSION['pago_datos'] ?? $_SESSION['pago_oferta'] ?? null;

    if (in_array($status, ['approved','pending']) && $pago_datos) {
        $monto = (float)$pago_datos['monto'];
        $total = $monto + round($monto * 0.19, 2);
        $estado_tx = $status === 'approved' ? 'completada' : 'pendiente';
        try {
            $pdo->beginTransaction();
            if (!empty($pago_datos['oferta_id'])) {
                try { $pdo->prepare("UPDATE oferta_necesidad SET estado='aceptada' WHERE id=? AND id_comprador=?")->execute([$pago_datos['oferta_id'],$id]); } catch(PDOException $e){}
            }
            $pdo->prepare("INSERT INTO transacciones (id_comprador,id_vendedor,producto_id,monto,estado) VALUES (?,?,?,?,?)")
                ->execute([$id,$pago_datos['id_vendedor'],$pago_datos['id_producto']??null,$total,$estado_tx]);
            $tx_id = $pdo->lastInsertId();
            try { $pdo->prepare("INSERT INTO notificaciones (mensaje,id_usuario_destino,tipo) VALUES (?,?,'oferta')")->execute(["✅ Pago MP recibido: \"{$pago_datos['producto']}\"",$pago_datos['id_vendedor']]); } catch(PDOException $ignored){}
            $pdo->commit();
            $pago = ['tx_id'=>$tx_id,'producto'=>$pago_datos['producto'],'vendedor'=>$pago_datos['vendedor']??'—','monto'=>$total,'metodo'=>'MercadoPago','estado'=>$estado_tx,'ref'=>$ref,'mp_id'=>$mp_id];
        } catch(PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
        }
    }
    unset($_SESSION['pago_referencia'],$_SESSION['pago_datos'],$_SESSION['pago_oferta']);
    if (!$pago) { $_SESSION['flash_error']='El pago no fue completado.'; header("Location: ofertas_recibidas.php"); exit; }
}

// ── From PagoController ──
if (!$pago && isset($_SESSION['pago_exito'])) {
    $pago = $_SESSION['pago_exito'];
    unset($_SESSION['pago_exito']);
}

if (!$pago) { header("Location: ofertas_recibidas.php"); exit; }
$es_pendiente = ($pago['estado'] ?? '') !== 'completada';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $es_pendiente?'Pago Registrado':'¡Pago Exitoso!' ?> — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
<style>
.success-wrap{max-width:520px;margin:2.5rem auto;text-align:center}
.icon-circle{width:88px;height:88px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2.5rem;margin:0 auto 1.5rem;animation:pop .5s cubic-bezier(.175,.885,.32,1.275)}
@keyframes pop{0%{transform:scale(.3);opacity:0}70%{transform:scale(1.12)}100%{transform:scale(1);opacity:1}}
.receipt{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.75rem;margin:1.5rem 0;text-align:left}
.r-row{display:flex;justify-content:space-between;align-items:center;padding:.55rem 0;border-bottom:1px solid rgba(0,255,200,.05);font-size:.88rem;gap:.5rem}
.r-row:last-child{border-bottom:none}
.r-label{color:var(--text-muted);flex-shrink:0}
.r-val{text-align:right;word-break:break-all}
.tx-code{font-family:monospace;background:var(--bg-panel);border:1px solid var(--border);padding:.3rem .75rem;border-radius:8px;font-size:.8rem;color:var(--accent)}
</style>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_comprador.php'; ?>
<div class="main-content">
  <div class="success-wrap">
    <div class="icon-circle" style="background:<?= $es_pendiente?'rgba(255,190,11,.12)':'rgba(6,214,160,.12)' ?>;border:2px solid <?= $es_pendiente?'var(--warning)':'var(--success)' ?>">
      <?= $es_pendiente ? '⏳' : '✅' ?>
    </div>
    <h1 style="font-family:var(--font-display);font-size:1.7rem;font-weight:800;margin-bottom:.5rem;color:<?= $es_pendiente?'var(--warning)':'var(--success)' ?>">
      <?= $es_pendiente ? 'Pago registrado' : '¡Pago exitoso!' ?>
    </h1>
    <p style="color:var(--text-muted);font-size:.92rem;line-height:1.65">
      <?php if ($es_pendiente): ?>
        El vendedor fue notificado. Coordina los detalles del pago directamente con él.
      <?php else: ?>
        Tu pago fue procesado correctamente. El vendedor ha sido notificado automáticamente.
      <?php endif; ?>
    </p>

    <div class="receipt">
      <div style="font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-dim);margin-bottom:1rem">Comprobante</div>
      <div class="r-row"><span class="r-label">N° Transacción</span><span class="tx-code r-val">#<?= str_pad($pago['tx_id'],8,'0',STR_PAD_LEFT) ?></span></div>
      <?php if (!empty($pago['mp_id'])): ?>
      <div class="r-row"><span class="r-label">ID MercadoPago</span><span class="r-val" style="font-size:.78rem;color:var(--text-muted)"><?= htmlspecialchars($pago['mp_id']) ?></span></div>
      <?php endif; ?>
      <?php if (!empty($pago['comprobante'])): ?>
      <div class="r-row"><span class="r-label">Comprobante</span><span class="r-val" style="font-weight:600"><?= htmlspecialchars($pago['comprobante']) ?></span></div>
      <?php endif; ?>
      <div class="r-row"><span class="r-label">Concepto</span><span class="r-val" style="font-weight:600"><?= htmlspecialchars($pago['producto']) ?></span></div>
      <div class="r-row"><span class="r-label">Vendedor</span><span class="r-val"><?= htmlspecialchars($pago['vendedor']) ?></span></div>
      <div class="r-row"><span class="r-label">Método</span><span class="r-val"><?= htmlspecialchars($pago['metodo']) ?></span></div>
      <div class="r-row"><span class="r-label">Estado</span>
        <span style="font-weight:700;color:<?= $es_pendiente?'var(--warning)':'var(--success)' ?>">
          <?= $es_pendiente ? '⏳ Pendiente confirmación' : '✅ Completado' ?>
        </span>
      </div>
      <div class="r-row"><span class="r-label">Fecha</span><span class="r-val"><?= date('d M Y, H:i') ?></span></div>
      <div class="r-row" style="padding-top:.85rem">
        <span style="font-family:var(--font-display);font-weight:800">Total</span>
        <span style="font-family:var(--font-display);font-weight:800;font-size:1.25rem;color:var(--accent)">$<?= number_format($pago['monto'],2) ?> COP</span>
      </div>
    </div>

    <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
      <a href="pagos.php" class="btn btn-primary" style="padding:.75rem 1.75rem">Ver mis pagos</a>
      <?php if ($es_pendiente): ?>
        <a href="chat.php" class="btn btn-outline" style="padding:.75rem 1.5rem">💬 Coordinar con vendedor</a>
      <?php else: ?>
        <a href="calificaciones_comprador.php" class="btn btn-outline" style="padding:.75rem 1.5rem">⭐ Calificar</a>
      <?php endif; ?>
      <a href="home_comprador.php" class="btn btn-outline" style="padding:.75rem 1.5rem">Inicio</a>
    </div>
  </div>
</div>
</div>
</body>
</html>
