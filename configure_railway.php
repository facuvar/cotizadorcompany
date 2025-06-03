<?php
/**
 * Script para configurar automáticamente el sistema para Railway
 * Crea los archivos de configuración necesarios
 */

echo "<h1>⚙️ CONFIGURACIÓN AUTOMÁTICA PARA RAILWAY</h1>";
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

// Paso 1: Crear config.php principal
echo "<div class='step'>";
echo "<h2>📄 Creando archivo config.php principal</h2>";

$configContent = '<?php
/**
 * Configuración principal del sistema
 * Auto-detecta si está en Railway o local
 */

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

// Configuración de sesión
ini_set("session.cookie_httponly", 1);
ini_set("session.use_only_cookies", 1);
ini_set("session.cookie_secure", $isRailway ? 1 : 0);

// Zona horaria
date_default_timezone_set("America/Argentina/Buenos_Aires");

// Crear directorios necesarios si no existen
$dirs = [UPLOAD_DIR, BACKUP_DIR, LOG_DIR];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}
?>';

if (file_put_contents('sistema/config.php', $configContent)) {
    echo "<div class='success'>✅ Archivo sistema/config.php creado exitosamente</div>";
} else {
    echo "<div class='error'>❌ Error creando sistema/config.php</div>";
}
echo "</div>";

// Paso 2: Verificar estructura de directorios
echo "<div class='step'>";
echo "<h2>📁 Verificando estructura de directorios</h2>";

$requiredDirs = [
    'sistema',
    'admin', 
    'uploads',
    'backups',
    'logs'
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<div class='success'>✅ Directorio $dir creado</div>";
        } else {
            echo "<div class='error'>❌ Error creando directorio $dir</div>";
        }
    } else {
        echo "<div class='info'>ℹ️ Directorio $dir ya existe</div>";
    }
}
echo "</div>";

// Paso 3: Crear archivo .htaccess para Railway
echo "<div class='step'>";
echo "<h2>🔧 Creando archivo .htaccess</h2>";

$htaccessContent = '# Configuración para Railway
RewriteEngine On

# Redirigir a index.php si el archivo no existe
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Configuración de seguridad
<Files "*.php">
    Order allow,deny
    Allow from all
</Files>

# Proteger archivos de configuración
<Files "config*.php">
    Order deny,allow
    Deny from all
</Files>

# Configuración de tipos MIME
AddType application/json .json
AddType text/css .css
AddType application/javascript .js

# Configuración de cache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
</IfModule>';

if (file_put_contents('.htaccess', $htaccessContent)) {
    echo "<div class='success'>✅ Archivo .htaccess creado exitosamente</div>";
} else {
    echo "<div class='error'>❌ Error creando .htaccess</div>";
}
echo "</div>";

// Paso 4: Probar conexión a base de datos
echo "<div class='step'>";
echo "<h2>🔌 Probando conexión a base de datos</h2>";

try {
    require_once 'sistema/config.php';
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    echo "<div class='success'>✅ Conexión a base de datos exitosa</div>";
    echo "<div class='info'>🔢 Servidor: " . DB_HOST . ":" . DB_PORT . "</div>";
    echo "<div class='info'>🗄️ Base de datos: " . DB_NAME . "</div>";
    echo "<div class='info'>👤 Usuario: " . DB_USER . "</div>";
    echo "<div class='info'>🔢 Versión MySQL: " . $conn->server_info . "</div>";
    
    // Verificar tablas
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    if ($result) {
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
    }
    
    echo "<div class='info'>📋 Tablas encontradas (" . count($tables) . "): " . implode(', ', $tables) . "</div>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Paso 5: Enlaces de prueba
echo "<div class='step'>";
echo "<h2>🔗 Enlaces de Prueba</h2>";

$baseUrl = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
$baseUrl .= $_SERVER['HTTP_HOST'];
$baseUrl .= dirname($_SERVER['REQUEST_URI']);
if (substr($baseUrl, -1) !== '/') $baseUrl .= '/';

echo "<div class='info'>";
echo "<h3>🌐 URLs del Sistema:</h3>";
echo "<p><a href='{$baseUrl}' class='btn' target='_blank'>🏠 Página Principal</a></p>";
echo "<p><a href='{$baseUrl}sistema/cotizador.php' class='btn' target='_blank'>💰 Cotizador</a></p>";
echo "<p><a href='{$baseUrl}admin/' class='btn' target='_blank'>🔧 Panel Admin</a></p>";
echo "<p><strong>Credenciales Admin:</strong> usuario: <code>admin</code>, contraseña: <code>admin123</code></p>";
echo "</div>";

echo "<div class='success'>";
echo "<h3>🎉 ¡CONFIGURACIÓN COMPLETADA!</h3>";
echo "<p>El sistema ha sido configurado automáticamente para Railway.</p>";
echo "<p>Todos los archivos de configuración han sido creados y la conexión a la base de datos está funcionando.</p>";
echo "</div>";

echo "</div>";

echo "</div>"; // container
?> 