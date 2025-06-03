<?php
/**
 * EJEMPLO DE CÓMO ACTUALIZAR ARCHIVOS EXISTENTES
 * 
 * Este archivo muestra cómo convertir archivos que usan conexiones directas
 * para que usen la nueva configuración universal (config.php)
 */

// ========================================
// ANTES (Conexión directa - NO USAR)
// ========================================

/*
// ❌ CÓDIGO VIEJO - NO USAR
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'cotizador_company';

$conexion = new mysqli($host, $user, $pass, $dbname);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
*/

// ========================================
// DESPUÉS (Usando config.php - USAR)
// ========================================

// ✅ CÓDIGO NUEVO - USAR SIEMPRE
require_once 'config.php';

// Opción 1: Usar PDO (RECOMENDADO)
try {
    $pdo = getDBConnection();
    
    // Ejemplo de consulta con PDO
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE activo = ?");
    $stmt->execute([1]);
    $categorias = $stmt->fetchAll();
    
    echo "Categorías encontradas: " . count($categorias) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Opción 2: Usar MySQLi (si es necesario)
try {
    $mysqli = getMySQLiConnection();
    
    // Ejemplo de consulta con MySQLi
    $result = $mysqli->query("SELECT COUNT(*) as total FROM opciones");
    $row = $result->fetch_assoc();
    
    echo "Opciones totales: " . $row['total'] . "\n";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error MySQLi: " . $e->getMessage() . "\n";
}

// ========================================
// EJEMPLOS DE CONVERSIÓN ESPECÍFICOS
// ========================================

/**
 * EJEMPLO 1: Convertir archivo de API
 */
function ejemploAPI() {
    // ❌ ANTES
    /*
    $conexion = new mysqli('localhost', 'root', '', 'cotizador_company');
    $query = "SELECT * FROM opciones";
    $result = $conexion->query($query);
    */
    
    // ✅ DESPUÉS
    require_once 'config.php';
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM opciones");
    $opciones = $stmt->fetchAll();
    
    return $opciones;
}

/**
 * EJEMPLO 2: Convertir archivo de administración
 */
function ejemploAdmin() {
    // ❌ ANTES
    /*
    $host = $_SERVER['HTTP_HOST'] === 'localhost' ? 'localhost' : 'mysql.railway.internal';
    $user = $_SERVER['HTTP_HOST'] === 'localhost' ? 'root' : 'root';
    $pass = $_SERVER['HTTP_HOST'] === 'localhost' ? '' : 'password123';
    $db = $_SERVER['HTTP_HOST'] === 'localhost' ? 'cotizador_company' : 'railway';
    */
    
    // ✅ DESPUÉS
    require_once '../config.php'; // Ajustar ruta según ubicación
    $pdo = getDBConnection();
    
    // La configuración se maneja automáticamente
    echo "Conectado a: " . ENVIRONMENT . " - " . DB_HOST . "\n";
}

/**
 * EJEMPLO 3: Convertir archivo de cotizador
 */
function ejemploCotizador() {
    // ❌ ANTES
    /*
    if (strpos($_SERVER['HTTP_HOST'], 'railway') !== false) {
        $conexion = new mysqli('mysql.railway.internal', 'root', 'password', 'railway');
    } else {
        $conexion = new mysqli('localhost', 'root', '', 'cotizador_company');
    }
    */
    
    // ✅ DESPUÉS
    require_once 'config.php';
    $pdo = getDBConnection();
    
    // Obtener categorías
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY orden");
    $categorias = $stmt->fetchAll();
    
    return $categorias;
}

// ========================================
// PASOS PARA ACTUALIZAR UN ARCHIVO
// ========================================

/*
PASO 1: Agregar al inicio del archivo
require_once 'config.php';

PASO 2: Reemplazar conexiones directas
// Eliminar líneas como:
$conexion = new mysqli($host, $user, $pass, $db);

// Reemplazar por:
$pdo = getDBConnection();
// o
$mysqli = getMySQLiConnection();

PASO 3: Actualizar consultas (si usas PDO)
// Cambiar de:
$result = $conexion->query("SELECT * FROM tabla");

// A:
$stmt = $pdo->query("SELECT * FROM tabla");
$result = $stmt->fetchAll();

PASO 4: Usar constantes del config
// En lugar de hardcodear valores:
echo "Moneda: ARS";

// Usar:
echo "Moneda: " . CURRENCY;

PASO 5: Aprovechar funciones de utilidad
$envInfo = getEnvironmentInfo();
$stats = getDatabaseStats();
$isConnected = testConnection();
*/

// ========================================
// ARCHIVOS QUE NECESITAN ACTUALIZACIÓN
// ========================================

/*
ARCHIVOS PRINCIPALES A ACTUALIZAR:
1. cotizador.php - Archivo principal del cotizador
2. admin/index.php - Panel de administración
3. api/opciones.php - API para obtener opciones
4. api/guardar_presupuesto.php - API para guardar presupuestos
5. upload_sql_railway.php - Gestor de uploads
6. Cualquier archivo que tenga conexiones directas

BUSCAR EN ARCHIVOS:
- mysqli_connect()
- new mysqli()
- new PDO()
- Hardcoded database credentials
*/

echo "✅ Configuración universal lista para usar\n";
echo "📋 Revisa este archivo para ver ejemplos de conversión\n";
echo "🔧 Entorno actual: " . ENVIRONMENT . "\n";
echo "🗄️ Base de datos: " . DB_HOST . "/" . DB_NAME . "\n";

?> 