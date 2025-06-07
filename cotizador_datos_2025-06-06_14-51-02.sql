-- =====================================================
-- DATOS DEL COTIZADOR INTELIGENTE
-- Exportado el: 2025-06-06 14:51:02
-- Base de datos: company_presupuestos
-- Total registros: 112
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET AUTOCOMMIT = 0;
START TRANSACTION;

-- =====================================================
-- ESTRUCTURA DE TABLAS
-- =====================================================

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `opciones`;
CREATE TABLE `opciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `precio_90_dias` decimal(10,2) DEFAULT 0.00,
  `precio_160_dias` decimal(10,2) DEFAULT 0.00,
  `precio_270_dias` decimal(10,2) DEFAULT 0.00,
  `descuento` decimal(5,2) DEFAULT 0.00,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_categoria` (`categoria_id`),
  KEY `idx_nombre` (`nombre`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS DE CATEGORÍAS
-- =====================================================

INSERT INTO `categorias` (`id`, `nombre`, `orden`) VALUES
(2, 'ADICIONALES', 1),
(1, 'ASCENSORES', 2),
(3, 'DESCUENTOS', 3);

-- =====================================================
-- DATOS DE OPCIONES
-- =====================================================

INSERT INTO `opciones` (`id`, `categoria_id`, `nombre`, `precio_90_dias`, `precio_160_dias`, `precio_270_dias`, `descuento`, `orden`) VALUES
(484, 1, 'Equipo Electromecanico 450kg carga util - 4 Paradas', 44417214.27, 34167087.90, 30750379.11, 0.00, 1),
(485, 1, 'Equipo Electromecanico 450kg carga util - 5 Paradas', 45736546.28, 35181958.68, 31663762.81, 0.00, 2),
(486, 1, 'Equipo Electromecanico 450kg carga util - 6 Paradas', 47078189.73, 36213992.10, 32592592.89, 0.00, 3),
(487, 1, 'Equipo Electromecanico 450kg carga util - 7 Paradas', 49832463.68, 38332664.37, 34499397.93, 0.00, 4),
(488, 1, 'Equipo Electromecanico 450kg carga util - 8 Paradas', 49662095.77, 38201612.13, 34381450.92, 0.00, 5),
(489, 1, 'Equipo Electromecanico 450kg carga util - 9 Paradas ', 50968246.33, 39206343.33, 35285709.00, 0.00, 6),
(490, 1, 'Equipo Electromecanico 450kg carga util - 10 Paradas', 52320368.53, 40246437.33, 36221793.60, 0.00, 7),
(492, 1, 'Equipo Electromecanico 450kg carga util - 11 Paradas ', 53523757.29, 41172120.99, 37054908.89, 0.00, 8),
(493, 1, 'Equipo Electromecanico 450kg carga util - 12 Paradas', 54858301.64, 42198693.57, 37978824.21, 0.00, 9),
(494, 1, 'Equipo Electromecanico 450kg carga util - 13 Paradas', 56200959.24, 43231507.11, 38908356.40, 0.00, 10),
(495, 1, 'Equipo Electromecanico 450kg carga util - 14 Paradas ', 57408403.34, 44160310.26, 39744279.23, 0.00, 11),
(496, 1, 'Equipo Electromecanico 450kg carga util - 15 Paradas ', 58749709.59, 45192084.30, 40672875.87, 0.00, 12),
(498, 1, 'Opción Gearless - 4 Paradas', 49758264.27, 38275587.90, 34448029.11, 0.00, 13),
(499, 1, 'Opción Gearless - 5 Paradas', 51077596.28, 39290458.68, 35361412.81, 0.00, 14),
(500, 1, 'Opción Gearless - 6 Paradas ', 52419239.73, 40322492.10, 36290242.89, 0.00, 15),
(501, 1, 'Opción Gearless - 7 Paradas ', 54208263.68, 41698664.37, 37528797.93, 0.00, 16),
(502, 1, 'Opción Gearless - 8 Paradas', 54037895.77, 41567612.13, 37410850.92, 0.00, 17),
(503, 1, 'Opción Gearless - 9 Paradas', 55344046.33, 42572343.33, 38315109.00, 0.00, 18),
(505, 1, 'Opción Gearless - 10 Paradas', 56696168.53, 43612437.33, 39251193.60, 0.00, 19),
(506, 1, 'Opción Gearless - 11 Paradas', 57899557.29, 44538120.99, 40084308.89, 0.00, 20),
(507, 1, 'Opción Gearless - 12 Paradas', 59234101.64, 45564693.57, 41008224.21, 0.00, 21),
(508, 1, 'Opción Gearless - 13 Paradas', 60576759.24, 46597507.11, 41937756.40, 0.00, 22),
(509, 1, 'Opción Gearless - 14 Paradas', 61784203.34, 47526310.26, 42773679.23, 0.00, 23),
(510, 1, 'Opción Gearless - 15Paradas', 63125509.59, 48558084.30, 43702275.87, 0.00, 24),
(511, 1, 'Hidraulico 450kg central 13hp piston 1 tramo - 2 Paradas', 46060297.57, 35430998.13, 31887898.32, 0.00, 25),
(512, 1, 'Hidraulico 450kg central 13hp piston 1 tramo - 3 Paradas', 47220549.95, 36323499.96, 32691149.96, 0.00, 26),
(513, 1, 'Hidraulico 450kg central 13hp piston 1 tramo - 4 Paradas', 48423743.08, 37249033.14, 33524129.83, 0.00, 27),
(514, 1, 'Hidraulico 450kg central 13hp piston 1 tramo - 5 Paradas', 49806274.52, 38312518.86, 34481266.97, 0.00, 28),
(515, 1, 'Hidraulico 450kg central 13hp piston 1 tramo - 6 Paradas', 50784944.07, 39065341.59, 35158807.43, 0.00, 29),
(516, 1, 'Hidraulico 450kg central 25lts 4hp - 2 Paradas', 38731631.80, 29793562.92, 26814206.63, 0.00, 30),
(517, 1, 'Hidraulico 450kg central 25lts 4hp - 3 Paradas', 39329526.95, 30253482.27, 27228134.04, 0.00, 31),
(527, 1, 'Hidraulico 450kg central 25lts 4hp - 4 Paradas', 40524994.22, 31173072.48, 28055765.23, 0.00, 32),
(518, 1, 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 2 Paradas Cabina Acero', 35014941.82, 26934570.63, 24241113.57, 0.00, 33),
(519, 1, 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 2 Paradas Cabina Chapa ', 34656931.88, 26659178.37, 23993260.53, 0.00, 34),
(520, 1, 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 3 Paradas Cabina Acero', 35616548.68, 27397345.14, 24657610.63, 0.00, 35),
(521, 1, 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 3 Paradas Cabina Chapa', 35254846.34, 27119112.57, 24407201.31, 0.00, 36),
(522, 1, 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 4 Paradas Cabina Acero', 36225485.01, 27865757.70, 25079181.93, 0.00, 37),
(523, 1, 'Montavehiculos - 2 Paradas ', 51062748.16, 39279037.05, 35351133.34, 0.00, 38),
(524, 1, 'Montavehiculos - 3 Paradas', 53111102.62, 40854694.32, 36769224.89, 0.00, 39),
(525, 1, 'Montavehiculos - 4 Paradas', 55270240.74, 42515569.80, 38264012.82, 0.00, 40),
(526, 1, 'Montavehiculos - Rampa - 2 Paradas', 51025780.38, 39250600.29, 35325540.26, 0.00, 41),
(528, 1, 'Montacargas - Maquina Tambor - Hasta 400 kg Puerta Manual	', 35885323.19, 27604094.76, 24843685.28, 0.00, 42),
(529, 1, 'Montacargas - Maquina Tambor - Hasta 1000 kg Puerta Manual	 (copia)', 44812033.70, 34470795.15, 31023715.64, 0.00, 43),
(530, 1, 'Salvaescaleras Modelo Simple H/ 1.80 M', 17033199.18, 13102460.91, 11792214.82, 0.00, 44),
(531, 1, 'Salvaescaleras Modelo Completo H/ 1.80 M ', 22329545.24, 17176573.26, 15458915.93, 0.00, 45),
(532, 1, 'Salvaescaleras Modelo Completo H/ 3 M ', 28094175.25, 21610904.04, 19449813.64, 0.00, 46),
(533, 1, 'Escaleras Mecanicas - Vidriado - Faldon Acero - H/ 4.50 angulo 35 - Peldaños 800 (Dolares)', 54450.00, 54450.00, 54450.00, 0.00, 47),
(534, 1, 'Montaplatos - 2 Paradas H/ 100 kg - 0,80x1,20	', 19622115.51, 15093935.01, 13584541.51, 0.00, 48),
(535, 1, 'Montaplatos - 2 Paradas H/ 100 kg - 0,80x1,20	 (copia)', 19622115.51, 15093935.01, 13584541.51, 0.00, 49),
(536, 1, 'Montaplatos - 3 Paradas H/ 100 kg - 0,80x1,20	', 23826902.81, 18328386.78, 16495548.10, 0.00, 50),
(537, 1, 'Giracoches - Estructura 1,80x1,80 2 Paradas	', 17286183.49, 13297064.22, 11967357.80, 0.00, 51),
(538, 1, 'Giracoches - Estructura 1,80x1,80 3 Paradas', 19622152.84, 15093963.72, 13584567.35, 0.00, 52),
(539, 1, 'Estructura - 2 Paradas', 15224688.77, 11711299.05, 10540169.15, 0.00, 53),
(540, 1, 'Estructura - Parada Adicional - C/U ', 7612343.74, 5855649.03, 5270084.13, 0.00, 54),
(541, 1, 'Perfil Divisorio por Unidad con Mano de Obra', 86640.84, 66646.80, 59982.12, 0.00, 55),
(542, 2, 'Ascensores Electromecanicos Adicional 750kg Maquina - Cabina 2,25m3', 3522824.31, 2709865.62, 2438879.06, 0.00, 1),
(543, 2, 'Ascensores Electromecanicos Adicional 1000kg Maquina Cabina 2,66', 4586194.90, 3527842.23, 3175058.01, 0.00, 2),
(410, 2, 'Ascensores Electromecanicos Adicional Acceso Cabina en acero', 1679329.08, 1291791.60, 1162612.44, 0.00, 7),
(440, 2, 'Ascensores Electromecanicos Adicional Acero Pisos', 370927.56, 285328.89, 256796.00, 0.00, 8),
(412, 2, 'Ascensores Electromecanicos Adicional Lateral Panoramico', 538859.18, 414507.06, 373056.35, 0.00, 9),
(442, 2, 'Ascensores Electromecanicos Adicional Cabina en chapa c/detalles restar', 348163.10, 267817.77, 241035.99, 0.00, 10),
(464, 2, 'Ascensores Electromecanicos Adicional PB y puerta de cabina en chapa restar', 505789.71, 389069.01, 350162.11, 0.00, 11),
(415, 2, 'Ascensores Electromecanicos Adicional Puertas de 900', 573923.49, 441479.61, 397331.65, 0.00, 12),
(444, 2, 'Ascensores Electromecanicos Adicional Puertas de 1000', 1149692.54, 884378.88, 795940.99, 0.00, 13),
(443, 2, 'Ascensores Electromecanicos Adicional Puertas de 1300', 1439424.56, 1107249.66, 996524.69, 0.00, 14),
(460, 2, 'Ascensores Electromecanicos Adicional Puertas de 1800', 1629501.59, 1253462.76, 1128116.48, 0.00, 15),
(436, 2, 'Ascensores Electromecanicos Adicional Puerta panoramica cabina + PB', 1799114.03, 1383933.87, 1245540.48, 0.00, 16),
(431, 2, 'Ascensores Electromecanicos Adicional Puerta panoramica pisos', 839663.25, 645894.81, 581305.33, 0.00, 17),
(416, 2, 'Ascensores Electromecanicos Adicional Tarjeta chip keypass', 18453.01, 14194.62, 12775.16, 0.00, 18),
(479, 2, 'Ascensores Electromecanicos Adicional Sistema keypass completo (un cod por piso)', 1088795.57, 837535.05, 753781.54, 0.00, 19),
(434, 2, 'Ascensores Electromecanicos Adicional Sistema keypass simple (un cod universal)', 479807.76, 369082.89, 332174.60, 0.00, 20),
(433, 2, 'Ascensores Electromecanicos Adicional Sistema UPS', 250975.30, 193057.92, 173752.13, 0.00, 21),
(478, 2, 'Ascensores Electromecanicos Adicional Indicador LED alfa num 1, 2', 101496.68, 78074.37, 70266.93, 0.00, 22),
(430, 2, 'Ascensores Electromecanicos Adicional Indicador LED alfa num 0, 8', 75660.16, 58200.12, 52380.11, 0.00, 23),
(429, 2, 'Ascensores Electromecanicos Adicional Indicador LCD color 5\"', 308134.83, 237026.79, 213324.11, 0.00, 24),
(428, 2, 'Ascensores Electromecanicos Adicional Balanza', 1037122.52, 797786.55, 718007.90, 0.00, 25),
(432, 2, 'Ascensores Electromecanicos Adicional Intercomunicador', 830435.46, 638796.51, 574916.86, 0.00, 26),
(435, 2, 'Ascensores Electromecanicos Adicional Fase I/ fase II bomberios', 415217.09, 319397.76, 287457.98, 0.00, 27),
(417, 2, 'Ascensores Electromecanicos Adicional Extension de panel cabina a 2,30', 830435.46, 638796.51, 574916.86, 0.00, 28),
(465, 2, 'Ascensores Electromecanicos Adicional Parada adicional chapa (precio por cada una)', 2089010.78, 1606931.37, 1446238.23, 0.00, 29),
(461, 2, 'Ascensores Hidraulicos adicional 2 tramos', 1504013.94, 1156933.80, 1353612.55, 0.00, 30),
(422, 2, 'Ascensores Hidraulicos adicional 750kg central y piston', 922706.93, 709774.56, 830436.24, 0.00, 31),
(454, 2, 'Ascensores Hidraulicos adicional cabina 2,25m3', 359855.50, 276811.92, 323869.95, 0.00, 32),
(423, 2, 'Ascensores Hidraulicos adicional 1000kg central y piston', 2242180.51, 1724754.24, 2017962.46, 0.00, 33),
(455, 2, 'Ascensores Hidraulicos adicional cabina 2,66', 636019.96, 489246.12, 572417.96, 0.00, 34),
(462, 2, 'Ascensores Hidraulicos adicionalpiso en acero', 370927.56, 285328.89, 333834.80, 0.00, 35),
(476, 2, 'Ascensores Hidraulicos adicional panoramico', 539781.96, 415216.89, 485803.76, 0.00, 36),
(419, 2, 'Ascensores Hidraulicos adicional restar cabina en chapa', 370927.56, 285328.89, 333834.80, 0.00, 37),
(450, 2, 'Ascensores Hidraulicos adicional restar puerta cabina y pb a chapa', 538860.46, 414508.05, 484974.42, 0.00, 38),
(451, 2, 'Ascensores Hidraulicos adicional restar sin puertas ext x4', 2229262.89, 1714817.61, 2006336.60, 0.00, 39),
(420, 2, 'Ascensores Hidraulicos adicional restar operador y dejar puerta plegadiza chapa', 658812.73, 506779.02, 592931.46, 0.00, 40),
(421, 2, 'Ascensores Hidraulicos adicional puertas de 900', 567260.69, 436354.38, 510534.62, 0.00, 41),
(452, 2, 'Ascensores Hidraulicos adicional puertas de 1000', 1132704.14, 871310.88, 1019433.73, 0.00, 42),
(418, 2, 'Ascensores Hidraulicos adicional puertas de 1200', 1418153.02, 1090886.94, 1276337.72, 0.00, 43),
(449, 2, 'Ascensores Hidraulicos adicional puertas de 1800', 1607239.06, 1236337.74, 1446515.15, 0.00, 44),
(445, 2, 'Ascensores Hidraulicos adicional acceso en cabina en acero', 1677482.24, 1290370.95, 1509734.02, 0.00, 45),
(448, 2, 'Ascensores Hidraulicos adicional puerta panoramica cabina + PB ', 1799114.03, 1383933.87, 1619202.63, 0.00, 46),
(446, 2, 'Ascensores Hidraulicos adicional puerta panoramica pisos', 839663.25, 645894.81, 755696.92, 0.00, 47),
(447, 2, 'Ascensores Hidraulicos adicional tarjeta chip keypass', 18453.01, 14194.62, 16607.71, 0.00, 48),
(463, 2, 'Ascensores Hidraulicos adicional sistema keypass completo (un cod por piso)', 1088795.57, 837535.05, 979916.01, 0.00, 49),
(477, 2, 'Ascensores Hidraulicos adicional sistema keypass simple (un cod universal)', 479807.76, 369082.89, 431826.98, 0.00, 50),
(480, 2, 'Ascensores Hidraulicos adicional sistema UPS', 250975.30, 193057.92, 225877.77, 0.00, 51),
(457, 2, 'Montacargas Adicional puerta guillotina - precio unitario', 2200304.70, 2200304.70, 2574356.50, 0.00, 53),
(426, 2, 'Montacargas Adicional puerta tijera - precio unitario', 2148669.67, 1652822.82, 1933802.70, 0.00, 54),
(458, 2, 'Salvaescaleras adicional en acero', 2817950.85, 2167654.50, 2536155.76, 0.00, 55),
(468, 3, 'Efectivo X', 0.00, 0.00, 0.00, 8.00, 1),
(482, 3, 'Transferencia', 0.00, 0.00, 0.00, 5.00, 2),
(481, 3, 'Cheques Electronicos(30-45) - financiacion en 6 cheques electronicos 0-15-30-45-60-90', 0.00, 0.00, 0.00, 2.00, 3),
(470, 3, 'Mejora de Presupuesto', 0.00, 0.00, 0.00, 5.00, 6);

-- =====================================================
-- FINALIZACIÓN
-- =====================================================

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- ESTADÍSTICAS DE EXPORTACIÓN
-- =====================================================
-- Categorías: 3
-- Opciones: 109
-- Total registros: 112
-- Archivo generado: 2025-06-06 14:51:02
-- =====================================================
