<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol'])) { header("Location: login.php"); exit; }
$id  = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'];
$flash_ok = $_SESSION['flash_ok'] ?? ''; unset($_SESSION['flash_ok']);
$flash_err = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

if ($rol === 'comprador' && $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['publicar'])) {
    $cond  = trim($_POST['condiciones'] ?? '');
    $precio= (float)($_POST['precio'] ?? 0);
    $dias  = (int)($_POST['tiempo_entrega'] ?? 0);
    if ($cond && $precio > 0) {
        try {
            $pdo->prepare("INSERT INTO solicitudes (id_comprador,condiciones,precio,tiempo_entrega,estado) VALUES (?,?,?,?,'activa')")->execute([$id,$cond,$precio,$dias]);
            $_SESSION['flash_ok']='✅ Solicitud publicada correctamente.';
        } catch(PDOException $e){ $_SESSION['flash_error']=$e->getMessage(); }
    } else { $_SESSION['flash_error']='Completa todos los campos requeridos.'; }
    header("Location: lista_solicitudes.php"); exit;
}
if ($rol==='comprador' && isset($_GET['cerrar'])) {
    try { $pdo->prepare("UPDATE solicitudes SET estado='cerrada' WHERE id=? AND id_comprador=?")->execute([(int)$_GET['cerrar'],$id]); $_SESSION['flash_ok']='Solicitud cerrada.'; } catch(PDOException $e){}
    header("Location: lista_solicitudes.php"); exit;
}

if ($rol==='comprador') {
    try { $st=$pdo->prepare("SELECT * FROM solicitudes WHERE id_comprador=? ORDER BY fecha_publicacion DESC"); $st->execute([$id]); $items=$st->fetchAll(); } catch(PDOException $e){ $items=[]; }
} else {
    try { $items=$pdo->query("SELECT s.*,u.nombre AS comprador FROM solicitudes s JOIN usuarios u ON s.id_comprador=u.id WHERE s.estado='activa' ORDER BY s.fecha_publicacion DESC")->fetchAll(); } catch(PDOException $e){ $items=[]; }
}
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Solicitudes — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head><body>
<div class="dashboard-layout">
<?php if($rol==='comprador'): include __DIR__ . '/../partials/sidebar_comprador.php';
elseif($rol==='vendedor'): include __DIR__ . '/../partials/sidebar_vendedor.php';
else: include __DIR__ . '/../partials/sidebar_admin.php'; endif; ?>
<div class="main-content">
  <?php if($flash_ok):?><span data-toast data-type="success" data-msg="<?=htmlspecialchars($flash_ok)?>"></span><?php endif;?>
  <?php if($flash_err):?><span data-toast data-type="error" data-msg="<?=htmlspecialchars($flash_err)?>"></span><?php endif;?>
  <div class="page-header">
    <div><h1 class="page-title">📋 <?=$rol==='comprador'?'Mis Solicitudes':'Solicitudes del Mercado'?></h1><p class="page-subtitle"><?=$rol==='comprador'?'Gestiona tus solicitudes de productos':'Solicitudes activas de compradores'?></p></div>
  </div>
  <div style="display:grid;grid-template-columns:<?=$rol==='comprador'?'360px 1fr':'1fr'?>;gap:1.5rem;align-items:start">
    <?php if($rol==='comprador'):?>
    <div class="card" style="position:sticky;top:1rem">
      <div class="card-header"><span class="card-title">✏️ Nueva solicitud</span></div>
      <div class="card-body">
        <form method="POST" style="display:flex;flex-direction:column;gap:1.1rem">
          <div><label class="form-label">Condiciones y descripción *</label><textarea name="condiciones" class="form-control" rows="3" placeholder="Describe lo que necesitas, calidad esperada, condiciones de entrega..." required></textarea></div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
            <div><label class="form-label">Presupuesto (COP) *</label><input type="number" name="precio" class="form-control" placeholder="50000" min="1" required></div>
            <div><label class="form-label">Entrega (días)</label><input type="number" name="tiempo_entrega" class="form-control" placeholder="7" min="1"></div>
          </div>
          <button type="submit" name="publicar" class="btn btn-primary">📋 Publicar solicitud</button>
        </form>
      </div>
    </div>
    <?php endif;?>
    <div>
      <?php if(empty($items)):?>
        <div class="empty-state"><div class="empty-icon">📋</div><h3>Sin solicitudes</h3><p><?=$rol==='comprador'?'Publica tu primera solicitud.':'No hay solicitudes activas aún.'?></p></div>
      <?php else:?>
      <div class="table-wrap">
        <table class="rm-table">
          <thead><tr>
            <?php if($rol==='vendedor'):?><th>Comprador</th><?php endif;?>
            <th>Condiciones</th><th>Presupuesto</th><th>Entrega</th><th>Estado</th>
            <?php if($rol==='vendedor'):?><th>Acción</th><?php else:?><th>Acción</th><?php endif;?>
          </tr></thead>
          <tbody>
            <?php foreach($items as $s): $e=$s['estado']??'activa';?>
            <tr>
              <?php if($rol==='vendedor'):?><td style="font-weight:600"><?=htmlspecialchars($s['comprador']??'')?></td><?php endif;?>
              <td style="font-size:.84rem;color:var(--text-muted);max-width:260px"><?=htmlspecialchars(substr($s['condiciones']??'—',0,80))?><?=strlen($s['condiciones']??'')>80?'…':''?></td>
              <td style="font-family:var(--font-display);font-weight:700;color:var(--accent)">$<?=number_format($s['precio']??0,0,',','.')?></td>
              <td style="font-size:.85rem"><?=$s['tiempo_entrega']??'—'?> días</td>
              <td><span class="badge <?=$e==='activa'?'badge-success':'badge-danger'?>"><?=ucfirst($e)?></span></td>
              <td>
                <?php if($rol==='vendedor' && $e==='activa'):?>
                  <a href="enviar_oferta.php?solicitud=<?=$s['id']?>" class="btn btn-primary btn-sm">Ofertar</a>
                <?php elseif($rol==='comprador' && $e==='activa'):?>
                  <a href="?cerrar=<?=$s['id']?>" class="btn btn-sm btn-outline" onclick="var h=this.href;event.preventDefault();RM.confirm('¿Cerrar esta solicitud?',function(){window.location.href=h})">Cerrar</a>
                <?php else:?><span style="font-size:.76rem;color:var(--text-dim)">—</span><?php endif;?>
              </td>
            </tr>
            <?php endforeach;?>
          </tbody>
        </table>
      </div>
      <?php endif;?>
    </div>
  </div>
</div></div></body></html>
