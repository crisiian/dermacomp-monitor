-- ESQUEMA DE BASE DE DATOS PARA DERMACOMP (InfinityFree)

-- 1. Tabla de Productos Principales
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    marca VARCHAR(100),
    sku VARCHAR(100) UNIQUE, -- El identificador único del producto
    imagen VARCHAR(500),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabla de Historial de Precios
CREATE TABLE IF NOT EXISTS precios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT,
    tienda VARCHAR(100), -- Ejemplo: 'Medipiel', 'Bella Piel'
    precio DECIMAL(15, 2) NOT NULL,
    precio_oferta DECIMAL(15, 2), -- Precio antes de descuento
    url VARCHAR(1000), -- Link directo para comprar
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE
);

-- 3. Tabla de Configuración (Margen de ganancia, Token de Telegram, etc.)
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE,
    valor VARCHAR(255)
);

-- Insertar configuración inicial (Ejemplo de margen de ganancia del 20%)
INSERT IGNORE INTO configuracion (clave, valor) VALUES ('margen_ganancia', '20');
