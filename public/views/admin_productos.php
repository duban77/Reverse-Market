<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header("Location: login.php"); exit; }
$flash_ok  = $_SESSION['flash_ok']    ?? ''; unset($_SESSION['flash_ok']);
$flash_err = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

if (isset($_GET['eliminar'])) {
    $pid = (int)$_GET['eliminar'];
    try {
        foreach (['DELETE FROM carrito WHERE id_producto=?','DELETE FROM ofertas WHERE id_producto=?','DELETE FROM respuesta_necesidad WHERE id_producto=?','DELETE FROM calificaciones WHERE id_producto=?','DELETE FROM reportes WHERE producto_id=?'] as $q) { try{$pdo->prepare($q)->execute([$pid]);}catch(PDOException $e){} }
        try{$pdo->prepare("UPDATE transacciones SET producto_id=NULL WHERE producto_id=?")->execute([$pid]);}catch(PDOException $e){}
        $pdo->prepare("DELETE FROM productos WHERE id=?")->execute([$pid]);
        $_SESSION['flash_ok']='Producto eliminado correctamente.';
    } catch(PDOException $e){ $_SESSION['flash_error']='No se pudo eliminar: '.$e->getMessage(); }
    header("Location: admin_productos.php"); exit;
}

$buscar = trim($_GET['q'] ?? '');
try {
    $sql = "SELECT p.*,u.nombre AS vendedor FROM productos p JOIN usuarios u ON p.id_vendedor=u.id WHERE 1=1";
    $params=[];
    if($buscar){$sql.=" AND (p.nombre LIKE ? OR u.nombre LIKE ?)";$params[]="%$buscar%";$params[]="%$buscar%";}
    $sql.=" ORDER BY p.id DESC";
    $stmt=$pdo->prepare($sql);$stmt->execute($params);$productos=$stmt->fetchAll();
} catch(PDOException $e){$productos=[];}
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Productos — Admin</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head><body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_admin.php'; ?>
<div class="main-content">
  <?php if($flash_ok):?><span data-toast data-type="success" data-msg="<?= htmlspecialchars($flash_ok)?>"></span><?php endif;?>
  <?php if($flash_err):?><span data-toast data-type="error" data-msg="<?= htmlspecialchars($flash_err)?>"></span><?php endif;?>

  <div class="page-header">
    <div><h1 class="page-title">📦 Gestión de Productos</h1><p class="page-subtitle"><?= count($productos) ?> productos registrados</p></div>
  </div>

  <form method="GET" style="display:flex;gap:.75rem;margin-bottom:1.5rem">
    <input type="text" name="q" class="form-control" placeholder="🔍 Buscar por nombre o vendedor..." value="<?= htmlspecialchars($buscar) ?>" style="max-width:360px">
    <button type="submit" class="btn btn-primary">Buscar</button>
    <?php if($buscar):?><a href="admin_productos.php" class="btn btn-outline">✕</a><?php endif;?>
  </form>

  <div class="table-wrap">
    <table class="rm-table">
      <thead><tr><th>#</th><th>Producto</th><th>Vendedor</th><th>Precio</th><th>Categoría</th><th>Fecha</th><th>Acción</th></tr></thead>
      <tbody>
        <?php foreach($productos as $p):?>
        <tr>
          <td style="color:var(--text-dim);font-size:.8rem"><?= $p['id'] ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:.75rem">
              <div style="width:38px;height:38px;border-radius:8px;background:var(--bg-panel);display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;overflow:hidden">
                <?php if(!empty($p['imagen'])&&file_exists(__DIR__.'/../../public/uploads/'.$p['imagen'])):?><img src="../uploads/<?= htmlspecialchars($p['imagen']) ?>" style="width:38px;height:38px;object-fit:cover"><?php else:?>📦<?php endif;?>
              </div>
              <div style="font-weight:600;font-size:.875rem"><?= htmlspecialchars($p['nombre']) ?></div>
            </div>
          </td>
          <td style="font-size:.85rem;color:var(--text-muted)"><?= htmlspecialchars($p['vendedor']) ?></td>
          <td style="font-family:var(--font-display);font-weight:700;color:var(--accent)">$<?= number_format($p['precio'],0,',','.') ?></td>
          <td><span class="badge badge-neutral"><?= htmlspecialchars($p['categoria']??'—') ?></span></td>
          <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d/m/Y',strtotime($p['fecha_creacion']??'now')) ?></td>
          <td><a href="?eliminar=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="var h=this.href;event.preventDefault();RM.confirm('¿Eliminar este producto?',function(){window.location.href=h})">🗑 Eliminar</a></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div></div></body></html>
