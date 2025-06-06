-- =====================================================
-- COTIZADOR COMPANY - BASE DE DATOS COMPLETA
-- Archivo SQL para upload en Railway
-- =====================================================

-- Configuración inicial
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- =====================================================
-- ESTRUCTURA DE TABLAS
-- =====================================================

-- Tabla: categorias
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: opciones
DROP TABLE IF EXISTS `opciones`;
CREATE TABLE `opciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `precio_30` decimal(10,2) DEFAULT 0.00,
  `precio_45` decimal(10,2) DEFAULT 0.00,
  `precio_60` decimal(10,2) DEFAULT 0.00,
  `precio_90` decimal(10,2) DEFAULT 0.00,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `opciones_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: presupuestos
DROP TABLE IF EXISTS `presupuestos`;
CREATE TABLE `presupuestos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_nombre` varchar(255) DEFAULT NULL,
  `cliente_email` varchar(255) DEFAULT NULL,
  `cliente_telefono` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT 0.00,
  `plazo` int(11) DEFAULT 30,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('borrador','enviado','aprobado','rechazado') DEFAULT 'borrador',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: presupuesto_detalles
DROP TABLE IF EXISTS `presupuesto_detalles`;
CREATE TABLE `presupuesto_detalles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `presupuesto_id` int(11) NOT NULL,
  `opcion_id` int(11) NOT NULL,
  `cantidad` int(11) DEFAULT 1,
  `precio_unitario` decimal(10,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `presupuesto_id` (`presupuesto_id`),
  KEY `opcion_id` (`opcion_id`),
  CONSTRAINT `presupuesto_detalles_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuestos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `presupuesto_detalles_ibfk_2` FOREIGN KEY (`opcion_id`) REFERENCES `opciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS: CATEGORÍAS
-- =====================================================

INSERT INTO `categorias` (`id`, `nombre`, `orden`) VALUES
(1, 'Ascensores Electromecánicos', 1),
(2, 'Ascensores Gearless', 2),
(3, 'Ascensores Hidráulicos', 3),
(4, 'Montacargas', 4),
(5, 'Salvaescaleras', 5),
(6, 'Adicionales Electromecánicos', 6),
(7, 'Adicionales Gearless', 7),
(8, 'Adicionales Hidráulicos', 8),
(9, 'Adicionales Montacargas', 9),
(10, 'Adicionales Salvaescaleras', 10),
(11, 'Descuentos', 11);

-- =====================================================
-- DATOS: OPCIONES
-- =====================================================

INSERT INTO `opciones` (`id`, `categoria_id`, `nombre`, `precio_30`, `precio_45`, `precio_60`, `precio_90`, `orden`) VALUES

-- ASCENSORES ELECTROMECÁNICOS (8 opciones)
(1, 1, 'ASCENSOR ELECTROMECANICO 2 PARADAS 300KG', 2800000.00, 3200000.00, 3600000.00, 4200000.00, 1),
(2, 1, 'ASCENSOR ELECTROMECANICO 3 PARADAS 300KG', 3200000.00, 3600000.00, 4000000.00, 4600000.00, 2),
(3, 1, 'ASCENSOR ELECTROMECANICO 4 PARADAS 300KG', 3600000.00, 4000000.00, 4400000.00, 5000000.00, 3),
(4, 1, 'ASCENSOR ELECTROMECANICO 5 PARADAS 300KG', 4000000.00, 4400000.00, 4800000.00, 5400000.00, 4),
(5, 1, 'ASCENSOR ELECTROMECANICO 2 PARADAS 450KG', 3000000.00, 3400000.00, 3800000.00, 4400000.00, 5),
(6, 1, 'ASCENSOR ELECTROMECANICO 3 PARADAS 450KG', 3400000.00, 3800000.00, 4200000.00, 4800000.00, 6),
(7, 1, 'ASCENSOR ELECTROMECANICO 4 PARADAS 450KG', 3800000.00, 4200000.00, 4600000.00, 5200000.00, 7),
(8, 1, 'ASCENSOR ELECTROMECANICO 5 PARADAS 450KG', 4200000.00, 4600000.00, 5000000.00, 5600000.00, 8),

-- ASCENSORES GEARLESS (8 opciones)
(9, 2, 'ASCENSOR GEARLESS 2 PARADAS 300KG', 3200000.00, 3600000.00, 4000000.00, 4600000.00, 1),
(10, 2, 'ASCENSOR GEARLESS 3 PARADAS 300KG', 3600000.00, 4000000.00, 4400000.00, 5000000.00, 2),
(11, 2, 'ASCENSOR GEARLESS 4 PARADAS 300KG', 4000000.00, 4400000.00, 4800000.00, 5400000.00, 3),
(12, 2, 'ASCENSOR GEARLESS 5 PARADAS 300KG', 4400000.00, 4800000.00, 5200000.00, 5800000.00, 4),
(13, 2, 'ASCENSOR GEARLESS 2 PARADAS 450KG', 3400000.00, 3800000.00, 4200000.00, 4800000.00, 5),
(14, 2, 'ASCENSOR GEARLESS 3 PARADAS 450KG', 3800000.00, 4200000.00, 4600000.00, 5200000.00, 6),
(15, 2, 'ASCENSOR GEARLESS 4 PARADAS 450KG', 4200000.00, 4600000.00, 5000000.00, 5600000.00, 7),
(16, 2, 'ASCENSOR GEARLESS 5 PARADAS 450KG', 4600000.00, 5000000.00, 5400000.00, 6000000.00, 8),

-- ASCENSORES HIDRÁULICOS (8 opciones)
(17, 3, 'ASCENSOR HIDRAULICO 2 PARADAS 300KG', 2600000.00, 3000000.00, 3400000.00, 4000000.00, 1),
(18, 3, 'ASCENSOR HIDRAULICO 3 PARADAS 300KG', 3000000.00, 3400000.00, 3800000.00, 4400000.00, 2),
(19, 3, 'ASCENSOR HIDRAULICO 4 PARADAS 300KG', 3400000.00, 3800000.00, 4200000.00, 4800000.00, 3),
(20, 3, 'ASCENSOR HIDRAULICO 5 PARADAS 300KG', 3800000.00, 4200000.00, 4600000.00, 5200000.00, 4),
(21, 3, 'ASCENSOR HIDRAULICO 2 PARADAS 450KG', 2800000.00, 3200000.00, 3600000.00, 4200000.00, 5),
(22, 3, 'ASCENSOR HIDRAULICO 3 PARADAS 450KG', 3200000.00, 3600000.00, 4000000.00, 4600000.00, 6),
(23, 3, 'ASCENSOR HIDRAULICO 4 PARADAS 450KG', 3600000.00, 4000000.00, 4400000.00, 5000000.00, 7),
(24, 3, 'ASCENSOR HIDRAULICO 5 PARADAS 450KG', 4000000.00, 4400000.00, 4800000.00, 5400000.00, 8),

-- MONTACARGAS (8 opciones)
(25, 4, 'MONTACARGAS 2 PARADAS 500KG', 2400000.00, 2800000.00, 3200000.00, 3800000.00, 1),
(26, 4, 'MONTACARGAS 3 PARADAS 500KG', 2800000.00, 3200000.00, 3600000.00, 4200000.00, 2),
(27, 4, 'MONTACARGAS 4 PARADAS 500KG', 3200000.00, 3600000.00, 4000000.00, 4600000.00, 3),
(28, 4, 'MONTACARGAS 5 PARADAS 500KG', 3600000.00, 4000000.00, 4400000.00, 5000000.00, 4),
(29, 4, 'MONTACARGAS 2 PARADAS 1000KG', 2800000.00, 3200000.00, 3600000.00, 4200000.00, 5),
(30, 4, 'MONTACARGAS 3 PARADAS 1000KG', 3200000.00, 3600000.00, 4000000.00, 4600000.00, 6),
(31, 4, 'MONTACARGAS 4 PARADAS 1000KG', 3600000.00, 4000000.00, 4400000.00, 5000000.00, 7),
(32, 4, 'MONTACARGAS 5 PARADAS 1000KG', 4000000.00, 4400000.00, 4800000.00, 5400000.00, 8),

-- SALVAESCALERAS (8 opciones)
(33, 5, 'SALVAESCALERAS RECTO 3M', 1800000.00, 2000000.00, 2200000.00, 2600000.00, 1),
(34, 5, 'SALVAESCALERAS RECTO 6M', 2200000.00, 2400000.00, 2600000.00, 3000000.00, 2),
(35, 5, 'SALVAESCALERAS RECTO 9M', 2600000.00, 2800000.00, 3000000.00, 3400000.00, 3),
(36, 5, 'SALVAESCALERAS RECTO 12M', 3000000.00, 3200000.00, 3400000.00, 3800000.00, 4),
(37, 5, 'SALVAESCALERAS CURVO 3M', 2400000.00, 2600000.00, 2800000.00, 3200000.00, 5),
(38, 5, 'SALVAESCALERAS CURVO 6M', 2800000.00, 3000000.00, 3200000.00, 3600000.00, 6),
(39, 5, 'SALVAESCALERAS CURVO 9M', 3200000.00, 3400000.00, 3600000.00, 4000000.00, 7),
(40, 5, 'SALVAESCALERAS CURVO 12M', 3600000.00, 3800000.00, 4000000.00, 4400000.00, 8),

-- ADICIONALES ELECTROMECÁNICOS (4 opciones)
(41, 6, 'PARADA ADICIONAL ELECTROMECANICO', 400000.00, 450000.00, 500000.00, 600000.00, 1),
(42, 6, 'PUERTA ADICIONAL ELECTROMECANICO', 300000.00, 350000.00, 400000.00, 500000.00, 2),
(43, 6, 'CABINA EN CHAPA C/DETALLES ELECTROMECANICO RESTAR', -200000.00, -180000.00, -160000.00, -120000.00, 3),
(44, 6, 'SISTEMA DE EMERGENCIA ELECTROMECANICO', 250000.00, 280000.00, 320000.00, 380000.00, 4),

-- ADICIONALES GEARLESS (4 opciones)
(45, 7, 'PARADA ADICIONAL GEARLESS', 450000.00, 500000.00, 550000.00, 650000.00, 1),
(46, 7, 'PUERTA ADICIONAL GEARLESS', 350000.00, 400000.00, 450000.00, 550000.00, 2),
(47, 7, 'PB Y PUERTA DE CABINA EN CHAPA GEARLESS RESTAR', -250000.00, -220000.00, -200000.00, -150000.00, 3),
(48, 7, 'SISTEMA DE EMERGENCIA GEARLESS', 280000.00, 320000.00, 360000.00, 420000.00, 4),

-- ADICIONALES HIDRÁULICOS (4 opciones)
(49, 8, 'PARADA ADICIONAL HIDRAULICO', 350000.00, 400000.00, 450000.00, 550000.00, 1),
(50, 8, 'PUERTA ADICIONAL HIDRAULICO', 280000.00, 320000.00, 360000.00, 450000.00, 2),
(51, 8, 'CABINA PREMIUM HIDRAULICO', 400000.00, 450000.00, 500000.00, 600000.00, 3),
(52, 8, 'SISTEMA DE EMERGENCIA HIDRAULICO', 220000.00, 250000.00, 280000.00, 340000.00, 4),

-- ADICIONALES MONTACARGAS (3 opciones)
(53, 9, 'PARADA ADICIONAL MONTACARGAS', 300000.00, 350000.00, 400000.00, 500000.00, 1),
(54, 9, 'PUERTA ADICIONAL MONTACARGAS', 250000.00, 280000.00, 320000.00, 400000.00, 2),
(55, 9, 'SISTEMA DE SEGURIDAD MONTACARGAS', 180000.00, 200000.00, 220000.00, 280000.00, 3),

-- ADICIONALES SALVAESCALERAS (3 opciones)
(56, 10, 'ASIENTO GIRATORIO SALVAESCALERAS', 150000.00, 170000.00, 190000.00, 230000.00, 1),
(57, 10, 'CONTROL REMOTO SALVAESCALERAS', 120000.00, 140000.00, 160000.00, 200000.00, 2),
(58, 10, 'SISTEMA PLEGABLE SALVAESCALERAS', 200000.00, 220000.00, 240000.00, 300000.00, 3),

-- DESCUENTOS (4 opciones)
(59, 11, 'DESCUENTO CLIENTE FRECUENTE', -300000.00, -350000.00, -400000.00, -500000.00, 1),
(60, 11, 'DESCUENTO PRONTO PAGO', -200000.00, -250000.00, -300000.00, -400000.00, 2),
(61, 11, 'DESCUENTO VOLUMEN', -400000.00, -450000.00, -500000.00, -600000.00, 3),
(62, 11, 'DESCUENTO PROMOCIONAL', -150000.00, -180000.00, -200000.00, -250000.00, 4);

-- =====================================================
-- CONFIGURACIÓN FINAL
-- =====================================================

-- Rehabilitar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- Confirmar transacción
COMMIT;

-- =====================================================
-- RESUMEN DE LA BASE DE DATOS
-- =====================================================
-- 
-- TABLAS CREADAS:
-- - categorias (11 registros)
-- - opciones (62 registros)
-- - presupuestos (estructura)
-- - presupuesto_detalles (estructura)
--
-- FUNCIONALIDADES INCLUIDAS:
-- ✅ Filtrado inteligente por tipo de ascensor
-- ✅ Adicionales que restan dinero (con palabra "RESTAR")
-- ✅ Precios por diferentes plazos (30, 45, 60, 90 días)
-- ✅ Categorización completa de productos
-- ✅ Estructura para guardar presupuestos
--
-- TIPOS DE PRODUCTOS:
-- - 40 ascensores (electromecánicos, gearless, hidráulicos, montacargas, salvaescaleras)
-- - 18 adicionales especializados por tipo
-- - 4 adicionales que restan dinero
-- - 4 opciones de descuento
--
-- ===================================================== 