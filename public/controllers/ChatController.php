<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']); exit;
}
$mi_id  = $_SESSION['usuario_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Ensure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_mensajes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_emisor   INT NOT NULL,
        id_receptor INT NOT NULL,
        mensaje     TEXT NOT NULL,
        fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
        leido       BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (id_emisor)   REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (id_receptor) REFERENCES usuarios(id) ON DELETE CASCADE
    )");
} catch(PDOException $e){}

try {
    switch ($action) {

        case 'send':
            // Accept both 'receptor' and 'receptor_id'
            $receptor = (int)($_POST['receptor_id'] ?? $_POST['receptor'] ?? 0);
            $mensaje  = trim($_POST['mensaje'] ?? '');

            if (!$receptor || !$mensaje) {
                echo json_encode(['error' => 'Datos incompletos', 'receptor'=>$receptor, 'msg_len'=>strlen($mensaje)]); exit;
            }

            $stmt = $pdo->prepare("INSERT INTO chat_mensajes (id_emisor, id_receptor, mensaje) VALUES (?,?,?)");
            $stmt->execute([$mi_id, $receptor, $mensaje]);
            $new_id = $pdo->lastInsertId();

            // Notification
            try {
                $pdo->prepare("INSERT INTO notificaciones (mensaje, id_usuario_destino, tipo) VALUES (?,?,'mensaje')")
                    ->execute(["💬 Nuevo mensaje de {$_SESSION['nombre']}", $receptor]);
            } catch(PDOException $ignored){}

            echo json_encode([
                'ok'      => true,
                'id'      => $new_id,
                'mensaje' => $mensaje,
                'emisor'  => $_SESSION['nombre'],
                'hora'    => date('d/m H:i'),
            ]);
            break;

        case 'load':
            // Accept 'otro' or 'con', and 'since' or 'last'
            $otro  = (int)($_GET['otro'] ?? $_GET['con'] ?? 0);
            $since = (int)($_GET['since'] ?? $_GET['last'] ?? 0);

            if (!$otro) { echo json_encode(['messages' => []]); exit; }

            $sql    = "SELECT cm.id, cm.mensaje, cm.fecha_envio, cm.id_emisor,
                              u.nombre AS emisor_nombre
                       FROM chat_mensajes cm
                       JOIN usuarios u ON cm.id_emisor = u.id
                       WHERE ((cm.id_emisor=? AND cm.id_receptor=?)
                           OR (cm.id_emisor=? AND cm.id_receptor=?))";
            $params = [$mi_id, $otro, $otro, $mi_id];
            if ($since > 0) { $sql .= " AND cm.id > ?"; $params[] = $since; }
            $sql .= " ORDER BY cm.fecha_envio ASC LIMIT 80";

            $st = $pdo->prepare($sql);
            $st->execute($params);
            $msgs = $st->fetchAll();

            // Mark received messages as read
            $pdo->prepare("UPDATE chat_mensajes SET leido=1 WHERE id_emisor=? AND id_receptor=?")
                ->execute([$otro, $mi_id]);

            $out = [];
            foreach ($msgs as $m) {
                $out[] = [
                    'id'       => $m['id'],
                    'id_emisor'=> $m['id_emisor'],
                    'mine'     => $m['id_emisor'] == $mi_id,
                    'nombre'   => $m['emisor_nombre'],
                    'mensaje'  => htmlspecialchars($m['mensaje']),
                    'hora'     => date('d/m H:i', strtotime($m['fecha_envio'])),
                ];
            }
            echo json_encode(['messages' => $out]);
            break;

        default:
            echo json_encode(['error' => 'Acción no válida: ' . $action]);
    }

} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
