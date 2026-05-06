<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') { header("Location: login.php"); exit; }
$id = $_SESSION['usuario_id'];
$flash_ok = $_SESSION['flash_ok'] ?? ''; unset($_SESSION['flash_ok']);
$flash_err = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['publicar'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $desc   = trim($_POST['descripcion'] ?? '');
    if ($titulo && $desc) {
        try {
            $pdo->prepare("INSERT INTO necesidades (titulo, descripcion, id_comprador) VALUES (?,?,?)")->execute([$titulo,$desc,$id]);
            $_SESSION['flash_ok'] = '✅ Necesidad publicada. Los vendedores podrán verte y enviarte ofertas.';
        } catch(PDOException $e){ $_SESSION['flash_error']=$e->getMessage(); }
    } else { $_SESSION['flash_error']='Completa título y descripción.'; }
    header("Location: necesidades_comprador.php"); exit;
}
if (isset($_GET['cerrar'])) {
    try { $pdo->prepare("UPDATE necesidades SET estado='cerrada' WHERE id=? AND id_comprador=?")->execute([(int)$_GET['cerrar'],$id]); $_SESSION['flash_ok']='Necesidad cerrada.'; } catch(PDOException $e){}
    header("Location: necesidades_comprador.php"); exit;
}
if (isset($_GET['eliminar'])) {
    try { $pdo->prepare("DELETE FROM necesidades WHERE id=? AND id_comprador=?")->execute([(int)$_GET['eliminar'],$id]); $_SESSION['flash_ok']='Necesidad eliminada.'; } catch(PDOException $e){}
    header("Location: necesidades_comprador.php"); exit;
}
try { $st=$pdo->prepare("SELECT * FROM necesidades WHERE id_comprador=? ORDER BY fecha_creacion DESC"); $st->execute([$id]); $necesidades=$st->fetchAll(); } catch(PDOException $e){ $necesidades=[]; }
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mis Necesidades — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head><body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_comprador.php'; ?>
<div class="main-content">
  <?php if($flash_ok):?><span data-toast data-type="success" data-msg="<?=htmlspecialchars($flash_ok)?>"></span><?php endif;?>
  <?php if($flash_err):?><span data-toast data-type="error" data-msg="<?=htmlspecialchars($flash_err)?>"></span><?php endif;?>
  <div class="page-header">
    <div><h1 class="page-title">📌 Mis Necesidades</h1><p class="page-subtitle">Publica lo que buscas y recibe ofertas de vendedores</p></div>
  </div>
  <div style="display:grid;grid-template-columns:380px 1fr;gap:1.5rem;align-items:start">
    <!-- Form -->
    <div class="card" style="position:sticky;top:1rem">
      <div class="card-header"><span class="card-title">✏️ Nueva necesidad</span></div>
      <div class="card-body">
        <form method="POST" style="display:flex;flex-direction:column;gap:1.1rem">
          <div>
            <label class="form-label">Título de tu necesidad *</label>
            <input type="text" name="titulo" class="form-control" placeholder="Ej: Necesito laptop para trabajo remoto" required maxlength="255">
          </div>
          <div>
            <label class="form-label">Descripción detallada *</label>
            <textarea name="descripcion" class="form-control" rows="4" required placeholder="Describe especificaciones, condiciones, presupuesto aproximado..."></textarea>
          </div>
          <button type="submit" name="publicar" class="btn btn-primary btn-lg">📌 Publicar necesidad</button>
          <p style="font-size:.76rem;color:var(--text-muted);text-align:center">Los vendedores podrán ver tu necesidad y enviarte ofertas con su precio</p>
        </form>
      </div>
    </div>
    <!-- List -->
    <div>
      <h3 style="font-family:var(--font-display);font-size:.95rem;font-weight:700;margin-bottom:1rem">Mis necesidades publicadas (<?=count($necesidades)?>)</h3>
      <?php if(empty($necesidades)):?>
        <div class="empty-state"><div class="empty-icon">📌</div><h3>Sin necesidades publicadas</h3><p>Publica tu primera necesidad para que los vendedores te envíen ofertas.</p></div>
      <?php else: foreach($necesidades as $n): $e=$n['estado']??'abierta';?>
        <div class="card" style="margin-bottom:.85rem;opacity:<?=$e==='cerrada'?.7:1?>">
          <div class="card-body" style="padding:1.25rem 1.4rem">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:.6rem">
              <div style="font-weight:700;font-size:.95rem"><?=htmlspecialchars($n['titulo'])?></div>
              <span class="badge <?=$e==='abierta'?'badge-success':'badge-danger'?>"><?=ucfirst($e)?></span>
            </div>
            <p style="font-size:.85rem;color:var(--text-muted);line-height:1.65;margin-bottom:.85rem"><?=htmlspecialchars(substr($n['descripcion'],0,200))?><?=strlen($n['descripcion'])>200?'…':''?></p>
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
              <span style="font-size:.76rem;color:var(--text-dim)"><?=date('d M Y',strtotime($n['fecha_creacion']))?></span>
              <div style="display:flex;gap:.4rem">
                <?php if($e==='abierta'):?>
                  <a href="?cerrar=<?=$n['id']?>" class="btn btn-sm btn-outline" onclick="var h=this.href;event.preventDefault();RM.confirm('¿Cerrar esta necesidad?',function(){window.location.href=h})">Cerrar</a>
                <?php endif;?>
                <a href="?eliminar=<?=$n['id']?>" class="btn btn-sm btn-danger" onclick="var h=this.href;event.preventDefault();RM.confirm('¿Eliminar esta necesidad?',function(){window.location.href=h})">🗑</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; endif;?>
    </div>
  </div>
</div></div></body></html>
