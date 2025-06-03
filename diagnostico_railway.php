<?php
/**
 * DIAGNÓSTICO RAILWAY
 * Script para verificar conexión y archivos disponibles
 */

echo "<h1>🔍 Diagnóstico Railway</h1>";
echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>";

// 1. Verificar archivos disponibles
echo "<h2>📁 Archivos en el directorio:</h2>";
$files = scandir('.');
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $size = is_file($file) ? filesize($file) : 'DIR';
        echo "<p>📄 {$file} ({$size} bytes)</p>";
    }
}

// 2. Buscar archivos SQL específicamente
echo "<h2>🗃️ Archivos SQL encontrados:</h2>";
$sql_files = glob('*.sql');
if (empty($sql_files)) {
    echo "<p>❌ No se encontraron archivos .sql</p>";
} else {
    foreach ($sql_files as $file) {
        $size = filesize($file);
        echo "<p>✅ {$file} ({$size} bytes)</p>";
    }
}

// 3. Verificar variables de entorno
echo "<h2>🔧 Variables de entorno Railway:</h2>";
$env_vars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
foreach ($env_vars as $var) {
    $value = $_ENV[$var] ?? 'NO DEFINIDA';
    if ($var == 'DB_PASS') {
        $value = $value ? '***DEFINIDA***' : 'NO DEFINIDA';
    }
    echo "<p>{$var}: {$value}</p>";
}

// 4. Intentar conexión a base de datos
echo "<h2>🚀 Prueba de conexión a Railway:</h2>";

$config = [
    'host' => $_ENV['DB_HOST'] ?? 'autorack.proxy.rlwy.net',
    'port' => $_ENV['DB_PORT'] ?? '47470',
    'database' => $_ENV['DB_NAME'] ?? 'railway',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? 'LjEWJGgCJHdBcgfGAhGjfEBEhfJjGGjA'
];

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "<p>✅ Conexión exitosa a Railway</p>";
    echo "<p>Host: {$config['host']}:{$config['port']}</p>";
    echo "<p>Base de datos: {$config['database']}</p>";
    
    // Verificar tablas existentes
    echo "<h3>📊 Tablas existentes:</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo "<p>❌ No hay tablas en la base de datos</p>";
    } else {
        foreach ($tables as $table) {
            $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            echo "<p>📋 {$table}: {$count} registros</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 5. Información del servidor
echo "<h2>🖥️ Información del servidor:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Directorio actual: " . getcwd() . "</p>";
echo "<p>Usuario del servidor: " . get_current_user() . "</p>";

// 6. Acciones recomendadas
echo "<h2>🎯 Acciones recomendadas:</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Si no hay archivos SQL:</strong></p>";
echo "<p>1. Railway no se sincronizó con GitHub</p>";
echo "<p>2. Necesitas hacer redeploy manual en Railway</p>";
echo "<p>3. O subir archivos manualmente</p>";
echo "<br>";
echo "<p><strong>Si hay error de conexión:</strong></p>";
echo "<p>1. Verificar credenciales de Railway</p>";
echo "<p>2. Confirmar que MySQL esté activo</p>";
echo "<p>3. Revisar variables de entorno</p>";
echo "<br>";
echo "<p><strong>Si no hay tablas:</strong></p>";
echo "<p>1. La base de datos está vacía</p>";
echo "<p>2. Necesitas ejecutar el script de importación</p>";
echo "</div>";

echo "<h2>🔗 Enlaces útiles:</h2>";
echo "<p><a href='actualizar_db_railway.php'>🔄 Actualizar DB Railway</a></p>";
echo "<p><a href='importar_desde_sql.php'>📥 Importar desde SQL</a></p>";
echo "<p><a href='importar_railway_simple.php'>⚡ Importador Simple</a></p>";
echo "<p><a href='cotizador.php'>🎯 Ir al Cotizador</a></p>";
?> 