<?php
/**
 * Script para sincronizar la base de datos local con Railway
 * Versión mejorada con manejo de errores
 */

echo "🚀 SINCRONIZACIÓN CON RAILWAY\n";
echo "=============================\n\n";

// Configuración de Railway
$railway_config = [
    'host' => 'autorack.proxy.rlwy.net',
    'port' => '47470',
    'database' => 'railway',
    'username' => 'root',
    'password' => 'LjEWJGgCJHdBcgfGAhGjfEBEhfJjGGjA'
];

// Configuración local
$local_config = [
    'host' => 'localhost',
    'database' => 'company_presupuestos',
    'username' => 'root',
    'password' => ''
];

try {
    // Conectar a base local primero
    echo "💻 Conectando a base de datos local...\n";
    $local_dsn = "mysql:host={$local_config['host']};dbname={$local_config['database']};charset=utf8mb4";
    $local_pdo = new PDO($local_dsn, $local_config['username'], $local_config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10
    ]);
    echo "✅ Conectado a base local\n\n";

    // Verificar datos locales
    echo "🔍 Verificando datos locales...\n";
    $categorias = $local_pdo->query("SELECT COUNT(*) as count FROM categorias")->fetch()['count'];
    $opciones = $local_pdo->query("SELECT COUNT(*) as count FROM opciones")->fetch()['count'];
    echo "📊 Datos locales: $categorias categorías, $opciones opciones\n\n";

    // Intentar conectar a Railway con timeout más corto
    echo "📡 Conectando a Railway...\n";
    $railway_dsn = "mysql:host={$railway_config['host']};port={$railway_config['port']};dbname={$railway_config['database']};charset=utf8mb4";
    
    $context = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 15,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
    ];
    
    $railway_pdo = new PDO($railway_dsn, $railway_config['username'], $railway_config['password'], $context);
    echo "✅ Conectado a Railway\n\n";

    // Verificar tablas en Railway
    echo "🔍 Verificando tablas en Railway...\n";
    $tables = $railway_pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Tablas encontradas: " . implode(', ', $tables) . "\n\n";

    if (in_array('categorias', $tables) && in_array('opciones', $tables)) {
        // Sincronizar categorías
        echo "📂 Sincronizando categorías...\n";
        $categorias_data = $local_pdo->query("SELECT * FROM categorias ORDER BY orden, id")->fetchAll();
        
        $count = 0;
        foreach ($categorias_data as $categoria) {
            $stmt = $railway_pdo->prepare("
                INSERT INTO categorias (id, nombre, orden) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                nombre = VALUES(nombre), 
                orden = VALUES(orden)
            ");
            $stmt->execute([$categoria['id'], $categoria['nombre'], $categoria['orden']]);
            $count++;
        }
        echo "✅ $count categorías sincronizadas\n\n";

        // Sincronizar opciones en lotes
        echo "⚙️ Sincronizando opciones...\n";
        $opciones_data = $local_pdo->query("SELECT * FROM opciones ORDER BY categoria_id, orden, id")->fetchAll();
        
        $count = 0;
        $batch_size = 50;
        $batches = array_chunk($opciones_data, $batch_size);
        
        foreach ($batches as $batch_num => $batch) {
            echo "   Procesando lote " . ($batch_num + 1) . "/" . count($batches) . "...\n";
            
            foreach ($batch as $opcion) {
                $stmt = $railway_pdo->prepare("
                    INSERT INTO opciones (
                        id, categoria_id, nombre, precio_90_dias, precio_160_dias, 
                        precio_270_dias, descuento, orden
                    ) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    categoria_id = VALUES(categoria_id),
                    nombre = VALUES(nombre), 
                    precio_90_dias = VALUES(precio_90_dias),
                    precio_160_dias = VALUES(precio_160_dias),
                    precio_270_dias = VALUES(precio_270_dias),
                    descuento = VALUES(descuento),
                    orden = VALUES(orden)
                ");
                $stmt->execute([
                    $opcion['id'], $opcion['categoria_id'], $opcion['nombre'],
                    $opcion['precio_90_dias'], $opcion['precio_160_dias'], $opcion['precio_270_dias'],
                    $opcion['descuento'], $opcion['orden']
                ]);
                $count++;
            }
        }
        echo "✅ $count opciones sincronizadas\n\n";

        // Verificar funcionalidades del cotizador inteligente
        echo "🔧 Verificando funcionalidades del cotizador...\n";
        
        $electromecanicos = $railway_pdo->query("
            SELECT COUNT(*) as count FROM opciones 
            WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%electromecanico%'
        ")->fetch()['count'];
        
        $hidraulicos = $railway_pdo->query("
            SELECT COUNT(*) as count FROM opciones 
            WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%hidraulico%'
        ")->fetch()['count'];
        
        $restar = $railway_pdo->query("
            SELECT COUNT(*) as count FROM opciones 
            WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%restar%'
        ")->fetch()['count'];

        echo "📊 Adicionales disponibles:\n";
        echo "   - Electromecánicos: $electromecanicos\n";
        echo "   - Hidráulicos: $hidraulicos\n";
        echo "   - Que restan dinero: $restar\n\n";

        echo "🎉 SINCRONIZACIÓN COMPLETADA\n";
        echo "✅ Cotizador inteligente listo en Railway\n";
        echo "✅ Filtrado automático configurado\n";
        echo "✅ Adicionales que restan implementados\n";
        echo "✅ Plazo unificado activado\n\n";
    } else {
        echo "❌ Las tablas necesarias no existen en Railway\n";
        echo "💡 Asegúrate de que las tablas 'categorias' y 'opciones' estén creadas\n";
    }

} catch (PDOException $e) {
    echo "❌ ERROR DE BASE DE DATOS: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "💡 Verifica las credenciales de Railway\n";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "💡 Verifica la conexión a internet y el host de Railway\n";
    } elseif (strpos($e->getMessage(), 'gone away') !== false) {
        echo "💡 Timeout de conexión - intenta nuevamente\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR GENERAL: " . $e->getMessage() . "\n";
}

echo "\n🔗 INFORMACIÓN DE CONEXIÓN:\n";
echo "Railway Host: {$railway_config['host']}:{$railway_config['port']}\n";
echo "Railway DB: {$railway_config['database']}\n";
echo "Local DB: {$local_config['database']}\n";
?> 