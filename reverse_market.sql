-- =====================================================
-- BASE DE DATOS REVERSE_MARKET - VERSION COMPLETA
-- =====================================================

CREATE DATABASE IF NOT EXISTS reverse_market;
USE reverse_market;

-- Tabla: usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    correo          VARCHAR(100) UNIQUE NOT NULL,
    contraseña      VARCHAR(255) NOT NULL,
    rol             ENUM('comprador','vendedor','admin') NOT NULL,
    estado          ENUM('activo','bloqueado','inactivo') DEFAULT 'activo',
    fecha_creacion  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: perfiles
CREATE TABLE IF NOT EXISTS perfiles (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    id_vendedor           INT NOT NULL,
    nombre                VARCHAR(255) NOT NULL,
    telefono              VARCHAR(50)  NOT NULL,
    direccion             VARCHAR(255) NOT NULL,
    descripcion           TEXT,
    calificacion_promedio FLOAT DEFAULT NULL,
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla: productos
CREATE TABLE IF NOT EXISTS productos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    id_vendedor     INT NOT NULL,
    nombre          VARCHAR(255) NOT NULL,
    descripcion     TEXT,
    precio          DECIMAL(10,2) NOT NULL,
    categoria       VARCHAR(100),
    fecha_creacion  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    imagen          VARCHAR(255),
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla: necesidades
CREATE TABLE IF NOT EXISTS necesidades (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    titulo          VARCHAR(255) NOT NULL,
    descripcion     TEXT NOT NULL,
    id_comprador    INT NOT NULL,
    estado          ENUM('abierta','cerrada') DEFAULT 'abierta',
    fecha_creacion  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_comprador) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla: respuesta_necesidad
CREATE TABLE IF NOT EXISTS respuesta_necesidad (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    id_necesidad    INT NOT NULL,
    id_producto     INT NOT NULL,
    FOREIGN KEY (id_necesidad) REFERENCES necesidades(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE
);

-- Tabla: ofertas
CREATE TABLE IF NOT EXISTS ofertas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    id_producto     INT NOT NULL,
    id_comprador    INT NOT NULL,
    precio_oferta   DECIMAL(10,2) NOT NULL,
    fecha_oferta    DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado          ENUM('pendiente','aceptada','rechazada') DEFAULT 'pendiente',
    FOREIGN KEY (id_producto)  REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_comprador) REFERENCES usuarios(id)  ON DELETE CASCADE
);

-- Tabla: solicitudes
CREATE TABLE IF NOT EXISTS solicitudes (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    precio            DECIMAL(10,2) NOT NULL,
    tiempo_entrega    INT NOT NULL,
    condiciones       TEXT,
    id_comprador      INT NOT NULL,
    id_vendedor       INT DEFAULT NULL,
    estado            ENUM('activa','cerrada','comprada') DEFAULT 'activa',
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_comprador) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_vendedor)  REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla: transacciones
CREATE TABLE IF NOT EXISTS transacciones (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    id_comprador INT NOT NULL,
    id_vendedor  INT NOT NULL,
    producto_id  INT NOT NULL,
    monto        DECIMAL(10,2) NOT NULL,
    fecha        DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado       ENUM('pendiente','completada','cancelada') DEFAULT 'pendiente',
    FOREIGN KEY (id_comprador) REFERENCES usuarios(id),
    FOREIGN KEY (id_vendedor)  REFERENCES usuarios(id),
    FOREIGN KEY (producto_id)  REFERENCES productos(id)
);

-- Tabla: calificaciones
CREATE TABLE IF NOT EXISTS calificaciones (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    id_comprador    INT,
    id_vendedor     INT,
    id_producto     INT,
    puntuacion      TINYINT CHECK (puntuacion BETWEEN 1 AND 5),
    comentario      TEXT,
    fecha           DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_comprador) REFERENCES usuarios(id),
    FOREIGN KEY (id_vendedor)  REFERENCES usuarios(id),
    FOREIGN KEY (id_producto)  REFERENCES productos(id)
);

-- Tabla: notificaciones
CREATE TABLE IF NOT EXISTS notificaciones (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    mensaje            TEXT,
    fecha              DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_usuario_destino INT,
    leido              BOOLEAN DEFAULT FALSE,
    tipo               ENUM('oferta','mensaje','sistema') DEFAULT 'mensaje',
    FOREIGN KEY (id_usuario_destino) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla: medios_pago
CREATE TABLE IF NOT EXISTS medios_pago (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    id_vendedor    INT NOT NULL,
    metodo_pago    VARCHAR(50) NOT NULL,
    numero_cuenta  VARCHAR(100) NOT NULL,
    descripcion    TEXT,
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla: mensajes_contacto
CREATE TABLE IF NOT EXISTS mensajes_contacto (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    nombre   VARCHAR(100) NOT NULL,
    email    VARCHAR(100) NOT NULL,
    mensaje  TEXT NOT NULL,
    fecha    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: reportes
CREATE TABLE IF NOT EXISTS reportes (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    producto_id    INT NOT NULL,
    usuario_id     INT,
    motivo         TEXT NOT NULL,
    estado         ENUM('pendiente','revisado','resuelto') DEFAULT 'pendiente',
    fecha_reporte  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE SET NULL
);

-- Tabla: chat_mensajes
CREATE TABLE IF NOT EXISTS chat_mensajes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    id_emisor    INT NOT NULL,
    id_receptor  INT NOT NULL,
    mensaje      TEXT NOT NULL,
    leido        BOOLEAN DEFAULT FALSE,
    fecha        DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_emisor)   REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_receptor) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla: recuperacion_password
CREATE TABLE IF NOT EXISTS recuperacion_password (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    token      VARCHAR(64) NOT NULL,
    expira     DATETIME NOT NULL,
    usado      BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla auditoria
CREATE TABLE IF NOT EXISTS usuarios_log (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT,
    nombre      VARCHAR(100),
    correo      VARCHAR(100),
    rol         ENUM('comprador','vendedor','admin'),
    accion      ENUM('DELETE','BLOCK','UNBLOCK') NOT NULL,
    fecha       DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Trigger de auditoría
DELIMITER //
DROP TRIGGER IF EXISTS trg_usuarios_after_delete //
CREATE TRIGGER trg_usuarios_after_delete
AFTER DELETE ON usuarios
FOR EACH ROW
BEGIN
    INSERT INTO usuarios_log(usuario_id, nombre, correo, rol, accion)
    VALUES (OLD.id, OLD.nombre, OLD.correo, OLD.rol, 'DELETE');
END //
DELIMITER ;

-- Usuario admin por defecto (password: admin1234)
INSERT IGNORE INTO usuarios (nombre, correo, contraseña, rol, estado) VALUES
('Administrador', 'admin@reversemarket.com', '$2y$10$YkH2R1wR4pZ5v6X3qM9SOetdGBNrCmQlP4JlFo7g3t8HfnKWxI2Ky', 'admin', 'activo');

-- Tabla: carrito
CREATE TABLE IF NOT EXISTS carrito (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    id_comprador INT NOT NULL,
    id_producto  INT NOT NULL,
    cantidad     INT NOT NULL DEFAULT 1,
    fecha        DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_item (id_comprador, id_producto),
    FOREIGN KEY (id_comprador) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto)  REFERENCES productos(id) ON DELETE CASCADE
);
