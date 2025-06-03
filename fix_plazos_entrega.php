<?php
/**
 * Script para corregir la estructura de la tabla plazos_entrega
 * Agregar la columna 'orden' faltante
 */

echo "<h1>🔧 CORREGIR TABLA PLAZOS_ENTREGA</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
</style>";

echo "<div class='container'>";

try {
    // Cargar configuración
    require_once 'sistema/config.php';
    require_once 'sistema/includes/db.php';
    
    echo "<div class='success'>✅ Configuración cargada</div>";
    
    // Obtener conexión
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<div class='success'>✅ Conexión establecida</div>";
    
    // Verificar estructura actual de plazos_entrega
    echo "<h2>📋 Verificar estructura actual</h2>";
    $query = "DESCRIBE plazos_entrega";
    $result = $conn->query($query);
    
    $columnas = [];
    $tieneOrden = false;
    
    echo "<div class='info'><strong>Columnas actuales:</strong><br>";
    while ($row = $result->fetch_assoc()) {
        $columnas[] = $row['Field'];
        echo "• " . $row['Field'] . " (" . $row['Type'] . ")<br>";
        if ($row['Field'] === 'orden') {
            $tieneOrden = true;
        }
    }
    echo "</div>";
    
    if ($tieneOrden) {
        echo "<div class='success'>✅ La columna 'orden' ya existe</div>";
    } else {
        echo "<div class='warning'>⚠️ La columna 'orden' no existe. Agregándola...</div>";
        
        // Agregar la columna orden
        $alterQuery = "ALTER TABLE plazos_entrega ADD COLUMN orden INT DEFAULT 0";
        if ($conn->query($alterQuery)) {
            echo "<div class='success'>✅ Columna 'orden' agregada correctamente</div>";
            
            // Actualizar valores de orden basados en el ID
            $updateQuery = "UPDATE plazos_entrega SET orden = id";
            if ($conn->query($updateQuery)) {
                echo "<div class='success'>✅ Valores de orden actualizados</div>";
            } else {
                echo "<div class='error'>❌ Error actualizando valores de orden: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='error'>❌ Error agregando columna 'orden': " . $conn->error . "</div>";
        }
    }
    
    // Verificar datos en plazos_entrega
    echo "<h2>📊 Verificar datos en plazos_entrega</h2>";
    $query = "SELECT * FROM plazos_entrega ORDER BY id";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo "<div class='success'>✅ Tabla plazos_entrega tiene " . $result->num_rows . " registros</div>";
        echo "<div class='info'><strong>Plazos disponibles:</strong><br>";
        while ($row = $result->fetch_assoc()) {
            $orden = isset($row['orden']) ? $row['orden'] : 'N/A';
            echo "• ID: " . $row['id'] . " - " . $row['nombre'] . " (orden: " . $orden . ")<br>";
        }
        echo "</div>";
    } else {
        echo "<div class='warning'>⚠️ La tabla plazos_entrega está vacía</div>";
        echo "<div class='info'>Insertando plazos por defecto...</div>";
        
        // Insertar plazos por defecto
        $plazosDefecto = [
            ['90 días', 1],
            ['160-180 días', 2],
            ['270 días', 3]
        ];
        
        foreach ($plazosDefecto as $index => $plazo) {
            $insertQuery = "INSERT INTO plazos_entrega (nombre, orden) VALUES (?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param('si', $plazo[0], $plazo[1]);
            
            if ($stmt->execute()) {
                echo "<div class='success'>✅ Plazo '" . $plazo[0] . "' insertado</div>";
            } else {
                echo "<div class='error'>❌ Error insertando plazo '" . $plazo[0] . "': " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
    
    // Verificar otras tablas que podrían necesitar la columna orden
    echo "<h2>🔍 Verificar otras tablas</h2>";
    $tablasConOrden = ['categorias', 'opciones'];
    
    foreach ($tablasConOrden as $tabla) {
        $query = "DESCRIBE " . $tabla;
        $result = $conn->query($query);
        
        $tieneOrden = false;
        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] === 'orden') {
                $tieneOrden = true;
                break;
            }
        }
        
        if ($tieneOrden) {
            echo "<div class='success'>✅ Tabla " . $tabla . " tiene columna 'orden'</div>";
        } else {
            echo "<div class='warning'>⚠️ Tabla " . $tabla . " no tiene columna 'orden'</div>";
            
            // Agregar columna orden si no existe
            $alterQuery = "ALTER TABLE " . $tabla . " ADD COLUMN orden INT DEFAULT 0";
            if ($conn->query($alterQuery)) {
                echo "<div class='success'>✅ Columna 'orden' agregada a " . $tabla . "</div>";
                
                // Actualizar valores de orden basados en el ID
                $updateQuery = "UPDATE " . $tabla . " SET orden = id";
                $conn->query($updateQuery);
            }
        }
    }
    
    echo "<h2>🧪 Probar consultas corregidas</h2>";
    
    // Probar la consulta que estaba fallando
    try {
        $query = "SELECT * FROM plazos_entrega ORDER BY orden ASC";
        $result = $conn->query($query);
        
        if ($result) {
            echo "<div class='success'>✅ Consulta ORDER BY orden funciona correctamente</div>";
            echo "<div class='info'>Registros encontrados: " . $result->num_rows . "</div>";
        } else {
            echo "<div class='error'>❌ La consulta ORDER BY orden sigue fallando: " . $conn->error . "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error en consulta de prueba: " . $e->getMessage() . "</div>";
    }
    
    echo "<h2>🚀 Probar cotizador</h2>";
    echo "<div class='info'>";
    echo "Ahora puedes probar el cotizador:<br>";
    echo "<a href='sistema/cotizador.php' target='_blank' style='color: blue; text-decoration: underline;'>🚀 Abrir Cotizador</a><br>";
    echo "<a href='admin/' target='_blank' style='color: blue; text-decoration: underline;'>👤 Abrir Panel Admin</a><br>";
    echo "<a href='debug_cotizador.php' target='_blank' style='color: blue; text-decoration: underline;'>🔍 Diagnóstico Completo</a><br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div>";
?> 