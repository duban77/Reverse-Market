<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (isset($_SESSION['rol'])) { header("Location: home_".$_SESSION['rol'].".php"); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $pass   = $_POST['contraseña'] ?? '';
    $rol    = in_array($_POST['rol']??'',['comprador','vendedor']) ? $_POST['rol'] : 'comprador';
    if (!$nombre || !$correo || strlen($pass)<6) { $error='Completa todos los campos. La contraseña debe tener mínimo 6 caracteres.'; }
    else {
        try {
            $ex=$pdo->prepare("SELECT id FROM usuarios WHERE correo=?");$ex->execute([$correo]);
            if ($ex->fetch()) { $error='Este correo ya está registrado.'; }
            else {
                $pdo->prepare("INSERT INTO usuarios (nombre,correo,contraseña,rol,estado) VALUES (?,?,?,'$rol','activo')")->execute([$nombre,$correo,password_hash($pass,PASSWORD_DEFAULT)]);
                $uid=$pdo->lastInsertId();
                $_SESSION['usuario_id']=$uid;$_SESSION['nombre']=$nombre;$_SESSION['correo']=$correo;$_SESSION['rol']=$rol;
                header("Location:".($rol==='vendedor'?'home_vendedor.php':'home_comprador.php')); exit;
            }
        } catch(PDOException $e){ $error=$e->getMessage(); }
    }
}
$rol_default = $_GET['rol'] ?? 'comprador';
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Registro — Reverse Market</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:#040810;color:#f0f4ff;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;-webkit-font-smoothing:antialiased}
.form-wrap{width:100%;max-width:480px}
.brand{font-family:'Syne',sans-serif;font-size:1.2rem;font-weight:800;text-align:center;margin-bottom:2rem;color:#f0f4ff}
.brand span{color:#00ffc8}
.card{background:#0c1422;border:1px solid rgba(0,255,200,.1);border-radius:18px;padding:2.25rem;box-shadow:0 16px 56px rgba(0,0,0,.6)}
.form-h{font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;letter-spacing:-.03em;margin-bottom:.35rem}
.form-sub{font-size:.85rem;color:#6b7fa3;margin-bottom:1.75rem;font-weight:300}
.form-group{margin-bottom:1.1rem}
.form-label{display:block;font-size:.73rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b7fa3;margin-bottom:.4rem}
.form-input{width:100%;background:#0a1120;border:1.5px solid rgba(0,255,200,.1);border-radius:10px;padding:.8rem 1rem;color:#f0f4ff;font-family:'DM Sans',sans-serif;font-size:.92rem;transition:all .2s}
.form-input:focus{outline:none;border-color:#00ffc8;box-shadow:0 0 0 3px rgba(0,255,200,.1);background:#0c1422}
.form-input::placeholder{color:#2d3d5a}
.role-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem}
.rol-card{border:2px solid rgba(0,255,200,.1);border-radius:12px;padding:1rem;cursor:pointer;transition:all .2s;text-align:center}
.rol-card:hover{border-color:rgba(0,255,200,.3)}
.rol-card input{display:none}
.rol-card.selected{border-color:#00ffc8;background:rgba(0,255,200,.06)}
.role-icon{font-size:1.75rem;margin-bottom:.4rem}
.role-label{font-weight:700;font-size:.88rem}
.role-desc{font-size:.74rem;color:#6b7fa3;margin-top:.2rem}
.btn-submit{width:100%;padding:.9rem;background:#00ffc8;color:#040810;border:none;border-radius:10px;font-weight:800;font-size:1rem;cursor:pointer;font-family:'Syne',sans-serif;box-shadow:0 4px 24px rgba(0,255,200,.3);transition:all .2s;margin-top:.5rem}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 6px 32px rgba(0,255,200,.4)}
.error-box{background:rgba(255,77,109,.1);border:1px solid rgba(255,77,109,.3);border-radius:10px;padding:.85rem 1rem;font-size:.85rem;color:#ff4d6d;margin-bottom:1.25rem}
.links{margin-top:1.25rem;text-align:center;font-size:.85rem;color:#6b7fa3}
.links a{color:#00ffc8;font-weight:600}
</style>
</head><body>
<div class="form-wrap">
  <div class="brand">Reverse<span>Market</span></div>
  <div class="card">
    <h2 class="form-h">Crear cuenta gratis</h2>
    <p class="form-sub">Únete y empieza a comprar o vender diferente</p>
    <?php if($error):?><div class="error-box">⚠ <?=htmlspecialchars($error)?></div><?php endif;?>
    <form method="POST" id="regForm">
      <!-- Role selection -->
      <div class="form-label" style="margin-bottom:.6rem">¿Qué quieres hacer?</div>
      <div class="role-grid">
        <label class="rol-card <?=$rol_default==='comprador'?'selected':''?>" onclick="selectRole(this,'comprador')">
          <input type="radio" name="rol" value="comprador" <?=$rol_default==='comprador'?'checked':''?>>
          <div class="role-icon">🛒</div>
          <div class="role-label">Soy Comprador</div>
          <div class="role-desc">Publico necesidades y recibo ofertas</div>
        </label>
        <label class="rol-card <?=$rol_default==='vendedor'?'selected':''?>" onclick="selectRole(this,'vendedor')">
          <input type="radio" name="rol" value="vendedor" <?=$rol_default==='vendedor'?'checked':''?>>
          <div class="role-icon">🏪</div>
          <div class="role-label">Soy Vendedor</div>
          <div class="role-desc">Ofrezco productos y servicios</div>
        </label>
      </div>
      <div class="form-group"><label class="form-label">Nombre completo</label><input type="text" name="nombre" class="form-input" placeholder="Tu nombre" required value="<?=htmlspecialchars($_POST['nombre']??'')?>"></div>
      <div class="form-group"><label class="form-label">Correo electrónico</label><input type="email" name="correo" class="form-input" placeholder="tu@correo.com" required value="<?=htmlspecialchars($_POST['correo']??'')?>"></div>
      <div class="form-group"><label class="form-label">Contraseña (mín. 6 caracteres)</label><input type="password" name="contraseña" class="form-input" placeholder="••••••••" required minlength="6"></div>
      <button type="submit" class="btn-submit">Crear cuenta gratis →</button>
    </form>
    <div class="links">¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a></div>
  </div>
</div>
<script>
function selectRole(el,role){
  document.querySelectorAll('.rol-card').forEach(c=>c.classList.remove('selected'));
  el.classList.add('selected');
  el.querySelector('input').checked=true;
}
</script>
</body></html>
