<?php
/**
 * 🔍 DEBUG CATEGORÍAS RAILWAY
 * 
 * Diagnóstico específico para entender por qué el cotizador
 * sigue mostrando "Error al cargar las categorías"
 */

// Detectar entorno
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || 
             (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'railway.app') !== false);

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>🔍 Debug Categorías Railway</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f2f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #155724; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { color: #721c24; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        .warning { color: #856404; background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #ffc107; }
        .code { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; margin: 10px 0; white-space: pre-wrap; border: 1px solid #e9ecef; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: 600; }
        .highlight { background: #fff3cd; }
        h1, h2 { color: #333; }
        h1 { text-align: center; color: #dc3545; }
    </style>
</head>
<body>
    <div class='container'>";

echo "<h1>🔍 DEBUG CATEGORÍAS RAILWAY</h1>";

// Mostrar entorno
echo "<div class='info'>";
echo "<h3>🌍 Entorno Detectado</h3>";
echo "<strong>Entorno:</strong> " . ($isRailway ? "🚂 Railway" : "💻 Local") . "<br>";
echo "<strong>Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'No definido') . "<br>";
echo "<strong>PHP Version:</strong> " . PHP_VERSION . "<br>";
echo "</div>";

// Configuración de base de datos
echo "<h2>🔧 Paso 1: Configuración de Base de Datos</h2>";

try {
    if ($isRailway) {
        // Configuración Railway
        $host = $_ENV['MYSQLHOST'] ?? 'localhost';
        $user = $_ENV['MYSQLUSER'] ?? 'root';
        $pass = $_ENV['MYSQLPASSWORD'] ?? '';
        $name = $_ENV['MYSQLDATABASE'] ?? 'railway';
        $port = $_ENV['MYSQLPORT'] ?? 3306;
        
        echo "<div class='info'>";
        echo "<strong>🚂 Configuración Railway:</strong><br>";
        echo "• Host: $host<br>";
        echo "• User: $user<br>";
        echo "• Database: $name<br>";
        echo "• Port: $port<br>";
        echo "• Password: " . (empty($pass) ? 'Vacío' : 'Configurado (' . strlen($pass) . ' caracteres)') . "<br>";
        echo "</div>";
    } else {
        // Configuración Local
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $name = 'company_presupuestos';
        $port = 3306;
        
        echo "<div class='info'>";
        echo "<strong>💻 Configuración Local:</strong><br>";
        echo "• Host: $host<br>";
        echo "• User: $user<br>";
        echo "• Database: $name<br>";
        echo "• Port: $port<br>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error obteniendo configuración: " . $e->getMessage() . "</div>";
}

// Conexión a base de datos
echo "<h2>🔌 Paso 2: Conexión a Base de Datos</h2>";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<div class='success'>✅ Conexión exitosa a la base de datos</div>";
    
    // Información del servidor
    $version = $pdo->query("SELECT VERSION() as version")->fetch();
    echo "<div class='info'>📊 Versión MySQL: " . $version['version'] . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    echo "</div></body></html>";
    exit;
}

// Verificar tabla categorias
echo "<h2>📋 Paso 3: Verificar Tabla Categorías</h2>";

try {
    // Verificar si existe la tabla
    $stmt = $pdo->query("SHOW TABLES LIKE 'categorias'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<div class='success'>✅ Tabla 'categorias' existe</div>";
        
        // Estructura de la tabla
        echo "<h3>🏗️ Estructura de la tabla:</h3>";
        $stmt = $pdo->query("DESCRIBE categorias");
        $structure = $stmt->fetchAll();
        
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($structure as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categorias");
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            echo "<div class='success'>✅ Tabla tiene $count registros</div>";
            
            // Mostrar contenido
            echo "<h3>📊 Contenido de la tabla:</h3>";
            $stmt = $pdo->query("SELECT * FROM categorias ORDER BY id");
            $categorias = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Orden</th><th>Activo</th></tr>";
            foreach ($categorias as $cat) {
                echo "<tr>";
                echo "<td>" . $cat['id'] . "</td>";
                echo "<td>" . htmlspecialchars($cat['nombre'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($cat['descripcion'] ?? '') . "</td>";
                echo "<td>" . ($cat['orden'] ?? 'NULL') . "</td>";
                echo "<td>" . ($cat['activo'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } else {
            echo "<div class='error'>❌ La tabla existe pero está vacía</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Tabla 'categorias' NO existe</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error verificando tabla: " . $e->getMessage() . "</div>";
}

// Simular la consulta del cotizador
echo "<h2>🎯 Paso 4: Simular Consulta del Cotizador</h2>";

try {
    // Esta es probablemente la consulta que usa el cotizador
    $queries = [
        "SELECT * FROM categorias WHERE activo = 1 ORDER BY orden ASC, id ASC",
        "SELECT * FROM categorias ORDER BY orden ASC, id ASC", 
        "SELECT * FROM categorias WHERE activo IS NULL OR activo = 1 ORDER BY orden ASC",
        "SELECT * FROM categorias ORDER BY id ASC"
    ];
    
    foreach ($queries as $index => $query) {
        echo "<h4>🔍 Consulta " . ($index + 1) . ":</h4>";
        echo "<div class='code'>$query</div>";
        
        try {
            $stmt = $pdo->query($query);
            $results = $stmt->fetchAll();
            
            if (count($results) > 0) {
                echo "<div class='success'>✅ Consulta exitosa: " . count($results) . " resultados</div>";
                
                // Mostrar primeros resultados
                echo "<table>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Activo</th><th>Orden</th></tr>";
                foreach (array_slice($results, 0, 3) as $row) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['nombre'] ?? '') . "</td>";
                    echo "<td>" . ($row['activo'] ?? 'NULL') . "</td>";
                    echo "<td>" . ($row['orden'] ?? 'NULL') . "</td>";
                    echo "</tr>";
                }
                if (count($results) > 3) {
                    echo "<tr><td colspan='4'>... y " . (count($results) - 3) . " más</td></tr>";
                }
                echo "</table>";
                
            } else {
                echo "<div class='warning'>⚠️ Consulta exitosa pero sin resultados</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error en consulta: " . $e->getMessage() . "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error simulando consultas: " . $e->getMessage() . "</div>";
}

// Verificar archivos del cotizador
echo "<h2>📁 Paso 5: Verificar Archivos del Cotizador</h2>";

$cotizadorFiles = [
    'cotizador.php',
    'sistema/cotizador.php', 
    'includes/cotizador.php',
    'sistema/includes/functions.php',
    'sistema/config.php'
];

foreach ($cotizadorFiles as $file) {
    if (file_exists($file)) {
        echo "<div class='success'>✅ Archivo encontrado: $file</div>";
    } else {
        echo "<div class='warning'>⚠️ Archivo no encontrado: $file</div>";
    }
}

// Buscar la función que carga categorías
echo "<h2>🔍 Paso 6: Buscar Función de Carga de Categorías</h2>";

$searchFiles = ['cotizador.php', 'sistema/cotizador.php'];
foreach ($searchFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Buscar patrones comunes
        $patterns = [
            '/SELECT.*FROM.*categorias/i',
            '/cargar.*categorias/i',
            '/getCategorias/i',
            '/loadCategories/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                echo "<div class='info'>🔍 Patrón encontrado en $file: " . htmlspecialchars($matches[0]) . "</div>";
            }
        }
    }
}

echo "<h2>🎯 Conclusiones y Próximos Pasos</h2>";

echo "<div class='warning'>";
echo "<h3>📋 Resumen del Diagnóstico:</h3>";
echo "<ul>";
echo "<li>✅ Conexión a base de datos: OK</li>";
echo "<li>✅ Tabla categorias: " . ($tableExists ? "Existe" : "NO existe") . "</li>";
if ($tableExists) {
    echo "<li>✅ Registros en categorias: $count</li>";
}
echo "</ul>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>🚀 Próximos pasos recomendados:</h3>";
echo "<ol>";
echo "<li><strong>Verificar el archivo cotizador.php</strong> - Ver exactamente qué consulta está fallando</li>";
echo "<li><strong>Revisar la configuración</strong> - Asegurar que usa la base de datos correcta</li>";
echo "<li><strong>Verificar permisos</strong> - El usuario de BD debe tener acceso a la tabla</li>";
echo "<li><strong>Revisar logs de PHP</strong> - Puede haber errores específicos</li>";
echo "</ol>";
echo "</div>";

echo "</div></body></html>";
?> 