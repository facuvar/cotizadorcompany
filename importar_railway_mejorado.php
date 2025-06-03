<?php
/**
 * Script mejorado para importar un archivo SQL en Railway
 * Incluye desactivación de verificación de claves foráneas
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
    <title>Importar Base de Datos en Railway (Mejorado)</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; }
        h1 { text-align: center; }
        .info { background-color: #e3f2fd; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .success { background-color: #e8f5e9; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .error { background-color: #ffebee; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .warning { background-color: #fff3e0; padding: 10px; margin: 5px 0; border-radius: 5px; }
        form { margin: 20px 0; }
        input[type='file'] { display: block; margin: 10px 0; }
        input[type='submit'] { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        input[type='submit']:hover { background-color: #45a049; }
        pre { background-color: #f8f8f8; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .options { margin: 15px 0; }
        label { margin-right: 15px; }
        .checkbox-container { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Importar Base de Datos en Railway (Mejorado)</h1>";

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

// Cargar configuración
if (file_exists(__DIR__ . '/sistema/config.php')) {
    require_once __DIR__ . '/sistema/config.php';
    echo "<div class='info'>✅ Archivo de configuración cargado</div>";
} else {
    // Intentar detectar configuración de Railway
    $db_host = $_ENV['MYSQLHOST'] ?? $_SERVER['MYSQLHOST'] ?? getenv('MYSQLHOST') ?? 'mysql.railway.internal';
    $db_user = $_ENV['MYSQLUSER'] ?? $_SERVER['MYSQLUSER'] ?? getenv('MYSQLUSER') ?? 'root';
    $db_pass = $_ENV['MYSQLPASSWORD'] ?? $_SERVER['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?? '';
    $db_name = $_ENV['MYSQLDATABASE'] ?? $_SERVER['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?? 'railway';
    $db_port = $_ENV['MYSQLPORT'] ?? $_SERVER['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? 3306;
    
    echo "<div class='warning'>⚠️ Archivo de configuración no encontrado. Usando variables de entorno.</div>";
}

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    echo "<div class='{$tipo}'>{$mensaje}</div>";
}

// Función mejorada para ejecutar consultas SQL
function ejecutarSQL($conn, $sql, $foreign_key_checks = false) {
    // Desactivar comprobación de claves foráneas si se solicita
    if (!$foreign_key_checks) {
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
    }
    
    $consultas = explode(';', $sql);
    $errores = [];
    $ejecutadas = 0;
    
    foreach ($consultas as $consulta) {
        $consulta = trim($consulta);
        if (empty($consulta)) continue;
        
        if ($conn->query($consulta)) {
            $ejecutadas++;
        } else {
            $errores[] = "Error en consulta: " . $conn->error . "\nConsulta: " . substr($consulta, 0, 100) . "...";
        }
    }
    
    // Reactivar comprobación de claves foráneas
    if (!$foreign_key_checks) {
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
    }
    
    return [
        'ejecutadas' => $ejecutadas,
        'errores' => $errores
    ];
}

// Función para limpiar todas las tablas existentes
function limpiarTablas($conn) {
    $result = $conn->query("SHOW TABLES");
    $tablas = [];
    
    while ($row = $result->fetch_array()) {
        $tablas[] = $row[0];
    }
    
    if (!empty($tablas)) {
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        
        foreach ($tablas as $tabla) {
            $conn->query("DROP TABLE IF EXISTS `{$tabla}`");
        }
        
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
        return count($tablas);
    }
    
    return 0;
}

// Si se ha subido un archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sql_file'])) {
    $archivo = $_FILES['sql_file'];
    $limpiar_tablas = isset($_POST['limpiar_tablas']) && $_POST['limpiar_tablas'] == '1';
    $foreign_key_checks = isset($_POST['foreign_key_checks']) && $_POST['foreign_key_checks'] == '1';
    
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        mostrarMensaje("Error al subir el archivo: " . $archivo['error'], "error");
    } else if ($archivo['type'] !== 'application/sql' && $archivo['type'] !== 'text/plain' && !preg_match('/\.sql$/i', $archivo['name'])) {
        mostrarMensaje("El archivo debe ser un archivo SQL válido", "error");
    } else {
        mostrarMensaje("Archivo recibido: " . $archivo['name'], "success");
        
        // Conectar a la base de datos
        try {
            $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
            
            if ($conn->connect_error) {
                throw new Exception("Error de conexión: " . $conn->connect_error);
            }
            
            $conn->set_charset("utf8mb4");
            mostrarMensaje("Conexión exitosa a la base de datos", "success");
            
            // Limpiar todas las tablas si se solicitó
            if ($limpiar_tablas) {
                $tablas_eliminadas = limpiarTablas($conn);
                mostrarMensaje("Se eliminaron {$tablas_eliminadas} tablas existentes", "warning");
            }
            
            // Leer el archivo SQL
            $contenidoSQL = file_get_contents($archivo['tmp_name']);
            
            // Verificar si el archivo contiene DROP DATABASE y CREATE DATABASE
            $contieneDrop = stripos($contenidoSQL, 'DROP DATABASE') !== false;
            $contieneCreate = stripos($contenidoSQL, 'CREATE DATABASE') !== false;
            $contieneUse = stripos($contenidoSQL, 'USE `') !== false;
            
            echo "<h2>Procesando archivo SQL</h2>";
            
            if ($contieneDrop) {
                mostrarMensaje("⚠️ El archivo contiene instrucciones DROP DATABASE. Estas serán ignoradas para evitar problemas en Railway.", "warning");
                // Eliminar las instrucciones DROP DATABASE
                $contenidoSQL = preg_replace('/DROP\s+DATABASE\s+.+?;/i', '', $contenidoSQL);
            }
            
            if ($contieneCreate) {
                mostrarMensaje("⚠️ El archivo contiene instrucciones CREATE DATABASE. Estas serán ignoradas ya que la base de datos ya existe en Railway.", "warning");
                // Eliminar las instrucciones CREATE DATABASE
                $contenidoSQL = preg_replace('/CREATE\s+DATABASE\s+.+?;/i', '', $contenidoSQL);
            }
            
            if ($contieneUse) {
                mostrarMensaje("⚠️ El archivo contiene instrucciones USE. Estas serán modificadas para usar la base de datos actual de Railway.", "warning");
                // Modificar las instrucciones USE para usar la base de datos actual
                $contenidoSQL = preg_replace('/USE\s+`[^`]+`\s*;/i', "USE `{$db_name}`;", $contenidoSQL);
            }
            
            // Ejecutar las consultas
            mostrarMensaje("Ejecutando consultas SQL " . ($foreign_key_checks ? "CON" : "SIN") . " verificación de claves foráneas...", "info");
            $resultado = ejecutarSQL($conn, $contenidoSQL, $foreign_key_checks);
            
            if (empty($resultado['errores'])) {
                mostrarMensaje("✅ Importación completada con éxito. Se ejecutaron {$resultado['ejecutadas']} consultas.", "success");
            } else {
                mostrarMensaje("⚠️ Importación completada con errores. Se ejecutaron {$resultado['ejecutadas']} consultas, pero ocurrieron " . count($resultado['errores']) . " errores:", "warning");
                echo "<pre>" . implode("\n\n", $resultado['errores']) . "</pre>";
            }
            
            $conn->close();
            
        } catch (Exception $e) {
            mostrarMensaje("Error: " . $e->getMessage(), "error");
        }
    }
}

// Formulario para subir archivo
echo "
    <h2>Subir archivo SQL</h2>
    <form action='' method='post' enctype='multipart/form-data'>
        <div>
            <input type='file' name='sql_file' accept='.sql' required>
        </div>
        
        <div class='options'>
            <div class='checkbox-container'>
                <label>
                    <input type='checkbox' name='limpiar_tablas' value='1' checked> 
                    Limpiar todas las tablas existentes antes de importar (recomendado para evitar conflictos)
                </label>
            </div>
            
            <div class='checkbox-container'>
                <label>
                    <input type='checkbox' name='foreign_key_checks' value='1'> 
                    Mantener verificación de claves foráneas (desmarque para ignorar errores de clave foránea)
                </label>
            </div>
        </div>
        
        <div>
            <input type='submit' value='Importar SQL'>
        </div>
    </form>
    
    <h2>Información de conexión</h2>
    <ul>
        <li><strong>Host:</strong> " . htmlspecialchars($db_host) . "</li>
        <li><strong>Usuario:</strong> " . htmlspecialchars($db_user) . "</li>
        <li><strong>Base de datos:</strong> " . htmlspecialchars($db_name) . "</li>
        <li><strong>Puerto:</strong> " . htmlspecialchars($db_port) . "</li>
    </ul>
    
    <h2>Instrucciones</h2>
    <ol>
        <li>Ejecuta el script <code>exportar_db.php</code> en tu entorno local para generar un archivo SQL.</li>
        <li>Descarga el archivo SQL generado.</li>
        <li>Marca la opción para limpiar tablas si deseas una importación limpia (recomendado).</li>
        <li>Desmarca la opción de verificación de claves foráneas para evitar errores durante la importación.</li>
        <li>Sube el archivo SQL usando el formulario anterior.</li>
        <li>El script importará automáticamente la estructura y datos a la base de datos de Railway.</li>
    </ol>
    
    <div class='warning'>
        <p><strong>Nota:</strong> Este script modificará automáticamente las instrucciones DROP DATABASE, CREATE DATABASE y USE para adaptarlas al entorno de Railway.</p>
        <p>Si decides limpiar todas las tablas, asegúrate de tener una copia de seguridad si hay datos importantes en la base de datos.</p>
    </div>
";

// Cerrar la página HTML
echo "
    </div>
</body>
</html>"; 