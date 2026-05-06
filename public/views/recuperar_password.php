<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';

$paso  = $_SESSION['recuperar_paso']  ?? 1;
$email = $_SESSION['recuperar_email'] ?? '';
$msg   = '';
$tipo  = '';

// Create table if missing
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS recuperacion_password (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        token VARCHAR(64) NOT NULL,
        expira DATETIME NOT NULL,
        usado TINYINT DEFAULT 0,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
    )");
} catch(PDOException $e){}

// Reset session
if (isset($_GET['reiniciar'])) {
    unset($_SESSION['recuperar_paso'], $_SESSION['recuperar_email'],
          $_SESSION['recuperar_user_id'], $_SESSION['recuperar_token_id'],
          $_SESSION['codigo_dev'], $_SESSION['codigo_visible']);
    header("Location: recuperar_password.php"); exit;
}

// ── PASO 1: Ingresar correo ───────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['paso1'])) {
    $email_input = trim($_POST['email'] ?? '');

    if (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Ingresa un correo electrónico válido.'; $tipo = 'error';
    } else {
        $st = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE correo=?");
        $st->execute([$email_input]);
        $user = $st->fetch();

        if ($user) {
            $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expira = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            try { $pdo->prepare("DELETE FROM recuperacion_password WHERE id_usuario=?")->execute([$user['id']]); } catch(PDOException $e){}
            $pdo->prepare("INSERT INTO recuperacion_password (id_usuario, token, expira) VALUES (?,?,?)")
                ->execute([$user['id'], $codigo, $expira]);

            // Try to send email (optional)
            $email_enviado = false;
            if (file_exists(__DIR__ . '/../../config/mailer.php')) {
                try {
                    require_once __DIR__ . '/../../config/mailer.php';
                    $email_enviado = enviar_codigo_email($email_input, $user['nombre'], $codigo);
                } catch(Exception $e){}
            }

            $_SESSION['recuperar_paso']    = 2;
            $_SESSION['recuperar_email']   = $email_input;
            $_SESSION['recuperar_user_id'] = $user['id'];
            $_SESSION['codigo_visible']    = $codigo; // Always store for display
            $_SESSION['email_enviado']     = $email_enviado;
        } else {
            // User not found - still go to step 2 but with fake code (security)
            $_SESSION['recuperar_paso']    = 2;
            $_SESSION['recuperar_email']   = $email_input;
            $_SESSION['recuperar_user_id'] = null;
            $_SESSION['codigo_visible']    = null;
            $_SESSION['email_enviado']     = false;
        }
        header("Location: recuperar_password.php"); exit;
    }
}

// ── PASO 2: Verificar código ─────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['paso2'])) {
    $codigo_input = trim(str_replace([' ','-'], '', $_POST['codigo'] ?? ''));
    $uid = $_SESSION['recuperar_user_id'] ?? null;

    if (!$uid) {
        $msg = 'Correo no encontrado en el sistema.'; $tipo = 'error';
    } else {
        $st = $pdo->prepare("SELECT * FROM recuperacion_password WHERE id_usuario=? AND token=? AND usado=0 AND expira > NOW()");
        $st->execute([$uid, $codigo_input]);
        $row = $st->fetch();

        if ($row) {
            $_SESSION['recuperar_paso']     = 3;
            $_SESSION['recuperar_token_id'] = $row['id'];
            unset($_SESSION['codigo_visible']);
            header("Location: recuperar_password.php"); exit;
        } else {
            $msg = 'Código incorrecto o expirado. Revisa el código e intenta de nuevo.';
            $tipo = 'error';
        }
    }
}

// ── PASO 3: Nueva contraseña ─────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['paso3'])) {
    $pass1 = $_POST['password']  ?? '';
    $pass2 = $_POST['password2'] ?? '';
    $uid   = $_SESSION['recuperar_user_id']  ?? null;
    $tid   = $_SESSION['recuperar_token_id'] ?? null;

    if (!$uid || !$tid) { $msg = 'Sesión expirada. Empieza de nuevo.'; $tipo = 'error'; }
    elseif (strlen($pass1) < 6) { $msg = 'Mínimo 6 caracteres.'; $tipo = 'error'; }
    elseif ($pass1 !== $pass2)  { $msg = 'Las contraseñas no coinciden.'; $tipo = 'error'; }
    else {
        $pdo->prepare("UPDATE usuarios SET contraseña=? WHERE id=?")->execute([password_hash($pass1, PASSWORD_DEFAULT), $uid]);
        $pdo->prepare("UPDATE recuperacion_password SET usado=1 WHERE id=?")->execute([$tid]);
        unset($_SESSION['recuperar_paso'], $_SESSION['recuperar_email'],
              $_SESSION['recuperar_user_id'], $_SESSION['recuperar_token_id'],
              $_SESSION['codigo_visible'], $_SESSION['email_enviado']);
        $tipo = 'exito_final';
    }
}

$paso  = $_SESSION['recuperar_paso']  ?? 1;
$email = $_SESSION['recuperar_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Recuperar contraseña — Reverse Market</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:#040810;color:#f0f4ff;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;-webkit-font-smoothing:antialiased}
.wrap{width:100%;max-width:440px}
.back{display:inline-flex;align-items:center;gap:.4rem;font-size:.82rem;color:#6b7fa3;text-decoration:none;margin-bottom:1.5rem;padding:.45rem .9rem;border:1px solid rgba(255,255,255,.08);border-radius:8px;transition:all .2s}
.back:hover{color:#00ffc8;border-color:rgba(0,255,200,.3)}
.card{background:#0c1422;border:1px solid rgba(0,255,200,.12);border-radius:18px;padding:2.25rem;box-shadow:0 16px 56px rgba(0,0,0,.6)}
.brand{font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;margin-bottom:1.5rem}
.brand span{color:#00ffc8}
.steps{display:flex;align-items:center;gap:.5rem;margin-bottom:1.75rem}
.step-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800;flex-shrink:0}
.step-dot.done{background:#00ffc8;color:#040810}
.step-dot.active{background:rgba(0,255,200,.12);border:2px solid #00ffc8;color:#00ffc8}
.step-dot.pending{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#2d3d5a}
.step-line{flex:1;height:1px;background:rgba(255,255,255,.08)}
.step-line.done{background:#00ffc8}
.icon{width:52px;height:52px;border-radius:14px;background:rgba(0,255,200,.08);border:1px solid rgba(0,255,200,.15);display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:1rem}
h2{font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:800;letter-spacing:-.03em;margin-bottom:.4rem}
.sub{font-size:.86rem;color:#6b7fa3;margin-bottom:1.5rem;font-weight:300;line-height:1.65}
.lbl{display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b7fa3;margin-bottom:.4rem}
.inp{width:100%;background:#0a1120;border:1.5px solid rgba(0,255,200,.1);border-radius:10px;padding:.8rem 1rem;color:#f0f4ff;font-family:'DM Sans',sans-serif;font-size:.92rem;transition:all .22s;margin-bottom:1rem}
.inp:focus{outline:none;border-color:#00ffc8;box-shadow:0 0 0 3px rgba(0,255,200,.1);background:#0c1422}
.inp::placeholder{color:#2d3d5a}
.code-inp{text-align:center;font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;letter-spacing:.4em;border-radius:12px}
.btn{width:100%;padding:.9rem;background:#00ffc8;color:#040810;border:none;border-radius:10px;font-weight:800;font-size:.95rem;cursor:pointer;font-family:'Syne',sans-serif;box-shadow:0 4px 20px rgba(0,255,200,.28);transition:all .22s;display:flex;align-items:center;justify-content:center;gap:.5rem}
.btn:hover{transform:translateY(-1px);box-shadow:0 6px 28px rgba(0,255,200,.4)}
.alert{border-radius:10px;padding:.9rem 1rem;margin-bottom:1.25rem;font-size:.875rem;line-height:1.6;display:flex;align-items:flex-start;gap:.65rem}
.alert-error{background:rgba(255,77,109,.08);border:1px solid rgba(255,77,109,.22);color:#ff4d6d}
/* Code display box */
.code-box{background:#070d1a;border:2px solid #00ffc8;border-radius:16px;padding:1.5rem;text-align:center;margin-bottom:1.5rem;position:relative}
.code-box-label{font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#6b7fa3;margin-bottom:.5rem}
.code-number{font-family:'Syne',sans-serif;font-size:3rem;font-weight:800;color:#00ffc8;letter-spacing:.3em;margin:.3rem 0}
.code-expiry{font-size:.76rem;color:#ffbe0b;margin-top:.35rem}
.copy-btn{position:absolute;top:.75rem;right:.75rem;background:rgba(0,255,200,.1);border:1px solid rgba(0,255,200,.2);color:#00ffc8;border-radius:7px;padding:.3rem .65rem;font-size:.72rem;cursor:pointer;font-family:inherit;transition:all .2s}
.copy-btn:hover{background:rgba(0,255,200,.2)}
.strength-bar{height:4px;border-radius:2px;background:#0a1120;margin-bottom:1rem;overflow:hidden}
.sf{height:100%;border-radius:2px;width:0;transition:all .35s}
.foot{margin-top:1.25rem;text-align:center;font-size:.82rem;color:#6b7fa3}
.foot a{color:#00ffc8;font-weight:600;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
  <a href="<?= $paso===1 ? '../index.php' : '?reiniciar=1' ?>" class="back">
    ← <?= $paso===1 ? 'Volver al inicio' : 'Volver' ?>
  </a>

  <?php if ($tipo === 'exito_final'): ?>
  <div class="card" style="text-align:center">
    <div class="brand">Reverse<span>Market</span></div>
    <div style="font-size:3rem;margin:1rem 0">✅</div>
    <h2>¡Contraseña actualizada!</h2>
    <p class="sub" style="margin-bottom:1.5rem">Ya puedes iniciar sesión con tu nueva contraseña.</p>
    <a href="login.php" class="btn" style="text-decoration:none">Ir al login →</a>
  </div>

  <?php else: ?>
  <div class="card">
    <div class="brand">Reverse<span>Market</span></div>

    <!-- Steps -->
    <div class="steps">
      <div class="step-dot <?= $paso>=1?($paso>1?'done':'active'):'pending' ?>">1</div>
      <div class="step-line <?= $paso>1?'done':'' ?>"></div>
      <div class="step-dot <?= $paso>=2?($paso>2?'done':'active'):'pending' ?>">2</div>
      <div class="step-line <?= $paso>2?'done':'' ?>"></div>
      <div class="step-dot <?= $paso>=3?'active':'pending' ?>">3</div>
    </div>

    <?php if ($msg): ?>
      <div class="alert alert-error"><span>✕</span><span><?= htmlspecialchars($msg) ?></span></div>
    <?php endif; ?>

    <?php if ($paso === 1): ?>
    <!-- PASO 1 -->
    <div class="icon">📧</div>
    <h2>¿Olvidaste tu contraseña?</h2>
    <p class="sub">Ingresa tu correo y te mostraremos un código para restablecer tu contraseña.</p>
    <form method="POST">
      <label class="lbl">Tu correo registrado</label>
      <input type="email" name="email" class="inp" placeholder="tucorreo@ejemplo.com" required autofocus>
      <button type="submit" name="paso1" class="btn">Continuar →</button>
    </form>
    <div class="foot"><a href="login.php">← Volver al login</a></div>

    <?php elseif ($paso === 2): ?>
    <!-- PASO 2 -->
    <div class="icon">🔢</div>
    <h2>Ingresa el código</h2>
    <p class="sub">Generamos un código para <strong style="color:#f0f4ff"><?= htmlspecialchars($email) ?></strong></p>

    <?php if (!empty($_SESSION['codigo_visible'])): ?>
    <!-- Show code directly on screen -->
    <div class="code-box">
      <button class="copy-btn" onclick="copiarCodigo()">📋 Copiar</button>
      <div class="code-box-label">Tu código de verificación</div>
      <div class="code-number" id="codigoNum"><?= $_SESSION['codigo_visible'] ?></div>
      <div class="code-expiry">⏱ Válido por 15 minutos</div>
    </div>
    <?php elseif (!empty($_SESSION['email_enviado'])): ?>
    <div style="background:rgba(0,255,200,.06);border:1px solid rgba(0,255,200,.18);border-radius:10px;padding:.9rem 1rem;margin-bottom:1.25rem;font-size:.85rem;color:#00ffc8">
      📬 Código enviado a tu correo. Revisa también la carpeta de spam.
    </div>
    <?php endif; ?>

    <form method="POST">
      <label class="lbl">Código de 6 dígitos</label>
      <input type="text" name="codigo" class="inp code-inp"
             placeholder="000000" maxlength="6"
             inputmode="numeric" autocomplete="one-time-code" required autofocus>
      <button type="submit" name="paso2" class="btn">Verificar código →</button>
    </form>
    <div style="margin-top:.85rem;text-align:center">
      <a href="?reiniciar=1" style="font-size:.82rem;color:#6b7fa3;text-decoration:none">Usar otro correo</a>
    </div>

    <?php elseif ($paso === 3): ?>
    <!-- PASO 3 -->
    <div class="icon">🔒</div>
    <h2>Nueva contraseña</h2>
    <p class="sub">Elige una contraseña segura de al menos 6 caracteres.</p>
    <form method="POST">
      <label class="lbl">Nueva contraseña</label>
      <input type="password" name="password" id="p1" class="inp"
             placeholder="Mínimo 6 caracteres" required minlength="6"
             oninput="fuerza(this.value)">
      <div class="strength-bar"><div class="sf" id="sf"></div></div>
      <label class="lbl">Confirmar contraseña</label>
      <input type="password" name="password2" id="p2" class="inp"
             placeholder="Repite tu contraseña" required oninput="coincide()">
      <div id="matchMsg" style="font-size:.75rem;margin:-8px 0 1rem;min-height:16px"></div>
      <button type="submit" name="paso3" class="btn">🔒 Guardar nueva contraseña</button>
    </form>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<script>
// Copy code to clipboard
function copiarCodigo() {
  const code = document.getElementById('codigoNum')?.textContent?.trim();
  if (!code) return;
  navigator.clipboard.writeText(code).then(() => {
    const btn = document.querySelector('.copy-btn');
    btn.textContent = '✓ Copiado';
    btn.style.background = 'rgba(6,214,160,.2)';
    btn.style.color = '#06d6a0';
    setTimeout(() => { btn.textContent = '📋 Copiar'; btn.style.background=''; btn.style.color=''; }, 2000);
  });
}
// Auto-paste code in input if copied
const ci = document.querySelector('.code-inp');
if (ci) {
  ci.addEventListener('input', function() {
    this.value = this.value.replace(/\D/g,'').slice(0,6);
  });
  // Auto-fill from clipboard on focus
  ci.addEventListener('focus', async function() {
    try {
      const text = await navigator.clipboard.readText();
      if (/^\d{6}$/.test(text.trim())) this.value = text.trim();
    } catch(e){}
  });
}
// Password strength
function fuerza(v){
  let s=0;
  if(v.length>=6)s++;if(v.length>=10)s++;
  if(/[A-Z]/.test(v))s++;if(/[0-9]/.test(v))s++;if(/[^A-Za-z0-9]/.test(v))s++;
  const c=['#ff4d6d','#ff4d6d','#ffbe0b','#06d6a0','#00ffc8'];
  const w=['20%','35%','55%','80%','100%'];
  const f=document.getElementById('sf');
  if(f){f.style.width=w[Math.min(s,4)];f.style.background=c[Math.min(s,4)];}
}
function coincide(){
  const p1=document.getElementById('p1')?.value;
  const p2=document.getElementById('p2')?.value;
  const m=document.getElementById('matchMsg');
  if(!m||!p2)return;
  m.textContent=p1===p2?'✓ Contraseñas coinciden':'✕ No coinciden';
  m.style.color=p1===p2?'#06d6a0':'#ff4d6d';
}
</script>
</body>
</html>
