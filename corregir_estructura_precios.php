<?php
// Script para corregir la estructura de precios en la base de datos
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
        <title>Corregir Estructura de Precios</title>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
            h1, h2, h3 { color: #333; }
            .success { color: green; }
            .error { color: red; }
            .warning { color: orange; }
            .card { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: white; }
            .btn { display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; border: none; cursor: pointer; }
        </style>
    </head>
    <body>
        <h1>Corregir Estructura de Precios</h1>";
    
    // Verificar si se solicitó la corrección
    if (isset($_POST['corregir'])) {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // 1. Verificar si existe la tabla xls_precios
            $result = $conn->query("SHOW TABLES LIKE 'xls_precios'");
            if ($result->num_rows == 0) {
                // Crear tabla xls_precios
                $sql = "CREATE TABLE xls_precios (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    opcion_id INT(11) NOT NULL,
                    plazo_id INT(11) NOT NULL,
                    precio DECIMAL(15,2) NOT NULL,
                    PRIMARY KEY (id),
                    KEY opcion_id (opcion_id),
                    KEY plazo_id (plazo_id),
                    CONSTRAINT fk_xls_precios_opcion FOREIGN KEY (opcion_id) REFERENCES xls_opciones (id) ON DELETE CASCADE,
                    CONSTRAINT fk_xls_precios_plazo FOREIGN KEY (plazo_id) REFERENCES xls_plazos (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                
                if ($conn->query($sql)) {
                    mostrarMensaje("Tabla xls_precios creada correctamente", "success");
                } else {
                    throw new Exception("Error al crear la tabla xls_precios: " . $conn->error);
                }
            } else {
                mostrarMensaje("La tabla xls_precios ya existe", "info");
                
                // Verificar la estructura de la tabla
                $result = $conn->query("SHOW COLUMNS FROM xls_precios LIKE 'plazo_entrega'");
                if ($result->num_rows > 0) {
                    // La columna plazo_entrega existe, hay que migrar a la nueva estructura
                    mostrarMensaje("Migrando estructura antigua a nueva estructura...", "info");
                    
                    // Crear tabla temporal
                    $conn->query("CREATE TABLE xls_precios_temp (
                        id INT(11) NOT NULL AUTO_INCREMENT,
                        opcion_id INT(11) NOT NULL,
                        plazo_id INT(11) NOT NULL,
                        precio DECIMAL(15,2) NOT NULL,
                        PRIMARY KEY (id),
                        KEY opcion_id (opcion_id),
                        KEY plazo_id (plazo_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                    
                    // Verificar si existe la tabla de plazos
                    $result = $conn->query("SHOW TABLES LIKE 'xls_plazos'");
                    if ($result->num_rows == 0) {
                        // Crear tabla de plazos
                        $conn->query("CREATE TABLE xls_plazos (
                            id INT(11) NOT NULL AUTO_INCREMENT,
                            nombre VARCHAR(100) NOT NULL,
                            multiplicador DECIMAL(5,2) DEFAULT 1.00,
                            PRIMARY KEY (id)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                        
                        // Insertar plazos predeterminados
                        $conn->query("INSERT INTO xls_plazos (nombre, multiplicador) VALUES 
                            ('160-180 dias', 1.00),
                            ('90 dias', 1.30),
                            ('270 dias', 0.90)");
                        
                        mostrarMensaje("Tabla xls_plazos creada con plazos predeterminados", "success");
                    }
                    
                    // Obtener plazos distintos
                    $result = $conn->query("SELECT DISTINCT plazo_entrega FROM xls_precios");
                    while ($row = $result->fetch_assoc()) {
                        $plazoEntrega = $row['plazo_entrega'];
                        
                        // Buscar o crear el plazo correspondiente
                        $stmt = $conn->prepare("SELECT id FROM xls_plazos WHERE nombre LIKE ?");
                        $busqueda = "%$plazoEntrega%";
                        $stmt->bind_param("s", $busqueda);
                        $stmt->execute();
                        $plazoResult = $stmt->get_result();
                        
                        if ($plazoResult->num_rows > 0) {
                            $plazoRow = $plazoResult->fetch_assoc();
                            $plazoId = $plazoRow['id'];
                        } else {
                            // Crear nuevo plazo
                            $multiplicador = 1.0;
                            if (strpos($plazoEntrega, "90") !== false) {
                                $multiplicador = 1.3; // 30% adicional
                            } else if (strpos($plazoEntrega, "270") !== false) {
                                $multiplicador = 0.9; // 10% descuento
                            }
                            
                            $stmt = $conn->prepare("INSERT INTO xls_plazos (nombre, multiplicador) VALUES (?, ?)");
                            $stmt->bind_param("sd", $plazoEntrega, $multiplicador);
                            $stmt->execute();
                            $plazoId = $conn->insert_id;
                            
                            mostrarMensaje("Plazo creado: $plazoEntrega (ID: $plazoId)", "success");
                        }
                        
                        // Migrar precios a la nueva estructura
                        $stmt = $conn->prepare("INSERT INTO xls_precios_temp (opcion_id, plazo_id, precio) 
                                               SELECT opcion_id, ?, precio FROM xls_precios WHERE plazo_entrega = ?");
                        $stmt->bind_param("is", $plazoId, $plazoEntrega);
                        $stmt->execute();
                    }
                    
                    // Renombrar tablas
                    $conn->query("DROP TABLE xls_precios");
                    $conn->query("RENAME TABLE xls_precios_temp TO xls_precios");
                    
                    // Agregar restricciones de clave foránea
                    $conn->query("ALTER TABLE xls_precios 
                                 ADD CONSTRAINT fk_xls_precios_opcion FOREIGN KEY (opcion_id) REFERENCES xls_opciones (id) ON DELETE CASCADE,
                                 ADD CONSTRAINT fk_xls_precios_plazo FOREIGN KEY (plazo_id) REFERENCES xls_plazos (id) ON DELETE CASCADE");
                    
                    mostrarMensaje("Migración completada correctamente", "success");
                } else {
                    // Verificar si la tabla tiene la estructura correcta
                    $result = $conn->query("SHOW COLUMNS FROM xls_precios LIKE 'plazo_id'");
                    if ($result->num_rows == 0) {
                        // La tabla no tiene la estructura correcta, recrearla
                        mostrarMensaje("La tabla xls_precios no tiene la estructura correcta. Recreando...", "warning");
                        
                        // Eliminar la tabla existente
                        $conn->query("DROP TABLE xls_precios");
                        
                        // Crear tabla con la estructura correcta
                        $sql = "CREATE TABLE xls_precios (
                            id INT(11) NOT NULL AUTO_INCREMENT,
                            opcion_id INT(11) NOT NULL,
                            plazo_id INT(11) NOT NULL,
                            precio DECIMAL(15,2) NOT NULL,
                            PRIMARY KEY (id),
                            KEY opcion_id (opcion_id),
                            KEY plazo_id (plazo_id),
                            CONSTRAINT fk_xls_precios_opcion FOREIGN KEY (opcion_id) REFERENCES xls_opciones (id) ON DELETE CASCADE,
                            CONSTRAINT fk_xls_precios_plazo FOREIGN KEY (plazo_id) REFERENCES xls_plazos (id) ON DELETE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                        
                        if ($conn->query($sql)) {
                            mostrarMensaje("Tabla xls_precios recreada correctamente", "success");
                        } else {
                            throw new Exception("Error al recrear la tabla xls_precios: " . $conn->error);
                        }
                    } else {
                        mostrarMensaje("La tabla xls_precios ya tiene la estructura correcta", "success");
                    }
                }
            }
            
            // 2. Verificar si existe la tabla opcion_precios (necesaria para el cotizador)
            $result = $conn->query("SHOW TABLES LIKE 'opcion_precios'");
            if ($result->num_rows == 0) {
                // Crear tabla opcion_precios
                $sql = "CREATE TABLE opcion_precios (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    opcion_id INT(11) NOT NULL,
                    plazo_entrega VARCHAR(100) NOT NULL,
                    precio DECIMAL(15,2) NOT NULL,
                    PRIMARY KEY (id),
                    KEY opcion_id (opcion_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                
                if ($conn->query($sql)) {
                    mostrarMensaje("Tabla opcion_precios creada correctamente", "success");
                } else {
                    throw new Exception("Error al crear la tabla opcion_precios: " . $conn->error);
                }
            } else {
                mostrarMensaje("La tabla opcion_precios ya existe", "info");
            }
            
            // 3. Verificar si existe la tabla plazos_entrega (necesaria para el cotizador)
            $result = $conn->query("SHOW TABLES LIKE 'plazos_entrega'");
            if ($result->num_rows == 0) {
                // Crear tabla plazos_entrega
                $sql = "CREATE TABLE plazos_entrega (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    nombre VARCHAR(100) NOT NULL,
                    multiplicador DECIMAL(5,2) DEFAULT 1.00,
                    PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                
                if ($conn->query($sql)) {
                    mostrarMensaje("Tabla plazos_entrega creada correctamente", "success");
                    
                    // Insertar plazos predeterminados
                    $sql = "INSERT INTO plazos_entrega (nombre, multiplicador) VALUES 
                        ('160-180 dias', 1.00),
                        ('90 dias', 1.30),
                        ('270 dias', 0.90)";
                    
                    if ($conn->query($sql)) {
                        mostrarMensaje("Plazos predeterminados insertados correctamente", "success");
                    } else {
                        throw new Exception("Error al insertar plazos predeterminados: " . $conn->error);
                    }
                } else {
                    throw new Exception("Error al crear la tabla plazos_entrega: " . $conn->error);
                }
            } else {
                mostrarMensaje("La tabla plazos_entrega ya existe", "info");
            }
            
            // 4. Sincronizar datos entre xls_plazos y plazos_entrega
            $result = $conn->query("SELECT * FROM xls_plazos");
            while ($row = $result->fetch_assoc()) {
                $nombre = $row['nombre'];
                $multiplicador = $row['multiplicador'];
                
                // Verificar si el plazo ya existe en plazos_entrega
                $stmt = $conn->prepare("SELECT id FROM plazos_entrega WHERE nombre LIKE ?");
                $busqueda = "%$nombre%";
                $stmt->bind_param("s", $busqueda);
                $stmt->execute();
                $plazoResult = $stmt->get_result();
                
                if ($plazoResult->num_rows == 0) {
                    // Insertar el plazo en plazos_entrega
                    $stmt = $conn->prepare("INSERT INTO plazos_entrega (nombre, multiplicador) VALUES (?, ?)");
                    $stmt->bind_param("sd", $nombre, $multiplicador);
                    $stmt->execute();
                    
                    mostrarMensaje("Plazo sincronizado: $nombre", "success");
                }
            }
            
            // Confirmar transacción
            $conn->commit();
            
            mostrarMensaje("Estructura de precios corregida correctamente", "success");
            echo "<p><a href='importar_xls_formulas_v2.php' class='btn'>Volver al Importador</a></p>";
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $conn->rollback();
            mostrarMensaje("Error al corregir la estructura: " . $e->getMessage(), "error");
        }
    } else {
        // Mostrar formulario de corrección
        echo "
        <div class='card'>
            <p>Este script corregirá la estructura de las tablas de precios y plazos en la base de datos para que sean compatibles con el cotizador.</p>
            <p>Se realizarán las siguientes acciones:</p>
            <ul>
                <li>Verificar y corregir la estructura de la tabla xls_precios</li>
                <li>Verificar y crear la tabla opcion_precios si no existe</li>
                <li>Verificar y crear la tabla plazos_entrega si no existe</li>
                <li>Sincronizar datos entre xls_plazos y plazos_entrega</li>
            </ul>
            
            <form method='post'>
                <button type='submit' name='corregir' class='btn'>Corregir Estructura de Precios</button>
            </form>
        </div>";
    }
    
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>Error: " . $e->getMessage() . "</p>";
}
?>
