<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'vendedor') { header("Location: login.php"); exit; }
$id = $_SESSION['usuario_id'];
$flash_ok = $_SESSION['flash_ok'] ?? ''; unset($_SESSION['flash_ok']);
$flash_err = '';
try { $st=$pdo->prepare("SELECT * FROM perfiles WHERE id_vendedor=?");$st->execute([$id]);$perfil=$st->fetch(); } catch(PDOException $e){ $perfil=null; }

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $tel    = trim($_POST['telefono'] ?? '');
    $desc   = trim($_POST['descripcion'] ?? '');
    try {
        if ($perfil) { $pdo->prepare("UPDATE perfiles SET nombre=?,telefono=?,descripcion=? WHERE id_vendedor=?")->execute([$nombre,$tel,$desc,$id]); }
        else { $pdo->prepare("INSERT INTO perfiles (id_vendedor,nombre,telefono,descripcion) VALUES (?,?,?,?)")->execute([$id,$nombre,$tel,$desc]); }
        $_SESSION['flash_ok']='✅ Perfil guardado correctamente.'; header("Location: crear_perfil.php"); exit;
    } catch(PDOException $e){ $flash_err=$e->getMessage(); }
}
try { $rating=$pdo->prepare("SELECT AVG(puntuacion) AS avg,COUNT(*) AS total FROM calificaciones WHERE id_vendedor=?");$rating->execute([$id]);$rating=$rating->fetch(); } catch(PDOException $e){ $rating=['avg'=>0,'total'=>0]; }
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mi Perfil — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head><body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_vendedor.php'; ?>
<div class="main-content">
  <?php if($flash_ok):?><span data-toast data-type="success" data-msg="<?=htmlspecialchars($flash_ok)?>"></span><?php endif;?>
  <?php if($flash_err):?><span data-toast data-type="error" data-msg="<?=htmlspecialchars($flash_err)?>"></span><?php endif;?>
  <div class="page-header"><div><h1 class="page-title">👤 Mi Perfil de Vendedor</h1><p class="page-subtitle">Configura cómo te ven los compradores</p></div></div>
  <div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;max-width:900px">
    <div class="card">
      <div class="card-header"><span class="card-title">✏️ Editar perfil</span></div>
      <div class="card-body">
        <form method="POST" style="display:flex;flex-direction:column;gap:1.2rem">
          <div><label class="form-label">Nombre público *</label><input type="text" name="nombre" class="form-control" value="<?=htmlspecialchars($perfil['nombre']??$_SESSION['nombre']??'')?>" required placeholder="Tu nombre o nombre de negocio"></div>
          <div><label class="form-label">Teléfono de contacto</label><input type="text" name="telefono" class="form-control" value="<?=htmlspecialchars($perfil['telefono']??'')?>" placeholder="300 000 0000"></div>
          <div><label class="form-label">Descripción de tu negocio</label><textarea name="descripcion" class="form-control" rows="4" placeholder="Cuéntales a los compradores quién eres, qué vendes, tu experiencia..."><?=htmlspecialchars($perfil['descripcion']??'')?></textarea></div>
          <button type="submit" class="btn btn-primary btn-lg">💾 Guardar perfil</button>
        </form>
      </div>
    </div>
    <!-- Preview card -->
    <div class="card">
      <div class="card-header"><span class="card-title">👁 Vista previa</span></div>
      <div class="card-body" style="text-align:center">
        <div style="width:64px;height:64px;border-radius:14px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:800;margin:0 auto 1rem;font-family:var(--font-display)">
          <?=strtoupper(mb_substr($perfil['nombre']??$_SESSION['nombre']??'V',0,1))?>
        </div>
        <div style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:.25rem"><?=htmlspecialchars($perfil['nombre']??$_SESSION['nombre']??'Tu nombre')?></div>
        <?php if(!empty($perfil['telefono'])):?><div style="font-size:.82rem;color:var(--text-muted);margin-bottom:.5rem">📞 <?=htmlspecialchars($perfil['telefono'])?></div><?php endif;?>
        <?php if($rating['total']>0):?>
          <div style="color:#ffbe0b;font-size:1rem;margin-bottom:.5rem"><?=str_repeat('★',round($rating['avg']))?> <span style="color:var(--text-muted);font-size:.82rem"><?=number_format($rating['avg'],1)?> (<?=$rating['total']?> reseñas)</span></div>
        <?php endif;?>
        <span class="badge badge-info">Vendedor verificado</span>
      </div>
    </div>
  </div>
</div></div></body></html>
