<?php
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Configuración de Plazos de Entrega</title>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
            h1, h2 { color: #333; }
            table { border-collapse: collapse; margin-bottom: 20px; width: 100%; }
            th { background-color: #f2f2f2; }
            td, th { padding: 8px; text-align: left; border: 1px solid #ddd; }
            .success { color: green; }
            .error { color: red; }
            .warning { color: orange; }
            .section { margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
            .btn { display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; }
            .btn-blue { background-color: #2196F3; }
        </style>
    </head>
    <body>
    <h1>Configuración de Plazos de Entrega</h1>";
    
    // Verificar si existe la tabla plazos_entrega
    $result = $conn->query("SHOW TABLES LIKE 'plazos_entrega'");
    
    if ($result->num_rows === 0) {
        echo "<div class='section'>";
        echo "<p>La tabla plazos_entrega no existe. Creando tabla...</p>";
        
        // Crear la tabla plazos_entrega
        $sql = "CREATE TABLE plazos_entrega (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            factor DECIMAL(10,2) DEFAULT 1.00,
            orden INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($conn->query($sql)) {
            echo "<p class='success'>✅ Tabla plazos_entrega creada correctamente.</p>";
        } else {
            echo "<p class='error'>❌ Error al crear la tabla: " . $conn->error . "</p>";
            exit;
        }
        echo "</div>";
    } else {
        echo "<div class='section'>";
        echo "<p>La tabla plazos_entrega ya existe.</p>";
        
        // Verificar si tiene la estructura correcta
        $result = $conn->query("SHOW COLUMNS FROM plazos_entrega LIKE 'factor'");
        if ($result->num_rows === 0) {
            echo "<p class='warning'>La tabla plazos_entrega no tiene la columna 'factor'. Intentando agregarla...</p>";
            
            if ($conn->query("ALTER TABLE plazos_entrega ADD COLUMN factor DECIMAL(10,2) DEFAULT 1.00")) {
                echo "<p class='success'>✅ Columna 'factor' agregada correctamente.</p>";
            } else {
                echo "<p class='error'>❌ Error al agregar la columna 'factor': " . $conn->error . "</p>";
            }
        }
        echo "</div>";
    }
    
    // Verificar si hay plazos registrados
    $result = $conn->query("SELECT COUNT(*) as total FROM plazos_entrega");
    $row = $result->fetch_assoc();
    
    if ($row['total'] == 0) {
        echo "<div class='section'>";
        echo "<p>No hay plazos de entrega registrados. Agregando plazos predeterminados...</p>";
        
        // Plazos predeterminados
        $plazos = [
            ['nombre' => '30-60 días', 'descripcion' => 'Entrega rápida (30-60 días)', 'factor' => 1.15, 'orden' => 1],
            ['nombre' => '60-90 días', 'descripcion' => 'Entrega estándar (60-90 días)', 'factor' => 1.10, 'orden' => 2],
            ['nombre' => '90-120 días', 'descripcion' => 'Entrega normal (90-120 días)', 'factor' => 1.05, 'orden' => 3],
            ['nombre' => '120-150 días', 'descripcion' => 'Entrega programada (120-150 días)', 'factor' => 1.00, 'orden' => 4],
            ['nombre' => '150-180 días', 'descripcion' => 'Entrega extendida (150-180 días)', 'factor' => 0.95, 'orden' => 5],
            ['nombre' => '180-210 días', 'descripcion' => 'Entrega económica (180-210 días)', 'factor' => 0.90, 'orden' => 6]
        ];
        
        // Insertar plazos
        $stmt = $conn->prepare("INSERT INTO plazos_entrega (nombre, descripcion, factor, orden) VALUES (?, ?, ?, ?)");
        
        foreach ($plazos as $plazo) {
            $stmt->bind_param("ssdi", $plazo['nombre'], $plazo['descripcion'], $plazo['factor'], $plazo['orden']);
            
            if ($stmt->execute()) {
                echo "<p class='success'>✅ Plazo '{$plazo['nombre']}' agregado correctamente.</p>";
            } else {
                echo "<p class='error'>❌ Error al agregar plazo '{$plazo['nombre']}': " . $stmt->error . "</p>";
            }
        }
        echo "</div>";
    } else {
        echo "<div class='section'>";
        echo "<p>Ya existen " . $row['total'] . " plazos de entrega registrados.</p>";
        
        // Mostrar plazos existentes
        $result = $conn->query("SELECT * FROM plazos_entrega ORDER BY orden ASC");
        
        if ($result->num_rows > 0) {
            echo "<h2>Plazos de entrega existentes</h2>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Factor</th><th>Orden</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['nombre'] . "</td>";
                echo "<td>" . $row['descripcion'] . "</td>";
                echo "<td>" . (isset($row['factor']) ? $row['factor'] : 'N/A') . "</td>";
                echo "<td>" . $row['orden'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
        echo "</div>";
    }
    
    // Verificar si existe la tabla opcion_precios
    $result = $conn->query("SHOW TABLES LIKE 'opcion_precios'");
    
    if ($result->num_rows === 0) {
        echo "<div class='section'>";
        echo "<h2>Configuración de Precios por Plazo</h2>";
        echo "<p>La tabla opcion_precios no existe. Creando tabla...</p>";
        
        // Crear la tabla opcion_precios
        $sql = "CREATE TABLE opcion_precios (
            id INT(11) NOT NULL AUTO_INCREMENT,
            opcion_id INT(11) NOT NULL,
            plazo_id INT(11) NOT NULL,
            precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            PRIMARY KEY (id),
            FOREIGN KEY (opcion_id) REFERENCES opciones(id) ON DELETE CASCADE,
            FOREIGN KEY (plazo_id) REFERENCES plazos_entrega(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($conn->query($sql)) {
            echo "<p class='success'>✅ Tabla opcion_precios creada correctamente.</p>";
        } else {
            echo "<p class='error'>❌ Error al crear la tabla: " . $conn->error . "</p>";
        }
        echo "</div>";
    } else {
        echo "<div class='section'>";
        echo "<h2>Tabla opcion_precios</h2>";
        echo "<p>La tabla opcion_precios ya existe.</p>";
        
        // Verificar si hay precios registrados
        $result = $conn->query("SELECT COUNT(*) as total FROM opcion_precios");
        $row = $result->fetch_assoc();
        echo "<p>Registros en opcion_precios: " . $row['total'] . "</p>";
        echo "</div>";
    }
    
    // Verificar si necesitamos generar precios para las opciones según los plazos
    $result = $conn->query("SELECT COUNT(*) as total FROM opcion_precios");
    $row = $result->fetch_assoc();
    
    if ($row['total'] == 0) {
        echo "<div class='section'>";
        echo "<h2>Generando precios por plazo para las opciones</h2>";
        
        // Obtener todas las opciones
        $opciones = $conn->query("SELECT id, precio FROM opciones");
        
        // Obtener todos los plazos
        $plazos = $conn->query("SELECT id, factor FROM plazos_entrega");
        
        if ($opciones->num_rows > 0 && $plazos->num_rows > 0) {
            $plazosArray = [];
            while ($plazo = $plazos->fetch_assoc()) {
                $plazosArray[] = $plazo;
            }
            
            $stmt = $conn->prepare("INSERT INTO opcion_precios (opcion_id, plazo_id, precio) VALUES (?, ?, ?)");
            $totalInsertados = 0;
            
            while ($opcion = $opciones->fetch_assoc()) {
                foreach ($plazosArray as $plazo) {
                    // Calcular precio según el factor del plazo
                    $factorPlazo = isset($plazo['factor']) ? $plazo['factor'] : 1.0;
                    $precio = $opcion['precio'] * $factorPlazo;
                    
                    $stmt->bind_param("iid", $opcion['id'], $plazo['id'], $precio);
                    
                    if ($stmt->execute()) {
                        $totalInsertados++;
                    } else {
                        echo "<p class='error'>❌ Error al insertar precio para opción ID " . $opcion['id'] . " y plazo ID " . $plazo['id'] . ": " . $stmt->error . "</p>";
                    }
                }
            }
            
            echo "<p class='success'>✅ Se insertaron " . $totalInsertados . " registros de precios.</p>";
        } else {
            echo "<p class='error'>No hay opciones o plazos para generar precios.</p>";
        }
        echo "</div>";
    }
    
    // Modificar el script del cotizador para usar la tabla opcion_precios
    echo "<div class='section'>";
    echo "<h2>Configuración completada</h2>";
    echo "<p>Ahora el cotizador debería mostrar correctamente las opciones, precios y plazos.</p>";
    echo "<p><a href='sistema/cotizador.php' class='btn'>Ir al Cotizador</a> <a href='verificar_estado.php' class='btn btn-blue'>Verificar Estado</a></p>";
    echo "</div>";
    
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
