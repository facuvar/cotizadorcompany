<?php
/**
 * Diagnóstico de páginas en blanco
 * Identifica errores que causan páginas vacías
 */

// Habilitar reporte de errores completo
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>🔍 DIAGNÓSTICO DE PÁGINAS EN BLANCO</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .debug { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto; white-space: pre-wrap; }
    .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; border-radius: 5px; }
</style>";

echo "<div class='container'>";

// Función para capturar errores
function captureErrors($file) {
    ob_start();
    
    // Capturar errores
    set_error_handler(function($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
    
    try {
        include $file;
        $output = ob_get_clean();
        restore_error_handler();
        return ['success' => true, 'output' => $output, 'error' => null];
    } catch (Exception $e) {
        ob_end_clean();
        restore_error_handler();
        return ['success' => false, 'output' => '', 'error' => $e->getMessage() . ' en línea ' . $e->getLine()];
    } catch (Error $e) {
        ob_end_clean();
        restore_error_handler();
        return ['success' => false, 'output' => '', 'error' => 'Error fatal: ' . $e->getMessage() . ' en línea ' . $e->getLine()];
    }
}

echo "<div class='step'>";
echo "<h2>📁 Verificando archivos problemáticos</h2>";

$problematicFiles = [
    'sistema/cotizador.php' => 'Cotizador principal',
    'admin/index.php' => 'Panel admin principal',
    'admin/dashboard.php' => 'Dashboard admin'
];

foreach ($problematicFiles as $file => $description) {
    echo "<h3>🔍 Analizando: $description ($file)</h3>";
    
    if (file_exists($file)) {
        echo "<div class='success'>✅ Archivo existe</div>";
        
        // Mostrar primeras líneas
        $content = file_get_contents($file);
        $lines = explode("\n", $content);
        $preview = array_slice($lines, 0, 10);
        
        echo "<div class='info'>📄 Primeras 10 líneas:</div>";
        echo "<div class='debug'>";
        foreach ($preview as $i => $line) {
            $lineNum = $i + 1;
            echo sprintf("%2d: %s\n", $lineNum, htmlspecialchars($line));
        }
        echo "</div>";
        
        // Intentar ejecutar el archivo
        echo "<div class='info'>🧪 Intentando ejecutar...</div>";
        $result = captureErrors($file);
        
        if ($result['success']) {
            $outputLength = strlen($result['output']);
            if ($outputLength > 0) {
                echo "<div class='success'>✅ Archivo ejecuta correctamente ($outputLength bytes de output)</div>";
                echo "<div class='info'>📄 Primeros 500 caracteres del output:</div>";
                echo "<div class='debug'>" . htmlspecialchars(substr($result['output'], 0, 500)) . "...</div>";
            } else {
                echo "<div class='warning'>⚠️ Archivo ejecuta pero no genera output</div>";
            }
        } else {
            echo "<div class='error'>❌ Error ejecutando archivo: " . $result['error'] . "</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Archivo no existe</div>";
    }
    
    echo "<hr>";
}
echo "</div>";

echo "<div class='step'>";
echo "<h2>🔧 Verificando configuración</h2>";

if (file_exists('sistema/config.php')) {
    echo "<div class='success'>✅ sistema/config.php existe</div>";
    
    $result = captureErrors('sistema/config.php');
    if ($result['success']) {
        echo "<div class='success'>✅ Config carga correctamente</div>";
        
        // Verificar constantes
        $constants = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME', 'BASE_URL'];
        foreach ($constants as $const) {
            if (defined($const)) {
                echo "<div class='success'>✅ $const: " . constant($const) . "</div>";
            } else {
                echo "<div class='error'>❌ $const no definida</div>";
            }
        }
    } else {
        echo "<div class='error'>❌ Error en config: " . $result['error'] . "</div>";
    }
} else {
    echo "<div class='error'>❌ sistema/config.php no existe</div>";
}
echo "</div>";

echo "<div class='step'>";
echo "<h2>🗄️ Verificando base de datos</h2>";

if (defined('DB_HOST')) {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT ?? 3306);
        
        if ($conn->connect_error) {
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }
        
        echo "<div class='success'>✅ Conexión a base de datos exitosa</div>";
        
        // Verificar tablas necesarias
        $requiredTables = ['categorias', 'opciones', 'plazos_entrega', 'presupuestos'];
        foreach ($requiredTables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "<div class='success'>✅ Tabla '$table' existe</div>";
            } else {
                echo "<div class='error'>❌ Tabla '$table' no existe</div>";
            }
        }
        
        $conn->close();
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error de base de datos: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='error'>❌ Constantes de base de datos no definidas</div>";
}
echo "</div>";

echo "<div class='step'>";
echo "<h2>🔗 Enlaces de prueba</h2>";
echo "<div class='info'>";
echo "• <a href='admin/test.php' target='_blank'>🧪 Admin de prueba</a><br>";
echo "• <a href='verify_config.php' target='_blank'>🔍 Verificación completa</a><br>";
echo "• <a href='create_config.php' target='_blank'>🔧 Recrear configuración</a><br>";
echo "• <a href='debug_admin.php' target='_blank'>🐛 Debug admin</a><br>";
echo "</div>";
echo "</div>";

echo "</div>";
?> 