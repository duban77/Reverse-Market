<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Normalize session variable aliases for backwards compatibility
if (isset($_SESSION['usuario_id']) && !isset($_SESSION['user_id'])) {
    $_SESSION['user_id']   = $_SESSION['usuario_id'];
    $_SESSION['user_name'] = $_SESSION['nombre']  ?? '';
    $_SESSION['user_role'] = $_SESSION['rol']     ?? '';
}
if (isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = $_SESSION['user_id'];
    $_SESSION['nombre']     = $_SESSION['user_name'] ?? '';
    $_SESSION['rol']        = $_SESSION['user_role'] ?? '';
}
