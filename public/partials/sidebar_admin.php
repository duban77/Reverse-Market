<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
try {
  $stmtR = $pdo->query("SELECT COUNT(*) FROM reportes WHERE estado='pendiente'");
  $rCount = (int)$stmtR->fetchColumn();
} catch(PDOException $e){ $rCount=0; }
$initials = strtoupper(mb_substr($_SESSION['nombre'] ?? 'A', 0, 2));
$page     = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-brand">Reverse<span>Market</span></div>

  <div class="sidebar-section">Administración</div>
  <ul class="sidebar-nav">
    <li><a href="admin_dashboard.php" class="<?= $page=='admin_dashboard.php'?'active':'' ?>"><span class="icon">📊</span> Dashboard</a></li>
    <li><a href="admin_usuarios.php" class="<?= $page=='admin_usuarios.php'?'active':'' ?>"><span class="icon">👥</span> Usuarios</a></li>
    <li><a href="admin_productos.php" class="<?= $page=='admin_productos.php'?'active':'' ?>"><span class="icon">📦</span> Productos</a></li>
    <li><a href="admin_solicitudes.php" class="<?= $page=='admin_solicitudes.php'?'active':'' ?>"><span class="icon">📋</span> Solicitudes</a></li>
    <li><a href="admin_reportes.php" class="<?= $page=='admin_reportes.php'?'active':'' ?>">
      <span class="icon">🚨</span> Reportes
      <?php if($rCount>0):?><span class="badge"><?= $rCount ?></span><?php endif;?>
    </a></li>
    <li><a href="admin_calificaciones.php" class="<?= $page=='admin_calificaciones.php'?'active':'' ?>"><span class="icon">⭐</span> Calificaciones</a></li>
  </ul>

  <div class="sidebar-section">Sistema</div>
  <ul class="sidebar-nav">
    <li><a href="admin_email_config.php" class="<?= basename($_SERVER['PHP_SELF'])==='admin_email_config.php'?'active':'' ?>"><span class="icon">📧</span> Config Email</a></li>
  </ul>

  <div class="sidebar-bottom">
    <div class="sidebar-user">
      <div class="user-avatar" style="background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff"><?= $initials ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars(mb_substr($_SESSION['nombre'] ?? 'Admin',0,16)) ?></div>
        <div class="user-role">Administrador</div>
      </div>
    </div>
    <ul class="sidebar-nav">
      <li><a href="../controllers/logout.php" style="color:var(--danger)"><span class="icon">🚪</span> Cerrar sesión</a></li>
    </ul>
  </div>
</aside>
