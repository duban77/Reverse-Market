<?php
class User {
    private $db; // Propiedad privada para la conexión a la base de datos

    // Constructor que recibe la conexión a la base de datos y la guarda en la propiedad $db
    public function __construct($database) {
        $this->db = $database;
    }

    // Método para registrar un nuevo usuario
    public function register($name, $email, $password, $role) {
        // Hashear la contraseña para mayor seguridad antes de guardarla
        $hash = password_hash($password, PASSWORD_DEFAULT);
        // Consulta SQL para insertar un nuevo usuario en la tabla 'users'
        $query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        // Preparar la consulta para evitar inyección SQL
        $stmt = $this->db->prepare($query);
        // Ejecutar la consulta con los valores proporcionados y retornar resultado (true/false)
        return $stmt->execute([$name, $email, $hash, $role]);
    }

    // Método para autenticar (login) un usuario
    public function login($email, $password) {
        // Preparar consulta para buscar un usuario por email
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        // Obtener el usuario encontrado (si existe)
        $user = $stmt->fetch();
        // Verificar si el usuario existe y si la contraseña coincide con el hash almacenado
        if ($user && password_verify($password, $user['password'])) {
            // Si la contraseña es correcta, retornar la información del usuario
            return $user;
        }
        // Si no, retornar false
        return false;
    }
}
?>
