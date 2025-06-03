<?php
/**
 * Script ULTIMATE para importar datos a Railway
 * Versión más permisiva que maneja mejor la clasificación de consultas
 */

echo "<h1>🎯 IMPORTACIÓN ULTIMATE - Railway MySQL</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; border-radius: 5px; }
    .debug { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto; }
    .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
    .progress { background: #e9ecef; border-radius: 5px; height: 20px; margin: 10px 0; }
    .progress-bar { background: #28a745; height: 100%; border-radius: 5px; transition: width 0.3s; }
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
    
    $conn->set_charset("utf8mb4");
    echo "<div class='success'>✅ Conexión exitosa a Railway MySQL</div>";
    echo "<div class='info'>🔢 Versión servidor: " . $conn->server_info . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    exit;
}
echo "</div>";

// Leer y limpiar archivo SQL
echo "<div class='step'>";
echo "<h2>📄 Procesando Archivo SQL</h2>";

$sqlFile = 'railway_import.sql';
if (!file_exists($sqlFile)) {
    echo "<div class='error'>❌ No se encontró el archivo $sqlFile</div>";
    exit;
}

// Leer archivo y remover BOM
$sqlContent = file_get_contents($sqlFile);
$fileSize = filesize($sqlFile);
echo "<div class='info'>📄 Archivo original: " . number_format($fileSize) . " bytes</div>";

// Remover BOM (Byte Order Mark) y caracteres especiales
$sqlContent = preg_replace('/^\xEF\xBB\xBF/', '', $sqlContent); // UTF-8 BOM
$sqlContent = preg_replace('/^[\x00-\x1F\x7F-\xFF]*/', '', $sqlContent); // Caracteres de control
$sqlContent = str_replace(["\r\n", "\r"], "\n", $sqlContent); // Normalizar saltos de línea

echo "<div class='success'>✅ Archivo limpiado de caracteres especiales</div>";

// Procesar líneas de forma más permisiva
$lines = explode("\n", $sqlContent);
$cleanLines = [];

foreach ($lines as $line) {
    $line = trim($line);
    
    // Saltar líneas vacías
    if (empty($line)) continue;
    
    // Saltar comentarios simples
    if (preg_match('/^--/', $line) || preg_match('/^#/', $line)) continue;
    
    // Saltar solo comandos específicos problemáticos
    if (preg_match('/^\/\*!40000/', $line)) continue;
    if (preg_match('/^(LOCK|UNLOCK)\s+TABLES/i', $line)) continue;
    
    $cleanLines[] = $line;
}

echo "<div class='info'>📊 Líneas procesadas: " . count($lines) . " → " . count($cleanLines) . " líneas válidas</div>";

// Reconstruir consultas de forma más inteligente
$queries = [];
$currentQuery = '';
$inMultiLineQuery = false;

foreach ($cleanLines as $line) {
    // Si la línea contiene CREATE, INSERT, ALTER, UPDATE, DELETE, DROP - es inicio de consulta
    if (preg_match('/^(CREATE|INSERT|ALTER|UPDATE|DELETE|DROP)\s+/i', $line)) {
        // Si había una consulta anterior, guardarla
        if (!empty($currentQuery)) {
            $queries[] = trim(rtrim($currentQuery, ';'));
        }
        $currentQuery = $line . ' ';
        $inMultiLineQuery = true;
    } else {
        // Continuar con la consulta actual
        $currentQuery .= $line . ' ';
    }
    
    // Si termina con ';', finalizar consulta
    if (substr(trim($line), -1) === ';') {
        if (!empty($currentQuery)) {
            $queries[] = trim(rtrim($currentQuery, ';'));
        }
        $currentQuery = '';
        $inMultiLineQuery = false;
    }
}

// Agregar última consulta si existe
if (!empty($currentQuery)) {
    $queries[] = trim(rtrim($currentQuery, ';'));
}

echo "<div class='success'>✅ Consultas extraídas: " . count($queries) . "</div>";

// Mostrar ejemplos de consultas encontradas
echo "<div class='info'>📝 Primeras 5 consultas encontradas:</div>";
echo "<div class='debug'>";
for ($i = 0; $i < min(5, count($queries)); $i++) {
    $query = substr($queries[$i], 0, 150) . (strlen($queries[$i]) > 150 ? '...' : '');
    echo "Query " . ($i + 1) . ": " . htmlspecialchars($query) . "\n\n";
}
echo "</div>";
echo "</div>";

// Filtrar y ejecutar consultas
echo "<div class='step'>";
echo "<h2>📥 Importando Datos</h2>";

if (empty($queries)) {
    echo "<div class='error'>❌ No se encontraron consultas para importar</div>";
} else {
    // Clasificar consultas de forma más permisiva
    $createQueries = [];
    $insertQueries = [];
    $otherQueries = [];
    
    foreach ($queries as $query) {
        if (preg_match('/^CREATE\s+TABLE/i', $query)) {
            $createQueries[] = $query;
        } elseif (preg_match('/^INSERT\s+INTO/i', $query)) {
            $insertQueries[] = $query;
        } elseif (preg_match('/^(ALTER|UPDATE|DELETE|DROP)\s+/i', $query)) {
            $otherQueries[] = $query;
        }
    }
    
    // Todas las consultas válidas (más permisivo)
    $validQueries = array_merge($createQueries, $insertQueries, $otherQueries);
    
    echo "<div class='info'>📊 Total consultas: " . count($queries) . "</div>";
    echo "<div class='info'>🏗️ CREATE TABLE: " . count($createQueries) . "</div>";
    echo "<div class='info'>📝 INSERT INTO: " . count($insertQueries) . "</div>";
    echo "<div class='info'>🔧 Otras consultas: " . count($otherQueries) . "</div>";
    echo "<div class='info'>✅ Consultas a ejecutar: " . count($validQueries) . "</div>";
    
    if (!empty($validQueries)) {
        $conn->autocommit(false);
        
        try {
            $successful = 0;
            $errors = 0;
            $totalQueries = count($validQueries);
            
            echo "<div class='progress'><div class='progress-bar' id='progressBar' style='width: 0%'></div></div>";
            echo "<div id='progressText'>Iniciando importación...</div>";
            echo "<div class='debug' id='logOutput' style='max-height: 400px;'>";
            
            foreach ($validQueries as $index => $query) {
                $progress = round(($index + 1) / $totalQueries * 100);
                
                // Mostrar progreso
                echo "<script>
                    document.getElementById('progressBar').style.width = '{$progress}%';
                    document.getElementById('progressText').innerHTML = 'Procesando: {$progress}% (" . ($index + 1) . "/{$totalQueries})';
                </script>";
                
                $shortQuery = substr($query, 0, 100) . (strlen($query) > 100 ? '...' : '');
                echo "Ejecutando " . ($index + 1) . "/$totalQueries: " . htmlspecialchars($shortQuery) . "\n";
                
                if ($conn->query($query)) {
                    $successful++;
                    echo "✅ Éxito\n\n";
                } else {
                    $errors++;
                    $error = $conn->error;
                    echo "❌ Error: " . htmlspecialchars($error) . "\n";
                    
                    // Solo mostrar query completa para errores críticos
                    if (!strpos($error, 'already exists') && !strpos($error, 'Duplicate entry')) {
                        echo "Query completa: " . htmlspecialchars(substr($query, 0, 300)) . "...\n";
                    }
                    echo "\n";
                }
                
                // Flush output cada 3 consultas
                if ($index % 3 == 0) {
                    ob_flush();
                    flush();
                }
            }
            
            echo "</div>";
            
            $conn->commit();
            
            echo "<div class='success'>✅ Importación completada</div>";
            echo "<div class='info'>📊 Consultas exitosas: $successful de $totalQueries</div>";
            if ($errors > 0) {
                echo "<div class='warning'>⚠️ Consultas con errores: $errors</div>";
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            echo "<div class='error'>❌ Error durante importación: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='error'>❌ No se encontraron consultas válidas para ejecutar</div>";
    }
}
echo "</div>";

// Verificar datos finales
echo "<div class='step'>";
echo "<h2>🔍 Verificación Final</h2>";

// Primero verificar qué tablas existen
$result = $conn->query("SHOW TABLES");
$existingTables = [];
if ($result) {
    while ($row = $result->fetch_array()) {
        $existingTables[] = $row[0];
    }
}

echo "<div class='info'>📋 Tablas existentes: " . implode(', ', $existingTables) . "</div>";

$tables = ['categorias', 'opciones', 'plazos_entrega', 'presupuestos', 'opcion_precios', 'xls_productos', 'xls_opciones', 'xls_precios'];

$totalRecords = 0;
foreach ($tables as $table) {
    if (in_array($table, $existingTables)) {
        $result = $conn->query("SELECT COUNT(*) as total FROM $table");
        if ($result) {
            $row = $result->fetch_assoc();
            $count = $row['total'];
            $totalRecords += $count;
            
            if ($count > 0) {
                echo "<div class='success'>✅ $table: $count registros</div>";
            } else {
                echo "<div class='warning'>⚠️ $table: 0 registros</div>";
            }
        } else {
            echo "<div class='error'>❌ Error consultando $table: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='error'>❌ Tabla $table no existe</div>";
    }
}

echo "<div class='info'><strong>📊 Total de registros importados: $totalRecords</strong></div>";

if ($totalRecords > 0) {
    echo "<div class='success'>";
    echo "<h3>🎉 ¡IMPORTACIÓN EXITOSA!</h3>";
    echo "<p>Los datos se han importado correctamente a Railway.</p>";
    echo "<h3>🔗 Enlaces del Sistema:</h3>";
    echo "<p><a href='https://cotizadorcompany-production.up.railway.app/' class='btn' target='_blank'>🏠 Página Principal</a></p>";
    echo "<p><a href='https://cotizadorcompany-production.up.railway.app/sistema/cotizador.php' class='btn' target='_blank'>💰 Cotizador</a></p>";
    echo "<p><a href='https://cotizadorcompany-production.up.railway.app/admin/' class='btn' target='_blank'>🔧 Panel Admin</a></p>";
    echo "<p><strong>Credenciales Admin:</strong> usuario: <code>admin</code>, contraseña: <code>admin123</code></p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>❌ Importación Incompleta</h3>";
    echo "<p>No se importaron datos. El archivo SQL puede tener un formato incompatible.</p>";
    echo "</div>";
}

echo "</div>";

echo "</div>"; // container

$conn->close();
?> 