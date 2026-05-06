<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') {
    header("Location: login.php"); exit;
}
$id = $_SESSION['usuario_id'];
$id_necesidad = (int)($_GET['id_necesidad'] ?? 0);
$flash_ok = $_SESSION['flash_ok'] ?? ''; unset($_SESSION['flash_ok']);

// Obtain buyer's necesidades
try {
    $mis = $pdo->prepare("SELECT * FROM necesidades WHERE id_comprador=? ORDER BY fecha_creacion DESC");
    $mis->execute([$id]);
    $misNecesidades = $mis->fetchAll();

    $respuestas = [];
    $necesidad  = null;
    if ($id_necesidad) {
        $stN = $pdo->prepare("SELECT * FROM necesidades WHERE id=? AND id_comprador=?");
        $stN->execute([$id_necesidad, $id]);
        $necesidad = $stN->fetch();

        if ($necesidad) {
            $stR = $pdo->prepare("
                SELECT rn.*, p.nombre AS prod_nombre, p.precio, p.descripcion AS prod_desc,
                       p.imagen, p.id AS pid,
                       u.nombre AS vendedor, u.id AS id_vendedor,
                       COALESCE((SELECT AVG(puntuacion) FROM calificaciones WHERE id_vendedor=u.id),0) AS rating,
                       (SELECT COUNT(*) FROM carrito WHERE id_comprador=? AND id_producto=p.id) AS en_carrito
                FROM respuesta_necesidad rn
                JOIN productos p ON rn.id_producto = p.id
                JOIN usuarios u ON p.id_vendedor = u.id
                WHERE rn.id_necesidad = ?
            ");
            $stR->execute([$id, $id_necesidad]);
            $respuestas = $stR->fetchAll();
        }
    }
    $nCount = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario_destino=? AND leido=0");
    $nCount->execute([$id]); $nCount = $nCount->fetchColumn();
    $cartCount = $pdo->prepare("SELECT COALESCE(SUM(cantidad),0) FROM carrito WHERE id_comprador=?");
    $cartCount->execute([$id]); $cartCount = (int)$cartCount->fetchColumn();
} catch (PDOException $e) {
    $misNecesidades = []; $respuestas = []; $necesidad = null; $nCount = 0; $cartCount = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Respuestas a mis Necesidades — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
<style>
.resp-card {
  background:var(--bg-card); border:1px solid var(--border);
  border-radius:var(--radius); overflow:hidden; display:flex;
  flex-direction:column; transition:var(--transition);
}
.resp-card:hover { border-color:rgba(0,255,200,.35); transform:translateY(-3px); box-shadow:var(--shadow-accent); }
.resp-img { width:100%; height:160px; background:var(--bg-panel); display:flex; align-items:center; justify-content:center; font-size:2.5rem; overflow:hidden; }
.resp-img img { width:100%; height:160px; object-fit:cover; }
.resp-body { padding:1.1rem; flex:1; display:flex; flex-direction:column; }
.resp-name { font-weight:700; font-size:.97rem; margin-bottom:.2rem; }
.resp-price { color:var(--accent); font-weight:800; font-size:1.15rem; font-family:var(--font-display); margin-bottom:.35rem; }
.resp-vendor { font-size:.78rem; color:var(--text-muted); margin-bottom:.5rem; }
.stars-row { color:#ffbe0b; font-size:.85rem; margin-bottom:.75rem; }
.btn-add { width:100%; padding:.65rem; border-radius:var(--radius-sm); font-size:.88rem; font-weight:700; cursor:pointer; border:none; font-family:var(--font-body); transition:all .2s; margin-bottom:.5rem; }
.btn-add.not-in { background:var(--accent); color:#05080f; }
.btn-add.not-in:hover { box-shadow:0 4px 16px rgba(0,255,200,.35); }
.btn-add.in-cart { background:rgba(0,255,200,.12); color:var(--accent); border:1px solid var(--accent); cursor:default; }
.nec-list-item { background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:1.1rem 1.4rem; display:flex; align-items:center; gap:1rem; margin-bottom:.75rem; transition:var(--transition); }
.nec-list-item:hover { border-color:rgba(0,255,200,.25); }
</style>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_comprador.php'; ?>
<div class="main-content">

  <?php if ($flash_ok): ?>
    <span data-toast data-type="success" data-msg="<?= htmlspecialchars($flash_ok) ?>"></span>
  <?php endif; ?>

  <?php if (!$id_necesidad): ?>
    <!-- LIST OF NECESIDADES -->
    <div class="page-header">
      <div>
        <h1 class="page-title">📦 Mis Necesidades</h1>
        <p class="page-subtitle">Selecciona una necesidad para ver las propuestas recibidas</p>
      </div>
      <div style="display:flex;gap:.75rem">
        <a href="carrito.php" class="btn btn-outline" style="position:relative">
          🛒 Carrito
          <?php if ($cartCount > 0): ?><span style="position:absolute;top:-6px;right:-8px;background:var(--danger);color:#fff;font-size:.62rem;font-weight:800;width:17px;height:17px;border-radius:50%;display:flex;align-items:center;justify-content:center"><?= $cartCount ?></span><?php endif; ?>
        </a>
        <a href="necesidades_comprador.php" class="btn btn-primary">+ Nueva necesidad</a>
      </div>
    </div>

    <?php if (empty($misNecesidades)): ?>
      <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>Sin necesidades publicadas</h3>
        <p>Publica tu primera necesidad para que los vendedores te envíen propuestas.</p>
        <a href="necesidades_comprador.php" class="btn btn-primary">Publicar necesidad</a>
      </div>
    <?php else: ?>
      <?php foreach ($misNecesidades as $n): ?>
      <div class="nec-list-item">
        <div style="flex:1">
          <div style="font-weight:700;margin-bottom:.3rem"><?= htmlspecialchars($n['titulo']) ?></div>
          <div style="font-size:.82rem;color:var(--text-muted)"><?= date('d M Y', strtotime($n['fecha_creacion'])) ?></div>
        </div>
        <span style="background:<?= ($n['estado']??'abierta')==='abierta'?'rgba(6,214,160,.12)':'rgba(255,77,109,.12)' ?>;color:<?= ($n['estado']??'abierta')==='abierta'?'var(--success)':'var(--danger)' ?>;padding:.25rem .75rem;border-radius:20px;font-size:.76rem;font-weight:700">
          <?= ($n['estado']??'abierta')==='abierta'?'🟢 Abierta':'🔴 Cerrada' ?>
        </span>
        <a href="?id_necesidad=<?= $n['id'] ?>" class="btn btn-primary" style="font-size:.85rem">Ver propuestas →</a>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>

  <?php else: ?>
    <!-- PROPUESTAS FOR A SPECIFIC NECESIDAD -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Propuestas recibidas</h1>
        <p class="page-subtitle">Para: <strong><?= htmlspecialchars($necesidad['titulo'] ?? '') ?></strong> · <?= count($respuestas) ?> propuesta(s)</p>
      </div>
      <div style="display:flex;gap:.75rem;align-items:center">
        <a href="carrito.php" class="btn btn-outline" style="position:relative">
          🛒 Ver carrito
          <?php if ($cartCount > 0): ?><span style="position:absolute;top:-6px;right:-8px;background:var(--danger);color:#fff;font-size:.62rem;font-weight:800;width:17px;height:17px;border-radius:50%;display:flex;align-items:center;justify-content:center"><?= $cartCount ?></span><?php endif; ?>
        </a>
        <a href="respuestas_necesidades.php" class="btn btn-outline">← Volver</a>
      </div>
    </div>

    <?php if (empty($respuestas)): ?>
      <div class="empty-state">
        <div class="empty-icon">⏳</div>
        <h3>Sin propuestas aún</h3>
        <p>Los vendedores aún no han respondido a esta necesidad. Te notificaremos cuando llegue una propuesta.</p>
      </div>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem">
        <?php foreach ($respuestas as $r): ?>
        <div class="resp-card">
          <div class="resp-img">
            <?php if (!empty($r['imagen']) && file_exists(__DIR__ . '/../../public/uploads/' . $r['imagen'])): ?>
              <img src="../uploads/<?= htmlspecialchars($r['imagen']) ?>" alt="<?= htmlspecialchars($r['prod_nombre']) ?>">
            <?php else: ?>📦<?php endif; ?>
          </div>
          <div class="resp-body">
            <div class="resp-name"><?= htmlspecialchars($r['prod_nombre']) ?></div>
            <div class="resp-price">$<?= number_format($r['precio'], 2) ?></div>
            <div class="resp-vendor">
              por <span style="color:var(--accent)"><?= htmlspecialchars($r['vendedor']) ?></span>
            </div>
            <?php if ($r['rating'] > 0): ?>
              <div class="stars-row">
                <?= str_repeat('★', round($r['rating'])) . str_repeat('☆', 5 - round($r['rating'])) ?>
                <span style="color:var(--text-muted);font-size:.75rem"> <?= number_format($r['rating'],1) ?></span>
              </div>
            <?php endif; ?>
            <?php if (!empty($r['prod_desc'])): ?>
              <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem;line-height:1.5">
                <?= htmlspecialchars(substr($r['prod_desc'], 0, 90)) ?><?= strlen($r['prod_desc'])>90?'…':'' ?>
              </div>
            <?php endif; ?>

            <!-- ADD TO CART BUTTON -->
            <button class="btn-add <?= $r['en_carrito'] ? 'in-cart' : 'not-in' ?>"
                    id="btn-<?= $r['pid'] ?>"
                    <?= $r['en_carrito'] ? 'disabled' : "onclick=\"addToCart({$r['pid']}, this)\"" ?>>
              <?= $r['en_carrito'] ? '✓ Ya está en el carrito' : '🛒 Agregar al carrito' ?>
            </button>

            <a href="ver_perfil.php?id=<?= $r['id_vendedor'] ?>" class="btn btn-outline" style="display:block;text-align:center;font-size:.8rem;padding:.45rem">
              👤 Ver vendedor
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="margin-top:2rem;padding:1.25rem;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
        <div>
          <div style="font-weight:700;margin-bottom:.2rem">¿Listo para comprar?</div>
          <div style="font-size:.85rem;color:var(--text-muted)">Agrega los productos al carrito y completa tu compra de forma segura.</div>
        </div>
        <a href="carrito.php" class="btn btn-primary" style="padding:.75rem 1.75rem">
          Ver mi carrito →
        </a>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
</div>

<script>
const CART_URL = '../controllers/CarritoController.php';

async function addToCart(pid, btn) {
  btn.disabled = true;
  btn.textContent = 'Agregando...';

  const fd = new FormData();
  fd.append('action', 'add');
  fd.append('producto_id', pid);
  fd.append('cantidad', 1);

  try {
    const res  = await fetch(CART_URL, { method: 'POST', body: fd });
    const data = await res.json();

    if (data.ok) {
      btn.textContent = '✓ Ya está en el carrito';
      btn.classList.replace('not-in', 'in-cart');
      // Update all cart badges on page
      document.querySelectorAll('.cart-badge-count').forEach(el => el.textContent = data.count);
      RM.toast('✓ Producto agregado al carrito', 'success', '¡Listo!');
    } else {
      btn.disabled = false;
      btn.textContent = '🛒 Agregar al carrito';
      RM.toast(data.error || 'No se pudo agregar', 'error');
    }
  } catch (e) {
    btn.disabled = false;
    btn.textContent = '🛒 Agregar al carrito';
    RM.toast('Error de conexión', 'error');
  }
}
</script>
</body>
</html>
