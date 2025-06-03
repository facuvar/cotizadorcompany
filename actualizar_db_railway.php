<?php
/**
 * Script para actualizar la configuración de moneda en Railway
 */

echo "<h1>🔄 Actualización de Base de Datos en Railway</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; white-space: pre-wrap; }
</style>";

echo "<div class='container'>";

// Conectar a la base de datos
require_once 'sistema/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<div class='success'>✅ Conexión exitosa a la base de datos: " . DB_NAME . "</div>";
    echo "<div class='info'>ℹ️ Host: " . DB_HOST . ":" . DB_PORT . "</div>";
    
    // Verificar si estamos en Railway
    if (IS_RAILWAY) {
        echo "<div class='info'>ℹ️ Entorno detectado: Railway</div>";
    } else {
        echo "<div class='warning'>⚠️ Entorno detectado: Local</div>";
    }
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    echo "</div>";
    exit;
}

// Verificar tablas existentes
echo "<h2>🔍 Verificando tablas existentes</h2>";

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='info'>ℹ️ Tablas encontradas: " . count($tables) . "</div>";
    
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error verificando tablas: " . $e->getMessage() . "</div>";
}

// Actualizar configuración de moneda
echo "<h2>🔄 Actualizando configuración de moneda</h2>";

try {
    // Verificar si existe la tabla configuracion
    if (in_array('configuracion', $tables)) {
        // Verificar si existe el registro 'moneda'
        $stmt = $pdo->query("SELECT * FROM configuracion WHERE nombre = 'moneda'");
        $moneda = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($moneda) {
            // Actualizar el valor existente
            $stmt = $pdo->prepare("UPDATE configuracion SET valor = 'ARS', descripcion = 'Pesos Argentinos' WHERE nombre = 'moneda'");
            $stmt->execute();
            echo "<div class='success'>✅ Configuración de moneda actualizada a 'ARS' (Pesos Argentinos)</div>";
        } else {
            // Insertar nuevo registro
            $stmt = $pdo->prepare("INSERT INTO configuracion (nombre, valor, descripcion, tipo) VALUES (?, ?, ?, ?)");
            $stmt->execute(['moneda', 'ARS', 'Pesos Argentinos', 'text']);
            echo "<div class='success'>✅ Configuración de moneda creada: 'ARS' (Pesos Argentinos)</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ La tabla 'configuracion' no existe. Creando tabla...</div>";
        
        // Crear tabla configuracion
        $sql = "
            CREATE TABLE IF NOT EXISTS configuracion (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL UNIQUE,
                valor TEXT,
                descripcion TEXT,
                tipo ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($sql);
        echo "<div class='success'>✅ Tabla 'configuracion' creada</div>";
        
        // Insertar registro de moneda
        $stmt = $pdo->prepare("INSERT INTO configuracion (nombre, valor, descripcion, tipo) VALUES (?, ?, ?, ?)");
        $stmt->execute(['moneda', 'ARS', 'Pesos Argentinos', 'text']);
        echo "<div class='success'>✅ Configuración de moneda creada: 'ARS' (Pesos Argentinos)</div>";
    }
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error actualizando configuración de moneda: " . $e->getMessage() . "</div>";
}

// Verificar resultados finales
echo "<h2>📊 Verificación final</h2>";

try {
    if (in_array('configuracion', $tables)) {
        $stmt = $pdo->query("SELECT * FROM configuracion WHERE nombre = 'moneda'");
        $moneda = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($moneda) {
            echo "<div class='info'>ℹ️ Configuración actual de moneda: " . $moneda['valor'] . " (" . $moneda['descripcion'] . ")</div>";
        } else {
            echo "<div class='warning'>⚠️ No se encontró configuración de moneda</div>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error verificando configuración final: " . $e->getMessage() . "</div>";
}

echo "<h2>🎉 ¡Actualización completa!</h2>";
echo "</div>"; 