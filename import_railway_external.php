<?php
/**
 * Script para importar datos a Railway desde máquina local
 * Usa URL externa de Railway para conexión remota
 */

echo "<h1>🚀 Importación a Railway (Conexión Externa)</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; border-radius: 5px; }
    .progress { background: #e9ecef; border-radius: 5px; overflow: hidden; margin: 10px 0; }
    .progress-bar { background: #007bff; color: white; text-align: center; padding: 5px; transition: width 0.3s; }
    .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
    .debug { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace; font-size: 12px; }
    .input-group { margin: 15px 0; }
    .input-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .input-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
</style>";

echo "<div class='container'>";

// Configuración de Railway - necesitamos la URL externa
echo "<div class='step'>";
echo "<h2>🔧 Configuración de Conexión</h2>";
echo "<div class='warning'>⚠️ <strong>Importante:</strong> Necesitas la URL externa de tu base de datos Railway</div>";
echo "<div class='info'>📋 Ve a tu proyecto en Railway → Base de datos → Connect → External URL</div>";

// Intentar diferentes configuraciones
$configs = [
    [
        'name' => 'Railway External (Opción 1)',
        'host' => 'autorack.proxy.rlwy.net',
        'user' => 'root',
        'pass' => 'DEACLLVQgoBvLmRKkFqUazfcOaDVwMKd',
        'name' => 'railway',
        'port' => '3306'
    ],
    [
        'name' => 'Railway External (Opción 2)',
        'host' => 'mysql.railway.app',
        'user' => 'root',
        'pass' => 'DEACLLVQgoBvLmRKkFqUazfcOaDVwMKd',
        'name' => 'railway',
        'port' => '3306'
    ]
];

$conn = null;
$workingConfig = null;

foreach ($configs as $config) {
    echo "<div class='info'>🔍 Probando: {$config['name']}</div>";
    echo "<div class='info'>📡 Host: {$config['host']}:{$config['port']}</div>";
    
    try {
        $testConn = new mysqli($config['host'], $config['user'], $config['pass'], $config['name'], $config['port']);
        
        if ($testConn->connect_error) {
            echo "<div class='warning'>❌ Falló: " . $testConn->connect_error . "</div>";
            continue;
        }
        
        echo "<div class='success'>✅ ¡Conexión exitosa!</div>";
        $conn = $testConn;
        $workingConfig = $config;
        break;
        
    } catch (Exception $e) {
        echo "<div class='warning'>❌ Error: " . $e->getMessage() . "</div>";
    }
}

if (!$conn) {
    echo "<div class='error'>❌ No se pudo conectar con ninguna configuración</div>";
    echo "<div class='warning'>";
    echo "<h3>🔧 Solución Manual:</h3>";
    echo "<p>1. Ve a tu proyecto Railway</p>";
    echo "<p>2. Selecciona tu base de datos MySQL</p>";
    echo "<p>3. Ve a la pestaña 'Connect'</p>";
    echo "<p>4. Copia la 'External URL' que se ve así:</p>";
    echo "<code>mysql://root:password@host:port/database</code>";
    echo "<p>5. Edita este script con los datos correctos</p>";
    echo "</div>";
    exit;
}

// Configurar charset
$conn->set_charset("utf8");
echo "<div class='info'>🔤 Charset configurado: " . $conn->character_set_name() . "</div>";
echo "<div class='info'>🔢 Versión servidor: " . $conn->server_info . "</div>";
echo "</div>";

// Leer y procesar archivo SQL
echo "<div class='step'>";
echo "<h2>📄 Analizando Archivo SQL</h2>";

$sqlFile = 'railway_import.sql';
if (!file_exists($sqlFile)) {
    echo "<div class='error'>❌ No se encontró el archivo $sqlFile</div>";
    echo "<div class='info'>💡 Asegúrate de haber ejecutado el export primero</div>";
    exit;
}

$sqlContent = file_get_contents($sqlFile);
$fileSize = filesize($sqlFile);
echo "<div class='success'>✅ Archivo SQL leído: " . number_format($fileSize) . " bytes</div>";

// Análisis detallado del contenido
$totalLines = substr_count($sqlContent, "\n");
echo "<div class='info'>📄 Total de líneas: " . number_format($totalLines) . "</div>";

// Función para limpiar y validar consultas SQL
function cleanAndValidateSQL($content) {
    $lines = explode("\n", $content);
    $validQueries = [];
    $currentQuery = '';
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Saltar líneas vacías
        if (empty($line)) {
            continue;
        }
        
        // Saltar comentarios
        if (strpos($line, '--') === 0 || strpos($line, '#') === 0) {
            continue;
        }
        
        // Saltar comandos específicos problemáticos
        $skipPatterns = [
            '/^\/\*!/',           // Comentarios MySQL específicos
            '/^LOCK TABLES/',     // Lock tables
            '/^UNLOCK TABLES/',   // Unlock tables
            '/^SET @/',           // Variables de usuario
            '/^SET NAMES/',       // Set names
            '/^SET TIME_ZONE/',   // Time zone
            '/^SET SQL_MODE/',    // SQL mode
            '/^SET FOREIGN_KEY_CHECKS/',  // Foreign key checks
            '/^SET UNIQUE_CHECKS/',       // Unique checks
            '/^SET AUTOCOMMIT/',          // Autocommit
            '/^START TRANSACTION/',       // Start transaction
            '/^COMMIT/',                  // Commit
            '/^USE `/',                   // USE database
        ];
        
        $skip = false;
        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                $skip = true;
                break;
            }
        }
        
        if ($skip) {
            continue;
        }
        
        // Acumular líneas de la consulta
        $currentQuery .= $line . ' ';
        
        // Si la línea termina con ';', es el final de una consulta
        if (substr($line, -1) === ';') {
            $query = trim($currentQuery);
            
            // Validar que la consulta no esté vacía y tenga contenido válido
            if (!empty($query) && strlen($query) > 1 && $query !== ';') {
                // Verificar que sea una consulta SQL válida básica
                if (preg_match('/^(INSERT|CREATE|ALTER|UPDATE|DELETE|DROP)\s+/i', $query)) {
                    $validQueries[] = rtrim($query, ';'); // Remover el ; final
                }
            }
            
            $currentQuery = '';
        }
    }
    
    return $validQueries;
}

echo "<div class='info'>🔍 Procesando y validando consultas...</div>";
$queries = cleanAndValidateSQL($sqlContent);
echo "<div class='success'>✅ Consultas válidas encontradas: " . count($queries) . "</div>";

// Mostrar estadísticas de tipos de consulta
$queryTypes = [];
foreach ($queries as $query) {
    if (preg_match('/^(INSERT|CREATE|ALTER|UPDATE|DELETE|DROP)\s+/i', $query, $matches)) {
        $type = strtoupper($matches[1]);
        $queryTypes[$type] = ($queryTypes[$type] ?? 0) + 1;
    }
}

echo "<div class='info'>📊 Tipos de consulta:</div>";
echo "<ul>";
foreach ($queryTypes as $type => $count) {
    echo "<li>$type: $count consultas</li>";
}
echo "</ul>";
echo "</div>";

// Importar datos
echo "<div class='step'>";
echo "<h2>📥 Importando Datos</h2>";

if (empty($queries)) {
    echo "<div class='error'>❌ No se encontraron consultas válidas para importar</div>";
    exit;
}

$conn->autocommit(false);

try {
    $successful = 0;
    $errors = 0;
    $total = count($queries);
    
    echo "<div class='progress'>";
    echo "<div class='progress-bar' id='progressBar' style='width: 0%'>0%</div>";
    echo "</div>";
    
    echo "<div id='queryLog' style='max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin: 10px 0; background: #f8f9fa;'>";
    
    foreach ($queries as $index => $query) {
        // Actualizar progreso
        $progress = round((($index + 1) / $total) * 100);
        echo "<script>
            if(document.getElementById('progressBar')) {
                document.getElementById('progressBar').style.width = '{$progress}%';
                document.getElementById('progressBar').textContent = '{$progress}%';
            }
        </script>";
        
        // Log de la consulta actual
        $shortQuery = substr($query, 0, 80) . (strlen($query) > 80 ? '...' : '');
        echo "<div style='color: #666; font-size: 11px;'>Ejecutando: " . htmlspecialchars($shortQuery) . "</div>";
        
        // Ejecutar query
        if ($conn->query($query)) {
            $successful++;
            echo "<div style='color: green; font-size: 11px;'>✅ Éxito</div>";
        } else {
            $errors++;
            $error = $conn->error;
            echo "<div style='color: red; font-size: 11px;'>❌ Error: " . htmlspecialchars($error) . "</div>";
            
            // Si es un error crítico, mostrar la consulta completa
            if (!strpos($error, 'already exists') && 
                !strpos($error, 'Duplicate entry') &&
                !strpos($error, "doesn't exist")) {
                echo "<div class='debug'>Query completa: " . htmlspecialchars($query) . "</div>";
            }
        }
        
        // Flush output cada 5 queries
        if ($index % 5 == 0) {
            ob_flush();
            flush();
        }
    }
    
    echo "</div>"; // queryLog
    
    $conn->commit();
    
    echo "<div class='success'>✅ Importación completada</div>";
    echo "<div class='info'>📊 Consultas exitosas: $successful de $total</div>";
    if ($errors > 0) {
        echo "<div class='warning'>⚠️ Consultas con errores: $errors</div>";
    }
    
} catch (Exception $e) {
    $conn->rollback();
    echo "<div class='error'>❌ Error durante importación: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Verificar datos importados
echo "<div class='step'>";
echo "<h2>🔍 Verificando Datos Importados</h2>";

try {
    // Verificar tablas principales
    $tables = ['categorias', 'opciones', 'plazos_entrega', 'presupuestos'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) as total FROM $table");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<div class='info'>📋 $table: {$row['total']} registros</div>";
        } else {
            echo "<div class='warning'>⚠️ No se pudo verificar tabla: $table</div>";
        }
    }
    
    // Mostrar algunas categorías como ejemplo
    $result = $conn->query("SELECT nombre FROM categorias LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "<div class='success'>✅ Categorías disponibles:</div>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['nombre']) . "</li>";
        }
        echo "</ul>";
    }
    
    // Verificar opciones
    $result = $conn->query("SELECT COUNT(*) as total FROM opciones");
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row['total'] > 0) {
            echo "<div class='success'>✅ Sistema con {$row['total']} opciones cargadas</div>";
        } else {
            echo "<div class='warning'>⚠️ No se encontraron opciones en la base de datos</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error verificando datos: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Enlaces finales
echo "<div class='step'>";
echo "<h2>🎉 Proceso Completado</h2>";

echo "<div class='success'>";
echo "<h3>🔗 Enlaces del Sistema Railway:</h3>";
echo "<p><a href='https://cotizadorcompany-production.up.railway.app/' class='btn' target='_blank'>🏠 Página Principal</a></p>";
echo "<p><a href='https://cotizadorcompany-production.up.railway.app/sistema/cotizador.php' class='btn' target='_blank'>💰 Cotizador</a></p>";
echo "<p><a href='https://cotizadorcompany-production.up.railway.app/admin/' class='btn' target='_blank'>🔧 Panel Admin</a></p>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>🔑 Credenciales de Admin:</h3>";
echo "<p><strong>Usuario:</strong> admin</p>";
echo "<p><strong>Contraseña:</strong> admin123</p>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>📊 Configuración usada:</h3>";
echo "<p><strong>Host:</strong> {$workingConfig['host']}</p>";
echo "<p><strong>Puerto:</strong> {$workingConfig['port']}</p>";
echo "<p><strong>Base de datos:</strong> {$workingConfig['name']}</p>";
echo "</div>";
echo "</div>";

echo "</div>"; // container

$conn->close();
?> 