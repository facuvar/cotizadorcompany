<?php
/**
 * Configuración Universal - Railway y Local
 * Detecta automáticamente el entorno y usa las credenciales correctas
 */

// Detectar si estamos en Railway
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || 
             isset($_SERVER['RAILWAY_ENVIRONMENT']) ||
             strpos($_SERVER['HTTP_HOST'] ?? '', 'railway.app') !== false ||
             strpos($_SERVER['HTTP_HOST'] ?? '', 'up.railway.app') !== false;

if ($isRailway) {
    // ========================================
    // CONFIGURACIÓN RAILWAY (PRODUCCIÓN)
    // ========================================
    define('DB_HOST', $_ENV['DB_HOST'] ?? 'mysql.railway.internal');
    define('DB_USER', $_ENV['DB_USER'] ?? 'root');
    define('DB_PASS', $_ENV['DB_PASS'] ?? '');
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'railway');
    define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
    
    // Configuración de entorno
    define('ENVIRONMENT', 'railway');
    define('DEBUG_MODE', false);
    define('BASE_URL', 'https://' . $_SERVER['HTTP_HOST']);
    
} else {
    // ========================================
    // CONFIGURACIÓN LOCAL (DESARROLLO)
    // ========================================
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'company_presupuestos');
    define('DB_PORT', 3306);
    
    // Configuración de entorno
    define('ENVIRONMENT', 'local');
    define('DEBUG_MODE', true);
    define('BASE_URL', 'http://localhost/company-presupuestos-online-2');
}

// ========================================
// CONFIGURACIÓN COMÚN
// ========================================

// Configuración de errores según entorno
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuración de zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Configuración de charset
ini_set('default_charset', 'UTF-8');

// ========================================
// FUNCIÓN DE CONEXIÓN PDO
// ========================================

function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        // Log de conexión exitosa (solo en debug)
        if (DEBUG_MODE) {
            error_log("Conexión exitosa a " . ENVIRONMENT . " - Host: " . DB_HOST . " - DB: " . DB_NAME);
        }
        
        return $pdo;
        
    } catch (PDOException $e) {
        // Log del error
        error_log("Error de conexión DB (" . ENVIRONMENT . "): " . $e->getMessage());
        
        if (DEBUG_MODE) {
            die("Error de conexión: " . $e->getMessage());
        } else {
            die("Error de conexión a la base de datos. Por favor, inténtelo más tarde.");
        }
    }
}

// ========================================
// FUNCIÓN DE CONEXIÓN MYSQLI (ALTERNATIVA)
// ========================================

function getMySQLiConnection() {
    try {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if ($mysqli->connect_error) {
            throw new Exception("Error de conexión: " . $mysqli->connect_error);
        }
        
        $mysqli->set_charset("utf8mb4");
        
        return $mysqli;
        
    } catch (Exception $e) {
        error_log("Error de conexión MySQLi (" . ENVIRONMENT . "): " . $e->getMessage());
        
        if (DEBUG_MODE) {
            die("Error de conexión MySQLi: " . $e->getMessage());
        } else {
            die("Error de conexión a la base de datos.");
        }
    }
}

// ========================================
// FUNCIONES DE UTILIDAD
// ========================================

/**
 * Obtener información del entorno actual
 */
function getEnvironmentInfo() {
    return [
        'environment' => ENVIRONMENT,
        'host' => DB_HOST,
        'database' => DB_NAME,
        'port' => DB_PORT,
        'debug' => DEBUG_MODE,
        'base_url' => BASE_URL,
        'is_railway' => ENVIRONMENT === 'railway'
    ];
}

/**
 * Verificar si la conexión está funcionando
 */
function testConnection() {
    try {
        $pdo = getDBConnection();
        $result = $pdo->query("SELECT 1 as test")->fetch();
        return $result['test'] === 1;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Obtener estadísticas de la base de datos
 */
function getDatabaseStats() {
    try {
        $pdo = getDBConnection();
        
        $stats = [];
        
        // Contar categorías
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categorias");
        $stats['categorias'] = $stmt->fetch()['count'];
        
        // Contar opciones
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM opciones");
        $stats['opciones'] = $stmt->fetch()['count'];
        
        // Contar presupuestos
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM presupuestos");
        $stats['presupuestos'] = $stmt->fetch()['count'];
        
        return $stats;
        
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// ========================================
// CONFIGURACIÓN ESPECÍFICA POR ENTORNO
// ========================================

if (ENVIRONMENT === 'railway') {
    // Configuración específica de Railway
    define('UPLOAD_MAX_SIZE', '10M');
    define('SESSION_LIFETIME', 3600); // 1 hora
    
} else {
    // Configuración específica de Local
    define('UPLOAD_MAX_SIZE', '50M');
    define('SESSION_LIFETIME', 7200); // 2 horas
}

// ========================================
// INICIALIZACIÓN
// ========================================

// Configurar sesiones
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    session_start();
}

// Log de inicialización (solo en debug)
if (DEBUG_MODE) {
    error_log("Config cargada - Entorno: " . ENVIRONMENT . " - Host: " . DB_HOST);
}

// ========================================
// CONSTANTES ADICIONALES
// ========================================

define('APP_NAME', 'Cotizador Company');
define('APP_VERSION', '2.0.0');
define('CURRENCY', 'ARS');
define('CURRENCY_SYMBOL', 'AR$');

// Rutas de archivos
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('PDF_DIR', __DIR__ . '/presupuestos/');
define('LOG_DIR', __DIR__ . '/logs/');

// Crear directorios si no existen
$dirs = [UPLOAD_DIR, PDF_DIR, LOG_DIR];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

?> 