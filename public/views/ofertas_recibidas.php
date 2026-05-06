<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') {
    header("Location: login.php"); exit;
}
$id = $_SESSION['usuario_id'];
$flash_ok  = $_SESSION['flash_ok']    ?? ''; unset($_SESSION['flash_ok']);
$flash_err = $_SESSION['flash_error'] ?? ''; unset($_SESSION['flash_error']);

// Auto-create tables
try { $pdo->exec("CREATE TABLE IF NOT EXISTS oferta_necesidad (id INT AUTO_INCREMENT PRIMARY KEY, id_necesidad INT NOT NULL, id_vendedor INT NOT NULL, id_comprador INT NOT NULL, precio DECIMAL(10,2) NOT NULL, mensaje TEXT, estado ENUM('pendiente','aceptada','rechazada','negociando') DEFAULT 'pendiente', fecha DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (id_necesidad) REFERENCES necesidades(id) ON DELETE CASCADE, FOREIGN KEY (id_vendedor) REFERENCES usuarios(id) ON DELETE CASCADE, FOREIGN KEY (id_comprador) REFERENCES usuarios(id) ON DELETE CASCADE)"); } catch(PDOException $e){}
try { $pdo->exec("CREATE TABLE IF NOT EXISTS negociacion_mensajes (id INT AUTO_INCREMENT PRIMARY KEY, id_oferta INT NOT NULL, id_emisor INT NOT NULL, mensaje TEXT NOT NULL, precio_propuesto DECIMAL(10,2) NULL, fecha DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (id_oferta) REFERENCES oferta_necesidad(id) ON DELETE CASCADE, FOREIGN KEY (id_emisor) REFERENCES usuarios(id) ON DELETE CASCADE)"); } catch(PDOException $e){}

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Accept offer → go to payment
    if (isset($_POST['aceptar'])) {
        $oid = (int)$_POST['oferta_id'];
        $o = $pdo->prepare("SELECT on2.*, n.titulo, u.nombre AS vend FROM oferta_necesidad on2 JOIN necesidades n ON on2.id_necesidad=n.id JOIN usuarios u ON on2.id_vendedor=u.id WHERE on2.id=? AND on2.id_comprador=?");
        $o->execute([$oid, $id]); $o = $o->fetch();
        if ($o) {
            $_SESSION['pago_oferta'] = [
                'oferta_id'   => $oid,
                'tipo'        => 'necesidad',
                'producto'    => $o['titulo'],
                'vendedor'    => $o['vend'],
                'monto'       => $o['precio'],
                'id_vendedor' => $o['id_vendedor'],
                'id_producto' => null,
            ];
            header("Location: pago_oferta.php"); exit;
        }
    }

    // Reject offer
    if (isset($_POST['rechazar'])) {
        $oid = (int)$_POST['oferta_id'];
        try {
            $pdo->prepare("UPDATE oferta_necesidad SET estado='rechazada' WHERE id=? AND id_comprador=?")->execute([$oid,$id]);
            $vend = $pdo->prepare("SELECT id_vendedor FROM oferta_necesidad WHERE id=?"); $vend->execute([$oid]); $vend=$vend->fetchColumn();
            if ($vend) { try{$pdo->prepare("INSERT INTO notificaciones (mensaje,id_usuario_destino,tipo) VALUES (?,?,'oferta')")->execute(["❌ {$_SESSION['nombre']} rechazó tu oferta.",$vend]);}catch(PDOException $ignored){} }
            $_SESSION['flash_ok'] = 'Oferta rechazada.';
        } catch(PDOException $e) { $_SESSION['flash_error']=$e->getMessage(); }
        header("Location: ofertas_recibidas.php"); exit;
    }

    // Send negotiation message / counter-offer
    if (isset($_POST['neg_mensaje'])) {
        $oid  = (int)$_POST['id_oferta'];
        $msg  = trim($_POST['neg_mensaje']);
        $np   = !empty($_POST['nuevo_precio']) ? (float)$_POST['nuevo_precio'] : null;
        if ($msg) {
            try {
                $pdo->prepare("INSERT INTO negociacion_mensajes (id_oferta,id_emisor,mensaje,precio_propuesto) VALUES (?,?,?,?)")
                    ->execute([$oid,$id,$msg,$np]);
                if ($np) $pdo->prepare("UPDATE oferta_necesidad SET precio=?,estado='negociando' WHERE id=? AND id_comprador=?")->execute([$np,$oid,$id]);
                $vend=$pdo->prepare("SELECT id_vendedor FROM oferta_necesidad WHERE id=?");$vend->execute([$oid]);$vend=$vend->fetchColumn();
                if($vend){try{$pdo->prepare("INSERT INTO notificaciones (mensaje,id_usuario_destino,tipo) VALUES (?,?,'oferta')")->execute(["💬 {$_SESSION['nombre']} respondió tu oferta",$vend]);}catch(PDOException $ignored){}}
                $_SESSION['flash_ok']='Mensaje enviado.';
            } catch(PDOException $e){$_SESSION['flash_error']=$e->getMessage();}
        }
        header("Location: ofertas_recibidas.php#oferta-$oid"); exit;
    }
}

try {
    $ofertas = $pdo->prepare("
        SELECT on2.*, n.titulo AS necesidad,
               u.nombre AS vendedor, u.id AS vid,
               COALESCE((SELECT AVG(puntuacion) FROM calificaciones WHERE id_vendedor=u.id),0) AS rating
        FROM oferta_necesidad on2
        JOIN necesidades n ON on2.id_necesidad=n.id
        JOIN usuarios u ON on2.id_vendedor=u.id
        WHERE on2.id_comprador=?
        ORDER BY on2.fecha DESC
    ");
    $ofertas->execute([$id]); $ofertas=$ofertas->fetchAll();
} catch(PDOException $e){$ofertas=[];}

$pendientes = array_filter($ofertas,fn($o)=>in_array($o['estado']??'pendiente',['pendiente','negociando']));
$cerradas   = array_filter($ofertas,fn($o)=>!in_array($o['estado']??'pendiente',['pendiente','negociando']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ofertas Recibidas — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
<style>
.oferta-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;transition:var(--transition);margin-bottom:1rem}
.oferta-card.pendiente{border-color:rgba(255,190,11,.25)}
.neg-thread{background:var(--bg-panel);border-radius:var(--radius-sm);padding:1rem;margin-top:1rem;max-height:200px;overflow-y:auto;display:flex;flex-direction:column;gap:.5rem}
.neg-msg{padding:.55rem .85rem;border-radius:10px;font-size:.83rem;line-height:1.5}
.neg-msg.mine{background:rgba(0,255,200,.09);border:1px solid rgba(0,255,200,.15);align-self:flex-end;max-width:80%}
.neg-msg.theirs{background:var(--bg-card);border:1px solid var(--border);align-self:flex-start;max-width:80%}
.action-bar{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border)}
.status-pill{padding:.25rem .75rem;border-radius:20px;font-size:.74rem;font-weight:700;display:inline-block}
.s-pendiente{background:rgba(255,190,11,.12);color:var(--warning)}
.s-negociando{background:rgba(0,102,255,.12);color:#60a5fa}
.s-aceptada{background:rgba(6,214,160,.12);color:var(--success)}
.s-rechazada{background:rgba(255,77,109,.12);color:var(--danger)}
</style>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_comprador.php'; ?>
<div class="main-content">

  <?php if($flash_ok): ?><span data-toast data-type="success" data-msg="<?= htmlspecialchars($flash_ok) ?>"></span><?php endif; ?>
  <?php if($flash_err): ?><span data-toast data-type="error" data-msg="<?= htmlspecialchars($flash_err) ?>"></span><?php endif; ?>

  <div class="page-header">
    <div><h1 class="page-title">💼 Ofertas Recibidas</h1><p class="page-subtitle">Vendedores que respondieron a tus necesidades</p></div>
    <a href="necesidades_comprador.php" class="btn btn-outline">Mis necesidades</a>
  </div>

  <div class="stats-grid" style="margin-bottom:2rem">
    <div class="stat-card"><div class="stat-number" style="color:var(--warning)"><?= count($pendientes) ?></div><div class="stat-label">Por responder</div></div>
    <div class="stat-card"><div class="stat-number"><?= count($ofertas) ?></div><div class="stat-label">Total recibidas</div></div>
  </div>

  <?php if (empty($ofertas)): ?>
    <div class="empty-state">
      <div class="empty-icon">📭</div>
      <h3>Sin ofertas aún</h3>
      <p>Publica necesidades para recibir ofertas de vendedores.</p>
      <a href="necesidades_comprador.php" class="btn btn-primary">Publicar necesidad</a>
    </div>
  <?php else: ?>

    <?php if (!empty($pendientes)): ?>
    <h2 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem">
      <span style="width:9px;height:9px;border-radius:50%;background:var(--warning);box-shadow:0 0 8px rgba(255,190,11,.5)"></span>
      Esperando tu respuesta
    </h2>
    <?php foreach ($pendientes as $o): ?>
    <?php
    $estado=$o['estado']??'pendiente';
    try{$msgs=$pdo->prepare("SELECT nm.*,u.nombre AS emisor FROM negociacion_mensajes nm JOIN usuarios u ON nm.id_emisor=u.id WHERE nm.id_oferta=? ORDER BY nm.fecha ASC");$msgs->execute([$o['id']]);$msgs=$msgs->fetchAll();}catch(PDOException $e){$msgs=[];}
    ?>
    <div class="oferta-card pendiente" id="oferta-<?= $o['id'] ?>">
      <div style="display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap">
        <div style="flex:1">
          <div style="font-weight:700;font-size:1rem;margin-bottom:.25rem"><?= htmlspecialchars($o['necesidad']) ?></div>
          <div style="font-size:.82rem;color:var(--text-muted);margin-bottom:.4rem">
            Vendedor: <strong style="color:var(--text-primary)"><?= htmlspecialchars($o['vendedor']) ?></strong>
            <?php if($o['rating']>0):?><span style="color:#ffbe0b"> ★<?= number_format($o['rating'],1)?></span><?php endif;?>
            · <?= date('d M Y',strtotime($o['fecha'])) ?>
          </div>
          <?php if($o['mensaje']):?>
            <div style="font-size:.85rem;color:var(--text-muted);background:var(--bg-panel);border-radius:8px;padding:.6rem .85rem;font-style:italic;line-height:1.55">"<?= htmlspecialchars($o['mensaje']) ?>"</div>
          <?php endif;?>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <div style="font-family:var(--font-display);font-size:1.6rem;font-weight:800;color:var(--accent)">$<?= number_format($o['precio'],2) ?></div>
          <span class="status-pill s-<?= $estado ?>"><?= $estado==='negociando'?'💬 Negociando':'⏳ Nueva oferta' ?></span>
        </div>
      </div>

      <!-- Negotiation thread -->
      <?php if(!empty($msgs)):?>
      <div class="neg-thread">
        <?php foreach($msgs as $m):?>
        <div class="neg-msg <?= $m['id_emisor']==$id?'mine':'theirs'?>">
          <div style="font-size:.7rem;color:var(--text-dim);margin-bottom:.15rem"><?= htmlspecialchars($m['emisor']) ?> · <?= date('d/m H:i',strtotime($m['fecha']))?></div>
          <?= htmlspecialchars($m['mensaje'])?>
          <?php if($m['precio_propuesto']):?><div style="margin-top:.25rem;font-weight:700;color:var(--accent)">Precio propuesto: $<?= number_format($m['precio_propuesto'],2)?></div><?php endif;?>
        </div>
        <?php endforeach;?>
      </div>
      <?php endif;?>

      <!-- Actions -->
      <div class="action-bar">
        <!-- Accept -->
        <form method="POST" style="display:contents">
          <input type="hidden" name="oferta_id" value="<?= $o['id'] ?>">
          <button type="submit" name="aceptar" value="1" class="btn btn-primary" style="font-weight:800">
            💳 Aceptar y Pagar $<?= number_format($o['precio'],2)?>
          </button>
        </form>
        <!-- Reject -->
        <form method="POST" style="display:contents">
          <input type="hidden" name="oferta_id" value="<?= $o['id'] ?>">
          <button type="submit" name="rechazar" value="1" class="btn btn-danger" style="font-size:.85rem"
            onclick="return confirm('¿Rechazar esta oferta?')">✕ Rechazar</button>
        </form>
        <!-- Chat -->
        <a href="chat.php?con=<?= $o['vid'] ?>" class="btn btn-outline" style="font-size:.85rem">💬 Chat</a>
      </div>

      <!-- Counter-offer form -->
      <details style="margin-top:.75rem">
        <summary style="font-size:.82rem;color:var(--accent);cursor:pointer;user-select:none">💬 Negociar / contraoferta</summary>
        <form method="POST" style="margin-top:.75rem;display:flex;gap:.5rem;flex-wrap:wrap">
          <input type="hidden" name="id_oferta" value="<?= $o['id'] ?>">
          <input type="text" name="neg_mensaje" class="form-control" placeholder="Tu mensaje o contraoferta..." style="flex:1;min-width:200px" required>
          <input type="number" name="nuevo_precio" class="form-control" placeholder="Tu precio propuesto" style="width:180px" min="1" step="100">
          <button type="submit" class="btn btn-outline">Enviar →</button>
        </form>
      </details>
    </div>
    <?php endforeach;?>
    <?php endif;?>

    <?php if(!empty($cerradas)):?>
    <h2 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:1rem;margin-top:1.5rem;color:var(--text-muted)">Historial</h2>
    <?php foreach($cerradas as $o):?>
    <div class="oferta-card" style="opacity:.7">
      <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
        <div style="flex:1"><div style="font-weight:600"><?= htmlspecialchars($o['necesidad'])?></div><div style="font-size:.8rem;color:var(--text-muted)"><?= htmlspecialchars($o['vendedor'])?></div></div>
        <div style="font-family:var(--font-display);font-weight:700;color:var(--text-muted)">$<?= number_format($o['precio'],2)?></div>
        <?php $e=$o['estado']??'pendiente';?>
        <span class="status-pill s-<?= $e?>"><?= $e==='aceptada'?'✅ Aceptada y pagada':'❌ Rechazada'?></span>
      </div>
    </div>
    <?php endforeach;?>
    <?php endif;?>

  <?php endif;?>
</div>
</div>
</body>
</html>
