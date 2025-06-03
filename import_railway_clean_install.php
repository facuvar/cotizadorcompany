<?php
/**
 * Script DEFINITIVO para INSTALACIÓN LIMPIA en Railway
 * Elimina tablas existentes y hace importación completa desde cero
 */

echo "<h1>🧹 INSTALACIÓN LIMPIA - Railway</h1>";
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

// Limpiar base de datos existente
echo "<div class='step'>";
echo "<h2>🧹 Limpiando Base de Datos Existente</h2>";

// Deshabilitar verificaciones de foreign key para poder eliminar tablas
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Obtener todas las tablas existentes
$result = $conn->query("SHOW TABLES");
$existingTables = [];
if ($result) {
    while ($row = $result->fetch_array()) {
        $existingTables[] = $row[0];
    }
}

echo "<div class='info'>📋 Tablas existentes encontradas: " . count($existingTables) . "</div>";

if (!empty($existingTables)) {
    echo "<div class='debug'>";
    foreach ($existingTables as $table) {
        echo "🗑️ Eliminando tabla: $table\n";
        if ($conn->query("DROP TABLE IF EXISTS `$table`")) {
            echo "✅ Eliminada: $table\n";
        } else {
            echo "❌ Error eliminando $table: " . $conn->error . "\n";
        }
    }
    echo "</div>";
    echo "<div class='success'>✅ Base de datos limpiada</div>";
} else {
    echo "<div class='info'>ℹ️ No hay tablas existentes para eliminar</div>";
}

// Rehabilitar verificaciones de foreign key
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "</div>";

// Leer y convertir archivo SQL
echo "<div class='step'>";
echo "<h2>📄 Convirtiendo Archivo SQL de UTF-16 a UTF-8</h2>";

$sqlFile = 'railway_import.sql';
if (!file_exists($sqlFile)) {
    echo "<div class='error'>❌ No se encontró el archivo $sqlFile</div>";
    exit;
}

$sqlContent = file_get_contents($sqlFile);
$fileSize = filesize($sqlFile);
echo "<div class='info'>📄 Archivo original: " . number_format($fileSize) . " bytes</div>";

// Detectar y convertir de UTF-16 LE a UTF-8
$firstBytes = substr($sqlContent, 0, 2);
if ($firstBytes === "\xFF\xFE") {
    echo "<div class='info'>🔍 Detectado: UTF-16 LE con BOM</div>";
    
    // Remover BOM y convertir a UTF-8
    $sqlContent = substr($sqlContent, 2); // Remover BOM
    $sqlContent = mb_convert_encoding($sqlContent, 'UTF-8', 'UTF-16LE');
    
    echo "<div class='success'>✅ Convertido a UTF-8</div>";
    echo "<div class='info'>📄 Tamaño después de conversión: " . number_format(strlen($sqlContent)) . " bytes</div>";
} else {
    echo "<div class='warning'>⚠️ No se detectó UTF-16 LE, procesando como UTF-8</div>";
}

// Normalizar saltos de línea
$sqlContent = str_replace(["\r\n", "\r"], "\n", $sqlContent);
echo "</div>";

// Procesar consultas SQL
echo "<div class='step'>";
echo "<h2>🔍 Extrayendo y Ordenando Consultas SQL</h2>";

// Dividir por punto y coma y filtrar
$statements = explode(';', $sqlContent);
$createQueries = [];
$insertQueries = [];

foreach ($statements as $statement) {
    $statement = trim($statement);
    
    // Saltar líneas vacías
    if (empty($statement)) continue;
    
    // Saltar comentarios
    if (strpos($statement, '--') === 0) continue;
    
    // Saltar comandos MySQL específicos problemáticos
    if (preg_match('/^\/\*!40000/', $statement)) continue;
    if (preg_match('/^(LOCK|UNLOCK)\s+TABLES/i', $statement)) continue;
    if (preg_match('/^SET\s+(@|NAMES|TIME_ZONE|SQL_MODE|FOREIGN_KEY_CHECKS|UNIQUE_CHECKS|AUTOCOMMIT)/i', $statement)) continue;
    
    // Clasificar consultas
    if (preg_match('/^CREATE\s+TABLE/i', $statement)) {
        $createQueries[] = $statement;
    } elseif (preg_match('/^INSERT\s+INTO/i', $statement)) {
        $insertQueries[] = $statement;
    }
}

echo "<div class='info'>🏗️ CREATE TABLE encontradas: " . count($createQueries) . "</div>";
echo "<div class='info'>📝 INSERT INTO encontradas: " . count($insertQueries) . "</div>";

// Ordenar CREATE TABLE según dependencias (orden extendido)
echo "<div class='info'>🔄 Ordenando CREATE TABLE según dependencias...</div>";

// Definir orden de dependencias completo
$tableOrder = [
    'categorias',
    'plazos_entrega', 
    'configuraciones',
    'fuente_datos',
    'productos',
    'opciones',
    'adicionales',
    'presupuestos',
    'presupuesto_detalle',
    'presupuestos_historial',
    'opcion_precios',
    'precios',
    'adicionales_productos',
    'xls_productos',
    'xls_opciones', 
    'xls_precios',
    'xls_adicionales',
    'xls_adicionales_precios',
    'xls_plazos',
    'xls_productos_adicionales'
];

// Función para extraer nombre de tabla de CREATE TABLE
function extractTableName($createQuery) {
    if (preg_match('/CREATE\s+TABLE\s+`?([^`\s]+)`?\s*\(/i', $createQuery, $matches)) {
        return $matches[1];
    }
    return null;
}

// Ordenar CREATE queries según el orden definido
$orderedCreateQueries = [];
$remainingCreateQueries = $createQueries;

foreach ($tableOrder as $tableName) {
    foreach ($remainingCreateQueries as $index => $query) {
        $queryTableName = extractTableName($query);
        if ($queryTableName === $tableName) {
            $orderedCreateQueries[] = $query;
            unset($remainingCreateQueries[$index]);
            echo "<div class='info'>✅ Ordenada: $tableName</div>";
            break;
        }
    }
}

// Agregar cualquier tabla restante al final
foreach ($remainingCreateQueries as $query) {
    $tableName = extractTableName($query);
    $orderedCreateQueries[] = $query;
    echo "<div class='warning'>⚠️ Tabla no ordenada: $tableName</div>";
}

// Ordenar INSERT queries según el mismo orden
$orderedInsertQueries = [];
$remainingInsertQueries = $insertQueries;

foreach ($tableOrder as $tableName) {
    foreach ($remainingInsertQueries as $index => $query) {
        if (preg_match('/INSERT\s+INTO\s+`?' . preg_quote($tableName) . '`?\s/i', $query)) {
            $orderedInsertQueries[] = $query;
            unset($remainingInsertQueries[$index]);
            break;
        }
    }
}

// Agregar cualquier INSERT restante al final
foreach ($remainingInsertQueries as $query) {
    $orderedInsertQueries[] = $query;
}

echo "<div class='success'>✅ Consultas ordenadas correctamente</div>";
echo "</div>";

// Ejecutar consultas
echo "<div class='step'>";
echo "<h2>📥 Ejecutando Consultas Ordenadas</h2>";

// Deshabilitar verificaciones de foreign key temporalmente
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Combinar todas las consultas en orden
$allQueries = array_merge($orderedCreateQueries, $orderedInsertQueries);
$totalQueries = count($allQueries);

echo "<div class='info'>✅ Total de consultas a ejecutar: $totalQueries</div>";

if ($totalQueries > 0) {
    $conn->autocommit(false);
    
    try {
        $successful = 0;
        $errors = 0;
        
        echo "<div class='progress'><div class='progress-bar' id='progressBar' style='width: 0%'></div></div>";
        echo "<div id='progressText'>Iniciando importación...</div>";
        echo "<div class='debug' id='logOutput' style='max-height: 400px;'>";
        
        foreach ($allQueries as $index => $query) {
            $progress = round(($index + 1) / $totalQueries * 100);
            
            // Determinar tipo de consulta
            $queryType = 'UNKNOWN';
            if (stripos($query, 'CREATE TABLE') !== false) {
                $queryType = 'CREATE';
                $tableName = extractTableName($query);
            } elseif (stripos($query, 'INSERT INTO') !== false) {
                $queryType = 'INSERT';
                if (preg_match('/INSERT\s+INTO\s+`?([^`\s]+)`?\s/i', $query, $matches)) {
                    $tableName = $matches[1];
                } else {
                    $tableName = 'unknown';
                }
            }
            
            echo "[$queryType] " . ($index + 1) . "/$totalQueries: $tableName\n";
            
            if ($conn->query($query)) {
                $successful++;
                echo "✅ Éxito\n\n";
            } else {
                $errors++;
                $error = $conn->error;
                echo "❌ Error: " . htmlspecialchars($error) . "\n";
                
                // Mostrar query completa para errores críticos
                if (!strpos($error, 'already exists') && !strpos($error, 'Duplicate entry')) {
                    echo "Query: " . htmlspecialchars(substr($query, 0, 200)) . "...\n";
                }
                echo "\n";
            }
            
            // Actualizar progreso cada 3 consultas
            if ($index % 3 == 0) {
                echo "<script>
                    if(document.getElementById('progressBar')) {
                        document.getElementById('progressBar').style.width = '{$progress}%';
                        document.getElementById('progressText').innerHTML = 'Procesando: {$progress}% (" . ($index + 1) . "/{$totalQueries})';
                    }
                </script>";
                @ob_flush();
                @flush();
            }
        }
        
        echo "</div>";
        
        // Rehabilitar verificaciones de foreign key
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        $conn->commit();
        
        echo "<div class='success'>✅ Importación completada</div>";
        echo "<div class='info'>📊 Consultas exitosas: $successful de $totalQueries</div>";
        if ($errors > 0) {
            echo "<div class='warning'>⚠️ Consultas con errores: $errors</div>";
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        echo "<div class='error'>❌ Error durante importación: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='error'>❌ No se encontraron consultas para ejecutar</div>";
}
echo "</div>";

// Verificar datos finales
echo "<div class='step'>";
echo "<h2>🔍 Verificación Final</h2>";

// Verificar tablas existentes
$result = $conn->query("SHOW TABLES");
$existingTables = [];
if ($result) {
    while ($row = $result->fetch_array()) {
        $existingTables[] = $row[0];
    }
}

echo "<div class='info'>📋 Tablas creadas (" . count($existingTables) . "): " . implode(', ', $existingTables) . "</div>";

$allTables = ['categorias', 'opciones', 'plazos_entrega', 'presupuestos', 'opcion_precios', 'productos', 'adicionales', 'xls_productos', 'xls_opciones', 'xls_precios', 'configuraciones', 'fuente_datos', 'precios', 'presupuesto_detalle', 'presupuestos_historial', 'xls_adicionales', 'xls_adicionales_precios', 'xls_plazos', 'xls_productos_adicionales'];

$totalRecords = 0;
foreach ($allTables as $table) {
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
    echo "<h3>🎉 ¡INSTALACIÓN LIMPIA EXITOSA!</h3>";
    echo "<p>El sistema se ha instalado completamente desde cero en Railway.</p>";
    echo "<h3>🔗 Enlaces del Sistema:</h3>";
    echo "<p><a href='https://cotizadorcompany-production.up.railway.app/' class='btn' target='_blank'>🏠 Página Principal</a></p>";
    echo "<p><a href='https://cotizadorcompany-production.up.railway.app/sistema/cotizador.php' class='btn' target='_blank'>💰 Cotizador</a></p>";
    echo "<p><a href='https://cotizadorcompany-production.up.railway.app/admin/' class='btn' target='_blank'>🔧 Panel Admin</a></p>";
    echo "<p><strong>Credenciales Admin:</strong> usuario: <code>admin</code>, contraseña: <code>admin123</code></p>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Instalación Parcial</h3>";
    echo "<p>Las tablas se crearon correctamente pero algunos datos pueden no haberse importado.</p>";
    echo "<p>El sistema debería funcionar con datos básicos.</p>";
    echo "</div>";
}

echo "</div>";

echo "</div>"; // container

$conn->close();
?> 