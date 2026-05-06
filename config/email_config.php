<?php
// ════════════════════════════════════════════════════
// CORREO — Reverse Market
// Configura UNO de estos proveedores (todos gratuitos)
// ════════════════════════════════════════════════════

// ── OPCIÓN 1: Gmail (ilimitado, requiere app password) ──
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USER',     '');   // <-- Tu Gmail: ejemplo@gmail.com
define('SMTP_PASS',     '');   // <-- Contraseña de aplicación de 16 caracteres
define('SMTP_FROM',     '');   // <-- Igual que SMTP_USER
define('SMTP_FROMNAME', 'Reverse Market');

// ── OPCIÓN 2: Outlook/Hotmail (ilimitado, requiere app password) ──
// define('SMTP_HOST', 'smtp-mail.outlook.com');
// define('SMTP_PORT', 587);
// define('SMTP_USER', 'ejemplo@hotmail.com');
// define('SMTP_PASS', 'tu_contraseña_normal'); // Outlook NO requiere app password
// define('SMTP_FROM', 'ejemplo@hotmail.com');
// define('SMTP_FROMNAME', 'Reverse Market');

// App URL
define('APP_URL', 'http://localhost/Proyecto_Reverse_Market/public');
