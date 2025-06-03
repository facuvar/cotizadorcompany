<?php
/**
 * Script para importar datos locales a Railway
 * Ejecutar después de configurar las variables de entorno en Railway
 */

echo "<h1>🚀 Importación de Datos a Railway</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; border-radius: 5px; }
    .progress { background: #e9ecef; border-radius: 5px; overflow: hidden; margin: 10px 0; }
    .progress-bar { background: #007bff; color: white; text-align: center; padding: 5px; transition: width 0.3s; }
</style>";

echo "<div class='container'>";

// Verificar entorno
echo "<div class='step'>";
echo "<h2>🌐 Verificando Entorno</h2>";

$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || isset($_ENV['RAILWAY_STATIC_URL']);
if ($isRailway) {
    echo "<div class='success'>✅ Ejecutándose en Railway</div>";
} else {
    echo "<div class='warning'>⚠️ Ejecutándose en entorno local</div>";
}
echo "</div>";

// Conectar a Railway
echo "<div class='step'>";
echo "<h2>🔌 Conectando a Railway MySQL</h2>";

try {
    $host = $_ENV['DB_HOST'] ?? 'mysql.railway.internal';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? 'DEACLLVQgoBvLmRKkFqUazfcOaDVwMKd';
    $name = $_ENV['DB_NAME'] ?? 'railway';
    $port = $_ENV['DB_PORT'] ?? '3306';
    
    echo "<div class='info'>🔌 Conectando a Railway: $host:$port</div>";
    
    $conn = new mysqli($host, $user, $pass, $name, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    echo "<div class='success'>✅ Conexión exitosa a Railway MySQL</div>";
    echo "<div class='info'>📊 Base de datos: $name</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<div class='warning'>🔧 Verifica las variables de entorno en Railway</div>";
    exit;
}
echo "</div>";

// Leer archivo SQL
echo "<div class='step'>";
echo "<h2>📄 Leyendo Datos de Exportación</h2>";

$sqlFile = 'railway_import.sql';
if (!file_exists($sqlFile)) {
    echo "<div class='error'>❌ No se encontró el archivo $sqlFile</div>";
    echo "<div class='info'>💡 Ejecuta primero la exportación local</div>";
    exit;
}

$sqlContent = file_get_contents($sqlFile);
$fileSize = filesize($sqlFile);
echo "<div class='success'>✅ Archivo SQL leído: " . number_format($fileSize) . " bytes</div>";

// Dividir en consultas individuales
$queries = array_filter(array_map('trim', explode(';', $sqlContent)));
echo "<div class='info'>📋 Total de consultas: " . count($queries) . "</div>";
echo "</div>";

// Importar datos
echo "<div class='step'>";
echo "<h2>📥 Importando Datos</h2>";

$conn->autocommit(false); // Iniciar transacción

try {
    $successful = 0;
    $errors = 0;
    $total = count($queries);
    
    echo "<div class='progress'>";
    echo "<div class='progress-bar' id='progressBar' style='width: 0%'>0%</div>";
    echo "</div>";
    
    echo "<div id='status'></div>";
    
    foreach ($queries as $index => $query) {
        if (empty($query) || strpos($query, '--') === 0) {
            continue;
        }
        
        // Actualizar progreso
        $progress = round(($index / $total) * 100);
        echo "<script>
            document.getElementById('progressBar').style.width = '{$progress}%';
            document.getElementById('progressBar').textContent = '{$progress}%';
        </script>";
        
        if ($conn->query($query)) {
            $successful++;
        } else {
            $errors++;
            // Solo mostrar errores críticos
            if (!strpos($conn->error, 'already exists') && !strpos($conn->error, 'Duplicate entry')) {
                echo "<div class='warning'>⚠️ Query " . ($index + 1) . ": " . $conn->error . "</div>";
            }
        }
        
        // Flush output para mostrar progreso en tiempo real
        if ($index % 10 == 0) {
            ob_flush();
            flush();
        }
    }
    
    $conn->commit();
    
    echo "<div class='success'>✅ Importación completada</div>";
    echo "<div class='info'>📊 Consultas exitosas: $successful</div>";
    if ($errors > 0) {
        echo "<div class='warning'>⚠️ Consultas con advertencias: $errors (normal para tablas existentes)</div>";
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
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error verificando datos: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Enlaces finales
echo "<div class='step'>";
echo "<h2>🎉 ¡Importación Completada!</h2>";

$baseUrl = $isRailway 
    ? 'https://' . ($_ENV['RAILWAY_STATIC_URL'] ?? $_SERVER['HTTP_HOST'])
    : 'http://' . $_SERVER['HTTP_HOST'];

echo "<div class='success'>";
echo "<h3>✅ Sistema listo para usar</h3>";
echo "<p>🔗 <a href='$baseUrl/' target='_blank'>Página Principal</a></p>";
echo "<p>💰 <a href='$baseUrl/sistema/cotizador.php' target='_blank'>Cotizador</a></p>";
echo "<p>🔧 <a href='$baseUrl/admin/' target='_blank'>Panel Admin</a></p>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>🔑 Credenciales de Admin:</h3>";
echo "<p><strong>Usuario:</strong> admin</p>";
echo "<p><strong>Contraseña:</strong> admin123</p>";
echo "</div>";
echo "</div>";

echo "</div>"; // container

$conn->close();
?> 