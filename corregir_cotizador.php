<?php
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Corrección del Cotizador</title>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
            h1, h2, h3 { color: #333; }
            .success { color: green; }
            .error { color: red; }
            .warning { color: orange; }
            .section { margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
            .btn { display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; }
            .btn-blue { background-color: #2196F3; }
        </style>
    </head>
    <body>
    <h1>Corrección del Cotizador</h1>";
    
    // Paso 1: Verificar la tabla plazos_entrega
    echo "<div class='section'>";
    echo "<h2>Paso 1: Verificar tabla plazos_entrega</h2>";
    
    $result = $conn->query("SHOW TABLES LIKE 'plazos_entrega'");
    if ($result->num_rows === 0) {
        echo "<p class='warning'>La tabla plazos_entrega no existe. Creando tabla...</p>";
        
        $sql = "CREATE TABLE plazos_entrega (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            orden INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($conn->query($sql)) {
            echo "<p class='success'>✅ Tabla plazos_entrega creada correctamente.</p>";
            
            // Insertar plazos predeterminados
            $plazos = [
                ['nombre' => '30-60 días', 'descripcion' => 'Entrega rápida (30-60 días)', 'orden' => 1],
                ['nombre' => '60-90 días', 'descripcion' => 'Entrega estándar (60-90 días)', 'orden' => 2],
                ['nombre' => '90-120 días', 'descripcion' => 'Entrega normal (90-120 días)', 'orden' => 3],
                ['nombre' => '120-150 días', 'descripcion' => 'Entrega programada (120-150 días)', 'orden' => 4],
                ['nombre' => '150-180 días', 'descripcion' => 'Entrega extendida (150-180 días)', 'orden' => 5],
                ['nombre' => '180-210 días', 'descripcion' => 'Entrega económica (180-210 días)', 'orden' => 6]
            ];
            
            foreach ($plazos as $plazo) {
                $sql = "INSERT INTO plazos_entrega (nombre, descripcion, orden) VALUES ('{$plazo['nombre']}', '{$plazo['descripcion']}', {$plazo['orden']})";
                if ($conn->query($sql)) {
                    echo "<p class='success'>✅ Plazo '{$plazo['nombre']}' agregado correctamente.</p>";
                } else {
                    echo "<p class='error'>❌ Error al agregar plazo: " . $conn->error . "</p>";
                }
            }
        } else {
            echo "<p class='error'>❌ Error al crear la tabla: " . $conn->error . "</p>";
        }
    } else {
        echo "<p class='success'>✅ La tabla plazos_entrega ya existe.</p>";
        
        // Verificar si tiene datos
        $result = $conn->query("SELECT COUNT(*) as total FROM plazos_entrega");
        $row = $result->fetch_assoc();
        
        if ($row['total'] == 0) {
            echo "<p class='warning'>La tabla plazos_entrega está vacía. Agregando plazos predeterminados...</p>";
            
            // Insertar plazos predeterminados
            $plazos = [
                ['nombre' => '30-60 días', 'descripcion' => 'Entrega rápida (30-60 días)', 'orden' => 1],
                ['nombre' => '60-90 días', 'descripcion' => 'Entrega estándar (60-90 días)', 'orden' => 2],
                ['nombre' => '90-120 días', 'descripcion' => 'Entrega normal (90-120 días)', 'orden' => 3],
                ['nombre' => '120-150 días', 'descripcion' => 'Entrega programada (120-150 días)', 'orden' => 4],
                ['nombre' => '150-180 días', 'descripcion' => 'Entrega extendida (150-180 días)', 'orden' => 5],
                ['nombre' => '180-210 días', 'descripcion' => 'Entrega económica (180-210 días)', 'orden' => 6]
            ];
            
            foreach ($plazos as $plazo) {
                $sql = "INSERT INTO plazos_entrega (nombre, descripcion, orden) VALUES ('{$plazo['nombre']}', '{$plazo['descripcion']}', {$plazo['orden']})";
                if ($conn->query($sql)) {
                    echo "<p class='success'>✅ Plazo '{$plazo['nombre']}' agregado correctamente.</p>";
                } else {
                    echo "<p class='error'>❌ Error al agregar plazo: " . $conn->error . "</p>";
                }
            }
        } else {
            echo "<p class='success'>✅ La tabla plazos_entrega tiene {$row['total']} registros.</p>";
        }
    }
    echo "</div>";
    
    // Paso 2: Verificar la tabla opcion_precios
    echo "<div class='section'>";
    echo "<h2>Paso 2: Verificar tabla opcion_precios</h2>";
    
    $result = $conn->query("SHOW TABLES LIKE 'opcion_precios'");
    if ($result->num_rows === 0) {
        echo "<p class='warning'>La tabla opcion_precios no existe. Creando tabla...</p>";
        
        $sql = "CREATE TABLE opcion_precios (
            id INT(11) NOT NULL AUTO_INCREMENT,
            opcion_id INT(11) NOT NULL,
            plazo_entrega VARCHAR(100) NOT NULL,
            precio DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($conn->query($sql)) {
            echo "<p class='success'>✅ Tabla opcion_precios creada correctamente.</p>";
        } else {
            echo "<p class='error'>❌ Error al crear la tabla: " . $conn->error . "</p>";
        }
    } else {
        echo "<p class='success'>✅ La tabla opcion_precios ya existe.</p>";
        
        // Verificar si tiene datos
        $result = $conn->query("SELECT COUNT(*) as total FROM opcion_precios");
        $row = $result->fetch_assoc();
        echo "<p>La tabla opcion_precios tiene {$row['total']} registros.</p>";
        
        if ($row['total'] > 0) {
            // Si ya tiene datos, no hacemos nada más
            echo "<p class='success'>✅ La tabla opcion_precios ya tiene datos.</p>";
        } else {
            echo "<p class='warning'>La tabla opcion_precios está vacía.</p>";
        }
    }
    echo "</div>";
    
    // Paso 3: Generar precios para todas las opciones
    echo "<div class='section'>";
    echo "<h2>Paso 3: Generar precios para todas las opciones</h2>";
    
    // Obtener todas las opciones
    $result = $conn->query("SELECT id, precio FROM opciones");
    
    if ($result->num_rows === 0) {
        echo "<p class='error'>❌ No hay opciones en la base de datos.</p>";
    } else {
        echo "<p>Se encontraron {$result->num_rows} opciones en la base de datos.</p>";
        
        // Obtener todos los plazos
        $plazosResult = $conn->query("SELECT nombre FROM plazos_entrega ORDER BY orden");
        
        if ($plazosResult->num_rows === 0) {
            echo "<p class='error'>❌ No hay plazos de entrega en la base de datos.</p>";
        } else {
            echo "<p>Se encontraron {$plazosResult->num_rows} plazos de entrega.</p>";
            
            // Limpiar tabla opcion_precios para evitar duplicados
            if ($conn->query("DELETE FROM opcion_precios")) {
                echo "<p class='success'>✅ Tabla opcion_precios limpiada para evitar duplicados.</p>";
            } else {
                echo "<p class='error'>❌ Error al limpiar la tabla opcion_precios: " . $conn->error . "</p>";
            }
            
            // Generar precios para cada opción y plazo
            $totalInsertados = 0;
            
            while ($opcion = $result->fetch_assoc()) {
                $plazosResult->data_seek(0); // Reiniciar el puntero
                
                while ($plazo = $plazosResult->fetch_assoc()) {
                    $plazoNombre = $plazo['nombre'];
                    $precio = $opcion['precio'];
                    
                    // Aplicar multiplicador según el plazo
                    if (strpos($plazoNombre, '30-60') !== false) {
                        $precio = $precio * 1.15; // 15% más caro para entrega rápida
                    } elseif (strpos($plazoNombre, '60-90') !== false) {
                        $precio = $precio * 1.10; // 10% más caro
                    } elseif (strpos($plazoNombre, '90-120') !== false) {
                        $precio = $precio * 1.05; // 5% más caro
                    } elseif (strpos($plazoNombre, '150-180') !== false) {
                        $precio = $precio * 0.95; // 5% más barato
                    } elseif (strpos($plazoNombre, '180-210') !== false) {
                        $precio = $precio * 0.90; // 10% más barato
                    }
                    
                    $sql = "INSERT INTO opcion_precios (opcion_id, plazo_entrega, precio) VALUES ({$opcion['id']}, '{$plazoNombre}', {$precio})";
                    
                    if ($conn->query($sql)) {
                        $totalInsertados++;
                    } else {
                        echo "<p class='error'>❌ Error al insertar precio para opción ID {$opcion['id']} y plazo {$plazoNombre}: " . $conn->error . "</p>";
                    }
                }
            }
            
            echo "<p class='success'>✅ Se insertaron {$totalInsertados} registros de precios.</p>";
        }
    }
    echo "</div>";
    
    // Paso 4: Verificar la estructura del cotizador
    echo "<div class='section'>";
    echo "<h2>Paso 4: Verificar la estructura del cotizador</h2>";
    
    // Verificar si el cotizador tiene la estructura correcta
    $cotizadorPath = __DIR__ . '/sistema/cotizador.php';
    
    if (file_exists($cotizadorPath)) {
        echo "<p class='success'>✅ El archivo cotizador.php existe.</p>";
        
        // Verificar si contiene la consulta correcta
        $cotizadorContent = file_get_contents($cotizadorPath);
        
        if (strpos($cotizadorContent, 'SELECT plazo_entrega, precio FROM opcion_precios') !== false) {
            echo "<p class='success'>✅ El cotizador está configurado correctamente para usar la tabla opcion_precios.</p>";
        } else {
            echo "<p class='warning'>⚠️ El cotizador podría no estar configurado correctamente para usar la tabla opcion_precios.</p>";
        }
    } else {
        echo "<p class='error'>❌ El archivo cotizador.php no existe en la ruta esperada.</p>";
    }
    echo "</div>";
    
    // Finalización
    echo "<div class='section'>";
    echo "<h2>Corrección completada</h2>";
    echo "<p>Se han realizado las correcciones necesarias para que el cotizador funcione correctamente.</p>";
    echo "<p>Ahora puedes acceder al cotizador y verificar que muestra correctamente las opciones, precios y plazos.</p>";
    echo "<p><a href='sistema/cotizador.php' class='btn'>Ir al Cotizador</a> <a href='verificar_estado.php' class='btn btn-blue'>Verificar Estado</a></p>";
    echo "</div>";
    
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
