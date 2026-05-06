<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header("Location: login.php"); exit; }

$flash_ok = ''; $flash_err = '';
$config_file = __DIR__ . '/../../config/email_config.php';

// Read current values
$current = ['SMTP_USER'=>'','SMTP_PASS'=>'','SMTP_HOST'=>'smtp.gmail.com','SMTP_PORT'=>'587'];
if (file_exists($config_file)) {
    $content = file_get_contents($config_file);
    foreach ($current as $k => $_) {
        if (preg_match("/define\('$k',\s*'([^']*)'\)/", $content, $m)) $current[$k] = $m[1];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $host = trim($_POST['host'] ?? 'smtp.gmail.com');
    $port = (int)($_POST['port'] ?? 587);
    $user = trim($_POST['user'] ?? '');
    $pass = trim($_POST['pass'] ?? '');

    if (!$user || !$pass) { $flash_err = 'Completa el correo y la contraseña.'; }
    else {
        $content = "<?php\n";
        $content .= "define('SMTP_HOST',     '$host');\n";
        $content .= "define('SMTP_PORT',     $port);\n";
        $content .= "define('SMTP_USER',     '$user');\n";
        $content .= "define('SMTP_PASS',     '$pass');\n";
        $content .= "define('SMTP_FROM',     '$user');\n";
        $content .= "define('SMTP_FROMNAME', 'Reverse Market');\n";
        $content .= "define('APP_URL', 'http://" . $_SERVER['HTTP_HOST'] . "/Proyecto_Reverse_Market/public');\n";

        if (file_put_contents($config_file, $content)) {
            $flash_ok = '✅ Configuración guardada correctamente.';
            $current['SMTP_USER'] = $user; $current['SMTP_HOST'] = $host; $current['SMTP_PORT'] = $port;
        } else {
            $flash_err = 'No se pudo guardar. Verifica permisos del archivo.';
        }
    }
}

// Test email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test'])) {
    require_once __DIR__ . '/../../config/email_config.php';
    require_once __DIR__ . '/../../config/PHPMailer.php';
    require_once __DIR__ . '/../../config/mailer.php';
    $test_email = trim($_POST['test_email'] ?? $_SESSION['correo'] ?? '');
    if ($test_email) {
        $ok = enviar_codigo_email($test_email, 'Administrador', '123456');
        $flash_ok = $ok ? '✅ Email de prueba enviado a ' . $test_email : '❌ Error al enviar. Verifica tus credenciales.';
    }
}
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Config Email — Admin</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head><body>
<div class="dashboard-layout">
<?php include __DIR__ . '/../partials/sidebar_admin.php'; ?>
<div class="main-content">
  <?php if($flash_ok):?><span data-toast data-type="success" data-msg="<?=htmlspecialchars($flash_ok)?>"></span><?php endif;?>
  <?php if($flash_err):?><span data-toast data-type="error" data-msg="<?=htmlspecialchars($flash_err)?>"></span><?php endif;?>

  <div class="page-header">
    <div><h1 class="page-title">📧 Configuración de Correo</h1><p class="page-subtitle">Activa el envío de emails para recuperar contraseña</p></div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;max-width:900px">

    <!-- Config Form -->
    <div class="card">
      <div class="card-header"><span class="card-title">⚙️ Credenciales SMTP</span></div>
      <div class="card-body">
        <form method="POST" style="display:flex;flex-direction:column;gap:1.1rem">

          <div style="background:rgba(0,255,200,.06);border:1px solid rgba(0,255,200,.15);border-radius:10px;padding:1rem;font-size:.82rem;color:var(--text-muted);line-height:1.7">
            <strong style="color:var(--text-primary);display:block;margin-bottom:.35rem">📌 Opción recomendada: Gmail</strong>
            1. Activa verificación en 2 pasos en Gmail<br>
            2. Ve a <strong style="color:var(--accent)">myaccount.google.com/apppasswords</strong><br>
            3. Crea una contraseña → copia las 16 letras<br>
            4. Pégala abajo en "Contraseña"
          </div>

          <div>
            <label class="form-label">Servidor SMTP</label>
            <select name="host" class="form-control" onchange="updatePort(this)">
              <option value="smtp.gmail.com" <?=$current['SMTP_HOST']==='smtp.gmail.com'?'selected':''?>>Gmail (smtp.gmail.com)</option>
              <option value="smtp-mail.outlook.com" <?=$current['SMTP_HOST']==='smtp-mail.outlook.com'?'selected':''?>>Outlook / Hotmail</option>
              <option value="smtp.yahoo.com" <?=$current['SMTP_HOST']==='smtp.yahoo.com'?'selected':''?>>Yahoo Mail</option>
            </select>
          </div>

          <div><label class="form-label">Correo electrónico (remitente)</label>
            <input type="email" name="user" class="form-control" placeholder="tucorreo@gmail.com" value="<?=htmlspecialchars($current['SMTP_USER'])?>" required></div>

          <div><label class="form-label">Contraseña de aplicación</label>
            <input type="password" name="pass" class="form-control" placeholder="<?=$current['SMTP_PASS']?'••••••••••••••••':'abcdefghijklmnop (16 caracteres)'?>" <?=$current['SMTP_PASS']?'':'required'?>>
            <div style="font-size:.74rem;color:var(--text-dim);margin-top:.3rem">Para Gmail: contraseña de aplicación (no tu contraseña normal)</div>
          </div>

          <button type="submit" name="guardar" class="btn btn-primary">💾 Guardar configuración</button>
        </form>
      </div>
    </div>

    <!-- Status + Test -->
    <div style="display:flex;flex-direction:column;gap:1rem">
      <!-- Status -->
      <div class="card">
        <div class="card-header"><span class="card-title">📊 Estado actual</span></div>
        <div class="card-body">
          <?php if ($current['SMTP_USER']): ?>
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.85rem">
              <div style="width:10px;height:10px;border-radius:50%;background:var(--success);box-shadow:0 0 8px rgba(6,214,160,.5)"></div>
              <span style="font-size:.88rem;color:var(--success);font-weight:700">Email configurado</span>
            </div>
            <div style="font-size:.82rem;color:var(--text-muted)">
              Remitente: <strong style="color:var(--text-primary)"><?=htmlspecialchars($current['SMTP_USER'])?></strong><br>
              Servidor: <?=htmlspecialchars($current['SMTP_HOST'])?>:<?=htmlspecialchars($current['SMTP_PORT'])?>
            </div>
          <?php else: ?>
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.85rem">
              <div style="width:10px;height:10px;border-radius:50%;background:var(--warning)"></div>
              <span style="font-size:.88rem;color:var(--warning);font-weight:700">Sin configurar</span>
            </div>
            <p style="font-size:.82rem;color:var(--text-muted)">Los códigos se muestran en pantalla (modo desarrollo).</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Test -->
      <?php if ($current['SMTP_USER']): ?>
      <div class="card">
        <div class="card-header"><span class="card-title">🧪 Enviar email de prueba</span></div>
        <div class="card-body">
          <form method="POST" style="display:flex;flex-direction:column;gap:.85rem">
            <div><label class="form-label">Correo destino</label>
              <input type="email" name="test_email" class="form-control" placeholder="test@ejemplo.com" value="<?=htmlspecialchars($_SESSION['correo']??'')?>"></div>
            <button type="submit" name="test" class="btn btn-secondary">📨 Enviar prueba</button>
          </form>
        </div>
      </div>
      <?php endif; ?>

      <!-- Outlook tip -->
      <div style="background:rgba(37,99,235,.08);border:1px solid rgba(37,99,235,.2);border-radius:var(--radius);padding:1.1rem;font-size:.82rem;color:var(--text-muted);line-height:1.7">
        <strong style="color:#60a5fa;display:block;margin-bottom:.4rem">💡 Tip: Outlook es más fácil</strong>
        Con <strong>Outlook/Hotmail</strong> no necesitas contraseña de aplicación — usa tu contraseña normal y funciona directamente.
      </div>
    </div>
  </div>
</div></div>
<script>
function updatePort(sel) {
  const ports = {'smtp.gmail.com':587,'smtp-mail.outlook.com':587,'smtp.yahoo.com':587};
  // Port is auto-selected internally
}
</script>
</body></html>
