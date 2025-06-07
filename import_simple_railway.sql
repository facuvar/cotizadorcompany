-- Script simple para Railway
-- Ejecutar línea por línea o en bloques pequeños

-- 1. Configuración inicial
SET FOREIGN_KEY_CHECKS = 0;

-- 2. Eliminar tablas si existen
DROP TABLE IF EXISTS opciones;
DROP TABLE IF EXISTS categorias;

-- 3. Crear tabla categorias
CREATE TABLE categorias (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(100) NOT NULL,
  descripcion text DEFAULT NULL,
  orden int(11) DEFAULT 0,
  activo tinyint(1) DEFAULT 1,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Insertar categorías
INSERT INTO categorias (id, nombre, descripcion, orden, activo, created_at, updated_at) VALUES
(1, 'ASCENSORES', 'Equipos electromecánicos de ascensores', 2, 1, '2025-05-28 18:56:56', '2025-06-02 12:30:00'),
(2, 'ADICIONALES', 'Opciones adicionales para ascensores', 1, 1, '2025-05-28 18:56:56', '2025-06-02 12:30:00'),
(3, 'DESCUENTOS', 'Formas de pago y descuentos', 3, 1, '2025-05-28 18:56:56', '2025-05-28 18:56:56');

-- 5. Crear tabla opciones
CREATE TABLE opciones (
  id int(11) NOT NULL AUTO_INCREMENT,
  categoria_id int(11) NOT NULL,
  nombre varchar(255) NOT NULL,
  descripcion text DEFAULT NULL,
  precio decimal(10,2) DEFAULT 0.00,
  precio_90_dias decimal(10,2) DEFAULT 0.00,
  precio_160_dias decimal(10,2) DEFAULT 0.00,
  precio_270_dias decimal(10,2) DEFAULT 0.00,
  descuento decimal(5,2) DEFAULT 0.00,
  orden int(11) DEFAULT 0,
  activo tinyint(1) DEFAULT 1,
  es_titulo tinyint(1) DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY categoria_id (categoria_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Insertar algunas opciones básicas (las más importantes)
INSERT INTO opciones (id, categoria_id, nombre, precio_90_dias, precio_160_dias, precio_270_dias, orden, activo) VALUES
(484, 1, 'Equipo Electromecanico 450kg carga util - 4 Paradas', 44417214.27, 34167087.90, 30750379.11, 1, 1),
(485, 1, 'Equipo Electromecanico 450kg carga util - 5 Paradas', 45736546.28, 35181958.68, 31663762.81, 2, 1),
(486, 1, 'Equipo Electromecanico 450kg carga util - 6 Paradas', 47078189.73, 36213992.10, 32592592.89, 3, 1),
(410, 2, 'Ascensores Electromecanicos Adicional Acceso Cabina en acero', 1679329.08, 1291791.60, 1162612.44, 7, 1),
(412, 2, 'Ascensores Electromecanicos Adicional Lateral Panoramico', 538859.18, 414507.06, 373056.35, 9, 1),
(468, 3, 'Efectivo', 0.00, 0.00, 0.00, 1, 1),
(482, 3, 'Transferencia', 0.00, 0.00, 0.00, 2, 1);

-- 7. Finalizar
SET FOREIGN_KEY_CHECKS = 1;
COMMIT; 