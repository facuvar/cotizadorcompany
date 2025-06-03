<?php
/**
 * Script para actualizar los valores de orden en plazos_entrega
 * La columna existe pero los valores están incorrectos
 */

echo "<h1>🔧 ACTUALIZAR ORDEN EN PLAZOS_ENTREGA</h1>";
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

// Mostrar datos actuales
echo "<h2>📊 Paso 3: Datos actuales</h2>";

$result = $conn->query("SELECT * FROM plazos_entrega ORDER BY id ASC");
if ($result && $result->num_rows > 0) {
    echo "<div class='success'>✅ Plazos encontrados: " . $result->num_rows . "</div>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Multiplicador</th><th>Orden Actual</th></tr>";
    
    $needsUpdate = false;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
        echo "<td>" . ($row['multiplicador'] ?? '1.00') . "</td>";
        echo "<td>" . ($row['orden'] ?? 'NULL') . "</td>";
        echo "</tr>";
        
        // Verificar si necesita actualización
        if (empty($row['orden']) || $row['orden'] == 0) {
            $needsUpdate = true;
        }
    }
    echo "</table>";
    
    if ($needsUpdate) {
        echo "<div class='warning'>⚠️ Los valores de orden necesitan ser actualizados</div>";
    } else {
        echo "<div class='success'>✅ Los valores de orden están correctos</div>";
    }
} else {
    echo "<div class='warning'>⚠️ No hay plazos en la tabla</div>";
}

// Botón para actualizar
echo "<h2>🔧 Paso 4: Actualizar valores de orden</h2>";
echo "<div class='info'>Se asignarán los siguientes valores de orden:</div>";
echo "<ul>";
echo "<li><strong>90 días</strong> → orden = 1 (más rápido, más caro)</li>";
echo "<li><strong>160-180 días</strong> → orden = 2 (estándar)</li>";
echo "<li><strong>270 días</strong> → orden = 3 (más lento, más barato)</li>";
echo "</ul>";

echo "<form method='post' style='margin: 20px 0;'>";
echo "<button type='submit' name='actualizar_orden' class='btn'>🔧 Actualizar Valores de Orden</button>";
echo "</form>";

// Procesar actualización
if (isset($_POST['actualizar_orden'])) {
    echo "<h2>🔧 Ejecutando actualización</h2>";
    
    try {
        // Actualizar valores de orden basados en el nombre
        $updateQueries = [
            [
                'sql' => "UPDATE plazos_entrega SET orden = 1 WHERE nombre LIKE '%90%'",
                'desc' => 'Plazos de 90 días → orden = 1'
            ],
            [
                'sql' => "UPDATE plazos_entrega SET orden = 2 WHERE nombre LIKE '%160%' OR nombre LIKE '%180%'",
                'desc' => 'Plazos de 160-180 días → orden = 2'
            ],
            [
                'sql' => "UPDATE plazos_entrega SET orden = 3 WHERE nombre LIKE '%270%'",
                'desc' => 'Plazos de 270 días → orden = 3'
            ]
        ];
        
        $totalUpdated = 0;
        foreach ($updateQueries as $query) {
            echo "<div class='code'>SQL: " . $query['sql'] . "</div>";
            if ($conn->query($query['sql'])) {
                $affected = $conn->affected_rows;
                echo "<div class='success'>✅ " . $query['desc'] . " - " . $affected . " filas actualizadas</div>";
                $totalUpdated += $affected;
            } else {
                echo "<div class='error'>❌ Error: " . $conn->error . "</div>";
            }
        }
        
        if ($totalUpdated > 0) {
            echo "<div class='success'>🎉 Total de filas actualizadas: " . $totalUpdated . "</div>";
            
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
                    echo "<td><strong>" . $row['orden'] . "</strong></td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
            echo "<div class='success'>🎉 ¡Actualización completada! El cotizador debería funcionar ahora.</div>";
            echo "<div class='info'>🔄 Probando consulta ORDER BY orden ASC...</div>";
            
            // Probar la consulta que usa el cotizador
            $testResult = $conn->query("SELECT * FROM plazos_entrega ORDER BY orden ASC");
            if ($testResult) {
                echo "<div class='success'>✅ Consulta ORDER BY orden ASC funciona correctamente</div>";
            } else {
                echo "<div class='error'>❌ Error en consulta ORDER BY: " . $conn->error . "</div>";
            }
            
        } else {
            echo "<div class='warning'>⚠️ No se actualizaron filas. Verificar nombres de plazos.</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error en la actualización: " . $e->getMessage() . "</div>";
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