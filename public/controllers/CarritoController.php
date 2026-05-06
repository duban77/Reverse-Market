<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$comprador_id = $_SESSION['usuario_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

header('Content-Type: application/json');

try {
    // Auto-create carrito table if not exists
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS carrito (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_comprador INT NOT NULL,
            id_producto  INT NOT NULL,
            cantidad     INT NOT NULL DEFAULT 1,
            fecha        DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_item (id_comprador, id_producto),
            FOREIGN KEY (id_comprador) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY (id_producto)  REFERENCES productos(id) ON DELETE CASCADE
        )");
    } catch (PDOException $ignored) {}
    
        switch ($action) {

        case 'add':
            $producto_id = (int)($_POST['producto_id'] ?? 0);
            $cantidad    = max(1, (int)($_POST['cantidad'] ?? 1));
            if (!$producto_id) { echo json_encode(['error'=>'Producto inválido']); exit; }
            // Verify product exists
            $p = $pdo->prepare("SELECT id, nombre, precio FROM productos WHERE id=?");
            $p->execute([$producto_id]);
            if (!$p->fetch()) { echo json_encode(['error'=>'Producto no encontrado']); exit; }
            // Insert or update
            $pdo->prepare("INSERT INTO carrito (id_comprador, id_producto, cantidad)
                           VALUES (?,?,?) ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)")
                ->execute([$comprador_id, $producto_id, $cantidad]);
            $count = $pdo->prepare("SELECT SUM(cantidad) FROM carrito WHERE id_comprador=?");
            $count->execute([$comprador_id]);
            echo json_encode(['ok'=>true, 'count'=>(int)$count->fetchColumn()]);
            break;

        case 'remove':
            $producto_id = (int)($_POST['producto_id'] ?? 0);
            $pdo->prepare("DELETE FROM carrito WHERE id_comprador=? AND id_producto=?")
                ->execute([$comprador_id, $producto_id]);
            echo json_encode(['ok'=>true]);
            break;

        case 'update':
            $producto_id = (int)($_POST['producto_id'] ?? 0);
            $cantidad    = max(1, (int)($_POST['cantidad'] ?? 1));
            $pdo->prepare("UPDATE carrito SET cantidad=? WHERE id_comprador=? AND id_producto=?")
                ->execute([$cantidad, $comprador_id, $producto_id]);
            echo json_encode(['ok'=>true]);
            break;

        case 'count':
            $st = $pdo->prepare("SELECT COALESCE(SUM(cantidad),0) FROM carrito WHERE id_comprador=?");
            $st->execute([$comprador_id]);
            echo json_encode(['count'=>(int)$st->fetchColumn()]);
            break;

        case 'checkout':
            // Process all cart items as transactions
            $items = $pdo->prepare("SELECT c.*, p.precio, p.id_vendedor, p.nombre AS prod_nombre, u.nombre AS vend_nombre
                                    FROM carrito c
                                    JOIN productos p ON c.id_producto = p.id
                                    JOIN usuarios u ON p.id_vendedor = u.id
                                    WHERE c.id_comprador=?");
            $items->execute([$comprador_id]);
            $items = $items->fetchAll();
            if (empty($items)) { echo json_encode(['error'=>'Carrito vacío']); exit; }

            $pdo->beginTransaction();
            $total = 0;
            foreach ($items as $item) {
                $monto = $item['precio'] * $item['cantidad'];
                $total += $monto;
                $pdo->prepare("INSERT INTO transacciones (id_comprador, id_vendedor, producto_id, monto, estado)
                                VALUES (?,?,?,?,'completada')")
                    ->execute([$comprador_id, $item['id_vendedor'], $item['id_producto'], $monto]);
                // Notify vendor
                $pdo->prepare("INSERT INTO notificaciones (mensaje, id_usuario_destino, tipo) VALUES (?,?,'oferta')")
                    ->execute(["Nueva compra de {$_SESSION['nombre']}: {$item['prod_nombre']}", $item['id_vendedor']]);
            }
            $pdo->prepare("DELETE FROM carrito WHERE id_comprador=?")->execute([$comprador_id]);
            $pdo->commit();
            echo json_encode(['ok'=>true, 'total'=>$total, 'items'=>count($items)]);
            break;

        default:
            echo json_encode(['error'=>'Acción no válida']);
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
