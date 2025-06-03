<?php
/**
 * Script para crear automáticamente el archivo de configuración
 * Especialmente útil para Railway donde config.php no se sube por estar en .gitignore
 */

// Detectar si estamos en Railway
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || 
             isset($_SERVER['RAILWAY_ENVIRONMENT']) || 
             getenv('RAILWAY_ENVIRONMENT') !== false ||
             isset($_ENV['MYSQLHOST']) ||
             isset($_SERVER['MYSQLHOST']);

$configPath = __DIR__ . '/sistema/config.php';
$configDir = dirname($configPath);

// Crear directorio si no existe
if (!is_dir($configDir)) {
    mkdir($configDir, 0755, true);
}

// Contenido del archivo de configuración
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
             isset($_SERVER[\'MYSQLHOST\']);

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

// Intentar escribir el archivo
$success = file_put_contents($configPath, $configContent);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Configuración - Sistema de Presupuestos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .info {
            background: #e3f2fd;
            color: #1565c0;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .btn-success {
            background: #4caf50;
        }
        .btn-success:hover {
            background: #45a049;
        }
        .details {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Crear Configuración del Sistema</h1>
        
        <?php if ($success !== false): ?>
            <div class="success">
                ✅ <strong>¡Configuración creada exitosamente!</strong><br>
                El archivo <code>sistema/config.php</code> ha sido generado correctamente.
            </div>
            
            <div class="info">
                <h3>📋 Información de la configuración:</h3>
                <div class="details">
                    <strong>Entorno detectado:</strong> <?php echo $isRailway ? 'Railway' : 'Local'; ?><br>
                    <strong>Archivo creado:</strong> <?php echo $configPath; ?><br>
                    <strong>Tamaño del archivo:</strong> <?php echo number_format($success); ?> bytes<br>
                    <strong>Fecha de creación:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                </div>
            </div>
            
            <div class="info">
                <h3>🚀 Próximos pasos:</h3>
                <ol>
                    <li>Verificar que la configuración funciona correctamente</li>
                    <li>Acceder al panel de administración</li>
                    <li>Probar el cotizador principal</li>
                </ol>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="verify_config.php" class="btn btn-success">✅ Verificar Configuración</a>
                <a href="admin/" class="btn">👤 Panel Admin</a>
                <a href="sistema/cotizador.php" class="btn">🏠 Cotizador</a>
            </div>
            
        <?php else: ?>
            <div class="error">
                ❌ <strong>Error al crear la configuración</strong><br>
                No se pudo escribir el archivo de configuración.
            </div>
            
            <div class="info">
                <h3>🔍 Información de diagnóstico:</h3>
                <div class="details">
                    <strong>Directorio objetivo:</strong> <?php echo $configDir; ?><br>
                    <strong>Archivo objetivo:</strong> <?php echo $configPath; ?><br>
                    <strong>Directorio existe:</strong> <?php echo is_dir($configDir) ? 'Sí' : 'No'; ?><br>
                    <strong>Directorio escribible:</strong> <?php echo is_writable($configDir) ? 'Sí' : 'No'; ?><br>
                    <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?><br>
                    <strong>Usuario PHP:</strong> <?php echo get_current_user(); ?>
                </div>
            </div>
            
            <div class="info">
                <h3>🛠️ Posibles soluciones:</h3>
                <ul>
                    <li>Verificar permisos del directorio <code>sistema/</code></li>
                    <li>Crear manualmente el directorio si no existe</li>
                    <li>Contactar al administrador del servidor</li>
                </ul>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="javascript:location.reload()" class="btn">🔄 Reintentar</a>
                <a href="diagnose_blank_pages.php" class="btn">🔍 Diagnóstico Completo</a>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <strong>Información técnica:</strong><br>
            • Entorno: <?php echo $isRailway ? 'Railway' : 'Local'; ?><br>
            • Directorio actual: <?php echo getcwd(); ?><br>
            • PHP Version: <?php echo PHP_VERSION; ?>
        </div>
    </div>
</body>
</html> 