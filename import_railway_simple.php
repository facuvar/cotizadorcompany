<?php
/**
 * Script para importar datos a Railway - Versión Simplificada
 * Filtra comandos problemáticos de MySQL/MariaDB
 */

echo "<h1>🚀 Importación Simplificada a Railway</h1>";
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
    
    echo "<div class='success'>✅ Conexión exitosa a Railway MySQL</div>";
    echo "<div class='info'>🔢 Versión servidor: " . $conn->server_info . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    exit;
}
echo "</div>";

// Leer y procesar archivo SQL
echo "<div class='step'>";
echo "<h2>📄 Procesando Archivo SQL</h2>";

$sqlFile = 'railway_import.sql';
if (!file_exists($sqlFile)) {
    echo "<div class='error'>❌ No se encontró el archivo $sqlFile</div>";
    exit;
}

$sqlContent = file_get_contents($sqlFile);
echo "<div class='success'>✅ Archivo SQL leído: " . number_format(filesize($sqlFile)) . " bytes</div>";

// Filtrar comandos problemáticos
$lines = explode("\n", $sqlContent);
$cleanedLines = [];

foreach ($lines as $line) {
    $line = trim($line);
    
    // Saltar líneas vacías y comentarios
    if (empty($line) || strpos($line, '--') === 0) {
        continue;
    }
    
    // Saltar comandos específicos de MariaDB/MySQL que pueden causar problemas
    if (strpos($line, '/*!') === 0 || 
        strpos($line, 'LOCK TABLES') === 0 ||
        strpos($line, 'UNLOCK TABLES') === 0 ||
        strpos($line, 'SET @') === 0 ||
        strpos($line, 'SET NAMES') === 0 ||
        strpos($line, 'SET TIME_ZONE') === 0 ||
        strpos($line, 'SET SQL_MODE') === 0 ||
        strpos($line, 'SET FOREIGN_KEY_CHECKS') === 0 ||
        strpos($line, 'SET UNIQUE_CHECKS') === 0) {
        continue;
    }
    
    $cleanedLines[] = $line;
}

$cleanedSQL = implode("\n", $cleanedLines);
$queries = array_filter(array_map('trim', explode(';', $cleanedSQL)));

echo "<div class='info'>📋 Consultas procesadas: " . count($queries) . "</div>";
echo "</div>";

// Importar datos
echo "<div class='step'>";
echo "<h2>📥 Importando Datos</h2>";

$conn->autocommit(false);

try {
    $successful = 0;
    $errors = 0;
    $total = count($queries);
    
    echo "<div class='progress'>";
    echo "<div class='progress-bar' id='progressBar' style='width: 0%'>0%</div>";
    echo "</div>";
    
    foreach ($queries as $index => $query) {
        if (empty($query)) {
            continue;
        }
        
        // Actualizar progreso
        $progress = round(($index / $total) * 100);
        echo "<script>
            if(document.getElementById('progressBar')) {
                document.getElementById('progressBar').style.width = '{$progress}%';
                document.getElementById('progressBar').textContent = '{$progress}%';
            }
        </script>";
        
        // Ejecutar query con manejo de errores mejorado
        if ($conn->query($query)) {
            $successful++;
        } else {
            $errors++;
            $error = $conn->error;
            
            // Solo mostrar errores que no sean esperados
            if (!strpos($error, 'already exists') && 
                !strpos($error, 'Duplicate entry') &&
                !strpos($error, "doesn't exist")) {
                echo "<div class='warning'>⚠️ Query " . ($index + 1) . ": " . htmlspecialchars($error) . "</div>";
                echo "<div class='info'>🔍 Query: " . htmlspecialchars(substr($query, 0, 100)) . "...</div>";
            }
        }
        
        // Flush output cada 10 queries
        if ($index % 10 == 0) {
            ob_flush();
            flush();
        }
    }
    
    $conn->commit();
    
    echo "<div class='success'>✅ Importación completada</div>";
    echo "<div class='info'>📊 Consultas exitosas: $successful</div>";
    if ($errors > 0) {
        echo "<div class='warning'>⚠️ Consultas con advertencias: $errors</div>";
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
    
    // Verificar opciones
    $result = $conn->query("SELECT COUNT(*) as total FROM opciones");
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row['total'] > 0) {
            echo "<div class='success'>✅ Sistema con {$row['total']} opciones cargadas</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error verificando datos: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Enlaces finales
echo "<div class='step'>";
echo "<h2>🎉 ¡Importación Completada!</h2>";

$baseUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'cotizadorcompany-production.up.railway.app');

echo "<div class='success'>";
echo "<h3>✅ Sistema listo para usar</h3>";
echo "<p><a href='$baseUrl/' class='btn' target='_blank'>🏠 Página Principal</a></p>";
echo "<p><a href='$baseUrl/sistema/cotizador.php' class='btn' target='_blank'>💰 Cotizador</a></p>";
echo "<p><a href='$baseUrl/admin/' class='btn' target='_blank'>🔧 Panel Admin</a></p>";
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