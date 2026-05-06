<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'comprador') {
    header("Location: ../views/login.php"); exit;
}
if (!isset($_SESSION['pago_oferta'])) {
    header("Location: ../views/ofertas_recibidas.php"); exit;
}

$id         = $_SESSION['usuario_id'];
$pago       = $_SESSION['pago_oferta'];
$metodo     = $_POST['metodo'] ?? 'efectivo';
$comprobante = trim($_POST['referencia_comprobante'] ?? '');

$monto = (float)$pago['monto'];
$iva   = round($monto * 0.19, 2);
$total = $monto + $iva;
$ref   = 'RM-' . $id . '-' . time();

try {
    $pdo->beginTransaction();

    // Mark oferta as accepted if it's a necesidad offer
    if (!empty($pago['oferta_id'])) {
        try {
            $pdo->prepare("UPDATE oferta_necesidad SET estado='aceptada' WHERE id=? AND id_comprador=?")
                ->execute([$pago['oferta_id'], $id]);
        } catch(PDOException $e){}
    }

    // Estado depends on method
    $estado_tx = in_array($metodo, ['nequi','daviplata','transferencia']) ? 'pendiente' : 'pendiente';

    // Record transaction
    $pdo->prepare("INSERT INTO transacciones (id_comprador, id_vendedor, producto_id, monto, estado)
                   VALUES (?,?,?,?,?)")
        ->execute([$id, $pago['id_vendedor'], $pago['id_producto'] ?? null, $total, $estado_tx]);
    $tx_id = $pdo->lastInsertId();

    // Notify vendor with comprobante if provided
    $comp_text = $comprobante ? " · Comprobante: $comprobante" : '';
    $metodo_labels = ['nequi'=>'Nequi','daviplata'=>'Daviplata','transferencia'=>'Transferencia bancaria','efectivo'=>'Efectivo'];
    $metodo_label  = $metodo_labels[$metodo] ?? $metodo;
    $msg_not = "💳 {$_SESSION['nombre']} realizó el pago por \"{$pago['producto']}\" vía $metodo_label$comp_text";
    try {
        $pdo->prepare("INSERT INTO notificaciones (mensaje, id_usuario_destino, tipo) VALUES (?,?,'oferta')")
            ->execute([$msg_not, $pago['id_vendedor']]);
    } catch(PDOException $ignored){}

    $pdo->commit();

    $_SESSION['pago_exito'] = [
        'tx_id'       => $tx_id,
        'producto'    => $pago['producto'],
        'vendedor'    => $pago['vendedor'] ?? '—',
        'monto'       => $total,
        'metodo'      => $metodo_label,
        'estado'      => $estado_tx,
        'ref'         => $ref,
        'comprobante' => $comprobante,
    ];
    unset($_SESSION['pago_oferta']);
    header("Location: ../views/pago_exitoso.php"); exit;

} catch(PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['flash_error'] = 'Error al registrar el pago: ' . $e->getMessage();
    header("Location: ../views/pago_oferta.php"); exit;
}
