<?php
class Usuario {
    public $id;            // Identificador único del usuario
    public $nombre;        // Nombre del usuario
    public $correo;        // Correo electrónico del usuario
    public $contraseña;    // Contraseña del usuario (guardada hasheada)
    public $rol;           // Rol del usuario (por ejemplo: comprador, vendedor)
    public $fecha_creacion; // Fecha en que fue creado el usuario

    // CREATE: Método estático para crear un nuevo usuario en la base de datos
    public static function crear($db, $nombre, $correo, $contraseña, $rol) {
        // Preparar la consulta SQL para insertar un nuevo usuario con contraseña hasheada
        $stmt = $db->prepare("INSERT INTO usuarios (nombre, correo, contraseña, rol) VALUES (?, ?, ?, ?)");
        // Ejecutar la consulta con los valores proporcionados
        return $stmt->execute([$nombre, $correo, password_hash($contraseña, PASSWORD_BCRYPT), $rol]);
    }

    // READ: Método estático para obtener un usuario por su ID
    public static function obtenerPorId($db, $id) {
        // Preparar la consulta SQL para seleccionar usuario por ID
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        // Devolver el resultado como un objeto
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // UPDATE: Método estático para actualizar el correo de un usuario
    public static function actualizarCorreo($db, $id, $nuevoCorreo) {
        // Preparar la consulta SQL para actualizar el correo del usuario por su ID
        $stmt = $db->prepare("UPDATE usuarios SET correo = ? WHERE id = ?");
        // Ejecutar la consulta con el nuevo correo y el ID correspondiente
        return $stmt->execute([$nuevoCorreo, $id]);
    }

    // DELETE: Método estático para eliminar un usuario por su ID
    public static function eliminar($db, $id) {
        // Preparar la consulta SQL para eliminar el usuario
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
        // Ejecutar la consulta con el ID del usuario a eliminar
        return $stmt->execute([$id]);
    }
}
?>
