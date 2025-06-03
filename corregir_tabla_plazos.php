<?php
// Script para corregir la estructura de la tabla xls_plazos
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
        <title>Corregir Tabla Plazos</title>
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
        </style>
    </head>
    <body>
        <h1>Corregir Tabla Plazos</h1>";
    
    // Verificar si se solicitó la corrección
    if (isset($_POST['corregir'])) {
        // Verificar si la tabla xls_plazos existe
        $result = $conn->query("SHOW TABLES LIKE 'xls_plazos'");
        if ($result->num_rows == 0) {
            // Crear la tabla xls_plazos
            $conn->query("CREATE TABLE xls_plazos (
                id INT(11) NOT NULL AUTO_INCREMENT,
                nombre VARCHAR(100) NOT NULL,
                multiplicador DECIMAL(5,2) DEFAULT 1.00,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            mostrarMensaje("Tabla xls_plazos creada", "success");
        } else {
            // Verificar si la columna multiplicador existe
            $result = $conn->query("SHOW COLUMNS FROM xls_plazos LIKE 'multiplicador'");
            if ($result->num_rows == 0) {
                // Agregar la columna multiplicador
                $conn->query("ALTER TABLE xls_plazos ADD COLUMN multiplicador DECIMAL(5,2) DEFAULT 1.00");
                mostrarMensaje("Columna multiplicador agregada a la tabla xls_plazos", "success");
                
                // Actualizar los multiplicadores según el nombre del plazo
                $conn->query("UPDATE xls_plazos SET multiplicador = 1.3 WHERE nombre LIKE '%90%'");
                $conn->query("UPDATE xls_plazos SET multiplicador = 0.9 WHERE nombre LIKE '%270%'");
                $conn->query("UPDATE xls_plazos SET multiplicador = 1.0 WHERE multiplicador = 1.00");
                mostrarMensaje("Multiplicadores actualizados según el nombre del plazo", "success");
            } else {
                mostrarMensaje("La columna multiplicador ya existe en la tabla xls_plazos", "info");
            }
        }
        
        // Mostrar los plazos actuales
        $result = $conn->query("SELECT * FROM xls_plazos");
        if ($result->num_rows > 0) {
            echo "<h2>Plazos actuales</h2>";
            echo "<table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Multiplicador</th>
                </tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['nombre']}</td>
                    <td>{$row['multiplicador']}</td>
                </tr>";
            }
            
            echo "</table>";
        } else {
            mostrarMensaje("No hay plazos en la tabla xls_plazos", "warning");
            
            // Insertar plazos predeterminados
            $conn->query("INSERT INTO xls_plazos (nombre, multiplicador) VALUES 
                ('Precio 160-180 dias', 1.00),
                ('Precio 90 dias', 1.30),
                ('Precio 270 dias', 0.90)");
            mostrarMensaje("Plazos predeterminados insertados", "success");
        }
        
        // Verificar si la tabla plazos_entrega existe
        $result = $conn->query("SHOW TABLES LIKE 'plazos_entrega'");
        if ($result->num_rows == 0) {
            // Crear la tabla plazos_entrega
            $conn->query("CREATE TABLE plazos_entrega (
                id INT(11) NOT NULL AUTO_INCREMENT,
                nombre VARCHAR(100) NOT NULL,
                descripcion VARCHAR(255),
                multiplicador DECIMAL(5,2) DEFAULT 1.00,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            mostrarMensaje("Tabla plazos_entrega creada", "success");
            
            // Insertar plazos predeterminados
            $conn->query("INSERT INTO plazos_entrega (nombre, descripcion, multiplicador) VALUES 
                ('160-180 dias', 'Plazo estándar (160-180 días)', 1.00),
                ('90 dias', 'Plazo rápido (90 días)', 1.30),
                ('270 dias', 'Plazo económico (270 días)', 0.90)");
            mostrarMensaje("Plazos predeterminados insertados en plazos_entrega", "success");
        } else {
            // Desactivar restricciones de clave foránea
            $conn->query("SET FOREIGN_KEY_CHECKS = 0");
            
            // Limpiar tabla plazos_entrega
            $conn->query("TRUNCATE TABLE plazos_entrega");
            
            // Copiar plazos desde xls_plazos
            $conn->query("INSERT INTO plazos_entrega (nombre, descripcion, multiplicador)
                SELECT nombre, CONCAT('Plazo de entrega: ', nombre), multiplicador FROM xls_plazos");
            
            // Reactivar restricciones de clave foránea
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            
            mostrarMensaje("Plazos copiados desde xls_plazos a plazos_entrega", "success");
        }
        
        // Verificar si la tabla opcion_precios existe
        $result = $conn->query("SHOW TABLES LIKE 'opcion_precios'");
        if ($result->num_rows == 0) {
            // Crear la tabla opcion_precios
            $conn->query("CREATE TABLE opcion_precios (
                id INT(11) NOT NULL AUTO_INCREMENT,
                opcion_id INT(11) NOT NULL,
                plazo_entrega VARCHAR(100) NOT NULL,
                precio DECIMAL(15,2) NOT NULL,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            mostrarMensaje("Tabla opcion_precios creada", "success");
        }
        
        mostrarMensaje("Estructura de tablas corregida correctamente", "success");
        echo "<p><a href='sincronizar_datos_cotizador.php' class='btn'>Volver a Sincronizar Datos</a></p>";
    } else {
        // Mostrar estructura actual
        echo "<h2>Estructura Actual de la Tabla xls_plazos</h2>";
        
        $result = $conn->query("SHOW TABLES LIKE 'xls_plazos'");
        if ($result->num_rows > 0) {
            // Mostrar estructura
            $result = $conn->query("DESCRIBE xls_plazos");
            echo "<table>
                <tr>
                    <th>Campo</th>
                    <th>Tipo</th>
                    <th>Nulo</th>
                    <th>Clave</th>
                    <th>Predeterminado</th>
                    <th>Extra</th>
                </tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['Field']}</td>
                    <td>{$row['Type']}</td>
                    <td>{$row['Null']}</td>
                    <td>{$row['Key']}</td>
                    <td>{$row['Default']}</td>
                    <td>{$row['Extra']}</td>
                </tr>";
            }
            
            echo "</table>";
            
            // Mostrar datos
            $result = $conn->query("SELECT * FROM xls_plazos");
            if ($result->num_rows > 0) {
                echo "<h3>Datos de xls_plazos</h3>";
                echo "<table>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>";
                
                // Verificar si existe la columna multiplicador
                $columnas = $conn->query("SHOW COLUMNS FROM xls_plazos");
                $tieneMultiplicador = false;
                while ($columna = $columnas->fetch_assoc()) {
                    if ($columna['Field'] == 'multiplicador') {
                        $tieneMultiplicador = true;
                        echo "<th>Multiplicador</th>";
                        break;
                    }
                }
                
                echo "</tr>";
                
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['nombre']}</td>";
                    
                    if ($tieneMultiplicador) {
                        echo "<td>{$row['multiplicador']}</td>";
                    }
                    
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                mostrarMensaje("No hay datos en la tabla xls_plazos", "warning");
            }
        } else {
            mostrarMensaje("La tabla xls_plazos no existe", "error");
        }
        
        // Mostrar formulario de corrección
        echo "
        <div class='card'>
            <p>Este script corregirá la estructura de la tabla xls_plazos y otras tablas relacionadas.</p>
            <p>Se realizarán las siguientes acciones:</p>
            <ul>
                <li>Verificar y crear la tabla xls_plazos si no existe</li>
                <li>Agregar la columna multiplicador a la tabla xls_plazos si no existe</li>
                <li>Actualizar los multiplicadores según el nombre del plazo</li>
                <li>Verificar y crear la tabla plazos_entrega si no existe</li>
                <li>Sincronizar los plazos entre xls_plazos y plazos_entrega</li>
                <li>Verificar y crear la tabla opcion_precios si no existe</li>
            </ul>
            
            <form method='post'>
                <button type='submit' name='corregir' class='btn'>Corregir Estructura</button>
            </form>
        </div>";
    }
    
    echo "</body></html>";
    
} catch (Exception $e) {
    mostrarMensaje("Error: " . $e->getMessage(), "error");
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
