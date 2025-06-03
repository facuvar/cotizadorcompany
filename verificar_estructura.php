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
        <title>Verificar Estructura de Tablas</title>
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
        <h1>Verificar Estructura de Tablas</h1>";
    
    // Verificar si se solicitó la corrección
    if (isset($_POST['corregir'])) {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Desactivar restricciones de clave foránea
            $conn->query("SET FOREIGN_KEY_CHECKS = 0");
            
            // 1. Corregir tabla xls_opciones
            $result = $conn->query("SHOW COLUMNS FROM xls_opciones LIKE 'producto_id'");
            if ($result->num_rows == 0) {
                // La columna no existe, renombrar la columna existente
                $result = $conn->query("SHOW COLUMNS FROM xls_opciones");
                $columnas = [];
                while ($row = $result->fetch_assoc()) {
                    $columnas[] = $row['Field'];
                }
                
                if (in_array('producto_id', $columnas)) {
                    mostrarMensaje("La columna producto_id ya existe en xls_opciones", "success");
                } else {
                    // Buscar la columna que contiene el ID del producto
                    $encontrada = false;
                    foreach ($columnas as $columna) {
                        if ($columna != 'id' && $columna != 'nombre') {
                            // Intentar renombrar esta columna
                            $sql = "ALTER TABLE xls_opciones CHANGE `$columna` `producto_id` INT(11) NOT NULL";
                            if ($conn->query($sql)) {
                                mostrarMensaje("Columna $columna renombrada a producto_id en xls_opciones", "success");
                                $encontrada = true;
                                break;
                            }
                        }
                    }
                    
                    if (!$encontrada) {
                        // Agregar la columna producto_id
                        $sql = "ALTER TABLE xls_opciones ADD COLUMN `producto_id` INT(11) NOT NULL AFTER `id`";
                        if ($conn->query($sql)) {
                            mostrarMensaje("Columna producto_id agregada a xls_opciones", "success");
                        } else {
                            throw new Exception("Error al agregar columna producto_id a xls_opciones: " . $conn->error);
                        }
                    }
                }
            } else {
                mostrarMensaje("La columna producto_id ya existe en xls_opciones", "success");
            }
            
            // 2. Crear tablas para el cotizador
            $tablas = [
                'productos' => "CREATE TABLE productos (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    nombre VARCHAR(255) NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
                
                'opciones' => "CREATE TABLE opciones (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    producto_id INT(11) NOT NULL,
                    nombre VARCHAR(255) NOT NULL,
                    PRIMARY KEY (id),
                    KEY producto_id (producto_id),
                    CONSTRAINT fk_opciones_producto FOREIGN KEY (producto_id) REFERENCES productos (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
                
                'plazos_entrega' => "CREATE TABLE plazos_entrega (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    nombre VARCHAR(100) NOT NULL,
                    multiplicador DECIMAL(5,2) DEFAULT 1.00,
                    PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
                
                'opcion_precios' => "CREATE TABLE opcion_precios (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    opcion_id INT(11) NOT NULL,
                    plazo_entrega VARCHAR(100) NOT NULL,
                    precio DECIMAL(15,2) NOT NULL,
                    PRIMARY KEY (id),
                    KEY opcion_id (opcion_id),
                    CONSTRAINT fk_opcion_precios_opcion FOREIGN KEY (opcion_id) REFERENCES opciones (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            ];
            
            foreach ($tablas as $tabla => $sql) {
                $result = $conn->query("SHOW TABLES LIKE '$tabla'");
                if ($result->num_rows == 0) {
                    if ($conn->query($sql)) {
                        mostrarMensaje("Tabla $tabla creada correctamente", "success");
                    } else {
                        throw new Exception("Error al crear la tabla $tabla: " . $conn->error);
                    }
                } else {
                    mostrarMensaje("La tabla $tabla ya existe", "info");
                }
            }
            
            // 3. Insertar plazos predeterminados
            $result = $conn->query("SELECT COUNT(*) as total FROM plazos_entrega");
            $row = $result->fetch_assoc();
            if ($row['total'] == 0) {
                $sql = "INSERT INTO plazos_entrega (nombre, multiplicador) VALUES 
                    ('160-180 dias', 1.00),
                    ('90 dias', 1.30),
                    ('270 dias', 0.90)";
                
                if ($conn->query($sql)) {
                    mostrarMensaje("Plazos predeterminados insertados correctamente", "success");
                } else {
                    throw new Exception("Error al insertar plazos predeterminados: " . $conn->error);
                }
            }
            
            // 4. Sincronizar datos
            // 4.1 Sincronizar productos
            $conn->query("TRUNCATE TABLE productos");
            $conn->query("INSERT INTO productos (id, nombre) SELECT id, nombre FROM xls_productos");
            $productosCount = $conn->affected_rows;
            mostrarMensaje("$productosCount productos sincronizados", "success");
            
            // 4.2 Sincronizar opciones
            $conn->query("TRUNCATE TABLE opciones");
            
            // Verificar si la tabla xls_opciones tiene la columna producto_id
            $result = $conn->query("SHOW COLUMNS FROM xls_opciones LIKE 'producto_id'");
            if ($result->num_rows > 0) {
                $conn->query("INSERT INTO opciones (id, producto_id, nombre) SELECT id, producto_id, nombre FROM xls_opciones");
                $opcionesCount = $conn->affected_rows;
                mostrarMensaje("$opcionesCount opciones sincronizadas", "success");
            } else {
                // Buscar la columna que contiene el ID del producto
                $result = $conn->query("SHOW COLUMNS FROM xls_opciones");
                $columnas = [];
                while ($row = $result->fetch_assoc()) {
                    $columnas[] = $row['Field'];
                }
                
                // Intentar encontrar la columna correcta
                foreach ($columnas as $columna) {
                    if ($columna != 'id' && $columna != 'nombre') {
                        // Intentar usar esta columna como producto_id
                        $sql = "INSERT INTO opciones (id, producto_id, nombre) SELECT id, `$columna`, nombre FROM xls_opciones";
                        if ($conn->query($sql)) {
                            $opcionesCount = $conn->affected_rows;
                            mostrarMensaje("$opcionesCount opciones sincronizadas usando la columna $columna como producto_id", "success");
                            break;
                        }
                    }
                }
            }
            
            // 4.3 Sincronizar precios
            $conn->query("TRUNCATE TABLE opcion_precios");
            
            // Obtener precios de xls_precios
            $result = $conn->query("
                SELECT xp.opcion_id, xpl.nombre AS plazo_entrega, xp.precio
                FROM xls_precios xp
                JOIN xls_plazos xpl ON xp.plazo_id = xpl.id
            ");
            
            if ($result && $result->num_rows > 0) {
                $preciosCount = 0;
                while ($row = $result->fetch_assoc()) {
                    $opcionId = $row['opcion_id'];
                    $plazoEntrega = $row['plazo_entrega'];
                    $precio = $row['precio'];
                    
                    $stmt = $conn->prepare("INSERT INTO opcion_precios (opcion_id, plazo_entrega, precio) VALUES (?, ?, ?)");
                    $stmt->bind_param("isd", $opcionId, $plazoEntrega, $precio);
                    $stmt->execute();
                    $preciosCount++;
                }
                mostrarMensaje("$preciosCount precios sincronizados", "success");
            } else {
                mostrarMensaje("No se encontraron precios para sincronizar", "warning");
            }
            
            // Reactivar restricciones de clave foránea
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            
            // Confirmar transacción
            $conn->commit();
            
            mostrarMensaje("Estructura verificada y corregida correctamente", "success");
            echo "<p><a href='cotizador_xls_fixed.php' class='btn'>Ir al Cotizador</a></p>";
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $conn->rollback();
            mostrarMensaje("Error al verificar la estructura: " . $e->getMessage(), "error");
        }
    } else {
        // Mostrar estructura actual
        echo "<h2>Estructura Actual de las Tablas</h2>";
        
        $tablas = ['xls_productos', 'xls_opciones', 'xls_plazos', 'xls_precios', 'productos', 'opciones', 'plazos_entrega', 'opcion_precios'];
        
        foreach ($tablas as $tabla) {
            $result = $conn->query("SHOW TABLES LIKE '$tabla'");
            if ($result->num_rows > 0) {
                echo "<h3>Tabla: $tabla</h3>";
                
                // Mostrar estructura
                $result = $conn->query("DESCRIBE $tabla");
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
                
                // Mostrar cantidad de registros
                $result = $conn->query("SELECT COUNT(*) as total FROM $tabla");
                $row = $result->fetch_assoc();
                echo "<p>Total de registros: {$row['total']}</p>";
            } else {
                echo "<h3>Tabla: $tabla</h3>";
                echo "<p>La tabla no existe</p>";
            }
        }
        
        // Mostrar formulario de corrección
        echo "
        <div class='card'>
            <p>Este script verificará la estructura de las tablas y corregirá los problemas encontrados.</p>
            <p>Se realizarán las siguientes acciones:</p>
            <ul>
                <li>Verificar y corregir la estructura de la tabla xls_opciones</li>
                <li>Verificar y crear las tablas necesarias para el cotizador</li>
                <li>Sincronizar datos entre las tablas</li>
            </ul>
            
            <form method='post'>
                <button type='submit' name='corregir' class='btn'>Verificar y Corregir Estructura</button>
            </form>
        </div>";
    }
    
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>Error: " . $e->getMessage() . "</p>";
}
?>
