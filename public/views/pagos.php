<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol'])) { header("Location: login.php"); exit; }
$id = $_SESSION['usuario_id']; $rol = $_SESSION['rol'];
try {
    if ($rol==='comprador') {
        $st=$pdo->prepare("SELECT t.*,p.nombre AS producto,u.nombre AS vendedor FROM transacciones t LEFT JOIN productos p ON t.producto_id=p.id LEFT JOIN usuarios u ON t.id_vendedor=u.id WHERE t.id_comprador=? ORDER BY t.fecha DESC");
    } else {
        $st=$pdo->prepare("SELECT t.*,p.nombre AS producto,u.nombre AS comprador FROM transacciones t LEFT JOIN productos p ON t.producto_id=p.id LEFT JOIN usuarios u ON t.id_comprador=u.id WHERE t.id_vendedor=? ORDER BY t.fecha DESC");
    }
    $st->execute([$id]); $transacciones=$st->fetchAll();
    $total_completadas = array_sum(array_map(fn($t)=>$t['estado']==='completada'?$t['monto']:0, $transacciones));
    $total_pendientes  = count(array_filter($transacciones, fn($t)=>$t['estado']==='pendiente'));
} catch(PDOException $e){ $transacciones=[]; $total_completadas=0; $total_pendientes=0; }
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mis Pagos — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head><body>
<div class="dashboard-layout">
<?php if($rol==='comprador'): include __DIR__ . '/../partials/sidebar_comprador.php';
else: include __DIR__ . '/../partials/sidebar_vendedor.php'; endif; ?>
<div class="main-content">
  <div class="page-header">
    <div><h1 class="page-title">💳 <?=$rol==='comprador'?'Mis Pagos':'Mis Cobros'?></h1><p class="page-subtitle">Historial completo de transacciones</p></div>
  </div>
  <div class="stats-grid" style="margin-bottom:1.75rem">
    <div class="stat-card"><div class="stat-number"><?=count($transacciones)?></div><div class="stat-label">Total transacciones</div></div>
    <div class="stat-card"><div class="stat-number" style="color:var(--success)">$<?=number_format($total_completadas,0,',','.')?></div><div class="stat-label">Monto completado COP</div></div>
    <div class="stat-card"><div class="stat-number" style="color:<?=$total_pendientes>0?'var(--warning)':'inherit'?>"><?=$total_pendientes?></div><div class="stat-label">Pagos pendientes</div></div>
  </div>
  <?php if(empty($transacciones)):?>
    <div class="empty-state"><div class="empty-icon">💳</div><h3>Sin transacciones aún</h3><p><?=$rol==='comprador'?'Tus compras aparecerán aquí.':'Tus ventas cobradas aparecerán aquí.'?></p></div>
  <?php else:?>
  <div class="table-wrap">
    <table class="rm-table">
      <thead><tr><th>N° TX</th><th>Producto</th><th><?=$rol==='comprador'?'Vendedor':'Comprador'?></th><th>Monto</th><th>Estado</th><th>Fecha</th></tr></thead>
      <tbody>
        <?php foreach($transacciones as $t): $e=$t['estado']??'pendiente';?>
        <tr>
          <td style="font-family:monospace;font-size:.8rem;color:var(--text-muted)">#<?=str_pad($t['id'],6,'0',STR_PAD_LEFT)?></td>
          <td style="font-weight:600;font-size:.875rem"><?=htmlspecialchars($t['producto']??'—')?></td>
          <td style="font-size:.85rem;color:var(--text-muted)"><?=htmlspecialchars($rol==='comprador'?($t['vendedor']??'—'):($t['comprador']??'—'))?></td>
          <td style="font-family:var(--font-display);font-weight:800;color:var(--accent)">$<?=number_format($t['monto'],0,',','.')?></td>
          <td><span class="badge <?=$e==='completada'?'badge-success':($e==='pendiente'?'badge-warning':'badge-danger')?>"><?=ucfirst($e)?></span></td>
          <td style="font-size:.8rem;color:var(--text-muted)"><?=date('d M Y, H:i',strtotime($t['fecha']??'now'))?></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <?php endif;?>
</div></div></body></html>
