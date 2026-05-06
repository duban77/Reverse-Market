-- ═══════════════════════════════════════════════════════════════
-- REVERSE MARKET — Script de actualización de base de datos
-- Ejecuta este script en phpMyAdmin → Base de datos reverse_market → Importar
-- ═══════════════════════════════════════════════════════════════

USE reverse_market;

-- Agregar columnas faltantes si no existen
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS estado ENUM('activo','bloqueado') DEFAULT 'activo';
ALTER TABLE notificaciones ADD COLUMN IF NOT EXISTS tipo ENUM('oferta','mensaje','sistema') DEFAULT 'mensaje';
ALTER TABLE notificaciones ADD COLUMN IF NOT EXISTS id_usuario_destino INT NULL;
ALTER TABLE reportes ADD COLUMN IF NOT EXISTS usuario_id INT NULL;

-- Carrito
CREATE TABLE IF NOT EXISTS carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_comprador INT NOT NULL,
    id_producto  INT NOT NULL,
    cantidad     INT NOT NULL DEFAULT 1,
    fecha        DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_item (id_comprador, id_producto),
    FOREIGN KEY (id_comprador) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto)  REFERENCES productos(id) ON DELETE CASCADE
);

-- Chat
CREATE TABLE IF NOT EXISTS chat_mensajes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    id_emisor   INT NOT NULL,
    id_receptor INT NOT NULL,
    mensaje     TEXT NOT NULL,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    leido       BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_emisor)   REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_receptor) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Transacciones
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

-- NOTA: Para crear el admin usa el archivo crear_admin.php
-- Colócalo en htdocs/reverse_market/ y abre:
-- http://localhost/reverse_market/crear_admin.php

SELECT '✅ Base de datos actualizada correctamente' AS resultado;

-- Allow NULL in transacciones for safe product/user deletion
ALTER TABLE transacciones MODIFY COLUMN producto_id INT NULL;
ALTER TABLE transacciones MODIFY COLUMN id_comprador INT NULL;
ALTER TABLE transacciones MODIFY COLUMN id_vendedor INT NULL;

-- Add estado to reportes if missing
ALTER TABLE reportes ADD COLUMN IF NOT EXISTS estado ENUM('pendiente','revisado','resuelto') DEFAULT 'pendiente';

SELECT '✅ Actualización completada' AS resultado;

-- Add id_vendedor to ofertas if missing
ALTER TABLE ofertas ADD COLUMN IF NOT EXISTS id_vendedor INT NULL;
ALTER TABLE ofertas ADD COLUMN IF NOT EXISTS mensaje TEXT NULL;
ALTER TABLE solicitudes ADD COLUMN IF NOT EXISTS descripcion VARCHAR(500) NULL;
ALTER TABLE solicitudes ADD COLUMN IF NOT EXISTS titulo VARCHAR(255) NULL;

-- Tabla para ofertas directas sobre necesidades (con negociación)
CREATE TABLE IF NOT EXISTS oferta_necesidad (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    id_necesidad    INT NOT NULL,
    id_vendedor     INT NOT NULL,
    id_comprador    INT NOT NULL,
    precio          DECIMAL(10,2) NOT NULL,
    mensaje         TEXT,
    estado          ENUM('pendiente','aceptada','rechazada','negociando') DEFAULT 'pendiente',
    fecha           DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_necesidad)  REFERENCES necesidades(id) ON DELETE CASCADE,
    FOREIGN KEY (id_vendedor)   REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_comprador)  REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla para mensajes de negociación dentro de una oferta
CREATE TABLE IF NOT EXISTS negociacion_mensajes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    id_oferta       INT NOT NULL,
    id_emisor       INT NOT NULL,
    mensaje         TEXT NOT NULL,
    precio_propuesto DECIMAL(10,2) NULL,
    fecha           DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_oferta)  REFERENCES oferta_necesidad(id) ON DELETE CASCADE,
    FOREIGN KEY (id_emisor)  REFERENCES usuarios(id) ON DELETE CASCADE
);
