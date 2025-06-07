<?php
/**
 * 🔄 COMPARADOR DE ESTRUCTURAS LOCAL vs RAILWAY
 * 
 * Compara automáticamente las estructuras de base de datos
 * y genera un reporte de diferencias específicas
 */

// Configuración LOCAL
$localConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'company_presupuestos',
    'port' => 3306
];

// Configuración RAILWAY (simulada - necesitarás las credenciales reales)
$railwayConfig = [
    'host' => 'autorack.proxy.rlwy.net', // Reemplazar con el host real
    'user' => 'root',
    'pass' => 'LjEWJGgCJHdBcgfGAhGjfEBEhfJjGGjA', // Reemplazar con la contraseña real
    'name' => 'railway',
    'port' => 47470 // Reemplazar con el puerto real
];

function connectToDatabase($config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 10
        ];
        
        return new PDO($dsn, $config['user'], $config['pass'], $options);
        
    } catch (PDOException $e) {
        return ['error' => $e->getMessage()];
    }
}

function getDatabaseInfo($pdo, $environment) {
    // Si $pdo es un array, significa que hay un error
    if (is_array($pdo) && isset($pdo['error'])) {
        return ['error' => $pdo['error'], 'environment' => $environment];
    }
    
    // Si no es un objeto PDO válido, error
    if (!($pdo instanceof PDO)) {
        return ['error' => 'Conexión PDO inválida', 'environment' => $environment];
    }
    
    try {
        // Obtener todas las tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $info = [
            'environment' => $environment,
            'tables' => $tables,
            'table_details' => []
        ];
        
        // Para cada tabla, obtener estructura y conteo
        foreach ($tables as $table) {
            try {
                // Estructura
                $stmt = $pdo->query("DESCRIBE `$table`");
                $structure = $stmt->fetchAll();
                
                // Conteo
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
                $count = $stmt->fetch()['count'];
                
                // Datos de muestra (primeros 3 registros)
                $stmt = $pdo->query("SELECT * FROM `$table` LIMIT 3");
                $sample = $stmt->fetchAll();
                
                $info['table_details'][$table] = [
                    'structure' => $structure,
                    'count' => $count,
                    'sample' => $sample
                ];
                
            } catch (Exception $e) {
                $info['table_details'][$table] = ['error' => $e->getMessage()];
            }
        }
        
        return $info;
        
    } catch (Exception $e) {
        return ['error' => $e->getMessage(), 'environment' => $environment];
    }
}

function compareStructures($localInfo, $railwayInfo) {
    $comparison = [
        'tables_only_local' => [],
        'tables_only_railway' => [],
        'tables_both' => [],
        'structure_differences' => [],
        'data_differences' => []
    ];
    
    if (isset($localInfo['error']) || isset($railwayInfo['error'])) {
        $comparison['error'] = 'No se pudo conectar a una o ambas bases de datos';
        if (isset($localInfo['error'])) {
            $comparison['local_error'] = $localInfo['error'];
        }
        if (isset($railwayInfo['error'])) {
            $comparison['railway_error'] = $railwayInfo['error'];
        }
        return $comparison;
    }
    
    $localTables = $localInfo['tables'];
    $railwayTables = $railwayInfo['tables'];
    
    // Tablas solo en local
    $comparison['tables_only_local'] = array_diff($localTables, $railwayTables);
    
    // Tablas solo en Railway
    $comparison['tables_only_railway'] = array_diff($railwayTables, $localTables);
    
    // Tablas en ambos
    $comparison['tables_both'] = array_intersect($localTables, $railwayTables);
    
    // Comparar estructuras de tablas comunes
    foreach ($comparison['tables_both'] as $table) {
        $localStructure = $localInfo['table_details'][$table]['structure'] ?? [];
        $railwayStructure = $railwayInfo['table_details'][$table]['structure'] ?? [];
        
        $localCount = $localInfo['table_details'][$table]['count'] ?? 0;
        $railwayCount = $railwayInfo['table_details'][$table]['count'] ?? 0;
        
        // Comparar campos
        $localFields = array_column($localStructure, 'Field');
        $railwayFields = array_column($railwayStructure, 'Field');
        
        $fieldsOnlyLocal = array_diff($localFields, $railwayFields);
        $fieldsOnlyRailway = array_diff($railwayFields, $localFields);
        
        if (!empty($fieldsOnlyLocal) || !empty($fieldsOnlyRailway)) {
            $comparison['structure_differences'][$table] = [
                'fields_only_local' => $fieldsOnlyLocal,
                'fields_only_railway' => $fieldsOnlyRailway
            ];
        }
        
        // Comparar conteos
        if ($localCount != $railwayCount) {
            $comparison['data_differences'][$table] = [
                'local_count' => $localCount,
                'railway_count' => $railwayCount,
                'difference' => $localCount - $railwayCount
            ];
        }
    }
    
    return $comparison;
}

// Ejecutar comparación
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>🔄 Comparación Local vs Railway</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .section { margin: 30px 0; padding: 20px; border-radius: 8px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .card { background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔄 Comparación de Estructuras</h1>
            <p>Local vs Railway - Análisis Detallado</p>
        </div>";

echo "<div class='section info'>
        <h3>🔄 Iniciando comparación...</h3>
        <p>Conectando a ambas bases de datos...</p>
      </div>";

// Conectar a ambas bases de datos
echo "<div class='section'>
        <h3>📡 Conexiones</h3>";

$localPdo = connectToDatabase($localConfig);
if (is_array($localPdo) && isset($localPdo['error'])) {
    echo "<p class='status-error'>❌ Local: " . $localPdo['error'] . "</p>";
} else {
    echo "<p class='status-ok'>✅ Local: Conectado exitosamente</p>";
}

$railwayPdo = connectToDatabase($railwayConfig);
if (is_array($railwayPdo) && isset($railwayPdo['error'])) {
    echo "<p class='status-error'>❌ Railway: " . $railwayPdo['error'] . "</p>";
    echo "<p><strong>Nota:</strong> Para conectar a Railway, necesitas las credenciales correctas. Puedes obtenerlas desde:</p>";
    echo "<ul>
            <li>Panel de Railway > Variables de entorno</li>
            <li>O desde el archivo de diagnóstico en Railway</li>
          </ul>";
} else {
    echo "<p class='status-ok'>✅ Railway: Conectado exitosamente</p>";
}

echo "</div>";

// Obtener información de ambas bases de datos
echo "<div class='section'>
        <h3>📊 Obteniendo información...</h3>";

$localInfo = getDatabaseInfo($localPdo, 'local');
$railwayInfo = getDatabaseInfo($railwayPdo, 'railway');

if (isset($localInfo['error'])) {
    echo "<p class='status-error'>❌ Error obteniendo info local: " . $localInfo['error'] . "</p>";
} else {
    echo "<p class='status-ok'>✅ Info local obtenida: " . count($localInfo['tables']) . " tablas</p>";
}

if (isset($railwayInfo['error'])) {
    echo "<p class='status-error'>❌ Error obteniendo info Railway: " . $railwayInfo['error'] . "</p>";
} else {
    echo "<p class='status-ok'>✅ Info Railway obtenida: " . count($railwayInfo['tables']) . " tablas</p>";
}

echo "</div>";

// Realizar comparación
$comparison = compareStructures($localInfo, $railwayInfo);

if (isset($comparison['error'])) {
    echo "<div class='section error'>
            <h3>❌ Error en la Comparación</h3>
            <p>" . $comparison['error'] . "</p>";
    
    if (isset($comparison['local_error'])) {
        echo "<p><strong>Error Local:</strong> " . $comparison['local_error'] . "</p>";
    }
    
    if (isset($comparison['railway_error'])) {
        echo "<p><strong>Error Railway:</strong> " . $comparison['railway_error'] . "</p>";
    }
    
    echo "<h4>🛠️ Soluciones Alternativas:</h4>
          <ol>
            <li><strong>Usar el diagnóstico individual:</strong> 
                <ul>
                    <li><a href='diagnostico_railway.php' class='btn'>🏠 Diagnóstico Local</a></li>
                    <li><a href='https://cotizadorcompany-production.up.railway.app/diagnostico_railway.php' class='btn' target='_blank'>🚂 Diagnóstico Railway</a></li>
                </ul>
            </li>
            <li><strong>Exportar estructura local:</strong> Usa phpMyAdmin para exportar solo la estructura</li>
            <li><strong>Verificar credenciales Railway:</strong> Revisa las variables de entorno en Railway</li>
          </ol>
          </div>";
} else {
    // Mostrar resultados de la comparación
    echo "<div class='section success'>
            <h3>✅ Comparación Completada</h3>
            <div class='grid'>
                <div class='card'>
                    <h4>🏠 Local</h4>
                    <p><strong>Tablas:</strong> " . count($localInfo['tables']) . "</p>
                </div>
                <div class='card'>
                    <h4>🚂 Railway</h4>
                    <p><strong>Tablas:</strong> " . count($railwayInfo['tables']) . "</p>
                </div>
            </div>
          </div>";
    
    // Tablas solo en local
    if (!empty($comparison['tables_only_local'])) {
        echo "<div class='section warning'>
                <h3>⚠️ Tablas Solo en Local</h3>
                <p>Estas tablas existen en local pero NO en Railway:</p>
                <ul>";
        foreach ($comparison['tables_only_local'] as $table) {
            $count = $localInfo['table_details'][$table]['count'] ?? 0;
            echo "<li><strong>$table</strong> ($count registros)</li>";
        }
        echo "</ul>
              <p><strong>Acción recomendada:</strong> Exportar estas tablas y subirlas a Railway</p>
              </div>";
    }
    
    // Tablas solo en Railway
    if (!empty($comparison['tables_only_railway'])) {
        echo "<div class='section info'>
                <h3>ℹ️ Tablas Solo en Railway</h3>
                <p>Estas tablas existen en Railway pero NO en local:</p>
                <ul>";
        foreach ($comparison['tables_only_railway'] as $table) {
            $count = $railwayInfo['table_details'][$table]['count'] ?? 0;
            echo "<li><strong>$table</strong> ($count registros)</li>";
        }
        echo "</ul>
              </div>";
    }
    
    // Diferencias de estructura
    if (!empty($comparison['structure_differences'])) {
        echo "<div class='section error'>
                <h3>❌ Diferencias de Estructura</h3>
                <p>Estas tablas tienen campos diferentes:</p>";
        
        foreach ($comparison['structure_differences'] as $table => $diff) {
            echo "<h4>Tabla: $table</h4>";
            
            if (!empty($diff['fields_only_local'])) {
                echo "<p><strong>Campos solo en Local:</strong> " . implode(', ', $diff['fields_only_local']) . "</p>";
            }
            
            if (!empty($diff['fields_only_railway'])) {
                echo "<p><strong>Campos solo en Railway:</strong> " . implode(', ', $diff['fields_only_railway']) . "</p>";
            }
        }
        echo "</div>";
    }
    
    // Diferencias de datos
    if (!empty($comparison['data_differences'])) {
        echo "<div class='section warning'>
                <h3>⚠️ Diferencias de Datos</h3>
                <table>
                    <tr><th>Tabla</th><th>Local</th><th>Railway</th><th>Diferencia</th></tr>";
        
        foreach ($comparison['data_differences'] as $table => $diff) {
            $status = $diff['difference'] > 0 ? 'Local tiene más' : 'Railway tiene más';
            echo "<tr>
                    <td><strong>$table</strong></td>
                    <td>" . number_format($diff['local_count']) . "</td>
                    <td>" . number_format($diff['railway_count']) . "</td>
                    <td>" . abs($diff['difference']) . " ($status)</td>
                  </tr>";
        }
        echo "</table>
              </div>";
    }
    
    // Tablas críticas para el cotizador
    echo "<div class='section info'>
            <h3>🎯 Análisis del Cotizador</h3>
            <p>Estado de las tablas críticas para el funcionamiento del cotizador:</p>
            <table>
                <tr><th>Tabla</th><th>Local</th><th>Railway</th><th>Estado</th></tr>";
    
    $criticalTables = ['categorias', 'opciones', 'plazos_entrega', 'opcion_precios'];
    foreach ($criticalTables as $table) {
        $localExists = in_array($table, $localInfo['tables']);
        $railwayExists = in_array($table, $railwayInfo['tables']);
        
        $localCount = $localExists ? ($localInfo['table_details'][$table]['count'] ?? 0) : 0;
        $railwayCount = $railwayExists ? ($railwayInfo['table_details'][$table]['count'] ?? 0) : 0;
        
        $status = '';
        if (!$localExists && !$railwayExists) {
            $status = '<span class="status-error">❌ No existe en ninguno</span>';
        } elseif (!$railwayExists) {
            $status = '<span class="status-error">❌ Falta en Railway</span>';
        } elseif ($railwayCount == 0) {
            $status = '<span class="status-warning">⚠️ Vacía en Railway</span>';
        } elseif ($localCount != $railwayCount) {
            $status = '<span class="status-warning">⚠️ Datos diferentes</span>';
        } else {
            $status = '<span class="status-ok">✅ OK</span>';
        }
        
        echo "<tr>
                <td><strong>$table</strong></td>
                <td>" . ($localExists ? number_format($localCount) : 'No existe') . "</td>
                <td>" . ($railwayExists ? number_format($railwayCount) : 'No existe') . "</td>
                <td>$status</td>
              </tr>";
    }
    echo "</table>
          </div>";
}

echo "<div class='section'>
        <h3>🔗 Enlaces Útiles</h3>
        <a href='diagnostico_railway.php' class='btn'>🏠 Diagnóstico Local</a>
        <a href='https://cotizadorcompany-production.up.railway.app/diagnostico_railway.php' class='btn' target='_blank'>🚂 Diagnóstico Railway</a>
        <a href='upload_database_completa_standalone.php' class='btn'>📤 Upload Database</a>
        <a href='cotizador.php' class='btn'>💼 Cotizador</a>
      </div>";

echo "</div>
</body>
</html>";
?> 