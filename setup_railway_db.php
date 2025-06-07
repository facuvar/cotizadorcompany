<?php
/**
 * Script para configurar la base de datos en Railway
 * Detecta las credenciales automáticamente y configura las tablas
 */

// Detectar si estamos en Railway
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || 
             isset($_SERVER['RAILWAY_ENVIRONMENT']) || 
             getenv('RAILWAY_ENVIRONMENT') !== false ||
             isset($_ENV['MYSQLHOST']) ||
             isset($_SERVER['MYSQLHOST']);

echo "<!DOCTYPE html>\n<html lang='es'>\n<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Configuración Railway DB</title>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;background:#f8f9fa;} .container{max-width:900px;margin:0 auto;background:white;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);} .success{color:#28a745;background:#d4edda;padding:10px;border:1px solid #c3e6cb;border-radius:4px;margin:10px 0;} .error{color:#dc3545;background:#f8d7da;padding:10px;border:1px solid #f5c6cb;border-radius:4px;margin:10px 0;} .info{color:#0c5460;background:#d1ecf1;padding:10px;border:1px solid #bee5eb;border-radius:4px;margin:10px 0;} .code{background:#f8f9fa;padding:15px;border:1px solid #dee2e6;border-radius:4px;font-family:monospace;margin:10px 0;} .step{background:#e9ecef;padding:15px;margin:15px 0;border-left:4px solid #007bff;} h1{color:#343a40;} h2{color:#495057;border-bottom:2px solid #007bff;padding-bottom:10px;}</style>\n";
echo "</head>\n<body>\n";
echo "<div class='container'>\n";

echo "<h1>🚀 Configuración de Base de Datos en Railway</h1>\n";

if ($isRailway) {
    echo "<div class='success'>✅ Entorno Railway detectado correctamente</div>\n";
} else {
    echo "<div class='info'>ℹ️ Ejecutándose en entorno local. Las instrucciones son para Railway.</div>\n";
}

// ==========================================
// 1. VERIFICAR CREDENCIALES DE RAILWAY
// ==========================================
echo "<h2>🔧 1. Credenciales de Railway</h2>\n";

$railwayVars = ['MYSQLHOST', 'MYSQLUSER', 'MYSQLPASSWORD', 'MYSQLDATABASE', 'MYSQLPORT'];
$credentials = [];

foreach ($railwayVars as $var) {
    $value = $_ENV[$var] ?? $_SERVER[$var] ?? getenv($var);
    $credentials[$var] = $value;
    
    if ($value) {
        $displayValue = ($var === 'MYSQLPASSWORD') ? str_repeat('*', min(strlen($value), 20)) : $value;
        echo "<div class='success'>✅ {$var}: {$displayValue}</div>\n";
    } else {
        echo "<div class='error'>❌ {$var}: No encontrada</div>\n";
    }
}

// ==========================================
// 2. SCRIPT DE CREACIÓN COMPLETO
// ==========================================
echo "<h2>🗄️ 2. Script SQL Completo para Railway</h2>\n";

echo "<div class='step'>\n";
echo "<h3>📋 Instrucciones:</h3>\n";
echo "<ol>\n";
echo "<li>Copia el script SQL de abajo</li>\n";
echo "<li>Ve a tu base de datos MySQL en Railway</li>\n";
echo "<li>Ejecuta el script completo</li>\n";
echo "<li>Verifica que las tablas se crearon correctamente</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<div class='code'>\n";
echo "<h3>Script SQL completo:</h3>\n";
echo "<textarea style='width:100%;height:600px;font-family:monospace;font-size:11px;'>\n";

echo "-- ==========================================\n";
echo "-- CONFIGURACIÓN COMPLETA PARA RAILWAY\n";
echo "-- ==========================================\n\n";

echo "-- Usar la base de datos de Railway\n";
echo "-- (Railway ya tiene la base de datos creada)\n\n";

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

echo "-- ==========================================\n";
echo "-- DATOS BÁSICOS\n";
echo "-- ==========================================\n\n";

echo "-- Categorías básicas\n";
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
// 3. VERIFICACIÓN DE CONEXIÓN
// ==========================================
echo "<h2>🔍 3. Verificación de Conexión</h2>\n";

if (count(array_filter($credentials)) >= 4) {
    try {
        $host = $credentials['MYSQLHOST'];
        $user = $credentials['MYSQLUSER'];
        $pass = $credentials['MYSQLPASSWORD'];
        $name = $credentials['MYSQLDATABASE'];
        $port = $credentials['MYSQLPORT'] ?? 3306;
        
        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", 
            $user, 
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "<div class='success'>✅ Conexión a Railway MySQL exitosa</div>\n";
        
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
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>\n";
        echo "<div class='info'>💡 Asegúrate de que la base de datos MySQL esté creada en Railway</div>\n";
    }
} else {
    echo "<div class='error'>❌ Credenciales de Railway insuficientes</div>\n";
    echo "<div class='info'>💡 Asegúrate de haber agregado MySQL a tu proyecto Railway</div>\n";
}

// ==========================================
// 4. IMPORTAR DATOS COMPLETOS
// ==========================================
echo "<h2>📊 4. Importar Datos Completos</h2>\n";

echo "<div class='step'>\n";
echo "<h3>🔄 Para importar todos tus datos locales:</h3>\n";
echo "<ol>\n";
echo "<li>Abre en tu navegador local: <code>http://localhost/company-presupuestos-online-2/export_railway_data.html</code></li>\n";
echo "<li>Copia todos los scripts SQL generados</li>\n";
echo "<li>Ejecuta cada script en orden en tu base de datos Railway</li>\n";
echo "<li>Verifica que todos los datos se importaron correctamente</li>\n";
echo "</ol>\n";
echo "</div>\n";

// ==========================================
// 5. PRÓXIMOS PASOS
// ==========================================
echo "<h2>🎯 5. Próximos Pasos</h2>\n";

echo "<div class='step'>\n";
echo "<h3>📋 Lista de verificación:</h3>\n";
echo "<ol>\n";
echo "<li>✅ MySQL agregado a Railway</li>\n";
echo "<li>✅ Credenciales verificadas</li>\n";
echo "<li>✅ Script SQL ejecutado</li>\n";
echo "<li>✅ Datos importados (opcional)</li>\n";
echo "<li>✅ Aplicación desplegada</li>\n";
echo "<li>✅ Cotizador funcionando</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<div class='success'>\n";
echo "<h3>🎉 ¡Tu base de datos estará lista en Railway!</h3>\n";
echo "<p>Una vez ejecutado el script, tu sistema funcionará perfectamente en Railway.</p>\n";
echo "</div>\n";

// ==========================================
// 6. ENLACES ÚTILES
// ==========================================
echo "<h2>🔗 6. Enlaces Útiles</h2>\n";

echo "<div class='info'>\n";
echo "<ul>\n";
echo "<li><strong>Panel Railway:</strong> <a href='https://railway.app/dashboard' target='_blank'>https://railway.app/dashboard</a></li>\n";
echo "<li><strong>Datos exportados:</strong> <a href='export_railway_data.html' target='_blank'>export_railway_data.html</a></li>\n";
echo "<li><strong>Documentación:</strong> <a href='README.md' target='_blank'>README.md</a></li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "</div>\n"; // container
echo "</body>\n</html>\n";
?> 