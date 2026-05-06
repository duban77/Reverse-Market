<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
$stmtN = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario_destino=? AND leido=0");
$stmtN->execute([$_SESSION['usuario_id'] ?? 0]);
$nCount  = (int)$stmtN->fetchColumn();
$initials = strtoupper(mb_substr($_SESSION['nombre'] ?? 'U', 0, 2));
$page     = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-brand">Reverse<span>Market</span></div>

  <div class="sidebar-section">Principal</div>
  <ul class="sidebar-nav">
    <li><a href="home_vendedor.php" class="<?= $page=='home_vendedor.php'?'active':'' ?>"><span class="icon">🏠</span> Panel</a></li>
    <li><a href="agregar_producto.php" class="<?= $page=='agregar_producto.php'?'active':'' ?>"><span class="icon">➕</span> Agregar Producto</a></li>
    <li><a href="ver_necesidades.php" class="<?= $page=='ver_necesidades.php'?'active':'' ?>"><span class="icon">👁</span> Ver Necesidades</a></li>
    <li><a href="mis_ofertas.php" class="<?= $page=='mis_ofertas.php'?'active':'' ?>"><span class="icon">💼</span> Mis Ofertas</a></li>
    <li><a href="lista_solicitudes.php" class="<?= $page=='lista_solicitudes.php'?'active':'' ?>"><span class="icon">📋</span> Solicitudes</a></li>
  </ul>

  <div class="sidebar-section">Perfil & Finanzas</div>
  <ul class="sidebar-nav">
    <li><a href="crear_perfil.php" class="<?= $page=='crear_perfil.php'?'active':'' ?>"><span class="icon">👤</span> Mi Perfil</a></li>
    <li><a href="medios_pago.php" class="<?= $page=='medios_pago.php'?'active':'' ?>"><span class="icon">💳</span> Medios de Pago</a></li>
    <li><a href="chat.php" class="<?= $page=='chat.php'?'active':'' ?>"><span class="icon">💬</span> Chat</a></li>
    <li><a href="notificaciones.php" class="<?= $page=='notificaciones.php'?'active':'' ?>">
      <span class="icon">🔔</span> Notificaciones
      <?php if($nCount>0):?><span class="badge"><?= $nCount ?></span><?php endif;?>
    </a></li>
  </ul>

  <div class="sidebar-bottom">
    <div class="sidebar-user">
      <div class="user-avatar" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff"><?= $initials ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars(mb_substr($_SESSION['nombre'] ?? 'Vendedor',0,16)) ?></div>
        <div class="user-role">Vendedor</div>
      </div>
    </div>
    <ul class="sidebar-nav">
      <li><a href="../controllers/logout.php" style="color:var(--danger)"><span class="icon">🚪</span> Cerrar sesión</a></li>
    </ul>
  </div>
</aside>
