<?php
require_once __DIR__ . '/../../config/session.php';
$flash_ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre  = trim($_POST['nombre'] ?? '');
    $correo  = trim($_POST['correo'] ?? '');
    $asunto  = trim($_POST['asunto'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    if ($nombre && $correo && $mensaje) {
        // In production: send email here
        $flash_ok = '✅ Mensaje enviado. Te responderemos en menos de 24 horas.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Contacto — Reverse Market</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#040810;--bg2:#070d1a;--card:#0c1422;--panel:#0f1929;
  --border:rgba(0,255,200,.1);--accent:#00ffc8;--accent-dim:#00a88a;
  --text:#f0f4ff;--muted:#6b7fa3;--dim:#2d3d5a;
  --success:#06d6a0;--danger:#ff4d6d;
  --font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;
  --radius:16px;--radius-sm:10px;--transition:all .22s ease;
}
body{font-family:var(--font-body);background:var(--bg);color:var(--text);min-height:100vh;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}

/* NAV */
.nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;padding:0 4vw;height:68px;background:rgba(4,8,16,.85);backdrop-filter:blur(20px);border-bottom:1px solid var(--border)}
.nav-logo{font-family:var(--font-display);font-size:1.25rem;font-weight:800;letter-spacing:-.02em}
.nav-logo span{color:var(--accent)}
.nav-back{margin-left:auto;display:inline-flex;align-items:center;gap:.4rem;font-size:.85rem;color:var(--muted);transition:color .2s}
.nav-back:hover{color:var(--accent)}

/* PAGE */
.page{min-height:100vh;padding:8rem 4vw 5rem;display:flex;align-items:flex-start;justify-content:center}
.page-inner{width:100%;max-width:1100px;display:grid;grid-template-columns:1fr 1.4fr;gap:4rem;align-items:start}

/* LEFT INFO */
.contact-info{position:sticky;top:7rem}
.eyebrow{font-size:.7rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--accent);margin-bottom:.75rem}
.info-h{font-family:var(--font-display);font-size:2.4rem;font-weight:800;letter-spacing:-.04em;line-height:1.1;margin-bottom:1rem}
.info-sub{font-size:.95rem;color:var(--muted);line-height:1.75;font-weight:300;margin-bottom:2.5rem;max-width:380px}
.info-cards{display:flex;flex-direction:column;gap:.85rem}
.info-card{display:flex;align-items:flex-start;gap:1rem;background:var(--card);border:1px solid var(--border);border-radius:var(--radius-sm);padding:1.1rem 1.25rem;transition:var(--transition)}
.info-card:hover{border-color:rgba(0,255,200,.25);transform:translateX(4px)}
.info-icon{width:42px;height:42px;border-radius:10px;background:rgba(0,255,200,.08);border:1px solid rgba(0,255,200,.15);display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}
.info-card-title{font-weight:700;font-size:.88rem;margin-bottom:.2rem}
.info-card-text{font-size:.82rem;color:var(--muted);font-weight:300;line-height:1.5}

/* FORM */
.form-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:2.5rem;position:relative;overflow:hidden}
.form-card::before{content:'';position:absolute;top:-60px;right:-60px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(0,255,200,.05),transparent 65%)}
.form-h2{font-family:var(--font-display);font-size:1.35rem;font-weight:800;margin-bottom:.35rem}
.form-sub2{font-size:.84rem;color:var(--muted);margin-bottom:1.75rem;font-weight:300}
.form-group{margin-bottom:1.25rem;position:relative}
.form-label{display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin-bottom:.4rem}
.form-input{width:100%;background:#0a1120;border:1.5px solid rgba(0,255,200,.1);border-radius:var(--radius-sm);padding:.8rem 1rem;color:var(--text);font-family:var(--font-body);font-size:.92rem;transition:all .22s}
.form-input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(0,255,200,.08);background:var(--card)}
.form-input::placeholder{color:var(--dim)}
textarea.form-input{resize:vertical;min-height:130px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.btn-submit{width:100%;padding:.95rem;background:var(--accent);color:#040810;border:none;border-radius:var(--radius-sm);font-weight:800;font-size:1rem;cursor:pointer;font-family:var(--font-display);box-shadow:0 4px 24px rgba(0,255,200,.3);transition:all .22s;display:flex;align-items:center;justify-content:center;gap:.5rem;margin-top:.5rem}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 6px 32px rgba(0,255,200,.42)}
.btn-submit:active{transform:translateY(0)}
.success-box{background:rgba(6,214,160,.08);border:1px solid rgba(6,214,160,.25);border-radius:var(--radius-sm);padding:1rem 1.25rem;display:flex;align-items:center;gap:.85rem;margin-bottom:1.5rem;font-size:.88rem;color:var(--success)}
.topic-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-bottom:1.25rem}
.topic-chip{padding:.45rem .6rem;border:1.5px solid var(--border);border-radius:8px;font-size:.76rem;cursor:pointer;text-align:center;transition:all .2s;color:var(--muted);background:transparent}
.topic-chip:hover,.topic-chip.active{border-color:var(--accent);color:var(--accent);background:rgba(0,255,200,.06)}
.char-count{position:absolute;right:.75rem;bottom:.75rem;font-size:.7rem;color:var(--dim)}
@media(max-width:860px){.page-inner{grid-template-columns:1fr}.contact-info{position:static}.info-h{font-size:1.8rem}.form-row{grid-template-columns:1fr}}
</style>
</head>
<body>

<nav class="nav">
  <a href="../index.php" class="nav-logo">Reverse<span>Market</span></a>
  <a href="../index.php" class="nav-back">← Volver al inicio</a>
</nav>

<div class="page">
  <div class="page-inner">

    <!-- LEFT: Info -->
    <div class="contact-info">
      <div class="eyebrow">Soporte & Contacto</div>
      <h1 class="info-h">¿En qué podemos<br>ayudarte?</h1>
      <p class="info-sub">Nuestro equipo está listo para resolver tus dudas sobre la plataforma, pagos, ofertas o cualquier problema técnico.</p>

      <div class="info-cards">
        <div class="info-card">
          <div class="info-icon">💬</div>
          <div>
            <div class="info-card-title">Chat en vivo</div>
            <div class="info-card-text">Disponible dentro de la plataforma para compradores y vendedores registrados</div>
          </div>
        </div>
        <div class="info-card">
          <div class="info-icon">📧</div>
          <div>
            <div class="info-card-title">Correo electrónico</div>
            <div class="info-card-text">soporte@reversemarket.co · Respuesta en menos de 24 horas hábiles</div>
          </div>
        </div>
        <div class="info-card">
          <div class="info-icon">⏰</div>
          <div>
            <div class="info-card-title">Horario de atención</div>
            <div class="info-card-text">Lunes a viernes · 8:00 AM — 6:00 PM · Hora Colombia</div>
          </div>
        </div>
        <div class="info-card">
          <div class="info-icon">🚨</div>
          <div>
            <div class="info-card-title">Urgente / Reportes</div>
            <div class="info-card-text">Usa el botón "Reportar" dentro de la plataforma para casos de fraude o abuso</div>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT: Form -->
    <div class="form-card">
      <h2 class="form-h2">Envíanos un mensaje</h2>
      <p class="form-sub2">Completa el formulario y te responderemos lo antes posible</p>

      <?php if ($flash_ok): ?>
        <div class="success-box">
          <span style="font-size:1.25rem">✅</span>
          <div><?= htmlspecialchars($flash_ok) ?></div>
        </div>
      <?php endif; ?>

      <form method="POST" id="contactForm">
        <!-- Topic chips -->
        <div>
          <label class="form-label">Tema del mensaje</label>
          <div class="topic-grid">
            <?php foreach(['Pagos','Ofertas','Cuenta','Producto','Error técnico','Otro'] as $t): ?>
            <div class="topic-chip" onclick="selectTopic(this,'<?= $t ?>')"><?= $t ?></div>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="asunto" id="asunto-hidden" value="">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Nombre *</label>
            <input type="text" name="nombre" class="form-input" placeholder="Tu nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Correo electrónico *</label>
            <input type="email" name="correo" class="form-input" placeholder="tu@correo.com" required value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group" style="position:relative">
          <label class="form-label">Mensaje * <span id="charLeft" style="font-size:.7rem;color:var(--dim);text-transform:none;letter-spacing:0;font-weight:400">(0/500)</span></label>
          <textarea name="mensaje" class="form-input" placeholder="Describe tu consulta o problema con el mayor detalle posible..." required maxlength="500" oninput="updateCount(this)"><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn-submit">
          <span>📨</span> Enviar mensaje
        </button>

        <p style="text-align:center;font-size:.76rem;color:var(--dim);margin-top:1rem">
          🔒 Tu información está protegida y no será compartida con terceros
        </p>
      </form>
    </div>

  </div>
</div>

<script>
function selectTopic(el, topic) {
  document.querySelectorAll('.topic-chip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('asunto-hidden').value = topic;
}

function updateCount(el) {
  document.getElementById('charLeft').textContent = '(' + el.value.length + '/500)';
}
</script>
</body>
</html>
