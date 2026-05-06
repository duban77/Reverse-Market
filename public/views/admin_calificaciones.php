<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header("Location: login.php"); exit; }
$flash_ok = $_SESSION['flash_ok'] ?? ''; unset($_SESSION['flash_ok']);
if (isset($_GET['eliminar'])) {
    try{$pdo->prepare("DELETE FROM calificaciones WHERE id=?")->execute([(int)$_GET['eliminar']]);$_SESSION['flash_ok']='Calificación eliminada.';}catch(PDOException $e){}
    header("Location: admin_calificaciones.php"); exit;
}
try{$cals=$pdo->query("SELECT cal.*,uc.nombre AS comprador,uv.nombre AS vendedor,p.nombre AS producto FROM calificaciones cal JOIN usuarios uc ON cal.id_comprador=uc.id JOIN usuarios uv ON cal.id_vendedor=uv.id LEFT JOIN productos p ON cal.id_producto=p.id ORDER BY cal.fecha DESC")->fetchAll();}catch(PDOException $e){$cals=[];}
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Calificaciones — Admin</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head><body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_admin.php'; ?>
<div class="main-content">
  <?php if($flash_ok):?><span data-toast data-type="success" data-msg="<?= htmlspecialchars($flash_ok)?>"></span><?php endif;?>
  <div class="page-header"><div><h1 class="page-title">⭐ Gestión de Calificaciones</h1><p class="page-subtitle"><?= count($cals) ?> calificaciones registradas</p></div></div>
  <?php if(empty($cals)):?>
    <div class="empty-state"><div class="empty-icon">⭐</div><h3>Sin calificaciones</h3><p>No hay calificaciones registradas aún.</p></div>
  <?php else:?>
  <div class="table-wrap">
    <table class="rm-table">
      <thead><tr><th>Comprador</th><th>Vendedor</th><th>Producto</th><th>Puntuación</th><th>Comentario</th><th>Fecha</th><th>Acción</th></tr></thead>
      <tbody>
        <?php foreach($cals as $c):?>
        <tr>
          <td style="font-weight:600"><?= htmlspecialchars($c['comprador']) ?></td>
          <td style="font-size:.875rem"><?= htmlspecialchars($c['vendedor']) ?></td>
          <td style="font-size:.82rem;color:var(--text-muted)"><?= htmlspecialchars($c['producto']??'—') ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:.35rem">
              <span style="color:#ffbe0b;letter-spacing:1px"><?= str_repeat('★',(int)$c['puntuacion']).'☆'.str_repeat('☆',5-(int)$c['puntuacion']) ?></span>
              <span style="font-weight:700;font-size:.85rem"><?= $c['puntuacion'] ?></span>
            </div>
          </td>
          <td style="font-size:.83rem;color:var(--text-muted);max-width:200px"><?= htmlspecialchars(substr($c['comentario']??'—',0,60)) ?></td>
          <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d/m/Y',strtotime($c['fecha']??'now')) ?></td>
          <td><a href="?eliminar=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="var h=this.href;event.preventDefault();RM.confirm('¿Eliminar esta calificación?',function(){window.location.href=h})">🗑</a></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <?php endif;?>
</div></div></body></html>
