<?php
require_once __DIR__ . '/../../config/session.php';
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../views/login.php'); exit;
}

if ($_POST['action'] === 'save_mp_token') {
    $token    = trim($_POST['token'] ?? '');
    $redirect = $_POST['redirect'] ?? '../views/pago_oferta.php';

    // Validate token format
    if (!preg_match('/^(TEST-|APP_USR-)/', $token)) {
        $_SESSION['flash_error'] = 'Token inválido. Debe empezar con TEST- o APP_USR-';
        header("Location: ../views/$redirect"); exit;
    }

    // Save token to a config file (not in public folder)
    $config_file = __DIR__ . '/../../config/mp_config.php';
    $content = '<?php' . "\n" . 'define("MP_ACCESS_TOKEN", ' . var_export($token, true) . ');' . "\n";

    if (file_put_contents($config_file, $content) !== false) {
        $_SESSION['flash_ok'] = '✅ MercadoPago configurado correctamente.';
    } else {
        $_SESSION['flash_error'] = 'No se pudo guardar. Edita manualmente pago_oferta.php';
    }
    header("Location: ../views/pago_oferta.php"); exit;
}

header('Location: ../views/home_comprador.php');
