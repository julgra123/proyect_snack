CREATE DATABASE IF NOT EXISTS sistema_snacks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_snacks;

-- Esquema del sistema. No elimina tablas ni datos existentes.
-- Ejecutar solamente si necesitas crear las tablas que todavía no existen.

CREATE TABLE IF NOT EXISTS usuarios (
 id INT AUTO_INCREMENT PRIMARY KEY,
 usuario VARCHAR(50) NOT NULL UNIQUE,
 clave VARCHAR(255) NOT NULL,
 nombre VARCHAR(100) NOT NULL,
 rol ENUM('admin','empleado') NOT NULL,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS productos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 cod_producto VARCHAR(20) UNIQUE,
 nomb_producto VARCHAR(100) NOT NULL,
 precio DECIMAL(10,2) NOT NULL DEFAULT 0,
 imagen VARCHAR(255) DEFAULT NULL,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventario (
 id INT AUTO_INCREMENT PRIMARY KEY,
 producto_id INT NOT NULL UNIQUE,
 cantidad INT NOT NULL DEFAULT 0,
 fec_ingreso_producto DATE DEFAULT NULL,
 actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ventas (
 id INT AUTO_INCREMENT PRIMARY KEY,
 cod_ventas VARCHAR(30) UNIQUE,
 usuario_id INT NOT NULL,
 fec_venta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 total DECIMAL(10,2) NOT NULL,
 FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS detalle_venta (
 id INT AUTO_INCREMENT PRIMARY KEY,
 venta_id INT NOT NULL,
 producto_id INT NOT NULL,
 cantidad INT NOT NULL,
 precio_unitario DECIMAL(10,2) NOT NULL,
 subtotal DECIMAL(10,2) NOT NULL,
 FOREIGN KEY (venta_id) REFERENCES ventas(id),
 FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;
