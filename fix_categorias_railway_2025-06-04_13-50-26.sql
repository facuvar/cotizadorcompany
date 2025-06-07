-- ========================================
-- FIX CATEGORÍAS RAILWAY - SOLO CATEGORIAS
-- Fecha: 2025-06-04 13:50:26
-- ========================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- PASO 1: Eliminar tabla categorias
DROP TABLE IF EXISTS `categorias`;

-- PASO 2: Crear estructura tabla categorias
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PASO 3: Insertar datos en categorias
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
('1', 'ASCENSORES', 'Equipos electromecánicos de ascensores', '2', '1', '2025-05-28 18:56:56', '2025-06-02 12:30:00'),
('2', 'ADICIONALES', 'Opciones adicionales para ascensores', '1', '1', '2025-05-28 18:56:56', '2025-06-02 12:30:00'),
('3', 'DESCUENTOS', 'Formas de pago y descuentos', '3', '1', '2025-05-28 18:56:56', '2025-05-28 18:56:56');

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- TABLA CATEGORIAS CREADA EXITOSAMENTE
-- Registros: 3
-- ========================================
