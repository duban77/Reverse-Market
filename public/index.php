<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['rol'])) {
    switch ($_SESSION['rol']) {
        case 'comprador': header("Location: views/home_comprador.php"); exit;
        case 'vendedor':  header("Location: views/home_vendedor.php");  exit;
        case 'admin':     header("Location: views/admin_dashboard.php"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reverse Market — El mercado que te trabaja a ti</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<style>
/* ════════════════════════════════════════════
   REVERSE MARKET — Landing v3
   Dark luxury · Teal accent · Glass morphism
   ════════════════════════════════════════════ */
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#040810;--bg2:#070d1a;--card:#0c1422;--panel:#0f1929;
  --border:rgba(0,255,200,.1);--border2:rgba(255,255,255,.06);
  --accent:#00ffc8;--accent2:#00d4aa;--blue:#2563eb;
  --text:#f0f4ff;--muted:#6b7fa3;--dim:#2d3d5a;
  --r:18px;--r-sm:10px;
  --shadow-accent:0 0 60px rgba(0,255,200,.15);
  --font-display:'Syne',sans-serif;
  --font-body:'DM Sans',sans-serif;
}
html{scroll-behavior:smooth;font-size:16px}
body{font-family:var(--font-body);background:var(--bg);color:var(--text);overflow-x:hidden;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}
img{display:block;max-width:100%}

/* ── NAVBAR ─────────────────────────────── */
.nav{
  position:fixed;top:0;left:0;right:0;z-index:1000;
  display:flex;align-items:center;padding:0 4vw;height:72px;
  background:rgba(4,8,16,.75);backdrop-filter:blur(24px) saturate(1.5);
  border-bottom:1px solid var(--border);
  transition:all .3s;
}
.nav-logo{
  font-family:var(--font-display);font-size:1.4rem;font-weight:800;
  letter-spacing:-.03em;color:var(--text);
}
.nav-logo span{color:var(--accent)}
.nav-links{display:flex;gap:2.5rem;margin:0 auto}
.nav-links a{font-size:.88rem;font-weight:500;color:var(--muted);transition:color .2s;letter-spacing:.01em}
.nav-links a:hover{color:var(--accent)}
.nav-cta{display:flex;gap:.75rem;align-items:center}
.btn{display:inline-flex;align-items:center;gap:.4rem;border-radius:var(--r-sm);font-weight:700;cursor:pointer;transition:all .22s;font-family:var(--font-body);border:none;letter-spacing:.01em}
.btn-ghost{background:transparent;border:1.5px solid var(--border2);color:var(--muted);padding:.55rem 1.25rem;font-size:.85rem}
.btn-ghost:hover{border-color:var(--accent);color:var(--accent)}
.btn-primary{background:var(--accent);color:#040810;padding:.6rem 1.35rem;font-size:.85rem;font-family:var(--font-display);font-weight:800;box-shadow:0 4px 20px rgba(0,255,200,.28)}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 28px rgba(0,255,200,.38)}
.btn-xl{padding:1rem 2.5rem;font-size:1rem;border-radius:12px;box-shadow:0 6px 32px rgba(0,255,200,.35)}
.btn-xl:hover{transform:translateY(-2px);box-shadow:0 10px 44px rgba(0,255,200,.45)}
.btn-outline-xl{background:transparent;border:1.5px solid rgba(255,255,255,.18);color:var(--text);padding:1rem 2.25rem;font-size:1rem;border-radius:12px;font-weight:600}
.btn-outline-xl:hover{border-color:var(--accent);color:var(--accent);background:rgba(0,255,200,.04)}

/* ── HERO ───────────────────────────────── */
.hero{
  min-height:100vh;display:flex;align-items:center;
  padding:7rem 4vw 4rem;position:relative;overflow:hidden;
}
/* Mesh gradient background */
.hero-bg{
  position:absolute;inset:0;z-index:0;
  background:
    radial-gradient(ellipse 80% 60% at 60% 20%,rgba(0,255,200,.07) 0%,transparent 55%),
    radial-gradient(ellipse 50% 40% at 10% 80%,rgba(37,99,235,.06) 0%,transparent 55%),
    radial-gradient(ellipse 40% 40% at 90% 70%,rgba(0,200,160,.04) 0%,transparent 55%);
}
/* Animated grid */
.hero-grid{
  position:absolute;inset:0;z-index:0;
  background-image:linear-gradient(rgba(0,255,200,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(0,255,200,.04) 1px,transparent 1px);
  background-size:80px 80px;
  mask-image:radial-gradient(ellipse 100% 100% at 50% 0%,black 30%,transparent 80%);
}
/* Floating orbs */
.orb{position:absolute;border-radius:50%;pointer-events:none;filter:blur(1px)}
.orb-1{width:600px;height:600px;top:-200px;right:-100px;background:radial-gradient(circle,rgba(0,255,200,.08),transparent 65%);animation:float1 9s ease-in-out infinite}
.orb-2{width:500px;height:500px;bottom:-150px;left:-100px;background:radial-gradient(circle,rgba(37,99,235,.07),transparent 65%);animation:float2 11s ease-in-out infinite}
.orb-3{width:300px;height:300px;top:40%;left:40%;background:radial-gradient(circle,rgba(0,255,200,.05),transparent 65%);animation:float3 7s ease-in-out infinite}
@keyframes float1{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(-30px,20px) scale(1.05)}}
@keyframes float2{0%,100%{transform:translate(0,0)}50%{transform:translate(20px,-25px)}}
@keyframes float3{0%,100%{transform:translate(0,0)}50%{transform:translate(-15px,15px)}}

.hero-inner{
  position:relative;z-index:1;
  display:grid;grid-template-columns:1fr 1fr;
  gap:4rem;align-items:center;max-width:1300px;margin:0 auto;width:100%;
}
.hero-left{display:flex;flex-direction:column;gap:1.75rem}
.hero-badge{
  display:inline-flex;align-items:center;gap:.6rem;width:fit-content;
  background:rgba(0,255,200,.07);border:1px solid rgba(0,255,200,.2);
  color:var(--accent);padding:.45rem 1.1rem;border-radius:100px;
  font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
}
.hero-badge::before{content:'';width:7px;height:7px;border-radius:50%;background:var(--accent);box-shadow:0 0 10px var(--accent);flex-shrink:0;animation:blink 2s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}
.hero-h1{
  font-family:var(--font-display);
  font-size:clamp(3rem,5.5vw,5rem);
  font-weight:800;line-height:1.04;
  letter-spacing:-.045em;
}
.hero-h1 .line2{color:var(--accent)}
.hero-h1 .line3{
  background:linear-gradient(135deg,var(--text) 0%,var(--muted) 100%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.hero-p{font-size:1.08rem;color:var(--muted);line-height:1.75;max-width:480px;font-weight:300}
.hero-cta{display:flex;gap:1rem;flex-wrap:wrap;align-items:center}
.hero-trust{display:flex;align-items:center;gap:.75rem;padding-top:.5rem}
.trust-avatars{display:flex}
.trust-avatars span{
  width:34px;height:34px;border-radius:50%;border:2px solid var(--bg);
  display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;
  margin-left:-10px;flex-shrink:0;
}
.trust-avatars span:first-child{margin-left:0}
.trust-text{font-size:.8rem;color:var(--muted)}
.trust-text strong{color:var(--text)}

/* Hero right - visual */
.hero-right{position:relative}
.hero-visual{
  position:relative;border-radius:24px;overflow:hidden;
  background:linear-gradient(135deg,var(--card),var(--panel));
  border:1px solid var(--border);
  box-shadow:0 32px 80px rgba(0,0,0,.6),0 0 0 1px rgba(0,255,200,.08);
}
.hero-img{
  width:100%;height:400px;object-fit:cover;
  display:block;opacity:.85;
  transition:opacity .3s;
}
.hero-img:hover{opacity:1}
/* Overlay on image */
.hero-visual-overlay{
  position:absolute;inset:0;
  background:linear-gradient(to top,rgba(4,8,16,.8) 0%,rgba(4,8,16,.2) 50%,transparent 100%);
}
.hero-visual-tag{
  position:absolute;bottom:1.5rem;left:1.5rem;right:1.5rem;
  background:rgba(4,8,16,.8);backdrop-filter:blur(12px);
  border:1px solid var(--border);border-radius:14px;padding:1rem 1.25rem;
  display:flex;align-items:center;gap:1rem;
}
.hvt-icon{width:44px;height:44px;border-radius:12px;background:rgba(0,255,200,.12);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0}
.hvt-text{font-size:.82rem;color:var(--muted);line-height:1.5}
.hvt-text strong{color:var(--text);display:block;font-size:.9rem;margin-bottom:.1rem}
/* Floating cards on visual */
.float-card{
  position:absolute;background:rgba(4,8,16,.85);backdrop-filter:blur(16px);
  border:1px solid var(--border);border-radius:14px;padding:.85rem 1.1rem;
  display:flex;align-items:center;gap:.75rem;box-shadow:0 8px 32px rgba(0,0,0,.5);
}
.fc-top{top:-1.5rem;right:1.5rem;animation:fcard1 5s ease-in-out infinite}
.fc-left{bottom:6rem;left:-2rem;animation:fcard2 6s ease-in-out infinite}
@keyframes fcard1{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
@keyframes fcard2{0%,100%{transform:translateY(0)}50%{transform:translateY(6px)}}
.fc-icon{font-size:1.5rem}
.fc-label{font-size:.72rem;color:var(--muted);margin-bottom:.15rem}
.fc-value{font-size:.92rem;font-weight:700;color:var(--text);font-family:var(--font-display)}
.fc-value.green{color:var(--accent)}

/* ── STATS BAR ──────────────────────────── */
.stats-bar{
  display:flex;justify-content:center;
  border-top:1px solid var(--border);border-bottom:1px solid var(--border);
  background:linear-gradient(90deg,transparent,rgba(0,255,200,.02),transparent);
}
.stat{
  flex:1;max-width:280px;text-align:center;padding:2.75rem 1rem;
  border-right:1px solid var(--border);position:relative;overflow:hidden;
}
.stat:last-child{border-right:none}
.stat::before{content:'';position:absolute;top:0;left:50%;transform:translateX(-50%);width:60%;height:1px;background:linear-gradient(90deg,transparent,var(--accent),transparent);opacity:.5}
.stat-n{font-family:var(--font-display);font-size:2.8rem;font-weight:800;color:var(--accent);line-height:1;letter-spacing:-.04em}
.stat-l{font-size:.82rem;color:var(--muted);margin-top:.35rem;font-weight:400}

/* ── SECTION COMMONS ────────────────────── */
.section{padding:8rem 4vw}
.section-inner{max-width:1300px;margin:0 auto}
.eyebrow{font-size:.72rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--accent);margin-bottom:.85rem}
.section-h{font-family:var(--font-display);font-size:clamp(1.9rem,3.5vw,2.8rem);font-weight:800;letter-spacing:-.04em;line-height:1.1;margin-bottom:1rem}
.section-sub{color:var(--muted);font-size:1rem;line-height:1.75;max-width:540px;font-weight:300}

/* ── HOW IT WORKS ───────────────────────── */
.hiw{background:var(--bg2)}
.hiw-layout{display:grid;grid-template-columns:1fr 1fr;gap:6rem;align-items:center}
.hiw-steps{display:flex;flex-direction:column;gap:0}
.step-item{
  display:flex;gap:1.5rem;align-items:flex-start;padding:1.75rem 0;
  border-bottom:1px solid var(--border);cursor:default;
  transition:all .25s;
}
.step-item:last-child{border-bottom:none}
.step-item:hover .step-num-wrap{background:rgba(0,255,200,.15);border-color:var(--accent)}
.step-item:hover .step-title{color:var(--accent)}
.step-num-wrap{
  width:48px;height:48px;border-radius:14px;flex-shrink:0;
  background:rgba(0,255,200,.07);border:1px solid rgba(0,255,200,.2);
  display:flex;align-items:center;justify-content:center;
  font-family:var(--font-display);font-size:1.1rem;font-weight:800;color:var(--accent);
  transition:all .25s;margin-top:.1rem;
}
.step-content{}
.step-title{font-family:var(--font-display);font-size:1.05rem;font-weight:700;margin-bottom:.4rem;transition:color .25s}
.step-desc{font-size:.9rem;color:var(--muted);line-height:1.7;font-weight:300}
/* Image side */
.hiw-img-wrap{position:relative;border-radius:24px;overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,.5)}
.hiw-img{width:100%;height:500px;object-fit:cover;display:block}
.hiw-img-badge{
  position:absolute;top:1.5rem;left:1.5rem;
  background:rgba(4,8,16,.85);backdrop-filter:blur(12px);
  border:1px solid var(--border);border-radius:12px;padding:.75rem 1rem;
  font-size:.8rem;color:var(--muted);display:flex;align-items:center;gap:.6rem;
}
.hiw-img-badge strong{color:var(--accent);font-size:1rem;font-family:var(--font-display)}

/* ── FEATURES GRID ──────────────────────── */
.feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-top:3.5rem}
.feat-card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--r);
  padding:2rem;position:relative;overflow:hidden;transition:all .3s;cursor:default;
  display:flex;flex-direction:column;gap:.75rem;
}
.feat-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:1px;
  background:linear-gradient(90deg,transparent,var(--accent),transparent);
  opacity:0;transition:opacity .3s;
}
.feat-card:hover{border-color:rgba(0,255,200,.3);transform:translateY(-4px);box-shadow:0 16px 48px rgba(0,0,0,.4),var(--shadow-accent)}
.feat-card:hover::before{opacity:.6}
.feat-icon-wrap{
  width:52px;height:52px;border-radius:14px;
  background:rgba(0,255,200,.08);border:1px solid rgba(0,255,200,.15);
  display:flex;align-items:center;justify-content:center;font-size:1.5rem;
}
.feat-title{font-family:var(--font-display);font-size:1.05rem;font-weight:700}
.feat-desc{font-size:.88rem;color:var(--muted);line-height:1.65;font-weight:300}

/* ── MARKETPLACE IMAGE SECTION ──────────── */
.showcase{background:var(--bg2);overflow:hidden}
.showcase-inner{display:grid;grid-template-columns:1fr 1fr;gap:0;min-height:520px}
.showcase-content{
  padding:5rem 4rem;display:flex;flex-direction:column;justify-content:center;gap:1.75rem;
  background:linear-gradient(135deg,var(--card),var(--panel));
  border-right:1px solid var(--border);
}
.showcase-img-wrap{position:relative;overflow:hidden}
.showcase-img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .6s ease}
.showcase-img-wrap:hover .showcase-img{transform:scale(1.04)}
.showcase-img-overlay{
  position:absolute;inset:0;
  background:linear-gradient(to right,rgba(7,13,26,.6) 0%,transparent 60%);
}
.showcase-chips{display:flex;gap:.6rem;flex-wrap:wrap}
.chip{
  padding:.35rem .85rem;border-radius:100px;
  background:rgba(0,255,200,.08);border:1px solid rgba(0,255,200,.18);
  color:var(--accent);font-size:.76rem;font-weight:700;letter-spacing:.04em;
}

/* ── TESTIMONIALS ───────────────────────── */
.testimonials{background:var(--bg)}
.testi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-top:3.5rem}
.testi-card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--r);
  padding:1.75rem;display:flex;flex-direction:column;gap:1.25rem;
  transition:all .3s;position:relative;
}
.testi-card:hover{border-color:rgba(0,255,200,.2);transform:translateY(-3px)}
.testi-quote{
  font-size:2.5rem;line-height:1;
  font-family:Georgia,serif;color:var(--dim);
  position:absolute;top:1.25rem;right:1.5rem;
}
.testi-text{font-size:.92rem;color:var(--muted);line-height:1.75;font-weight:300}
.testi-author{display:flex;align-items:center;gap:.85rem;margin-top:auto}
.testi-avatar{
  width:42px;height:42px;border-radius:50%;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-weight:800;font-size:.9rem;color:#040810;
}
.testi-name{font-weight:700;font-size:.88rem}
.testi-role{font-size:.76rem;color:var(--muted)}
.testi-stars{color:#ffbe0b;font-size:.85rem;letter-spacing:1px;margin-bottom:.35rem}

/* ── CTA FINAL ──────────────────────────── */
.cta-section{
  padding:9rem 4vw;text-align:center;position:relative;overflow:hidden;
  background:var(--bg2);
}
.cta-section::before{
  content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse 70% 70% at 50% 50%,rgba(0,255,200,.06) 0%,transparent 70%);
}
.cta-inner{position:relative;z-index:1;max-width:700px;margin:0 auto}
.cta-h{font-family:var(--font-display);font-size:clamp(2.2rem,5vw,3.5rem);font-weight:800;letter-spacing:-.045em;line-height:1.08;margin-bottom:1rem}
.cta-h em{font-style:normal;color:var(--accent)}
.cta-sub{color:var(--muted);font-size:1.05rem;line-height:1.7;margin-bottom:2.75rem;font-weight:300}
.cta-btns{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap}
.cta-note{margin-top:1.25rem;font-size:.78rem;color:var(--dim);display:flex;align-items:center;justify-content:center;gap:.5rem}

/* ── FOOTER ─────────────────────────────── */
footer{background:var(--bg);border-top:1px solid var(--border);padding:3rem 4vw 2rem}
.footer-inner{max-width:1300px;margin:0 auto}
.footer-top{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:3rem;margin-bottom:3rem}
.footer-brand-logo{font-family:var(--font-display);font-size:1.4rem;font-weight:800;margin-bottom:.75rem}
.footer-brand-logo span{color:var(--accent)}
.footer-brand-desc{font-size:.85rem;color:var(--muted);line-height:1.7;max-width:260px;font-weight:300}
.footer-col-title{font-size:.75rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--dim);margin-bottom:1rem}
.footer-links{display:flex;flex-direction:column;gap:.6rem}
.footer-links a{font-size:.85rem;color:var(--muted);transition:color .2s;font-weight:300}
.footer-links a:hover{color:var(--accent)}
.footer-bottom{border-top:1px solid var(--border);padding-top:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem}
.footer-bottom p{font-size:.8rem;color:var(--dim)}
.footer-bottom-links{display:flex;gap:1.5rem}
.footer-bottom-links a{font-size:.8rem;color:var(--dim);transition:color .2s}
.footer-bottom-links a:hover{color:var(--muted)}

/* ── RESPONSIVE ─────────────────────────── */
@media(max-width:1024px){
  .hero-inner{grid-template-columns:1fr}
  .hero-right{display:none}
  .hiw-layout{grid-template-columns:1fr}
  .hiw-img-wrap{display:none}
  .feat-grid{grid-template-columns:repeat(2,1fr)}
  .showcase-inner{grid-template-columns:1fr}
  .showcase-img-wrap{height:300px}
  .testi-grid{grid-template-columns:repeat(2,1fr)}
  .footer-top{grid-template-columns:1fr 1fr}
}
@media(max-width:640px){
  .nav-links{display:none}
  .feat-grid{grid-template-columns:1fr}
  .testi-grid{grid-template-columns:1fr}
  .stats-bar{flex-direction:column}
  .stat{border-right:none;border-bottom:1px solid var(--border)}
  .stat:last-child{border-bottom:none}
  .footer-top{grid-template-columns:1fr}
}

/* Scroll reveal */
.reveal{opacity:0;transform:translateY(30px);transition:opacity .7s ease,transform .7s ease}
.reveal.visible{opacity:1;transform:translateY(0)}
</style>
</head>
<body>

<!-- ══ NAVBAR ═══════════════════════════════════ -->
<nav class="nav" id="navbar">
  <a href="index.php" class="nav-logo">Reverse<span>Market</span></a>
  <div class="nav-links">
    <a href="#como-funciona">Cómo funciona</a>
    <a href="#servicios">Servicios</a>
    <a href="#testimonios">Comunidad</a>
    <a href="views/contacto.php">Contacto</a>
  </div>
  <div class="nav-cta">
    <a href="views/login.php" class="btn btn-ghost">Iniciar sesión</a>
    <a href="views/register.php" class="btn btn-primary">Registrarse →</a>
  </div>
</nav>

<!-- ══ HERO ══════════════════════════════════════ -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>

  <div class="hero-inner">
    <div class="hero-left">
      <div class="hero-badge">✦ Nuevo modelo de comercio digital</div>

      <h1 class="hero-h1">
        Publica<br>
        <span class="line2">lo que buscas.</span><br>
        <span class="line3">Recibe ofertas.</span>
      </h1>

      <p class="hero-p">
        El primer mercado invertido donde tú defines la demanda y los mejores vendedores compiten para darte la propuesta perfecta.
      </p>

      <div class="hero-cta">
        <a href="views/register.php?rol=comprador" class="btn btn-primary btn-xl">Soy Comprador →</a>
        <a href="views/register.php?rol=vendedor" class="btn btn-outline-xl">Soy Vendedor</a>
      </div>

      <div class="hero-trust">
        <div class="trust-avatars">
          <span style="background:linear-gradient(135deg,#00ffc8,#00a88a)">DS</span>
          <span style="background:linear-gradient(135deg,#2563eb,#1d4ed8)">MR</span>
          <span style="background:linear-gradient(135deg,#7c3aed,#6d28d9)">CP</span>
          <span style="background:linear-gradient(135deg,#dc2626,#b91c1c)">JL</span>
        </div>
        <div class="trust-text">
          <strong>+500 usuarios</strong> ya compran y venden diferente
        </div>
      </div>
    </div>

    <div class="hero-right">
      <div class="hero-visual">
        <img
          src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=700&q=80"
          alt="Marketplace digital"
          class="hero-img"
        >
        <div class="hero-visual-overlay"></div>
        <div class="hero-visual-tag">
          <div class="hvt-icon">🏆</div>
          <div class="hvt-text">
            <strong>Vendedores compiten por ti</strong>
            Recibes las mejores ofertas del mercado
          </div>
        </div>
      </div>

      <!-- Floating cards -->
      <div class="float-card fc-top">
        <div class="fc-icon">💰</div>
        <div>
          <div class="fc-label">Ahorro promedio</div>
          <div class="fc-value green">−23% vs precio lista</div>
        </div>
      </div>
      <div class="float-card fc-left">
        <div class="fc-icon">⚡</div>
        <div>
          <div class="fc-label">Tiempo de respuesta</div>
          <div class="fc-value">< 2 horas</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ STATS BAR ══════════════════════════════════ -->
<div class="stats-bar">
  <div class="stat">
    <div class="stat-n">100%</div>
    <div class="stat-l">Orientado al comprador</div>
  </div>
  <div class="stat">
    <div class="stat-n">$0</div>
    <div class="stat-l">Costo de registro</div>
  </div>
  <div class="stat">
    <div class="stat-n">3</div>
    <div class="stat-l">Roles de usuario</div>
  </div>
  <div class="stat">
    <div class="stat-n">24/7</div>
    <div class="stat-l">Disponibilidad</div>
  </div>
</div>

<!-- ══ CÓMO FUNCIONA ══════════════════════════════ -->
<section class="section hiw" id="como-funciona">
  <div class="section-inner">
    <div class="hiw-layout">
      <div>
        <div class="eyebrow">¿Cómo funciona?</div>
        <h2 class="section-h">El mercado que<br>trabaja para ti</h2>
        <p class="section-sub">Invierte el proceso tradicional. No busques — publica, espera ofertas y elige siempre la mejor.</p>

        <div class="hiw-steps" style="margin-top:2.5rem">
          <div class="step-item">
            <div class="step-num-wrap">1</div>
            <div class="step-content">
              <div class="step-title">Publica tu necesidad</div>
              <div class="step-desc">Describe exactamente lo que buscas. Agrega tu presupuesto máximo y condiciones. Sin costo.</div>
            </div>
          </div>
          <div class="step-item">
            <div class="step-num-wrap">2</div>
            <div class="step-content">
              <div class="step-title">Vendedores hacen ofertas</div>
              <div class="step-desc">Múltiples vendedores compiten enviándote propuestas personalizadas con sus mejores precios.</div>
            </div>
          </div>
          <div class="step-item">
            <div class="step-num-wrap">3</div>
            <div class="step-content">
              <div class="step-title">Negocia y elige</div>
              <div class="step-desc">Compara, chatea directamente con vendedores, haz contraofertas y elige la propuesta perfecta.</div>
            </div>
          </div>
          <div class="step-item">
            <div class="step-num-wrap">4</div>
            <div class="step-content">
              <div class="step-title">Paga seguro con MercadoPago</div>
              <div class="step-desc">Tarjeta, PSE, Nequi, Efecty. Pago protegido y transacción registrada en la plataforma.</div>
            </div>
          </div>
        </div>

        <a href="views/register.php" class="btn btn-primary btn-xl" style="margin-top:2.5rem;display:inline-flex">
          Empezar gratis →
        </a>
      </div>

      <div class="hiw-img-wrap">
        <img
          src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=600&q=80"
          alt="Personas negociando"
          class="hiw-img"
        >
        <div class="hiw-img-badge">
          <strong style="color:var(--accent);font-size:1.4rem">4.9</strong>
          <span>★★★★★<br><span style="font-size:.72rem">Calificación promedio</span></span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ FEATURES ═══════════════════════════════════ -->
<section class="section" id="servicios" style="background:var(--bg)">
  <div class="section-inner">
    <div style="text-align:center;max-width:600px;margin:0 auto">
      <div class="eyebrow">¿Por qué elegirnos?</div>
      <h2 class="section-h">Todo lo que necesitas<br>en una plataforma</h2>
    </div>
    <div class="feat-grid">
      <div class="feat-card">
        <div class="feat-icon-wrap">🔄</div>
        <div class="feat-title">Mercado invertido</div>
        <div class="feat-desc">El comprador define la demanda. Los vendedores compiten para ganarte. Siempre obtienes la mejor propuesta.</div>
      </div>
      <div class="feat-card">
        <div class="feat-icon-wrap">💬</div>
        <div class="feat-title">Negociación en tiempo real</div>
        <div class="feat-desc">Chat privado integrado. Haz contraofertas, pide más información y cierra el trato directamente.</div>
      </div>
      <div class="feat-card">
        <div class="feat-icon-wrap">💳</div>
        <div class="feat-title">Pagos con MercadoPago</div>
        <div class="feat-desc">Tarjeta débito/crédito, PSE, Nequi, Daviplata, Efecty. Pagos reales, seguros y registrados.</div>
      </div>
      <div class="feat-card">
        <div class="feat-icon-wrap">⭐</div>
        <div class="feat-title">Reputación verificada</div>
        <div class="feat-desc">Calificaciones reales de compradores y vendedores. Historial completo de transacciones.</div>
      </div>
      <div class="feat-card">
        <div class="feat-icon-wrap">🛡️</div>
        <div class="feat-title">Moderación activa</div>
        <div class="feat-desc">Panel de administración, reportes y moderación continua para mantener la plataforma segura.</div>
      </div>
      <div class="feat-card">
        <div class="feat-icon-wrap">📊</div>
        <div class="feat-title">Panel analítico</div>
        <div class="feat-desc">Estadísticas de ventas, necesidades publicadas, ofertas recibidas y calificaciones en tiempo real.</div>
      </div>
    </div>
  </div>
</section>

<!-- ══ SHOWCASE (imagen real) ════════════════════ -->
<section class="showcase">
  <div class="showcase-inner">
    <div class="showcase-content">
      <div class="eyebrow">Para vendedores</div>
      <h2 class="section-h" style="max-width:400px">Clientes que ya saben lo que quieren</h2>
      <p style="color:var(--muted);font-size:.95rem;line-height:1.75;font-weight:300;max-width:400px">
        Olvídate de la prospección fría. En Reverse Market los compradores llegan a ti con necesidades claras y presupuesto definido. Solo tienes que hacer tu mejor oferta.
      </p>
      <div class="showcase-chips">
        <span class="chip">Sin comisiones ocultas</span>
        <span class="chip">Leads calificados</span>
        <span class="chip">Pagos garantizados</span>
      </div>
      <a href="views/register.php?rol=vendedor" class="btn btn-primary btn-xl" style="display:inline-flex;margin-top:.5rem">
        Registrarme como vendedor →
      </a>
    </div>
    <div class="showcase-img-wrap">
      <img
        src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=700&q=80"
        alt="Vendedor profesional"
        class="showcase-img"
      >
      <div class="showcase-img-overlay"></div>
    </div>
  </div>
</section>

<!-- ══ SEGUNDA SHOWCASE ════════════════════════════ -->
<section class="showcase" style="background:var(--bg)">
  <div class="showcase-inner" style="direction:rtl">
    <div class="showcase-content" style="direction:ltr;border-right:none;border-left:1px solid var(--border)">
      <div class="eyebrow">Para compradores</div>
      <h2 class="section-h" style="max-width:400px">Tú pones las condiciones</h2>
      <p style="color:var(--muted);font-size:.95rem;line-height:1.75;font-weight:300;max-width:400px">
        Publica lo que necesitas, recibe propuestas de varios vendedores y elige la que más te convenga. El poder de negociación es tuyo.
      </p>
      <div class="showcase-chips">
        <span class="chip">Registro gratis</span>
        <span class="chip">Múltiples ofertas</span>
        <span class="chip">Paga solo lo acordado</span>
      </div>
      <a href="views/register.php?rol=comprador" class="btn btn-primary btn-xl" style="display:inline-flex;margin-top:.5rem">
        Empezar a comprar →
      </a>
    </div>
    <div class="showcase-img-wrap" style="direction:ltr">
      <img
        src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=700&q=80"
        alt="Comprador tomando decisiones"
        class="showcase-img"
      >
      <div class="showcase-img-overlay" style="background:linear-gradient(to left,rgba(7,13,26,.6) 0%,transparent 60%)"></div>
    </div>
  </div>
</section>

<!-- ══ TESTIMONIALS ════════════════════════════════ -->
<section class="section testimonials" id="testimonios">
  <div class="section-inner">
    <div style="text-align:center;max-width:550px;margin:0 auto">
      <div class="eyebrow">Lo que dicen</div>
      <h2 class="section-h">Nuestra comunidad habla</h2>
    </div>
    <div class="testi-grid">
      <div class="testi-card">
        <div class="testi-quote">"</div>
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">Publiqué que necesitaba un diseñador con presupuesto de $300k COP. En menos de 3 horas tenía 8 propuestas. Elegí la mejor y ahorré casi el 30%.</p>
        <div class="testi-author">
          <div class="testi-avatar" style="background:linear-gradient(135deg,#00ffc8,#00a88a)">MA</div>
          <div>
            <div class="testi-name">María Alejandra</div>
            <div class="testi-role">Compradora · Bogotá</div>
          </div>
        </div>
      </div>
      <div class="testi-card">
        <div class="testi-quote">"</div>
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">Como vendedor es increíble. Mis clientes llegan con presupuesto claro. Ya no pierdo tiempo en clientes que no pueden pagar. Dupliqué mis cierres.</p>
        <div class="testi-author">
          <div class="testi-avatar" style="background:linear-gradient(135deg,#2563eb,#1d4ed8)">JR</div>
          <div>
            <div class="testi-name">Juan Ricardo</div>
            <div class="testi-role">Vendedor · Medellín</div>
          </div>
        </div>
      </div>
      <div class="testi-card">
        <div class="testi-quote">"</div>
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">El sistema de calificaciones da mucha confianza. Saber que el vendedor tiene 4.9 estrellas verificadas hace el proceso muy tranquilo. Excelente plataforma.</p>
        <div class="testi-author">
          <div class="testi-avatar" style="background:linear-gradient(135deg,#7c3aed,#6d28d9)">CP</div>
          <div>
            <div class="testi-name">Carlos Pérez</div>
            <div class="testi-role">Comprador · Cali</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ CTA FINAL ══════════════════════════════════ -->
<section class="cta-section">
  <div class="cta-inner">
    <h2 class="cta-h">¿Listo para comprar<br><em>de forma inteligente?</em></h2>
    <p class="cta-sub">Únete gratis hoy. Sin tarjeta de crédito. Sin compromisos.<br>Empieza a recibir ofertas competitivas en minutos.</p>
    <div class="cta-btns">
      <a href="views/register.php?rol=comprador" class="btn btn-primary btn-xl">Crear cuenta de Comprador</a>
      <a href="views/register.php?rol=vendedor" class="btn btn-outline-xl">Registrarme como Vendedor</a>
    </div>
    <div class="cta-note">
      <span>🔒</span> Pagos procesados por MercadoPago · Plataforma 100% segura
    </div>
  </div>
</section>

<!-- ══ FOOTER ══════════════════════════════════════ -->
<footer>
  <div class="footer-inner">
    <div class="footer-top">
      <div>
        <div class="footer-brand-logo">Reverse<span>Market</span></div>
        <p class="footer-brand-desc">El primer mercado invertido de Colombia. Donde los compradores definen la demanda y los mejores vendedores compiten.</p>
      </div>
      <div>
        <div class="footer-col-title">Plataforma</div>
        <div class="footer-links">
          <a href="#como-funciona">Cómo funciona</a>
          <a href="#servicios">Servicios</a>
          <a href="views/register.php">Registrarse</a>
          <a href="views/login.php">Iniciar sesión</a>
        </div>
      </div>
      <div>
        <div class="footer-col-title">Para ti</div>
        <div class="footer-links">
          <a href="views/register.php?rol=comprador">Soy Comprador</a>
          <a href="views/register.php?rol=vendedor">Soy Vendedor</a>
          <a href="#testimonios">Testimonios</a>
          <a href="views/contacto.php">Contacto</a>
        </div>
      </div>
      <div>
        <div class="footer-col-title">Pagos</div>
        <div class="footer-links">
          <a href="#">MercadoPago</a>
          <a href="#">Nequi</a>
          <a href="#">PSE / Bancos</a>
          <a href="#">Efecty</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 Reverse Market · Todos los derechos reservados</p>
      <div class="footer-bottom-links">
        <a href="#">Privacidad</a>
        <a href="#">Términos</a>
        <a href="#">Soporte</a>
      </div>
    </div>
  </div>
</footer>

<script>
// Navbar scroll effect
const nav = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  if (window.scrollY > 50) {
    nav.style.background = 'rgba(4,8,16,.95)';
    nav.style.boxShadow = '0 8px 32px rgba(0,0,0,.4)';
  } else {
    nav.style.background = 'rgba(4,8,16,.75)';
    nav.style.boxShadow = 'none';
  }
});

// Scroll reveal
const observer = new IntersectionObserver((entries) => {
  entries.forEach((e, i) => {
    if (e.isIntersecting) {
      setTimeout(() => e.target.classList.add('visible'), i * 80);
      observer.unobserve(e.target);
    }
  });
}, { threshold: 0.12 });

document.querySelectorAll('.feat-card,.testi-card,.step-item,.stat').forEach(el => {
  el.classList.add('reveal');
  observer.observe(el);
});

// Counter animation on stats
const counters = document.querySelectorAll('.stat-n');
const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      const el = e.target;
      const text = el.textContent;
      if (!isNaN(parseInt(text))) {
        const target = parseInt(text);
        let current = 0;
        const step = target / 40;
        const timer = setInterval(() => {
          current += step;
          if (current >= target) { el.textContent = text; clearInterval(timer); }
          else el.textContent = Math.floor(current) + (text.includes('%') ? '%' : text.includes('+') ? '+' : '');
        }, 30);
      }
      counterObserver.unobserve(el);
    }
  });
}, { threshold: 0.5 });
counters.forEach(c => counterObserver.observe(c));
</script>

</body>
</html>
