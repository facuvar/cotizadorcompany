<?php
/**
 * Script de configuración completa para Railway
 * Crea todos los archivos necesarios y verifica el funcionamiento
 */

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🚀 CONFIGURACIÓN COMPLETA PARA RAILWAY</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; border-radius: 5px; }
    .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
    .btn-success { background: #28a745; }
    .btn-warning { background: #ffc107; color: #212529; }
</style>";

echo "<div class='container'>";

// Paso 1: Crear directorio sistema si no existe
echo "<div class='step'>";
echo "<h2>📁 Paso 1: Verificar/Crear directorios</h2>";

$directories = ['sistema', 'sistema/includes', 'uploads', 'backups'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<div class='success'>✅ Directorio '$dir' creado</div>";
        } else {
            echo "<div class='error'>❌ Error creando directorio '$dir'</div>";
        }
    } else {
        echo "<div class='success'>✅ Directorio '$dir' existe</div>";
    }
}
echo "</div>";

// Paso 2: Crear archivo config.php
echo "<div class='step'>";
echo "<h2>⚙️ Paso 2: Crear archivo de configuración</h2>";

$configContent = '<?php
/**
 * Configuración principal del sistema
 * Auto-detecta si está en Railway o entorno local
 */

// Detectar si estamos en Railway
$isRailway = isset($_ENV[\'RAILWAY_ENVIRONMENT\']) || 
             isset($_SERVER[\'RAILWAY_ENVIRONMENT\']) || 
             getenv(\'RAILWAY_ENVIRONMENT\') !== false ||
             isset($_ENV[\'MYSQLHOST\']) ||
             isset($_SERVER[\'MYSQLHOST\']) ||
             file_exists(\'/app\'); // Detectar contenedor Railway

if ($isRailway) {
    // Configuración para Railway
    
    // Intentar variables personalizadas primero
    $db_host = $_ENV[\'DB_HOST\'] ?? $_SERVER[\'DB_HOST\'] ?? getenv(\'DB_HOST\');
    $db_user = $_ENV[\'DB_USER\'] ?? $_SERVER[\'DB_USER\'] ?? getenv(\'DB_USER\');
    $db_pass = $_ENV[\'DB_PASS\'] ?? $_SERVER[\'DB_PASS\'] ?? getenv(\'DB_PASS\');
    $db_name = $_ENV[\'DB_NAME\'] ?? $_SERVER[\'DB_NAME\'] ?? getenv(\'DB_NAME\');
    $db_port = $_ENV[\'DB_PORT\'] ?? $_SERVER[\'DB_PORT\'] ?? getenv(\'DB_PORT\');
    
    // Si no hay variables personalizadas, usar las nativas de Railway
    if (!$db_host) {
        $db_host = $_ENV[\'MYSQLHOST\'] ?? $_SERVER[\'MYSQLHOST\'] ?? getenv(\'MYSQLHOST\') ?? \'mysql.railway.internal\';
        $db_user = $_ENV[\'MYSQLUSER\'] ?? $_SERVER[\'MYSQLUSER\'] ?? getenv(\'MYSQLUSER\') ?? \'root\';
        $db_pass = $_ENV[\'MYSQLPASSWORD\'] ?? $_SERVER[\'MYSQLPASSWORD\'] ?? getenv(\'MYSQLPASSWORD\') ?? \'DEACLLVQgoBvLmRKkFqUazfcOaDVwMKd\';
        $db_name = $_ENV[\'MYSQLDATABASE\'] ?? $_SERVER[\'MYSQLDATABASE\'] ?? getenv(\'MYSQLDATABASE\') ?? \'railway\';
        $db_port = $_ENV[\'MYSQLPORT\'] ?? $_SERVER[\'MYSQLPORT\'] ?? getenv(\'MYSQLPORT\') ?? 3306;
    }
    
    // Si aún no hay valores, usar credenciales directas conocidas
    if (!$db_host || $db_host === false) {
        $db_host = \'mysql.railway.internal\';
        $db_user = \'root\';
        $db_pass = \'DEACLLVQgoBvLmRKkFqUazfcOaDVwMKd\';
        $db_name = \'railway\';
        $db_port = 3306;
    }
    
    // URL base para Railway
    $base_url = \'https://\' . ($_SERVER[\'HTTP_HOST\'] ?? \'localhost\');
    
} else {
    // Configuración para entorno local (XAMPP)
    $db_host = \'localhost\';
    $db_user = \'root\';
    $db_pass = \'\';
    $db_name = \'company_presupuestos\';
    $db_port = 3306;
    
    // URL base para local
    $base_url = \'http://localhost/company-presupuestos-online-2\';
}

// Definir constantes
define(\'DB_HOST\', $db_host);
define(\'DB_USER\', $db_user);
define(\'DB_PASS\', $db_pass);
define(\'DB_NAME\', $db_name);
define(\'DB_PORT\', $db_port);
define(\'BASE_URL\', $base_url);

// Configuración adicional
define(\'UPLOAD_DIR\', __DIR__ . \'/../uploads/\');
define(\'BACKUP_DIR\', __DIR__ . \'/../backups/\');

// Configuración de sesión
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Configuración de zona horaria
date_default_timezone_set(\'America/Argentina/Buenos_Aires\');

// Configuración de errores (solo en desarrollo)
if (!$isRailway) {
    error_reporting(E_ALL);
    ini_set(\'display_errors\', 1);
} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set(\'display_errors\', 0);
}

// URL del sitio (se ajusta automáticamente en Railway)
$site_url = isset($_ENV[\'RAILWAY_STATIC_URL\']) 
    ? \'https://\' . $_ENV[\'RAILWAY_STATIC_URL\'] . \'/sistema\'
    : (isset($_SERVER[\'HTTP_HOST\']) 
        ? \'http://\' . $_SERVER[\'HTTP_HOST\'] . \'/sistema\'
        : \'http://localhost/company-presupuestos-online-2/sistema\');

define(\'SITE_URL\', $site_url);
define(\'XLS_DIR\', __DIR__ . \'/uploads/xls\');

// Color principal de la empresa
define(\'MAIN_COLOR\', \'#e50009\');

// Credenciales del administrador
define(\'ADMIN_USER\', $_ENV[\'ADMIN_USER\'] ?? \'admin\');
define(\'ADMIN_PASS\', $_ENV[\'ADMIN_PASS\'] ?? \'$2y$10$szOr0zBbR/0iUpJbHGzVgOyMS3vr7/3DbqFnOJTJRKZOwjyWO/vjm\'); // admin123

// Configuración de email (opcional)
define(\'SMTP_HOST\', $_ENV[\'SMTP_HOST\'] ?? \'smtp.gmail.com\');
define(\'SMTP_PORT\', $_ENV[\'SMTP_PORT\'] ?? 587);
define(\'SMTP_USER\', $_ENV[\'SMTP_USER\'] ?? \'\');
define(\'SMTP_PASS\', $_ENV[\'SMTP_PASS\'] ?? \'\');
define(\'FROM_EMAIL\', $_ENV[\'FROM_EMAIL\'] ?? \'noreply@tuempresa.com\');
define(\'FROM_NAME\', $_ENV[\'FROM_NAME\'] ?? \'Sistema de Presupuestos\');

// Configuración de Google Sheets (opcional)
define(\'GOOGLE_SHEETS_API_KEY\', $_ENV[\'GOOGLE_SHEETS_API_KEY\'] ?? \'\');
define(\'GOOGLE_SHEETS_ID\', $_ENV[\'GOOGLE_SHEETS_ID\'] ?? \'\');

// Detectar si estamos en Railway
define(\'IS_RAILWAY\', $isRailway);

// Función para debug en Railway
function railway_log($message) {
    if (IS_RAILWAY) {
        error_log("[RAILWAY] " . $message);
    }
}
?>';

if (file_put_contents('sistema/config.php', $configContent)) {
    echo "<div class='success'>✅ Archivo sistema/config.php creado exitosamente (" . filesize('sistema/config.php') . " bytes)</div>";
} else {
    echo "<div class='error'>❌ Error creando sistema/config.php</div>";
}
echo "</div>";

// Paso 3: Verificar configuración
echo "<div class='step'>";
echo "<h2>🔍 Paso 3: Verificar configuración</h2>";

try {
    require_once 'sistema/config.php';
    echo "<div class='success'>✅ Configuración cargada correctamente</div>";
    
    echo "<div class='info'>";
    echo "<strong>Configuración detectada:</strong><br>";
    echo "• Entorno: " . (defined('IS_RAILWAY') && IS_RAILWAY ? 'Railway' : 'Local') . "<br>";
    echo "• DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'No definido') . "<br>";
    echo "• DB_USER: " . (defined('DB_USER') ? DB_USER : 'No definido') . "<br>";
    echo "• DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'No definido') . "<br>";
    echo "• BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'No definido') . "<br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error cargando configuración: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Paso 4: Probar conexión a base de datos
echo "<div class='step'>";
echo "<h2>🗄️ Paso 4: Probar conexión a base de datos</h2>";

if (defined('DB_HOST')) {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
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
            } else {
                echo "<div class='warning'>⚠️ No hay tablas en la base de datos. Necesitas importar los datos.</div>";
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

// Paso 5: Enlaces de prueba
echo "<div class='step'>";
echo "<h2>🔗 Paso 5: Probar el sistema</h2>";

echo "<div class='info'>";
echo "<h3>🧪 Enlaces de prueba:</h3>";
echo "<a href='admin/test.php' class='btn btn-success' target='_blank'>🧪 Admin de Prueba</a>";
echo "<a href='admin/' class='btn' target='_blank'>👤 Panel Admin</a>";
echo "<a href='sistema/cotizador.php' class='btn' target='_blank'>💰 Cotizador</a>";
echo "<a href='verify_config.php' class='btn' target='_blank'>✅ Verificar Config</a>";
echo "<a href='diagnose_blank_pages.php' class='btn btn-warning' target='_blank'>🔍 Diagnóstico</a>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>📋 Credenciales por defecto:</h3>";
echo "<strong>Usuario:</strong> admin<br>";
echo "<strong>Contraseña:</strong> admin123";
echo "</div>";
echo "</div>";

echo "</div>";
?> 