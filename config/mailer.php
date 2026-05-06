<?php
require_once __DIR__ . '/email_config.php';
require_once __DIR__ . '/PHPMailer.php';

function enviar_codigo_email(string $to, string $nombre, string $codigo): bool {
    if (!SMTP_USER || !SMTP_PASS) return false;

    $mail = new PHPMailer();
    $mail->Host       = SMTP_HOST;
    $mail->Port       = SMTP_PORT;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->From       = SMTP_FROM ?: SMTP_USER;
    $mail->FromName   = SMTP_FROMNAME;
    $mail->Subject    = 'Tu código de verificación — Reverse Market';
    $mail->isHTML     = true;
    $mail->addAddress($to, $nombre);

    $mail->Body = '
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#040810;font-family:Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 16px"><tr><td align="center">
<table width="460" cellpadding="0" cellspacing="0" style="background:#0c1422;border-radius:16px;border:1px solid rgba(0,255,200,.15)">
  <tr><td style="padding:24px 32px;border-bottom:1px solid rgba(0,255,200,.1)">
    <span style="font-size:20px;font-weight:900;color:#f0f4ff;font-family:Arial">Reverse<span style="color:#00ffc8">Market</span></span>
  </td></tr>
  <tr><td style="padding:32px">
    <h2 style="color:#f0f4ff;font-size:19px;margin:0 0 10px">Código de verificación</h2>
    <p style="color:#6b7fa3;font-size:14px;line-height:1.7;margin:0 0 24px">
      Hola <strong style="color:#f0f4ff">' . htmlspecialchars($nombre) . '</strong>,<br>
      Usa este código para restablecer tu contraseña:
    </p>
    <div style="text-align:center;margin:28px 0">
      <div style="display:inline-block;background:#070d1a;border:2px solid #00ffc8;border-radius:14px;padding:16px 36px">
        <span style="font-family:monospace;font-size:40px;font-weight:900;color:#00ffc8;letter-spacing:10px">' . $codigo . '</span>
      </div>
    </div>
    <p style="color:#6b7fa3;font-size:13px;text-align:center;margin:16px 0 0">
      ⏱ Expira en <strong style="color:#ffbe0b">15 minutos</strong> · Si no lo solicitaste, ignora este mensaje.
    </p>
  </td></tr>
  <tr><td style="padding:14px 32px;border-top:1px solid rgba(0,255,200,.07);text-align:center">
    <p style="color:#2d3d5a;font-size:11px;margin:0">© 2025 Reverse Market · Colombia</p>
  </td></tr>
</table>
</td></tr></table>
</body></html>';

    return $mail->send();
}
