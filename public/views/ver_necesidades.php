<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'vendedor') {
    header("Location: login.php"); exit;
}
$id_vendedor = $_SESSION['usuario_id'];
$flash_ok = $_SESSION['flash_ok'] ?? ''; unset($_SESSION['flash_ok']);
$flash_err = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

// Auto-create table
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS oferta_necesidad (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_necesidad INT NOT NULL, id_vendedor INT NOT NULL, id_comprador INT NOT NULL,
        precio DECIMAL(10,2) NOT NULL, mensaje TEXT, estado ENUM('pendiente','aceptada','rechazada','negociando') DEFAULT 'pendiente',
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_necesidad) REFERENCES necesidades(id) ON DELETE CASCADE,
        FOREIGN KEY (id_vendedor) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (id_comprador) REFERENCES usuarios(id) ON DELETE CASCADE
    )");
} catch(PDOException $e){}

// Send offer on a necesidad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_oferta'])) {
    $id_nec    = (int)$_POST['id_necesidad'];
    $precio    = (float)str_replace(',','.',$_POST['precio']);
    $mensaje   = trim($_POST['mensaje'] ?? '');
    $id_comp   = (int)$_POST['id_comprador'];

    if ($precio <= 0) {
        $_SESSION['flash_error'] = 'El precio debe ser mayor a 0.';
    } else {
        try {
            // Check if already offered
            $existe = $pdo->prepare("SELECT id FROM oferta_necesidad WHERE id_necesidad=? AND id_vendedor=?");
            $existe->execute([$id_nec, $id_vendedor]);
            if ($existe->fetch()) {
                $_SESSION['flash_error'] = 'Ya enviaste una oferta a esta necesidad. Ve a Mis Ofertas para hacer seguimiento.';
            } else {
                $pdo->prepare("INSERT INTO oferta_necesidad (id_necesidad, id_vendedor, id_comprador, precio, mensaje) VALUES (?,?,?,?,?)")
                    ->execute([$id_nec, $id_vendedor, $id_comp, $precio, $mensaje]);
                try {
                    $pdo->prepare("INSERT INTO notificaciones (mensaje, id_usuario_destino, tipo) VALUES (?,?,'oferta')")
                        ->execute(["💼 {$_SESSION['nombre']} te envió una oferta por tu necesidad", $id_comp]);
                } catch(PDOException $ignored){}
                $_SESSION['flash_ok'] = '✅ Oferta enviada. El comprador verá tu propuesta.';
            }
        } catch(PDOException $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
        }
    }
    header("Location: ver_necesidades.php"); exit;
}

$buscar = trim($_GET['q'] ?? '');
try {
    $sql = "SELECT n.*, u.nombre AS comprador, u.id AS id_comprador,
            (SELECT COUNT(*) FROM oferta_necesidad WHERE id_necesidad=n.id AND id_vendedor=?) AS ya_oferte
            FROM necesidades n
            JOIN usuarios u ON n.id_comprador=u.id";
    $params = [$id_vendedor];
    if ($buscar) {
        $sql .= " WHERE (n.titulo LIKE ? OR n.descripcion LIKE ?)";
        $params[] = "%$buscar%"; $params[] = "%$buscar%";
    }
    $sql .= " ORDER BY n.fecha_creacion DESC LIMIT 50";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $necesidades = $stmt->fetchAll();
} catch(PDOException $e) { $necesidades = []; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Necesidades del Mercado — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
<style>
.nec-card { background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:1.5rem; transition:var(--transition); }
.nec-card:hover { border-color:rgba(0,255,200,.25); }
.offer-panel { background:var(--bg-panel); border:1px solid rgba(0,255,200,.15); border-radius:var(--radius-sm); padding:1.25rem; margin-top:1rem; display:none; }
.offer-panel.open { display:block; animation:slideIn .2s ease; }
@keyframes slideIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
.price-input-wrap { position:relative; }
.price-input-wrap span { position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-weight:700; }
.price-input-wrap input { padding-left:2rem; }
</style>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_vendedor.php'; ?>
<div class="main-content">

  <?php if($flash_ok): ?><span data-toast data-type="success" data-msg="<?= htmlspecialchars($flash_ok) ?>"></span><?php endif; ?>
  <?php if($flash_err): ?><span data-toast data-type="error" data-msg="<?= htmlspecialchars($flash_err) ?>"></span><?php endif; ?>

  <div class="page-header">
    <div>
      <h1 class="page-title">🛍 Necesidades del Mercado</h1>
      <p class="page-subtitle">Compradores buscando productos — envíales tu oferta con precio</p>
    </div>
    <a href="mis_ofertas.php" class="btn btn-outline">Ver mis ofertas →</a>
  </div>

  <form method="GET" style="display:flex;gap:.75rem;margin-bottom:1.5rem">
    <input type="text" name="q" class="form-control" placeholder="🔍 Buscar necesidades..." value="<?= htmlspecialchars($buscar) ?>" style="flex:1">
    <button type="submit" class="btn btn-primary">Buscar</button>
    <?php if($buscar): ?><a href="ver_necesidades.php" class="btn btn-outline">✕</a><?php endif; ?>
  </form>

  <?php if (empty($necesidades)): ?>
    <div class="empty-state">
      <div class="empty-icon">📋</div>
      <h3>Sin necesidades publicadas</h3>
      <p>Cuando los compradores publiquen necesidades aparecerán aquí.</p>
    </div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:1rem">
      <?php foreach ($necesidades as $n): ?>
      <div class="nec-card">
        <div style="display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap">
          <div style="flex:1;min-width:200px">
            <div style="font-weight:700;font-size:1.05rem;margin-bottom:.35rem"><?= htmlspecialchars($n['titulo']) ?></div>
            <p style="font-size:.88rem;color:var(--text-muted);line-height:1.6;margin-bottom:.6rem">
              <?= htmlspecialchars(substr($n['descripcion'],0,200)) ?><?= strlen($n['descripcion'])>200?'…':'' ?>
            </p>
            <div style="font-size:.78rem;color:var(--text-dim)">
              Publicado por <strong style="color:var(--text-muted)"><?= htmlspecialchars($n['comprador']) ?></strong>
              · <?= date('d M Y', strtotime($n['fecha_creacion'])) ?>
            </div>
          </div>
          <div style="flex-shrink:0;text-align:right">
            <?php if ($n['ya_oferte']): ?>
              <div style="background:rgba(6,214,160,.1);border:1px solid var(--success);color:var(--success);padding:.45rem 1rem;border-radius:20px;font-size:.82rem;font-weight:700;margin-bottom:.5rem">
                ✓ Oferta enviada
              </div>
              <a href="mis_ofertas.php" style="font-size:.8rem;color:var(--accent);text-decoration:none">Ver mi oferta →</a>
            <?php else: ?>
              <button onclick="toggleOffer(<?= $n['id'] ?>)" class="btn btn-primary" id="btn-<?= $n['id'] ?>">
                💰 Enviar oferta
              </button>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!$n['ya_oferte']): ?>
        <!-- Inline offer form -->
        <div class="offer-panel" id="offer-<?= $n['id'] ?>">
          <form method="POST">
            <input type="hidden" name="enviar_oferta" value="1">
            <input type="hidden" name="id_necesidad" value="<?= $n['id'] ?>">
            <input type="hidden" name="id_comprador" value="<?= $n['id_comprador'] ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
              <div>
                <label class="form-label" style="font-size:.75rem">Tu precio (COP) *</label>
                <div class="price-input-wrap">
                  <span>$</span>
                  <input type="number" name="precio" class="form-control" placeholder="0" min="1" step="100" required>
                </div>
              </div>
              <div style="display:flex;align-items:flex-end;gap:.5rem">
                <button type="submit" class="btn btn-primary" style="flex:1;padding:.72rem">Enviar →</button>
                <button type="button" onclick="toggleOffer(<?= $n['id'] ?>)" class="btn btn-outline" style="padding:.72rem .9rem">✕</button>
              </div>
            </div>
            <div>
              <label class="form-label" style="font-size:.75rem">Mensaje al comprador</label>
              <textarea name="mensaje" class="form-control" rows="2"
                placeholder="Describe qué ofreces, tiempo de entrega, garantía..." style="resize:vertical"></textarea>
            </div>
          </form>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</div>
<script>
function toggleOffer(id) {
  const panel = document.getElementById('offer-'+id);
  panel.classList.toggle('open');
}
</script>
</body>
</html>
