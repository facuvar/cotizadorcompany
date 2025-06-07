<?php
/**
 * CONFIGURACIÓN ESPECÍFICA PARA RAILWAY
 * Este archivo maneja automáticamente la conexión a la base de datos en Railway
 */

// Detectar si estamos en Railway
$isRailway = isset($_ENV['RAILWAY_STATIC_URL']) || 
             isset($_ENV['RAILWAY_ENVIRONMENT']) ||
             isset($_ENV['MYSQLHOST']) ||
             strpos($_SERVER['HTTP_HOST'] ?? '', 'railway.app') !== false;

if ($isRailway) {
    // CONFIGURACIÓN RAILWAY
    echo "<!-- Configuración Railway detectada -->\n";
    
    // Intentar múltiples formas de obtener las credenciales
    $db_host = $_ENV['DB_HOST'] ?? $_ENV['MYSQLHOST'] ?? 'mysql.railway.internal';
    $db_user = $_ENV['DB_USER'] ?? $_ENV['MYSQLUSER'] ?? 'root';
    $db_pass = $_ENV['DB_PASS'] ?? $_ENV['MYSQLPASSWORD'] ?? '';
    $db_name = $_ENV['DB_NAME'] ?? $_ENV['MYSQLDATABASE'] ?? 'railway';
    $db_port = $_ENV['DB_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306';
    
    // Configuración de base de datos
    define('DB_HOST', $db_host);
    define('DB_USER', $db_user);
    define('DB_PASS', $db_pass);
    define('DB_NAME', $db_name);
    define('DB_PORT', $db_port);
    
    // URLs base para Railway
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', 'https://' . $host . '/');
    define('ADMIN_URL', BASE_URL . 'admin/');
    define('SISTEMA_URL', BASE_URL . 'sistema/');
    
    // Configuración de archivos en Railway
    define('UPLOAD_DIR', '/app/uploads/');
    define('BACKUP_DIR', '/app/backups/');
    define('LOG_DIR', '/app/logs/');
    
    // Configuración específica de Railway
    define('IS_RAILWAY', true);
    define('DEBUG_MODE', false);
    
} else {
    // CONFIGURACIÓN LOCAL (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'company_presupuestos');
    define('DB_PORT', '3306');
    
    // URLs base para local
    define('BASE_URL', 'http://localhost/company-presupuestos-online-2/');
    define('ADMIN_URL', BASE_URL . 'admin/');
    define('SISTEMA_URL', BASE_URL . 'sistema/');
    
    // Configuración de archivos local
    define('UPLOAD_DIR', __DIR__ . '/uploads/');
    define('BACKUP_DIR', __DIR__ . '/backups/');
    define('LOG_DIR', __DIR__ . '/logs/');
    
    define('IS_RAILWAY', false);
    define('DEBUG_MODE', true);
}

// Configuración general
define('SITE_NAME', 'Cotizador de Ascensores');
define('ADMIN_EMAIL', 'admin@cotizador.com');

// Configuración de administrador
define('ADMIN_USER', $_ENV['ADMIN_USER'] ?? 'admin');
define('ADMIN_PASS', $_ENV['ADMIN_PASS'] ?? '$2y$10$szOr0zBbR/0iUpJbHGzVgOyMS3vr7/3DbqFnOJTJRKZOwjyWO/vjm'); // admin123

// Configuración de sesión
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', $isRailway ? 1 : 0);
}

// Zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Función para obtener conexión PDO
function getPDOConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        if (IS_RAILWAY) {
            // Configuración específica para Railway
            $options[PDO::ATTR_TIMEOUT] = 30;
            $options[PDO::ATTR_PERSISTENT] = false;
        }
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
        
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die("Error de conexión: " . $e->getMessage());
        } else {
            die("Error de conexión a la base de datos");
        }
    }
}

// Función para logging en Railway
function railway_log($message) {
    if (IS_RAILWAY) {
        error_log("[RAILWAY] " . date('Y-m-d H:i:s') . " - " . $message);
    }
}

// Función para debug
function debug_info() {
    if (DEBUG_MODE) {
        echo "<!-- DEBUG INFO:\n";
        echo "DB_HOST: " . DB_HOST . "\n";
        echo "DB_USER: " . DB_USER . "\n";
        echo "DB_NAME: " . DB_NAME . "\n";
        echo "DB_PORT: " . DB_PORT . "\n";
        echo "IS_RAILWAY: " . (IS_RAILWAY ? 'true' : 'false') . "\n";
        echo "BASE_URL: " . BASE_URL . "\n";
        echo "-->\n";
    }
}

// Mostrar información de debug si está habilitado
debug_info();

// Log de inicialización
railway_log("Configuración cargada - Railway: " . (IS_RAILWAY ? 'SI' : 'NO'));
?> 