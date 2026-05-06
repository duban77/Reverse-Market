<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') {
    header("Location: login.php"); exit;
}
$id = $_SESSION['usuario_id'];

try {
    $items = $pdo->prepare("SELECT c.id AS cid, c.cantidad, p.id AS pid, p.nombre, p.precio, p.imagen, p.categoria,
                                   u.nombre AS vendedor, u.id AS vendedor_id
                            FROM carrito c
                            JOIN productos p ON c.id_producto = p.id
                            JOIN usuarios u ON p.id_vendedor = u.id
                            WHERE c.id_comprador = ?
                            ORDER BY c.fecha DESC");
    $items->execute([$id]);
    $items = $items->fetchAll();
    $total = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $items));
    $nCount = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario_destino=? AND leido=0");
    $nCount->execute([$id]);
    $nCount = $nCount->fetchColumn();
} catch (PDOException $e) {
    $items = []; $total = 0; $nCount = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mi Carrito — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
<style>
.cart-layout { display:grid; grid-template-columns:1fr 340px; gap:1.5rem; align-items:start; }
.cart-item { background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:1.25rem; display:flex; gap:1.25rem; align-items:center; }
.cart-img { width:72px; height:72px; border-radius:var(--radius-sm); object-fit:cover; background:var(--bg-panel); display:flex; align-items:center; justify-content:center; font-size:1.8rem; flex-shrink:0; overflow:hidden; }
.cart-img img { width:72px; height:72px; object-fit:cover; }
.qty-control { display:flex; align-items:center; gap:.5rem; }
.qty-btn { width:30px; height:30px; border-radius:8px; background:var(--bg-panel); border:1px solid var(--border); color:var(--text-primary); cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:1rem; transition:all .2s; }
.qty-btn:hover { border-color:var(--accent); color:var(--accent); }
.qty-val { min-width:32px; text-align:center; font-weight:700; font-size:.95rem; }
.summary-card { background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:1.75rem; position:sticky; top:1.5rem; }
.summary-row { display:flex; justify-content:space-between; margin-bottom:.85rem; font-size:.92rem; }
.summary-row.total { font-family:var(--font-display); font-size:1.2rem; font-weight:800; border-top:1px solid var(--border); padding-top:.85rem; margin-top:.5rem; }
.remove-btn { background:none; border:none; cursor:pointer; color:var(--text-dim); font-size:1.1rem; transition:color .2s; padding:.25rem; }
.remove-btn:hover { color:var(--danger); }
@media(max-width:768px) { .cart-layout { grid-template-columns:1fr; } }
</style>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_comprador.php'; ?>
<div class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title">🛒 Mi Carrito</h1>
      <p class="page-subtitle"><?= count($items) ?> producto<?= count($items)!=1?'s':'' ?> en tu carrito</p>
    </div>
    <a href="home_comprador.php" class="btn btn-outline">← Seguir comprando</a>
  </div>

  <?php if (empty($items)): ?>
    <div class="empty-state">
      <div class="empty-icon">🛒</div>
      <h3>Tu carrito está vacío</h3>
      <p>Explora los productos disponibles y agrega lo que necesitas.</p>
      <a href="home_comprador.php" class="btn btn-primary">Explorar productos</a>
    </div>
  <?php else: ?>
  <div class="cart-layout">
    <!-- Items -->
    <div style="display:flex;flex-direction:column;gap:1rem" id="cart-items">
      <?php foreach ($items as $item): ?>
      <div class="cart-item" id="item-<?= $item['pid'] ?>">
        <div class="cart-img">
          <?php if ($item['imagen']): ?>
            <?php if(!empty($item['imagen']) && file_exists(__DIR__ . '/../../public/uploads/' . $item['imagen'])): ?>
            <?php if(!empty($item["imagen"]) && file_exists(__DIR__ . "/../../public/uploads/" . $item["imagen"])): ?><img src="../uploads/<?= htmlspecialchars($item['imagen']) ?>" alt="" ><?php else: ?>📦<?php endif; ?>
            <?php else: ?><span style='font-size:1.8rem;display:inline-flex;align-items:center;justify-content:center;width:100%;height:100%'>📦</span><?php endif; ?>
          <?php else: ?>📦<?php endif; ?>
        </div>
        <div style="flex:1">
          <div style="font-weight:700;margin-bottom:.2rem;color:var(--text-primary)"><?= htmlspecialchars($item['nombre']) ?></div>
          <div style="font-size:.82rem;color:var(--text-muted);margin-bottom:.6rem">Vendedor: <?= htmlspecialchars($item['vendedor']) ?></div>
          <div class="qty-control">
            <button class="qty-btn" onclick="changeQty(<?= $item['pid'] ?>, -1)">−</button>
            <span class="qty-val" id="qty-<?= $item['pid'] ?>"><?= $item['cantidad'] ?></span>
            <button class="qty-btn" onclick="changeQty(<?= $item['pid'] ?>, 1)">+</button>
          </div>
        </div>
        <div style="text-align:right">
          <div style="font-family:var(--font-display);font-size:1.15rem;font-weight:800;color:var(--accent)" id="price-<?= $item['pid'] ?>">
            $<?= number_format($item['precio'] * $item['cantidad'], 2) ?>
          </div>
          <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.5rem">$<?= number_format($item['precio'],2) ?> c/u</div>
          <button class="remove-btn" onclick="removeItem(<?= $item['pid'] ?>)" title="Eliminar">🗑</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Resumen -->
    <div class="summary-card">
      <h3 style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;margin-bottom:1.25rem">Resumen del pedido</h3>
      <?php foreach ($items as $item): ?>
        <div class="summary-row">
          <span style="color:var(--text-muted)"><?= htmlspecialchars(substr($item['nombre'],0,22)) ?>... ×<?= $item['cantidad'] ?></span>
          <span>$<?= number_format($item['precio']*$item['cantidad'],2) ?></span>
        </div>
      <?php endforeach; ?>
      <div class="summary-row total">
        <span>Total</span>
        <span id="cart-total" style="color:var(--accent)">$<?= number_format($total,2) ?></span>
      </div>
      <!-- Direct form submit - more reliable than AJAX -->
      <form method="POST" action="../controllers/CarritoCheckoutController.php" id="mp-checkout-form">
        <button type="submit" id="mp-pay-btn"
          style="width:100%;margin-top:1rem;padding:.9rem;font-size:1rem;font-family:var(--font-display);font-weight:800;background:linear-gradient(135deg,#009ee3,#007eb9);box-shadow:0 4px 20px rgba(0,158,227,.35);border:none;border-radius:12px;color:#fff;cursor:pointer;transition:all .2s"
          onclick="this.disabled=true;this.innerHTML='⏳ Conectando con MercadoPago...';this.form.submit()">
          🛒 Pagar con MercadoPago
        </button>
      </form>
      <div style="display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;margin-top:.75rem">
        <span style="font-size:.7rem;color:var(--text-dim)">💳 Tarjeta</span>
        <span style="font-size:.7rem;color:var(--text-dim)">·</span>
        <span style="font-size:.7rem;color:var(--text-dim)">🏦 PSE</span>
        <span style="font-size:.7rem;color:var(--text-dim)">·</span>
        <span style="font-size:.7rem;color:var(--text-dim)">📱 Nequi</span>
        <span style="font-size:.7rem;color:var(--text-dim)">·</span>
        <span style="font-size:.7rem;color:var(--text-dim)">🏪 Efecty</span>
      </div>
      <p style="font-size:.72rem;color:var(--text-dim);text-align:center;margin-top:.6rem">🔒 Pago seguro procesado por MercadoPago</p>
    </div>
  </div>
  <?php endif; ?>
</div>
</div>

<!-- Toast -->
<div id="toast" style="position:fixed;bottom:2rem;right:2rem;background:var(--bg-card);border:1px solid var(--accent);border-radius:var(--radius-sm);padding:1rem 1.5rem;color:var(--text-primary);font-size:.9rem;z-index:9999;transform:translateY(100px);opacity:0;transition:all .3s;max-width:320px"></div>

<script>
const BASE = '../controllers/CarritoController.php';
const prices = {<?php foreach($items as $i): ?><?= $i['pid'] ?>:<?= $i['precio'] ?>,<?php endforeach; ?>};
const qtys = {<?php foreach($items as $i): ?><?= $i['pid'] ?>:<?= $i['cantidad'] ?>,<?php endforeach; ?>};

function showToast(msg, ok=true) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.borderColor = ok ? 'var(--accent)' : 'var(--danger)';
  t.style.transform = 'translateY(0)'; t.style.opacity = '1';
  setTimeout(() => { t.style.transform = 'translateY(100px)'; t.style.opacity = '0'; }, 3000);
}

function updateTotal() {
  let total = 0;
  for (const [pid, qty] of Object.entries(qtys)) {
    if (document.getElementById('item-'+pid)) total += prices[pid] * qty;
  }
  document.getElementById('cart-total').textContent = '$' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

async function changeQty(pid, delta) {
  const newQty = Math.max(1, (qtys[pid] || 1) + delta);
  qtys[pid] = newQty;
  document.getElementById('qty-'+pid).textContent = newQty;
  document.getElementById('price-'+pid).textContent = '$' + (prices[pid]*newQty).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  updateTotal();
  const fd = new FormData();
  fd.append('action','update'); fd.append('producto_id',pid); fd.append('cantidad',newQty);
  await fetch(BASE, {method:'POST',body:fd});
}

async function removeItem(pid) {
  const el = document.getElementById('item-'+pid);
  el.style.opacity = '0.3'; el.style.pointerEvents = 'none';
  const fd = new FormData();
  fd.append('action','remove'); fd.append('producto_id',pid);
  await fetch(BASE, {method:'POST',body:fd});
  delete qtys[pid];
  el.remove();
  updateTotal();
  showToast('Producto eliminado del carrito');
  updateCartBadge();
}

async function pagarConMP() {
  const btn = document.getElementById('mp-pay-btn');
  btn.disabled = true;
  btn.innerHTML = '⏳ Conectando con MercadoPago...';

  try {
    const res  = await fetch('../controllers/CarritoMPController.php');
    const data = await res.json();

    if (data.ok && data.init_point) {
      if (data.is_localhost) {
        // On localhost: open MP in new tab, show manual confirmation
        window.open(data.init_point, '_blank');
        btn.disabled = false;
        btn.innerHTML = '🛒 Pagar con MercadoPago';
        showLocalhostModal(data.referencia, data.total, data.items_count);
      } else {
        // Production: redirect directly
        RM.toast('Redirigiendo a MercadoPago...', 'info');
        setTimeout(() => { window.location.href = data.init_point; }, 600);
      }
    } else {
      const errMsg = data.error || 'Error al conectar con MercadoPago';
      RM.toast(errMsg, 'error', 'Error de pago');
      btn.disabled = false;
      btn.innerHTML = '🛒 Pagar con MercadoPago';
    }
  } catch(e) {
    RM.toast('Error de conexión. Intenta nuevamente.', 'error');
    btn.disabled = false;
    btn.innerHTML = '🛒 Pagar con MercadoPago';
  }
}

function showLocalhostModal(ref, total, itemsCount) {
  const ov = document.createElement('div');
  ov.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:10000;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px)';
  ov.innerHTML = `
    <div style="background:#0f1929;border:1px solid rgba(0,255,200,.2);border-radius:16px;padding:2rem;max-width:460px;width:90%;box-shadow:0 24px 64px rgba(0,0,0,.8)">
      <div style="text-align:center;margin-bottom:1.25rem">
        <div style="font-size:2.5rem;margin-bottom:.5rem">🔗</div>
        <h3 style="font-family:'Syne',sans-serif;font-weight:800;margin-bottom:.35rem">MercadoPago abierto</h3>
        <p style="font-size:.85rem;color:#6b7fa3;line-height:1.6">
          Se abrió MercadoPago en una pestaña nueva. Completa el pago allí.<br>
          Cuando termines, ingresa tu comprobante aquí:
        </p>
      </div>
      <div style="background:#060d1c;border-radius:10px;padding:1rem;margin-bottom:1.25rem;font-size:.82rem;color:#6b7fa3">
        <div>Referencia: <strong style="color:#00ffc8">${ref}</strong></div>
        <div style="margin-top:.3rem">Total: <strong style="color:#00ffc8">$${parseFloat(total).toLocaleString('es-CO', {minimumFractionDigits:2})} COP</strong></div>
      </div>
      <div style="margin-bottom:1rem">
        <label style="display:block;font-size:.76rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#6b7fa3;margin-bottom:.4rem">N° de pago / comprobante MP</label>
        <input type="text" id="mp-comprobante" placeholder="Ej: 123456789 (aparece en MercadoPago)" style="width:100%;background:#0b1829;border:1px solid rgba(0,255,200,.2);border-radius:10px;padding:.75rem 1rem;color:#eef2ff;font-family:inherit;font-size:.9rem;box-sizing:border-box">
      </div>
      <div style="display:flex;gap:.75rem">
        <button onclick="this.closest('div[style*=fixed]').remove()" style="flex:1;padding:.7rem;border-radius:10px;background:transparent;border:1px solid rgba(255,255,255,.1);color:#6b7fa3;cursor:pointer;font-family:inherit">Cancelar</button>
        <button onclick="confirmarPagoMP('${ref}')" style="flex:2;padding:.7rem;border-radius:10px;background:#00ffc8;border:none;color:#05080f;cursor:pointer;font-weight:800;font-family:inherit;font-size:.95rem">✅ Confirmar pago</button>
      </div>
    </div>`;
  document.body.appendChild(ov);
  setTimeout(() => document.getElementById('mp-comprobante')?.focus(), 100);
}

async function confirmarPagoMP(ref) {
  const comp = document.getElementById('mp-comprobante')?.value?.trim();
  const fd = new FormData();
  fd.append('action', 'confirmar_mp');
  fd.append('referencia', ref);
  fd.append('comprobante', comp || 'Sin comprobante');
  
  try {
    const res  = await fetch('../controllers/CarritoMPController.php', {method:'POST', body:fd});
    const data = await res.json();
    if (data.ok) {
      document.querySelector('div[style*="position:fixed"][style*="z-index:10000"]')?.remove();
      window.location.href = 'carrito_exitoso.php?status=approved&ref=' + ref + '&manual=1';
    } else {
      RM.toast(data.error || 'Error al confirmar', 'error');
    }
  } catch(e) {
    RM.toast('Error de conexión', 'error');
  }
}

async function updateCartBadge() {
  const res = await fetch(BASE + '?action=count');
  const data = await res.json();
  const badge = document.getElementById('cart-badge');
  if (badge) badge.textContent = data.count > 0 ? data.count : '';
}
</script>
</body>
</html>
