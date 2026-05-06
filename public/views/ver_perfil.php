<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$id_vendedor = (int)($_GET['id_vendedor'] ?? 0);
if(!$id_vendedor) { header("Location: home_comprador.php"); exit; }

$perfil = $pdo->prepare("SELECT p.*, u.nombre AS usuario, u.fecha_creacion FROM perfiles p JOIN usuarios u ON p.id_vendedor=u.id WHERE p.id_vendedor=?");
$perfil->execute([$id_vendedor]);
$perfil = $perfil->fetch();

$productos = $pdo->prepare("SELECT * FROM productos WHERE id_vendedor=? ORDER BY fecha_creacion DESC");
$productos->execute([$id_vendedor]);
$productos = $productos->fetchAll();

$califs = $pdo->prepare("SELECT c.*, u.nombre AS comprador FROM calificaciones c JOIN usuarios u ON c.id_comprador=u.id WHERE c.id_vendedor=? ORDER BY c.fecha DESC LIMIT 10");
$califs->execute([$id_vendedor]);
$califs = $califs->fetchAll();

$rating = $pdo->prepare("SELECT COALESCE(AVG(puntuacion),0) as avg, COUNT(*) as total FROM calificaciones WHERE id_vendedor=?");
$rating->execute([$id_vendedor]);
$rating = $rating->fetch();

$msg = '';
if(isset($_POST['calificar'])) {
    $puntuacion  = (int)$_POST['puntuacion'];
    $comentario  = trim($_POST['comentario']);
    $id_comprador = $_SESSION['usuario_id'];
    if($puntuacion >= 1 && $puntuacion <= 5) {
        $pdo->prepare("INSERT INTO calificaciones (id_comprador,id_vendedor,puntuacion,comentario) VALUES (?,?,?,?)")
           ->execute([$id_comprador,$id_vendedor,$puntuacion,$comentario]);
        $pdo->prepare("UPDATE perfiles SET calificacion_promedio=? WHERE id_vendedor=?")
           ->execute([$rating['avg'], $id_vendedor]);
        $msg = 'ok';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<?php include __DIR__ . '/../partials/head.php'; ?>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Perfil Vendedor — Reverse Market</title>
  
</head>
<body>
<div class="dashboard-layout">
  <?php if($_SESSION['rol']==='comprador'): ?><?php include '../partials/sidebar_comprador.php'; ?>
  <?php else: ?><?php include '../partials/sidebar_vendedor.php'; ?><?php endif; ?>
  <main class="dashboard-main">
    <?php if($perfil): ?>
    <div class="card" style="margin-bottom:2rem;">
      <div class="flex gap-3 items-center" style="flex-wrap:wrap;">
        <div class="user-avatar" style="width:64px;height:64px;font-size:1.5rem;">
          <?= strtoupper(substr($perfil['usuario'],0,2)) ?>
        </div>
        <div style="flex:1;">
          <h2 style="margin-bottom:.25rem;"><?= htmlspecialchars($perfil['nombre']) ?></h2>
          <div style="font-size:.9rem;color:var(--text-muted);"><?= htmlspecialchars($perfil['usuario']) ?></div>
          <div style="display:flex;gap:1rem;margin-top:.5rem;flex-wrap:wrap;">
            <span class="badge badge-info">📞 <?= htmlspecialchars($perfil['telefono']) ?></span>
            <span class="badge badge-info">📍 <?= htmlspecialchars($perfil['direccion']) ?></span>
            <span class="stars badge badge-warning"><?= number_format($rating['avg'],1) ?>★ (<?= $rating['total'] ?> reseñas)</span>
          </div>
        </div>
      </div>
      <?php if($perfil['descripcion']): ?><p style="margin-top:1rem;"><?= nl2br(htmlspecialchars($perfil['descripcion'])) ?></p><?php endif; ?>
    </div>
    <?php else: ?><div class="alert alert-info">Este vendedor aún no tiene perfil público.</div><?php endif; ?>

    <div class="grid-2" style="gap:2rem; align-items:start;">
      <div>
        <h3 style="margin-bottom:1rem;">Productos (<?= count($productos) ?>)</h3>
        <?php if(empty($productos)): ?>
        <div class="empty-state card"><div class="empty-icon">📦</div><h3>Sin productos</h3></div>
        <?php else: ?>
        <?php foreach($productos as $p): ?>
        <div class="card flex gap-2 items-center" style="margin-bottom:.75rem;padding:1rem;">
          <div style="font-size:2rem;"><?= empty($p['imagen'])?'📦':'🖼' ?></div>
          <div>
            <div style="font-weight:600;"><?= htmlspecialchars($p['nombre']) ?></div>
            <div style="color:var(--accent);font-family:var(--font-display);font-weight:700;">$<?= number_format($p['precio'],2) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <div>
        <?php if($_SESSION['rol']==='comprador'): ?>
        <div class="card" style="margin-bottom:1.5rem;">
          <h3 style="margin-bottom:1rem;">Calificar Vendedor</h3>
          <?php if($msg=='ok'): ?><div class="alert alert-success">✅ Calificación enviada.</div><?php endif; ?>
          <form method="POST">
            <div class="form-group">
              <label class="form-label">Puntuación</label>
              <div class="rating-input" id="stars">
                <?php for($i=1;$i<=5;$i++): ?><span data-val="<?=$i?>" onclick="setRating(<?=$i?>)">☆</span><?php endfor; ?>
              </div>
              <input type="hidden" name="puntuacion" id="puntuacionInput" required>
            </div>
            <div class="form-group">
              <label class="form-label">Comentario</label>
              <textarea name="comentario" class="form-control" placeholder="¿Cómo fue tu experiencia con este vendedor?"></textarea>
            </div>
            <button type="submit" name="calificar" class="btn btn-primary">⭐ Enviar Calificación</button>
          </form>
        </div>
        <?php endif; ?>
        <h3 style="margin-bottom:1rem;">Reseñas</h3>
        <?php if(empty($califs)): ?>
        <div class="empty-state card"><div class="empty-icon">⭐</div><h3>Sin reseñas aún</h3></div>
        <?php else: ?>
        <?php foreach($califs as $c): ?>
        <div class="card" style="margin-bottom:.75rem;padding:1rem;">
          <div class="flex justify-between items-center" style="margin-bottom:.5rem;">
            <span style="font-weight:600;font-size:.9rem;"><?= htmlspecialchars($c['comprador']) ?></span>
            <span class="stars"><?= str_repeat('★',$c['puntuacion']).str_repeat('☆',5-$c['puntuacion']) ?></span>
          </div>
          <p style="font-size:.88rem;"><?= htmlspecialchars($c['comentario']) ?></p>
          <div style="font-size:.75rem;color:var(--text-dim);margin-top:.4rem;"><?= date('d/m/Y',strtotime($c['fecha'])) ?></div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
<script>
function setRating(val) {
  document.getElementById('puntuacionInput').value = val;
  document.querySelectorAll('#stars span').forEach((s,i) => {
    s.textContent = i < val ? '★' : '☆';
    s.classList.toggle('active', i < val);
  });
}
</script>
</body>
</html>
