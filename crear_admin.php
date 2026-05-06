<?php
/**
 * REVERSE MARKET — Crear admin + reparar BD
 * 1. Copia este archivo en: C:\xampp\htdocs\Proyecto_Reverse_Market\
 * 2. Abre: http://localhost/Proyecto_Reverse_Market/crear_admin.php
 * 3. Elimina el archivo después de usarlo.
 */
$host = 'localhost'; $db = 'reverse_market'; $user = 'root'; $pass = '';
$log = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $log[] = ['ok', 'Conexión a la BD exitosa'];

    // 1. Add missing columns safely
    $fixes = [
        "ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS estado ENUM('activo','bloqueado') DEFAULT 'activo'",
        "ALTER TABLE notificaciones ADD COLUMN IF NOT EXISTS id_usuario_destino INT NULL",
        "ALTER TABLE notificaciones ADD COLUMN IF NOT EXISTS tipo ENUM('oferta','mensaje','sistema') DEFAULT 'mensaje'",
        "ALTER TABLE reportes ADD COLUMN IF NOT EXISTS usuario_id INT NULL",
    ];
    foreach ($fixes as $sql) {
        try { $pdo->exec($sql); $log[] = ['ok', 'Columna verificada/agregada']; }
        catch (PDOException $e) { $log[] = ['warn', 'Columna ya existía o no aplica: ' . $e->getMessage()]; }
    }

    // 2. Create missing tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS carrito (
            id INT AUTO_INCREMENT PRIMARY KEY, id_comprador INT NOT NULL,
            id_producto INT NOT NULL, cantidad INT NOT NULL DEFAULT 1,
            fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_item (id_comprador, id_producto))",
        "CREATE TABLE IF NOT EXISTS chat_mensajes (
            id INT AUTO_INCREMENT PRIMARY KEY, id_emisor INT NOT NULL,
            id_receptor INT NOT NULL, mensaje TEXT NOT NULL,
            fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP, leido BOOLEAN DEFAULT FALSE)",
        "CREATE TABLE IF NOT EXISTS transacciones (
            id INT AUTO_INCREMENT PRIMARY KEY, id_comprador INT NOT NULL,
            id_vendedor INT NOT NULL, producto_id INT NOT NULL,
            monto DECIMAL(10,2) NOT NULL, fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
            estado ENUM('pendiente','completada','cancelada') DEFAULT 'pendiente')",
    ];
    foreach ($tables as $sql) {
        try { $pdo->exec($sql); $log[] = ['ok', 'Tabla verificada/creada']; }
        catch (PDOException $e) { $log[] = ['warn', $e->getMessage()]; }
    }

    // 3. Update existing users - set estado = 'activo' where NULL
    try {
        $pdo->exec("UPDATE usuarios SET estado='activo' WHERE estado IS NULL");
        $log[] = ['ok', 'Estado de usuarios actualizado'];
    } catch (PDOException $e) { $log[] = ['warn', $e->getMessage()]; }

    // 4. Create admin user
    $correo   = 'admin@reversemarket.com';
    $password = 'admin1234';
    $hash     = password_hash($password, PASSWORD_DEFAULT);

    // Check if admin exists
    $existe = $pdo->prepare("SELECT id, contraseña FROM usuarios WHERE correo = ?");
    $existe->execute([$correo]);
    $adminExistente = $existe->fetch(PDO::FETCH_ASSOC);

    if ($adminExistente) {
        // Update password and role
        $pdo->prepare("UPDATE usuarios SET contraseña=?, rol='admin', estado='activo', nombre='Administrador' WHERE correo=?")
            ->execute([$hash, $correo]);
        $id = $adminExistente['id'];
        $log[] = ['ok', "Admin actualizado (ID: $id)"];
    } else {
        // Insert new admin (without estado first, then update)
        try {
            $pdo->prepare("INSERT INTO usuarios (nombre, correo, contraseña, rol, estado) VALUES ('Administrador',?,?,'admin','activo')")
                ->execute([$correo, $hash]);
        } catch (PDOException $e) {
            // Fallback without estado column
            $pdo->prepare("INSERT INTO usuarios (nombre, correo, contraseña, rol) VALUES ('Administrador',?,?,'admin')")
                ->execute([$correo, $hash]);
        }
        $id = $pdo->lastInsertId();
        $log[] = ['ok', "Admin creado (ID: $id)"];
    }

    // 5. Verify login works
    $verify = $pdo->prepare("SELECT * FROM usuarios WHERE correo=?");
    $verify->execute([$correo]);
    $admin = $verify->fetch(PDO::FETCH_ASSOC);
    $loginOk = password_verify($password, $admin['contraseña']);
    $log[] = $loginOk
        ? ['ok', '✅ Verificación de contraseña: CORRECTA — el login funcionará']
        : ['error', '❌ Verificación fallida'];

    $success = true;
} catch (PDOException $e) {
    $log[] = ['error', $e->getMessage()];
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Admin — Reverse Market</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,sans-serif;background:#05080f;color:#eef2ff;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
.box{background:#0b1120;border:1px solid rgba(0,255,200,.2);border-radius:16px;padding:2.5rem;max-width:560px;width:100%}
h2{font-size:1.4rem;font-weight:800;margin-bottom:.5rem;color:#00ffc8}
.sub{color:#6b7fa3;font-size:.88rem;margin-bottom:1.75rem}
.log{display:flex;flex-direction:column;gap:.4rem;margin-bottom:1.75rem}
.log-item{display:flex;align-items:flex-start;gap:.6rem;font-size:.82rem;padding:.45rem .75rem;border-radius:8px}
.log-item.ok   {background:rgba(6,214,160,.08);color:#06d6a0}
.log-item.warn {background:rgba(255,190,11,.08);color:#ffbe0b}
.log-item.error{background:rgba(255,77,109,.1);color:#ff4d6d}
.log-icon{flex-shrink:0;font-weight:800}
table{width:100%;border-collapse:collapse;margin-bottom:1.5rem}
td{padding:.65rem .75rem;border-bottom:1px solid rgba(255,255,255,.06);font-size:.88rem}
td:first-child{color:#6b7fa3;width:38%}
td:last-child{font-weight:700}
.btn{display:block;text-align:center;background:#00ffc8;color:#05080f;padding:.8rem 2rem;border-radius:10px;text-decoration:none;font-weight:800;margin-bottom:.75rem;font-size:.95rem}
.btn-outline{background:transparent;border:1px solid rgba(0,255,200,.3);color:#00ffc8}
.warn-box{background:rgba(255,190,11,.08);border:1px solid rgba(255,190,11,.2);border-radius:10px;padding:.85rem 1rem;font-size:.82rem;color:#ffbe0b;margin-top:.75rem}
</style>
</head>
<body>
<div class="box">
  <h2><?= $success ? '✅ Admin listo' : '❌ Error' ?></h2>
  <p class="sub">Reverse Market — Configuración del administrador</p>

  <div class="log">
    <?php foreach ($log as [$type, $msg]): ?>
    <div class="log-item <?= $type ?>">
      <span class="log-icon"><?= $type==='ok'?'✓':($type==='warn'?'⚠':'✕') ?></span>
      <span><?= htmlspecialchars($msg) ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if ($success && $loginOk): ?>
  <table>
    <tr><td>Correo</td><td>admin@reversemarket.com</td></tr>
    <tr><td>Contraseña</td><td>admin1234</td></tr>
    <tr><td>Rol</td><td>Administrador</td></tr>
  </table>

  <?php
  // Detect project URL
  $base = str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']));
  $loginUrl = 'http://'.$_SERVER['HTTP_HOST'].$base.'/public/views/login.php';
  ?>
  <a href="<?= htmlspecialchars($loginUrl) ?>" class="btn">Ir al Login →</a>
  <div class="warn-box">⚠ Elimina <strong>crear_admin.php</strong> de tu servidor después de usarlo.</div>

  <?php else: ?>
  <p style="color:#6b7fa3;font-size:.9rem">Revisa los errores arriba. Asegúrate de que MySQL esté activo y la BD <strong>reverse_market</strong> exista.</p>
  <?php endif; ?>
</div>
</body>
</html>
