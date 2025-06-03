<?php
/**
 * Script de verificación rápida de configuración
 */

echo "<h1>🔍 VERIFICACIÓN RÁPIDA DE CONFIGURACIÓN</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
</style>";

echo "<div class='container'>";

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>📁 Verificando archivos</h2>";

// Verificar config.php
if (file_exists('sistema/config.php')) {
    echo "<div class='success'>✅ sistema/config.php existe</div>";
    
    try {
        require_once 'sistema/config.php';
        echo "<div class='success'>✅ config.php cargado correctamente</div>";
        
        // Mostrar configuración detectada
        echo "<div class='info'>";
        echo "<strong>Configuración detectada:</strong><br>";
        echo "• Entorno: " . (defined('IS_RAILWAY') && IS_RAILWAY ? 'Railway' : 'Local') . "<br>";
        echo "• DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'No definido') . "<br>";
        echo "• DB_USER: " . (defined('DB_USER') ? DB_USER : 'No definido') . "<br>";
        echo "• DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'No definido') . "<br>";
        echo "• DB_PORT: " . (defined('DB_PORT') ? DB_PORT : 'No definido') . "<br>";
        echo "• BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'No definido') . "<br>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error cargando config.php: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='error'>❌ sistema/config.php NO existe</div>";
}

// Verificar db.php
if (file_exists('sistema/includes/db.php')) {
    echo "<div class='success'>✅ sistema/includes/db.php existe</div>";
    
    if (defined('DB_HOST')) {
        try {
            require_once 'sistema/includes/db.php';
            echo "<div class='success'>✅ db.php cargado correctamente</div>";
            
            // Probar conexión
            if (isset($pdo)) {
                echo "<div class='success'>✅ Conexión PDO establecida</div>";
                
                // Probar consulta simple
                $stmt = $pdo->query("SELECT VERSION() as version");
                $result = $stmt->fetch();
                echo "<div class='success'>✅ Versión MySQL: " . $result['version'] . "</div>";
                
                // Contar tablas
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll();
                echo "<div class='info'>📊 Tablas en la base de datos: " . count($tables) . "</div>";
                
                if (count($tables) > 0) {
                    echo "<div class='info'><strong>Tablas encontradas:</strong><br>";
                    foreach ($tables as $table) {
                        $tableName = array_values($table)[0];
                        echo "• $tableName<br>";
                    }
                    echo "</div>";
                }
                
            } else {
                echo "<div class='error'>❌ Variable $pdo no está disponible</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error con db.php: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='error'>❌ Constantes de DB no definidas, no se puede probar db.php</div>";
    }
} else {
    echo "<div class='error'>❌ sistema/includes/db.php NO existe</div>";
}

echo "<h2>🌐 URLs de prueba</h2>";

if (defined('BASE_URL')) {
    $baseUrl = BASE_URL;
    if (substr($baseUrl, -1) !== '/') $baseUrl .= '/';
    
    echo "<div class='info'>";
    echo "<strong>URLs para probar:</strong><br>";
    echo "• <a href='{$baseUrl}' target='_blank'>Página principal</a><br>";
    echo "• <a href='{$baseUrl}sistema/cotizador.php' target='_blank'>Cotizador</a><br>";
    echo "• <a href='{$baseUrl}admin/' target='_blank'>Panel Admin</a><br>";
    echo "• <a href='{$baseUrl}admin/test.php' target='_blank'>Admin de prueba</a><br>";
    echo "</div>";
}

echo "<h2>🔧 Información del sistema</h2>";
echo "<div class='info'>";
echo "• PHP: " . PHP_VERSION . "<br>";
echo "• Directorio: " . getcwd() . "<br>";
echo "• Usuario: " . get_current_user() . "<br>";
echo "• Memoria: " . ini_get('memory_limit') . "<br>";
echo "• Tiempo máximo: " . ini_get('max_execution_time') . "s<br>";
echo "</div>";

echo "</div>";
?> 