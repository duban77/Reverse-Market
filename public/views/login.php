<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (isset($_SESSION['rol'])) { header("Location: ".($_SESSION['rol']==='admin'?'admin_dashboard.php':($_SESSION['rol']==='vendedor'?'home_vendedor.php':'home_comprador.php'))); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $correo = trim($_POST['correo'] ?? '');
    $pass   = $_POST['contraseña'] ?? '';
    try {
        $st=$pdo->prepare("SELECT * FROM usuarios WHERE correo=?");$st->execute([$correo]);$user=$st->fetch();
        if ($user && password_verify($pass,$user['contraseña'])) {
            if (($user['estado']??'activo')==='bloqueado') { $error='Tu cuenta está bloqueada. Contacta al soporte.'; }
            else {
                $_SESSION['usuario_id']=$user['id'];$_SESSION['nombre']=$user['nombre'];
                $_SESSION['correo']=$user['correo'];$_SESSION['rol']=$user['rol'];
                header("Location:".($user['rol']==='admin'?'admin_dashboard.php':($user['rol']==='vendedor'?'home_vendedor.php':'home_comprador.php'))); exit;
            }
        } else { $error='Correo o contraseña incorrectos.'; }
    } catch(PDOException $e){ $error='Error de conexión. Verifica que MySQL esté activo.'; }
}
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Iniciar Sesión — Reverse Market</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:#040810;color:#f0f4ff;min-height:100vh;display:grid;grid-template-columns:1fr 1fr;-webkit-font-smoothing:antialiased}
.left-panel{background:linear-gradient(135deg,#070d1a,#0c1422);border-right:1px solid rgba(0,255,200,.1);display:flex;flex-direction:column;justify-content:center;padding:4rem;position:relative;overflow:hidden}
.left-panel::before{content:'';position:absolute;top:-100px;right:-100px;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(0,255,200,.07),transparent 65%)}
.left-panel::after{content:'';position:absolute;bottom:-80px;left:-60px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(37,99,235,.06),transparent 65%)}
.brand{font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;margin-bottom:3rem;position:relative;z-index:1}
.brand span{color:#00ffc8}
.left-h{font-family:'Syne',sans-serif;font-size:2.5rem;font-weight:800;line-height:1.1;letter-spacing:-.04em;margin-bottom:1rem;position:relative;z-index:1}
.left-h em{font-style:normal;color:#00ffc8}
.left-sub{font-size:.95rem;color:#6b7fa3;line-height:1.7;font-weight:300;max-width:360px;position:relative;z-index:1}
.features{margin-top:2.5rem;display:flex;flex-direction:column;gap:.85rem;position:relative;z-index:1}
.feature{display:flex;align-items:center;gap:.85rem;font-size:.88rem;color:#6b7fa3}
.feat-icon{width:34px;height:34px;border-radius:10px;background:rgba(0,255,200,.08);border:1px solid rgba(0,255,200,.15);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.right-panel{display:flex;align-items:center;justify-content:center;padding:2rem}
.form-box{width:100%;max-width:400px}
.form-h{font-family:'Syne',sans-serif;font-size:1.75rem;font-weight:800;letter-spacing:-.03em;margin-bottom:.35rem}
.form-sub{font-size:.88rem;color:#6b7fa3;margin-bottom:2rem;font-weight:300}
.form-group{margin-bottom:1.1rem}
.form-label{display:block;font-size:.73rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b7fa3;margin-bottom:.4rem}
.form-input{width:100%;background:#0a1120;border:1.5px solid rgba(0,255,200,.1);border-radius:10px;padding:.8rem 1rem;color:#f0f4ff;font-family:'DM Sans',sans-serif;font-size:.92rem;transition:all .2s}
.form-input:focus{outline:none;border-color:#00ffc8;box-shadow:0 0 0 3px rgba(0,255,200,.1);background:#0c1422}
.form-input::placeholder{color:#2d3d5a}
.btn-submit{width:100%;padding:.9rem;background:#00ffc8;color:#040810;border:none;border-radius:10px;font-weight:800;font-size:1rem;cursor:pointer;font-family:'Syne',sans-serif;box-shadow:0 4px 24px rgba(0,255,200,.3);transition:all .2s;margin-top:.5rem}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 6px 32px rgba(0,255,200,.4)}
.error-box{background:rgba(255,77,109,.1);border:1px solid rgba(255,77,109,.3);border-radius:10px;padding:.85rem 1rem;font-size:.85rem;color:#ff4d6d;margin-bottom:1.25rem;display:flex;align-items:center;gap:.6rem}
.links{margin-top:1.5rem;text-align:center;font-size:.85rem;color:#6b7fa3}
.links a{color:#00ffc8;font-weight:600}
.divider{display:flex;align-items:center;gap:.75rem;margin:1.25rem 0;color:#2d3d5a;font-size:.78rem}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#2d3d5a}
@media(max-width:768px){body{grid-template-columns:1fr}.left-panel{display:none}}
</style>
</head><body>
<div class="left-panel">
  <div class="brand">Reverse<span>Market</span></div>
  <h1 class="left-h">El mercado<br>que trabaja<br><em>para ti</em></h1>
  <p class="left-sub">Publica lo que buscas. Recibe ofertas de vendedores. Elige siempre lo mejor.</p>
  <div class="features">
    <div class="feature"><div class="feat-icon">🔄</div>Mercado invertido — tú defines la demanda</div>
    <div class="feature"><div class="feat-icon">💬</div>Negociación directa con vendedores</div>
    <div class="feature"><div class="feat-icon">💳</div>Pagos seguros con MercadoPago</div>
    <div class="feature"><div class="feat-icon">⭐</div>Calificaciones verificadas</div>
  </div>
</div>
<div class="right-panel">
  <div class="form-box">
    <a href="../index.php" style="display:inline-flex;align-items:center;gap:.45rem;font-size:.82rem;color:#6b7fa3;text-decoration:none;margin-bottom:1.75rem;padding:.5rem .9rem;border:1px solid rgba(255,255,255,.08);border-radius:8px;transition:all .2s" onmouseover="this.style.color='#00ffc8';this.style.borderColor='rgba(0,255,200,.3)'" onmouseout="this.style.color='#6b7fa3';this.style.borderColor='rgba(255,255,255,.08)'">← Volver al inicio</a>
    <h2 class="form-h">Bienvenido de vuelta</h2>
    <p class="form-sub">Ingresa a tu cuenta para continuar</p>
    <?php if($error):?><div class="error-box">⚠ <?=htmlspecialchars($error)?></div><?php endif;?>
    <form method="POST">
      <div class="form-group"><label class="form-label">Correo electrónico</label><input type="email" name="correo" class="form-input" placeholder="tu@correo.com" required autofocus value="<?=htmlspecialchars($_POST['correo']??'')?>"></div>
      <div class="form-group"><label class="form-label">Contraseña</label><input type="password" name="contraseña" class="form-input" placeholder="••••••••" required></div>
      <button type="submit" class="btn-submit">Iniciar sesión →</button>
    </form>
    <div class="divider">o</div>
    <div class="links">¿No tienes cuenta? <a href="register.php">Regístrate gratis</a></div>
    <div class="links" style="margin-top:.6rem"><a href="recuperar_password.php" style="color:#6b7fa3">¿Olvidaste tu contraseña?</a></div>
  </div>
</div>
</body></html>
