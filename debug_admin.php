<?php
/**
 * Script de diagnóstico específico para el panel admin
 */

echo "<h1>🔍 DIAGNÓSTICO DEL PANEL ADMIN</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
</style>";

echo "<div class='container'>";

// Información del entorno
echo "<h2>🌐 Información del entorno</h2>";
echo "<div class='info'>";
echo "<strong>Información básica:</strong><br>";
echo "• PHP Version: " . PHP_VERSION . "<br>";
echo "• Directorio actual: " . getcwd() . "<br>";
echo "• Usuario: " . get_current_user() . "<br>";
echo "• Memoria límite: " . ini_get('memory_limit') . "<br>";
echo "• Tiempo máximo: " . ini_get('max_execution_time') . "s<br>";
echo "</div>";

// Paso 1: Verificar archivos del admin
echo "<h2>📁 Paso 1: Verificar archivos del admin</h2>";

$archivosAdmin = [
    'admin/index.php',
    'admin/dashboard.php',
    'admin/test.php'
];

foreach ($archivosAdmin as $archivo) {
    if (file_exists($archivo)) {
        echo "<div class='success'>✅ " . $archivo . " existe (" . filesize($archivo) . " bytes)</div>";
    } else {
        echo "<div class='error'>❌ " . $archivo . " no existe</div>";
    }
}

// Paso 2: Verificar config.php
echo "<h2>📋 Paso 2: Verificar config.php</h2>";
try {
    if (file_exists('sistema/config.php')) {
        echo "<div class='success'>✅ sistema/config.php existe</div>";
        
        require_once 'sistema/config.php';
        echo "<div class='success'>✅ config.php cargado correctamente</div>";
        
        echo "<div class='info'>";
        echo "<strong>Configuración detectada:</strong><br>";
        echo "• Entorno: " . (defined('IS_RAILWAY') && IS_RAILWAY ? 'Railway' : 'Local') . "<br>";
        echo "• ADMIN_USER: " . (defined('ADMIN_USER') ? ADMIN_USER : 'No definido') . "<br>";
        echo "• ADMIN_PASS definido: " . (defined('ADMIN_PASS') ? 'Sí' : 'No') . "<br>";
        echo "• BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'No definido') . "<br>";
        echo "</div>";
        
    } else {
        echo "<div class='error'>❌ sistema/config.php no existe</div>";
        echo "<div class='warning'>⚠️ Necesitas ejecutar create_config.php primero</div>";
        echo "<div class='info'><a href='create_config.php' style='color: blue;'>🔧 Crear configuración</a></div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error cargando config.php: " . $e->getMessage() . "</div>";
    exit;
}

// Paso 3: Probar admin/index.php paso a paso
echo "<h2>🧪 Paso 3: Simular admin/index.php</h2>";

try {
    echo "<div class='info'>Simulando el código de admin/index.php...</div>";
    
    // Simular session_start
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
        echo "<div class='success'>✅ Sesión iniciada correctamente</div>";
    } else {
        echo "<div class='warning'>⚠️ Sesión ya iniciada o headers enviados</div>";
    }
    
    // Verificar ruta de config
    $configPath = __DIR__ . '/admin/../sistema/config.php';
    echo "<div class='info'>Ruta de config calculada: " . $configPath . "</div>";
    
    if (file_exists($configPath)) {
        echo "<div class='success'>✅ Config encontrado en ruta calculada</div>";
    } else {
        echo "<div class='error'>❌ Config NO encontrado en ruta calculada</div>";
    }
    
    // Verificar si el usuario está logueado
    $isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    echo "<div class='info'>Usuario logueado: " . ($isLoggedIn ? 'Sí' : 'No') . "</div>";
    
    // Verificar credenciales por defecto
    if (defined('ADMIN_USER') && defined('ADMIN_PASS')) {
        echo "<div class='success'>✅ Credenciales de admin definidas</div>";
        echo "<div class='info'>Usuario: " . ADMIN_USER . "</div>";
        
        // Verificar si la contraseña es válida
        $testPassword = 'admin123';
        if (password_verify($testPassword, ADMIN_PASS)) {
            echo "<div class='success'>✅ Contraseña 'admin123' es válida</div>";
        } else {
            echo "<div class='error'>❌ Contraseña 'admin123' NO es válida</div>";
        }
    } else {
        echo "<div class='error'>❌ Credenciales de admin NO definidas</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error simulando admin: " . $e->getMessage() . "</div>";
}

// Paso 4: Probar carga directa de admin/index.php
echo "<h2>📄 Paso 4: Probar contenido de admin/index.php</h2>";

try {
    if (file_exists('admin/index.php')) {
        echo "<div class='info'>Leyendo las primeras líneas de admin/index.php...</div>";
        
        $content = file_get_contents('admin/index.php');
        $lines = explode("\n", $content);
        
        echo "<div class='code'>";
        echo "<strong>Primeras 10 líneas:</strong><br>";
        for ($i = 0; $i < min(10, count($lines)); $i++) {
            echo ($i + 1) . ": " . htmlspecialchars($lines[$i]) . "<br>";
        }
        echo "</div>";
        
        // Verificar si hay errores de sintaxis
        $tempFile = tempnam(sys_get_temp_dir(), 'admin_check');
        file_put_contents($tempFile, $content);
        
        $output = [];
        $return_var = 0;
        exec("php -l " . escapeshellarg($tempFile) . " 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            echo "<div class='success'>✅ Sintaxis PHP válida</div>";
        } else {
            echo "<div class='error'>❌ Error de sintaxis PHP:</div>";
            echo "<div class='code'>" . implode("<br>", $output) . "</div>";
        }
        
        unlink($tempFile);
        
    } else {
        echo "<div class='error'>❌ admin/index.php no existe</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error verificando admin/index.php: " . $e->getMessage() . "</div>";
}

// Paso 5: Crear admin simplificado de prueba
echo "<h2>🛠️ Paso 5: Crear admin simplificado</h2>";

$adminSimple = '<?php
// Admin simplificado para pruebas
echo "<!DOCTYPE html>";
echo "<html><head><title>Admin Test</title></head><body>";
echo "<h1>🧪 ADMIN DE PRUEBA FUNCIONANDO</h1>";
echo "<p>Si ves esto, PHP está funcionando correctamente.</p>";
echo "<p>Fecha: " . date("Y-m-d H:i:s") . "</p>";
echo "<p>Directorio: " . __DIR__ . "</p>";

// Verificar config
if (file_exists(__DIR__ . "/../sistema/config.php")) {
    echo "<p>✅ Config existe</p>";
    require_once __DIR__ . "/../sistema/config.php";
    echo "<p>✅ Config cargado</p>";
    echo "<p>Usuario admin: " . (defined("ADMIN_USER") ? ADMIN_USER : "No definido") . "</p>";
} else {
    echo "<p>❌ Config no existe</p>";
}

echo "<hr>";
echo "<a href=\"../\">🏠 Volver al inicio</a> | ";
echo "<a href=\"../sistema/cotizador.php\">🚀 Cotizador</a> | ";
echo "<a href=\"../debug_admin.php\">🔍 Debug Admin</a>";
echo "</body></html>";
?>';

try {
    if (!is_dir('admin')) {
        mkdir('admin', 0755, true);
        echo "<div class='success'>✅ Directorio admin creado</div>";
    }
    
    if (file_put_contents('admin/simple.php', $adminSimple)) {
        echo "<div class='success'>✅ Admin simplificado creado: admin/simple.php</div>";
    } else {
        echo "<div class='error'>❌ No se pudo crear admin simplificado</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error creando admin simplificado: " . $e->getMessage() . "</div>";
}

echo "<h2>🔗 Enlaces de prueba</h2>";
echo "<div class='info'>";
echo "<strong>Prueba estos enlaces:</strong><br>";
echo "<a href='admin/simple.php' target='_blank' style='color: blue; text-decoration: underline;'>🧪 Admin Simplificado</a><br>";
echo "<a href='admin/test.php' target='_blank' style='color: blue; text-decoration: underline;'>🔧 Admin Test</a><br>";
echo "<a href='admin/index.php' target='_blank' style='color: blue; text-decoration: underline;'>👤 Admin Principal</a><br>";
echo "<a href='sistema/cotizador.php' target='_blank' style='color: blue; text-decoration: underline;'>🚀 Cotizador</a><br>";
echo "</div>";

echo "</div>";
?> 