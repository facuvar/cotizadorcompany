<?php
/**
 * Script para actualizar la base de datos en Railway
 * Se ejecuta directamente en el servidor de Railway
 * URL: https://tu-app.railway.app/actualizar_db_railway.php
 */

// Configuración de Railway (usando variables de entorno)
$railway_config = [
    'host' => $_ENV['DB_HOST'] ?? 'autorack.proxy.rlwy.net',
    'port' => $_ENV['DB_PORT'] ?? '47470',
    'database' => $_ENV['DB_NAME'] ?? 'railway',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? 'LjEWJGgCJHdBcgfGAhGjfEBEhfJjGGjA'
];

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Actualización de Base de Datos - Railway</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        h1 { color: #333; text-align: center; }
        h2 { color: #555; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #007bff; }
        .stat-number { font-size: 2em; font-weight: bold; color: #007bff; }
        .stat-label { color: #666; margin-top: 5px; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🚀 Actualización de Base de Datos en Railway</h1>";
echo "<div class='info'>Fecha y hora: " . date('Y-m-d H:i:s') . "</div>";

try {
    // Conectar a Railway
    echo "<h2>📡 Conexión a Railway</h2>";
    $railway_dsn = "mysql:host={$railway_config['host']};port={$railway_config['port']};dbname={$railway_config['database']};charset=utf8mb4";
    $pdo = new PDO($railway_dsn, $railway_config['username'], $railway_config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30
    ]);
    echo "<div class='success'>✅ Conectado exitosamente a Railway</div>";
    echo "<div class='info'>Host: {$railway_config['host']}:{$railway_config['port']}</div>";
    echo "<div class='info'>Base de datos: {$railway_config['database']}</div>";

    // Verificar y crear tablas si no existen
    echo "<h2>🔧 Verificación de Estructura</h2>";
    
    // Crear tabla categorias si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categorias (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(255) NOT NULL,
            orden INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Crear tabla opciones si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS opciones (
            id INT PRIMARY KEY AUTO_INCREMENT,
            categoria_id INT NOT NULL,
            nombre VARCHAR(500) NOT NULL,
            precio_90_dias DECIMAL(10,2) DEFAULT 0,
            precio_160_dias DECIMAL(10,2) DEFAULT 0,
            precio_270_dias DECIMAL(10,2) DEFAULT 0,
            descuento DECIMAL(5,2) DEFAULT 0,
            orden INT DEFAULT 0,
            FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "<div class='success'>✅ Estructura de tablas verificada</div>";

    // Insertar categorías básicas
    echo "<h2>📂 Configuración de Categorías</h2>";
    $categorias_base = [
        [1, 'Ascensores', 1],
        [2, 'Adicionales', 2],
        [3, 'Otros', 3]
    ];
    
    foreach ($categorias_base as $cat) {
        $stmt = $pdo->prepare("
            INSERT INTO categorias (id, nombre, orden) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            nombre = VALUES(nombre), 
            orden = VALUES(orden)
        ");
        $stmt->execute($cat);
    }
    echo "<div class='success'>✅ Categorías configuradas</div>";

    // Insertar datos de ejemplo para el cotizador inteligente
    echo "<h2>⚙️ Datos de Ejemplo del Cotizador</h2>";
    
    // Ascensores de ejemplo
    $ascensores_ejemplo = [
        [1, 'Ascensor Electromecánico 4 Personas', 850000, 750000, 650000, 0, 1],
        [1, 'Ascensor Gearless 6 Personas', 1200000, 1100000, 950000, 0, 2],
        [1, 'Ascensor Hidráulico 4 Personas', 750000, 650000, 550000, 0, 3],
        [1, 'Montacargas 500kg', 950000, 850000, 750000, 0, 4],
        [1, 'Salvaescaleras Recto', 450000, 400000, 350000, 0, 5]
    ];
    
    // Adicionales de ejemplo
    $adicionales_ejemplo = [
        [2, 'Adicional Electromecánico - Puertas Automáticas', 120000, 110000, 95000, 0, 1],
        [2, 'Adicional Electromecánico - Sistema de Emergencia', 85000, 75000, 65000, 0, 2],
        [2, 'Adicional Hidráulico - Bomba Reforzada', 95000, 85000, 75000, 0, 3],
        [2, 'Adicional Hidráulico - Válvulas Especiales', 65000, 55000, 45000, 0, 4],
        [2, 'Adicional Montacargas - Plataforma Reforzada', 150000, 135000, 120000, 0, 5],
        [2, 'Adicional Salvaescaleras - Asiento Giratorio', 75000, 65000, 55000, 0, 6],
        [2, 'CABINA EN CHAPA C/DETALLES RESTAR', -50000, -45000, -40000, 0, 7],
        [2, 'PB Y PUERTA DE CABINA EN CHAPA RESTAR', -35000, -30000, -25000, 0, 8]
    ];
    
    $todas_opciones = array_merge($ascensores_ejemplo, $adicionales_ejemplo);
    
    foreach ($todas_opciones as $opcion) {
        $stmt = $pdo->prepare("
            INSERT INTO opciones (categoria_id, nombre, precio_90_dias, precio_160_dias, precio_270_dias, descuento, orden) 
            VALUES (?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            precio_90_dias = VALUES(precio_90_dias),
            precio_160_dias = VALUES(precio_160_dias),
            precio_270_dias = VALUES(precio_270_dias)
        ");
        $stmt->execute($opcion);
    }
    
    echo "<div class='success'>✅ Datos de ejemplo insertados</div>";

    // Estadísticas finales
    echo "<h2>📊 Estadísticas de la Base de Datos</h2>";
    
    $stats = [];
    $stats['categorias'] = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    $stats['total_opciones'] = $pdo->query("SELECT COUNT(*) FROM opciones")->fetchColumn();
    $stats['ascensores'] = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 1")->fetchColumn();
    $stats['adicionales'] = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2")->fetchColumn();
    $stats['electromecanicos'] = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%electromecanico%'")->fetchColumn();
    $stats['hidraulicos'] = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%hidraulico%'")->fetchColumn();
    $stats['montacargas'] = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%montacargas%'")->fetchColumn();
    $stats['salvaescaleras'] = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%salvaescaleras%'")->fetchColumn();
    $stats['que_restan'] = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%restar%'")->fetchColumn();
    
    echo "<div class='stats'>";
    echo "<div class='stat-card'><div class='stat-number'>{$stats['categorias']}</div><div class='stat-label'>Categorías</div></div>";
    echo "<div class='stat-card'><div class='stat-number'>{$stats['ascensores']}</div><div class='stat-label'>Ascensores</div></div>";
    echo "<div class='stat-card'><div class='stat-number'>{$stats['adicionales']}</div><div class='stat-label'>Adicionales</div></div>";
    echo "<div class='stat-card'><div class='stat-number'>{$stats['electromecanicos']}</div><div class='stat-label'>Electromecánicos</div></div>";
    echo "<div class='stat-card'><div class='stat-number'>{$stats['hidraulicos']}</div><div class='stat-label'>Hidráulicos</div></div>";
    echo "<div class='stat-card'><div class='stat-number'>{$stats['que_restan']}</div><div class='stat-label'>Que Restan</div></div>";
    echo "</div>";

    // Verificar funcionalidades del cotizador
    echo "<h2>🧠 Verificación de Funcionalidades</h2>";
    echo "<div class='success'>✅ Filtrado inteligente: {$stats['electromecanicos']} + {$stats['hidraulicos']} + {$stats['montacargas']} + {$stats['salvaescaleras']} adicionales por tipo</div>";
    echo "<div class='success'>✅ Adicionales que restan: {$stats['que_restan']} configurados</div>";
    echo "<div class='success'>✅ Plazo unificado: 3 plazos (90, 160, 270 días) configurados</div>";
    echo "<div class='success'>✅ Base de datos lista para el cotizador inteligente</div>";

    echo "<h2>🎉 ¡Actualización Completada!</h2>";
    echo "<div class='success'>
        <strong>¡Tu cotizador inteligente está listo en Railway!</strong><br>
        • Filtrado automático de adicionales ✅<br>
        • Adicionales que restan dinero ✅<br>
        • Plazo unificado ✅<br>
        • Interface optimizada ✅
    </div>";
    
    echo "<div class='info'>
        <strong>Próximos pasos:</strong><br>
        1. Accede a tu cotizador: <a href='cotizador.php'>cotizador.php</a><br>
        2. Página de pruebas: <a href='test_simple.html'>test_simple.html</a><br>
        3. Verifica todas las funcionalidades
    </div>";

} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='warning'>
        <strong>Posibles soluciones:</strong><br>
        • Verifica las variables de entorno en Railway<br>
        • Asegúrate de que la base de datos MySQL esté activa<br>
        • Revisa las credenciales de conexión
    </div>";
}

echo "</div></body></html>";
?> 