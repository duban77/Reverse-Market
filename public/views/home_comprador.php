<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') {
    header("Location: login.php"); exit;
}
$id = $_SESSION['usuario_id'];

try {
    $buscar    = trim($_GET['q'] ?? '');
    $categoria = trim($_GET['cat'] ?? '');
    $sql = "SELECT p.*, u.nombre AS vendedor_nombre, u.id AS vendedor_id FROM productos p JOIN usuarios u ON p.id_vendedor=u.id WHERE 1=1";
    $params = [];
    if ($buscar)    { $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)"; $params[]="%$buscar%";$params[]="%$buscar%"; }
    if ($categoria) { $sql .= " AND p.categoria=?"; $params[]=$categoria; }
    $sql .= " ORDER BY p.fecha_creacion DESC LIMIT 40";
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    $productos = $stmt->fetchAll();

    $categorias = $pdo->query("SELECT DISTINCT categoria FROM productos WHERE categoria IS NOT NULL AND categoria!='' ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);
    $stN=$pdo->prepare("SELECT COUNT(*) FROM necesidades WHERE id_comprador=?");$stN->execute([$id]);$n_nec=$stN->fetchColumn();
    $stS=$pdo->prepare("SELECT COUNT(*) FROM solicitudes WHERE id_comprador=?");$stS->execute([$id]);$n_sol=$stS->fetchColumn();
    $stNot=$pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario_destino=? AND leido=0");$stNot->execute([$id]);$nCount=$stNot->fetchColumn();
    $stCart=$pdo->prepare("SELECT COALESCE(SUM(cantidad),0) FROM carrito WHERE id_comprador=?");$stCart->execute([$id]);$cartCount=(int)$stCart->fetchColumn();
    $inCart=$pdo->prepare("SELECT id_producto FROM carrito WHERE id_comprador=?");$inCart->execute([$id]);$inCartIds=array_column($inCart->fetchAll(),'id_producto');
} catch(PDOException $e){$productos=[];$categorias=[];$n_nec=$n_sol=$nCount=$cartCount=0;$inCartIds=[];}
$nombre = htmlspecialchars($_SESSION['nombre'] ?? 'Comprador');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Inicio — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
<style>
/* Search bar */
.search-bar{display:flex;gap:.75rem;margin-bottom:1.75rem;flex-wrap:wrap}
.search-input{flex:1;min-width:200px}
/* Add to cart button */
.btn-cart{width:100%;padding:.6rem;border-radius:var(--radius-xs);font-size:.82rem;font-weight:700;cursor:pointer;border:none;font-family:var(--font-body);transition:var(--transition);margin-top:auto}
.btn-cart.not-in{background:var(--accent);color:#040810}
.btn-cart.not-in:hover{box-shadow:0 4px 16px rgba(0,255,200,.35)}
.btn-cart.in-cart{background:rgba(0,255,200,.1);color:var(--accent);border:1px solid rgba(0,255,200,.25);cursor:default}
/* Cart badge */
.cart-badge-wrap{position:relative}
.cart-count-badge{position:absolute;top:-7px;right:-8px;background:var(--danger);color:#fff;font-size:.6rem;font-weight:800;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid var(--bg-dark)}
/* Welcome banner */
.welcome-banner{
  background:linear-gradient(135deg,var(--bg-card),rgba(0,255,200,.04));
  border:1px solid var(--border);border-radius:var(--radius);
  padding:1.5rem 1.75rem;margin-bottom:1.75rem;
  display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;
  position:relative;overflow:hidden;
}
.welcome-banner::before{content:'';position:absolute;right:-40px;top:-40px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(0,255,200,.06),transparent 65%)}
.welcome-text h2{font-family:var(--font-display);font-size:1.3rem;font-weight:800;margin-bottom:.2rem}
.welcome-text p{font-size:.85rem;color:var(--text-muted);font-weight:300}
</style>
</head>
<body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_comprador.php'; ?>
<div class="main-content">

  <!-- Welcome banner -->
  <div class="welcome-banner">
    <div class="welcome-text">
      <h2>Hola, <?= $nombre ?> 👋</h2>
      <p>Explora productos, publica necesidades y recibe las mejores ofertas</p>
    </div>
    <div style="display:flex;gap:.75rem;align-items:center">
      <a href="carrito.php" class="btn btn-outline cart-badge-wrap">
        🛒 Carrito
        <?php if($cartCount>0):?><span class="cart-count-badge"><?= $cartCount ?></span><?php endif;?>
      </a>
      <a href="necesidades_comprador.php" class="btn btn-primary">+ Nueva necesidad</a>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid" style="margin-bottom:1.75rem">
    <div class="stat-card"><div class="stat-icon">📦</div><div class="stat-number"><?= count($productos) ?></div><div class="stat-label">Productos disponibles</div></div>
    <div class="stat-card"><div class="stat-icon">📌</div><div class="stat-number"><?= $n_nec ?></div><div class="stat-label">Mis necesidades</div></div>
    <div class="stat-card"><div class="stat-icon">📋</div><div class="stat-number"><?= $n_sol ?></div><div class="stat-label">Mis solicitudes</div></div>
    <div class="stat-card"><div class="stat-icon">🛒</div><div class="stat-number" style="color:<?= $cartCount>0?'var(--accent)':'inherit' ?>"><?= $cartCount ?></div><div class="stat-label">En el carrito</div></div>
  </div>

  <!-- Search -->
  <form method="GET" class="search-bar">
    <input type="text" name="q" class="form-control search-input" placeholder="🔍  Buscar productos..." value="<?= htmlspecialchars($buscar) ?>">
    <select name="cat" class="form-control" style="width:200px">
      <option value="">Todas las categorías</option>
      <?php foreach($categorias as $cat):?>
        <option value="<?= htmlspecialchars($cat) ?>" <?= $categoria===$cat?'selected':''?>><?= htmlspecialchars($cat) ?></option>
      <?php endforeach;?>
    </select>
    <button type="submit" class="btn btn-primary">Buscar</button>
    <?php if($buscar||$categoria):?><a href="home_comprador.php" class="btn btn-outline">✕</a><?php endif;?>
  </form>

  <!-- Products grid -->
  <?php if(empty($productos)):?>
    <div class="empty-state">
      <div class="empty-icon">🛒</div>
      <h3>Sin productos <?= $buscar?"para \"$buscar\"":'' ?></h3>
      <p>Publica una necesidad y los vendedores te enviarán propuestas.</p>
      <a href="necesidades_comprador.php" class="btn btn-primary">Publicar necesidad</a>
    </div>
  <?php else:?>
    <div class="product-grid">
      <?php foreach($productos as $p): $inC=in_array($p['id'],$inCartIds);?>
      <div class="product-card">
        <div class="product-img">
          <?php if(!empty($p['imagen'])&&file_exists(__DIR__.'/../../public/uploads/'.$p['imagen'])):?>
            <img src="../uploads/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
          <?php else:?>📦<?php endif;?>
        </div>
        <div class="product-body">
          <div class="product-name" title="<?= htmlspecialchars($p['nombre']) ?>"><?= htmlspecialchars($p['nombre']) ?></div>
          <div class="product-vendor" style="display:flex;align-items:center;justify-content:space-between">
            <span>por <?= htmlspecialchars($p['vendedor_nombre']) ?></span>
            <a href="chat.php?con=<?= $p['vendedor_id'] ?>" style="color:var(--accent);font-size:.8rem" title="Chat">💬</a>
          </div>
          <?php if(!empty($p['categoria'])):?><span class="product-cat"><?= htmlspecialchars($p['categoria']) ?></span><?php endif;?>
          <div class="product-price">$<?= number_format($p['precio'],0,',','.') ?></div>
          <button class="btn-cart <?= $inC?'in-cart':'not-in' ?>" id="btn-<?= $p['id'] ?>"
            <?= $inC?'disabled':'' ?> onclick="addToCart(<?= $p['id'] ?>,this)">
            <?= $inC?'✓ En el carrito':'+ Agregar al carrito' ?>
          </button>
        </div>
      </div>
      <?php endforeach;?>
    </div>
  <?php endif;?>
</div>
</div>

<script>
async function addToCart(pid,btn){
  btn.disabled=true;btn.textContent='Agregando...';
  const fd=new FormData();fd.append('action','add');fd.append('producto_id',pid);fd.append('cantidad',1);
  try{
    const res=await fetch('../controllers/CarritoController.php',{method:'POST',body:fd});
    const data=await res.json();
    if(data.ok){
      btn.textContent='✓ En el carrito';btn.className='btn-cart in-cart';
      RM.toast('Producto agregado al carrito','success','¡Listo!');
      const badge=document.querySelector('.cart-count-badge');
      if(badge)badge.textContent=data.count;
      else{const a=document.querySelector('.cart-badge-wrap');if(a){const s=document.createElement('span');s.className='cart-count-badge';s.textContent=data.count;a.appendChild(s);}}
    }else{btn.disabled=false;btn.textContent='+ Agregar al carrito';RM.toast(data.error||'Error al agregar','error');}
  }catch(e){btn.disabled=false;btn.textContent='+ Agregar al carrito';RM.toast('Error de conexión','error');}
}
</script>
</body>
</html>
