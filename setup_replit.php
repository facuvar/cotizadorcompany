<?php
/**
 * Script de configuración automática para Replit
 * Detecta el entorno y configura la base de datos
 */

// Detectar si estamos en Replit
$isReplit = isset($_ENV['REPL_ID']) || 
            isset($_SERVER['REPL_ID']) || 
            getenv('REPL_ID') !== false ||
            strpos($_SERVER['HTTP_HOST'] ?? '', 'replit.dev') !== false;

echo "<!DOCTYPE html>\n<html lang='es'>\n<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Configuración Replit</title>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;background:#f8f9fa;} .container{max-width:800px;margin:0 auto;background:white;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);} .success{color:#28a745;background:#d4edda;padding:10px;border:1px solid #c3e6cb;border-radius:4px;margin:10px 0;} .error{color:#dc3545;background:#f8d7da;padding:10px;border:1px solid #f5c6cb;border-radius:4px;margin:10px 0;} .info{color:#0c5460;background:#d1ecf1;padding:10px;border:1px solid #bee5eb;border-radius:4px;margin:10px 0;} .code{background:#f8f9fa;padding:15px;border:1px solid #dee2e6;border-radius:4px;font-family:monospace;margin:10px 0;} .step{background:#e9ecef;padding:15px;margin:15px 0;border-left:4px solid #007bff;} h1{color:#343a40;} h2{color:#495057;border-bottom:2px solid #007bff;padding-bottom:10px;}</style>\n";
echo "</head>\n<body>\n";
echo "<div class='container'>\n";

echo "<h1>🚀 Configuración para Replit</h1>\n";

if ($isReplit) {
    echo "<div class='success'>✅ Entorno Replit detectado correctamente</div>\n";
} else {
    echo "<div class='info'>ℹ️ No se detectó entorno Replit. Configuración manual disponible.</div>\n";
}

// ==========================================
// 1. VERIFICAR VARIABLES DE ENTORNO
// ==========================================
echo "<h2>🔧 1. Variables de Entorno</h2>\n";

$requiredVars = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];
$envVars = [];

foreach ($requiredVars as $var) {
    $value = $_ENV[$var] ?? $_SERVER[$var] ?? getenv($var);
    $envVars[$var] = $value;
    
    if ($value) {
        echo "<div class='success'>✅ {$var}: " . (strlen($value) > 20 ? substr($value, 0, 20) . "..." : $value) . "</div>\n";
    } else {
        echo "<div class='error'>❌ {$var}: No configurada</div>\n";
    }
}

// ==========================================
// 2. CONFIGURACIÓN AUTOMÁTICA
// ==========================================
echo "<h2>⚙️ 2. Configuración Automática</h2>\n";

if ($isReplit) {
    echo "<div class='step'>\n";
    echo "<h3>📋 Pasos para configurar en Replit:</h3>\n";
    echo "<ol>\n";
    echo "<li><strong>Ir a la pestaña 'Secrets' en Replit</strong></li>\n";
    echo "<li><strong>Agregar las siguientes variables:</strong></li>\n";
    echo "<ul>\n";
    echo "<li><code>DB_HOST</code> = tu_host_mysql_replit</li>\n";
    echo "<li><code>DB_USER</code> = tu_usuario_mysql</li>\n";
    echo "<li><code>DB_PASS</code> = tu_password_mysql</li>\n";
    echo "<li><code>DB_NAME</code> = company_presupuestos</li>\n";
    echo "<li><code>DB_PORT</code> = 3306</li>\n";
    echo "</ul>\n";
    echo "<li><strong>Reiniciar el Repl</strong></li>\n";
    echo "</ol>\n";
    echo "</div>\n";
}

// ==========================================
// 3. SCRIPT DE CREACIÓN DE BASE DE DATOS
// ==========================================
echo "<h2>🗄️ 3. Script de Creación de Base de Datos</h2>\n";

echo "<div class='code'>\n";
echo "<h3>SQL para crear la estructura completa:</h3>\n";
echo "<textarea style='width:100%;height:500px;font-family:monospace;font-size:12px;'>\n";

echo "-- ==========================================\n";
echo "-- SCRIPT DE CREACIÓN COMPLETA PARA REPLIT\n";
echo "-- ==========================================\n\n";

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

echo "-- Datos básicos de categorías\n";
echo "INSERT INTO categorias (nombre, descripcion, orden, activo) VALUES\n";
echo "('ASCENSORES', 'Equipos de ascensores electromecánicos', 1, 1),\n";
echo "('ADICIONALES', 'Opciones adicionales para ascensores', 2, 1),\n";
echo "('DESCUENTOS', 'Formas de pago y descuentos', 3, 1);\n\n";

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
// 4. VERIFICACIÓN DE CONEXIÓN
// ==========================================
echo "<h2>🔍 4. Verificación de Conexión</h2>\n";

if (count(array_filter($envVars)) >= 3) {
    try {
        $host = $envVars['DB_HOST'] ?: 'localhost';
        $user = $envVars['DB_USER'] ?: 'root';
        $pass = $envVars['DB_PASS'] ?: '';
        $name = $envVars['DB_NAME'] ?: 'company_presupuestos';
        
        $pdo = new PDO(
            "mysql:host={$host};charset=utf8mb4", 
            $user, 
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "<div class='success'>✅ Conexión a MySQL exitosa</div>\n";
        
        // Verificar si la base de datos existe
        $stmt = $pdo->query("SHOW DATABASES LIKE '{$name}'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✅ Base de datos '{$name}' encontrada</div>\n";
            
            // Conectar a la base de datos específica
            $pdo = new PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4", 
                $user, 
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Verificar tablas
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($tables) > 0) {
                echo "<div class='success'>✅ Tablas encontradas: " . implode(', ', $tables) . "</div>\n";
                
                // Verificar datos
                if (in_array('categorias', $tables)) {
                    $stmt = $pdo->query("SELECT COUNT(*) FROM categorias");
                    $count = $stmt->fetchColumn();
                    echo "<div class='success'>✅ Categorías: {$count} registros</div>\n";
                }
                
                if (in_array('opciones', $tables)) {
                    $stmt = $pdo->query("SELECT COUNT(*) FROM opciones");
                    $count = $stmt->fetchColumn();
                    echo "<div class='success'>✅ Opciones: {$count} registros</div>\n";
                }
            } else {
                echo "<div class='error'>❌ No se encontraron tablas. Ejecuta el script SQL de arriba.</div>\n";
            }
        } else {
            echo "<div class='error'>❌ Base de datos '{$name}' no encontrada. Ejecuta el script SQL de arriba.</div>\n";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>\n";
    }
} else {
    echo "<div class='error'>❌ Variables de entorno insuficientes para probar la conexión</div>\n";
}

// ==========================================
// 5. CONFIGURACIÓN DEL ARCHIVO CONFIG.PHP
// ==========================================
echo "<h2>📝 5. Configuración del archivo config.php</h2>\n";

echo "<div class='info'>\n";
echo "<p>El archivo <code>sistema/config.php</code> ya está configurado para detectar automáticamente el entorno Replit.</p>\n";
echo "<p>Solo necesitas configurar las variables de entorno en Replit y el sistema se configurará automáticamente.</p>\n";
echo "</div>\n";

// ==========================================
// 6. PRÓXIMOS PASOS
// ==========================================
echo "<h2>🎯 6. Próximos Pasos</h2>\n";

echo "<div class='step'>\n";
echo "<h3>📋 Lista de verificación:</h3>\n";
echo "<ol>\n";
echo "<li>✅ Configurar variables de entorno en Replit</li>\n";
echo "<li>✅ Ejecutar el script SQL para crear la estructura</li>\n";
echo "<li>✅ Ejecutar <code>export_for_replit.php</code> en tu entorno local</li>\n";
echo "<li>✅ Copiar y ejecutar los scripts de datos generados</li>\n";
echo "<li>✅ Verificar que el cotizador funcione correctamente</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<div class='success'>\n";
echo "<h3>🎉 ¡Tu sistema estará listo para funcionar en Replit!</h3>\n";
echo "<p>Una vez completados estos pasos, tu cotizador funcionará exactamente igual que en tu entorno local.</p>\n";
echo "</div>\n";

echo "</div>\n"; // container
echo "</body>\n</html>\n";
?> 