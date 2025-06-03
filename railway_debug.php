<?php
/**
 * Script de diagnóstico específico para Railway
 * Ayuda a identificar problemas de conexión y configuración
 */

echo "<h1>🚂 DIAGNÓSTICO RAILWAY</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; white-space: pre-wrap; }
</style>";

echo "<div class='container'>";

// Paso 1: Información del entorno
echo "<h2>🌐 Información del entorno</h2>";
echo "<div class='info'>";
echo "<strong>Información básica:</strong><br>";
echo "• PHP Version: " . PHP_VERSION . "<br>";
echo "• Directorio actual: " . getcwd() . "<br>";
echo "• Usuario: " . get_current_user() . "<br>";
echo "• Memoria límite: " . ini_get('memory_limit') . "<br>";
echo "• Tiempo máximo: " . ini_get('max_execution_time') . "s<br>";
echo "</div>";

// Paso 2: Variables de entorno Railway
echo "<h2>🔧 Variables de entorno Railway</h2>";

$railwayVars = [
    'RAILWAY_ENVIRONMENT',
    'RAILWAY_PROJECT_ID',
    'RAILWAY_SERVICE_ID',
    'MYSQLHOST',
    'MYSQLUSER', 
    'MYSQLPASSWORD',
    'MYSQLDATABASE',
    'MYSQLPORT',
    'DB_HOST',
    'DB_USER',
    'DB_PASS',
    'DB_NAME',
    'DB_PORT'
];

echo "<div class='code'>";
foreach ($railwayVars as $var) {
    $value = $_ENV[$var] ?? $_SERVER[$var] ?? getenv($var);
    if ($value !== false && $value !== null) {
        // Ocultar contraseñas
        if (strpos($var, 'PASS') !== false || strpos($var, 'PASSWORD') !== false) {
            $displayValue = str_repeat('*', min(strlen($value), 20));
        } else {
            $displayValue = $value;
        }
        echo "✅ {$var}: {$displayValue}\n";
    } else {
        echo "❌ {$var}: No definida\n";
    }
}
echo "</div>";

// Paso 3: Cargar configuración
echo "<h2>📋 Cargar configuración</h2>";

try {
    if (file_exists('sistema/config.php')) {
        echo "<div class='success'>✅ sistema/config.php existe</div>";
        
        require_once 'sistema/config.php';
        echo "<div class='success'>✅ config.php cargado correctamente</div>";
        
        echo "<div class='info'>";
        echo "<strong>Configuración detectada:</strong><br>";
        echo "• IS_RAILWAY: " . (defined('IS_RAILWAY') && IS_RAILWAY ? 'true' : 'false') . "<br>";
        echo "• DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'No definido') . "<br>";
        echo "• DB_USER: " . (defined('DB_USER') ? DB_USER : 'No definido') . "<br>";
        echo "• DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'No definido') . "<br>";
        echo "• DB_PORT: " . (defined('DB_PORT') ? DB_PORT : 'No definido') . "<br>";
        echo "• BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'No definido') . "<br>";
        echo "</div>";
        
    } else {
        echo "<div class='error'>❌ sistema/config.php no existe</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error cargando config.php: " . $e->getMessage() . "</div>";
    exit;
}

// Paso 4: Probar conexión directa
echo "<h2>🔌 Probar conexión directa a MySQL</h2>";

if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
    try {
        echo "<div class='info'>Intentando conexión con:</div>";
        echo "<div class='code'>";
        echo "Host: " . DB_HOST . "\n";
        echo "User: " . DB_USER . "\n";
        echo "Database: " . DB_NAME . "\n";
        echo "Port: " . (defined('DB_PORT') ? DB_PORT : 3306) . "\n";
        echo "</div>";
        
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT ?? 3306);
        
        if ($mysqli->connect_error) {
            echo "<div class='error'>❌ Error de conexión: " . $mysqli->connect_error . "</div>";
        } else {
            echo "<div class='success'>✅ Conexión exitosa</div>";
            echo "<div class='info'>Versión del servidor: " . $mysqli->server_info . "</div>";
            
            // Probar consulta simple
            $result = $mysqli->query("SELECT 1 as test");
            if ($result) {
                echo "<div class='success'>✅ Consulta de prueba exitosa</div>";
            } else {
                echo "<div class='error'>❌ Error en consulta de prueba: " . $mysqli->error . "</div>";
            }
            
            $mysqli->close();
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Excepción en conexión: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='error'>❌ Constantes de base de datos no definidas</div>";
}

// Paso 5: Probar carga de db.php
echo "<h2>🗄️ Probar carga de db.php</h2>";

try {
    if (file_exists('sistema/includes/db.php')) {
        echo "<div class='success'>✅ sistema/includes/db.php existe</div>";
        
        ob_start();
        require_once 'sistema/includes/db.php';
        $output = ob_get_clean();
        
        if (!empty($output)) {
            echo "<div class='warning'>⚠️ db.php generó output:</div>";
            echo "<div class='code'>" . htmlspecialchars($output) . "</div>";
        } else {
            echo "<div class='success'>✅ db.php cargado sin output</div>";
        }
        
        // Probar Database class
        try {
            $db = Database::getInstance();
            echo "<div class='success'>✅ Database::getInstance() exitoso</div>";
            
            $conn = $db->getConnection();
            if ($conn && !$conn->connect_error) {
                echo "<div class='success'>✅ getConnection() exitoso</div>";
            } else {
                echo "<div class='error'>❌ getConnection() falló: " . ($conn ? $conn->connect_error : 'Conexión nula') . "</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error con Database class: " . $e->getMessage() . "</div>";
        }
        
    } else {
        echo "<div class='error'>❌ sistema/includes/db.php no existe</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error cargando db.php: " . $e->getMessage() . "</div>";
}

// Paso 6: Verificar tablas
echo "<h2>📊 Verificar tablas de la base de datos</h2>";

if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
    try {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT ?? 3306);
        
        if (!$mysqli->connect_error) {
            $tablas = ['categorias', 'opciones', 'plazos_entrega', 'presupuestos', 'fuente_datos'];
            
            foreach ($tablas as $tabla) {
                $result = $mysqli->query("SELECT COUNT(*) as count FROM `$tabla`");
                if ($result) {
                    $row = $result->fetch_assoc();
                    echo "<div class='success'>✅ Tabla '$tabla': " . $row['count'] . " registros</div>";
                } else {
                    echo "<div class='error'>❌ Tabla '$tabla': " . $mysqli->error . "</div>";
                }
            }
            
            $mysqli->close();
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error verificando tablas: " . $e->getMessage() . "</div>";
    }
}

// Paso 7: Enlaces útiles
echo "<h2>🔗 Enlaces útiles</h2>";
echo "<div class='info'>";
echo "<strong>Enlaces de diagnóstico:</strong><br>";
echo "<a href='admin/' target='_blank' style='color: blue; text-decoration: underline;'>🔐 Panel Admin</a><br>";
echo "<a href='sistema/cotizador.php' target='_blank' style='color: blue; text-decoration: underline;'>🚀 Cotizador</a><br>";
echo "<a href='switch_config.php' target='_blank' style='color: blue; text-decoration: underline;'>🔄 Cambiar Configuración</a><br>";
echo "</div>";

echo "</div>";
?> 