<?php
/**
 * Script para corregir la tabla plazos_entrega en Railway
 * Agrega la columna 'orden' faltante que causa el error en el cotizador
 */

echo "<h1>🔧 CORRECCIÓN TABLA PLAZOS_ENTREGA</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; white-space: pre-wrap; }
    .btn { display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 5px; border: none; cursor: pointer; }
    .btn:hover { background: #45a049; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

echo "<div class='container'>";

// Cargar configuración
echo "<h2>📋 Paso 1: Cargar configuración</h2>";

try {
    require_once 'sistema/config.php';
    echo "<div class='success'>✅ Configuración cargada correctamente</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Error cargando configuración: " . $e->getMessage() . "</div>";
    exit;
}

// Conectar a la base de datos
echo "<h2>🔌 Paso 2: Conectar a la base de datos</h2>";

try {
    require_once 'sistema/includes/db.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if ($conn && !$conn->connect_error) {
        echo "<div class='success'>✅ Conexión exitosa a la base de datos</div>";
        echo "<div class='info'>Versión del servidor: " . $conn->server_info . "</div>";
    } else {
        echo "<div class='error'>❌ Error de conexión: " . ($conn ? $conn->connect_error : 'Conexión nula') . "</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error conectando: " . $e->getMessage() . "</div>";
    exit;
}

// Verificar estructura actual
echo "<h2>🔍 Paso 3: Verificar estructura actual</h2>";

$structResult = $conn->query("DESCRIBE plazos_entrega");
if ($structResult) {
    echo "<div class='info'>Estructura actual de plazos_entrega:</div>";
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $hasOrden = false;
    while ($field = $structResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $field['Field'] . "</td>";
        echo "<td>" . $field['Type'] . "</td>";
        echo "<td>" . $field['Null'] . "</td>";
        echo "<td>" . $field['Key'] . "</td>";
        echo "<td>" . ($field['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
        
        if ($field['Field'] === 'orden') {
            $hasOrden = true;
        }
    }
    echo "</table>";
    
    if ($hasOrden) {
        echo "<div class='success'>✅ La columna 'orden' ya existe</div>";
    } else {
        echo "<div class='warning'>⚠️ La columna 'orden' NO existe - esto causa el error</div>";
    }
} else {
    echo "<div class='error'>❌ Error verificando estructura: " . $conn->error . "</div>";
    exit;
}

// Mostrar datos actuales
echo "<h2>📊 Paso 4: Datos actuales</h2>";

$result = $conn->query("SELECT * FROM plazos_entrega");
if ($result && $result->num_rows > 0) {
    echo "<div class='success'>✅ Plazos encontrados: " . $result->num_rows . "</div>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Multiplicador</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
        echo "<td>" . ($row['multiplicador'] ?? '1.00') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='warning'>⚠️ No hay plazos en la tabla</div>";
}

// Botón para corregir
if (!$hasOrden) {
    echo "<h2>🔧 Paso 5: Corregir estructura</h2>";
    echo "<div class='warning'>Es necesario agregar la columna 'orden' para que el cotizador funcione correctamente.</div>";
    echo "<form method='post' style='margin: 20px 0;'>";
    echo "<button type='submit' name='agregar_orden' class='btn'>🔧 Agregar Columna 'orden'</button>";
    echo "</form>";
}

// Procesar corrección
if (isset($_POST['agregar_orden'])) {
    echo "<h2>🔧 Ejecutando corrección</h2>";
    
    try {
        // Agregar columna orden
        $alterQuery = "ALTER TABLE plazos_entrega ADD COLUMN orden INT DEFAULT 0";
        echo "<div class='code'>SQL: " . $alterQuery . "</div>";
        
        if ($conn->query($alterQuery)) {
            echo "<div class='success'>✅ Columna 'orden' agregada exitosamente</div>";
            
            // Actualizar valores de orden basados en el ID
            $updateQueries = [
                "UPDATE plazos_entrega SET orden = 1 WHERE nombre LIKE '%90%'",
                "UPDATE plazos_entrega SET orden = 2 WHERE nombre LIKE '%160%' OR nombre LIKE '%180%'",
                "UPDATE plazos_entrega SET orden = 3 WHERE nombre LIKE '%270%'"
            ];
            
            foreach ($updateQueries as $updateQuery) {
                echo "<div class='code'>SQL: " . $updateQuery . "</div>";
                if ($conn->query($updateQuery)) {
                    echo "<div class='success'>✅ Orden actualizado</div>";
                } else {
                    echo "<div class='error'>❌ Error actualizando orden: " . $conn->error . "</div>";
                }
            }
            
            // Mostrar resultado final
            echo "<h3>📊 Resultado final</h3>";
            $finalResult = $conn->query("SELECT * FROM plazos_entrega ORDER BY orden ASC");
            if ($finalResult && $finalResult->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Multiplicador</th><th>Orden</th></tr>";
                
                while ($row = $finalResult->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                    echo "<td>" . ($row['multiplicador'] ?? '1.00') . "</td>";
                    echo "<td>" . $row['orden'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
            echo "<div class='success'>🎉 ¡Corrección completada! El cotizador debería funcionar ahora.</div>";
            echo "<div class='info'>🔄 Recargando página para verificar...</div>";
            echo "<script>setTimeout(function(){ window.location.reload(); }, 3000);</script>";
            
        } else {
            echo "<div class='error'>❌ Error agregando columna: " . $conn->error . "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error en la corrección: " . $e->getMessage() . "</div>";
    }
}

// Enlaces de prueba
echo "<h2>🔗 Enlaces de prueba</h2>";
echo "<div class='info'>";
echo "<a href='sistema/cotizador.php' target='_blank' style='color: blue; text-decoration: underline;'>🚀 Probar Cotizador</a><br>";
echo "<a href='debug_cotizador_railway.php' target='_blank' style='color: blue; text-decoration: underline;'>🔍 Debug Cotizador</a><br>";
echo "<a href='railway_debug.php' target='_blank' style='color: blue; text-decoration: underline;'>📊 Diagnóstico General</a><br>";
echo "</div>";

echo "</div>";
?> 