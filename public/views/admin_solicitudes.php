<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header("Location: login.php"); exit; }
try{$solicitudes=$pdo->query("SELECT s.*,u.nombre AS comprador FROM solicitudes s JOIN usuarios u ON s.id_comprador=u.id ORDER BY s.fecha_publicacion DESC")->fetchAll();}catch(PDOException $e){$solicitudes=[];}
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Solicitudes — Admin</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head><body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_admin.php'; ?>
<div class="main-content">
  <div class="page-header"><div><h1 class="page-title">📋 Gestión de Solicitudes</h1><p class="page-subtitle"><?= count($solicitudes) ?> solicitudes registradas</p></div></div>
  <?php if(empty($solicitudes)):?>
    <div class="empty-state"><div class="empty-icon">📋</div><h3>Sin solicitudes</h3><p>No hay solicitudes registradas aún.</p></div>
  <?php else:?>
  <div class="table-wrap">
    <table class="rm-table">
      <thead><tr><th>Comprador</th><th>Condiciones</th><th>Presupuesto</th><th>Entrega</th><th>Estado</th><th>Fecha</th></tr></thead>
      <tbody>
        <?php foreach($solicitudes as $s): $e=$s['estado']??'activa';?>
        <tr>
          <td style="font-weight:600"><?= htmlspecialchars($s['comprador']) ?></td>
          <td style="font-size:.83rem;color:var(--text-muted);max-width:250px"><?= htmlspecialchars(substr($s['condiciones']??'—',0,80)) ?></td>
          <td style="font-family:var(--font-display);font-weight:700;color:var(--accent)">$<?= number_format($s['precio']??0,0,',','.') ?></td>
          <td style="font-size:.85rem"><?= $s['tiempo_entrega']??'—' ?> días</td>
          <td><span class="badge <?= $e==='activa'?'badge-success':'badge-danger' ?>"><?= ucfirst($e) ?></span></td>
          <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d/m/Y',strtotime($s['fecha_publicacion']??'now')) ?></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <?php endif;?>
</div></div></body></html>
