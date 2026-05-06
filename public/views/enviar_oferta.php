<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'vendedor') {
    header("Location: login.php"); exit;
}

$id_vendedor  = $_SESSION['usuario_id'];
$id_solicitud = (int)($_GET['solicitud'] ?? $_GET['id_solicitud'] ?? 0);
$flash_ok = $flash_err = '';

// Auto-add missing columns
try { $pdo->exec("ALTER TABLE ofertas ADD COLUMN IF NOT EXISTS id_vendedor INT NULL"); } catch(PDOException $e){}
try { $pdo->exec("ALTER TABLE ofertas ADD COLUMN IF NOT EXISTS mensaje TEXT NULL"); } catch(PDOException $e){}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar'])) {
    $id_producto  = (int)($_POST['id_producto'] ?? 0);
    $id_comprador = (int)($_POST['id_comprador'] ?? 0);
    $precio       = (float)($_POST['precio_oferta'] ?? 0);
    $mensaje      = trim($_POST['mensaje'] ?? '');
    $id_sol       = (int)($_POST['id_solicitud'] ?? 0);

    if (!$id_producto || $precio <= 0 || !$id_comprador) {
        $flash_err = 'Completa todos los campos requeridos.';
    } else {
        try {
            // Check not already offered this product for this solicitud
            $exists = $pdo->prepare("SELECT id FROM ofertas WHERE id_vendedor=? AND id_comprador=? AND id_producto=?");
            $exists->execute([$id_vendedor, $id_comprador, $id_producto]);
            if ($exists->fetch()) {
                $flash_err = 'Ya enviaste una oferta con este producto a este comprador.';
            } else {
                $pdo->prepare("INSERT INTO ofertas (id_producto, id_comprador, id_vendedor, precio_oferta, mensaje, estado)
                               VALUES (?,?,?,?,?,'pendiente')")
                    ->execute([$id_producto, $id_comprador, $id_vendedor, $precio, $mensaje]);

                // Notify buyer
                $prod = $pdo->prepare("SELECT nombre FROM productos WHERE id=?");
                $prod->execute([$id_producto]);
                $prod = $prod->fetch();
                try {
                    $pdo->prepare("INSERT INTO notificaciones (mensaje, id_usuario_destino, tipo) VALUES (?,?,'oferta')")
                        ->execute(["💼 {$_SESSION['nombre']} te envió una oferta: " . ($prod['nombre']??''), $id_comprador]);
                } catch(PDOException $ignored){}

                $_SESSION['flash_ok'] = '✅ Oferta enviada al comprador exitosamente.';
                header("Location: mis_ofertas.php"); exit;
            }
        } catch (PDOException $e) {
            $flash_err = 'Error al enviar: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Load solicitud
$solicitud = null;
if ($id_solicitud) {
    try {
        $st = $pdo->prepare("SELECT s.*, u.nombre AS comprador, u.id AS id_comprador FROM solicitudes s JOIN usuarios u ON s.id_comprador=u.id WHERE s.id=?");
        $st->execute([$id_solicitud]);
        $solicitud = $st->fetch();
    } catch(PDOException $e){}
}

// Load vendor's products
try {
    $misProductos = $pdo->prepare("SELECT * FROM productos WHERE id_vendedor=? ORDER BY nombre");
    $misProductos->execute([$id_vendedor]);
    $misProductos = $misProductos->fetchAll();
} catch(PDOException $e) { $misProductos = []; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Enviar Oferta — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_vendedor.php'; ?>
<div class="main-content">
  <?php if ($flash_err): ?><span data-toast data-type="error" data-msg="<?= htmlspecialchars($flash_err) ?>"></span><?php endif; ?>

  <div class="page-header">
    <div><h1 class="page-title">📤 Enviar Oferta</h1><p class="page-subtitle">Propón tu precio y producto al comprador</p></div>
    <a href="lista_solicitudes.php" class="btn btn-outline">← Volver</a>
  </div>

  <?php if ($solicitud): ?>
  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;margin-bottom:1.5rem">
    <div style="font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--accent);margin-bottom:.75rem">Solicitud del comprador</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1.25rem">
      <div><div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.2rem">Comprador</div><div style="font-weight:700"><?= htmlspecialchars($solicitud['comprador']) ?></div></div>
      <div><div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.2rem">Presupuesto máximo</div><div style="font-weight:700;color:var(--accent)">$<?= number_format($solicitud['precio'],2) ?></div></div>
      <div><div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.2rem">Entrega</div><div style="font-weight:700"><?= $solicitud['tiempo_entrega'] ?? '—' ?> días</div></div>
    </div>
    <?php if (!empty($solicitud['condiciones'])): ?>
    <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border)">
      <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.35rem">Condiciones del comprador</div>
      <p style="font-size:.9rem;color:var(--text-primary);line-height:1.6"><?= htmlspecialchars($solicitud['condiciones']) ?></p>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.75rem;max-width:580px">
    <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:1.5rem">Tu propuesta</h3>
    <form method="POST">
      <input type="hidden" name="enviar" value="1">
      <input type="hidden" name="id_solicitud" value="<?= $id_solicitud ?>">
      <input type="hidden" name="id_comprador" value="<?= htmlspecialchars($solicitud['id_comprador'] ?? '') ?>">

      <div style="margin-bottom:1.2rem">
        <label class="form-label">Producto a ofrecer *</label>
        <?php if (empty($misProductos)): ?>
          <div style="padding:1rem;background:var(--bg-panel);border-radius:var(--radius-sm);color:var(--text-muted);font-size:.88rem">
            No tienes productos. <a href="agregar_producto.php" style="color:var(--accent)">Agrega uno primero →</a>
          </div>
        <?php else: ?>
        <select name="id_producto" class="form-control" required style="color-scheme:dark" onchange="updatePrice(this)">
          <option value="">Selecciona un producto...</option>
          <?php foreach ($misProductos as $p): ?>
          <option value="<?= $p['id'] ?>" data-precio="<?= $p['precio'] ?>">
            <?= htmlspecialchars($p['nombre']) ?> — Precio base: $<?= number_format($p['precio'],2) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <?php endif; ?>
      </div>

      <div style="margin-bottom:1.2rem">
        <label class="form-label">Tu precio de oferta (COP) *</label>
        <div style="position:relative">
          <span style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:700">$</span>
          <input type="number" name="precio_oferta" id="precioInput" class="form-control" style="padding-left:2rem"
                 placeholder="0.00" step="100" min="1" required>
        </div>
        <?php if ($solicitud && !empty($solicitud['precio'])): ?>
          <div style="font-size:.78rem;color:var(--text-muted);margin-top:.3rem">Presupuesto del comprador: <strong style="color:var(--accent)">$<?= number_format($solicitud['precio'],2) ?></strong></div>
        <?php endif; ?>
      </div>

      <div style="margin-bottom:1.5rem">
        <label class="form-label">Mensaje al comprador (opcional)</label>
        <textarea name="mensaje" class="form-control" rows="3"
          placeholder="Ej: Puedo entregar en 3 días, incluye garantía de 6 meses..."
          style="resize:vertical"></textarea>
      </div>

      <?php if (!empty($misProductos)): ?>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:.9rem;font-size:1rem;font-family:var(--font-display);font-weight:800">
        📤 Enviar oferta al comprador
      </button>
      <?php endif; ?>
    </form>
  </div>
</div>
</div>
<script>
function updatePrice(sel) {
  const precio = sel.options[sel.selectedIndex]?.dataset?.precio;
  if (precio) document.getElementById('precioInput').value = precio;
}
</script>
</body>
</html>
