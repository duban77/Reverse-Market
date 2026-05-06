<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'vendedor') {
    header("Location: login.php"); exit;
}
$id = $_SESSION['usuario_id'];
$flash_ok  = $_SESSION['flash_ok']    ?? ''; unset($_SESSION['flash_ok']);
$flash_err = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

// Auto-create table
try { $pdo->exec("CREATE TABLE IF NOT EXISTS oferta_necesidad (id INT AUTO_INCREMENT PRIMARY KEY, id_necesidad INT NOT NULL, id_vendedor INT NOT NULL, id_comprador INT NOT NULL, precio DECIMAL(10,2) NOT NULL, mensaje TEXT, estado ENUM('pendiente','aceptada','rechazada','negociando') DEFAULT 'pendiente', fecha DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (id_necesidad) REFERENCES necesidades(id) ON DELETE CASCADE, FOREIGN KEY (id_vendedor) REFERENCES usuarios(id) ON DELETE CASCADE, FOREIGN KEY (id_comprador) REFERENCES usuarios(id) ON DELETE CASCADE)"); } catch(PDOException $e){}
try { $pdo->exec("CREATE TABLE IF NOT EXISTS negociacion_mensajes (id INT AUTO_INCREMENT PRIMARY KEY, id_oferta INT NOT NULL, id_emisor INT NOT NULL, mensaje TEXT NOT NULL, precio_propuesto DECIMAL(10,2) NULL, fecha DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (id_oferta) REFERENCES oferta_necesidad(id) ON DELETE CASCADE, FOREIGN KEY (id_emisor) REFERENCES usuarios(id) ON DELETE CASCADE)"); } catch(PDOException $e){}

// Send negotiation message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['neg_mensaje'])) {
    $id_oferta = (int)$_POST['id_oferta'];
    $msg       = trim($_POST['neg_mensaje']);
    $nuevo_precio = !empty($_POST['nuevo_precio']) ? (float)$_POST['nuevo_precio'] : null;
    if ($msg) {
        try {
            $pdo->prepare("INSERT INTO negociacion_mensajes (id_oferta,id_emisor,mensaje,precio_propuesto) VALUES (?,?,?,?)")
                ->execute([$id_oferta, $id, $msg, $nuevo_precio]);
            if ($nuevo_precio) {
                $pdo->prepare("UPDATE oferta_necesidad SET precio=?, estado='negociando' WHERE id=? AND id_vendedor=?")
                    ->execute([$nuevo_precio, $id_oferta, $id]);
            }
            // Get comprador id for notification
            $comp = $pdo->prepare("SELECT id_comprador FROM oferta_necesidad WHERE id=?");
            $comp->execute([$id_oferta]); $comp = $comp->fetchColumn();
            if ($comp) {
                try { $pdo->prepare("INSERT INTO notificaciones (mensaje,id_usuario_destino,tipo) VALUES (?,?,'oferta')")
                    ->execute(["💬 {$_SESSION['nombre']} actualizó su oferta", $comp]); } catch(PDOException $ignored){}
            }
            $_SESSION['flash_ok'] = 'Mensaje enviado.';
        } catch(PDOException $e) { $_SESSION['flash_error'] = $e->getMessage(); }
    }
    header("Location: mis_ofertas.php#oferta-$id_oferta"); exit;
}

try {
    $ofertas = $pdo->prepare("
        SELECT on2.*, n.titulo AS necesidad, n.descripcion AS nec_desc,
               u.nombre AS comprador, u.id AS comprador_id
        FROM oferta_necesidad on2
        JOIN necesidades n ON on2.id_necesidad = n.id
        JOIN usuarios u ON on2.id_comprador = u.id
        WHERE on2.id_vendedor = ?
        ORDER BY on2.fecha DESC
    ");
    $ofertas->execute([$id]);
    $ofertas = $ofertas->fetchAll();
} catch(PDOException $e) { $ofertas = []; }

$pendientes  = array_filter($ofertas, fn($o)=>in_array($o['estado']??'pendiente',['pendiente','negociando']));
$aceptadas   = array_filter($ofertas, fn($o)=>($o['estado']??'')==='aceptada');
$rechazadas  = array_filter($ofertas, fn($o)=>($o['estado']??'')==='rechazada');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mis Ofertas — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
<style>
.oferta-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;transition:var(--transition)}
.oferta-card:hover{border-color:rgba(0,255,200,.2)}
.neg-thread{background:var(--bg-panel);border-radius:var(--radius-sm);padding:1rem;margin-top:1rem;max-height:220px;overflow-y:auto;display:flex;flex-direction:column;gap:.6rem}
.neg-msg{padding:.6rem .85rem;border-radius:10px;font-size:.84rem;line-height:1.5}
.neg-msg.mine{background:rgba(0,255,200,.1);border:1px solid rgba(0,255,200,.15);align-self:flex-end;max-width:80%}
.neg-msg.theirs{background:var(--bg-card);border:1px solid var(--border);align-self:flex-start;max-width:80%}
.status-pill{padding:.25rem .75rem;border-radius:20px;font-size:.74rem;font-weight:700;display:inline-block}
.s-pendiente{background:rgba(255,190,11,.12);color:var(--warning)}
.s-negociando{background:rgba(0,102,255,.12);color:#60a5fa}
.s-aceptada{background:rgba(6,214,160,.12);color:var(--success)}
.s-rechazada{background:rgba(255,77,109,.12);color:var(--danger)}
</style>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_vendedor.php'; ?>
<div class="main-content">

  <?php if($flash_ok): ?><span data-toast data-type="success" data-msg="<?= htmlspecialchars($flash_ok) ?>"></span><?php endif; ?>
  <?php if($flash_err): ?><span data-toast data-type="error" data-msg="<?= htmlspecialchars($flash_err) ?>"></span><?php endif; ?>

  <div class="page-header">
    <div><h1 class="page-title">💼 Mis Ofertas Enviadas</h1><p class="page-subtitle">Seguimiento de tus propuestas a compradores</p></div>
    <a href="ver_necesidades.php" class="btn btn-primary">+ Enviar nueva oferta</a>
  </div>

  <div class="stats-grid" style="margin-bottom:2rem">
    <div class="stat-card"><div class="stat-number" style="color:var(--warning)"><?= count($pendientes) ?></div><div class="stat-label">En proceso</div></div>
    <div class="stat-card"><div class="stat-number" style="color:var(--success)"><?= count($aceptadas) ?></div><div class="stat-label">Aceptadas</div></div>
    <div class="stat-card"><div class="stat-number" style="color:var(--danger)"><?= count($rechazadas) ?></div><div class="stat-label">Rechazadas</div></div>
  </div>

  <?php if (empty($ofertas)): ?>
    <div class="empty-state">
      <div class="empty-icon">💼</div>
      <h3>Sin ofertas enviadas</h3>
      <p>Ve al mercado de necesidades y envía tu primera oferta con precio.</p>
      <a href="ver_necesidades.php" class="btn btn-primary">Ver necesidades →</a>
    </div>
  <?php else: ?>

    <?php foreach ($ofertas as $o): ?>
    <?php
    $estado = $o['estado'] ?? 'pendiente';
    // Load negotiation messages
    try {
        $msgs = $pdo->prepare("SELECT nm.*,u.nombre AS emisor FROM negociacion_mensajes nm JOIN usuarios u ON nm.id_emisor=u.id WHERE nm.id_oferta=? ORDER BY nm.fecha ASC");
        $msgs->execute([$o['id']]); $msgs = $msgs->fetchAll();
    } catch(PDOException $e) { $msgs = []; }
    ?>
    <div class="oferta-card" id="oferta-<?= $o['id'] ?>" style="margin-bottom:1rem;<?= $estado==='pendiente'||$estado==='negociando'?'border-color:rgba(255,190,11,.2)':'' ?>">
      <div style="display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap">
        <div style="flex:1;min-width:200px">
          <div style="font-weight:700;font-size:1rem;margin-bottom:.25rem"><?= htmlspecialchars($o['necesidad']) ?></div>
          <div style="font-size:.82rem;color:var(--text-muted);margin-bottom:.5rem">
            Comprador: <strong style="color:var(--text-primary)"><?= htmlspecialchars($o['comprador']) ?></strong>
            · <?= date('d M Y, H:i', strtotime($o['fecha'])) ?>
          </div>
          <?php if ($o['mensaje']): ?>
            <div style="font-size:.83rem;color:var(--text-muted);font-style:italic;margin-bottom:.5rem">"<?= htmlspecialchars($o['mensaje']) ?>"</div>
          <?php endif; ?>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <div style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:var(--accent)">$<?= number_format($o['precio'],2) ?></div>
          <span class="status-pill s-<?= $estado ?>"><?= ['pendiente'=>'⏳ Pendiente','negociando'=>'💬 Negociando','aceptada'=>'✅ Aceptada','rechazada'=>'❌ Rechazada'][$estado] ?? $estado ?></span>
        </div>
      </div>

      <?php if (in_array($estado,['pendiente','negociando'])): ?>
      <!-- Negotiation thread -->
      <?php if (!empty($msgs)): ?>
      <div class="neg-thread">
        <?php foreach ($msgs as $m): ?>
        <div class="neg-msg <?= $m['id_emisor']==$id?'mine':'theirs' ?>">
          <div style="font-size:.72rem;color:var(--text-dim);margin-bottom:.2rem"><?= htmlspecialchars($m['emisor']) ?> · <?= date('d/m H:i',strtotime($m['fecha'])) ?></div>
          <?= htmlspecialchars($m['mensaje']) ?>
          <?php if ($m['precio_propuesto']): ?>
            <div style="margin-top:.3rem;font-weight:700;color:var(--accent)">Precio propuesto: $<?= number_format($m['precio_propuesto'],2) ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Send message / counter-offer -->
      <form method="POST" style="margin-top:.85rem;display:flex;gap:.5rem;flex-wrap:wrap">
        <input type="hidden" name="id_oferta" value="<?= $o['id'] ?>">
        <input type="text" name="neg_mensaje" class="form-control" placeholder="Escribe un mensaje o contraoferta..." style="flex:1;min-width:200px" required>
        <input type="number" name="nuevo_precio" class="form-control" placeholder="Nuevo precio (opcional)" style="width:200px" min="1" step="100">
        <button type="submit" class="btn btn-primary" style="white-space:nowrap">Enviar →</button>
        <a href="chat.php?con=<?= $o['comprador_id'] ?>" class="btn btn-outline" style="white-space:nowrap">💬 Chat</a>
      </form>
      <?php elseif ($estado==='aceptada'): ?>
        <div style="margin-top:.75rem;padding:.75rem;background:rgba(6,214,160,.08);border-radius:8px;font-size:.85rem;color:var(--success)">
          ✅ El comprador aceptó tu oferta y realizó el pago. La transacción fue registrada.
        </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
</div>
</body>
</html>
