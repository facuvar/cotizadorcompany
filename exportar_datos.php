<?php
/**
 * Script para exportar datos de la base local a archivo SQL
 * Genera un archivo .sql completo para importar en Railway
 */

// Configuración local
$config = [
    'host' => 'localhost',
    'database' => 'company_presupuestos',
    'username' => 'root',
    'password' => ''
];

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Exportar Datos a SQL</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f5f5f5; padding: 15px; border-radius: 5px; font-family: monospace; margin: 10px 0; white-space: pre-wrap; }
        h1 { color: #333; text-align: center; }
        h2 { color: #555; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .download-btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
        .download-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>📤 Exportar Datos a Archivo SQL</h1>";
echo "<div class='info'>Fecha y hora: " . date('Y-m-d H:i:s') . "</div>";

try {
    // Conectar a base local
    echo "<h2>💻 Conectando a Base de Datos Local</h2>";
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "<div class='success'>✅ Conectado exitosamente</div>";

    // Verificar datos
    $stats = [];
    $stats['categorias'] = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    $stats['opciones'] = $pdo->query("SELECT COUNT(*) FROM opciones")->fetchColumn();
    
    echo "<div class='info'>📊 Datos encontrados: {$stats['categorias']} categorías, {$stats['opciones']} opciones</div>";

    // Generar archivo SQL
    echo "<h2>📝 Generando Archivo SQL</h2>";
    
    $sql_content = "-- =====================================================\n";
    $sql_content .= "-- DATOS DEL COTIZADOR INTELIGENTE\n";
    $sql_content .= "-- Exportado el: " . date('Y-m-d H:i:s') . "\n";
    $sql_content .= "-- Base de datos: {$config['database']}\n";
    $sql_content .= "-- Total registros: " . ($stats['categorias'] + $stats['opciones']) . "\n";
    $sql_content .= "-- =====================================================\n\n";

    // Configuración inicial
    $sql_content .= "SET FOREIGN_KEY_CHECKS = 0;\n";
    $sql_content .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
    $sql_content .= "SET AUTOCOMMIT = 0;\n";
    $sql_content .= "START TRANSACTION;\n\n";

    // Crear estructura de tablas
    $sql_content .= "-- =====================================================\n";
    $sql_content .= "-- ESTRUCTURA DE TABLAS\n";
    $sql_content .= "-- =====================================================\n\n";

    // Tabla categorias
    $sql_content .= "DROP TABLE IF EXISTS `categorias`;\n";
    $sql_content .= "CREATE TABLE `categorias` (\n";
    $sql_content .= "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
    $sql_content .= "  `nombre` varchar(255) NOT NULL,\n";
    $sql_content .= "  `orden` int(11) DEFAULT 0,\n";
    $sql_content .= "  PRIMARY KEY (`id`)\n";
    $sql_content .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

    // Tabla opciones
    $sql_content .= "DROP TABLE IF EXISTS `opciones`;\n";
    $sql_content .= "CREATE TABLE `opciones` (\n";
    $sql_content .= "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
    $sql_content .= "  `categoria_id` int(11) NOT NULL,\n";
    $sql_content .= "  `nombre` varchar(500) NOT NULL,\n";
    $sql_content .= "  `precio_90_dias` decimal(10,2) DEFAULT 0.00,\n";
    $sql_content .= "  `precio_160_dias` decimal(10,2) DEFAULT 0.00,\n";
    $sql_content .= "  `precio_270_dias` decimal(10,2) DEFAULT 0.00,\n";
    $sql_content .= "  `descuento` decimal(5,2) DEFAULT 0.00,\n";
    $sql_content .= "  `orden` int(11) DEFAULT 0,\n";
    $sql_content .= "  PRIMARY KEY (`id`),\n";
    $sql_content .= "  KEY `idx_categoria` (`categoria_id`),\n";
    $sql_content .= "  KEY `idx_nombre` (`nombre`(100))\n";
    $sql_content .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

    // Exportar datos de categorías
    $sql_content .= "-- =====================================================\n";
    $sql_content .= "-- DATOS DE CATEGORÍAS\n";
    $sql_content .= "-- =====================================================\n\n";

    $categorias = $pdo->query("SELECT * FROM categorias ORDER BY orden, id")->fetchAll();
    
    if (!empty($categorias)) {
        $sql_content .= "INSERT INTO `categorias` (`id`, `nombre`, `orden`) VALUES\n";
        $valores_categorias = [];
        
        foreach ($categorias as $categoria) {
            $id = $categoria['id'];
            $nombre = addslashes($categoria['nombre']);
            $orden = $categoria['orden'];
            $valores_categorias[] = "({$id}, '{$nombre}', {$orden})";
        }
        
        $sql_content .= implode(",\n", $valores_categorias) . ";\n\n";
    }

    echo "<div class='success'>✅ Categorías exportadas: " . count($categorias) . "</div>";

    // Exportar datos de opciones
    $sql_content .= "-- =====================================================\n";
    $sql_content .= "-- DATOS DE OPCIONES\n";
    $sql_content .= "-- =====================================================\n\n";

    $opciones = $pdo->query("SELECT * FROM opciones ORDER BY categoria_id, orden, id")->fetchAll();
    
    if (!empty($opciones)) {
        $sql_content .= "INSERT INTO `opciones` (`id`, `categoria_id`, `nombre`, `precio_90_dias`, `precio_160_dias`, `precio_270_dias`, `descuento`, `orden`) VALUES\n";
        $valores_opciones = [];
        
        foreach ($opciones as $opcion) {
            $id = $opcion['id'];
            $categoria_id = $opcion['categoria_id'];
            $nombre = addslashes($opcion['nombre']);
            $precio_90 = $opcion['precio_90_dias'];
            $precio_160 = $opcion['precio_160_dias'];
            $precio_270 = $opcion['precio_270_dias'];
            $descuento = $opcion['descuento'];
            $orden = $opcion['orden'];
            
            $valores_opciones[] = "({$id}, {$categoria_id}, '{$nombre}', {$precio_90}, {$precio_160}, {$precio_270}, {$descuento}, {$orden})";
        }
        
        $sql_content .= implode(",\n", $valores_opciones) . ";\n\n";
    }

    echo "<div class='success'>✅ Opciones exportadas: " . count($opciones) . "</div>";

    // Finalizar archivo SQL
    $sql_content .= "-- =====================================================\n";
    $sql_content .= "-- FINALIZACIÓN\n";
    $sql_content .= "-- =====================================================\n\n";
    $sql_content .= "COMMIT;\n";
    $sql_content .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";

    // Estadísticas finales
    $sql_content .= "-- =====================================================\n";
    $sql_content .= "-- ESTADÍSTICAS DE EXPORTACIÓN\n";
    $sql_content .= "-- =====================================================\n";
    $sql_content .= "-- Categorías: " . count($categorias) . "\n";
    $sql_content .= "-- Opciones: " . count($opciones) . "\n";
    $sql_content .= "-- Total registros: " . (count($categorias) + count($opciones)) . "\n";
    $sql_content .= "-- Archivo generado: " . date('Y-m-d H:i:s') . "\n";
    $sql_content .= "-- =====================================================\n";

    // Guardar archivo
    $filename = 'cotizador_datos_' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = __DIR__ . '/' . $filename;
    
    if (file_put_contents($filepath, $sql_content)) {
        $filesize = round(filesize($filepath) / 1024, 2);
        echo "<div class='success'>✅ Archivo SQL generado exitosamente</div>";
        echo "<div class='info'>📁 Archivo: {$filename} ({$filesize} KB)</div>";
        
        // Botón de descarga
        echo "<div style='text-align: center; margin: 20px 0;'>";
        echo "<a href='{$filename}' class='download-btn' download>📥 Descargar {$filename}</a>";
        echo "</div>";
        
        // Mostrar preview del contenido
        echo "<h2>👀 Preview del Archivo SQL</h2>";
        $preview = substr($sql_content, 0, 2000) . "\n\n... (archivo completo: " . strlen($sql_content) . " caracteres) ...";
        echo "<div class='code'>" . htmlspecialchars($preview) . "</div>";
        
        // Verificar funcionalidades del cotizador
        echo "<h2>🧠 Verificación de Funcionalidades</h2>";
        
        $electromecanicos = 0;
        $hidraulicos = 0;
        $montacargas = 0;
        $salvaescaleras = 0;
        $que_restan = 0;
        
        foreach ($opciones as $opcion) {
            if ($opcion['categoria_id'] == 2) {
                $nombre_lower = strtolower($opcion['nombre']);
                if (strpos($nombre_lower, 'electromecanico') !== false) $electromecanicos++;
                if (strpos($nombre_lower, 'hidraulico') !== false) $hidraulicos++;
                if (strpos($nombre_lower, 'montacargas') !== false) $montacargas++;
                if (strpos($nombre_lower, 'salvaescaleras') !== false) $salvaescaleras++;
                if (strpos($nombre_lower, 'restar') !== false) $que_restan++;
            }
        }
        
        echo "<div class='success'>✅ Filtrado inteligente: {$electromecanicos} electromecánicos + {$hidraulicos} hidráulicos + {$montacargas} montacargas + {$salvaescaleras} salvaescaleras</div>";
        echo "<div class='success'>✅ Adicionales que restan: {$que_restan} configurados</div>";
        echo "<div class='success'>✅ Datos completos para el cotizador inteligente</div>";
        
    } else {
        echo "<div class='error'>❌ Error al guardar el archivo SQL</div>";
    }

} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<h2>📋 Próximos Pasos</h2>";
echo "<div class='info'>
    <strong>1. Descarga el archivo SQL</strong> usando el botón de arriba<br>
    <strong>2. Sube el archivo a Railway</strong> (próximo script)<br>
    <strong>3. Ejecuta el importador</strong> en Railway<br>
    <strong>4. ¡Tu cotizador estará listo!</strong>
</div>";

echo "</div></body></html>";
?> 