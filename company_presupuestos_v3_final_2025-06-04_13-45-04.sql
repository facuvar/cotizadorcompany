-- ========================================
-- EXPORTACIÓN FINAL DE BASE DE DATOS V3
-- Base: company_presupuestos
-- Fecha: 2025-06-04 13:45:04
-- Versión final optimizada para Railway
-- ========================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

-- ========================================
-- PASO 1: ELIMINAR TABLAS EXISTENTES
-- ========================================

DROP TABLE IF EXISTS `presupuesto_items`;
DROP TABLE IF EXISTS `presupuesto_detalles`;
DROP TABLE IF EXISTS `presupuestos`;
DROP TABLE IF EXISTS `opciones`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `configuracion`;
DROP TABLE IF EXISTS `plazos_entrega`;
DROP TABLE IF EXISTS `categorias`;

-- ========================================
-- PASO 2: CREAR ESTRUCTURA DE TABLAS
-- ========================================

-- Estructura para tabla categorias
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

-- Estructura para tabla plazos_entrega
CREATE TABLE `plazos_entrega` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `dias` int(11) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura para tabla configuracion
CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('text','number','boolean','json') DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura para tabla usuarios
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `rol` enum('admin','usuario') DEFAULT 'usuario',
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura para tabla opciones
CREATE TABLE `opciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT 0.00,
  `precio_90_dias` decimal(10,2) DEFAULT 0.00,
  `precio_160_dias` decimal(10,2) DEFAULT 0.00,
  `precio_270_dias` decimal(10,2) DEFAULT 0.00,
  `descuento` decimal(5,2) DEFAULT 0.00,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `es_titulo` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `opciones_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=542 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura para tabla presupuestos
CREATE TABLE `presupuestos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_presupuesto` varchar(50) NOT NULL,
  `cliente_nombre` varchar(255) NOT NULL,
  `cliente_email` varchar(255) DEFAULT NULL,
  `cliente_telefono` varchar(50) DEFAULT NULL,
  `cliente_empresa` varchar(255) DEFAULT NULL,
  `plazo_entrega_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `descuento_porcentaje` decimal(5,2) DEFAULT 0.00,
  `descuento_monto` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) DEFAULT 0.00,
  `plazo_entrega` varchar(10) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('borrador','enviado','aprobado','rechazado') DEFAULT 'borrador',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_presupuesto` (`numero_presupuesto`),
  KEY `plazo_entrega_id` (`plazo_entrega_id`),
  CONSTRAINT `presupuestos_ibfk_1` FOREIGN KEY (`plazo_entrega_id`) REFERENCES `plazos_entrega` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura para tabla presupuesto_detalles
CREATE TABLE `presupuesto_detalles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `presupuesto_id` int(11) NOT NULL,
  `opcion_id` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `presupuesto_id` (`presupuesto_id`),
  KEY `opcion_id` (`opcion_id`),
  CONSTRAINT `presupuesto_detalles_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuestos` (`id`),
  CONSTRAINT `presupuesto_detalles_ibfk_2` FOREIGN KEY (`opcion_id`) REFERENCES `opciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura para tabla presupuesto_items
CREATE TABLE `presupuesto_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `presupuesto_id` int(11) NOT NULL,
  `opcion_id` int(11) NOT NULL,
  `cantidad` int(11) DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `presupuesto_id` (`presupuesto_id`),
  KEY `opcion_id` (`opcion_id`),
  CONSTRAINT `presupuesto_items_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuestos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `presupuesto_items_ibfk_2` FOREIGN KEY (`opcion_id`) REFERENCES `opciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- PASO 3: INSERTAR DATOS
-- ========================================

-- Datos para tabla categorias (3 registros)
-- Lote 1 de 1 para tabla categorias
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
('1', 'ASCENSORES', 'Equipos electromecánicos de ascensores', '2', '1', '2025-05-28 18:56:56', '2025-06-02 12:30:00'),
('2', 'ADICIONALES', 'Opciones adicionales para ascensores', '1', '1', '2025-05-28 18:56:56', '2025-06-02 12:30:00'),
('3', 'DESCUENTOS', 'Formas de pago y descuentos', '3', '1', '2025-05-28 18:56:56', '2025-05-28 18:56:56');

-- Datos para tabla plazos_entrega (3 registros)
-- Lote 1 de 1 para tabla plazos_entrega
INSERT INTO `plazos_entrega` (`id`, `nombre`, `dias`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
('1', '90 dias', '90', '1', '1', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('2', '160-180 dias', '170', '2', '1', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('3', '270 dias', '270', '3', '1', '2025-05-28 18:56:56', '2025-05-28 18:56:56');

-- Datos para tabla configuracion (8 registros)
-- Lote 1 de 1 para tabla configuracion
INSERT INTO `configuracion` (`id`, `nombre`, `valor`, `descripcion`, `tipo`, `created_at`, `updated_at`) VALUES
('1', 'titulo_sistema', 'Sistema de Presupuestos de Ascensores', 'Título del sistema', 'text', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('2', 'empresa_nombre', 'Tu Empresa', 'Nombre de la empresa', 'text', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('3', 'empresa_telefono', '+54 11 1234-5678', 'Teléfono de la empresa', 'text', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('4', 'empresa_email', 'info@tuempresa.com', 'Email de la empresa', 'text', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('5', 'empresa_direccion', 'Tu Dirección, Ciudad', 'Dirección de la empresa', 'text', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('6', 'moneda', 'ARS', 'Moneda del sistema', 'text', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('7', 'iva_porcentaje', '21', 'Porcentaje de IVA', 'number', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('8', 'validez_presupuesto', '30', 'Días de validez del presupuesto', 'number', '2025-05-28 18:56:56', '2025-05-28 18:56:56');

-- Datos para tabla usuarios (1 registros)
-- Lote 1 de 1 para tabla usuarios
INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `nombre`, `rol`, `activo`, `ultimo_acceso`, `created_at`, `updated_at`) VALUES
('1', 'admin', '$2y$10$2uIVLR3QCYelbTZgtD2j5uUWSZUVxXEPuw5.lCZd.Km75H/JVANH6', 'admin@tuempresa.com', 'Administrador', 'admin', '1', NULL, '2025-05-28 18:56:56', '2025-05-28 18:56:56');

-- Datos para tabla opciones (111 registros)
-- Lote 1 de 5 para tabla opciones
INSERT INTO `opciones` (`id`, `categoria_id`, `nombre`, `descripcion`, `precio`, `precio_90_dias`, `precio_160_dias`, `precio_270_dias`, `descuento`, `orden`, `activo`, `es_titulo`, `created_at`, `updated_at`) VALUES
('409', '2', 'Ascensores Electromecanicos Adicional 750kg Maquina', '637000', '2600.00', '3194919.00', '2457630.00', '2211867.00', '0.00', '1', '1', '0', '2025-05-29 10:08:53', '2025-06-03 09:09:33'),
('410', '2', 'Ascensores Electromecanicos Adicional Acceso Cabina en acero', '73500', '300.00', '1696292.00', '1304840.00', '1174356.00', '0.00', '5', '1', '0', '2025-05-29 10:08:53', '2025-06-03 09:15:09'),
('412', '2', 'Ascensores Electromecanicos Adicional Lateral Panoramico', '129850', '530.00', '544302.20', '418694.00', '376824.60', '0.00', '7', '1', '0', '2025-05-29 10:08:53', '2025-06-03 09:17:02'),
('413', '2', 'Ascensores Electromecanicos Adicional Cabina 2,25m3', '343000', '1400.00', '363490.40', '279608.00', '251647.20', '0.00', '2', '1', '0', '2025-05-29 10:08:53', '2025-06-03 09:12:20'),
('414', '2', 'Ascensores Electromecanicos Adicional Cabina 2,66', '75950', '310.00', '641234.10', '493257.00', '443931.30', '0.00', '4', '1', '0', '2025-05-29 10:08:53', '2025-06-03 09:14:23'),
('415', '2', 'Ascensores Electromecanicos Adicional Puertas de 900', '110250', '450.00', '579720.70', '445939.00', '401345.10', '0.00', '10', '1', '0', '2025-05-29 10:08:53', '2025-06-03 10:18:41'),
('416', '2', 'Ascensores Electromecanicos Adicional Tarjeta chip keypass', '75950', '310.00', '18639.40', '14338.00', '12904.20', '0.00', '16', '1', '0', '2025-05-29 10:08:53', '2025-06-03 11:50:33'),
('417', '2', 'Ascensores Electromecanicos Adicional Extension de panel cabina a 2,30', '110250', '450.00', '838823.70', '645249.00', '580724.10', '0.00', '26', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:00:52'),
('418', '2', 'Ascensores Hidraulicos adicional puertas de 1200', '117600', '480.00', '1432477.80', '1101906.00', '1289230.02', '0.00', '41', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:18:50'),
('419', '2', 'Ascensores Hidraulicos adicional restar cabina en chapa', '235200', '960.00', '374674.30', '288211.00', '337206.87', '0.00', '35', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:14:20'),
('420', '2', 'Ascensores Hidraulicos adicional restar operador y dejar puerta plegadiza chapa', '294000', '1300.00', '665467.40', '511898.00', '598920.66', '0.00', '38', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:16:38'),
('421', '2', 'Ascensores Hidraulicos adicional puertas de 900', '1178450', '4810.00', '572990.60', '440762.00', '515691.54', '0.00', '39', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:17:25'),
('422', '2', 'Ascensores Hidraulicos adicional 750kg central y piston', '367500', '1500.00', '932027.20', '716944.00', '838824.48', '0.00', '29', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:08:56'),
('423', '2', 'Ascensores Hidraulicos adicional 1000kg central y piston', '171500', '700.00', '2264828.80', '1742176.00', '2038345.92', '0.00', '31', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:10:39'),
('426', '2', 'Montacargas Adicional puerta tijera - precio unitario', '98000', '400.00', '2170373.40', '1669518.00', '1953336.06', '0.00', '52', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:31:48'),
('428', '2', 'Ascensores Electromecanicos Adicional Balanza', '', '0.00', '1047598.50', '805845.00', '725260.50', '0.00', '23', '1', '0', '2025-05-29 10:08:53', '2025-06-03 11:57:19'),
('429', '2', 'Ascensores Electromecanicos Adicional Indicador LCD color 5\"', '', '0.00', '311247.30', '239421.00', '215478.90', '0.00', '22', '1', '0', '2025-05-29 10:08:53', '2025-06-03 11:56:35'),
('430', '2', 'Ascensores Electromecanicos Adicional Indicador LED alfa num 0, 8', '', '0.00', '76424.40', '58788.00', '52909.20', '0.00', '21', '1', '0', '2025-05-29 10:08:53', '2025-06-03 11:55:47'),
('431', '2', 'Ascensores Electromecanicos Adicional Puerta panoramica pisos', '', '0.00', '848144.70', '652419.00', '587177.10', '0.00', '15', '1', '0', '2025-05-29 10:08:53', '2025-06-03 10:24:40'),
('432', '2', 'Ascensores Electromecanicos Adicional Intercomunicador', '', '0.00', '838823.70', '645249.00', '580724.10', '0.00', '24', '1', '0', '2025-05-29 10:08:53', '2025-06-03 11:58:11'),
('433', '2', 'Ascensores Electromecanicos Adicional Sistema UPS', '', '0.00', '253510.40', '195008.00', '175507.20', '0.00', '19', '1', '0', '2025-05-29 10:08:53', '2025-06-03 11:54:02'),
('434', '2', 'Ascensores Electromecanicos Adicional Sistema keypass simple (un cod universal)', '', '0.00', '484654.30', '372811.00', '335529.90', '0.00', '18', '1', '0', '2025-05-29 10:08:53', '2025-06-03 11:52:04'),
('435', '2', 'Ascensores Electromecanicos Adicional Fase I/ fase II bomberios', '', '0.00', '419411.20', '322624.00', '290361.60', '0.00', '25', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:00:07'),
('436', '2', 'Ascensores Electromecanicos Adicional Puerta panoramica cabina + PB', NULL, '0.00', '1817286.90', '1397913.00', '1258121.70', '0.00', '14', '1', '1', '2025-05-29 10:08:53', '2025-06-03 10:23:57'),
('440', '2', 'Ascensores Electromecanicos Adicional Acero Pisos', '73500', '300.00', '374674.30', '288211.00', '259389.90', '0.00', '6', '1', '0', '2025-05-29 10:08:53', '2025-06-03 09:16:10');

-- Lote 2 de 5 para tabla opciones
INSERT INTO `opciones` (`id`, `categoria_id`, `nombre`, `descripcion`, `precio`, `precio_90_dias`, `precio_160_dias`, `precio_270_dias`, `descuento`, `orden`, `activo`, `es_titulo`, `created_at`, `updated_at`) VALUES
('442', '2', 'Ascensores Electromecanicos Adicional Cabina en chapa c/detalles restar', '129850', '530.00', '351679.90', '270523.00', '243470.70', '0.00', '8', '1', '0', '2025-05-29 10:08:53', '2025-06-03 10:16:51'),
('443', '2', 'Ascensores Electromecanicos Adicional Puertas de 1300', '75950', '310.00', '1453964.20', '1118434.00', '1006590.60', '0.00', '12', '1', '0', '2025-05-29 10:08:53', '2025-06-03 10:21:57'),
('444', '2', 'Ascensores Electromecanicos Adicional Puertas de 1000', '110250', '450.00', '1161305.60', '893312.00', '803980.80', '0.00', '11', '1', '0', '2025-05-29 10:08:53', '2025-06-03 10:19:46'),
('445', '2', 'Ascensores Hidraulicos adicional acceso en cabina en acero', '75950', '310.00', '1694426.50', '1303405.00', '1524983.85', '0.00', '43', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:22:05'),
('446', '2', 'Ascensores Hidraulicos adicional puerta panoramica pisos', '110250', '450.00', '848144.70', '652419.00', '763330.23', '0.00', '45', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:24:52'),
('447', '2', 'Ascensores Hidraulicos adicional tarjeta chip keypass', '507150', '2070.00', '18639.40', '14338.00', '16775.46', '0.00', '46', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:27:10'),
('448', '2', 'Ascensores Hidraulicos adicional puerta panoramica cabina + PB ', '134750', '550.00', '1817286.90', '1397913.00', '1635558.21', '0.00', '44', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:23:50'),
('449', '2', 'Ascensores Hidraulicos adicional puertas de 1800', '117600', '480.00', '1623473.80', '1248826.00', '1461126.42', '0.00', '42', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:19:45'),
('450', '2', 'Ascensores Hidraulicos adicional restar puerta cabina y pb a chapa', '235200', '960.00', '544303.50', '418695.00', '489873.15', '0.00', '36', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:15:09'),
('451', '2', 'Ascensores Hidraulicos adicional restar sin puertas ext x4', '294000', '1300.00', '2251780.70', '1732139.00', '2026602.63', '0.00', '37', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:15:55'),
('452', '2', 'Ascensores Hidraulicos adicional puertas de 1000', '1178450', '4810.00', '1144145.60', '880112.00', '1029731.04', '0.00', '40', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:18:08'),
('453', '2', 'Ascensores Electromecanicos Adicional 1000kg Maquina', '343000', '0.00', '3991286.00', '3070220.00', '2763198.00', '0.00', '3', '1', '0', '2025-05-29 10:08:53', '2025-06-03 09:13:31'),
('454', '2', 'Ascensores Hidraulicos adicional cabina 2,25m3', '367500', '1500.00', '363490.40', '279608.00', '327141.36', '0.00', '30', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:09:45'),
('455', '2', 'Ascensores Hidraulicos adicional cabina 2,66', '171500', '700.00', '642444.40', '494188.00', '578199.96', '0.00', '32', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:11:24'),
('457', '2', 'Montacargas Adicional puerta guillotina - precio unitario', '220500', '980.00', '2222530.00', '2222530.00', '2600360.10', '0.00', '51', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:31:01'),
('458', '2', 'Salvaescaleras adicional en acero', '98000', '400.00', '2846415.00', '2189550.00', '2561773.50', '0.00', '53', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:32:34'),
('460', '2', 'Ascensores Electromecanicos Adicional Puertas de 1800', NULL, '0.00', '1645961.20', '1266124.00', '1139511.60', '0.00', '13', '1', '1', '2025-05-29 10:08:53', '2025-06-03 10:23:11'),
('461', '2', 'Ascensores Hidraulicos adicional 2 tramos', '', '0.00', '1519206.00', '1168620.00', '1367285.40', '0.00', '28', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:07:47'),
('462', '2', 'Ascensores Hidraulicos adicionalpiso en acero', '', '0.00', '374674.30', '288211.00', '337206.87', '0.00', '33', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:12:12'),
('463', '2', 'Ascensores Hidraulicos adicional sistema keypass completo (un cod por piso)', NULL, '0.00', '1099793.50', '845995.00', '989814.15', '0.00', '47', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:27:49'),
('464', '2', 'Ascensores Electromecanicos Adicional PB y puerta de cabina en chapa restar', '', '0.00', '510898.70', '392999.00', '353699.10', '0.00', '9', '1', '0', '2025-05-29 10:08:53', '2025-06-03 10:17:53'),
('465', '2', 'Ascensores Electromecanicos Adicional Parada adicional chapa (precio por cada una)', NULL, '0.00', '2110111.90', '1623163.00', '1460846.70', '0.00', '27', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:01:37'),
('468', '3', 'Efectivo X', NULL, '0.00', '0.00', '0.00', '0.00', '8.00', '1', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:33:28'),
('470', '3', 'Mejora de Presupuesto', NULL, '0.00', '0.00', '0.00', '0.00', '5.00', '6', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:36:08'),
('476', '2', 'Ascensores Hidraulicos adicional panoramico', NULL, '0.00', '545234.30', '419411.00', '490710.87', '0.00', '34', '1', '0', '2025-05-30 10:25:43', '2025-06-03 12:13:25');

-- Lote 3 de 5 para tabla opciones
INSERT INTO `opciones` (`id`, `categoria_id`, `nombre`, `descripcion`, `precio`, `precio_90_dias`, `precio_160_dias`, `precio_270_dias`, `descuento`, `orden`, `activo`, `es_titulo`, `created_at`, `updated_at`) VALUES
('477', '2', 'Ascensores Hidraulicos adicional sistema keypass simple (un cod universal)', NULL, '0.00', '484654.30', '372811.00', '436188.87', '0.00', '48', '1', '0', '2025-05-30 10:25:43', '2025-06-03 12:28:28'),
('478', '2', 'Ascensores Electromecanicos Adicional Indicador LED alfa num 1, 2', NULL, '0.00', '102521.90', '78863.00', '70976.70', '0.00', '20', '1', '0', '2025-05-30 10:25:43', '2025-06-03 11:55:00'),
('479', '2', 'Ascensores Electromecanicos Adicional Sistema keypass completo (un cod por piso)', NULL, '0.00', '1099793.50', '845995.00', '761395.50', '0.00', '17', '1', '0', '2025-05-30 10:25:43', '2025-06-03 11:51:19'),
('480', '2', 'Ascensores Hidraulicos adicional sistema UPS', NULL, '0.00', '253510.40', '195008.00', '228159.36', '0.00', '49', '1', '0', '2025-05-30 10:25:43', '2025-06-03 12:29:16'),
('481', '3', 'Cheques Electronicos(30-45) - financiacion en 6 cheques electronicos 0-15-30-45-60-90', NULL, '0.00', '0.00', '0.00', '0.00', '2.00', '3', '1', '0', '2025-05-30 10:25:43', '2025-06-03 12:35:01'),
('482', '3', 'Transferencia', NULL, '0.00', '0.00', '0.00', '0.00', '5.00', '2', '1', '0', '2025-05-30 10:25:43', '2025-06-03 12:33:57'),
('484', '1', 'Equipo Electromecanico 450kg carga util - 4 Paradas', NULL, '0.00', '44865873.00', '34512210.00', '31060989.00', '0.00', '1', '1', '0', '2025-05-30 12:59:11', '2025-06-02 12:45:23'),
('485', '1', 'Equipo Electromecanico 450kg carga util - 5 Paradas', NULL, '0.00', '46198531.60', '35537332.00', '31983598.80', '0.00', '2', '1', '0', '2025-05-30 13:03:02', '2025-06-02 13:02:02'),
('486', '1', 'Equipo Electromecanico 450kg carga util - 6 Paradas', NULL, '0.00', '47553727.00', '36579790.00', '32921811.00', '0.00', '3', '1', '0', '2025-05-30 13:09:18', '2025-06-02 13:02:15'),
('487', '1', 'Equipo Electromecanico 450kg carga util - 7 Paradas', NULL, '0.00', '50335821.90', '38719863.00', '34847876.70', '0.00', '4', '1', '0', '2025-05-30 14:32:22', '2025-06-02 14:07:17'),
('488', '1', 'Equipo Electromecanico 450kg carga util - 8 Paradas', NULL, '0.00', '50163733.10', '38587487.00', '34728738.30', '0.00', '5', '1', '0', '2025-05-30 14:33:08', '2025-06-02 14:07:19'),
('489', '1', 'Equipo Electromecanico 450kg carga util - 9 Paradas ', NULL, '0.00', '51483077.10', '39602367.00', '35642130.30', '0.00', '6', '1', '0', '2025-05-30 14:35:15', '2025-06-02 14:07:22'),
('490', '1', 'Equipo Electromecanico 450kg carga util - 10 Paradas', NULL, '0.00', '52848857.10', '40652967.00', '36587670.30', '0.00', '7', '1', '0', '2025-05-30 14:38:08', '2025-06-02 14:07:23'),
('492', '1', 'Equipo Electromecanico 450kg carga util - 11 Paradas ', NULL, '0.00', '54064401.30', '41588001.00', '37429200.90', '0.00', '8', '1', '0', '2025-05-30 14:43:17', '2025-06-02 14:07:25'),
('493', '1', 'Equipo Electromecanico 450kg carga util - 12 Paradas', NULL, '0.00', '55412425.90', '42624943.00', '38362448.70', '0.00', '9', '1', '0', '2025-05-30 15:00:59', '2025-06-02 14:07:27'),
('494', '1', 'Equipo Electromecanico 450kg carga util - 13 Paradas', NULL, '0.00', '56768645.70', '43668189.00', '39301370.10', '0.00', '10', '1', '0', '2025-05-30 15:02:11', '2025-06-02 14:07:32'),
('495', '1', 'Equipo Electromecanico 450kg carga util - 14 Paradas ', NULL, '0.00', '57988286.20', '44606374.00', '40145736.60', '0.00', '11', '1', '0', '2025-05-30 15:03:04', '2025-06-02 14:07:35'),
('496', '1', 'Equipo Electromecanico 450kg carga util - 15 Paradas ', NULL, '0.00', '59343141.00', '45648570.00', '41083713.00', '0.00', '12', '1', '0', '2025-05-30 15:03:50', '2025-06-02 14:07:38'),
('498', '1', 'Opción Gearless - 4 Paradas', NULL, '0.00', '50260873.00', '38662210.00', '34795989.00', '0.00', '13', '1', '0', '2025-05-30 15:05:59', '2025-06-02 14:07:38'),
('499', '1', 'Opción Gearless - 5 Paradas', NULL, '0.00', '51593531.60', '39687332.00', '35718598.80', '0.00', '14', '1', '0', '2025-05-30 15:50:18', '2025-06-02 14:10:42'),
('500', '1', 'Opción Gearless - 6 Paradas ', NULL, '0.00', '52948727.00', '40729790.00', '36656811.00', '0.00', '15', '1', '0', '2025-05-31 13:34:17', '2025-06-02 14:12:42'),
('501', '1', 'Opción Gearless - 7 Paradas ', NULL, '0.00', '54755821.90', '42119863.00', '37907876.70', '0.00', '16', '1', '0', '2025-05-31 13:36:13', '2025-06-02 14:28:27'),
('502', '1', 'Opción Gearless - 8 Paradas', NULL, '0.00', '54583733.10', '41987487.00', '37788738.30', '0.00', '17', '1', '0', '2025-05-31 13:37:43', '2025-06-02 14:28:49'),
('503', '1', 'Opción Gearless - 9 Paradas', NULL, '0.00', '55903077.10', '43002367.00', '38702130.30', '0.00', '18', '1', '0', '2025-05-31 13:39:45', '2025-06-02 14:28:58'),
('505', '1', 'Opción Gearless - 10 Paradas', NULL, '0.00', '57268857.10', '44052967.00', '39647670.30', '0.00', '19', '1', '0', '2025-05-31 13:41:37', '2025-06-02 14:29:07');

-- Lote 4 de 5 para tabla opciones
INSERT INTO `opciones` (`id`, `categoria_id`, `nombre`, `descripcion`, `precio`, `precio_90_dias`, `precio_160_dias`, `precio_270_dias`, `descuento`, `orden`, `activo`, `es_titulo`, `created_at`, `updated_at`) VALUES
('506', '1', 'Opción Gearless - 11 Paradas', NULL, '0.00', '58484401.30', '44988001.00', '40489200.90', '0.00', '20', '1', '0', '2025-05-31 13:47:52', '2025-06-02 14:29:27'),
('507', '1', 'Opción Gearless - 12 Paradas', NULL, '0.00', '59832425.90', '46024943.00', '41422448.70', '0.00', '21', '1', '0', '2025-05-31 13:48:59', '2025-06-02 14:29:36'),
('508', '1', 'Opción Gearless - 13 Paradas', NULL, '0.00', '61188645.70', '47068189.00', '42361370.10', '0.00', '22', '1', '0', '2025-05-31 13:49:26', '2025-06-02 14:29:43'),
('509', '1', 'Opción Gearless - 14 Paradas', NULL, '0.00', '62408286.20', '48006374.00', '43205736.60', '0.00', '23', '1', '0', '2025-05-31 13:50:09', '2025-06-02 14:29:51'),
('510', '1', 'Opción Gearless - 15Paradas', NULL, '0.00', '63763141.00', '49048570.00', '44143713.00', '0.00', '24', '1', '0', '2025-05-31 13:51:45', '2025-06-02 14:29:57'),
('511', '1', 'Hidraulico 450kg central 13hp piston 1 tramo - 2 Paradas', NULL, '0.00', '46525553.10', '35788887.00', '32209998.30', '0.00', '25', '1', '0', '2025-05-31 13:53:22', '2025-06-02 14:30:45'),
('512', '1', 'Hidraulico 450kg central 13hp piston 1 tramo - 3 Paradas', NULL, '0.00', '47697525.20', '36690404.00', '33021363.60', '0.00', '26', '1', '0', '2025-05-31 13:56:26', '2025-06-02 14:32:05'),
('513', '1', 'Hidraulico 450kg central 13hp piston 1 tramo - 4 Paradas', NULL, '0.00', '48912871.80', '37625286.00', '33862757.40', '0.00', '27', '1', '0', '2025-05-31 13:57:26', '2025-06-02 14:32:17'),
('514', '1', 'Hidraulico 450kg central 13hp piston 1 tramo - 5 Paradas', NULL, '0.00', '50309368.20', '38699514.00', '34829562.60', '0.00', '28', '1', '0', '2025-05-31 13:58:13', '2025-06-02 14:32:26'),
('515', '1', 'Hidraulico 450kg central 13hp piston 1 tramo - 6 Paradas', NULL, '0.00', '51297923.30', '39459941.00', '35513946.90', '0.00', '29', '1', '0', '2025-05-31 13:59:06', '2025-06-02 14:32:45'),
('516', '1', 'Hidraulico 450kg central 25lts 4hp - 2 Paradas', NULL, '0.00', '39122860.40', '30094508.00', '27085057.20', '0.00', '30', '1', '0', '2025-05-31 14:00:20', '2025-06-02 14:33:59'),
('517', '1', 'Hidraulico 450kg central 25lts 4hp - 3 Paradas', NULL, '0.00', '39726794.90', '30559073.00', '27503165.70', '0.00', '31', '1', '0', '2025-05-31 14:01:59', '2025-06-02 14:34:24'),
('518', '1', 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 2 Paradas Cabina Acero', NULL, '0.00', '35368628.10', '27206637.00', '24485973.30', '0.00', '33', '1', '0', '2025-05-31 14:03:37', '2025-06-02 14:37:31'),
('519', '1', 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 2 Paradas Cabina Chapa ', NULL, '0.00', '35007001.90', '26928463.00', '24235616.70', '0.00', '34', '1', '0', '2025-05-31 14:05:12', '2025-06-02 14:37:31'),
('520', '1', 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 3 Paradas Cabina Acero', NULL, '0.00', '35976311.80', '27674086.00', '24906677.40', '0.00', '35', '1', '0', '2025-05-31 14:06:15', '2025-06-02 14:37:31'),
('521', '1', 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 3 Paradas Cabina Chapa', NULL, '0.00', '35610955.90', '27393043.00', '24653738.70', '0.00', '36', '1', '0', '2025-05-31 14:07:27', '2025-06-02 14:37:31'),
('522', '1', 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 4 Paradas Cabina Acero', NULL, '0.00', '36591399.00', '28147230.00', '25332507.00', '0.00', '37', '1', '0', '2025-05-31 14:08:32', '2025-06-02 14:37:31'),
('523', '1', 'Montavehiculos - 2 Paradas ', NULL, '0.00', '51578533.50', '39675795.00', '35708215.50', '0.00', '38', '1', '0', '2025-05-31 14:10:48', '2025-06-02 14:37:31'),
('524', '1', 'Montavehiculos - 3 Paradas', NULL, '0.00', '53647578.40', '41267368.00', '37140631.20', '0.00', '39', '1', '0', '2025-05-31 14:12:26', '2025-06-02 14:37:31'),
('525', '1', 'Montavehiculos - 4 Paradas', NULL, '0.00', '55828526.00', '42945020.00', '38650518.00', '0.00', '40', '1', '0', '2025-05-31 14:12:54', '2025-06-02 14:37:31'),
('526', '1', 'Montavehiculos - Rampa - 2 Paradas', NULL, '0.00', '51541192.30', '39647071.00', '35682363.90', '0.00', '41', '1', '0', '2025-05-31 14:13:59', '2025-06-02 14:37:31'),
('527', '1', 'Hidraulico 450kg central 25lts 4hp - 4 Paradas', NULL, '0.00', '40934337.60', '31487952.00', '28339156.80', '0.00', '32', '1', '0', '2025-06-02 14:37:11', '2025-06-02 14:38:45'),
('528', '1', 'Montacargas - Maquina Tambor - Hasta 400 kg Puerta Manual\t', NULL, '0.00', '36247801.20', '27882924.00', '25094631.60', '0.00', '42', '1', '0', '2025-06-02 14:55:12', '2025-06-02 14:58:33'),
('529', '1', 'Montacargas - Maquina Tambor - Hasta 1000 kg Puerta Manual\t (copia)', NULL, '0.00', '45264680.50', '34818985.00', '31337086.50', '0.00', '43', '1', '0', '2025-06-02 14:58:40', '2025-06-02 15:00:35'),
('530', '1', 'Salvaescaleras Modelo Simple H/ 1.80 M', NULL, '0.00', '17205251.70', '13234809.00', '11911328.10', '0.00', '44', '1', '0', '2025-06-02 15:04:31', '2025-06-02 15:05:54');

-- Lote 5 de 5 para tabla opciones
INSERT INTO `opciones` (`id`, `categoria_id`, `nombre`, `descripcion`, `precio`, `precio_90_dias`, `precio_160_dias`, `precio_270_dias`, `descuento`, `orden`, `activo`, `es_titulo`, `created_at`, `updated_at`) VALUES
('531', '1', 'Salvaescaleras Modelo Completo H/ 1.80 M ', NULL, '0.00', '22555096.20', '17350074.00', '15615066.60', '0.00', '45', '1', '0', '2025-06-02 15:06:00', '2025-06-02 15:07:12'),
('532', '1', 'Salvaescaleras Modelo Completo H/ 3 M ', NULL, '0.00', '28377954.80', '21829196.00', '19646276.40', '0.00', '46', '1', '0', '2025-06-02 15:07:19', '2025-06-02 15:08:15'),
('533', '1', 'Escaleras Mecanicas - Vidriado - Faldon Acero - H/ 4.50 angulo 35 - Peldaños 800 (Dolares)', NULL, '0.00', '55000.00', '55000.00', '55000.00', '0.00', '47', '1', '0', '2025-06-02 15:15:30', '2025-06-02 15:18:09'),
('534', '1', 'Montaplatos - 2 Paradas H/ 100 kg - 0,80x1,20\t', NULL, '0.00', '19820318.70', '15246399.00', '13721759.10', '0.00', '48', '1', '0', '2025-06-02 15:18:22', '2025-06-02 15:19:58'),
('535', '1', 'Montaplatos - 2 Paradas H/ 100 kg - 0,80x1,20\t (copia)', NULL, '0.00', '19820318.70', '15246399.00', '13721759.10', '0.00', '49', '1', '0', '2025-06-02 15:20:04', '2025-06-02 15:20:04'),
('536', '1', 'Montaplatos - 3 Paradas H/ 100 kg - 0,80x1,20\t', NULL, '0.00', '24067578.60', '18513522.00', '16662169.80', '0.00', '50', '1', '0', '2025-06-02 15:20:29', '2025-06-02 15:22:15'),
('537', '1', 'Giracoches - Estructura 1,80x1,80 2 Paradas\t', NULL, '0.00', '17460791.40', '13431378.00', '12088240.20', '0.00', '51', '1', '0', '2025-06-02 15:23:22', '2025-06-02 15:24:38'),
('538', '1', 'Giracoches - Estructura 1,80x1,80 3 Paradas', NULL, '0.00', '19820356.40', '15246428.00', '13721785.20', '0.00', '52', '1', '0', '2025-06-02 15:24:42', '2025-06-02 15:25:27'),
('539', '1', 'Estructura - 2 Paradas', NULL, '0.00', '15378473.50', '11829595.00', '10646635.50', '0.00', '53', '1', '0', '2025-06-02 15:31:44', '2025-06-02 15:33:10'),
('540', '1', 'Estructura - Parada Adicional - C/U ', NULL, '0.00', '7689236.10', '5914797.00', '5323317.30', '0.00', '54', '1', '0', '2025-06-02 15:35:59', '2025-06-02 15:37:18'),
('541', '1', 'Perfil Divisorio por Unidad con Mano de Obra', NULL, '0.00', '87516.00', '67320.00', '60588.00', '0.00', '55', '1', '0', '2025-06-02 15:37:30', '2025-06-02 15:38:32');

-- Tabla presupuestos está vacía

-- Tabla presupuesto_detalles está vacía

-- Tabla presupuesto_items está vacía

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- ========================================
-- IMPORTACIÓN COMPLETADA EXITOSAMENTE
-- Tablas procesadas: 8
-- ========================================
