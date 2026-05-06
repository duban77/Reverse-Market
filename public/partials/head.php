<?php
if (!defined('BASE_URL')) {
    $docRoot   = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $publicDir = str_replace('\\', '/', dirname(dirname(__FILE__)));
    $base = str_replace($docRoot, '', $publicDir);
    define('BASE_URL', rtrim($base, '/'));
}
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<style>
/* ════════════════════════════════════════════════════════════════
   REVERSE MARKET — Design System v3
   Dark luxury · Navy + Teal · Glass morphism · 2025
   ════════════════════════════════════════════════════════════════ */

/* ── Reset & Base ────────────────────────────────────── */
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{font-size:16px;scroll-behavior:smooth}
:root{
  /* Colors */
  --bg-dark:   #040810;
  --bg-panel:  #070d1a;
  --bg-card:   #0c1422;
  --bg-input:  #0a1120;
  --border:    rgba(0,255,200,.1);
  --border-subtle: rgba(255,255,255,.06);
  --accent:    #00ffc8;
  --accent-dim:#00a88a;
  --blue:      #2563eb;
  --success:   #06d6a0;
  --warning:   #ffbe0b;
  --danger:    #ff4d6d;
  --text-primary:  #f0f4ff;
  --text-muted:    #6b7fa3;
  --text-dim:      #2d3d5a;
  /* Fonts */
  --font-display: 'Syne', sans-serif;
  --font-body:    'DM Sans', sans-serif;
  /* Radii */
  --radius:    16px;
  --radius-sm: 10px;
  --radius-xs: 7px;
  /* Shadows */
  --shadow-sm:     0 2px 8px rgba(0,0,0,.4);
  --shadow:        0 8px 32px rgba(0,0,0,.5);
  --shadow-lg:     0 16px 56px rgba(0,0,0,.6);
  --shadow-accent: 0 0 40px rgba(0,255,200,.12);
  /* Transition */
  --transition: all .22s ease;
}
body{
  font-family:var(--font-body);
  background:var(--bg-dark);
  color:var(--text-primary);
  -webkit-font-smoothing:antialiased;
  min-height:100vh;
}
a{text-decoration:none;color:inherit}
img{display:block;max-width:100%}
button{font-family:var(--font-body)}

/* ── DASHBOARD LAYOUT ────────────────────────────────── */
.dashboard-layout{
  display:grid;
  grid-template-columns:260px 1fr;
  min-height:100vh;
}

/* ── SIDEBAR ─────────────────────────────────────────── */
.sidebar{
  background:var(--bg-panel);
  border-right:1px solid var(--border);
  display:flex;flex-direction:column;
  height:100vh;position:sticky;top:0;
  overflow-y:auto;overflow-x:hidden;
  scrollbar-width:thin;scrollbar-color:var(--border) transparent;
}
.sidebar::-webkit-scrollbar{width:3px}
.sidebar::-webkit-scrollbar-thumb{background:var(--border);border-radius:99px}

/* Sidebar Brand */
.sidebar-brand{
  padding:1.1rem 1rem .9rem;
  font-family:var(--font-display);font-size:1rem;font-weight:800;
  color:var(--text-primary);letter-spacing:-.02em;
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:.55rem;
  flex-shrink:0;white-space:nowrap;overflow:hidden;
}
.sidebar-brand span{color:var(--accent)}
.sidebar-brand::before{
  content:'RM';width:28px;height:28px;border-radius:7px;
  background:linear-gradient(135deg,var(--accent),var(--accent-dim));
  color:#040810;font-size:.68rem;font-weight:800;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
  min-width:28px;
}

/* Section labels */
.sidebar-section{
  padding:.9rem 1.25rem .3rem;
  font-size:.65rem;font-weight:700;letter-spacing:.12em;
  text-transform:uppercase;color:var(--text-dim);
}

/* Nav links */
.sidebar-nav{list-style:none;padding:.25rem .65rem}
.sidebar-nav a{
  display:flex;align-items:center;gap:.7rem;
  padding:.62rem .75rem;border-radius:var(--radius-xs);
  font-size:.875rem;font-weight:500;color:var(--text-muted);
  transition:var(--transition);position:relative;
  white-space:nowrap;
}
.sidebar-nav a:hover{
  background:rgba(0,255,200,.07);
  color:var(--text-primary);
}
.sidebar-nav a.active{
  background:rgba(0,255,200,.1);
  color:var(--accent);font-weight:600;
}
.sidebar-nav a.active::before{
  content:'';position:absolute;left:-0.65rem;top:50%;transform:translateY(-50%);
  width:3px;height:60%;border-radius:2px;background:var(--accent);
}
.sidebar-nav .icon{
  font-size:1rem;width:22px;display:flex;
  align-items:center;justify-content:center;flex-shrink:0;
}
.sidebar-nav .badge{
  margin-left:auto;background:var(--danger);color:#fff;
  font-size:.62rem;font-weight:800;padding:.15rem .4rem;
  border-radius:100px;min-width:18px;text-align:center;
}

/* Sidebar bottom (user) */
.sidebar-bottom{margin-top:auto;padding:.75rem .65rem 1rem;border-top:1px solid var(--border)}
.sidebar-user{
  display:flex;align-items:center;gap:.8rem;
  padding:.75rem;border-radius:var(--radius-xs);
  background:rgba(0,255,200,.04);border:1px solid var(--border);
  margin-bottom:.35rem;
}
.user-avatar{
  width:36px;height:36px;border-radius:10px;flex-shrink:0;
  background:linear-gradient(135deg,var(--accent),var(--accent-dim));
  color:#040810;font-family:var(--font-display);font-weight:800;
  font-size:.85rem;display:flex;align-items:center;justify-content:center;
}
.user-name{font-size:.82rem;font-weight:700;color:var(--text-primary);line-height:1.2}
.user-role{font-size:.7rem;color:var(--text-muted);text-transform:capitalize}

/* ── MAIN CONTENT ────────────────────────────────────── */
.main-content{
  padding:2rem 2.25rem;
  min-height:100vh;
  background:var(--bg-dark);
  overflow:auto;
}

/* Page header */
.page-header{
  display:flex;align-items:flex-start;justify-content:space-between;
  gap:1rem;margin-bottom:2rem;flex-wrap:wrap;
}
.page-title{
  font-family:var(--font-display);font-size:1.65rem;font-weight:800;
  letter-spacing:-.03em;color:var(--text-primary);margin-bottom:.2rem;
}
.page-subtitle{font-size:.88rem;color:var(--text-muted);font-weight:300}

/* ── STATS GRID ──────────────────────────────────────── */
.stats-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
  gap:1rem;
}
.stat-card{
  background:var(--bg-card);
  border:1px solid var(--border);
  border-radius:var(--radius);
  padding:1.4rem 1.5rem;
  position:relative;overflow:hidden;
  transition:var(--transition);cursor:default;
}
.stat-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg,transparent,var(--accent),transparent);
  opacity:0;transition:opacity .3s;
}
.stat-card:hover{border-color:rgba(0,255,200,.2);transform:translateY(-2px)}
.stat-card:hover::before{opacity:.7}
.stat-number{
  font-family:var(--font-display);font-size:2rem;font-weight:800;
  color:var(--accent);letter-spacing:-.03em;line-height:1;margin-bottom:.3rem;
}
.stat-label{font-size:.78rem;color:var(--text-muted);font-weight:400}
.stat-card .stat-icon{
  position:absolute;right:1rem;top:50%;transform:translateY(-50%);
  font-size:2.5rem;opacity:.08;
}

/* ── BUTTONS ─────────────────────────────────────────── */
.btn{
  display:inline-flex;align-items:center;gap:.45rem;
  border-radius:var(--radius-sm);font-weight:700;cursor:pointer;
  transition:var(--transition);font-family:var(--font-body);
  border:none;font-size:.875rem;letter-spacing:.01em;
  white-space:nowrap;
}
.btn:disabled{opacity:.5;cursor:not-allowed;transform:none!important}

.btn-primary{
  background:var(--accent);color:#040810;
  padding:.65rem 1.4rem;
  box-shadow:0 4px 20px rgba(0,255,200,.25);
}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 28px rgba(0,255,200,.38)}

.btn-secondary{
  background:rgba(0,255,200,.12);color:var(--accent);
  border:1px solid rgba(0,255,200,.2);padding:.65rem 1.4rem;
}
.btn-secondary:hover{background:rgba(0,255,200,.18);border-color:var(--accent)}

.btn-outline{
  background:transparent;color:var(--text-muted);
  border:1px solid var(--border-subtle);padding:.65rem 1.4rem;
}
.btn-outline:hover{border-color:var(--accent);color:var(--accent)}

.btn-danger{
  background:rgba(255,77,109,.15);color:var(--danger);
  border:1px solid rgba(255,77,109,.25);padding:.65rem 1.4rem;
}
.btn-danger:hover{background:rgba(255,77,109,.25);border-color:var(--danger)}

.btn-sm{padding:.45rem 1rem;font-size:.8rem}
.btn-lg{padding:.85rem 1.85rem;font-size:.95rem;border-radius:12px}

/* ── CARDS ───────────────────────────────────────────── */
.card{
  background:var(--bg-card);border:1px solid var(--border);
  border-radius:var(--radius);transition:var(--transition);
}
.card:hover{border-color:rgba(0,255,200,.2)}
.card-body{padding:1.5rem}
.card-header{
  padding:1.1rem 1.5rem;border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
}
.card-title{font-family:var(--font-display);font-size:.95rem;font-weight:700}

/* ── FORMS ───────────────────────────────────────────── */
.form-label{
  display:block;font-size:.75rem;font-weight:700;
  letter-spacing:.06em;text-transform:uppercase;
  color:var(--text-muted);margin-bottom:.45rem;
}
.form-control{
  width:100%;background:var(--bg-input);
  border:1.5px solid var(--border);border-radius:var(--radius-sm);
  padding:.75rem 1rem;color:var(--text-primary);
  font-family:var(--font-body);font-size:.92rem;
  transition:var(--transition);
  -webkit-appearance:none;appearance:none;
}
.form-control::placeholder{color:var(--text-dim)}
.form-control:focus{
  outline:none;border-color:var(--accent);
  box-shadow:0 0 0 3px rgba(0,255,200,.1);
  background:var(--bg-card);
}
select.form-control{color-scheme:dark}
select.form-control option{background:#0c1422;color:var(--text-primary)}
textarea.form-control{resize:vertical;min-height:90px}

/* ── TABLES ──────────────────────────────────────────── */
.table-wrap{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
table.rm-table{width:100%;border-collapse:collapse}
table.rm-table th{
  padding:.85rem 1.1rem;font-size:.7rem;font-weight:700;
  letter-spacing:.08em;text-transform:uppercase;color:var(--text-dim);
  background:var(--bg-panel);border-bottom:1px solid var(--border);
  text-align:left;
}
table.rm-table td{
  padding:.95rem 1.1rem;font-size:.875rem;
  border-bottom:1px solid rgba(0,255,200,.04);
  vertical-align:middle;color:var(--text-primary);
}
table.rm-table tr:last-child td{border-bottom:none}
table.rm-table tbody tr{transition:background .15s}
table.rm-table tbody tr:hover td{background:rgba(0,255,200,.03)}

/* ── BADGES ──────────────────────────────────────────── */
.badge{
  display:inline-flex;align-items:center;gap:.3rem;
  padding:.25rem .75rem;border-radius:100px;
  font-size:.72rem;font-weight:700;letter-spacing:.03em;
}
.badge-success{background:rgba(6,214,160,.12);color:var(--success)}
.badge-warning{background:rgba(255,190,11,.12);color:var(--warning)}
.badge-danger {background:rgba(255,77,109,.12);color:var(--danger)}
.badge-info   {background:rgba(37,99,235,.12);color:#60a5fa}
.badge-neutral{background:rgba(255,255,255,.06);color:var(--text-muted)}

/* ── PRODUCT CARDS ───────────────────────────────────── */
.product-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
  gap:1.1rem;
}
.product-card{
  background:var(--bg-card);border:1px solid var(--border);
  border-radius:var(--radius);overflow:hidden;
  transition:var(--transition);display:flex;flex-direction:column;
}
.product-card:hover{
  border-color:rgba(0,255,200,.3);transform:translateY(-4px);
  box-shadow:var(--shadow-lg),var(--shadow-accent);
}
.product-img{
  width:100%;height:148px;background:var(--bg-panel);
  display:flex;align-items:center;justify-content:center;
  font-size:2.5rem;overflow:hidden;flex-shrink:0;
}
.product-img img{width:100%;height:148px;object-fit:cover}
.product-body{padding:.9rem 1rem;flex:1;display:flex;flex-direction:column}
.product-name{font-weight:700;font-size:.9rem;margin-bottom:.2rem;line-height:1.3}
.product-vendor{font-size:.75rem;color:var(--text-muted);margin-bottom:.5rem}
.product-price{
  font-family:var(--font-display);font-size:1.05rem;font-weight:800;
  color:var(--accent);margin-bottom:.7rem;
}
.product-cat{
  display:inline-block;background:rgba(0,255,200,.08);color:var(--accent);
  font-size:.68rem;padding:.2rem .6rem;border-radius:100px;margin-bottom:.6rem;
  font-weight:700;letter-spacing:.04em;
}
.product-actions{display:flex;gap:.5rem;margin-top:auto;flex-wrap:wrap}

/* ── EMPTY STATE ─────────────────────────────────────── */
.empty-state{
  text-align:center;padding:4rem 2rem;
  display:flex;flex-direction:column;align-items:center;gap:1rem;
}
.empty-icon{font-size:3.5rem;opacity:.6}
.empty-state h3{font-family:var(--font-display);font-size:1.1rem;font-weight:700}
.empty-state p{color:var(--text-muted);font-size:.88rem;max-width:300px;line-height:1.6;font-weight:300}

/* ── TOAST NOTIFICATIONS ─────────────────────────────── */
#toast-container{
  position:fixed;top:1.25rem;right:1.25rem;z-index:9999;
  display:flex;flex-direction:column;gap:.6rem;pointer-events:none;
}
.toast{
  display:flex;align-items:center;gap:.85rem;
  background:var(--bg-card);border-radius:14px;padding:1rem 1.25rem;
  min-width:300px;max-width:380px;
  border:1px solid var(--border);
  box-shadow:var(--shadow-lg);
  animation:slideInRight .3s ease;pointer-events:all;
}
.toast.success{border-left:3px solid var(--success)}
.toast.error  {border-left:3px solid var(--danger)}
.toast.warning{border-left:3px solid var(--warning)}
.toast.info   {border-left:3px solid var(--accent)}
.toast-icon{
  width:34px;height:34px;border-radius:50%;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-size:.9rem;font-weight:800;
}
.toast.success .toast-icon{background:rgba(6,214,160,.15);color:var(--success)}
.toast.error   .toast-icon{background:rgba(255,77,109,.15);color:var(--danger)}
.toast.warning .toast-icon{background:rgba(255,190,11,.15);color:var(--warning)}
.toast.info    .toast-icon{background:rgba(0,255,200,.1);color:var(--accent)}
.toast-body{flex:1}
.toast-title{font-weight:700;font-size:.85rem;margin-bottom:.1rem}
.toast-msg{font-size:.78rem;color:var(--text-muted)}
.toast-close{background:none;border:none;color:var(--text-dim);cursor:pointer;font-size:1.1rem;padding:.2rem;border-radius:6px;flex-shrink:0}
.toast-close:hover{color:var(--text-muted)}
.toast.fade-out{opacity:0;transition:opacity .4s}

/* ── ANIMATIONS ──────────────────────────────────────── */
@keyframes slideInRight{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
@keyframes slideIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
@keyframes pop{0%{transform:scale(.3);opacity:0}70%{transform:scale(1.12)}100%{transform:scale(1);opacity:1}}

/* ── MISC UTILITIES ──────────────────────────────────── */
.divider{height:1px;background:var(--border);margin:1.25rem 0}
.text-accent{color:var(--accent)}
.text-muted{color:var(--text-muted)}
.text-danger{color:var(--danger)}
.text-success{color:var(--success)}
.font-display{font-family:var(--font-display)}
.font-mono{font-family:monospace}

/* Alert boxes */
.alert{
  display:flex;align-items:center;gap:.85rem;
  padding:1rem 1.25rem;border-radius:12px;margin-bottom:1.25rem;
  font-size:.875rem;font-weight:500;animation:slideIn .3s ease;
}
.alert-ok   {background:rgba(6,214,160,.1);border:1px solid rgba(6,214,160,.25);color:var(--success)}
.alert-err  {background:rgba(255,77,109,.1);border:1px solid rgba(255,77,109,.25);color:var(--danger)}
.alert-warn {background:rgba(255,190,11,.1);border:1px solid rgba(255,190,11,.25);color:var(--warning)}
.alert-icon{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:800;font-size:.85rem}
.alert-ok   .alert-icon{background:rgba(6,214,160,.15);color:var(--success)}
.alert-err  .alert-icon{background:rgba(255,77,109,.15);color:var(--danger)}

/* ── RESPONSIVE ──────────────────────────────────────── */
@media(max-width:900px){
  .dashboard-layout{grid-template-columns:1fr}
  .sidebar{height:auto;position:relative;flex-direction:row;flex-wrap:wrap}
}

/* ── DARK SELECT FIX ─────────────────────────────────── */
select{color-scheme:dark}
select option{background:#0c1422!important;color:var(--text-primary)!important}
</style>
<div id="toast-container"></div>
<script>
window.RM = {
  toast(msg, type='success', title='', duration=4200) {
    const icons  = {success:'✓', error:'✕', warning:'⚠', info:'ℹ'};
    const titles = {success:'Éxito', error:'Error', warning:'Advertencia', info:'Info'};
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.innerHTML = '<div class="toast-icon">' + icons[type] + '</div>'
      + '<div class="toast-body"><div class="toast-title">' + (title || titles[type]) + '</div>'
      + '<div class="toast-msg">' + msg + '</div></div>'
      + '<button class="toast-close" onclick="this.closest(\'.toast\').remove()">×</button>';
    document.getElementById('toast-container').prepend(t);
    setTimeout(() => { t.classList.add('fade-out'); setTimeout(() => t.remove(), 420); }, duration);
  },

  confirm(msg, onYes, onNo) {
    const ov = document.createElement('div');
    ov.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.72);z-index:10000;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(6px)';
    ov.innerHTML = '<div style="background:#0c1422;border:1px solid rgba(0,255,200,.15);border-radius:18px;padding:2.25rem;max-width:420px;width:92%;box-shadow:0 24px 80px rgba(0,0,0,.8)">'
      + '<div style="font-size:2rem;margin-bottom:1rem;text-align:center">⚠️</div>'
      + '<p style="font-size:.95rem;line-height:1.65;color:#f0f4ff;text-align:center;margin-bottom:2rem">' + msg + '</p>'
      + '<div style="display:flex;gap:.75rem;justify-content:center">'
      + '<button id="rm-no" style="padding:.7rem 1.75rem;border-radius:10px;background:transparent;border:1px solid rgba(255,255,255,.12);color:#6b7fa3;cursor:pointer;font-size:.9rem;font-family:inherit">Cancelar</button>'
      + '<button id="rm-yes" style="padding:.7rem 1.75rem;border-radius:10px;background:#ff4d6d;border:none;color:#fff;cursor:pointer;font-weight:700;font-size:.9rem;font-family:inherit">Confirmar</button>'
      + '</div></div>';
    document.body.appendChild(ov);
    ov.querySelector('#rm-yes').onclick = () => { ov.remove(); onYes && onYes(); };
    ov.querySelector('#rm-no').onclick  = () => { ov.remove(); onNo  && onNo();  };
    ov.onclick = e => { if (e.target === ov) { ov.remove(); onNo && onNo(); } };
  }
};

document.addEventListener('DOMContentLoaded', () => {
  // Show flash toasts from data-toast elements
  document.querySelectorAll('[data-toast]').forEach(el => {
    const msg = el.dataset.msg || el.textContent.trim();
    if (msg) RM.toast(msg, el.dataset.type || 'success');
    el.remove();
  });
  // Show flash alerts
  document.querySelectorAll('.rm-alert, .alert-ok, .alert-err, .alert-warn').forEach(el => {
    const type = el.classList.contains('alert-ok') ? 'success'
               : el.classList.contains('alert-warn') ? 'warning' : 'error';
    const msg = el.textContent.trim();
    if (msg) { RM.toast(msg, type); el.remove(); }
  });
});
</script>
