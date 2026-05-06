<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../views/login.php'); exit;
}

$action     = $_POST['action'] ?? '';
$usuario_id = $_SESSION['usuario_id'];

// Always redirect back to calificaciones page (from controllers/ → views/)
define('BACK', '../views/calificaciones_comprador.php');

if ($action === 'calificar') {
    $id_vendedor = (int)($_POST['id_vendedor'] ?? 0);
    $id_producto = (int)($_POST['id_producto'] ?? 0);
    $puntuacion  = (int)($_POST['puntuacion']  ?? 0);
    $comentario  = trim($_POST['comentario']   ?? '');

    if (!$id_vendedor || $puntuacion < 1 || $puntuacion > 5) {
        $_SESSION['flash_error'] = 'Selecciona una puntuación entre 1 y 5 estrellas.';
        header('Location: ' . BACK); exit;
    }

    try {
        // Check if already rated
        $exists = $pdo->prepare("SELECT id FROM calificaciones WHERE id_comprador=? AND id_vendedor=?" .
                                ($id_producto ? " AND id_producto=?" : " AND id_producto IS NULL"));
        $params = [$usuario_id, $id_vendedor];
        if ($id_producto) $params[] = $id_producto;
        $exists->execute($params);
        $existing = $exists->fetch();

        if ($existing) {
            $pdo->prepare("UPDATE calificaciones SET puntuacion=?, comentario=?, fecha=NOW()
                           WHERE id_comprador=? AND id_vendedor=?" .
                          ($id_producto ? " AND id_producto=?" : " AND id_producto IS NULL"))
                ->execute(array_merge([$puntuacion, $comentario, $usuario_id, $id_vendedor],
                                      $id_producto ? [$id_producto] : []));
            $_SESSION['flash_ok'] = '✅ Calificación actualizada correctamente.';
        } else {
            $pdo->prepare("INSERT INTO calificaciones (id_comprador, id_vendedor, id_producto, puntuacion, comentario)
                           VALUES (?,?,?,?,?)")
                ->execute([$usuario_id, $id_vendedor, $id_producto ?: null, $puntuacion, $comentario]);
            $_SESSION['flash_ok'] = '✅ ¡Gracias por tu calificación!';
            // Notify vendor (safe)
            try {
                $estrellas = str_repeat('★', $puntuacion);
                $pdo->prepare("INSERT INTO notificaciones (mensaje, id_usuario_destino, tipo) VALUES (?,?,'sistema')")
                    ->execute(["{$_SESSION['nombre']} te calificó: $estrellas", $id_vendedor]);
            } catch (PDOException $ignored) {}
        }
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = 'Error al guardar: ' . htmlspecialchars($e->getMessage());
    }
    header('Location: ' . BACK); exit;
}

header('Location: ' . BACK);
