<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') { header("Location: login.php"); exit; }
$id = $_SESSION['usuario_id'];
$flash_ok = $_SESSION['flash_ok'] ?? ''; unset($_SESSION['flash_ok']);
$flash_err = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $vid   = (int)($_POST['id_vendedor'] ?? 0);
    $pid   = (int)($_POST['id_producto'] ?? 0) ?: null;
    $punt  = (int)($_POST['puntuacion'] ?? 0);
    $com   = trim($_POST['comentario'] ?? '');
    if ($vid && $punt>=1 && $punt<=5) {
        try {
            $existe=$pdo->prepare("SELECT id FROM calificaciones WHERE id_comprador=? AND id_vendedor=?");$existe->execute([$id,$vid]);
            if ($existe->fetch()) { $flash_err='Ya calificaste a este vendedor.'; }
            else {
                $pdo->prepare("INSERT INTO calificaciones (id_comprador,id_vendedor,id_producto,puntuacion,comentario) VALUES (?,?,?,?,?)")->execute([$id,$vid,$pid,$punt,$com]);
                $_SESSION['flash_ok']='✅ Calificación enviada. ¡Gracias por tu opinión!';
                header("Location: calificaciones_comprador.php"); exit;
            }
        } catch(PDOException $e){ $flash_err=$e->getMessage(); }
    } else { $flash_err='Selecciona un vendedor y una puntuación.'; }
}

try {
    $vendedores=$pdo->prepare("SELECT DISTINCT u.id,u.nombre FROM transacciones t JOIN usuarios u ON t.id_vendedor=u.id WHERE t.id_comprador=?");
    $vendedores->execute([$id]);$vendedores=$vendedores->fetchAll();
    $miscals=$pdo->prepare("SELECT cal.*,u.nombre AS vendedor FROM calificaciones cal JOIN usuarios u ON cal.id_vendedor=u.id WHERE cal.id_comprador=? ORDER BY cal.fecha DESC");
    $miscals->execute([$id]);$miscals=$miscals->fetchAll();
} catch(PDOException $e){ $vendedores=[]; $miscals=[]; }
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Calificaciones — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
<style>
.star-selector{display:flex;gap:.35rem;flex-direction:row-reverse;justify-content:flex-end}
.star-selector input{display:none}
.star-selector label{font-size:2rem;cursor:pointer;color:var(--text-dim);transition:color .15s}
.star-selector input:checked~label,.star-selector label:hover,.star-selector label:hover~label{color:#ffbe0b}
</style>
</head><body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_comprador.php'; ?>
<div class="main-content">
  <?php if($flash_ok):?><span data-toast data-type="success" data-msg="<?=htmlspecialchars($flash_ok)?>"></span><?php endif;?>
  <?php if($flash_err):?><span data-toast data-type="error" data-msg="<?=htmlspecialchars($flash_err)?>"></span><?php endif;?>
  <div class="page-header"><div><h1 class="page-title">⭐ Calificaciones</h1><p class="page-subtitle">Califica a los vendedores con quienes has comprado</p></div></div>
  <div style="display:grid;grid-template-columns:380px 1fr;gap:1.5rem;align-items:start">
    <div class="card" style="position:sticky;top:1rem">
      <div class="card-header"><span class="card-title">✏️ Nueva calificación</span></div>
      <div class="card-body">
        <?php if(empty($vendedores)):?>
          <div style="text-align:center;padding:1rem;color:var(--text-muted);font-size:.85rem">Realiza una compra primero para poder calificar vendedores.</div>
        <?php else:?>
        <form method="POST" style="display:flex;flex-direction:column;gap:1.2rem">
          <div><label class="form-label">Vendedor *</label>
            <select name="id_vendedor" class="form-control" required>
              <option value="">Selecciona un vendedor...</option>
              <?php foreach($vendedores as $v):?><option value="<?=$v['id']?>"><?=htmlspecialchars($v['nombre'])?></option><?php endforeach;?>
            </select>
          </div>
          <div><label class="form-label">Puntuación *</label>
            <div class="star-selector">
              <?php for($i=5;$i>=1;$i--):?>
              <input type="radio" name="puntuacion" id="s<?=$i?>" value="<?=$i?>">
              <label for="s<?=$i?>" title="<?=$i?> estrella<?=$i>1?'s':''?>">★</label>
              <?php endfor;?>
            </div>
          </div>
          <div><label class="form-label">Comentario</label><textarea name="comentario" class="form-control" rows="3" placeholder="¿Cómo fue tu experiencia?"></textarea></div>
          <button type="submit" class="btn btn-primary">⭐ Enviar calificación</button>
        </form>
        <?php endif;?>
      </div>
    </div>
    <div>
      <h3 style="font-family:var(--font-display);font-size:.95rem;font-weight:700;margin-bottom:1rem">Mis calificaciones enviadas (<?=count($miscals)?>)</h3>
      <?php if(empty($miscals)):?>
        <div class="empty-state"><div class="empty-icon">⭐</div><h3>Sin calificaciones aún</h3><p>Aquí aparecerán tus calificaciones a vendedores.</p></div>
      <?php else: foreach($miscals as $c):?>
        <div class="card" style="margin-bottom:.75rem">
          <div class="card-body" style="padding:1.1rem 1.25rem">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem">
              <span style="font-weight:700"><?=htmlspecialchars($c['vendedor'])?></span>
              <span style="color:#ffbe0b;font-size:1.1rem"><?=str_repeat('★',(int)$c['puntuacion']).'<span style="color:var(--text-dim)">'.str_repeat('★',5-(int)$c['puntuacion']).'</span>'?></span>
            </div>
            <?php if($c['comentario']):?><p style="font-size:.85rem;color:var(--text-muted)"><?=htmlspecialchars($c['comentario'])?></p><?php endif;?>
            <div style="font-size:.74rem;color:var(--text-dim);margin-top:.4rem"><?=date('d M Y',strtotime($c['fecha']??'now'))?></div>
          </div>
        </div>
      <?php endforeach; endif;?>
    </div>
  </div>
</div></div></body></html>
