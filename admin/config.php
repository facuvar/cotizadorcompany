<?php
// Configuración para el admin del cotizador
// Detecta automáticamente si está en Railway o local

// Detectar entorno
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || 
             isset($_SERVER['RAILWAY_ENVIRONMENT']) ||
             strpos($_SERVER['HTTP_HOST'] ?? '', 'railway.app') !== false;

if ($isRailway) {
    // Configuración para Railway
    define('DB_HOST', $_ENV['DB_HOST'] ?? 'mysql.railway.internal');
    define('DB_USER', $_ENV['DB_USER'] ?? 'root');
    define('DB_PASS', $_ENV['DB_PASS'] ?? '');
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'railway');
    define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
    define('ENVIRONMENT', 'railway');
} else {
    // Configuración para desarrollo local
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'cotizador_company');
    define('DB_PORT', 3306);
    define('ENVIRONMENT', 'local');
}

// Configuración general
define('SITE_URL', $isRailway ? 'https://cotizadorcompany-production.up.railway.app' : 'http://localhost/company-presupuestos-online-2');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOADS_DIR', __DIR__ . '/uploads');
define('DEBUG_MODE', !$isRailway); // Debug solo en local

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if ($isRailway) {
    ini_set('session.cookie_secure', 1); // HTTPS en Railway
}

// Configuración de errores
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Función para conectar a la base de datos
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $pdo;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die("Error de conexión: " . $e->getMessage());
        } else {
            die("Error de conexión a la base de datos");
        }
    }
}

// Función para verificar si el usuario está autenticado
function isAuthenticated() {
    session_start();
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Función para requerir autenticación
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

// Función para log de errores
function logError($message) {
    $logFile = __DIR__ . '/logs/error.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Configuración de timezone
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Headers de seguridad
if ($isRailway) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
}
?> 