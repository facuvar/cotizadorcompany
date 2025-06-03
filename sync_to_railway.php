<?php
/**
 * Script para sincronizar datos locales con Railway
 */

echo "<h1>🚂 SINCRONIZAR CON RAILWAY</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .progress { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace; font-size: 12px; }
    .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; white-space: pre-wrap; }
</style>";

echo "<div class='container'>";

// Configuración local
$localConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'company_presupuestos',
    'port' => 3306
];

// Configuración Railway
$railwayConfig = [
    'host' => 'mysql.railway.internal',
    'user' => 'root',
    'pass' => 'DEACLLVQgoBvLmRKkFqUazfcOaDVwMKd',
    'name' => 'railway',
    'port' => 3306
];

echo "<h2>🔌 Conectar a base de datos local</h2>";

try {
    $localPdo = new PDO(
        "mysql:host={$localConfig['host']};port={$localConfig['port']};dbname={$localConfig['name']};charset=utf8mb4",
        $localConfig['user'],
        $localConfig['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<div class='success'>✅ Conexión local exitosa</div>";
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error conexión local: " . $e->getMessage() . "</div>";
    echo "</div>";
    exit;
}

echo "<h2>🚂 Conectar a Railway</h2>";

// Intentar diferentes configuraciones de Railway
$railwayConnected = false;
$railwayPdo = null;

// Intentar con variables de entorno
if (getenv('MYSQLHOST')) {
    $railwayConfig = [
        'host' => getenv('MYSQLHOST'),
        'user' => getenv('MYSQLUSER'),
        'pass' => getenv('MYSQLPASSWORD'),
        'name' => getenv('MYSQLDATABASE'),
        'port' => getenv('MYSQLPORT') ?: 3306
    ];
    echo "<div class='info'>🔍 Intentando con variables de entorno Railway</div>";
} else {
    echo "<div class='info'>🔍 Usando credenciales directas de Railway</div>";
}

try {
    $railwayPdo = new PDO(
        "mysql:host={$railwayConfig['host']};port={$railwayConfig['port']};dbname={$railwayConfig['name']};charset=utf8mb4",
        $railwayConfig['user'],
        $railwayConfig['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<div class='success'>✅ Conexión Railway exitosa</div>";
    $railwayConnected = true;
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error conexión Railway: " . $e->getMessage() . "</div>";
    echo "<div class='warning'>⚠️ Este script debe ejecutarse desde Railway para funcionar correctamente</div>";
}

if (!$railwayConnected) {
    echo "<h2>📋 Generar script SQL para Railway</h2>";
    echo "<div class='info'>Como no se pudo conectar a Railway, generaremos un archivo SQL que puedes ejecutar manualmente</div>";
    
    // Generar script SQL
    $sqlFile = 'railway_sync.sql';
    $sql = "-- Script de sincronización para Railway\n";
    $sql .= "-- Generado: " . date('Y-m-d H:i:s') . "\n\n";
    
    $sql .= "-- Limpiar datos existentes\n";
    $sql .= "DELETE FROM opciones;\n";
    $sql .= "DELETE FROM categorias;\n";
    $sql .= "DELETE FROM plazos_entrega;\n";
    $sql .= "ALTER TABLE categorias AUTO_INCREMENT = 1;\n";
    $sql .= "ALTER TABLE opciones AUTO_INCREMENT = 1;\n";
    $sql .= "ALTER TABLE plazos_entrega AUTO_INCREMENT = 1;\n\n";
    
    // Exportar plazos_entrega
    $sql .= "-- Plazos de entrega\n";
    $stmt = $localPdo->query("SELECT * FROM plazos_entrega ORDER BY id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sql .= "INSERT INTO plazos_entrega (nombre, dias, orden, activo) VALUES (";
        $sql .= "'" . addslashes($row['nombre']) . "', ";
        $sql .= $row['dias'] . ", ";
        $sql .= $row['orden'] . ", ";
        $sql .= $row['activo'] . ");\n";
    }
    $sql .= "\n";
    
    // Exportar categorias
    $sql .= "-- Categorías\n";
    $stmt = $localPdo->query("SELECT * FROM categorias ORDER BY id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sql .= "INSERT INTO categorias (nombre, descripcion, orden, activo) VALUES (";
        $sql .= "'" . addslashes($row['nombre']) . "', ";
        $sql .= "'" . addslashes($row['descripcion']) . "', ";
        $sql .= $row['orden'] . ", ";
        $sql .= $row['activo'] . ");\n";
    }
    $sql .= "\n";
    
    // Exportar opciones
    $sql .= "-- Opciones\n";
    $stmt = $localPdo->query("SELECT * FROM opciones ORDER BY id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sql .= "INSERT INTO opciones (categoria_id, nombre, descripcion, precio, precio_90_dias, precio_160_dias, precio_270_dias, descuento, orden, activo) VALUES (";
        $sql .= $row['categoria_id'] . ", ";
        $sql .= "'" . addslashes($row['nombre']) . "', ";
        $sql .= ($row['descripcion'] ? "'" . addslashes($row['descripcion']) . "'" : "NULL") . ", ";
        $sql .= $row['precio'] . ", ";
        $sql .= $row['precio_90_dias'] . ", ";
        $sql .= $row['precio_160_dias'] . ", ";
        $sql .= $row['precio_270_dias'] . ", ";
        $sql .= $row['descuento'] . ", ";
        $sql .= $row['orden'] . ", ";
        $sql .= $row['activo'] . ");\n";
    }
    
    file_put_contents($sqlFile, $sql);
    echo "<div class='success'>✅ Archivo SQL generado: $sqlFile</div>";
    echo "<div class='info'>📋 Instrucciones:</div>";
    echo "<div class='code'>1. Descarga el archivo: <a href='$sqlFile' download>$sqlFile</a>
2. Ve a tu panel de Railway
3. Abre la consola de MySQL
4. Ejecuta el contenido del archivo SQL</div>";
    
    echo "</div>";
    exit;
}

// Si llegamos aquí, tenemos conexión a Railway
echo "<h2>🔄 Sincronizar datos</h2>";

try {
    // Limpiar Railway
    echo "<div class='progress'>🧹 Limpiando datos en Railway...</div>";
    $railwayPdo->exec("DELETE FROM opciones");
    $railwayPdo->exec("DELETE FROM categorias");
    $railwayPdo->exec("DELETE FROM plazos_entrega");
    $railwayPdo->exec("ALTER TABLE categorias AUTO_INCREMENT = 1");
    $railwayPdo->exec("ALTER TABLE opciones AUTO_INCREMENT = 1");
    $railwayPdo->exec("ALTER TABLE plazos_entrega AUTO_INCREMENT = 1");
    
    // Sincronizar plazos_entrega
    echo "<div class='progress'>⏰ Sincronizando plazos de entrega...</div>";
    $stmt = $localPdo->query("SELECT * FROM plazos_entrega ORDER BY id");
    $railwayStmt = $railwayPdo->prepare("INSERT INTO plazos_entrega (nombre, dias, orden, activo) VALUES (?, ?, ?, ?)");
    $count = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $railwayStmt->execute([$row['nombre'], $row['dias'], $row['orden'], $row['activo']]);
        $count++;
    }
    echo "<div class='success'>✅ $count plazos sincronizados</div>";
    
    // Sincronizar categorias
    echo "<div class='progress'>📂 Sincronizando categorías...</div>";
    $stmt = $localPdo->query("SELECT * FROM categorias ORDER BY id");
    $railwayStmt = $railwayPdo->prepare("INSERT INTO categorias (nombre, descripcion, orden, activo) VALUES (?, ?, ?, ?)");
    $count = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $railwayStmt->execute([$row['nombre'], $row['descripcion'], $row['orden'], $row['activo']]);
        $count++;
    }
    echo "<div class='success'>✅ $count categorías sincronizadas</div>";
    
    // Sincronizar opciones
    echo "<div class='progress'>🔧 Sincronizando opciones...</div>";
    $stmt = $localPdo->query("SELECT * FROM opciones ORDER BY id");
    $railwayStmt = $railwayPdo->prepare("INSERT INTO opciones (categoria_id, nombre, descripcion, precio, precio_90_dias, precio_160_dias, precio_270_dias, descuento, orden, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $count = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $railwayStmt->execute([
            $row['categoria_id'], 
            $row['nombre'], 
            $row['descripcion'], 
            $row['precio'], 
            $row['precio_90_dias'], 
            $row['precio_160_dias'], 
            $row['precio_270_dias'], 
            $row['descuento'], 
            $row['orden'], 
            $row['activo']
        ]);
        $count++;
    }
    echo "<div class='success'>✅ $count opciones sincronizadas</div>";
    
    // Verificar sincronización
    echo "<h2>📊 Verificar sincronización</h2>";
    
    $localStats = [];
    $railwayStats = [];
    
    $tables = ['categorias', 'opciones', 'plazos_entrega'];
    
    foreach ($tables as $table) {
        $stmt = $localPdo->query("SELECT COUNT(*) as total FROM $table");
        $localStats[$table] = $stmt->fetch()['total'];
        
        $stmt = $railwayPdo->query("SELECT COUNT(*) as total FROM $table");
        $railwayStats[$table] = $stmt->fetch()['total'];
    }
    
    echo "<table>";
    echo "<tr><th>Tabla</th><th>Local</th><th>Railway</th><th>Estado</th></tr>";
    
    foreach ($tables as $table) {
        $local = $localStats[$table];
        $railway = $railwayStats[$table];
        $status = ($local == $railway) ? "✅ OK" : "❌ Error";
        
        echo "<tr>";
        echo "<td>$table</td>";
        echo "<td>$local</td>";
        echo "<td>$railway</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<div class='success'>🎉 ¡Sincronización completada!</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error durante la sincronización: " . $e->getMessage() . "</div>";
}

echo "</div>";
?> 