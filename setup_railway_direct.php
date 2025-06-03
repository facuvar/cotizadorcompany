<?php
echo "<h1>🚀 Configuración Railway - Conexión Directa</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; border-radius: 5px; }
    .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
    .btn:hover { background: #0056b3; }
</style>";

echo "<div class='container'>";

// Credenciales directas de Railway
$host = 'mysql.railway.internal';
$user = 'root';
$pass = 'DEACLLVQgoBvLmRKkFqUazfcOaDVwMKd';
$name = 'railway';
$port = '3306';

// Verificar entorno
echo "<div class='step'>";
echo "<h2>🌐 Paso 1: Verificando Entorno</h2>";

$isRailway = isset($_SERVER['RAILWAY_STATIC_URL']) || isset($_ENV['RAILWAY_STATIC_URL']) || strpos($_SERVER['HTTP_HOST'] ?? '', 'railway.app') !== false;

if ($isRailway) {
    echo "<div class='success'>✅ Ejecutándose en Railway</div>";
    echo "<div class='info'>🔗 Host: " . ($_SERVER['HTTP_HOST'] ?? 'No disponible') . "</div>";
} else {
    echo "<div class='warning'>⚠️ Posiblemente ejecutándose en entorno local</div>";
}

echo "<div class='info'>🐘 Versión PHP: " . PHP_VERSION . "</div>";
echo "</div>";

// Mostrar credenciales que vamos a usar
echo "<div class='step'>";
echo "<h2>🔧 Paso 2: Credenciales de Conexión</h2>";

echo "<div class='info'>";
echo "<h3>🎯 Usando credenciales directas:</h3>";
echo "<p><strong>Host:</strong> $host</p>";
echo "<p><strong>Usuario:</strong> $user</p>";
echo "<p><strong>Contraseña:</strong> " . substr($pass, 0, 8) . "...</p>";
echo "<p><strong>Base de datos:</strong> $name</p>";
echo "<p><strong>Puerto:</strong> $port</p>";
echo "</div>";
echo "</div>";

// Conectar a base de datos
echo "<div class='step'>";
echo "<h2>🗄️ Paso 3: Conectando a Base de Datos</h2>";

try {
    echo "<div class='info'>🔌 Intentando conexión directa...</div>";
    
    $conn = new mysqli($host, $user, $pass, $name, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    echo "<div class='success'>✅ ¡Conexión exitosa a MySQL Railway!</div>";
    
    // Verificar información de la conexión
    echo "<div class='info'>📊 Información de la conexión:</div>";
    echo "<div class='info'>🏠 Host info: " . $conn->host_info . "</div>";
    echo "<div class='info'>🔢 Versión servidor: " . $conn->server_info . "</div>";
    echo "<div class='info'>🆔 Thread ID: " . $conn->thread_id . "</div>";
    
    // Verificar y crear tablas
    echo "<h3>📋 Configurando Tablas</h3>";
    
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
    
    echo "<div class='success'>✅ Esquema de base de datos configurado ($tablesCreated tablas)</div>";
    
    // Verificar tablas existentes
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    echo "<div class='info'>📊 Tablas disponibles (" . count($tables) . "): " . implode(', ', $tables) . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<div class='warning'>🔧 Verifica que las credenciales sean correctas</div>";
    
    // Intentar diagnóstico
    echo "<div class='warning'>";
    echo "<h3>🔍 Diagnóstico:</h3>";
    echo "<p>1. Verifica que el servicio MySQL esté ejecutándose en Railway</p>";
    echo "<p>2. Confirma que las credenciales sean exactas</p>";
    echo "<p>3. Asegúrate de que la aplicación y MySQL estén en el mismo proyecto</p>";
    echo "</div>";
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

$baseUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'cotizadorcompany-production.up.railway.app');

echo "<div class='info'>";
echo "<h3>🎯 Accesos Directos:</h3>";
echo "<p><a href='$baseUrl/' class='btn' target='_blank'>🏠 Página Principal</a></p>";
echo "<p><a href='$baseUrl/sistema/cotizador.php' class='btn' target='_blank'>💰 Cotizador</a></p>";
echo "<p><a href='$baseUrl/admin/' class='btn' target='_blank'>🔧 Panel Admin</a></p>";
echo "<p><a href='$baseUrl/import_railway_direct.php' class='btn' target='_blank'>📥 Importar Datos</a></p>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3>🔑 Credenciales de Administrador:</h3>";
echo "<p><strong>Usuario:</strong> admin</p>";
echo "<p><strong>Contraseña:</strong> admin123</p>";
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