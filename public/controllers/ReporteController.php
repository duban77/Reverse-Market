<?php
// Incluye el archivo de conexión a la base de datos (ajusta la ruta según tu proyecto)
include '../config/conexion.php';

// Carga las librerías necesarias de PHPMailer para enviar correos
require '../lib/PHPMailer/PHPMailer.php';
require '../lib/PHPMailer/SMTP.php';
require '../lib/PHPMailer/Exception.php';

// Importa las clases necesarias de PHPMailer en el namespace correspondiente
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verifica que la petición sea mediante método POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtiene y convierte a entero el ID del producto enviado por POST
    $producto_id = intval($_POST['producto_id']);
    // Obtiene el motivo del reporte y elimina espacios en blanco al inicio y fin
    $motivo = trim($_POST['motivo']);

    // Prepara la consulta SQL para insertar el reporte en la tabla 'reportes'
    $stmt = $conn->prepare("INSERT INTO reportes (producto_id, motivo, fecha_reporte) VALUES (?, ?, NOW())");
    // Asigna los parámetros a la consulta (un entero y una cadena)
    $stmt->bind_param("is", $producto_id, $motivo);

    // Ejecuta la consulta y verifica si fue exitosa
    if ($stmt->execute()) {
        // Si se insertó correctamente, intenta enviar un correo notificando del reporte
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';          // Servidor SMTP de Gmail
            $mail->SMTPAuth   = true;                       // Habilita autenticación SMTP
            $mail->Username   = 'revere.market01@gmail.com'; // Tu correo electrónico
            $mail->Password   = 'fpepfwabastzbioz';        // Contraseña de aplicación (App Password)
            $mail->SMTPSecure = 'tls';                      // Seguridad TLS
            $mail->Port       = 587;                        // Puerto SMTP para TLS

            // Configura el remitente y el destinatario
            $mail->setFrom('revere.market01@gmail.com', 'Reporte Reverse Market');
            $mail->addAddress('revere.market01@gmail.com'); // Destinatario (puede ser el mismo o un admin)

            // Configura el correo como texto plano (no HTML)
            $mail->isHTML(false);
            // Asunto del correo
            $mail->Subject = 'Nuevo reporte de producto';
            // Cuerpo del correo con detalles del reporte
            $mail->Body    = "Se ha reportado el producto con ID: $producto_id\n\nMotivo:\n$motivo";

            // Envía el correo
            $mail->send();
        } catch (Exception $e) {
            // Si falla el envío del correo, se ignora el error (el reporte igual se guarda)
        }

        // Redirecciona al formulario de reportes con un indicador de éxito
        header("Location: ../views/reportar_producto.php?success=1");
        exit;
    } else {
        // Si falla la inserción del reporte, redirecciona con un indicador de error
        header("Location: ../views/reportar_producto.php?error=1");
        exit;
    }

    // Cierra la sentencia preparada
    $stmt->close();
    // Cierra la conexión a la base de datos
    $conn->close();
}
?>
