<?php
// Bypass ngrok browser warning for MercadoPago redirects
// This file is auto-included via .htaccess when coming from ngrok
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) || 
    isset($_SERVER['HTTP_X_NGROK_TUNNEL'])) {
    header('ngrok-skip-browser-warning: true');
}
