<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header("Location: login.php"); exit; }

$flash_ok  = $_SESSION['flash_ok']    ?? ''; unset($_SESSION['flash_ok']);
$flash_err = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

if (isset($_GET['eliminar'])) {
    $uid = (int)$_GET['eliminar'];
    if ($uid === (int)$_SESSION['usuario_id']) { $_SESSION['flash_error']='No puedes eliminar tu propia cuenta.'; header("Location: admin_usuarios.php"); exit; }
    try {
        foreach (['DELETE FROM chat_mensajes WHERE id_emisor=? OR id_receptor=?'=>[$uid,$uid],'DELETE FROM notificaciones WHERE id_usuario_destino=?'=>[$uid],'DELETE FROM carrito WHERE id_comprador=?'=>[$uid],'DELETE FROM calificaciones WHERE id_comprador=? OR id_vendedor=?'=>[$uid,$uid],'UPDATE reportes SET usuario_id=NULL WHERE usuario_id=?'=>[$uid],'UPDATE transacciones SET id_comprador=NULL WHERE id_comprador=?'=>[$uid],'UPDATE transacciones SET id_vendedor=NULL WHERE id_vendedor=?'=>[$uid]] as $q=>$p) { try{$pdo->prepare($q)->execute($p);}catch(PDOException $e){} }
        $prods=$pdo->prepare("SELECT id FROM productos WHERE id_vendedor=?");$prods->execute([$uid]);
        foreach($prods->fetchAll() as $p){try{$pdo->prepare("DELETE FROM carrito WHERE id_producto=?")->execute([$p['id']]);}catch(PDOException $e){}try{$pdo->prepare("DELETE FROM reportes WHERE producto_id=?")->execute([$p['id']]);}catch(PDOException $e){}}
        try{$pdo->prepare("DELETE FROM productos WHERE id_vendedor=?")->execute([$uid]);}catch(PDOException $e){}
        $pdo->prepare("DELETE FROM usuarios WHERE id=?")->execute([$uid]);
        $_SESSION['flash_ok']='Usuario eliminado correctamente.';
    } catch(PDOException $e){ $_SESSION['flash_error']='No se pudo eliminar: '.$e->getMessage(); }
    header("Location: admin_usuarios.php"); exit;
}
if (isset($_GET['bloquear'])) {
    try{$pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS estado ENUM('activo','bloqueado') DEFAULT 'activo'");}catch(PDOException $e){}
    try{$pdo->prepare("UPDATE usuarios SET estado='bloqueado' WHERE id=?")->execute([(int)$_GET['bloquear']]);$_SESSION['flash_ok']='Usuario bloqueado.';}catch(PDOException $e){$_SESSION['flash_error']=$e->getMessage();}
    header("Location: admin_usuarios.php"); exit;
}
if (isset($_GET['activar'])) {
    try{$pdo->prepare("UPDATE usuarios SET estado='activo' WHERE id=?")->execute([(int)$_GET['activar']]);$_SESSION['flash_ok']='Usuario activado.';}catch(PDOException $e){$_SESSION['flash_error']=$e->getMessage();}
    header("Location: admin_usuarios.php"); exit;
}

$buscar = trim($_GET['q'] ?? '');
try {
    $sql = "SELECT * FROM usuarios WHERE 1=1";
    $params = [];
    if ($buscar) { $sql .= " AND (nombre LIKE ? OR correo LIKE ?)"; $params[]="%$buscar%";$params[]="%$buscar%"; }
    $sql .= " ORDER BY id DESC";
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    $usuarios = $stmt->fetchAll();
} catch(PDOException $e){ $usuarios=[]; }
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Usuarios — Admin</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head><body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_admin.php'; ?>
<div class="main-content">
  <?php if($flash_ok):?><span data-toast data-type="success" data-msg="<?= htmlspecialchars($flash_ok)?>"></span><?php endif;?>
  <?php if($flash_err):?><span data-toast data-type="error" data-msg="<?= htmlspecialchars($flash_err)?>"></span><?php endif;?>

  <div class="page-header">
    <div><h1 class="page-title">👥 Gestión de Usuarios</h1><p class="page-subtitle"><?= count($usuarios) ?> usuarios registrados</p></div>
  </div>

  <form method="GET" style="display:flex;gap:.75rem;margin-bottom:1.5rem">
    <input type="text" name="q" class="form-control" placeholder="🔍 Buscar por nombre o correo..." value="<?= htmlspecialchars($buscar) ?>" style="max-width:360px">
    <button type="submit" class="btn btn-primary">Buscar</button>
    <?php if($buscar):?><a href="admin_usuarios.php" class="btn btn-outline">✕ Limpiar</a><?php endif;?>
  </form>

  <div class="table-wrap">
    <table class="rm-table">
      <thead><tr><th>#</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php foreach($usuarios as $u): $e=$u['estado']??'activo'; ?>
        <tr>
          <td style="color:var(--text-dim);font-size:.8rem"><?= $u['id'] ?></td>
          <td style="font-weight:600"><?= htmlspecialchars($u['nombre']) ?></td>
          <td style="color:var(--text-muted);font-size:.85rem"><?= htmlspecialchars($u['correo']) ?></td>
          <td><span class="badge <?= $u['rol']==='admin'?'badge-danger':($u['rol']==='vendedor'?'badge-info':'badge-success') ?>"><?= ucfirst($u['rol']) ?></span></td>
          <td><span class="badge <?= $e==='activo'?'badge-success':'badge-danger' ?>"><?= ucfirst($e) ?></span></td>
          <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d/m/Y',strtotime($u['fecha_creacion']??'now')) ?></td>
          <td>
            <?php if($u['rol']!=='admin'):?>
            <div style="display:flex;gap:.4rem;flex-wrap:wrap">
              <?php if($e==='activo'):?>
                <a href="?bloquear=<?= $u['id'] ?>" class="btn btn-warning btn-sm" style="background:rgba(255,190,11,.15);color:var(--warning);border:1px solid rgba(255,190,11,.25)" onclick="event.preventDefault();RM.confirm('¿Bloquear a este usuario?',()=>{window.location.href=this.href})">🔒 Bloquear</a>
              <?php else:?>
                <a href="?activar=<?= $u['id'] ?>" class="btn btn-sm" style="background:rgba(6,214,160,.12);color:var(--success);border:1px solid rgba(6,214,160,.2)">✓ Activar</a>
              <?php endif;?>
              <a href="?eliminar=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="event.preventDefault();RM.confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.',()=>{window.location.href=this.href})">🗑</a>
            </div>
            <?php else:?><span style="font-size:.76rem;color:var(--text-dim)">—</span><?php endif;?>
          </td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div></div></body></html>
