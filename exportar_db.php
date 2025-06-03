<?php
/**
 * Script para exportar la estructura y datos de la base de datos
 * Se generará un archivo SQL que luego puede ser importado en Railway
 */

// Configuración de tiempo de ejecución
set_time_limit(300); // 5 minutos
ini_set('memory_limit', '256M');

// Cargar configuración
$configPath = __DIR__ . '/sistema/config.php';
if (!file_exists($configPath)) {
    die("Error: Archivo de configuración no encontrado");
}
require_once $configPath;

// Función para mostrar mensajes con formato
function mostrarMensaje($mensaje, $tipo = 'info') {
    $estilos = [
        'info' => 'background-color: #e3f2fd; padding: 10px; margin: 5px 0; border-radius: 5px;',
        'success' => 'background-color: #e8f5e9; padding: 10px; margin: 5px 0; border-radius: 5px;',
        'error' => 'background-color: #ffebee; padding: 10px; margin: 5px 0; border-radius: 5px;'
    ];
    
    echo "<div style='{$estilos[$tipo]}'>{$mensaje}</div>";
}

// Función para exportar la estructura y datos de una tabla
function exportarTabla($conn, $tabla) {
    $output = "";
    
    // Obtener la estructura de la tabla
    $resultado = $conn->query("SHOW CREATE TABLE `{$tabla}`");
    if (!$resultado) {
        return "-- Error al obtener la estructura de la tabla {$tabla}: " . $conn->error . "\n";
    }
    
    $fila = $resultado->fetch_assoc();
    $createTable = $fila['Create Table'];
    
    // Agregar DROP TABLE si existe
    $output .= "DROP TABLE IF EXISTS `{$tabla}`;\n";
    $output .= $createTable . ";\n\n";
    
    // Obtener los datos de la tabla
    $resultado = $conn->query("SELECT * FROM `{$tabla}`");
    if (!$resultado) {
        return $output . "-- Error al obtener los datos de la tabla {$tabla}: " . $conn->error . "\n";
    }
    
    if ($resultado->num_rows > 0) {
        // Generar los INSERT
        $output .= "-- Datos para la tabla `{$tabla}`\n";
        
        while ($fila = $resultado->fetch_assoc()) {
            $columnas = array_keys($fila);
            $valores = array_map(function($valor) use ($conn) {
                if ($valor === null) {
                    return "NULL";
                }
                return "'" . $conn->real_escape_string($valor) . "'";
            }, array_values($fila));
            
            $output .= "INSERT INTO `{$tabla}` (`" . implode("`, `", $columnas) . "`) VALUES (" . implode(", ", $valores) . ");\n";
        }
        
        $output .= "\n";
    }
    
    return $output;
}

// Iniciar la página HTML
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Exportar Base de Datos</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        pre { background-color: #f8f8f8; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .btn { display: inline-block; background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Exportar Base de Datos</h1>";

// Conectar a la base de datos
mostrarMensaje("Conectando a la base de datos...");

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    mostrarMensaje("Conexión exitosa a la base de datos", "success");
    
    // Obtener lista de tablas
    $resultado = $conn->query("SHOW TABLES");
    if (!$resultado) {
        throw new Exception("Error al obtener la lista de tablas: " . $conn->error);
    }
    
    $tablas = [];
    while ($fila = $resultado->fetch_array()) {
        $tablas[] = $fila[0];
    }
    
    if (empty($tablas)) {
        mostrarMensaje("No se encontraron tablas en la base de datos", "error");
    } else {
        mostrarMensaje("Se encontraron " . count($tablas) . " tablas: " . implode(", ", $tablas), "success");
        
        // Iniciar la exportación
        $sql = "-- Exportación de base de datos '{$DB_NAME}'\n";
        $sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Agregar DROP DATABASE y CREATE DATABASE
        $sql .= "DROP DATABASE IF EXISTS `" . DB_NAME . "`;\n";
        $sql .= "CREATE DATABASE `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
        $sql .= "USE `" . DB_NAME . "`;\n\n";
        
        // Exportar cada tabla
        foreach ($tablas as $tabla) {
            mostrarMensaje("Exportando tabla: {$tabla}...");
            $sql .= exportarTabla($conn, $tabla);
        }
        
        // Guardar el archivo SQL
        $filename = 'db_export_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = __DIR__ . '/' . $filename;
        
        if (file_put_contents($filepath, $sql)) {
            mostrarMensaje("Exportación completada. Archivo guardado como: {$filename}", "success");
            echo "<p>Contenido del archivo (primeras 1000 caracteres):</p>";
            echo "<pre>" . htmlspecialchars(substr($sql, 0, 1000)) . "...</pre>";
            echo "<p><a class='btn' href='{$filename}' download>Descargar archivo SQL</a></p>";
        } else {
            mostrarMensaje("Error al guardar el archivo SQL", "error");
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    mostrarMensaje("Error: " . $e->getMessage(), "error");
}

// Cerrar la página HTML
echo "
    </div>
</body>
</html>"; 