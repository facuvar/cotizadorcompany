<?php
/**
 * Script de DEBUG para importar datos a Railway
 * Muestra información detallada del archivo SQL
 */

echo "<h1>🔍 DEBUG - Análisis Detallado del Archivo SQL</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; border-radius: 5px; }
    .debug { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto; }
    .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
</style>";

echo "<div class='container'>";

// Credenciales directas de Railway
$host = 'mysql.railway.internal';
$user = 'root';
$pass = 'DEACLLVQgoBvLmRKkFqUazfcOaDVwMKd';
$name = 'railway';
$port = '3306';

// Conectar a Railway
echo "<div class='step'>";
echo "<h2>🔌 Conectando a Railway MySQL</h2>";

try {
    $conn = new mysqli($host, $user, $pass, $name, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    echo "<div class='success'>✅ Conexión exitosa a Railway MySQL</div>";
    echo "<div class='info'>🔢 Versión servidor: " . $conn->server_info . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    exit;
}
echo "</div>";

// Analizar archivo SQL en detalle
echo "<div class='step'>";
echo "<h2>📄 Análisis Detallado del Archivo SQL</h2>";

$sqlFile = 'railway_import.sql';
if (!file_exists($sqlFile)) {
    echo "<div class='error'>❌ No se encontró el archivo $sqlFile</div>";
    exit;
}

$sqlContent = file_get_contents($sqlFile);
$fileSize = filesize($sqlFile);
echo "<div class='success'>✅ Archivo SQL leído: " . number_format($fileSize) . " bytes</div>";

$lines = explode("\n", $sqlContent);
$totalLines = count($lines);
echo "<div class='info'>📄 Total de líneas: " . number_format($totalLines) . "</div>";

// Mostrar las primeras 20 líneas para debug
echo "<div class='info'>🔍 Primeras 20 líneas del archivo:</div>";
echo "<div class='debug'>";
for ($i = 0; $i < min(20, $totalLines); $i++) {
    $lineNum = $i + 1;
    $line = htmlspecialchars($lines[$i]);
    echo "Línea $lineNum: $line\n";
}
echo "</div>";

// Analizar tipos de líneas
$lineTypes = [
    'empty' => 0,
    'comments' => 0,
    'mysql_specific' => 0,
    'set_commands' => 0,
    'create_table' => 0,
    'insert_into' => 0,
    'other_sql' => 0,
    'unknown' => 0
];

$sampleLines = [];

foreach ($lines as $index => $line) {
    $line = trim($line);
    
    if (empty($line)) {
        $lineTypes['empty']++;
    } elseif (strpos($line, '--') === 0 || strpos($line, '#') === 0) {
        $lineTypes['comments']++;
        if (count($sampleLines) < 5) $sampleLines['comments'][] = $line;
    } elseif (strpos($line, '/*!') === 0) {
        $lineTypes['mysql_specific']++;
        if (count($sampleLines) < 5) $sampleLines['mysql_specific'][] = $line;
    } elseif (preg_match('/^SET\s+/i', $line)) {
        $lineTypes['set_commands']++;
        if (count($sampleLines) < 5) $sampleLines['set_commands'][] = $line;
    } elseif (preg_match('/^CREATE\s+TABLE/i', $line)) {
        $lineTypes['create_table']++;
        if (count($sampleLines) < 5) $sampleLines['create_table'][] = $line;
    } elseif (preg_match('/^INSERT\s+INTO/i', $line)) {
        $lineTypes['insert_into']++;
        if (count($sampleLines) < 5) $sampleLines['insert_into'][] = $line;
    } elseif (preg_match('/^(ALTER|UPDATE|DELETE|DROP|LOCK|UNLOCK)/i', $line)) {
        $lineTypes['other_sql']++;
        if (count($sampleLines) < 5) $sampleLines['other_sql'][] = $line;
    } else {
        $lineTypes['unknown']++;
        if (count($sampleLines) < 5) $sampleLines['unknown'][] = $line;
    }
}

echo "<div class='info'>📊 Análisis de tipos de líneas:</div>";
echo "<ul>";
foreach ($lineTypes as $type => $count) {
    echo "<li><strong>$type:</strong> $count líneas</li>";
}
echo "</ul>";

// Mostrar ejemplos de cada tipo
foreach ($sampleLines as $type => $examples) {
    if (!empty($examples)) {
        echo "<div class='info'>📝 Ejemplos de '$type':</div>";
        echo "<div class='debug'>";
        foreach ($examples as $example) {
            echo htmlspecialchars($example) . "\n";
        }
        echo "</div>";
    }
}
echo "</div>";

// Procesamiento más permisivo
echo "<div class='step'>";
echo "<h2>🔧 Procesamiento Permisivo</h2>";

function extractQueriesPermissive($content) {
    $lines = explode("\n", $content);
    $queries = [];
    $currentQuery = '';
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Saltar líneas completamente vacías
        if (empty($line)) {
            continue;
        }
        
        // Saltar comentarios simples
        if (strpos($line, '--') === 0 || strpos($line, '#') === 0) {
            continue;
        }
        
        // Saltar solo algunos comandos específicos problemáticos
        if (preg_match('/^(\/\*!|LOCK TABLES|UNLOCK TABLES)/', $line)) {
            continue;
        }
        
        // Acumular líneas
        $currentQuery .= $line . ' ';
        
        // Si termina con ';', es una consulta completa
        if (substr($line, -1) === ';') {
            $query = trim($currentQuery);
            if (!empty($query) && $query !== ';') {
                $queries[] = rtrim($query, ';');
            }
            $currentQuery = '';
        }
    }
    
    return $queries;
}

$allQueries = extractQueriesPermissive($sqlContent);
echo "<div class='success'>✅ Consultas extraídas (permisivo): " . count($allQueries) . "</div>";

// Mostrar primeras 10 consultas
if (!empty($allQueries)) {
    echo "<div class='info'>📝 Primeras 10 consultas encontradas:</div>";
    echo "<div class='debug'>";
    for ($i = 0; $i < min(10, count($allQueries)); $i++) {
        $queryNum = $i + 1;
        $query = substr($allQueries[$i], 0, 200) . (strlen($allQueries[$i]) > 200 ? '...' : '');
        echo "Query $queryNum: " . htmlspecialchars($query) . "\n\n";
    }
    echo "</div>";
    
    // Clasificar consultas
    $queryTypes = [];
    foreach ($allQueries as $query) {
        if (preg_match('/^(CREATE|INSERT|ALTER|UPDATE|DELETE|DROP|SET|USE)\s+/i', $query, $matches)) {
            $type = strtoupper($matches[1]);
            $queryTypes[$type] = ($queryTypes[$type] ?? 0) + 1;
        } else {
            $queryTypes['OTHER'] = ($queryTypes['OTHER'] ?? 0) + 1;
        }
    }
    
    echo "<div class='info'>📊 Tipos de consulta encontradas:</div>";
    echo "<ul>";
    foreach ($queryTypes as $type => $count) {
        echo "<li><strong>$type:</strong> $count consultas</li>";
    }
    echo "</ul>";
}
echo "</div>";

// Intentar importación con consultas válidas
echo "<div class='step'>";
echo "<h2>📥 Importación de Datos</h2>";

if (empty($allQueries)) {
    echo "<div class='error'>❌ No se encontraron consultas para importar</div>";
} else {
    // Filtrar solo consultas que realmente queremos ejecutar
    $validQueries = [];
    foreach ($allQueries as $query) {
        // Saltar comandos SET que pueden causar problemas
        if (preg_match('/^SET\s+(NAMES|TIME_ZONE|SQL_MODE|FOREIGN_KEY_CHECKS|UNIQUE_CHECKS|AUTOCOMMIT)/i', $query)) {
            continue;
        }
        
        // Saltar USE database
        if (preg_match('/^USE\s+/i', $query)) {
            continue;
        }
        
        // Incluir CREATE, INSERT, ALTER, etc.
        if (preg_match('/^(CREATE|INSERT|ALTER|UPDATE|DELETE|DROP)\s+/i', $query)) {
            $validQueries[] = $query;
        }
    }
    
    echo "<div class='info'>📋 Consultas válidas para ejecutar: " . count($validQueries) . "</div>";
    
    if (!empty($validQueries)) {
        $conn->autocommit(false);
        
        try {
            $successful = 0;
            $errors = 0;
            
            echo "<div class='debug' style='max-height: 400px;'>";
            
            foreach ($validQueries as $index => $query) {
                $shortQuery = substr($query, 0, 100) . (strlen($query) > 100 ? '...' : '');
                echo "Ejecutando " . ($index + 1) . ": " . htmlspecialchars($shortQuery) . "\n";
                
                if ($conn->query($query)) {
                    $successful++;
                    echo "✅ Éxito\n\n";
                } else {
                    $errors++;
                    $error = $conn->error;
                    echo "❌ Error: " . htmlspecialchars($error) . "\n\n";
                    
                    // Si es un error crítico, mostrar query completa
                    if (!strpos($error, 'already exists') && !strpos($error, 'Duplicate entry')) {
                        echo "Query completa: " . htmlspecialchars($query) . "\n\n";
                    }
                }
                
                if ($index % 10 == 0) {
                    ob_flush();
                    flush();
                }
            }
            
            echo "</div>";
            
            $conn->commit();
            
            echo "<div class='success'>✅ Importación completada</div>";
            echo "<div class='info'>📊 Consultas exitosas: $successful de " . count($validQueries) . "</div>";
            if ($errors > 0) {
                echo "<div class='warning'>⚠️ Consultas con errores: $errors</div>";
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            echo "<div class='error'>❌ Error durante importación: " . $e->getMessage() . "</div>";
        }
    }
}
echo "</div>";

// Verificar datos
echo "<div class='step'>";
echo "<h2>🔍 Verificación Final</h2>";

$tables = ['categorias', 'opciones', 'plazos_entrega', 'presupuestos'];

foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as total FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<div class='info'>📋 $table: {$row['total']} registros</div>";
    } else {
        echo "<div class='warning'>⚠️ Tabla $table no existe o error: " . $conn->error . "</div>";
    }
}

echo "<div class='success'>";
echo "<h3>🔗 Enlaces del Sistema:</h3>";
echo "<p><a href='https://cotizadorcompany-production.up.railway.app/' class='btn' target='_blank'>🏠 Página Principal</a></p>";
echo "<p><a href='https://cotizadorcompany-production.up.railway.app/sistema/cotizador.php' class='btn' target='_blank'>💰 Cotizador</a></p>";
echo "<p><a href='https://cotizadorcompany-production.up.railway.app/admin/' class='btn' target='_blank'>🔧 Panel Admin</a></p>";
echo "</div>";
echo "</div>";

echo "</div>"; // container

$conn->close();
?> 