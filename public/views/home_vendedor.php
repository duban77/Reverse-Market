<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'vendedor') {
    header("Location: login.php"); exit;
}
$id = $_SESSION['usuario_id'];
try {
    $productos=$pdo->prepare("SELECT * FROM productos WHERE id_vendedor=? ORDER BY fecha_creacion DESC");$productos->execute([$id]);$productos=$productos->fetchAll();
    $solicitudes=$pdo->query("SELECT s.*,u.nombre AS comprador FROM solicitudes s JOIN usuarios u ON s.id_comprador=u.id ORDER BY s.fecha_publicacion DESC LIMIT 8")->fetchAll();
    $stR=$pdo->prepare("SELECT COALESCE(AVG(puntuacion),0) AS avg,COUNT(*) AS total FROM calificaciones WHERE id_vendedor=?");$stR->execute([$id]);$rating=$stR->fetch();
    $stM=$pdo->prepare("SELECT * FROM medios_pago WHERE id_vendedor=?");$stM->execute([$id]);$medios=$stM->fetchAll();
    $stN=$pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario_destino=? AND leido=0");$stN->execute([$id]);$nCount=$stN->fetchColumn();
    $stOf=$pdo->prepare("SELECT COUNT(*) FROM oferta_necesidad WHERE id_vendedor=? AND estado='pendiente'");
    $stOf->execute([$id]); $ofertas_pendientes=$stOf->fetchColumn();
} catch(PDOException $e){$productos=[];$solicitudes=[];$rating=['avg'=>0,'total'=>0];$medios=[];$nCount=0;$ofertas_pendientes=0;}
$nombre=htmlspecialchars($_SESSION['nombre']??'Vendedor');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Panel Vendedor — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
<style>
.tab-nav{display:flex;gap:.4rem;margin-bottom:1.75rem;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.35rem}
.tab-btn{padding:.55rem 1.1rem;border-radius:var(--radius-xs);border:none;background:transparent;color:var(--text-muted);cursor:pointer;font-size:.84rem;font-family:var(--font-body);font-weight:500;transition:var(--transition);white-space:nowrap}
.tab-btn.active{background:rgba(0,255,200,.1);color:var(--accent);font-weight:700}
.tab-btn:hover:not(.active){color:var(--text-primary);background:rgba(255,255,255,.04)}
.tab-panel{display:none}.tab-panel.active{display:block;animation:slideIn .2s ease}
.sol-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:.75rem;transition:var(--transition)}
.sol-card:hover{border-color:rgba(0,255,200,.2)}
.rating-display{display:flex;align-items:center;gap:.5rem;color:#ffbe0b;font-size:1rem}
.welcome-banner{background:linear-gradient(135deg,var(--bg-card),rgba(0,255,200,.04));border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem 1.75rem;margin-bottom:1.75rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;position:relative;overflow:hidden}
.welcome-banner::before{content:'';position:absolute;right:-40px;top:-40px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(37,99,235,.08),transparent 65%)}
</style>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_vendedor.php'; ?>
<div class="main-content">

  <div class="welcome-banner">
    <div>
      <h2 style="font-family:var(--font-display);font-size:1.3rem;font-weight:800;margin-bottom:.2rem">Bienvenido, <?= $nombre ?> 👋</h2>
      <p style="font-size:.85rem;color:var(--text-muted);font-weight:300">Panel de vendedor — gestiona tu tienda</p>
    </div>
    <a href="agregar_producto.php" class="btn btn-primary">+ Agregar producto</a>
  </div>

  <!-- Stats -->
  <div class="stats-grid" style="margin-bottom:1.75rem">
    <div class="stat-card"><div class="stat-icon">📦</div><div class="stat-number"><?= count($productos) ?></div><div class="stat-label">Productos publicados</div></div>
    <div class="stat-card"><div class="stat-icon">📋</div><div class="stat-number"><?= count($solicitudes) ?></div><div class="stat-label">Solicitudes activas</div></div>
    <div class="stat-card"><div class="stat-icon">⭐</div>
      <div class="stat-number"><?= $rating['total']>0?number_format($rating['avg'],1):'—' ?></div>
      <div class="stat-label">Calificación promedio</div>
    </div>
    <div class="stat-card"><div class="stat-icon">💼</div><div class="stat-number" style="color:<?= $ofertas_pendientes>0?'var(--warning)':'inherit' ?>"><?= $ofertas_pendientes ?></div><div class="stat-label">Ofertas pendientes</div></div>
  </div>

  <!-- Tabs -->
  <div class="tab-nav">
    <button class="tab-btn active" onclick="showTab('productos',this)">📦 Mis Productos</button>
    <button class="tab-btn" onclick="showTab('solicitudes',this)">📋 Solicitudes del Mercado</button>
    <button class="tab-btn" onclick="window.location.href='ver_necesidades.php'">👁 Ver Necesidades</button>
    <button class="tab-btn" onclick="showTab('perfil',this)">👤 Mi Perfil</button>
    <button class="tab-btn" onclick="showTab('pagos',this)">💳 Medios de Pago</button>
  </div>

  <!-- Tab: Productos -->
  <div id="tab-productos" class="tab-panel active">
    <?php if(empty($productos)):?>
      <div class="empty-state"><div class="empty-icon">📦</div><h3>Sin productos aún</h3><p>Agrega tu primer producto para empezar a recibir clientes.</p><a href="agregar_producto.php" class="btn btn-primary">Agregar producto</a></div>
    <?php else:?>
      <div class="product-grid">
        <?php foreach($productos as $p):?>
        <div class="product-card">
          <div class="product-img">
            <?php if(!empty($p['imagen'])&&file_exists(__DIR__.'/../../public/uploads/'.$p['imagen'])):?>
              <img src="../uploads/<?= htmlspecialchars($p['imagen']) ?>" alt="">
            <?php else:?>📦<?php endif;?>
          </div>
          <div class="product-body">
            <div class="product-name"><?= htmlspecialchars($p['nombre']) ?></div>
            <?php if(!empty($p['categoria'])):?><span class="product-cat"><?= htmlspecialchars($p['categoria']) ?></span><?php endif;?>
            <div class="product-price">$<?= number_format($p['precio'],0,',','.') ?></div>
            <div class="product-actions">
              <a href="editar_producto.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
              <a href="../controllers/ProductoController.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="var h=this.href;event.preventDefault();RM.confirm('¿Eliminar este producto?',function(){window.location.href=h})">Eliminar</a>
            </div>
          </div>
        </div>
        <?php endforeach;?>
      </div>
    <?php endif;?>
  </div>

  <!-- Tab: Solicitudes -->
  <div id="tab-solicitudes" class="tab-panel">
    <?php if(empty($solicitudes)):?>
      <div class="empty-state"><div class="empty-icon">📋</div><h3>Sin solicitudes activas</h3><p>Cuando compradores publiquen solicitudes aparecerán aquí.</p></div>
    <?php else:?>
      <?php foreach($solicitudes as $s):?>
      <div class="sol-card">
        <div style="flex:1">
          <div style="font-weight:700;margin-bottom:.25rem"><?= htmlspecialchars($s['descripcion']??$s['titulo']??'Solicitud') ?></div>
          <div style="font-size:.82rem;color:var(--text-muted)">
            <span>Comprador: <strong style="color:var(--text-primary)"><?= htmlspecialchars($s['comprador']) ?></strong></span>
            <?php if(!empty($s['precio'])):?><span style="margin-left:1rem">Presupuesto: <strong style="color:var(--accent)">$<?= number_format($s['precio'],0,',','.') ?></strong></span><?php endif;?>
          </div>
        </div>
        <a href="enviar_oferta.php?solicitud=<?= $s['id'] ?>" class="btn btn-primary btn-sm">Enviar oferta</a>
      </div>
      <?php endforeach;?>
    <?php endif;?>
  </div>

  <!-- Tab: Perfil -->
  <div id="tab-perfil" class="tab-panel">
    <div class="card" style="max-width:580px">
      <div class="card-body">
        <?php
        try{$perfil=$pdo->prepare("SELECT * FROM perfiles WHERE id_vendedor=?");$perfil->execute([$id]);$perfil=$perfil->fetch();}catch(PDOException $e){$perfil=null;}
        if($perfil):?>
          <div style="display:flex;align-items:center;gap:1.25rem;margin-bottom:1.5rem">
            <div style="width:64px;height:64px;border-radius:14px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:800;font-family:var(--font-display)">
              <?= strtoupper(mb_substr($nombre,0,1)) ?>
            </div>
            <div>
              <div style="font-family:var(--font-display);font-size:1.1rem;font-weight:700"><?= htmlspecialchars($perfil['nombre']) ?></div>
              <div style="font-size:.82rem;color:var(--text-muted)"><?= htmlspecialchars($perfil['telefono']??'') ?></div>
              <?php if($rating['total']>0):?>
                <div class="rating-display" style="margin-top:.3rem;font-size:.85rem">
                  <?= str_repeat('★',round($rating['avg'])) ?> <span style="color:var(--text-muted)"><?= number_format($rating['avg'],1) ?> (<?= $rating['total'] ?> reseñas)</span>
                </div>
              <?php endif;?>
            </div>
          </div>
          <?php if($perfil['descripcion']):?><p style="font-size:.88rem;color:var(--text-muted);line-height:1.7;margin-bottom:1.25rem;font-weight:300"><?= htmlspecialchars($perfil['descripcion']) ?></p><?php endif;?>
          <a href="crear_perfil.php" class="btn btn-outline">Editar perfil</a>
        <?php else:?>
          <div class="empty-state" style="padding:2rem 0">
            <div class="empty-icon">👤</div><h3>Sin perfil configurado</h3>
            <p>Crea tu perfil para que los compradores te conozcan.</p>
            <a href="crear_perfil.php" class="btn btn-primary">Crear perfil</a>
          </div>
        <?php endif;?>
      </div>
    </div>
  </div>

  <!-- Tab: Medios de pago -->
  <div id="tab-pagos" class="tab-panel">
    <div style="margin-bottom:1rem"><a href="medios_pago.php" class="btn btn-primary">+ Agregar medio de pago</a></div>
    <?php if(empty($medios)):?>
      <div class="empty-state"><div class="empty-icon">💳</div><h3>Sin medios de pago</h3><p>Agrega Nequi, Daviplata, cuenta bancaria u otros métodos.</p></div>
    <?php else:?>
      <div style="display:flex;flex-wrap:wrap;gap:.75rem">
        <?php foreach($medios as $mp):?>
          <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.65rem 1.1rem;display:flex;align-items:center;gap:.6rem;font-size:.875rem">
            <span>💳</span>
            <strong><?= htmlspecialchars($mp['metodo_pago']??'') ?></strong>
            <span style="color:var(--text-muted)"><?= htmlspecialchars($mp['numero_cuenta']??'') ?></span>
          </div>
        <?php endforeach;?>
      </div>
    <?php endif;?>
  </div>

</div>
</div>
<script>
function showTab(name,btn){
  document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  btn.classList.add('active');
}
</script>
</body>
</html>
