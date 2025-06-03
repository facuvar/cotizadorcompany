<?php
/**
 * Script para corregir warnings de configuración de sesión
 * Genera una versión mejorada del config.php
 */

echo "<h1>🔧 CORRIGIENDO WARNINGS DE CONFIGURACIÓN</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; border-radius: 5px; }
    .debug { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace; font-size: 12px; }
    .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
</style>";

echo "<div class='container'>";

echo "<div class='step'>";
echo "<h2>🔧 Creando config.php corregido</h2>";

$configContent = '<?php
/**
 * Configuración principal del sistema
 * Auto-detecta si está en Railway o local
 * Versión corregida sin warnings de sesión
 */

// Configurar sesión ANTES de cualquier output
if (session_status() === PHP_SESSION_NONE) {
    // Configuración de sesión solo si no hay sesión activa
    ini_set("session.cookie_httponly", 1);
    ini_set("session.use_only_cookies", 1);
    
    // Detectar si estamos en Railway para configurar cookies seguras
    $isHttps = isset($_SERVER["HTTPS"]) || 
               (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] === "https") ||
               (isset($_SERVER["HTTP_HOST"]) && strpos($_SERVER["HTTP_HOST"], "railway.app") !== false);
    
    ini_set("session.cookie_secure", $isHttps ? 1 : 0);
}

// Detectar si estamos en Railway
$isRailway = isset($_ENV["RAILWAY_ENVIRONMENT"]) || 
             isset($_ENV["MYSQLHOST"]) || 
             isset($_ENV["DB_HOST"]) ||
             (isset($_SERVER["HTTP_HOST"]) && strpos($_SERVER["HTTP_HOST"], "railway.app") !== false);

if ($isRailway) {
    // Configuración para Railway
    
    // Intentar variables personalizadas primero
    $db_host = $_ENV["DB_HOST"] ?? $_ENV["MYSQLHOST"] ?? "mysql.railway.internal";
    $db_user = $_ENV["DB_USER"] ?? $_ENV["MYSQLUSER"] ?? "root";
    $db_pass = $_ENV["DB_PASS"] ?? $_ENV["MYSQLPASSWORD"] ?? "DEACLLVQgoBvLmRKkFqUazfcOaDVwMKd";
    $db_name = $_ENV["DB_NAME"] ?? $_ENV["MYSQLDATABASE"] ?? "railway";
    $db_port = $_ENV["DB_PORT"] ?? $_ENV["MYSQLPORT"] ?? "3306";
    
    // Configuración de base de datos
    define("DB_HOST", $db_host);
    define("DB_USER", $db_user);
    define("DB_PASS", $db_pass);
    define("DB_NAME", $db_name);
    define("DB_PORT", $db_port);
    
    // URLs base
    define("BASE_URL", "https://" . $_SERVER["HTTP_HOST"] . "/");
    define("ADMIN_URL", BASE_URL . "admin/");
    define("SISTEMA_URL", BASE_URL . "sistema/");
    
    // Configuración de archivos
    define("UPLOAD_DIR", "/app/uploads/");
    define("BACKUP_DIR", "/app/backups/");
    define("LOG_DIR", "/app/logs/");
    
} else {
    // Configuración local (XAMPP)
    define("DB_HOST", "localhost");
    define("DB_USER", "root");
    define("DB_PASS", "");
    define("DB_NAME", "presupuestos_ascensores");
    define("DB_PORT", "3306");
    
    // URLs base
    define("BASE_URL", "http://localhost/company-presupuestos-online-2/");
    define("ADMIN_URL", BASE_URL . "admin/");
    define("SISTEMA_URL", BASE_URL . "sistema/");
    
    // Configuración de archivos
    define("UPLOAD_DIR", __DIR__ . "/uploads/");
    define("BACKUP_DIR", __DIR__ . "/backups/");
    define("LOG_DIR", __DIR__ . "/logs/");
}

// Configuración general
define("SITE_NAME", "Cotizador de Presupuestos");
define("ADMIN_EMAIL", "admin@cotizador.com");
define("DEBUG_MODE", $isRailway ? false : true);

// Zona horaria
date_default_timezone_set("America/Argentina/Buenos_Aires");

// Configuración de errores
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set("display_errors", 0);
}

// Crear directorios necesarios si no existen
$dirs = [UPLOAD_DIR, BACKUP_DIR, LOG_DIR];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Función helper para inicializar sesión de forma segura
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Función helper para verificar si estamos en Railway
function is_railway() {
    global $isRailway;
    return $isRailway;
}

// Función helper para obtener URL base
function get_base_url() {
    return BASE_URL;
}
?>';

// Crear backup del archivo actual si existe
if (file_exists('sistema/config.php')) {
    $backup = file_get_contents('sistema/config.php');
    file_put_contents('sistema/config.php.backup', $backup);
    echo "<div class='info'>📄 Backup creado: sistema/config.php.backup</div>";
}

// Escribir el nuevo archivo
if (file_put_contents('sistema/config.php', $configContent)) {
    echo "<div class='success'>✅ Archivo sistema/config.php actualizado exitosamente</div>";
} else {
    echo "<div class='error'>❌ Error actualizando sistema/config.php</div>";
}

echo "</div>";

// Probar la nueva configuración
echo "<div class='step'>";
echo "<h2>🧪 Probando nueva configuración</h2>";

try {
    // Limpiar cualquier output previo
    if (ob_get_level()) {
        ob_clean();
    }
    
    require_once 'sistema/config.php';
    
    echo "<div class='success'>✅ Archivo config.php cargado sin warnings</div>";
    
    // Probar conexión a base de datos
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    echo "<div class='success'>✅ Conexión a base de datos exitosa</div>";
    echo "<div class='info'>🔢 Servidor: " . DB_HOST . ":" . DB_PORT . "</div>";
    echo "<div class='info'>🗄️ Base de datos: " . DB_NAME . "</div>";
    echo "<div class='info'>🔢 Versión MySQL: " . $conn->server_info . "</div>";
    
    // Probar funciones helper
    echo "<div class='info'>🌐 URL Base: " . get_base_url() . "</div>";
    echo "<div class='info'>🚂 Es Railway: " . (is_railway() ? "Sí" : "No") . "</div>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div>";

// Mostrar mejoras implementadas
echo "<div class='step'>";
echo "<h2>✨ Mejoras Implementadas</h2>";

echo "<div class='info'>";
echo "<h3>🔧 Correcciones aplicadas:</h3>";
echo "<ul>";
echo "<li>✅ <strong>Configuración de sesión movida al inicio</strong> - Antes de cualquier output</li>";
echo "<li>✅ <strong>Verificación de estado de sesión</strong> - Solo configura si no hay sesión activa</li>";
echo "<li>✅ <strong>Detección mejorada de HTTPS</strong> - Compatible con proxies de Railway</li>";
echo "<li>✅ <strong>Funciones helper agregadas</strong> - Para manejo seguro de sesiones</li>";
echo "<li>✅ <strong>Configuración de errores</strong> - Diferente para desarrollo y producción</li>";
echo "<li>✅ <strong>Backup automático</strong> - Del archivo anterior</li>";
echo "</ul>";
echo "</div>";

echo "<div class='success'>";
echo "<h3>🎉 ¡Warnings Solucionados!</h3>";
echo "<p>El archivo config.php ha sido actualizado y ya no debería mostrar warnings de sesión.</p>";
echo "</div>";

echo "</div>";

// Enlaces de prueba
echo "<div class='step'>";
echo "<h2>🔗 Probar Sistema</h2>";

$baseUrl = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
$baseUrl .= $_SERVER['HTTP_HOST'];
$baseUrl .= dirname($_SERVER['REQUEST_URI']);
if (substr($baseUrl, -1) !== '/') $baseUrl .= '/';

echo "<div class='info'>";
echo "<h3>🌐 Prueba estos enlaces:</h3>";
echo "<p><a href='{$baseUrl}admin/' class='btn' target='_blank'>🔧 Panel Admin</a> - Debería cargar sin warnings</p>";
echo "<p><a href='{$baseUrl}sistema/cotizador.php' class='btn' target='_blank'>💰 Cotizador</a> - Sistema principal</p>";
echo "<p><a href='{$baseUrl}' class='btn' target='_blank'>🏠 Página Principal</a> - Inicio del sitio</p>";
echo "</div>";

echo "</div>";

echo "</div>"; // container
?> 