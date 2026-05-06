<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol'])) { header("Location: login.php"); exit; }
$id = $_SESSION['usuario_id']; $rol = $_SESSION['rol'];
// Mark all as read
try { $pdo->prepare("UPDATE notificaciones SET leido=1 WHERE id_usuario_destino=?")->execute([$id]); } catch(PDOException $e){}
try { $nots=$pdo->prepare("SELECT * FROM notificaciones WHERE id_usuario_destino=? ORDER BY fecha DESC LIMIT 50"); $nots->execute([$id]); $nots=$nots->fetchAll(); } catch(PDOException $e){ $nots=[]; }
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Notificaciones — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head><body>
<div class="dashboard-layout">
<?php if($rol==='comprador'): include __DIR__ . '/../partials/sidebar_comprador.php';
elseif($rol==='vendedor'): include __DIR__ . '/../partials/sidebar_vendedor.php';
else: include __DIR__ . '/../partials/sidebar_admin.php'; endif; ?>
<div class="main-content">
  <div class="page-header"><div><h1 class="page-title">🔔 Notificaciones</h1><p class="page-subtitle"><?=count($nots)?> notificaciones</p></div></div>
  <?php if(empty($nots)):?>
    <div class="empty-state"><div class="empty-icon">🔔</div><h3>Sin notificaciones</h3><p>Aquí aparecerán tus notificaciones de ofertas, pagos y mensajes.</p></div>
  <?php else:?>
    <div style="display:flex;flex-direction:column;gap:.6rem;max-width:680px">
      <?php foreach($nots as $n):?>
      <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1rem 1.25rem;display:flex;align-items:center;gap:.85rem">
        <div style="width:36px;height:36px;border-radius:50%;background:rgba(0,255,200,.1);border:1px solid rgba(0,255,200,.15);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0">
          <?=($n['tipo']??'')==='oferta'?'💼':(($n['tipo']??'')==='sistema'?'⚙️':'💬')?>
        </div>
        <div style="flex:1">
          <div style="font-size:.875rem"><?=htmlspecialchars($n['mensaje']??'')?></div>
          <div style="font-size:.74rem;color:var(--text-dim);margin-top:.25rem"><?=date('d M Y, H:i',strtotime($n['fecha']??'now'))?></div>
        </div>
      </div>
      <?php endforeach;?>
    </div>
  <?php endif;?>
</div></div></body></html>
