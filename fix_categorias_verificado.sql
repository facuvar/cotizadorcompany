-- ========================================
-- FIX CATEGORÍAS RAILWAY - VERIFICADO
-- ========================================
-- Este archivo crea la tabla categorias con verificaciones
-- para asegurar que se cree correctamente en Railway

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- PASO 1: Eliminar tabla si existe
DROP TABLE IF EXISTS `categorias`;

-- PASO 2: Crear tabla categorias con estructura completa
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PASO 3: Insertar categorías básicas
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `orden`, `activo`) VALUES
(1, 'ASCENSORES ELECTROMECÁNICOS', 'Ascensores con motor eléctrico para edificios residenciales y comerciales', 1, 1),
(2, 'ASCENSORES HIDRÁULICOS', 'Ascensores con sistema hidráulico para edificios de baja altura', 2, 1),
(3, 'GIRACOCHES', 'Plataformas giratorias para vehículos', 3, 1),
(4, 'Opciones Adicionales', 'Accesorios y opciones adicionales para ascensores', 4, 1),
(5, 'Formas de Pago', 'Descuentos disponibles según forma de pago', 5, 1);

-- PASO 4: Verificar que la tabla se creó correctamente
SELECT 'VERIFICACIÓN: Tabla categorias creada' as mensaje;
SELECT COUNT(*) as total_registros FROM categorias;
SELECT * FROM categorias ORDER BY orden;

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- RESULTADO ESPERADO:
-- - Tabla categorias creada con 5 registros
-- - Verificación exitosa mostrando los datos
-- ======================================== 