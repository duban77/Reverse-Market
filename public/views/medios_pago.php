<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'vendedor') { header("Location: login.php"); exit; }
$id = $_SESSION['usuario_id'];
$flash_ok = $_SESSION['flash_ok'] ?? ''; unset($_SESSION['flash_ok']);
$flash_err = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['agregar'])) {
    $metodo = trim($_POST['metodo_pago'] ?? '');
    $numero = trim($_POST['numero_cuenta'] ?? '');
    $desc   = trim($_POST['descripcion'] ?? '');
    if ($metodo && $numero) {
        try {
            $pdo->prepare("INSERT INTO medios_pago (id_vendedor,metodo_pago,numero_cuenta,descripcion) VALUES (?,?,?,?)")
                ->execute([$id,$metodo,$numero,$desc]);
            $_SESSION['flash_ok'] = 'Medio de pago agregado.';
        } catch(PDOException $e){ $_SESSION['flash_error']=$e->getMessage(); }
    } else { $_SESSION['flash_error']='Completa todos los campos requeridos.'; }
    header("Location: medios_pago.php"); exit;
}
if (isset($_GET['eliminar'])) {
    try{ $pdo->prepare("DELETE FROM medios_pago WHERE id=? AND id_vendedor=?")->execute([(int)$_GET['eliminar'],$id]); $_SESSION['flash_ok']='Eliminado.'; } catch(PDOException $e){}
    header("Location: medios_pago.php"); exit;
}
$medios = [];
try { $st=$pdo->prepare("SELECT * FROM medios_pago WHERE id_vendedor=? ORDER BY id DESC"); $st->execute([$id]); $medios=$st->fetchAll(); } catch(PDOException $e){}
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Medios de Pago — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head><body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_vendedor.php'; ?>
<div class="main-content">
  <?php if($flash_ok):?><span data-toast data-type="success" data-msg="<?=htmlspecialchars($flash_ok)?>"></span><?php endif;?>
  <?php if($flash_err):?><span data-toast data-type="error" data-msg="<?=htmlspecialchars($flash_err)?>"></span><?php endif;?>
  <div class="page-header">
    <div><h1 class="page-title">💳 Medios de Pago</h1><p class="page-subtitle">Configura cómo quieres recibir pagos de tus compradores</p></div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start">
    <!-- Form -->
    <div class="card">
      <div class="card-header"><span class="card-title">➕ Agregar medio de pago</span></div>
      <div class="card-body">
        <form method="POST" style="display:flex;flex-direction:column;gap:1.1rem">
          <div>
            <label class="form-label">Método de pago *</label>
            <select name="metodo_pago" class="form-control" required>
              <option value="">Selecciona...</option>
              <option>Nequi</option><option>Daviplata</option>
              <option>Transferencia bancaria</option><option>Bancolombia</option>
              <option>Efectivo</option><option>Otro</option>
            </select>
          </div>
          <div>
            <label class="form-label">Número de cuenta / ID *</label>
            <input type="text" name="numero_cuenta" class="form-control" placeholder="Ej: 3001234567" required>
          </div>
          <div>
            <label class="form-label">Descripción adicional</label>
            <textarea name="descripcion" class="form-control" rows="2" placeholder="Instrucciones adicionales para el comprador..."></textarea>
          </div>
          <button type="submit" name="agregar" class="btn btn-primary">+ Agregar medio de pago</button>
        </form>
      </div>
    </div>
    <!-- List -->
    <div>
      <h3 style="font-family:var(--font-display);font-size:.95rem;font-weight:700;margin-bottom:1rem">Mis medios registrados (<?=count($medios)?>)</h3>
      <?php if(empty($medios)):?>
        <div class="empty-state" style="padding:2rem"><div class="empty-icon">💳</div><h3>Sin medios aún</h3><p>Agrega tu primer método de pago.</p></div>
      <?php else: foreach($medios as $m):?>
        <div class="card" style="margin-bottom:.75rem">
          <div class="card-body" style="padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem">
            <div style="display:flex;align-items:center;gap:.85rem">
              <div style="width:40px;height:40px;border-radius:10px;background:rgba(0,255,200,.1);border:1px solid rgba(0,255,200,.2);display:flex;align-items:center;justify-content:center;font-size:1.3rem">💳</div>
              <div>
                <div style="font-weight:700;font-size:.9rem"><?=htmlspecialchars($m['metodo_pago']??'')?></div>
                <div style="font-size:.82rem;color:var(--accent)"><?=htmlspecialchars($m['numero_cuenta']??'')?></div>
                <?php if(!empty($m['descripcion'])):?><div style="font-size:.76rem;color:var(--text-muted)"><?=htmlspecialchars($m['descripcion'])?></div><?php endif;?>
              </div>
            </div>
            <a href="?eliminar=<?=$m['id']?>" class="btn btn-danger btn-sm" onclick="var h=this.href;event.preventDefault();RM.confirm('¿Eliminar este medio de pago?',function(){window.location.href=h})">🗑</a>
          </div>
        </div>
      <?php endforeach; endif;?>
    </div>
  </div>
</div></div></body></html>
