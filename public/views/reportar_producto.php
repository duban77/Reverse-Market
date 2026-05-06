<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') { header("Location: login.php"); exit; }
$id = $_SESSION['usuario_id'];
$flash_ok = $_SESSION['flash_ok'] ?? ''; unset($_SESSION['flash_ok']);
$flash_err = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pid    = (int)($_POST['producto_id'] ?? 0);
    $motivo = trim($_POST['motivo'] ?? '');
    $detalle= trim($_POST['detalle'] ?? '');
    $full   = $detalle ? "$motivo: $detalle" : $motivo;
    if ($pid && $motivo) {
        try {
            $pdo->prepare("INSERT INTO reportes (producto_id,usuario_id,motivo,estado) VALUES (?,?,?,'pendiente')")->execute([$pid,$id,$full]);
            $_SESSION['flash_ok']='✅ Reporte enviado. El equipo lo revisará pronto.';
            header("Location: reportar_producto.php"); exit;
        } catch(PDOException $e){ $flash_err=$e->getMessage(); }
    } else { $flash_err='Selecciona un producto y el motivo del reporte.'; }
}
try { $prods=$pdo->query("SELECT p.*,u.nombre AS vendedor FROM productos p JOIN usuarios u ON p.id_vendedor=u.id ORDER BY p.nombre")->fetchAll(); } catch(PDOException $e){ $prods=[]; }
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reportar Producto — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head><body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_comprador.php'; ?>
<div class="main-content">
  <?php if($flash_ok):?><span data-toast data-type="success" data-msg="<?=htmlspecialchars($flash_ok)?>"></span><?php endif;?>
  <?php if($flash_err):?><span data-toast data-type="error" data-msg="<?=htmlspecialchars($flash_err)?>"></span><?php endif;?>
  <div class="page-header">
    <div><h1 class="page-title">🚩 Reportar Producto</h1><p class="page-subtitle">Ayúdanos a mantener la plataforma segura y confiable</p></div>
    <a href="home_comprador.php" class="btn btn-outline">← Volver</a>
  </div>
  <div style="max-width:580px">
    <div class="card">
      <div class="card-header"><span class="card-title">📋 Formulario de reporte</span></div>
      <div class="card-body">
        <form method="POST" style="display:flex;flex-direction:column;gap:1.2rem">
          <div>
            <label class="form-label">Producto a reportar *</label>
            <select name="producto_id" class="form-control" required>
              <option value="">Selecciona un producto...</option>
              <?php foreach($prods as $p):?>
                <option value="<?=$p['id']?>"><?=htmlspecialchars($p['nombre'])?> — por <?=htmlspecialchars($p['vendedor'])?></option>
              <?php endforeach;?>
            </select>
          </div>
          <div>
            <label class="form-label">Motivo del reporte *</label>
            <select name="motivo" class="form-control" required>
              <option value="">Selecciona el tipo de problema...</option>
              <option>Producto falso o engañoso</option>
              <option>Precio abusivo o estafa</option>
              <option>Contenido inapropiado</option>
              <option>Vendedor no responde</option>
              <option>Producto no disponible</option>
              <option>Otro problema</option>
            </select>
          </div>
          <div>
            <label class="form-label">Descripción adicional</label>
            <textarea name="detalle" class="form-control" rows="4" placeholder="Describe el problema con más detalle..."></textarea>
          </div>
          <div style="background:rgba(255,190,11,.06);border:1px solid rgba(255,190,11,.15);border-radius:var(--radius-sm);padding:.85rem 1rem;font-size:.82rem;color:var(--warning)">
            ⚠️ Los reportes falsos pueden resultar en la suspensión de tu cuenta. Solo reporta problemas reales.
          </div>
          <button type="submit" class="btn btn-danger btn-lg" style="background:rgba(255,77,109,.15);color:var(--danger);border:1px solid rgba(255,77,109,.3)">🚩 Enviar reporte</button>
        </form>
      </div>
    </div>
  </div>
</div></div></body></html>
