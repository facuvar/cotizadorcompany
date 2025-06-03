<?php
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Configuración de Plazos de Entrega</h1>";
    
    // Verificar si existe la tabla plazos_entrega
    $result = $conn->query("SHOW TABLES LIKE 'plazos_entrega'");
    
    if ($result->num_rows === 0) {
        echo "<p>La tabla plazos_entrega no existe. Creando tabla...</p>";
        
        // Crear la tabla plazos_entrega
        $sql = "CREATE TABLE plazos_entrega (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            multiplicador DECIMAL(10,2) DEFAULT 1.00,
            orden INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✅ Tabla plazos_entrega creada correctamente.</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al crear la tabla: " . $conn->error . "</p>";
            exit;
        }
    } else {
        echo "<p>La tabla plazos_entrega ya existe.</p>";
    }
    
    // Verificar si hay plazos registrados
    $result = $conn->query("SELECT COUNT(*) as total FROM plazos_entrega");
    $row = $result->fetch_assoc();
    
    if ($row['total'] == 0) {
        echo "<p>No hay plazos de entrega registrados. Agregando plazos predeterminados...</p>";
        
        // Plazos predeterminados
        $plazos = [
            ['nombre' => '30-60 días', 'descripcion' => 'Entrega rápida (30-60 días)', 'multiplicador' => 1.15, 'orden' => 1],
            ['nombre' => '60-90 días', 'descripcion' => 'Entrega estándar (60-90 días)', 'multiplicador' => 1.10, 'orden' => 2],
            ['nombre' => '90-120 días', 'descripcion' => 'Entrega normal (90-120 días)', 'multiplicador' => 1.05, 'orden' => 3],
            ['nombre' => '120-150 días', 'descripcion' => 'Entrega programada (120-150 días)', 'multiplicador' => 1.00, 'orden' => 4],
            ['nombre' => '150-180 días', 'descripcion' => 'Entrega extendida (150-180 días)', 'multiplicador' => 0.95, 'orden' => 5],
            ['nombre' => '180-210 días', 'descripcion' => 'Entrega económica (180-210 días)', 'multiplicador' => 0.90, 'orden' => 6]
        ];
        
        // Insertar plazos
        $stmt = $conn->prepare("INSERT INTO plazos_entrega (nombre, descripcion, multiplicador, orden) VALUES (?, ?, ?, ?)");
        
        foreach ($plazos as $plazo) {
            $stmt->bind_param("ssdi", $plazo['nombre'], $plazo['descripcion'], $plazo['multiplicador'], $plazo['orden']);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✅ Plazo '{$plazo['nombre']}' agregado correctamente.</p>";
            } else {
                echo "<p style='color: red;'>❌ Error al agregar plazo '{$plazo['nombre']}': " . $stmt->error . "</p>";
            }
        }
    } else {
        echo "<p>Ya existen " . $row['total'] . " plazos de entrega registrados.</p>";
        
        // Mostrar plazos existentes
        $result = $conn->query("SELECT * FROM plazos_entrega ORDER BY orden ASC");
        
        if ($result->num_rows > 0) {
            echo "<h2>Plazos de entrega existentes</h2>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Multiplicador</th><th>Orden</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['nombre'] . "</td>";
                echo "<td>" . $row['descripcion'] . "</td>";
                echo "<td>" . $row['multiplicador'] . "</td>";
                echo "<td>" . $row['orden'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    }
    
    // Verificar si existe la tabla opcion_precios
    $result = $conn->query("SHOW TABLES LIKE 'opcion_precios'");
    
    if ($result->num_rows === 0) {
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
            echo "<p style='color: green;'>✅ Tabla opcion_precios creada correctamente.</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al crear la tabla: " . $conn->error . "</p>";
        }
    } else {
        echo "<h2>Tabla opcion_precios</h2>";
        echo "<p>La tabla opcion_precios ya existe.</p>";
        
        // Verificar si hay precios registrados
        $result = $conn->query("SELECT COUNT(*) as total FROM opcion_precios");
        $row = $result->fetch_assoc();
        echo "<p>Registros en opcion_precios: " . $row['total'] . "</p>";
    }
    
    // Verificar si necesitamos generar precios para las opciones según los plazos
    $result = $conn->query("SELECT COUNT(*) as total FROM opcion_precios");
    $row = $result->fetch_assoc();
    
    if ($row['total'] == 0) {
        echo "<h2>Generando precios por plazo para las opciones</h2>";
        
        // Obtener todas las opciones
        $opciones = $conn->query("SELECT id, precio FROM opciones");
        
        // Obtener todos los plazos
        $plazos = $conn->query("SELECT id, multiplicador FROM plazos_entrega");
        
        if ($opciones->num_rows > 0 && $plazos->num_rows > 0) {
            $plazosArray = [];
            while ($plazo = $plazos->fetch_assoc()) {
                $plazosArray[] = $plazo;
            }
            
            $stmt = $conn->prepare("INSERT INTO opcion_precios (opcion_id, plazo_id, precio) VALUES (?, ?, ?)");
            $totalInsertados = 0;
            
            while ($opcion = $opciones->fetch_assoc()) {
                foreach ($plazosArray as $plazo) {
                    // Calcular precio según el multiplicador del plazo
                    $precio = $opcion['precio'] * $plazo['multiplicador'];
                    
                    $stmt->bind_param("iid", $opcion['id'], $plazo['id'], $precio);
                    
                    if ($stmt->execute()) {
                        $totalInsertados++;
                    } else {
                        echo "<p style='color: red;'>❌ Error al insertar precio para opción ID " . $opcion['id'] . " y plazo ID " . $plazo['id'] . ": " . $stmt->error . "</p>";
                    }
                }
            }
            
            echo "<p style='color: green;'>✅ Se insertaron " . $totalInsertados . " registros de precios.</p>";
        } else {
            echo "<p style='color: red;'>No hay opciones o plazos para generar precios.</p>";
        }
    }
    
    echo "<h2>Configuración completada</h2>";
    echo "<p><a href='sistema/cotizador.php' style='padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Ir al Cotizador</a></p>";
    echo "<p><a href='verificar_estado.php' style='padding: 10px 15px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 4px;'>Verificar Estado</a></p>";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
