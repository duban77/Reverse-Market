<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header("Location: login.php"); exit; }
$flash_ok = $_SESSION['flash_ok'] ?? ''; unset($_SESSION['flash_ok']);
$flash_err = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

if (isset($_GET['resolver'])) {
    try {
        try{$pdo->exec("ALTER TABLE reportes ADD COLUMN IF NOT EXISTS estado ENUM('pendiente','revisado','resuelto') DEFAULT 'pendiente'");}catch(PDOException $e){}
        $pdo->prepare("UPDATE reportes SET estado='resuelto' WHERE id=?")->execute([(int)$_GET['resolver']]);
        $_SESSION['flash_ok']='Reporte marcado como resuelto.';
    } catch(PDOException $e){$_SESSION['flash_error']=$e->getMessage();}
    header("Location: admin_reportes.php"); exit;
}
if (isset($_GET['eliminar_producto'])) {
    $pid=(int)$_GET['eliminar_producto'];
    try {
        foreach(['DELETE FROM carrito WHERE id_producto=?','DELETE FROM respuesta_necesidad WHERE id_producto=?','DELETE FROM calificaciones WHERE id_producto=?'] as $q){try{$pdo->prepare($q)->execute([$pid]);}catch(PDOException $e){}}
        try{$pdo->prepare("UPDATE transacciones SET producto_id=NULL WHERE producto_id=?")->execute([$pid]);}catch(PDOException $e){}
        $pdo->prepare("DELETE FROM reportes WHERE producto_id=?")->execute([$pid]);
        $pdo->prepare("DELETE FROM productos WHERE id=?")->execute([$pid]);
        $_SESSION['flash_ok']='Producto eliminado.';
    } catch(PDOException $e){$_SESSION['flash_error']='Error: '.$e->getMessage();}
    header("Location: admin_reportes.php"); exit;
}

try{$reportes=$pdo->query("SELECT r.*,p.nombre AS producto,p.id AS pid,u.nombre AS reportado_por FROM reportes r JOIN productos p ON r.producto_id=p.id LEFT JOIN usuarios u ON r.usuario_id=u.id ORDER BY r.fecha_reporte DESC")->fetchAll();}catch(PDOException $e){$reportes=[];}
$pendientes=count(array_filter($reportes,fn($r)=>($r['estado']??'pendiente')==='pendiente'));
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reportes — Admin</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head><body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_admin.php'; ?>
<div class="main-content">
  <?php if($flash_ok):?><span data-toast data-type="success" data-msg="<?= htmlspecialchars($flash_ok)?>"></span><?php endif;?>
  <?php if($flash_err):?><span data-toast data-type="error" data-msg="<?= htmlspecialchars($flash_err)?>"></span><?php endif;?>

  <div class="page-header">
    <div><h1 class="page-title">🚨 Gestión de Reportes</h1><p class="page-subtitle"><?= count($reportes) ?> reportes totales · <?= $pendientes ?> pendientes</p></div>
  </div>

  <div class="stats-grid" style="margin-bottom:1.5rem;grid-template-columns:repeat(3,1fr)">
    <div class="stat-card"><div class="stat-number" style="color:var(--danger)"><?= $pendientes ?></div><div class="stat-label">Pendientes</div></div>
    <div class="stat-card"><div class="stat-number" style="color:var(--success)"><?= count(array_filter($reportes,fn($r)=>($r['estado']??'')==='resuelto')) ?></div><div class="stat-label">Resueltos</div></div>
    <div class="stat-card"><div class="stat-number"><?= count($reportes) ?></div><div class="stat-label">Total</div></div>
  </div>

  <?php if(empty($reportes)):?>
    <div class="empty-state"><div class="empty-icon">✅</div><h3>Sin reportes</h3><p>La plataforma está limpia.</p></div>
  <?php else:?>
  <div class="table-wrap">
    <table class="rm-table">
      <thead><tr><th>Producto</th><th>Reportado por</th><th>Motivo</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php foreach($reportes as $r): $e=$r['estado']??'pendiente';?>
        <tr>
          <td style="font-weight:600"><?= htmlspecialchars($r['producto']) ?></td>
          <td style="font-size:.85rem;color:var(--text-muted)"><?= htmlspecialchars($r['reportado_por']??'Anónimo') ?></td>
          <td style="font-size:.83rem;color:var(--text-muted);max-width:220px"><?= htmlspecialchars(substr($r['motivo'],0,70)) ?><?= strlen($r['motivo'])>70?'…':''?></td>
          <td><span class="badge <?= $e==='pendiente'?'badge-warning':($e==='resuelto'?'badge-success':'badge-info') ?>"><?= ucfirst($e) ?></span></td>
          <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d/m/Y',strtotime($r['fecha_reporte']??'now')) ?></td>
          <td>
            <div style="display:flex;gap:.4rem;flex-wrap:wrap">
              <?php if($e==='pendiente'):?>
                <a href="?resolver=<?= $r['id'] ?>" class="btn btn-sm" style="background:rgba(6,214,160,.12);color:var(--success);border:1px solid rgba(6,214,160,.2)">✓ Resolver</a>
                <a href="?eliminar_producto=<?= $r['pid'] ?>" class="btn btn-danger btn-sm" onclick="var h=this.href;event.preventDefault();RM.confirm('¿Eliminar este producto? Acción permanente.',function(){window.location.href=h})">🗑 Eliminar producto</a>
              <?php else:?><span style="font-size:.76rem;color:var(--text-dim)">—</span><?php endif;?>
            </div>
          </td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <?php endif;?>
</div></div></body></html>
