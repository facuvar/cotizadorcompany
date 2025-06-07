-- =====================================================
-- DATOS DEL COTIZADOR INTELIGENTE
-- Exportado el: 2025-06-03 19:28:26
-- Base de datos: company_presupuestos
-- Total registros: 114
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
(484, 1, 'Equipo Electromecanico 450kg carga util - 4 Paradas', 44865873.00, 34512210.00, 31060989.00, 0.00, 1),
(485, 1, 'Equipo Electromecanico 450kg carga util - 5 Paradas', 46198531.60, 35537332.00, 31983598.80, 0.00, 2),
(486, 1, 'Equipo Electromecanico 450kg carga util - 6 Paradas', 47553727.00, 36579790.00, 32921811.00, 0.00, 3),
(487, 1, 'Equipo Electromecanico 450kg carga util - 7 Paradas', 50335821.90, 38719863.00, 34847876.70, 0.00, 4),
(488, 1, 'Equipo Electromecanico 450kg carga util - 8 Paradas', 50163733.10, 38587487.00, 34728738.30, 0.00, 5),
(489, 1, 'Equipo Electromecanico 450kg carga util - 9 Paradas ', 51483077.10, 39602367.00, 35642130.30, 0.00, 6),
(490, 1, 'Equipo Electromecanico 450kg carga util - 10 Paradas', 52848857.10, 40652967.00, 36587670.30, 0.00, 7),
(492, 1, 'Equipo Electromecanico 450kg carga util - 11 Paradas ', 54064401.30, 41588001.00, 37429200.90, 0.00, 8),
(493, 1, 'Equipo Electromecanico 450kg carga util - 12 Paradas', 55412425.90, 42624943.00, 38362448.70, 0.00, 9),
(494, 1, 'Equipo Electromecanico 450kg carga util - 13 Paradas', 56768645.70, 43668189.00, 39301370.10, 0.00, 10),
(495, 1, 'Equipo Electromecanico 450kg carga util - 14 Paradas ', 57988286.20, 44606374.00, 40145736.60, 0.00, 11),
(496, 1, 'Equipo Electromecanico 450kg carga util - 15 Paradas ', 59343141.00, 45648570.00, 41083713.00, 0.00, 12),
(498, 1, 'Opción Gearless - 4 Paradas', 50260873.00, 38662210.00, 34795989.00, 0.00, 13),
(499, 1, 'Opción Gearless - 5 Paradas', 51593531.60, 39687332.00, 35718598.80, 0.00, 14),
(500, 1, 'Opción Gearless - 6 Paradas ', 52948727.00, 40729790.00, 36656811.00, 0.00, 15),
(501, 1, 'Opción Gearless - 7 Paradas ', 54755821.90, 42119863.00, 37907876.70, 0.00, 16),
(502, 1, 'Opción Gearless - 8 Paradas', 54583733.10, 41987487.00, 37788738.30, 0.00, 17),
(503, 1, 'Opción Gearless - 9 Paradas', 55903077.10, 43002367.00, 38702130.30, 0.00, 18),
(505, 1, 'Opción Gearless - 10 Paradas', 57268857.10, 44052967.00, 39647670.30, 0.00, 19),
(506, 1, 'Opción Gearless - 11 Paradas', 58484401.30, 44988001.00, 40489200.90, 0.00, 20),
(507, 1, 'Opción Gearless - 12 Paradas', 59832425.90, 46024943.00, 41422448.70, 0.00, 21),
(508, 1, 'Opción Gearless - 13 Paradas', 61188645.70, 47068189.00, 42361370.10, 0.00, 22),
(509, 1, 'Opción Gearless - 14 Paradas', 62408286.20, 48006374.00, 43205736.60, 0.00, 23),
(510, 1, 'Opción Gearless - 15Paradas', 63763141.00, 49048570.00, 44143713.00, 0.00, 24),
(511, 1, 'Hidraulico 450kg central 13hp piston 1 tramo - 2 Paradas', 46525553.10, 35788887.00, 32209998.30, 0.00, 25),
(512, 1, 'Hidraulico 450kg central 13hp piston 1 tramo - 3 Paradas', 47697525.20, 36690404.00, 33021363.60, 0.00, 26),
(513, 1, 'Hidraulico 450kg central 13hp piston 1 tramo - 4 Paradas', 48912871.80, 37625286.00, 33862757.40, 0.00, 27),
(514, 1, 'Hidraulico 450kg central 13hp piston 1 tramo - 5 Paradas', 50309368.20, 38699514.00, 34829562.60, 0.00, 28),
(515, 1, 'Hidraulico 450kg central 13hp piston 1 tramo - 6 Paradas', 51297923.30, 39459941.00, 35513946.90, 0.00, 29),
(516, 1, 'Hidraulico 450kg central 25lts 4hp - 2 Paradas', 39122860.40, 30094508.00, 27085057.20, 0.00, 30),
(517, 1, 'Hidraulico 450kg central 25lts 4hp - 3 Paradas', 39726794.90, 30559073.00, 27503165.70, 0.00, 31),
(527, 1, 'Hidraulico 450kg central 25lts 4hp - 4 Paradas', 40934337.60, 31487952.00, 28339156.80, 0.00, 32),
(518, 1, 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 2 Paradas Cabina Acero', 35368628.10, 27206637.00, 24485973.30, 0.00, 33),
(519, 1, 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 2 Paradas Cabina Chapa ', 35007001.90, 26928463.00, 24235616.70, 0.00, 34),
(520, 1, 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 3 Paradas Cabina Acero', 35976311.80, 27674086.00, 24906677.40, 0.00, 35),
(521, 1, 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 3 Paradas Cabina Chapa', 35610955.90, 27393043.00, 24653738.70, 0.00, 36),
(522, 1, 'Domiciliario Puerta Plegadiza Cabina ext. sin puertas 4 Paradas Cabina Acero', 36591399.00, 28147230.00, 25332507.00, 0.00, 37),
(523, 1, 'Montavehiculos - 2 Paradas ', 51578533.50, 39675795.00, 35708215.50, 0.00, 38),
(524, 1, 'Montavehiculos - 3 Paradas', 53647578.40, 41267368.00, 37140631.20, 0.00, 39),
(525, 1, 'Montavehiculos - 4 Paradas', 55828526.00, 42945020.00, 38650518.00, 0.00, 40),
(526, 1, 'Montavehiculos - Rampa - 2 Paradas', 51541192.30, 39647071.00, 35682363.90, 0.00, 41),
(528, 1, 'Montacargas - Maquina Tambor - Hasta 400 kg Puerta Manual	', 36247801.20, 27882924.00, 25094631.60, 0.00, 42),
(529, 1, 'Montacargas - Maquina Tambor - Hasta 1000 kg Puerta Manual	 (copia)', 45264680.50, 34818985.00, 31337086.50, 0.00, 43),
(530, 1, 'Salvaescaleras Modelo Simple H/ 1.80 M', 17205251.70, 13234809.00, 11911328.10, 0.00, 44),
(531, 1, 'Salvaescaleras Modelo Completo H/ 1.80 M ', 22555096.20, 17350074.00, 15615066.60, 0.00, 45),
(532, 1, 'Salvaescaleras Modelo Completo H/ 3 M ', 28377954.80, 21829196.00, 19646276.40, 0.00, 46),
(533, 1, 'Escaleras Mecanicas - Vidriado - Faldon Acero - H/ 4.50 angulo 35 - Peldaños 800 (Dolares)', 55000.00, 55000.00, 55000.00, 0.00, 47),
(534, 1, 'Montaplatos - 2 Paradas H/ 100 kg - 0,80x1,20	', 19820318.70, 15246399.00, 13721759.10, 0.00, 48),
(535, 1, 'Montaplatos - 2 Paradas H/ 100 kg - 0,80x1,20	 (copia)', 19820318.70, 15246399.00, 13721759.10, 0.00, 49),
(536, 1, 'Montaplatos - 3 Paradas H/ 100 kg - 0,80x1,20	', 24067578.60, 18513522.00, 16662169.80, 0.00, 50),
(537, 1, 'Giracoches - Estructura 1,80x1,80 2 Paradas	', 17460791.40, 13431378.00, 12088240.20, 0.00, 51),
(538, 1, 'Giracoches - Estructura 1,80x1,80 3 Paradas', 19820356.40, 15246428.00, 13721785.20, 0.00, 52),
(539, 1, 'Estructura - 2 Paradas', 15378473.50, 11829595.00, 10646635.50, 0.00, 53),
(540, 1, 'Estructura - Parada Adicional - C/U ', 7689236.10, 5914797.00, 5323317.30, 0.00, 54),
(541, 1, 'Perfil Divisorio por Unidad con Mano de Obra', 87516.00, 67320.00, 60588.00, 0.00, 55),
(409, 2, 'Ascensores Electromecanicos Adicional 750kg Maquina', 3194919.00, 2457630.00, 2211867.00, 0.00, 1),
(413, 2, 'Ascensores Electromecanicos Adicional Cabina 2,25m3', 363490.40, 279608.00, 251647.20, 0.00, 2),
(453, 2, 'Ascensores Electromecanicos Adicional 1000kg Maquina', 3991286.00, 3070220.00, 2763198.00, 0.00, 3),
(414, 2, 'Ascensores Electromecanicos Adicional Cabina 2,66', 641234.10, 493257.00, 443931.30, 0.00, 4),
(410, 2, 'Ascensores Electromecanicos Adicional Acceso Cabina en acero', 1696292.00, 1304840.00, 1174356.00, 0.00, 5),
(440, 2, 'Ascensores Electromecanicos Adicional Acero Pisos', 374674.30, 288211.00, 259389.90, 0.00, 6),
(412, 2, 'Ascensores Electromecanicos Adicional Lateral Panoramico', 544302.20, 418694.00, 376824.60, 0.00, 7),
(442, 2, 'Ascensores Electromecanicos Adicional Cabina en chapa c/detalles restar', 351679.90, 270523.00, 243470.70, 0.00, 8),
(464, 2, 'Ascensores Electromecanicos Adicional PB y puerta de cabina en chapa restar', 510898.70, 392999.00, 353699.10, 0.00, 9),
(415, 2, 'Ascensores Electromecanicos Adicional Puertas de 900', 579720.70, 445939.00, 401345.10, 0.00, 10),
(444, 2, 'Ascensores Electromecanicos Adicional Puertas de 1000', 1161305.60, 893312.00, 803980.80, 0.00, 11),
(443, 2, 'Ascensores Electromecanicos Adicional Puertas de 1300', 1453964.20, 1118434.00, 1006590.60, 0.00, 12),
(460, 2, 'Ascensores Electromecanicos Adicional Puertas de 1800', 1645961.20, 1266124.00, 1139511.60, 0.00, 13),
(436, 2, 'Ascensores Electromecanicos Adicional Puerta panoramica cabina + PB', 1817286.90, 1397913.00, 1258121.70, 0.00, 14),
(431, 2, 'Ascensores Electromecanicos Adicional Puerta panoramica pisos', 848144.70, 652419.00, 587177.10, 0.00, 15),
(416, 2, 'Ascensores Electromecanicos Adicional Tarjeta chip keypass', 18639.40, 14338.00, 12904.20, 0.00, 16),
(479, 2, 'Ascensores Electromecanicos Adicional Sistema keypass completo (un cod por piso)', 1099793.50, 845995.00, 761395.50, 0.00, 17),
(434, 2, 'Ascensores Electromecanicos Adicional Sistema keypass simple (un cod universal)', 484654.30, 372811.00, 335529.90, 0.00, 18),
(433, 2, 'Ascensores Electromecanicos Adicional Sistema UPS', 253510.40, 195008.00, 175507.20, 0.00, 19),
(478, 2, 'Ascensores Electromecanicos Adicional Indicador LED alfa num 1, 2', 102521.90, 78863.00, 70976.70, 0.00, 20),
(430, 2, 'Ascensores Electromecanicos Adicional Indicador LED alfa num 0, 8', 76424.40, 58788.00, 52909.20, 0.00, 21),
(429, 2, 'Ascensores Electromecanicos Adicional Indicador LCD color 5\"', 311247.30, 239421.00, 215478.90, 0.00, 22),
(428, 2, 'Ascensores Electromecanicos Adicional Balanza', 1047598.50, 805845.00, 725260.50, 0.00, 23),
(432, 2, 'Ascensores Electromecanicos Adicional Intercomunicador', 838823.70, 645249.00, 580724.10, 0.00, 24),
(435, 2, 'Ascensores Electromecanicos Adicional Fase I/ fase II bomberios', 419411.20, 322624.00, 290361.60, 0.00, 25),
(417, 2, 'Ascensores Electromecanicos Adicional Extension de panel cabina a 2,30', 838823.70, 645249.00, 580724.10, 0.00, 26),
(465, 2, 'Ascensores Electromecanicos Adicional Parada adicional chapa (precio por cada una)', 2110111.90, 1623163.00, 1460846.70, 0.00, 27),
(461, 2, 'Ascensores Hidraulicos adicional 2 tramos', 1519206.00, 1168620.00, 1367285.40, 0.00, 28),
(422, 2, 'Ascensores Hidraulicos adicional 750kg central y piston', 932027.20, 716944.00, 838824.48, 0.00, 29),
(454, 2, 'Ascensores Hidraulicos adicional cabina 2,25m3', 363490.40, 279608.00, 327141.36, 0.00, 30),
(423, 2, 'Ascensores Hidraulicos adicional 1000kg central y piston', 2264828.80, 1742176.00, 2038345.92, 0.00, 31),
(455, 2, 'Ascensores Hidraulicos adicional cabina 2,66', 642444.40, 494188.00, 578199.96, 0.00, 32),
(462, 2, 'Ascensores Hidraulicos adicionalpiso en acero', 374674.30, 288211.00, 337206.87, 0.00, 33),
(476, 2, 'Ascensores Hidraulicos adicional panoramico', 545234.30, 419411.00, 490710.87, 0.00, 34),
(419, 2, 'Ascensores Hidraulicos adicional restar cabina en chapa', 374674.30, 288211.00, 337206.87, 0.00, 35),
(450, 2, 'Ascensores Hidraulicos adicional restar puerta cabina y pb a chapa', 544303.50, 418695.00, 489873.15, 0.00, 36),
(451, 2, 'Ascensores Hidraulicos adicional restar sin puertas ext x4', 2251780.70, 1732139.00, 2026602.63, 0.00, 37),
(420, 2, 'Ascensores Hidraulicos adicional restar operador y dejar puerta plegadiza chapa', 665467.40, 511898.00, 598920.66, 0.00, 38),
(421, 2, 'Ascensores Hidraulicos adicional puertas de 900', 572990.60, 440762.00, 515691.54, 0.00, 39),
(452, 2, 'Ascensores Hidraulicos adicional puertas de 1000', 1144145.60, 880112.00, 1029731.04, 0.00, 40),
(418, 2, 'Ascensores Hidraulicos adicional puertas de 1200', 1432477.80, 1101906.00, 1289230.02, 0.00, 41),
(449, 2, 'Ascensores Hidraulicos adicional puertas de 1800', 1623473.80, 1248826.00, 1461126.42, 0.00, 42),
(445, 2, 'Ascensores Hidraulicos adicional acceso en cabina en acero', 1694426.50, 1303405.00, 1524983.85, 0.00, 43),
(448, 2, 'Ascensores Hidraulicos adicional puerta panoramica cabina + PB ', 1817286.90, 1397913.00, 1635558.21, 0.00, 44),
(446, 2, 'Ascensores Hidraulicos adicional puerta panoramica pisos', 848144.70, 652419.00, 763330.23, 0.00, 45),
(447, 2, 'Ascensores Hidraulicos adicional tarjeta chip keypass', 18639.40, 14338.00, 16775.46, 0.00, 46),
(463, 2, 'Ascensores Hidraulicos adicional sistema keypass completo (un cod por piso)', 1099793.50, 845995.00, 989814.15, 0.00, 47),
(477, 2, 'Ascensores Hidraulicos adicional sistema keypass simple (un cod universal)', 484654.30, 372811.00, 436188.87, 0.00, 48),
(480, 2, 'Ascensores Hidraulicos adicional sistema UPS', 253510.40, 195008.00, 228159.36, 0.00, 49),
(457, 2, 'Montacargas Adicional puerta guillotina - precio unitario', 2222530.00, 2222530.00, 2600360.10, 0.00, 51),
(426, 2, 'Montacargas Adicional puerta tijera - precio unitario', 2170373.40, 1669518.00, 1953336.06, 0.00, 52),
(458, 2, 'Salvaescaleras adicional en acero', 2846415.00, 2189550.00, 2561773.50, 0.00, 53),
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
-- Opciones: 111
-- Total registros: 114
-- Archivo generado: 2025-06-03 19:28:26
-- =====================================================
