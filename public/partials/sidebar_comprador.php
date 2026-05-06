<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
$stmtN = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario_destino=? AND leido=0");
$stmtN->execute([$_SESSION['usuario_id'] ?? 0]);
$nCount   = (int)$stmtN->fetchColumn();
$stmtC    = $pdo->prepare("SELECT COALESCE(SUM(cantidad),0) FROM carrito WHERE id_comprador=?");
$stmtC->execute([$_SESSION['usuario_id'] ?? 0]);
$cartCount = (int)$stmtC->fetchColumn();
$initials  = strtoupper(mb_substr($_SESSION['nombre'] ?? 'U', 0, 2));
$page      = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-brand">Reverse<span>Market</span></div>

  <div class="sidebar-section">Principal</div>
  <ul class="sidebar-nav">
    <li><a href="home_comprador.php" class="<?= $page=='home_comprador.php'?'active':'' ?>"><span class="icon">🏠</span> Inicio</a></li>
    <li><a href="carrito.php" class="<?= $page=='carrito.php'?'active':'' ?>">
      <span class="icon">🛒</span> Mi Carrito
      <?php if($cartCount>0):?><span class="badge" style="background:var(--accent);color:#040810"><?= $cartCount ?></span><?php endif;?>
    </a></li>
    <li><a href="ofertas_recibidas.php" class="<?= $page=='ofertas_recibidas.php'?'active':'' ?>"><span class="icon">💼</span> Ofertas Recibidas</a></li>
    <li><a href="necesidades_comprador.php" class="<?= $page=='necesidades_comprador.php'?'active':'' ?>"><span class="icon">📌</span> Mis Necesidades</a></li>
    <li><a href="respuestas_necesidades.php" class="<?= $page=='respuestas_necesidades.php'?'active':'' ?>"><span class="icon">📦</span> Ver Propuestas</a></li>
    <li><a href="lista_solicitudes.php" class="<?= $page=='lista_solicitudes.php'?'active':'' ?>"><span class="icon">📋</span> Mis Solicitudes</a></li>
  </ul>

  <div class="sidebar-section">Cuenta</div>
  <ul class="sidebar-nav">
    <li><a href="chat.php" class="<?= $page=='chat.php'?'active':'' ?>"><span class="icon">💬</span> Chat</a></li>
    <li><a href="notificaciones.php" class="<?= $page=='notificaciones.php'?'active':'' ?>">
      <span class="icon">🔔</span> Notificaciones
      <?php if($nCount>0):?><span class="badge"><?= $nCount ?></span><?php endif;?>
    </a></li>
    <li><a href="calificaciones_comprador.php" class="<?= $page=='calificaciones_comprador.php'?'active':'' ?>"><span class="icon">⭐</span> Calificaciones</a></li>
    <li><a href="pagos.php" class="<?= $page=='pagos.php'?'active':'' ?>"><span class="icon">💳</span> Mis Pagos</a></li>
    <li><a href="reportar_producto.php" class="<?= $page=='reportar_producto.php'?'active':'' ?>"><span class="icon">🚩</span> Reportar</a></li>
  </ul>

  <div class="sidebar-bottom">
    <div class="sidebar-user">
      <div class="user-avatar"><?= $initials ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars(mb_substr($_SESSION['nombre'] ?? 'Usuario',0,16)) ?></div>
        <div class="user-role">Comprador</div>
      </div>
    </div>
    <ul class="sidebar-nav">
      <li><a href="../controllers/logout.php" style="color:var(--danger)"><span class="icon">🚪</span> Cerrar sesión</a></li>
    </ul>
  </div>
</aside>
