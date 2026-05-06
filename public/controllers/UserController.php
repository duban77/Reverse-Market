<?php
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');
if (session_status() === PHP_SESSION_NONE) session_start();

// ========== REGISTRO ==========
if (isset($_POST['register'])) {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirmPassword'] ?? '';
    $role     = $_POST['role'] ?? 'comprador';

    $errors = [];
    if ($password !== $confirm)     $errors[] = 'Las contraseñas no coinciden.';
    if (strlen($name) < 2)          $errors[] = 'El nombre es muy corto.';
    if (strlen($password) < 6)      $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    if (!in_array($role, ['comprador','vendedor'])) $errors[] = 'Rol inválido.';

    if ($errors) {
        $_SESSION['flash_error'] = implode(' ', $errors);
        header("Location: ../views/register.php"); exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['flash_error'] = 'Este correo ya está registrado.';
            header("Location: ../views/register.php"); exit;
        }
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO usuarios (nombre, correo, contraseña, rol) VALUES (?, ?, ?, ?)")
            ->execute([$name, $email, $hashed, $role]);
        $_SESSION['flash_ok'] = '¡Cuenta creada exitosamente! Inicia sesión.';
        header("Location: ../views/login.php"); exit;
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = 'Error al registrar. Intenta nuevamente.';
        header("Location: ../views/register.php"); exit;
    }
}

// ========== LOGIN ==========
if (isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['contraseña'])) {
            if ($user['estado'] === 'bloqueado') {
                $_SESSION['flash_error'] = 'Tu cuenta está bloqueada. Contacta al administrador.';
                header("Location: ../views/login.php"); exit;
            }
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nombre']     = $user['nombre'];
            $_SESSION['rol']        = $user['rol'];
            $_SESSION['correo']     = $user['correo'];
            // compat
            $_SESSION['usuario_id']    = $user['id'];
            $_SESSION['nombre']  = $user['nombre'];
            $_SESSION['rol']  = $user['rol'];

            switch ($user['rol']) {
                case 'comprador': header("Location: ../views/home_comprador.php"); break;
                case 'vendedor':  header("Location: ../views/home_vendedor.php");  break;
                case 'admin':     header("Location: ../views/admin_dashboard.php"); break;
            }
            exit;
        } else {
            $_SESSION['flash_error'] = 'Correo o contraseña incorrectos.';
            header("Location: ../views/login.php"); exit;
        }
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = 'Error del servidor. Intenta nuevamente.';
        header("Location: ../views/login.php"); exit;
    }
}
