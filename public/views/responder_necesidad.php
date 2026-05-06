<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'vendedor') {
    header("Location: login.php"); exit;
}
$id_necesidad = (int)($_GET['id'] ?? $_GET['id_necesidad'] ?? 0);
$id_vendedor  = $_SESSION['usuario_id'];
$msg = $msg_type = '';

// Get the necesidad with comprador info
try {
    $nec = $pdo->prepare("SELECT n.*, u.nombre AS comprador_nombre, u.id AS id_comprador FROM necesidades n JOIN usuarios u ON n.id_comprador = u.id WHERE n.id = ?");
    $nec->execute([$id_necesidad]);
    $necesidad = $nec->fetch();
    if (!$necesidad) { header("Location: ver_necesidades.php"); exit; }

    // Vendedor's products
    $prods = $pdo->prepare("SELECT id, nombre, precio, imagen FROM productos WHERE id_vendedor=? ORDER BY nombre");
    $prods->execute([$id_vendedor]);
    $productos = $prods->fetchAll();
} catch (PDOException $e) {
    die('<p style="color:red;font-family:sans-serif;padding:2rem">Error: ' . htmlspecialchars($e->getMessage()) . '</p>');
}

if (isset($_POST['responder'])) {
    $id_producto = (int)($_POST['id_producto'] ?? 0);
    try {
        $check = $pdo->prepare("SELECT id FROM productos WHERE id=? AND id_vendedor=?");
        $check->execute([$id_producto, $id_vendedor]);
        if ($check->fetch()) {
            $pdo->prepare("INSERT IGNORE INTO respuesta_necesidad (id_necesidad, id_producto) VALUES (?,?)")
                ->execute([$id_necesidad, $id_producto]);
            // Notify buyer — safely
            $id_comprador = (int)$necesidad['id_comprador'];
            if ($id_comprador > 0) {
                try {
                    $pdo->prepare("INSERT INTO notificaciones (mensaje, id_usuario_destino, tipo) VALUES (?,?,'oferta')")
                        ->execute(["El vendedor {$_SESSION['nombre']} respondió a tu necesidad: {$necesidad['titulo']}", $id_comprador]);
                } catch (PDOException $ignored) {}
            }
            $msg = '✅ ¡Propuesta enviada exitosamente al comprador!';
            $msg_type = 'ok';
        } else {
            $msg = '⚠ El producto no pertenece a tu tienda.';
            $msg_type = 'err';
        }
    } catch (PDOException $e) {
        $msg = '⚠ Error al enviar: ' . htmlspecialchars($e->getMessage());
        $msg_type = 'err';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Responder Necesidad — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_vendedor.php'; ?>
<div class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title">Responder Necesidad</h1>
      <p class="page-subtitle">Propón uno de tus productos para esta necesidad</p>
    </div>
    <a href="ver_necesidades.php" class="btn btn-outline">← Volver</a>
  </div>

  <?php if ($msg): ?>
    <span data-toast data-type="success" data-msg="<?= htmlspecialchars($flash_ok ?? $msg ?? $flash ?? '') ?>"></span>
  <?php endif; ?>

  <!-- Necesidad info -->
  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.75rem;margin-bottom:1.5rem">
    <div style="font-size:.75rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--accent);margin-bottom:.6rem">Necesidad del comprador</div>
    <h2 style="font-family:var(--font-display);font-size:1.2rem;font-weight:700;margin-bottom:.6rem"><?= htmlspecialchars($necesidad['titulo']) ?></h2>
    <p style="color:var(--text-muted);line-height:1.7;font-size:.93rem"><?= nl2br(htmlspecialchars($necesidad['descripcion'])) ?></p>
    <div style="margin-top:1rem;font-size:.82rem;color:var(--text-dim)">
      Publicado por <strong style="color:var(--text-muted)"><?= htmlspecialchars($necesidad['comprador_nombre']) ?></strong>
      · <?= date('d M Y', strtotime($necesidad['fecha_creacion'])) ?>
    </div>
  </div>

  <!-- Form -->
  <?php if (empty($productos)): ?>
    <div class="empty-state">
      <div class="empty-icon">📦</div>
      <h3>Sin productos para proponer</h3>
      <p>Agrega un producto primero para poder responder necesidades.</p>
      <a href="agregar_producto.php" class="btn btn-primary">Agregar producto</a>
    </div>
  <?php else: ?>
  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.75rem">
    <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:1.25rem">Selecciona el producto a proponer</h3>
    <form method="POST">
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem">
        <?php foreach ($productos as $p): ?>
        <label style="cursor:pointer">
          <input type="radio" name="id_producto" value="<?= $p['id'] ?>" required style="display:none" class="prod-radio">
          <div class="prod-option" style="background:var(--bg-panel);border:2px solid var(--border);border-radius:var(--radius-sm);padding:1rem;transition:all .2s;text-align:center"
               onclick="selectProd(this)">
            <div style="font-size:2rem;margin-bottom:.5rem">
              <?php if ($p['imagen']): ?>
                <?php if(!empty($p['imagen']) && file_exists(__DIR__ . '/../../public/uploads/' . $p['imagen'])): ?>
                <img src="../uploads/<?= htmlspecialchars($p['imagen']) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:8px" alt="">
                <?php else: ?><span style='font-size:1.8rem;display:inline-flex;align-items:center;justify-content:center;width:100%;height:100%'>📦</span><?php endif; ?>
              <?php else: ?>📦<?php endif; ?>
            </div>
            <div style="font-weight:600;font-size:.88rem;margin-bottom:.25rem"><?= htmlspecialchars($p['nombre']) ?></div>
            <div style="color:var(--accent);font-weight:800;font-family:var(--font-display)">$<?= number_format($p['precio'],2) ?></div>
          </div>
        </label>
        <?php endforeach; ?>
      </div>
      <input type="hidden" name="responder" value="1">
      <button type="submit" class="btn btn-primary" style="padding:.85rem 2rem;font-size:1rem">Enviar propuesta →</button>
    </form>
  </div>
  <?php endif; ?>
</div>
</div>
<script>
function selectProd(el) {
  document.querySelectorAll('.prod-option').forEach(d => {
    d.style.borderColor = 'var(--border)'; d.style.background = 'var(--bg-panel)';
  });
  el.style.borderColor = 'var(--accent)';
  el.style.background = 'rgba(0,255,200,.06)';
}
</script>
</body>
</html>
