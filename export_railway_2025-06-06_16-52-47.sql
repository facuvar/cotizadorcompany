-- ========================================
-- EXPORTACIÓN PARA RAILWAY
-- Base de datos: company_presupuestos
-- Fecha: 2025-06-06 16:52:47
-- ========================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

-- Eliminar tablas existentes
DROP TABLE IF EXISTS `presupuesto_items`;
DROP TABLE IF EXISTS `presupuesto_detalles`;
DROP TABLE IF EXISTS `presupuestos`;
DROP TABLE IF EXISTS `opciones`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `configuracion`;
DROP TABLE IF EXISTS `plazos_entrega`;
DROP TABLE IF EXISTS `categorias`;

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

-- Datos para tabla categorias
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
('1', 'ASCENSORES', 'Equipos electromecánicos de ascensores', '2', '1', '2025-05-28 18:56:56', '2025-06-02 12:30:00'),
('2', 'ADICIONALES', 'Opciones adicionales para ascensores', '1', '1', '2025-05-28 18:56:56', '2025-06-02 12:30:00'),
('3', 'DESCUENTOS', 'Formas de pago y descuentos', '3', '1', '2025-05-28 18:56:56', '2025-05-28 18:56:56');

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

-- Datos para tabla plazos_entrega
INSERT INTO `plazos_entrega` (`id`, `nombre`, `dias`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
('1', '90 dias', '90', '1', '1', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('2', '160-180 dias', '170', '2', '1', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('3', '270 dias', '270', '3', '1', '2025-05-28 18:56:56', '2025-05-28 18:56:56');

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

-- Datos para tabla configuracion
INSERT INTO `configuracion` (`id`, `nombre`, `valor`, `descripcion`, `tipo`, `created_at`, `updated_at`) VALUES
('1', 'titulo_sistema', 'Sistema de Presupuestos de Ascensores', 'Título del sistema', 'text', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('2', 'empresa_nombre', 'Tu Empresa', 'Nombre de la empresa', 'text', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('3', 'empresa_telefono', '+54 11 1234-5678', 'Teléfono de la empresa', 'text', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('4', 'empresa_email', 'info@tuempresa.com', 'Email de la empresa', 'text', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('5', 'empresa_direccion', 'Tu Dirección, Ciudad', 'Dirección de la empresa', 'text', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('6', 'moneda', 'ARS', 'Moneda del sistema', 'text', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('7', 'iva_porcentaje', '21', 'Porcentaje de IVA', 'number', '2025-05-28 18:56:56', '2025-05-28 18:56:56'),
('8', 'validez_presupuesto', '30', 'Días de validez del presupuesto', 'number', '2025-05-28 18:56:56', '2025-05-28 18:56:56');

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

-- Datos para tabla usuarios
INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `nombre`, `rol`, `activo`, `ultimo_acceso`, `created_at`, `updated_at`) VALUES
('1', 'admin', '$2y$10$2uIVLR3QCYelbTZgtD2j5uUWSZUVxXEPuw5.lCZd.Km75H/JVANH6', 'admin@tuempresa.com', 'Administrador', 'admin', '1', NULL, '2025-05-28 18:56:56', '2025-05-28 18:56:56');

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
) ENGINE=InnoDB AUTO_INCREMENT=546 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos para tabla opciones
INSERT INTO `opciones` (`id`, `categoria_id`, `nombre`, `descripcion`, `precio`, `precio_90_dias`, `precio_160_dias`, `precio_270_dias`, `descuento`, `orden`, `activo`, `es_titulo`, `created_at`, `updated_at`) VALUES
('410', '2', 'Ascensores Electromecanicos Adicional Acceso Cabina en acero', '73500', '297.00', '1679329.08', '1291791.60', '1162612.44', '0.00', '7', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('412', '2', 'Ascensores Electromecanicos Adicional Lateral Panoramico', '129850', '524.70', '538859.18', '414507.06', '373056.35', '0.00', '9', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('415', '2', 'Ascensores Electromecanicos Adicional Puertas de 900', '110250', '445.50', '573923.49', '441479.61', '397331.65', '0.00', '12', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('416', '2', 'Ascensores Electromecanicos Adicional Tarjeta chip keypass', '75950', '306.90', '18453.01', '14194.62', '12775.16', '0.00', '18', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('417', '2', 'Ascensores Electromecanicos Adicional Extension de panel cabina a 2,30', '110250', '445.50', '830435.46', '638796.51', '574916.86', '0.00', '28', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('418', '2', 'Ascensores Hidraulicos adicional puertas de 1200', '117600', '475.20', '1418153.02', '1090886.94', '1276337.72', '0.00', '45', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('419', '2', 'Ascensores Hidraulicos adicional restar cabina en chapa', '235200', '950.40', '370927.56', '285328.89', '333834.80', '0.00', '39', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('420', '2', 'Ascensores Hidraulicos adicional restar operador y dejar puerta plegadiza chapa', '294000', '1287.00', '658812.73', '506779.02', '592931.46', '0.00', '42', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('421', '2', 'Ascensores Hidraulicos adicional puertas de 900', '1178450', '4761.90', '567260.69', '436354.38', '510534.62', '0.00', '43', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('426', '2', 'Montacargas Adicional puerta tijera - precio unitario', '98000', '396.00', '2148669.67', '1652822.82', '1933802.70', '0.00', '56', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('428', '2', 'Ascensores Electromecanicos Adicional Balanza', '', '0.00', '1037122.52', '797786.55', '718007.90', '0.00', '25', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('429', '2', 'Ascensores Electromecanicos Adicional Indicador LCD color 5\"', '', '0.00', '308134.83', '237026.79', '213324.11', '0.00', '24', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('430', '2', 'Ascensores Electromecanicos Adicional Indicador LED alfa num 0, 8', '', '0.00', '75660.16', '58200.12', '52380.11', '0.00', '23', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('431', '2', 'Ascensores Electromecanicos Adicional Puerta panoramica pisos', '', '0.00', '839663.25', '645894.81', '581305.33', '0.00', '17', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('432', '2', 'Ascensores Electromecanicos Adicional Intercomunicador', '', '0.00', '830435.46', '638796.51', '574916.86', '0.00', '26', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('433', '2', 'Ascensores Electromecanicos Adicional Sistema UPS', '', '0.00', '250975.30', '193057.92', '173752.13', '0.00', '21', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('434', '2', 'Ascensores Electromecanicos Adicional Sistema keypass simple (un cod universal)', '', '0.00', '479807.76', '369082.89', '332174.60', '0.00', '20', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('435', '2', 'Ascensores Electromecanicos Adicional Fase I/ fase II bomberios', '', '0.00', '415217.09', '319397.76', '287457.98', '0.00', '27', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('436', '2', 'Ascensores Electromecanicos Adicional Puerta panoramica cabina + PB', NULL, '0.00', '1799114.03', '1383933.87', '1245540.48', '0.00', '16', '1', '1', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('440', '2', 'Ascensores Electromecanicos Adicional Acero Pisos', '73500', '297.00', '370927.56', '285328.89', '256796.00', '0.00', '8', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('442', '2', 'Ascensores Electromecanicos Adicional Cabina en chapa c/detalles restar', '129850', '524.70', '348163.10', '267817.77', '241035.99', '0.00', '10', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('443', '2', 'Ascensores Electromecanicos Adicional Puertas de 1300', '75950', '306.90', '1439424.56', '1107249.66', '996524.69', '0.00', '14', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('444', '2', 'Ascensores Electromecanicos Adicional Puertas de 1000', '110250', '445.50', '1149692.54', '884378.88', '795940.99', '0.00', '13', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('445', '2', 'Ascensores Hidraulicos adicional acceso en cabina en acero', '75950', '306.90', '1677482.24', '1290370.95', '1509734.02', '0.00', '47', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('446', '2', 'Ascensores Hidraulicos adicional puerta panoramica pisos', '110250', '445.50', '839663.25', '645894.81', '755696.92', '0.00', '49', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('447', '2', 'Ascensores Hidraulicos adicional tarjeta chip keypass', '507150', '2049.30', '18453.01', '14194.62', '16607.71', '0.00', '50', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('448', '2', 'Ascensores Hidraulicos adicional puerta panoramica cabina + PB ', '134750', '544.50', '1799114.03', '1383933.87', '1619202.63', '0.00', '48', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('449', '2', 'Ascensores Hidraulicos adicional puertas de 1800', '117600', '475.20', '1607239.06', '1236337.74', '1446515.15', '0.00', '46', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('450', '2', 'Ascensores Hidraulicos adicional restar puerta cabina y pb a chapa', '235200', '950.40', '538860.46', '414508.05', '484974.42', '0.00', '40', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('451', '2', 'Ascensores Hidraulicos adicional restar sin puertas ext x4', '294000', '1287.00', '2229262.89', '1714817.61', '2006336.60', '0.00', '41', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('452', '2', 'Ascensores Hidraulicos adicional puertas de 1000', '1178450', '4761.90', '1132704.14', '871310.88', '1019433.73', '0.00', '44', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('457', '2', 'Montacargas Adicional puerta guillotina - precio unitario', '220500', '970.20', '2200304.70', '2200304.70', '2574356.50', '0.00', '55', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('458', '2', 'Salvaescaleras adicional en acero', '98000', '396.00', '2817950.85', '2167654.50', '2536155.76', '0.00', '57', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('460', '2', 'Ascensores Electromecanicos Adicional Puertas de 1800', NULL, '0.00', '1629501.59', '1253462.76', '1128116.48', '0.00', '15', '1', '1', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('461', '2', 'Ascensores Hidraulicos adicional 2 tramos', '', '0.00', '1504013.94', '1156933.80', '1353612.55', '0.00', '30', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('462', '2', 'Ascensores Hidraulicos adicionalpiso en acero', '', '0.00', '370927.56', '285328.89', '333834.80', '0.00', '37', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('463', '2', 'Ascensores Hidraulicos adicional sistema keypass completo (un cod por piso)', NULL, '0.00', '1088795.57', '837535.05', '979916.01', '0.00', '51', '1', '0', '2025-05-29 10:08:53', '2025-06-06 10:07:12'),
('464', '2', 'Ascensores Electromecanicos Adicional PB y puerta de cabina en chapa restar', '', '0.00', '505789.71', '389069.01', '350162.11', '0.00', '11', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('465', '2', 'Ascensores Electromecanicos Adicional Parada adicional chapa (precio por cada una)', NULL, '0.00', '2089010.78', '1606931.37', '1446238.23', '0.00', '29', '1', '0', '2025-05-29 10:08:53', '2025-06-05 15:12:45'),
('468', '3', 'Efectivo X', NULL, '0.00', '0.00', '0.00', '0.00', '8.00', '1', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:33:28'),
('470', '3', 'Mejora de Presupuesto', NULL, '0.00', '0.00', '0.00', '0.00', '5.00', '6', '1', '0', '2025-05-29 10:08:53', '2025-06-03 12:36:08'),
('476', '2', 'Ascensores Hidraulicos adicional panoramico', NULL, '0.00', '539781.96', '415216.89', '485803.76', '0.00', '38', '1', '0', '2025-05-30 10:25:43', '2025-06-06 10:07:12'),
('477', '2', 'Ascensores Hidraulicos adicional sistema keypass simple (un cod universal)', NULL, '0.00', '479807.76', '369082.89', '431826.98', '0.00', '52', '1', '0', '2025-05-30 10:25:43', '2025-06-06 10:07:12'),
('478', '2', 'Ascensores Electromecanicos Adicional Indicador LED alfa num 1, 2', NULL, '0.00', '101496.68', '78074.37', '70266.93', '0.00', '22', '1', '0', '2025-05-30 10:25:43', '2025-06-05 15:12:45'),
('479', '2', 'Ascensores Electromecanicos Adicional Sistema keypass completo (un cod por piso)', NULL, '0.00', '1088795.57', '837535.05', '753781.54', '0.00', '19', '1', '0', '2025-05-30 10:25:43', '2025-06-05 15:12:45'),
('480', '2', 'Ascensores Hidraulicos adicional sistema UPS', NULL, '0.00', '250975.30', '193057.92', '225877.77', '0.00', '53', '1', '0', '2025-05-30 10:25:43', '2025-06-06 10:07:12'),
('481', '3', 'Cheques Electronicos(30-45) - financiacion en 6 cheques electronicos 0-15-30-45-60-90', NULL, '0.00', '0.00', '0.00', '0.00', '2.00', '3', '1', '0', '2025-05-30 10:25:43', '2025-06-03 12:35:01'),
('482', '3', 'Transferencia', NULL, '0.00', '0.00', '0.00', '0.00', '5.00', '2', '1', '0', '2025-05-30 10:25:43', '2025-06-03 12:33:57'),
('484', '1', 'Equipo Electromecanico 450kg carga util - 4 Paradas', NULL, '0.00', '44417214.27', '34167087.90', '30750379.11', '0.00', '1', '1', '0', '2025-05-30 12:59:11', '2025-06-05 09:49:22'),
('485', '1', 'Equipo Electromecanico 450kg carga util - 5 Paradas', NULL, '0.00', '45736546.28', '35181958.68', '31663762.81', '0.00', '2', '1', '0', '2025-05-30 13:03:02', '2025-06-05 09:49:22'),
('486', '1', 'Equipo Electromecanico 450kg carga util - 6 Paradas', NULL, '0.00', '47078189.73', '36213992.10', '32592592.89', '0.00', '3', '1', '0', '2025-05-30 13:09:18', '2025-06-05 09:49:22'),
('487', '1', 'Equipo Electromecanico 450kg carga util - 7 Paradas', NULL, '0.00', '49832463.68', '38332664.37', '34499397.93', '0.00', '4', '1', '0', '2025-05-30 14:32:22', '2025-06-05 09:49:22'),
('488', '1', 'Equipo Electromecanico 450kg carga util - 8 Paradas', NULL, '0.00', '49662095.77', '38201612.13', '34381450.92', '0.00', '5', '1', '0', '2025-05-30 14:33:08', '2025-06-05 09:49:22'),
('489', '1', 'Equipo Electromecanico 450kg carga util - 9 Paradas ', NULL, '0.00', '50968246.33', '39206343.33', '35285709.00', '0.00', '6', '1', '0', '2025-05-30 14:35:15', '2025-06-05 09:49:22'),
('490', '1', 'Equipo Electromecanico 450kg carga util - 10 Paradas', NULL, '0.00', '52320368.53', '40246437.33', '36221793.60', '0.00', '7', '1', '0', '2025-05-30 14:38:08', '2025-06-05 09:49:22'),
('492', '1', 'Equipo Electromecanico 450kg carga util - 11 Paradas ', NULL, '0.00', '53523757.29', '41172120.99', '37054908.89', '0.00', '8', '1', '0', '2025-05-30 14:43:17', '2025-06-05 09:49:22'),
('493', '1', 'Equipo Electromecanico 450kg carga util - 12 Paradas', NULL, '0.00', '54858301.64', '42198693.57', '37978824.21', '0.00', '9', '1', '0', '2025-05-30 15:00:59', '2025-06-05 09:49:22'),
('494', '1', 'Equipo Electromecanico 450kg carga util - 13 Paradas', NULL, '0.00', '56200959.24', '43231507.11', '38908356.40', '0.00', '10', '1', '0', '2025-05-30 15:02:11', '2025-06-05 09:49:22'),
('495', '1', 'Equipo Electromecanico 450kg carga util - 14 Paradas ', NULL, '0.00', '57408403.34', '44160310.26', '39744279.23', '0.00', '11', '1', '0', '2025-05-30 15:03:04', '2025-06-05 09:49:22'),
('496', '1', 'Equipo Electromecanico 450kg carga util - 15 Paradas ', NULL, '0.00', '58749709.59', '45192084.30', '40672875.87', '0.00', '12', '1', '0', '2025-05-30 15:03:50', '2025-06-05 09:49:22'),
('498', '1', 'Opción Gearless - 4 Paradas', NULL, '0.00', '49758264.27', '38275587.90', '34448029.11', '0.00', '13', '1', '0', '2025-05-30 15:05:59', '2025-06-05 09:49:22'),
('499', '1', 'Opción Gearless - 5 Paradas', NULL, '0.00', '51077596.28', '39290458.68', '35361412.81', '0.00', '14', '1', '0', '2025-05-30 15:50:18', '2025-06-05 09:49:22'),
('500', '1', 'Opción Gearless - 6 Paradas ', NULL, '0.00', '52419239.73', '40322492.10', '36290242.89', '0.00', '15', '1', '0', '2025-05-31 13:34:17', '2025-06-05 09:49:22'),
('501', '1', 'Opción Gearless - 7 Paradas ', NULL, '0.00', '54208263.68', '41698664.37', '37528797.93', '0.00', '16', '1', '0', '2025-05-31 13:36:13', '2025-06-05 09:49:22'),
('502', '1', 'Opción Gearless - 8 Paradas', NULL, '0.00', '54037895.77', '41567612.13', '37410850.92', '0.00', '17', '1', '0', '2025-05-31 13:37:43', '2025-06-05 09:49:22'),
('503', '1', 'Opción Gearless - 9 Paradas', NULL, '0.00', '55344046.33', '42572343.33', '38315109.00', '0.00', '18', '1', '0', '2025-05-31 13:39:45', '2025-06-05 09:49:22'),
('505', '1', 'Opción Gearless - 10 Paradas', NULL, '0.00', '56696168.53', '43612437.33', '39251193.60', '0.00', '19', '1', '0', '2025-05-31 13:41:37', '2025-06-05 09:49:22'),
('506', '1', 'Opción Gearless - 11 Paradas', NULL, '0.00', '57899557.29', '44538120.99', '40084308.89', '0.00', '20', '1', '0', '2025-05-31 13:47:52', '2025-06-05 09:49:22'),
('507', '1', 'Opción Gearless - 12 Paradas', NULL, '0.00', '59234101.64', '45564693.57', '41008224.21', '0.00', '21', '1', '0', '2025-05-31 13:48:59', '2025-06-05 09:49:22'),
('508', '1', 'Opción Gearless - 13 Paradas', NULL, '0.00', '60576759.24', '46597507.11', '41937756.40', '0.00', '22', '1', '0', '2025-05-31 13:49:26', '2025-06-05 09:49:22'),
('509', '1', 'Opción Gearless - 14 Paradas', NULL, '0.00', '61784203.34', '47526310.26', '42773679.23', '0.00', '23', '1', '0', '2025-05-31 13:50:09', '2025-06-05 09:49:22'),
('510', '1', 'Opción Gearless - 15Paradas', NULL, '0.00', '63125509.59', '48558084.30', '43702275.87', '0.00', '24', '1', '0', '2025-05-31 13:51:45', '2025-06-05 09:49:22'),
('511', '1', 'Hidraulico 450kg central 13hp piston 1 tramo - 2 Paradas', NULL, '0.00', '46060297.57', '35430998.13', '31887898.32', '0.00', '25', '1', '0', '2025-05-31 13:53:22', '2025-06-05 09:49:22'),
('512', '1', 'Hidraulico 450kg central 13hp piston 1 tramo - 3 Paradas', NULL, '0.00', '47220549.95', '36323499.96', '32691149.96', '0.00', '26', '1', '0', '2025-05-31 13:56:26', '2025-06-05 09:49:22'),
('513', '1', 'Hidraulico 450kg central 13hp piston 1 tramo - 4 Paradas', NULL, '0.00', '48423743.08', '37249033.14', '33524129.83', '0.00', '27', '1', '0', '2025-05-31 13:57:26', '2025-06-05 09:49:22'),
('514', '1', 'Hidraulico 450kg central 13hp piston 1 tramo - 5 Paradas', NULL, '0.00', '49806274.52', '38312518.86', '34481266.97', '0.00', '28', '1', '0', '2025-05-31 13:58:13', '2025-06-05 09:49:22'),
('515', '1', 'Hidraulico 450kg central 13hp piston 1 tramo - 6 Paradas', NULL, '0.00', '50784944.07', '39065341.59', '35158807.43', '0.00', '29', '1', '0', '2025-05-31 13:59:06', '2025-06-05 09:49:22'),
('516', '1', 'Hidraulico 450kg central 25lts 4hp - 2 Paradas', NULL, '0.00', '38731631.80', '29793562.92', '26814206.63', '0.00', '30', '1', '0', '2025-05-31 14:00:20', '2025-06-05 09:49:22'),
('517', '1', 'Hidraulico 450kg central 25lts 4hp - 3 Paradas', NULL, '0.00', '39329526.95', '30253482.27', '27228134.04', '0.00', '31', '1', '0', '2025-05-31 14:01:59', '2025-06-05 09:49:22'),
('518', '1', 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 2 Paradas Cabina Acero', NULL, '0.00', '35014941.82', '26934570.63', '24241113.57', '0.00', '33', '1', '0', '2025-05-31 14:03:37', '2025-06-05 09:49:22'),
('519', '1', 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 2 Paradas Cabina Chapa ', NULL, '0.00', '34656931.88', '26659178.37', '23993260.53', '0.00', '34', '1', '0', '2025-05-31 14:05:12', '2025-06-05 09:49:22'),
('520', '1', 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 3 Paradas Cabina Acero', NULL, '0.00', '35616548.68', '27397345.14', '24657610.63', '0.00', '35', '1', '0', '2025-05-31 14:06:15', '2025-06-05 09:49:22'),
('521', '1', 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 3 Paradas Cabina Chapa', NULL, '0.00', '35254846.34', '27119112.57', '24407201.31', '0.00', '36', '1', '0', '2025-05-31 14:07:27', '2025-06-05 09:49:22'),
('522', '1', 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 4 Paradas Cabina Acero', NULL, '0.00', '36225485.01', '27865757.70', '25079181.93', '0.00', '37', '1', '0', '2025-05-31 14:08:32', '2025-06-05 09:49:22'),
('523', '1', 'Montavehiculos - 2 Paradas ', NULL, '0.00', '51062748.16', '39279037.05', '35351133.34', '0.00', '38', '1', '0', '2025-05-31 14:10:48', '2025-06-05 09:49:22'),
('524', '1', 'Montavehiculos - 3 Paradas', NULL, '0.00', '53111102.62', '40854694.32', '36769224.89', '0.00', '39', '1', '0', '2025-05-31 14:12:26', '2025-06-05 09:49:22'),
('525', '1', 'Montavehiculos - 4 Paradas', NULL, '0.00', '55270240.74', '42515569.80', '38264012.82', '0.00', '40', '1', '0', '2025-05-31 14:12:54', '2025-06-05 09:49:22'),
('526', '1', 'Montavehiculos - Rampa - 2 Paradas', NULL, '0.00', '51025780.38', '39250600.29', '35325540.26', '0.00', '41', '1', '0', '2025-05-31 14:13:59', '2025-06-05 09:49:22'),
('527', '1', 'Hidraulico 450kg central 25lts 4hp - 4 Paradas', NULL, '0.00', '40524994.22', '31173072.48', '28055765.23', '0.00', '32', '1', '0', '2025-06-02 14:37:11', '2025-06-05 09:49:22'),
('528', '1', 'Montacargas - Maquina Tambor - Hasta 400 kg Puerta Manual\t', NULL, '0.00', '35885323.19', '27604094.76', '24843685.28', '0.00', '42', '1', '0', '2025-06-02 14:55:12', '2025-06-05 09:49:22'),
('529', '1', 'Montacargas - Maquina Tambor - Hasta 1000 kg Puerta Manual\t (copia)', NULL, '0.00', '44812033.70', '34470795.15', '31023715.64', '0.00', '43', '1', '0', '2025-06-02 14:58:40', '2025-06-05 09:49:22'),
('530', '1', 'Salvaescaleras Modelo Simple H/ 1.80 M', NULL, '0.00', '17033199.18', '13102460.91', '11792214.82', '0.00', '44', '1', '0', '2025-06-02 15:04:31', '2025-06-05 09:49:22'),
('531', '1', 'Salvaescaleras Modelo Completo H/ 1.80 M ', NULL, '0.00', '22329545.24', '17176573.26', '15458915.93', '0.00', '45', '1', '0', '2025-06-02 15:06:00', '2025-06-05 09:49:22'),
('532', '1', 'Salvaescaleras Modelo Completo H/ 3 M ', NULL, '0.00', '28094175.25', '21610904.04', '19449813.64', '0.00', '46', '1', '0', '2025-06-02 15:07:19', '2025-06-05 09:49:22'),
('533', '1', 'Escaleras Mecanicas - Vidriado - Faldon Acero - H/ 4.50 angulo 35 - Peldaños 800 (Dolares)', NULL, '0.00', '54450.00', '54450.00', '54450.00', '0.00', '47', '1', '0', '2025-06-02 15:15:30', '2025-06-05 09:49:22'),
('534', '1', 'Montaplatos - 2 Paradas H/ 100 kg - 0,80x1,20\t', NULL, '0.00', '19622115.51', '15093935.01', '13584541.51', '0.00', '48', '1', '0', '2025-06-02 15:18:22', '2025-06-05 09:49:22'),
('535', '1', 'Montaplatos - 2 Paradas H/ 100 kg - 0,80x1,20\t (copia)', NULL, '0.00', '19622115.51', '15093935.01', '13584541.51', '0.00', '49', '1', '0', '2025-06-02 15:20:04', '2025-06-05 09:49:22'),
('536', '1', 'Montaplatos - 3 Paradas H/ 100 kg - 0,80x1,20\t', NULL, '0.00', '23826902.81', '18328386.78', '16495548.10', '0.00', '50', '1', '0', '2025-06-02 15:20:29', '2025-06-05 09:49:22'),
('537', '1', 'Giracoches - Estructura 1,80x1,80 2 Paradas\t', NULL, '0.00', '17286183.49', '13297064.22', '11967357.80', '0.00', '51', '1', '0', '2025-06-02 15:23:22', '2025-06-05 09:49:22'),
('538', '1', 'Giracoches - Estructura 1,80x1,80 3 Paradas', NULL, '0.00', '19622152.84', '15093963.72', '13584567.35', '0.00', '52', '1', '0', '2025-06-02 15:24:42', '2025-06-05 09:49:22'),
('539', '1', 'Estructura - 2 Paradas', NULL, '0.00', '15224688.77', '11711299.05', '10540169.15', '0.00', '53', '1', '0', '2025-06-02 15:31:44', '2025-06-05 09:49:22'),
('540', '1', 'Estructura - Parada Adicional - C/U ', NULL, '0.00', '7612343.74', '5855649.03', '5270084.13', '0.00', '54', '1', '0', '2025-06-02 15:35:59', '2025-06-05 09:49:22'),
('541', '1', 'Perfil Divisorio por Unidad con Mano de Obra', NULL, '0.00', '86640.84', '66646.80', '59982.12', '0.00', '55', '1', '0', '2025-06-02 15:37:30', '2025-06-05 09:49:22'),
('542', '2', 'Ascensores Electromecanicos Adicional 750kg Maquina - Cabina 2,25m3', NULL, '0.00', '3522824.31', '2709865.62', '2438879.06', '0.00', '1', '1', '0', '2025-06-05 15:04:13', '2025-06-05 15:14:20'),
('543', '2', 'Ascensores Electromecanicos Adicional 1000kg Maquina Cabina 2,66', NULL, '0.00', '4586194.90', '3527842.23', '3175058.01', '0.00', '2', '1', '0', '2025-06-05 15:12:36', '2025-06-05 15:16:33'),
('544', '2', 'Ascensores Hidraulicos adicional 750kg central y piston, cabina 2,25m3', NULL, '0.00', '1295517.60', '996552.00', '1165965.84', '0.00', '31', '1', '0', '2025-06-06 10:00:00', '2025-06-06 10:04:16'),
('545', '2', 'Ascensores Hidraulicos adicional 1000kg central y piston, cabina de 2.66m3', NULL, '0.00', '2907273.20', '2236364.00', '2616545.88', '0.00', '34', '1', '0', '2025-06-06 10:07:04', '2025-06-06 10:08:42');

-- Estructura para tabla presupuestos
CREATE TABLE `presupuestos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_presupuesto` varchar(50) NOT NULL,
  `cliente_nombre` varchar(255) NOT NULL,
  `cliente_email` varchar(255) DEFAULT NULL,
  `cliente_telefono` varchar(50) DEFAULT NULL,
  `cliente_empresa` varchar(255) DEFAULT NULL,
  `ubicacion_obra` text DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla presupuestos está vacía

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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla presupuesto_detalles está vacía

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

-- Tabla presupuesto_items está vacía

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- Exportación completada: 2025-06-06 16:52:47
