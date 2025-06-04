<?php
/**
 * Script para exportar estructura y datos de la base local
 * para replicar en Replit
 */

require_once 'sistema/config.php';

try {
    // Conectar a la base de datos local
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', 
        DB_USER, 
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<!DOCTYPE html>\n<html lang='es'>\n<head>\n";
    echo "<meta charset='UTF-8'>\n";
    echo "<title>Exportación para Replit</title>\n";
    echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .sql-block{background:#f5f5f5;padding:15px;border:1px solid #ddd;margin:10px 0;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>\n";
    echo "</head>\n<body>\n";
    
    echo "<h1>🚀 Exportación de Base de Datos para Replit</h1>\n";
    echo "<p class='info'>Copia y ejecuta estos scripts SQL en Replit para replicar tu base de datos.</p>\n";
    
    // ==========================================
    // 1. ESTRUCTURA DE TABLAS
    // ==========================================
    echo "<h2>📋 1. Estructura de Tablas</h2>\n";
    echo "<div class='sql-block'>\n";
    echo "<h3>Script SQL para crear las tablas:</h3>\n";
    echo "<textarea style='width:100%;height:400px;font-family:monospace;'>\n";
    
    echo "-- Crear base de datos\n";
    echo "CREATE DATABASE IF NOT EXISTS company_presupuestos;\n";
    echo "USE company_presupuestos;\n\n";
    
    echo "-- Tabla categorias\n";
    echo "CREATE TABLE IF NOT EXISTS categorias (\n";
    echo "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
    echo "    nombre VARCHAR(100) NOT NULL,\n";
    echo "    descripcion TEXT,\n";
    echo "    orden INT DEFAULT 0,\n";
    echo "    activo TINYINT(1) DEFAULT 1,\n";
    echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
    echo "    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\n";
    echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";
    
    echo "-- Tabla opciones\n";
    echo "CREATE TABLE IF NOT EXISTS opciones (\n";
    echo "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
    echo "    categoria_id INT NOT NULL,\n";
    echo "    nombre VARCHAR(255) NOT NULL,\n";
    echo "    descripcion TEXT,\n";
    echo "    precio DECIMAL(10,2) DEFAULT 0.00,\n";
    echo "    precio_90_dias DECIMAL(10,2) DEFAULT 0.00,\n";
    echo "    precio_160_dias DECIMAL(10,2) DEFAULT 0.00,\n";
    echo "    precio_270_dias DECIMAL(10,2) DEFAULT 0.00,\n";
    echo "    descuento DECIMAL(5,2) DEFAULT 0.00,\n";
    echo "    orden INT DEFAULT 0,\n";
    echo "    es_titulo TINYINT(1) DEFAULT 0,\n";
    echo "    activo TINYINT(1) DEFAULT 1,\n";
    echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
    echo "    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
    echo "    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE\n";
    echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";
    
    echo "-- Tabla configuracion\n";
    echo "CREATE TABLE IF NOT EXISTS configuracion (\n";
    echo "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
    echo "    clave VARCHAR(100) NOT NULL UNIQUE,\n";
    echo "    valor TEXT,\n";
    echo "    descripcion TEXT,\n";
    echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
    echo "    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\n";
    echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";
    
    echo "</textarea>\n";
    echo "</div>\n";
    
    // ==========================================
    // 2. DATOS DE CATEGORÍAS
    // ==========================================
    echo "<h2>📂 2. Datos de Categorías</h2>\n";
    
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY orden ASC, id ASC");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($categorias) {
        echo "<div class='sql-block'>\n";
        echo "<h3>Script SQL para insertar categorías:</h3>\n";
        echo "<textarea style='width:100%;height:200px;font-family:monospace;'>\n";
        echo "-- Insertar categorías\n";
        echo "INSERT INTO categorias (id, nombre, descripcion, orden, activo) VALUES\n";
        
        $categoriasSQL = [];
        foreach ($categorias as $cat) {
            $nombre = addslashes($cat['nombre']);
            $descripcion = addslashes($cat['descripcion'] ?? '');
            $orden = $cat['orden'] ?? 0;
            $activo = $cat['activo'] ?? 1;
            $categoriasSQL[] = "({$cat['id']}, '{$nombre}', '{$descripcion}', {$orden}, {$activo})";
        }
        echo implode(",\n", $categoriasSQL) . ";\n\n";
        echo "</textarea>\n";
        echo "</div>\n";
        
        echo "<p class='success'>✅ " . count($categorias) . " categorías encontradas</p>\n";
    } else {
        echo "<p class='error'>❌ No se encontraron categorías</p>\n";
    }
    
    // ==========================================
    // 3. DATOS DE OPCIONES
    // ==========================================
    echo "<h2>🛠️ 3. Datos de Opciones</h2>\n";
    
    $stmt = $pdo->query("SELECT * FROM opciones ORDER BY categoria_id ASC, orden ASC, id ASC");
    $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($opciones) {
        echo "<div class='sql-block'>\n";
        echo "<h3>Script SQL para insertar opciones (Parte 1 - Primeras 50):</h3>\n";
        echo "<textarea style='width:100%;height:300px;font-family:monospace;'>\n";
        echo "-- Insertar opciones (Parte 1)\n";
        echo "INSERT INTO opciones (id, categoria_id, nombre, descripcion, precio, precio_90_dias, precio_160_dias, precio_270_dias, descuento, orden, es_titulo, activo) VALUES\n";
        
        $opcionesSQL = [];
        $contador = 0;
        foreach ($opciones as $opcion) {
            if ($contador >= 50) break; // Dividir en partes para evitar scripts muy largos
            
            $nombre = addslashes($opcion['nombre']);
            $descripcion = addslashes($opcion['descripcion'] ?? '');
            $precio = $opcion['precio'] ?? 0;
            $precio_90 = $opcion['precio_90_dias'] ?? 0;
            $precio_160 = $opcion['precio_160_dias'] ?? 0;
            $precio_270 = $opcion['precio_270_dias'] ?? 0;
            $descuento = $opcion['descuento'] ?? 0;
            $orden = $opcion['orden'] ?? 0;
            $es_titulo = $opcion['es_titulo'] ?? 0;
            $activo = $opcion['activo'] ?? 1;
            
            $opcionesSQL[] = "({$opcion['id']}, {$opcion['categoria_id']}, '{$nombre}', '{$descripcion}', {$precio}, {$precio_90}, {$precio_160}, {$precio_270}, {$descuento}, {$orden}, {$es_titulo}, {$activo})";
            $contador++;
        }
        echo implode(",\n", $opcionesSQL) . ";\n\n";
        echo "</textarea>\n";
        echo "</div>\n";
        
        // Si hay más de 50 opciones, crear más bloques
        if (count($opciones) > 50) {
            $partes = array_chunk($opciones, 50);
            for ($i = 1; $i < count($partes); $i++) {
                echo "<div class='sql-block'>\n";
                echo "<h3>Script SQL para insertar opciones (Parte " . ($i + 1) . "):</h3>\n";
                echo "<textarea style='width:100%;height:300px;font-family:monospace;'>\n";
                echo "-- Insertar opciones (Parte " . ($i + 1) . ")\n";
                echo "INSERT INTO opciones (id, categoria_id, nombre, descripcion, precio, precio_90_dias, precio_160_dias, precio_270_dias, descuento, orden, es_titulo, activo) VALUES\n";
                
                $opcionesSQL = [];
                foreach ($partes[$i] as $opcion) {
                    $nombre = addslashes($opcion['nombre']);
                    $descripcion = addslashes($opcion['descripcion'] ?? '');
                    $precio = $opcion['precio'] ?? 0;
                    $precio_90 = $opcion['precio_90_dias'] ?? 0;
                    $precio_160 = $opcion['precio_160_dias'] ?? 0;
                    $precio_270 = $opcion['precio_270_dias'] ?? 0;
                    $descuento = $opcion['descuento'] ?? 0;
                    $orden = $opcion['orden'] ?? 0;
                    $es_titulo = $opcion['es_titulo'] ?? 0;
                    $activo = $opcion['activo'] ?? 1;
                    
                    $opcionesSQL[] = "({$opcion['id']}, {$opcion['categoria_id']}, '{$nombre}', '{$descripcion}', {$precio}, {$precio_90}, {$precio_160}, {$precio_270}, {$descuento}, {$orden}, {$es_titulo}, {$activo})";
                }
                echo implode(",\n", $opcionesSQL) . ";\n\n";
                echo "</textarea>\n";
                echo "</div>\n";
            }
        }
        
        echo "<p class='success'>✅ " . count($opciones) . " opciones encontradas</p>\n";
    } else {
        echo "<p class='error'>❌ No se encontraron opciones</p>\n";
    }
    
    // ==========================================
    // 4. CONFIGURACIÓN BÁSICA
    // ==========================================
    echo "<h2>⚙️ 4. Configuración Básica</h2>\n";
    echo "<div class='sql-block'>\n";
    echo "<h3>Script SQL para configuración inicial:</h3>\n";
    echo "<textarea style='width:100%;height:150px;font-family:monospace;'>\n";
    echo "-- Configuración básica\n";
    echo "INSERT INTO configuracion (clave, valor, descripcion) VALUES\n";
    echo "('empresa_nombre', 'Tu Empresa', 'Nombre de la empresa'),\n";
    echo "('empresa_telefono', '+54 11 1234-5678', 'Teléfono de contacto'),\n";
    echo "('empresa_email', 'info@tuempresa.com', 'Email de contacto'),\n";
    echo "('moneda_simbolo', '$', 'Símbolo de la moneda'),\n";
    echo "('iva_porcentaje', '21', 'Porcentaje de IVA'),\n";
    echo "('descuento_maximo', '15', 'Descuento máximo permitido');\n\n";
    echo "</textarea>\n";
    echo "</div>\n";
    
    // ==========================================
    // 5. RESUMEN Y INSTRUCCIONES
    // ==========================================
    echo "<h2>📋 5. Instrucciones para Replit</h2>\n";
    echo "<div style='background:#e8f4fd;padding:15px;border:1px solid #bee5eb;border-radius:5px;'>\n";
    echo "<h3>🔧 Pasos para configurar en Replit:</h3>\n";
    echo "<ol>\n";
    echo "<li><strong>Crear base de datos MySQL en Replit</strong></li>\n";
    echo "<li><strong>Ejecutar el script de estructura</strong> (Paso 1)</li>\n";
    echo "<li><strong>Ejecutar el script de categorías</strong> (Paso 2)</li>\n";
    echo "<li><strong>Ejecutar todos los scripts de opciones</strong> (Paso 3)</li>\n";
    echo "<li><strong>Ejecutar el script de configuración</strong> (Paso 4)</li>\n";
    echo "<li><strong>Configurar las variables de entorno</strong> en Replit:</li>\n";
    echo "<ul>\n";
    echo "<li><code>DB_HOST</code> = tu_host_mysql_replit</li>\n";
    echo "<li><code>DB_USER</code> = tu_usuario_mysql</li>\n";
    echo "<li><code>DB_PASS</code> = tu_password_mysql</li>\n";
    echo "<li><code>DB_NAME</code> = company_presupuestos</li>\n";
    echo "<li><code>DB_PORT</code> = 3306</li>\n";
    echo "</ul>\n";
    echo "</ol>\n";
    echo "</div>\n";
    
    // ==========================================
    // 6. ESTADÍSTICAS
    // ==========================================
    echo "<h2>📊 6. Estadísticas de Exportación</h2>\n";
    echo "<div style='background:#d4edda;padding:15px;border:1px solid #c3e6cb;border-radius:5px;'>\n";
    echo "<ul>\n";
    echo "<li>✅ <strong>Categorías:</strong> " . count($categorias) . "</li>\n";
    echo "<li>✅ <strong>Opciones:</strong> " . count($opciones) . "</li>\n";
    echo "<li>✅ <strong>Tablas:</strong> 3 (categorias, opciones, configuracion)</li>\n";
    echo "</ul>\n";
    echo "<p><strong>🎉 ¡Exportación completada exitosamente!</strong></p>\n";
    echo "</div>\n";
    
    echo "</body>\n</html>\n";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>\n";
}
?> 