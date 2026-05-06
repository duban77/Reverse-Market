<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';

$token = trim($_GET['token'] ?? '');
$msg   = '';
$tipo  = '';
$valid = false;
$user  = null;

if (!$token) { header("Location: recuperar_password.php"); exit; }

// Verify token
try {
    $st = $pdo->prepare("SELECT rp.*, u.nombre, u.correo FROM recuperacion_password rp JOIN usuarios u ON rp.id_usuario=u.id WHERE rp.token=? AND rp.usado=0 AND rp.expira > NOW()");
    $st->execute([$token]);
    $row = $st->fetch();
    if ($row) { $valid = true; $user = $row; }
    else { $msg = 'Este enlace no es válido o ya expiró. Solicita uno nuevo.'; $tipo = 'error'; }
} catch(PDOException $e){ $msg = 'Error al verificar el enlace.'; $tipo = 'error'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $pass1 = $_POST['password']  ?? '';
    $pass2 = $_POST['password2'] ?? '';
    if (strlen($pass1) < 6) { $msg = 'La contraseña debe tener mínimo 6 caracteres.'; $tipo = 'error'; }
    elseif ($pass1 !== $pass2) { $msg = 'Las contraseñas no coinciden.'; $tipo = 'error'; }
    else {
        try {
            $pdo->prepare("UPDATE usuarios SET contraseña=? WHERE id=?")
                ->execute([password_hash($pass1, PASSWORD_DEFAULT), $user['id_usuario']]);
            $pdo->prepare("UPDATE recuperacion_password SET usado=1 WHERE token=?")
                ->execute([$token]);
            $msg  = '✅ ¡Contraseña actualizada! Ahora puedes iniciar sesión.';
            $tipo = 'success';
            $valid = false; // Hide form
        } catch(PDOException $e){ $msg = 'Error al actualizar: ' . $e->getMessage(); $tipo = 'error'; }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Nueva contraseña — Reverse Market</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:#040810;color:#f0f4ff;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;-webkit-font-smoothing:antialiased}
.wrap{width:100%;max-width:420px}
.card{background:#0c1422;border:1px solid rgba(0,255,200,.12);border-radius:18px;padding:2.25rem;box-shadow:0 16px 56px rgba(0,0,0,.6)}
.brand{font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;margin-bottom:1.5rem;color:#f0f4ff}
.brand span{color:#00ffc8}
.icon{width:52px;height:52px;border-radius:14px;background:rgba(0,255,200,.08);border:1px solid rgba(0,255,200,.18);display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:1.1rem}
h2{font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;letter-spacing:-.03em;margin-bottom:.4rem}
.sub{font-size:.88rem;color:#6b7fa3;margin-bottom:1.75rem;font-weight:300}
.form-label{display:block;font-size:.73rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b7fa3;margin-bottom:.4rem}
.form-input{width:100%;background:#0a1120;border:1.5px solid rgba(0,255,200,.1);border-radius:10px;padding:.8rem 1rem;color:#f0f4ff;font-family:'DM Sans',sans-serif;font-size:.92rem;transition:all .22s;margin-bottom:1rem}
.form-input:focus{outline:none;border-color:#00ffc8;box-shadow:0 0 0 3px rgba(0,255,200,.1)}
.form-input::placeholder{color:#2d3d5a}
.strength-bar{height:4px;border-radius:2px;background:#0a1120;margin:-8px 0 1rem;overflow:hidden;transition:all .3s}
.strength-fill{height:100%;border-radius:2px;width:0;transition:all .35s}
.btn{width:100%;padding:.9rem;background:#00ffc8;color:#040810;border:none;border-radius:10px;font-weight:800;font-size:.95rem;cursor:pointer;font-family:'Syne',sans-serif;box-shadow:0 4px 20px rgba(0,255,200,.28);transition:all .22s}
.btn:hover{transform:translateY(-1px)}
.alert{border-radius:12px;padding:1rem 1.1rem;margin-bottom:1.25rem;font-size:.875rem;line-height:1.6}
.alert-success{background:rgba(6,214,160,.08);border:1px solid rgba(6,214,160,.22);color:#06d6a0}
.alert-error{background:rgba(255,77,109,.08);border:1px solid rgba(255,77,109,.22);color:#ff4d6d}
.footer-link{margin-top:1.5rem;text-align:center;font-size:.84rem;color:#6b7fa3}
.footer-link a{color:#00ffc8;font-weight:600}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <div class="brand">Reverse<span>Market</span></div>
    <div class="icon">🔒</div>
    <h2>Nueva contraseña</h2>
    <?php if ($user && $valid): ?>
      <p class="sub">Hola <strong style="color:#f0f4ff"><?= htmlspecialchars($user['nombre']) ?></strong>, crea tu nueva contraseña.</p>
    <?php else: ?>
      <p class="sub">Restablece el acceso a tu cuenta.</p>
    <?php endif; ?>

    <?php if ($msg): ?>
      <div class="alert alert-<?= $tipo ?>">
        <?= htmlspecialchars($msg) ?>
        <?php if ($tipo === 'success'): ?>
          <div style="margin-top:.75rem"><a href="login.php" style="color:#00ffc8;font-weight:700">Ir al login →</a></div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($valid): ?>
    <form method="POST">
      <div>
        <label class="form-label">Nueva contraseña</label>
        <input type="password" name="password" id="pass1" class="form-input"
               placeholder="Mínimo 6 caracteres" required minlength="6"
               oninput="checkStrength(this.value)">
        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
      </div>
      <div>
        <label class="form-label">Confirmar contraseña</label>
        <input type="password" name="password2" id="pass2" class="form-input"
               placeholder="Repite la contraseña" required oninput="checkMatch()">
        <div id="matchMsg" style="font-size:.76rem;margin:-8px 0 1rem;min-height:16px"></div>
      </div>
      <button type="submit" class="btn">🔒 Guardar nueva contraseña</button>
    </form>
    <?php elseif ($tipo !== 'success'): ?>
      <div class="footer-link">
        <a href="recuperar_password.php">Solicitar nuevo enlace</a>
      </div>
    <?php endif; ?>

    <div class="footer-link">
      <a href="login.php">← Volver al login</a>
    </div>
  </div>
</div>
<script>
function checkStrength(v) {
  const fill = document.getElementById('strengthFill');
  let score = 0;
  if (v.length >= 6)  score++;
  if (v.length >= 10) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  const colors = ['#ff4d6d','#ff4d6d','#ffbe0b','#06d6a0','#00ffc8'];
  const widths  = ['20%','35%','55%','80%','100%'];
  fill.style.width = widths[Math.min(score,4)];
  fill.style.background = colors[Math.min(score,4)];
}
function checkMatch() {
  const p1 = document.getElementById('pass1').value;
  const p2 = document.getElementById('pass2').value;
  const msg = document.getElementById('matchMsg');
  if (!p2) { msg.textContent=''; return; }
  if (p1 === p2) { msg.textContent='✓ Las contraseñas coinciden'; msg.style.color='#06d6a0'; }
  else           { msg.textContent='✕ No coinciden'; msg.style.color='#ff4d6d'; }
}
</script>
</body>
</html>
