<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico de Conexión - Cotizador Company</h1>";

// Configuración local
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cotizador_company';
$port = 3306;

echo "<h2>📋 Información del Sistema</h2>";
echo "<p><strong>Host:</strong> {$host}</p>";
echo "<p><strong>Puerto:</strong> {$port}</p>";
echo "<p><strong>Base de datos:</strong> {$database}</p>";
echo "<p><strong>Usuario:</strong> {$username}</p>";

// Verificar extensión MySQL
echo "<h2>🔧 Verificación de Extensiones PHP</h2>";
if (extension_loaded('pdo_mysql')) {
    echo "<p>✅ PDO MySQL: Disponible</p>";
} else {
    echo "<p>❌ PDO MySQL: NO disponible</p>";
}

if (extension_loaded('mysqli')) {
    echo "<p>✅ MySQLi: Disponible</p>";
} else {
    echo "<p>❌ MySQLi: NO disponible</p>";
}

// Intentar conexión
echo "<h2>🔌 Prueba de Conexión</h2>";
try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", 
                   $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p>✅ <strong>Conexión al servidor MySQL: EXITOSA</strong></p>";
    
    // Verificar si existe la base de datos
    $databases = $pdo->query("SHOW DATABASES")->fetchAll();
    $dbExists = false;
    echo "<h3>📂 Bases de datos disponibles:</h3>";
    echo "<ul>";
    foreach ($databases as $db) {
        echo "<li>" . $db['Database'];
        if ($db['Database'] === $database) {
            echo " <strong>(OBJETIVO)</strong>";
            $dbExists = true;
        }
        echo "</li>";
    }
    echo "</ul>";
    
    if (!$dbExists) {
        echo "<p>⚠️ <strong>La base de datos '{$database}' NO existe. Creándola...</strong></p>";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p>✅ Base de datos '{$database}' creada</p>";
    }
    
    // Conectar a la base de datos específica
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", 
                   $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p>✅ <strong>Conexión a la base de datos '{$database}': EXITOSA</strong></p>";
    
    // Verificar tablas existentes
    echo "<h3>📊 Tablas existentes:</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll();
    if (empty($tables)) {
        echo "<p>⚠️ No hay tablas en la base de datos</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            $tableName = $table["Tables_in_{$database}"];
            echo "<li><strong>{$tableName}</strong>";
            
            // Contar registros
            try {
                $count = $pdo->query("SELECT COUNT(*) as total FROM `{$tableName}`")->fetch();
                echo " ({$count['total']} registros)";
            } catch (Exception $e) {
                echo " (Error contando registros)";
            }
            echo "</li>";
        }
        echo "</ul>";
    }
    
    // Verificar estructura específica si existen las tablas
    $requiredTables = ['categorias', 'opciones', 'presupuestos', 'presupuesto_detalles'];
    echo "<h3>🎯 Verificación de Tablas Requeridas:</h3>";
    foreach ($requiredTables as $requiredTable) {
        $exists = false;
        foreach ($tables as $table) {
            if ($table["Tables_in_{$database}"] === $requiredTable) {
                $exists = true;
                break;
            }
        }
        
        if ($exists) {
            echo "<p>✅ Tabla '{$requiredTable}': Existe</p>";
            
            // Mostrar estructura
            try {
                $structure = $pdo->query("DESCRIBE `{$requiredTable}`")->fetchAll();
                echo "<details><summary>Ver estructura de '{$requiredTable}'</summary>";
                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
                foreach ($structure as $field) {
                    echo "<tr>";
                    echo "<td>{$field['Field']}</td>";
                    echo "<td>{$field['Type']}</td>";
                    echo "<td>{$field['Null']}</td>";
                    echo "<td>{$field['Key']}</td>";
                    echo "<td>{$field['Default']}</td>";
                    echo "</tr>";
                }
                echo "</table></details>";
            } catch (Exception $e) {
                echo "<p>⚠️ Error obteniendo estructura: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>❌ Tabla '{$requiredTable}': NO existe</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error de conexión:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Código de error:</strong> " . $e->getCode() . "</p>";
    
    echo "<h3>🔧 Posibles soluciones:</h3>";
    echo "<ol>";
    echo "<li><strong>Verificar XAMPP:</strong> Asegúrate de que Apache y MySQL estén ejecutándose</li>";
    echo "<li><strong>Verificar puerto:</strong> El puerto MySQL por defecto es 3306</li>";
    echo "<li><strong>Verificar credenciales:</strong> Usuario 'root' sin contraseña es la configuración por defecto</li>";
    echo "<li><strong>Verificar firewall:</strong> Puede estar bloqueando la conexión</li>";
    echo "</ol>";
}

echo "<h2>🚀 Siguiente Paso</h2>";
echo "<p>Si la conexión es exitosa, puedes ejecutar:</p>";
echo "<p><code>http://localhost/company-presupuestos-online-2/setup_railway_completo_v2.php</code></p>";
?> 