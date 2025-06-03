<?php
/**
 * Admin de prueba simplificado
 * Para diagnosticar problemas básicos
 */

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Admin Test - Railway</title>";
echo "<meta charset='utf-8'>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo "</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🧪 Admin de Prueba - Railway</h1>";

try {
    echo "<div class='success'>✅ PHP funcionando correctamente</div>";
    echo "<div class='info'>🐘 Versión PHP: " . PHP_VERSION . "</div>";
    echo "<div class='info'>📁 Directorio actual: " . getcwd() . "</div>";
    
    // Verificar archivo de configuración
    $configPath = "../sistema/config.php";
    if (file_exists($configPath)) {
        echo "<div class='success'>✅ Archivo config.php encontrado</div>";
        
        try {
            require_once $configPath;
            echo "<div class='success'>✅ Config cargado correctamente</div>";
            
            // Mostrar configuración
            echo "<div class='info'>";
            echo "<strong>Configuración detectada:</strong><br>";
            echo "• Entorno: " . (defined('IS_RAILWAY') && IS_RAILWAY ? 'Railway' : 'Local') . "<br>";
            echo "• DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'No definido') . "<br>";
            echo "• DB_USER: " . (defined('DB_USER') ? DB_USER : 'No definido') . "<br>";
            echo "• DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'No definido') . "<br>";
            echo "• BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'No definido') . "<br>";
            echo "</div>";
            
            // Probar conexión a base de datos
            if (defined('DB_HOST')) {
                try {
                    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT ?? 3306);
                    
                    if ($conn->connect_error) {
                        throw new Exception("Error de conexión: " . $conn->connect_error);
                    }
                    
                    echo "<div class='success'>✅ Conexión a base de datos exitosa</div>";
                    
                    // Obtener versión de MySQL
                    $result = $conn->query("SELECT VERSION() as version");
                    if ($result) {
                        $row = $result->fetch_assoc();
                        echo "<div class='success'>✅ Versión MySQL: " . $row['version'] . "</div>";
                    }
                    
                    // Contar tablas
                    $result = $conn->query("SHOW TABLES");
                    if ($result) {
                        $tableCount = $result->num_rows;
                        echo "<div class='info'>📊 Tablas en la base de datos: $tableCount</div>";
                        
                        if ($tableCount > 0) {
                            echo "<div class='info'><strong>Tablas encontradas:</strong><br>";
                            while ($row = $result->fetch_array()) {
                                echo "• " . $row[0] . "<br>";
                            }
                            echo "</div>";
                        }
                    }
                    
                    $conn->close();
                    
                } catch (Exception $e) {
                    echo "<div class='error'>❌ Error de base de datos: " . $e->getMessage() . "</div>";
                }
            } else {
                echo "<div class='error'>❌ Constantes de base de datos no definidas</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error cargando config: " . $e->getMessage() . "</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Archivo config.php no encontrado en: $configPath</div>";
        echo "<div class='info'>📁 Archivos en directorio padre:</div>";
        echo "<div class='info'>";
        $files = scandir("../");
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                echo "• $file<br>";
            }
        }
        echo "</div>";
    }
    
    echo "<h2>🔗 Enlaces útiles</h2>";
    echo "<div class='info'>";
    echo "• <a href='../verify_config.php'>🔍 Verificación completa</a><br>";
    echo "• <a href='../create_config.php'>🔧 Recrear configuración</a><br>";
    echo "• <a href='../debug_admin.php'>🐛 Debug admin completo</a><br>";
    echo "• <a href='./'>👤 Panel admin principal</a><br>";
    echo "• <a href='../'>🏠 Página principal</a><br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error general: " . $e->getMessage() . "</div>";
} catch (Error $e) {
    echo "<div class='error'>❌ Error fatal: " . $e->getMessage() . "</div>";
}

echo "</div>";
echo "</body></html>";
?> 