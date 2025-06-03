<?php
// Script para verificar la estructura de las tablas
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $color = 'black';
    if ($tipo == 'success') $color = 'green';
    if ($tipo == 'error') $color = 'red';
    if ($tipo == 'warning') $color = 'orange';
    
    echo "<p style='color: $color;'>$mensaje</p>";
}

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Verificar Estructura de Base de Datos</title>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
            h1, h2, h3 { color: #333; }
            .success { color: green; }
            .error { color: red; }
            .warning { color: orange; }
            .card { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: white; }
            .btn { display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; border: none; cursor: pointer; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        </style>
    </head>
    <body>
        <h1>Verificación de Estructura de Base de Datos</h1>";
    
    // Verificar si existe la tabla presupuesto_detalles
    $result = $conn->query("SHOW TABLES LIKE 'presupuesto_detalles'");
    if ($result->num_rows > 0) {
        echo "<h2>Tabla presupuesto_detalles existe</h2>";
        
        // Mostrar estructura
        echo "<h3>Estructura:</h3>";
        $result = $conn->query("SHOW CREATE TABLE presupuesto_detalles");
        if ($row = $result->fetch_assoc()) {
            echo "<pre>" . htmlspecialchars($row['Create Table']) . "</pre>";
        }
        
        // Contar registros
        $result = $conn->query("SELECT COUNT(*) as total FROM presupuesto_detalles");
        if ($row = $result->fetch_assoc()) {
            echo "<p><strong>Total de registros:</strong> " . $row['total'] . "</p>";
        }
        
        // Mostrar registros que referencian opciones
        if ($row['total'] > 0) {
            echo "<h3>Registros que referencian opciones:</h3>";
            $result = $conn->query("
                SELECT pd.*, o.nombre as opcion_nombre 
                FROM presupuesto_detalles pd 
                LEFT JOIN opciones o ON pd.opcion_id = o.id 
                LIMIT 10
            ");
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Presupuesto ID</th><th>Opción ID</th><th>Nombre Opción</th><th>Precio</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['presupuesto_id'] . "</td>";
                echo "<td>" . $row['opcion_id'] . "</td>";
                echo "<td>" . ($row['opcion_nombre'] ?? 'OPCIÓN ELIMINADA') . "</td>";
                echo "<td>" . $row['precio'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<h2>Tabla presupuesto_detalles NO existe</h2>";
    }
    
    // Verificar restricciones de claves foráneas
    echo "<h2>Restricciones de Claves Foráneas</h2>";
    $result = $conn->query("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME,
            DELETE_RULE,
            UPDATE_RULE
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE 
            REFERENCED_TABLE_SCHEMA = '" . DB_NAME . "' 
            AND REFERENCED_TABLE_NAME = 'opciones'
    ");
    
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Tabla</th><th>Columna</th><th>Restricción</th><th>Tabla Ref.</th><th>Columna Ref.</th><th>Delete Rule</th><th>Update Rule</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['TABLE_NAME'] . "</td>";
            echo "<td>" . $row['COLUMN_NAME'] . "</td>";
            echo "<td>" . $row['CONSTRAINT_NAME'] . "</td>";
            echo "<td>" . $row['REFERENCED_TABLE_NAME'] . "</td>";
            echo "<td>" . $row['REFERENCED_COLUMN_NAME'] . "</td>";
            echo "<td>" . $row['DELETE_RULE'] . "</td>";
            echo "<td>" . $row['UPDATE_RULE'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No se encontraron restricciones de claves foráneas que referencien la tabla 'opciones'</p>";
    }
    
    echo "</body></html>";
    
} catch (Exception $e) {
    mostrarMensaje("Error: " . $e->getMessage(), "error");
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
