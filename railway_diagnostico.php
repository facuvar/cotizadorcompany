<?php
/**
 * Script de diagnóstico para la base de datos en Railway
 * Verifica la conexión, las tablas y la estructura
 */

// Configuración de tiempo de ejecución
set_time_limit(300); // 5 minutos
ini_set('memory_limit', '256M');

// Iniciar la página HTML
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnóstico de Base de Datos en Railway</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1, h2, h3 { color: #333; }
        h1 { text-align: center; }
        .info { background-color: #e3f2fd; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .success { background-color: #e8f5e9; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .error { background-color: #ffebee; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .warning { background-color: #fff3e0; padding: 10px; margin: 5px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
        .actions { margin: 20px 0; }
        .btn { display: inline-block; background-color: #4CAF50; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; margin-right: 5px; }
        .btn:hover { background-color: #45a049; }
        .btn-warning { background-color: #ff9800; }
        .btn-warning:hover { background-color: #e68a00; }
        pre { background-color: #f8f8f8; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Diagnóstico de Base de Datos en Railway</h1>";

// Verificar si estamos en Railway
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || 
             isset($_SERVER['RAILWAY_ENVIRONMENT']) || 
             getenv('RAILWAY_ENVIRONMENT') !== false ||
             isset($_ENV['MYSQLHOST']) ||
             isset($_SERVER['MYSQLHOST']);

if ($isRailway) {
    echo "<div class='success'>✅ Ejecutándose en entorno de Railway</div>";
} else {
    echo "<div class='warning'>⚠️ No se detectó el entorno de Railway. Este script está diseñado para ejecutarse directamente en Railway.</div>";
}

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    echo "<div class='{$tipo}'>{$mensaje}</div>";
}

// Función para mostrar una tabla con resultados
function mostrarTabla($datos, $titulo = 'Resultados') {
    if (empty($datos)) {
        echo "<h3>{$titulo}</h3>";
        echo "<p>No hay datos para mostrar.</p>";
        return;
    }
    
    echo "<h3>{$titulo}</h3>";
    echo "<table>";
    
    // Encabezados
    echo "<tr>";
    foreach (array_keys($datos[0]) as $columna) {
        echo "<th>{$columna}</th>";
    }
    echo "</tr>";
    
    // Datos
    foreach ($datos as $fila) {
        echo "<tr>";
        foreach ($fila as $valor) {
            echo "<td>" . (is_null($valor) ? "<em>NULL</em>" : htmlspecialchars($valor)) . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
}

// Obtener la configuración de la base de datos
$db_host = '';
$db_user = '';
$db_pass = '';
$db_name = '';
$db_port = '';

if (file_exists(__DIR__ . '/sistema/config.php')) {
    require_once __DIR__ . '/sistema/config.php';
    if (defined('DB_HOST')) {
        $db_host = DB_HOST;
        $db_user = DB_USER;
        $db_pass = DB_PASS;
        $db_name = DB_NAME;
        $db_port = DB_PORT;
        mostrarMensaje("✅ Archivo de configuración cargado", "success");
    } else {
        mostrarMensaje("⚠️ El archivo de configuración no contiene las constantes de base de datos", "warning");
    }
} else {
    mostrarMensaje("⚠️ Archivo de configuración no encontrado. Intentando obtener configuración de variables de entorno.", "warning");
}

// Si no tenemos la configuración del archivo, intentar obtenerla de variables de entorno
if (empty($db_host)) {
    $db_host = $_ENV['MYSQLHOST'] ?? $_SERVER['MYSQLHOST'] ?? getenv('MYSQLHOST') ?? 'mysql.railway.internal';
    $db_user = $_ENV['MYSQLUSER'] ?? $_SERVER['MYSQLUSER'] ?? getenv('MYSQLUSER') ?? 'root';
    $db_pass = $_ENV['MYSQLPASSWORD'] ?? $_SERVER['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?? '';
    $db_name = $_ENV['MYSQLDATABASE'] ?? $_SERVER['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?? 'railway';
    $db_port = $_ENV['MYSQLPORT'] ?? $_SERVER['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? 3306;
    
    mostrarMensaje("Usando configuración de variables de entorno", "info");
}

// Mostrar información de conexión
echo "<h2>Información de conexión</h2>";
echo "<ul>";
echo "<li><strong>Host:</strong> " . htmlspecialchars($db_host) . "</li>";
echo "<li><strong>Usuario:</strong> " . htmlspecialchars($db_user) . "</li>";
echo "<li><strong>Base de datos:</strong> " . htmlspecialchars($db_name) . "</li>";
echo "<li><strong>Puerto:</strong> " . htmlspecialchars($db_port) . "</li>";
echo "</ul>";

// Intentar conectar a la base de datos
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    mostrarMensaje("✅ Conexión exitosa a la base de datos", "success");
    
    // Verificar tablas existentes
    echo "<h2>Tablas de la base de datos</h2>";
    
    $result = $conn->query("SHOW TABLES");
    if (!$result) {
        throw new Exception("Error al consultar tablas: " . $conn->error);
    }
    
    $tablas = [];
    while ($row = $result->fetch_array()) {
        $tablas[] = $row[0];
    }
    
    if (empty($tablas)) {
        mostrarMensaje("⚠️ No se encontraron tablas en la base de datos", "warning");
    } else {
        mostrarMensaje("✅ Se encontraron " . count($tablas) . " tablas: " . implode(", ", $tablas), "success");
        
        // Mostrar detalles de cada tabla
        foreach ($tablas as $tabla) {
            echo "<h3>Tabla: {$tabla}</h3>";
            
            // Estructura de la tabla
            $result = $conn->query("DESCRIBE `{$tabla}`");
            if (!$result) {
                mostrarMensaje("Error al obtener estructura de la tabla {$tabla}: " . $conn->error, "error");
                continue;
            }
            
            $columnas = [];
            while ($row = $result->fetch_assoc()) {
                $columnas[] = $row;
            }
            
            mostrarTabla($columnas, "Estructura de la tabla {$tabla}");
            
            // Contar registros
            $result = $conn->query("SELECT COUNT(*) as total FROM `{$tabla}`");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "<p>La tabla contiene {$row['total']} registros</p>";
            }
        }
    }
    
    // Verificar columnas críticas
    echo "<h2>Verificación de columnas críticas</h2>";
    
    // Verificar si existe la columna plazo_entrega en la tabla presupuestos
    if (in_array('presupuestos', $tablas)) {
        $result = $conn->query("SHOW COLUMNS FROM presupuestos LIKE 'plazo_entrega'");
        if ($result && $result->num_rows > 0) {
            mostrarMensaje("✅ La columna 'plazo_entrega' existe en la tabla 'presupuestos'", "success");
        } else {
            mostrarMensaje("❌ La columna 'plazo_entrega' NO existe en la tabla 'presupuestos'", "error");
            echo "<div class='actions'>";
            echo "<a href='?action=add_plazo_entrega' class='btn'>Agregar columna plazo_entrega</a>";
            echo "</div>";
        }
    }
    
    // Acciones especiales si se solicitan
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        
        echo "<h2>Ejecutando acción: {$action}</h2>";
        
        if ($action === 'add_plazo_entrega') {
            $sql = "ALTER TABLE presupuestos ADD COLUMN plazo_entrega VARCHAR(50) NULL AFTER total";
            if ($conn->query($sql)) {
                mostrarMensaje("✅ Columna 'plazo_entrega' agregada correctamente a la tabla 'presupuestos'", "success");
            } else {
                mostrarMensaje("❌ Error al agregar columna 'plazo_entrega': " . $conn->error, "error");
            }
        }
    }
    
    // Cerrar conexión
    $conn->close();
    
} catch (Exception $e) {
    mostrarMensaje("❌ Error: " . $e->getMessage(), "error");
}

// Cerrar la página HTML
echo "
    </div>
</body>
</html>"; 