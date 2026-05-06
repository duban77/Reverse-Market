<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'vendedor') { header("Location: login.php"); exit; }
$id = $_SESSION['usuario_id'];
$pid = (int)($_GET['id'] ?? 0);
$flash_err = '';
try { $st=$pdo->prepare("SELECT * FROM productos WHERE id=? AND id_vendedor=?");$st->execute([$pid,$id]);$prod=$st->fetch(); } catch(PDOException $e){ $prod=null; }
if (!$prod) { $_SESSION['flash_error']='Producto no encontrado.'; header("Location: home_vendedor.php"); exit; }

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $desc   = trim($_POST['descripcion'] ?? '');
    $precio = (float)str_replace(',','.',$_POST['precio'] ?? 0);
    $cat    = trim($_POST['categoria'] ?? '');
    $imagen = $prod['imagen'];
    if (!$nombre || $precio <= 0) { $flash_err='El nombre y el precio son requeridos.'; }
    else {
        if (!empty($_FILES['imagen']['name'])) {
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            if (in_array($ext,['jpg','jpeg','png','webp','gif'])) {
                $fname = 'prod_' . uniqid() . '.' . $ext;
                $dest  = __DIR__ . '/../../public/uploads/' . $fname;
                if (!is_dir(dirname($dest))) mkdir(dirname($dest),0775,true);
                if (move_uploaded_file($_FILES['imagen']['tmp_name'],$dest)) $imagen=$fname;
            }
        }
        try {
            $pdo->prepare("UPDATE productos SET nombre=?,descripcion=?,precio=?,categoria=?,imagen=? WHERE id=? AND id_vendedor=?")->execute([$nombre,$desc,$precio,$cat,$imagen,$pid,$id]);
            $_SESSION['flash_ok']='✅ Producto actualizado.'; header("Location: home_vendedor.php"); exit;
        } catch(PDOException $e){ $flash_err=$e->getMessage(); }
    }
}
$categorias=['Electrónica','Ropa','Hogar','Deportes','Alimentos','Servicios','Tecnología','Arte','Otros'];
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Editar Producto — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
<style>.img-preview{width:100%;height:160px;border-radius:var(--radius-sm);border:2px dashed var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;overflow:hidden}.img-preview img{width:100%;height:100%;object-fit:cover}</style>
</head><body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_vendedor.php'; ?>
<div class="main-content">
  <?php if($flash_err):?><span data-toast data-type="error" data-msg="<?=htmlspecialchars($flash_err)?>"></span><?php endif;?>
  <div class="page-header"><div><h1 class="page-title">✏️ Editar Producto</h1><p class="page-subtitle">Modifica los datos de tu producto</p></div><a href="home_vendedor.php" class="btn btn-outline">← Volver</a></div>
  <div style="max-width:680px">
    <form method="POST" enctype="multipart/form-data">
      <div class="card">
        <div class="card-header"><span class="card-title">📦 Datos del producto</span></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:1.2rem">
          <div>
            <label class="form-label">Imagen actual</label>
            <div class="img-preview" id="imgPreview" onclick="document.getElementById('imgFile').click()">
              <?php if(!empty($prod['imagen'])&&file_exists(__DIR__.'/../../public/uploads/'.$prod['imagen'])):?>
                <img src="../uploads/<?=htmlspecialchars($prod['imagen'])?>" id="imgEl">
              <?php else:?><div style="text-align:center"><div style="font-size:2rem">📷</div><div style="font-size:.78rem;color:var(--text-dim)">Clic para cambiar imagen</div></div><?php endif;?>
            </div>
            <input type="file" name="imagen" id="imgFile" accept="image/*" style="display:none" onchange="previewImg(this)">
          </div>
          <div><label class="form-label">Nombre *</label><input type="text" name="nombre" class="form-control" value="<?=htmlspecialchars($prod['nombre'])?>" required></div>
          <div><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control" rows="3"><?=htmlspecialchars($prod['descripcion']??'')?></textarea></div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div><label class="form-label">Precio (COP) *</label>
              <div style="position:relative"><span style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:700">$</span>
              <input type="number" name="precio" class="form-control" style="padding-left:2rem" value="<?=$prod['precio']?>" min="100" required></div>
            </div>
            <div><label class="form-label">Categoría</label>
              <select name="categoria" class="form-control">
                <option value="">Sin categoría</option>
                <?php foreach($categorias as $c):?><option <?=$prod['categoria']===$c?'selected':''?>><?=$c?></option><?php endforeach;?>
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-lg">💾 Guardar cambios</button>
        </div>
      </div>
    </form>
  </div>
</div></div>
<script>function previewImg(input){if(input.files&&input.files[0]){const r=new FileReader();r.onload=e=>{document.getElementById('imgPreview').innerHTML='<img src="'+e.target.result+'">';};r.readAsDataURL(input.files[0]);}}</script>
</body></html>
