<?php
require_once './sistema/config.php';
require_once './sistema/includes/db.php';

// Obtener instancia de la base de datos
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Reparación de la tabla opciones</h1>";
    
    // Verificar la estructura de la tabla
    $result = $conn->query('DESCRIBE opciones');
    
    if (!$result) {
        echo "<p style='color: red;'>Error al verificar la estructura de la tabla: " . $conn->error . "</p>";
        exit;
    }
    
    echo "<h2>Estructura actual de la tabla opciones:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
    
    $columnas = [];
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . (isset($row['Default']) ? $row['Default'] : 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
        
        $columnas[] = $row['Field'];
    }
    echo "</table>";
    
    // Verificar si la estructura es correcta
    $columnas_esperadas = ['id', 'categoria_id', 'nombre', 'descripcion', 'precio', 'multiplicador', 'es_obligatorio', 'orden'];
    $columnas_faltantes = array_diff($columnas_esperadas, $columnas);
    
    if (!empty($columnas_faltantes)) {
        echo "<p style='color: red;'>Faltan las siguientes columnas: " . implode(', ', $columnas_faltantes) . "</p>";
        
        // Agregar columnas faltantes
        echo "<h3>Agregando columnas faltantes...</h3>";
        
        foreach ($columnas_faltantes as $columna) {
            $sql = "";
            switch ($columna) {
                case 'multiplicador':
                    $sql = "ALTER TABLE opciones ADD COLUMN multiplicador decimal(10,2) DEFAULT NULL";
                    break;
                case 'es_obligatorio':
                    $sql = "ALTER TABLE opciones ADD COLUMN es_obligatorio tinyint(1) NOT NULL DEFAULT 0";
                    break;
                case 'orden':
                    $sql = "ALTER TABLE opciones ADD COLUMN orden int(11) NOT NULL DEFAULT 0";
                    break;
                default:
                    $sql = "ALTER TABLE opciones ADD COLUMN $columna VARCHAR(255) NULL";
            }
            
            if ($conn->query($sql)) {
                echo "<p style='color: green;'>✅ Columna '$columna' agregada correctamente.</p>";
            } else {
                echo "<p style='color: red;'>❌ Error al agregar columna '$columna': " . $conn->error . "</p>";
            }
        }
    } else {
        echo "<p style='color: green;'>✅ La estructura de la tabla es correcta.</p>";
    }
    
    // Verificar datos corruptos en la tabla
    echo "<h2>Verificando datos corruptos...</h2>";
    
    // Buscar datos corruptos en el campo multiplicador
    $result = $conn->query("SELECT id, multiplicador FROM opciones WHERE multiplicador LIKE '%http%' OR multiplicador LIKE '%google%'");
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: red;'>Se encontraron " . $result->num_rows . " registros con datos corruptos en el campo multiplicador.</p>";
        
        echo "<h3>Corrigiendo datos corruptos...</h3>";
        
        // Corregir los datos corruptos
        if ($conn->query("UPDATE opciones SET multiplicador = NULL WHERE multiplicador LIKE '%http%' OR multiplicador LIKE '%google%'")) {
            echo "<p style='color: green;'>✅ Datos corruptos corregidos correctamente.</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al corregir datos corruptos: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ No se encontraron datos corruptos en el campo multiplicador.</p>";
    }
    
    // Mostrar algunos registros para verificar
    echo "<h2>Muestra de datos después de la reparación:</h2>";
    $datos = $conn->query("SELECT * FROM opciones LIMIT 5");
    
    if ($datos && $datos->num_rows > 0) {
        echo "<table border='1'>";
        
        // Cabecera de la tabla
        echo "<tr>";
        $fields = $datos->fetch_fields();
        foreach ($fields as $field) {
            echo "<th>" . $field->name . "</th>";
        }
        echo "</tr>";
        
        // Datos
        $datos->data_seek(0);
        while ($fila = $datos->fetch_assoc()) {
            echo "<tr>";
            foreach ($fila as $campo => $valor) {
                echo "<td>" . (is_null($valor) ? "NULL" : htmlspecialchars($valor)) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No hay datos en la tabla opciones o ocurrió un error: " . $conn->error . "</p>";
    }
    
    echo "<h2>Reparación completada</h2>";
    echo "<p><a href='index.html'>Volver a la página principal</a></p>";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
