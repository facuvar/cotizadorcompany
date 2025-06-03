<?php
/**
 * Debug específico para el cotizador en Railway
 */

echo "<h1>🔍 DEBUG COTIZADOR RAILWAY</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; white-space: pre-wrap; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

echo "<div class='container'>";

// Paso 1: Simular exactamente lo que hace el cotizador
echo "<h2>🔧 Paso 1: Simular carga del cotizador</h2>";

// Cargar configuración igual que el cotizador
$configPath = __DIR__ . '/sistema/config.php';
if (!file_exists($configPath)) {
    echo "<div class='error'>❌ Archivo de configuración no encontrado</div>";
    exit;
}

try {
    require_once $configPath;
    echo "<div class='success'>✅ Configuración cargada</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Error cargando configuración: " . $e->getMessage() . "</div>";
    exit;
}

// Cargar archivos igual que el cotizador
try {
    require_once __DIR__ . '/sistema/includes/db.php';
    require_once __DIR__ . '/sistema/includes/functions.php';
    echo "<div class='success'>✅ Archivos includes cargados</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Error cargando includes: " . $e->getMessage() . "</div>";
    exit;
}

// Paso 2: Conectar igual que el cotizador
echo "<h2>🔌 Paso 2: Conectar a la base de datos</h2>";

$dbConnected = false;
$db = null;
$conn = null;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if ($conn && !$conn->connect_error) {
        $dbConnected = true;
        echo "<div class='success'>✅ Conexión exitosa</div>";
        echo "<div class='info'>Versión del servidor: " . $conn->server_info . "</div>";
        
        // Verificar fuente_datos igual que el cotizador
        $query = "SELECT * FROM fuente_datos ORDER BY fecha_actualizacion DESC LIMIT 1";
        $result = $conn->query($query);
        
        if (!$result || $result->num_rows === 0) {
            $sinDatos = true;
            echo "<div class='warning'>⚠️ Sin datos en fuente_datos - usando valores por defecto</div>";
        } else {
            $sinDatos = false;
            echo "<div class='success'>✅ Datos en fuente_datos encontrados</div>";
        }
    } else {
        echo "<div class='error'>❌ Error de conexión: " . ($conn ? $conn->connect_error : 'Conexión nula') . "</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error conectando: " . $e->getMessage() . "</div>";
    exit;
}

// Paso 3: Obtener plazos igual que el cotizador
echo "<h2>⏰ Paso 3: Obtener plazos de entrega</h2>";

$plazos = [];
$plazoSeleccionado = '160-180 días';

try {
    $query = "SELECT * FROM plazos_entrega ORDER BY orden ASC";
    $plazosResult = $conn->query($query);
    
    if ($plazosResult && $plazosResult->num_rows > 0) {
        echo "<div class='success'>✅ Plazos encontrados: " . $plazosResult->num_rows . "</div>";
        while ($plazo = $plazosResult->fetch_assoc()) {
            $plazos[] = $plazo;
            echo "<div class='info'>• " . $plazo['nombre'] . " (multiplicador: " . $plazo['multiplicador'] . ")</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ No se encontraron plazos</div>";
    }
    
    $plazoSeleccionado = isset($plazos[0]) ? $plazos[0]['nombre'] : '160-180 días';
    echo "<div class='info'>Plazo seleccionado por defecto: " . $plazoSeleccionado . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error obteniendo plazos: " . $e->getMessage() . "</div>";
}

// Paso 4: Obtener categorías EXACTAMENTE igual que el cotizador
echo "<h2>📋 Paso 4: Obtener categorías (EXACTO como cotizador)</h2>";

$categorias = null;

try {
    $query = "SELECT * FROM categorias ORDER BY orden ASC";
    echo "<div class='code'>Consulta SQL: " . $query . "</div>";
    
    $categorias = $conn->query($query);
    
    if ($categorias) {
        echo "<div class='success'>✅ Consulta ejecutada exitosamente</div>";
        echo "<div class='info'>Número de filas: " . $categorias->num_rows . "</div>";
        
        if ($categorias->num_rows > 0) {
            echo "<div class='success'>✅ Categorías encontradas: " . $categorias->num_rows . "</div>";
            
            // Mostrar las categorías
            echo "<table>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Orden</th></tr>";
            
            while ($cat = $categorias->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $cat['id'] . "</td>";
                echo "<td>" . htmlspecialchars($cat['nombre']) . "</td>";
                echo "<td>" . htmlspecialchars($cat['descripcion'] ?? '') . "</td>";
                echo "<td>" . ($cat['orden'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Resetear el puntero para simular lo que hace el cotizador
            $categorias->data_seek(0);
            echo "<div class='info'>🔄 Puntero reseteado a posición 0</div>";
            
        } else {
            echo "<div class='error'>❌ No hay categorías (num_rows = 0)</div>";
        }
    } else {
        echo "<div class='error'>❌ Error en la consulta: " . $conn->error . "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error obteniendo categorías: " . $e->getMessage() . "</div>";
}

// Paso 5: Simular la verificación del cotizador
echo "<h2>🎯 Paso 5: Simular verificación del cotizador</h2>";

echo "<div class='code'>";
echo "Simulando la línea del cotizador:\n";
echo "(\$categorias && \$categorias->num_rows > 0)\n\n";

echo "Valores:\n";
echo "• \$categorias: " . ($categorias ? 'objeto mysqli_result' : 'null') . "\n";
echo "• \$categorias->num_rows: " . ($categorias ? $categorias->num_rows : 'N/A') . "\n";
echo "• Resultado: " . (($categorias && $categorias->num_rows > 0) ? 'TRUE' : 'FALSE') . "\n";
echo "</div>";

if ($categorias && $categorias->num_rows > 0) {
    echo "<div class='success'>✅ La verificación del cotizador debería pasar</div>";
} else {
    echo "<div class='error'>❌ La verificación del cotizador falla</div>";
    
    // Diagnóstico adicional
    echo "<h3>🔍 Diagnóstico adicional</h3>";
    
    // Verificar directamente con COUNT
    $countResult = $conn->query("SELECT COUNT(*) as total FROM categorias");
    if ($countResult) {
        $count = $countResult->fetch_assoc()['total'];
        echo "<div class='info'>COUNT(*) directo: " . $count . " categorías</div>";
    }
    
    // Verificar si hay problema con el ORDER BY
    $simpleResult = $conn->query("SELECT * FROM categorias");
    if ($simpleResult) {
        echo "<div class='info'>SELECT sin ORDER BY: " . $simpleResult->num_rows . " filas</div>";
    }
    
    // Verificar estructura de la tabla
    $structResult = $conn->query("DESCRIBE categorias");
    if ($structResult) {
        echo "<div class='info'>Estructura de la tabla:</div>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($field = $structResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $field['Field'] . "</td>";
            echo "<td>" . $field['Type'] . "</td>";
            echo "<td>" . $field['Null'] . "</td>";
            echo "<td>" . $field['Key'] . "</td>";
            echo "<td>" . ($field['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Paso 6: Enlaces de prueba
echo "<h2>🔗 Enlaces de prueba</h2>";
echo "<div class='info'>";
echo "<a href='sistema/cotizador.php' target='_blank' style='color: blue; text-decoration: underline;'>🚀 Ir al Cotizador</a><br>";
echo "<a href='fix_categorias_railway.php' target='_blank' style='color: blue; text-decoration: underline;'>🔧 Script de Corrección</a><br>";
echo "<a href='railway_debug.php' target='_blank' style='color: blue; text-decoration: underline;'>🔍 Diagnóstico General</a><br>";
echo "</div>";

echo "</div>";
?> 