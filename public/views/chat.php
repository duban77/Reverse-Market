<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['rol'])) { header("Location: login.php"); exit; }
$id  = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'];
$con = (int)($_GET['con'] ?? 0); // ID of the other user

// Get list of conversations
try {
    $convos = $pdo->prepare("SELECT DISTINCT u.id,u.nombre,u.rol FROM chat_mensajes cm JOIN usuarios u ON (cm.id_emisor=u.id OR cm.id_receptor=u.id) WHERE (cm.id_emisor=? OR cm.id_receptor=?) AND u.id!=? ORDER BY u.nombre");
    $convos->execute([$id,$id,$id]); $convos=$convos->fetchAll();
} catch(PDOException $e){ $convos=[]; }
// Current conversation
$receptor = null;
if ($con) { try { $st=$pdo->prepare("SELECT * FROM usuarios WHERE id=?");$st->execute([$con]);$receptor=$st->fetch(); } catch(PDOException $e){} }
// Messages
$mensajes = [];
if ($con) { try { $st=$pdo->prepare("SELECT cm.*,u.nombre AS emisor_nombre FROM chat_mensajes cm JOIN usuarios u ON cm.id_emisor=u.id WHERE (cm.id_emisor=? AND cm.id_receptor=?) OR (cm.id_emisor=? AND cm.id_receptor=?) ORDER BY cm.fecha_envio ASC LIMIT 80"); $st->execute([$id,$con,$con,$id]); $mensajes=$st->fetchAll(); } catch(PDOException $e){} }
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Chat — Reverse Market</title>
<?php include __DIR__ . '/../partials/head.php'; ?>
<style>
.chat-layout{display:grid;grid-template-columns:260px 1fr;height:calc(100vh - 4rem);gap:0;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;background:var(--bg-card)}
.chat-sidebar{border-right:1px solid var(--border);background:var(--bg-panel);display:flex;flex-direction:column}
.chat-sidebar-header{padding:1rem;border-bottom:1px solid var(--border);font-weight:700;font-size:.88rem;color:var(--text-muted)}
.convo-list{overflow-y:auto;flex:1}
.convo-item{display:flex;align-items:center;gap:.75rem;padding:.85rem 1rem;cursor:pointer;transition:background .15s;text-decoration:none;border-bottom:1px solid rgba(0,255,200,.04)}
.convo-item:hover,.convo-item.active{background:rgba(0,255,200,.06)}
.convo-item.active .convo-name{color:var(--accent)}
.convo-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent-dim));color:#040810;font-weight:800;font-size:.85rem;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.convo-name{font-size:.88rem;font-weight:600}
.convo-role{font-size:.72rem;color:var(--text-muted)}
.chat-main{display:flex;flex-direction:column;height:100%}
.chat-header{padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.75rem;background:var(--bg-card)}
.messages{flex:1;overflow-y:auto;padding:1rem;display:flex;flex-direction:column;gap:.6rem}
.msg{max-width:72%;padding:.65rem .95rem;border-radius:14px;font-size:.875rem;line-height:1.55}
.msg.mine{align-self:flex-end;background:rgba(0,255,200,.12);border:1px solid rgba(0,255,200,.2);border-bottom-right-radius:4px}
.msg.theirs{align-self:flex-start;background:var(--bg-panel);border:1px solid var(--border);border-bottom-left-radius:4px}
.msg-time{font-size:.68rem;color:var(--text-dim);margin-top:.2rem}
.chat-input-area{padding:.85rem;border-top:1px solid var(--border);display:flex;gap:.6rem;background:var(--bg-card)}
.chat-input{flex:1;background:var(--bg-input);border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:.65rem 1rem;color:var(--text-primary);font-family:var(--font-body);font-size:.9rem}
.chat-input:focus{outline:none;border-color:var(--accent)}
.no-convo{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:1rem;color:var(--text-muted)}
</style>
</head><body>
<div class="dashboard-layout">
<?php if($rol==='comprador'): include __DIR__ . '/../partials/sidebar_comprador.php';
else: include __DIR__ . '/../partials/sidebar_vendedor.php'; endif;?>
<div class="main-content" style="padding:1.5rem 1.75rem">
  <div class="page-header" style="margin-bottom:1rem"><div><h1 class="page-title">💬 Chat</h1></div></div>
  <div class="chat-layout">
    <!-- Sidebar -->
    <div class="chat-sidebar">
      <div class="chat-sidebar-header">Conversaciones</div>
      <div class="convo-list">
        <?php if(empty($convos)):?>
          <div style="padding:1.5rem;text-align:center;font-size:.82rem;color:var(--text-dim)">Sin conversaciones aún</div>
        <?php else: foreach($convos as $c):?>
          <a href="chat.php?con=<?=$c['id']?>" class="convo-item <?=$con==$c['id']?'active':''?>">
            <div class="convo-avatar"><?=strtoupper(mb_substr($c['nombre'],0,2))?></div>
            <div><div class="convo-name"><?=htmlspecialchars(mb_substr($c['nombre'],0,18))?></div><div class="convo-role"><?=ucfirst($c['rol'])?></div></div>
          </a>
        <?php endforeach; endif;?>
      </div>
    </div>
    <!-- Main -->
    <div class="chat-main">
      <?php if(!$receptor):?>
        <div class="no-convo"><span style="font-size:3rem">💬</span><p>Selecciona una conversación o inicia una nueva desde el perfil de un usuario.</p></div>
      <?php else:?>
        <div class="chat-header">
          <div class="convo-avatar"><?=strtoupper(mb_substr($receptor['nombre'],0,2))?></div>
          <div><div style="font-weight:700"><?=htmlspecialchars($receptor['nombre'])?></div><div style="font-size:.76rem;color:var(--text-muted)"><?=ucfirst($receptor['rol']??'')?></div></div>
        </div>
        <div class="messages" id="msgBox">
          <?php foreach($mensajes as $m): $mine=$m['id_emisor']==$id;?>
          <div class="msg <?=$mine?'mine':'theirs'?>" id="msg-<?=$m['id']?>">
            <?=htmlspecialchars($m['mensaje'])?>
            <div class="msg-time"><?=date('d/m H:i',strtotime($m['fecha_envio']))?></div>
          </div>
          <?php endforeach;?>
        </div>
        <div class="chat-input-area">
          <input type="text" class="chat-input" id="msgInput" placeholder="Escribe un mensaje..." onkeypress="if(event.key==='Enter')sendMsg()">
          <button onclick="sendMsg()" class="btn btn-primary">Enviar</button>
        </div>
      <?php endif;?>
    </div>
  </div>
</div>
</div>
<script>
const msgBox=document.getElementById('msgBox');
if(msgBox) msgBox.scrollTop=msgBox.scrollHeight;

async function sendMsg(){
  const inp=document.getElementById('msgInput');
  const msg=inp.value.trim();
  if(!msg)return;
  const sendBtn=document.querySelector('.chat-input-area .btn');
  inp.value='';sendBtn.disabled=true;sendBtn.textContent='...';
  const fd=new FormData();
  fd.append('action','send');
  fd.append('receptor_id','<?=$con?>');
  fd.append('mensaje',msg);
  try{
    const res=await fetch('../controllers/ChatController.php',{method:'POST',body:fd});
    const data=await res.json();
    if(data.ok){
      const d=document.createElement('div');d.className='msg mine';
      d.id='msg-'+data.id;
      d.innerHTML=msg.replace(/[<>&"]/g,c=>({'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[c]))
        +'<div class="msg-time">'+data.hora+'</div>';
      msgBox.appendChild(d);msgBox.scrollTop=msgBox.scrollHeight;
      if(data.id&&data.id>lastId)lastId=data.id;
    } else {
      console.error('Chat error:',data.error);
      inp.value=msg; // restore message if failed
    }
  }catch(e){console.error('Fetch error:',e);inp.value=msg;}
  finally{sendBtn.disabled=false;sendBtn.textContent='Enviar';inp.focus();}
}

// Poll for new messages
<?php if($con):?>
let lastId=<?=empty($mensajes)?0:end($mensajes)['id']?>;
setInterval(async()=>{
  try{
    const res=await fetch('../controllers/ChatController.php?action=load&con=<?=$con?>&last='+lastId);
    const data=await res.json();
    if(data.messages&&data.messages.length){
      data.messages.forEach(m=>{
        if(document.getElementById('msg-'+m.id)) return;
        const d=document.createElement('div');
        d.className='msg '+(m.mine?'mine':'theirs');
        d.id='msg-'+m.id;
        d.innerHTML=m.mensaje+'<div class="msg-time">'+m.hora+'</div>';
        msgBox.appendChild(d);if(parseInt(m.id)>lastId)lastId=parseInt(m.id);
      });
      msgBox.scrollTop=msgBox.scrollHeight;
    }
  }catch(e){}
},3000);
<?php endif;?>
</script>
</body></html>
