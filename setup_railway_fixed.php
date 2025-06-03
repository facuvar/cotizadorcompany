<?php
echo "<h1>🚀 Configuración Railway - Sistema de Presupuestos (FIXED)</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; border-radius: 5px; }
    .env-var { background: #f8f9fa; padding: 5px; border-radius: 3px; font-family: monospace; }
    .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
    .btn:hover { background: #0056b3; }
    .debug { background: #f1f3f4; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px; margin: 10px 0; }
</style>";

echo "<div class='container'>";

// Función para obtener variables de entorno de múltiples formas
function getEnvVar($name, $default = null) {
    // Intentar múltiples métodos para obtener variables de entorno
    $value = $_ENV[$name] ?? getenv($name) ?? $_SERVER[$name] ?? $default;
    return $value;
}

// Verificar si estamos en Railway
echo "<div class='step'>";
echo "<h2>🌐 Paso 1: Verificando Entorno</h2>";

$isRailway = getEnvVar('RAILWAY_ENVIRONMENT') || getEnvVar('RAILWAY_STATIC_URL');
$railwayUrl = getEnvVar('RAILWAY_STATIC_URL');

if ($isRailway) {
    echo "<div class='success'>✅ Ejecutándose en Railway</div>";
    echo "<div class='info'>🔗 URL del proyecto: " . ($railwayUrl ?: 'No disponible') . "</div>";
} else {
    echo "<div class='warning'>⚠️ No se detectó entorno Railway</div>";
}

echo "<div class='info'>🐘 Versión PHP: " . PHP_VERSION . "</div>";

// Debug de variables de entorno
echo "<div class='debug'>";
echo "<strong>🔍 Debug Variables de Entorno:</strong><br>";
echo "RAILWAY_ENVIRONMENT: " . (getEnvVar('RAILWAY_ENVIRONMENT') ?: 'No definida') . "<br>";
echo "RAILWAY_STATIC_URL: " . (getEnvVar('RAILWAY_STATIC_URL') ?: 'No definida') . "<br>";
echo "Método \$_ENV disponible: " . (function_exists('getenv') ? 'Sí' : 'No') . "<br>";
echo "Total variables \$_ENV: " . count($_ENV) . "<br>";
echo "Total variables \$_SERVER: " . count($_SERVER) . "<br>";
echo "</div>";
echo "</div>";

// Verificar variables de entorno con múltiples métodos
echo "<div class='step'>";
echo "<h2>🔧 Paso 2: Verificando Variables de Entorno (Mejorado)</h2>";

$requiredVars = [
    'DB_HOST' => 'mysql.railway.internal',
    'DB_USER' => 'root', 
    'DB_PASS' => 'DEACLLVQgoBvLmRKkFqUazfcOaDVwMKd',
    'DB_NAME' => 'railway',
    'DB_PORT' => '3306'
];

$configuredVars = [];
$missingVars = [];

foreach ($requiredVars as $var => $defaultValue) {
    $value = getEnvVar($var);
    
    if ($value && !empty($value)) {
        echo "<div class='success'>✅ <span class='env-var'>$var</span>: Configurada (" . substr($value, 0, 10) . "...)</div>";
        $configuredVars[$var] = $value;
    } else {
        echo "<div class='error'>❌ <span class='env-var'>$var</span>: No configurada</div>";
        echo "<div class='warning'>💡 Usando valor por defecto: $defaultValue</div>";
        $configuredVars[$var] = $defaultValue;
        $missingVars[] = $var;
    }
}

if (!empty($missingVars)) {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Variables faltantes (usando valores por defecto)</h3>";
    echo "<p>Si hay problemas de conexión, configura estas variables en Railway:</p>";
    echo "<ul>";
    foreach ($missingVars as $var) {
        echo "<li><span class='env-var'>$var</span></li>";
    }
    echo "</ul>";
    echo "</div>";
}
echo "</div>";

// Intentar conexión a base de datos
echo "<div class='step'>";
echo "<h2>🗄️ Paso 3: Conectando a Base de Datos</h2>";

try {
    $host = $configuredVars['DB_HOST'];
    $user = $configuredVars['DB_USER'];
    $pass = $configuredVars['DB_PASS'];
    $name = $configuredVars['DB_NAME'];
    $port = $configuredVars['DB_PORT'];
    
    echo "<div class='info'>🔌 Conectando a: $host:$port</div>";
    echo "<div class='info'>👤 Usuario: $user</div>";
    echo "<div class='info'>🗄️ Base de datos: $name</div>";
    
    $conn = new mysqli($host, $user, $pass, $name, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    echo "<div class='success'>✅ Conexión exitosa a MySQL</div>";
    
    // Verificar y crear tablas
    echo "<h3>📋 Configurando Tablas</h3>";
    
    // Crear esquema básico si no existe
    $tables = [
        "CREATE TABLE IF NOT EXISTS `categorias` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nombre` varchar(255) NOT NULL,
            `descripcion` text,
            `orden` int(11) DEFAULT 0,
            `activo` tinyint(1) DEFAULT 1,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE IF NOT EXISTS `opciones` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `categoria_id` int(11) NOT NULL,
            `nombre` varchar(255) NOT NULL,
            `descripcion` text,
            `precio_90` decimal(10,2) DEFAULT 0.00,
            `precio_160` decimal(10,2) DEFAULT 0.00,
            `precio_270` decimal(10,2) DEFAULT 0.00,
            `orden` int(11) DEFAULT 0,
            `activo` tinyint(1) DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `categoria_id` (`categoria_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE IF NOT EXISTS `plazos_entrega` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nombre` varchar(100) NOT NULL,
            `dias` int(11) NOT NULL,
            `descripcion` text,
            `activo` tinyint(1) DEFAULT 1,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE IF NOT EXISTS `presupuestos` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `cliente_nombre` varchar(255) DEFAULT NULL,
            `cliente_email` varchar(255) DEFAULT NULL,
            `total` decimal(10,2) DEFAULT 0.00,
            `plazo_id` int(11) DEFAULT NULL,
            `opciones_json` longtext,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];
    
    $tablesCreated = 0;
    foreach ($tables as $sql) {
        if ($conn->query($sql)) {
            $tablesCreated++;
        } else {
            echo "<div class='warning'>⚠️ " . $conn->error . "</div>";
        }
    }
    
    echo "<div class='success'>✅ Esquema de base de datos verificado ($tablesCreated tablas)</div>";
    
    // Verificar tablas existentes
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    echo "<div class='info'>📊 Tablas disponibles (" . count($tables) . "): " . implode(', ', $tables) . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<div class='warning'>🔧 Verifica las credenciales de la base de datos</div>";
}
echo "</div>";

// Verificar datos
if (isset($conn) && !$conn->connect_error) {
    echo "<div class='step'>";
    echo "<h2>📊 Paso 4: Verificando Datos</h2>";
    
    try {
        $result = $conn->query("SELECT COUNT(*) as total FROM categorias");
        $row = $result->fetch_assoc();
        
        if ($row['total'] > 0) {
            echo "<div class='success'>✅ Sistema con datos: {$row['total']} categorías</div>";
            
            // Mostrar algunas categorías
            $result = $conn->query("SELECT nombre FROM categorias LIMIT 3");
            $cats = [];
            while ($row = $result->fetch_assoc()) {
                $cats[] = $row['nombre'];
            }
            echo "<div class='info'>📋 Categorías: " . implode(', ', $cats) . "...</div>";
        } else {
            echo "<div class='warning'>⚠️ No hay datos cargados</div>";
            echo "<div class='info'>💡 Usa el script de importación para cargar datos</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error verificando datos: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
}

// Enlaces del sistema
echo "<div class='step'>";
echo "<h2>🔗 Paso 5: Enlaces del Sistema</h2>";

$baseUrl = $isRailway 
    ? 'https://' . ($railwayUrl ?: $_SERVER['HTTP_HOST'])
    : 'http://' . $_SERVER['HTTP_HOST'];

echo "<div class='info'>";
echo "<h3>🎯 Accesos Directos:</h3>";
echo "<p><a href='$baseUrl/' class='btn' target='_blank'>🏠 Página Principal</a></p>";
echo "<p><a href='$baseUrl/sistema/cotizador.php' class='btn' target='_blank'>💰 Cotizador</a></p>";
echo "<p><a href='$baseUrl/admin/' class='btn' target='_blank'>🔧 Panel Admin</a></p>";
echo "<p><a href='$baseUrl/import_to_railway.php' class='btn' target='_blank'>📥 Importar Datos</a></p>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3>🔑 Credenciales de Administrador:</h3>";
echo "<p><strong>Usuario:</strong> " . (getEnvVar('ADMIN_USER') ?: 'admin') . "</p>";
echo "<p><strong>Contraseña:</strong> admin123 (por defecto)</p>";
echo "</div>";
echo "</div>";

// Estado final
echo "<div class='step'>";
echo "<h2>🎉 Estado del Sistema</h2>";

if (isset($conn) && !$conn->connect_error) {
    echo "<div class='success'>";
    echo "<h3>✅ ¡Sistema configurado correctamente!</h3>";
    echo "<p>🚀 La base de datos está conectada y las tablas están listas</p>";
    echo "<p>📥 Ahora puedes importar los datos usando el script de importación</p>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Configuración incompleta</h3>";
    echo "<p>Revisa la conexión a la base de datos</p>";
    echo "</div>";
}
echo "</div>";

echo "</div>"; // container

if (isset($conn)) {
    $conn->close();
}
?> 