<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php"); exit;
}
date_default_timezone_set('America/Bogota');

$stats = [];
$stat_queries = [
    'usuarios'      => "SELECT COUNT(*) FROM usuarios WHERE rol!='admin'",
    'compradores'   => "SELECT COUNT(*) FROM usuarios WHERE rol='comprador'",
    'vendedores'    => "SELECT COUNT(*) FROM usuarios WHERE rol='vendedor'",
    'productos'     => "SELECT COUNT(*) FROM productos",
    'necesidades'   => "SELECT COUNT(*) FROM necesidades",
    'solicitudes'   => "SELECT COUNT(*) FROM solicitudes",
    'reportes'      => "SELECT COUNT(*) FROM reportes WHERE estado='pendiente'",
    'transacciones' => "SELECT COUNT(*) FROM transacciones WHERE estado='completada'",
    'ingresos'      => "SELECT COALESCE(SUM(monto),0) FROM transacciones WHERE estado='completada'",
    'calificaciones'=> "SELECT COUNT(*) FROM calificaciones",
];
foreach($stat_queries as $k=>$q){try{$stats[$k]=$pdo->query($q)->fetchColumn();}catch(PDOException $e){$stats[$k]=0;}}

try{$usuarios_recientes=$pdo->query("SELECT * FROM usuarios ORDER BY id DESC LIMIT 6")->fetchAll();}catch(PDOException $e){$usuarios_recientes=[];}
try{$reportes_pendientes=$pdo->query("SELECT r.*,p.nombre AS producto,u.nombre AS reportado_por FROM reportes r JOIN productos p ON r.producto_id=p.id LEFT JOIN usuarios u ON r.usuario_id=u.id WHERE (r.estado IS NULL OR r.estado='pendiente') ORDER BY r.fecha_reporte DESC LIMIT 5")->fetchAll();}catch(PDOException $e){$reportes_pendientes=[];}
try{$ventas_recientes=$pdo->query("SELECT t.*,p.nombre AS producto,uc.nombre AS comprador,uv.nombre AS vendedor FROM transacciones t JOIN productos p ON t.producto_id=p.id JOIN usuarios uc ON t.id_comprador=uc.id JOIN usuarios uv ON t.id_vendedor=uv.id ORDER BY t.fecha DESC LIMIT 5")->fetchAll();}catch(PDOException $e){$ventas_recientes=[];}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Panel Admin — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
<style>
.admin-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:1rem;margin-bottom:1.75rem}
.admin-stat{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.35rem 1.4rem;position:relative;overflow:hidden;transition:var(--transition);cursor:default}
.admin-stat:hover{border-color:rgba(0,255,200,.2);transform:translateY(-2px)}
.admin-stat::after{content:attr(data-icon);position:absolute;right:.85rem;top:50%;transform:translateY(-50%);font-size:2.4rem;opacity:.07}
.admin-stat-n{font-family:var(--font-display);font-size:1.9rem;font-weight:800;line-height:1;margin-bottom:.3rem;letter-spacing:-.03em}
.admin-stat-l{font-size:.75rem;color:var(--text-muted);font-weight:400}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem}
.rol-badge{padding:.22rem .7rem;border-radius:100px;font-size:.7rem;font-weight:700}
.rol-comprador{background:rgba(0,255,200,.1);color:var(--accent)}
.rol-vendedor{background:rgba(37,99,235,.15);color:#60a5fa}
.rol-admin{background:rgba(255,77,109,.12);color:var(--danger)}
@media(max-width:900px){.two-col{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_admin.php'; ?>
<div class="main-content">

  <div class="page-header">
    <div>
      <h1 class="page-title">📊 Panel de Administración</h1>
      <p class="page-subtitle"><?= date('l, d M Y — H:i') ?> · Bienvenido, <?= htmlspecialchars($_SESSION['nombre']??'Admin') ?></p>
    </div>
    <?php if($stats['reportes']>0):?>
      <a href="admin_reportes.php" class="btn btn-danger">🚨 <?= $stats['reportes'] ?> reporte<?= $stats['reportes']>1?'s':'' ?> pendiente<?= $stats['reportes']>1?'s':'' ?></a>
    <?php endif;?>
  </div>

  <!-- Stats grid -->
  <div class="admin-grid">
    <div class="admin-stat" data-icon="👥"><div class="admin-stat-n" style="color:var(--accent)"><?= $stats['usuarios'] ?></div><div class="admin-stat-l">Usuarios totales</div></div>
    <div class="admin-stat" data-icon="🛒"><div class="admin-stat-n" style="color:var(--accent)"><?= $stats['compradores'] ?></div><div class="admin-stat-l">Compradores</div></div>
    <div class="admin-stat" data-icon="🏪"><div class="admin-stat-n" style="color:#60a5fa"><?= $stats['vendedores'] ?></div><div class="admin-stat-l">Vendedores</div></div>
    <div class="admin-stat" data-icon="📦"><div class="admin-stat-n" style="color:var(--success)"><?= $stats['productos'] ?></div><div class="admin-stat-l">Productos</div></div>
    <div class="admin-stat" data-icon="📌"><div class="admin-stat-n" style="color:var(--warning)"><?= $stats['necesidades'] ?></div><div class="admin-stat-l">Necesidades</div></div>
    <div class="admin-stat" data-icon="✅"><div class="admin-stat-n" style="color:var(--success)"><?= $stats['transacciones'] ?></div><div class="admin-stat-l">Ventas</div></div>
    <div class="admin-stat" data-icon="💰"><div class="admin-stat-n" style="color:var(--accent);font-size:1.3rem">$<?= number_format($stats['ingresos'],0,',','.') ?></div><div class="admin-stat-l">Volumen total COP</div></div>
    <div class="admin-stat" data-icon="⭐"><div class="admin-stat-n" style="color:#ffbe0b"><?= $stats['calificaciones'] ?></div><div class="admin-stat-l">Calificaciones</div></div>
    <div class="admin-stat" data-icon="🚨"><div class="admin-stat-n" style="color:<?= $stats['reportes']>0?'var(--danger)':'inherit' ?>"><?= $stats['reportes'] ?></div><div class="admin-stat-l">Reportes pendientes</div></div>
  </div>

  <!-- Two columns -->
  <div class="two-col">
    <!-- Usuarios recientes -->
    <div class="table-wrap">
      <div class="card-header"><span class="card-title">👥 Usuarios recientes</span><a href="admin_usuarios.php" style="font-size:.8rem;color:var(--accent)">Ver todos →</a></div>
      <table class="rm-table">
        <thead><tr><th>Nombre</th><th>Rol</th><th>Estado</th></tr></thead>
        <tbody>
          <?php foreach($usuarios_recientes as $u):?>
          <tr>
            <td>
              <div style="font-weight:600;font-size:.875rem"><?= htmlspecialchars($u['nombre']) ?></div>
              <div style="font-size:.73rem;color:var(--text-muted)"><?= htmlspecialchars($u['correo']) ?></div>
            </td>
            <td><span class="rol-badge rol-<?= $u['rol'] ?>"><?= ucfirst($u['rol']) ?></span></td>
            <td><span style="font-size:.75rem;font-weight:700;color:<?= ($u['estado']??'activo')==='activo'?'var(--success)':'var(--danger)' ?>"><?= ucfirst($u['estado']??'activo') ?></span></td>
          </tr>
          <?php endforeach;?>
        </tbody>
      </table>
    </div>

    <!-- Ventas recientes -->
    <div class="table-wrap">
      <div class="card-header"><span class="card-title">💳 Ventas recientes</span><span style="font-size:.8rem;color:var(--text-muted)"><?= $stats['transacciones'] ?> completadas</span></div>
      <?php if(empty($ventas_recientes)):?>
        <div style="padding:2rem;text-align:center;color:var(--text-muted);font-size:.85rem">Sin transacciones aún</div>
      <?php else:?>
      <table class="rm-table">
        <thead><tr><th>Producto</th><th>Monto</th><th>Fecha</th></tr></thead>
        <tbody>
          <?php foreach($ventas_recientes as $t):?>
          <tr>
            <td>
              <div style="font-weight:600;font-size:.85rem"><?= htmlspecialchars(substr($t['producto'],0,20)) ?></div>
              <div style="font-size:.73rem;color:var(--text-muted)"><?= htmlspecialchars($t['comprador']) ?> → <?= htmlspecialchars($t['vendedor']) ?></div>
            </td>
            <td style="color:var(--accent);font-family:var(--font-display);font-weight:700">$<?= number_format($t['monto'],0,',','.') ?></td>
            <td style="font-size:.76rem;color:var(--text-muted)"><?= date('d/m/Y',strtotime($t['fecha'])) ?></td>
          </tr>
          <?php endforeach;?>
        </tbody>
      </table>
      <?php endif;?>
    </div>
  </div>

  <!-- Reportes pendientes -->
  <?php if(!empty($reportes_pendientes)):?>
  <div class="table-wrap" style="margin-bottom:1.25rem">
    <div class="card-header">
      <span class="card-title" style="color:var(--danger)">🚨 Reportes pendientes</span>
      <a href="admin_reportes.php" style="font-size:.8rem;color:var(--danger)">Gestionar →</a>
    </div>
    <table class="rm-table">
      <thead><tr><th>Producto</th><th>Motivo</th><th>Por</th><th>Fecha</th><th>Acción</th></tr></thead>
      <tbody>
        <?php foreach($reportes_pendientes as $r):?>
        <tr>
          <td style="font-weight:600"><?= htmlspecialchars($r['producto']) ?></td>
          <td style="font-size:.82rem;color:var(--text-muted);max-width:180px"><?= htmlspecialchars(substr($r['motivo'],0,50)) ?>…</td>
          <td style="font-size:.82rem"><?= htmlspecialchars($r['reportado_por']??'Anónimo') ?></td>
          <td style="font-size:.76rem;color:var(--text-muted)"><?= date('d/m/Y',strtotime($r['fecha_reporte'])) ?></td>
          <td><a href="admin_reportes.php" class="btn btn-danger btn-sm">Revisar</a></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <?php else:?>
  <div style="background:rgba(6,214,160,.06);border:1px solid rgba(6,214,160,.15);border-radius:var(--radius);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;margin-bottom:1.25rem">
    <span style="font-size:1.5rem">✅</span>
    <div><div style="font-weight:700;margin-bottom:.1rem">Sin reportes pendientes</div><div style="font-size:.84rem;color:var(--text-muted)">La plataforma está limpia.</div></div>
  </div>
  <?php endif;?>

  <!-- Quick actions -->
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.75rem">
    <?php foreach([['admin_usuarios.php','👥','Usuarios'],['admin_productos.php','📦','Productos'],['admin_reportes.php','🚨','Reportes'],['admin_solicitudes.php','📋','Solicitudes'],['admin_calificaciones.php','⭐','Calificaciones']] as [$url,$icon,$label]):?>
    <a href="<?= $url ?>" class="btn btn-outline" style="flex-direction:column;gap:.4rem;padding:1.1rem .75rem;text-align:center;border-radius:var(--radius)">
      <span style="font-size:1.4rem"><?= $icon ?></span>
      <span style="font-size:.78rem"><?= $label ?></span>
    </a>
    <?php endforeach;?>
  </div>

</div>
</div>
</body>
</html>
