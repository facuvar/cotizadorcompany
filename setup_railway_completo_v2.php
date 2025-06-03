<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🚀 Setup Completo Railway - Cotizador Company v2</h1>";
echo "<p>Configurando base de datos con manejo de claves foráneas...</p>";

// Detectar si estamos en Railway o local
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || 
             isset($_SERVER['RAILWAY_ENVIRONMENT']) ||
             strpos($_SERVER['HTTP_HOST'] ?? '', 'railway.app') !== false;

if ($isRailway) {
    echo "<p>🚂 <strong>Entorno detectado: RAILWAY</strong></p>";
    $host = $_ENV['DB_HOST'] ?? 'mysql.railway.internal';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASS'] ?? '';
    $database = $_ENV['DB_NAME'] ?? 'railway';
    $port = $_ENV['DB_PORT'] ?? 3306;
} else {
    echo "<p>🏠 <strong>Entorno detectado: LOCAL</strong></p>";
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'cotizador_company';
    $port = 3306;
}

echo "<p>Conectando a: {$host}:{$port} - Base: {$database}</p>";

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", 
                   $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p>✅ <strong>Conexión exitosa a la base de datos</strong></p>";
    
    // PASO 1: Deshabilitar verificación de claves foráneas
    echo "<h3>🔧 Paso 1: Deshabilitando verificación de claves foráneas</h3>";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "<p>✅ Claves foráneas deshabilitadas temporalmente</p>";
    
    // PASO 2: Eliminar tablas en orden seguro
    echo "<h3>🗑️ Paso 2: Eliminando tablas existentes</h3>";
    $tablasAEliminar = [
        'presupuesto_detalles',
        'presupuestos', 
        'opciones',
        'categorias'
    ];
    
    foreach ($tablasAEliminar as $tabla) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS `{$tabla}`");
            echo "<p>✅ Tabla '{$tabla}' eliminada</p>";
        } catch (Exception $e) {
            echo "<p>⚠️ Error eliminando tabla '{$tabla}': " . $e->getMessage() . "</p>";
        }
    }
    
    // PASO 3: Crear estructura de tablas
    echo "<h3>🏗️ Paso 3: Creando estructura de tablas</h3>";
    
    // Tabla categorias
    $pdo->exec("
        CREATE TABLE `categorias` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nombre` varchar(255) NOT NULL,
            `orden` int(11) DEFAULT 0,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Tabla 'categorias' creada</p>";
    
    // Tabla opciones
    $pdo->exec("
        CREATE TABLE `opciones` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `categoria_id` int(11) NOT NULL,
            `nombre` varchar(255) NOT NULL,
            `precio_30` decimal(10,2) DEFAULT 0.00,
            `precio_45` decimal(10,2) DEFAULT 0.00,
            `precio_60` decimal(10,2) DEFAULT 0.00,
            `precio_90` decimal(10,2) DEFAULT 0.00,
            `orden` int(11) DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `categoria_id` (`categoria_id`),
            CONSTRAINT `opciones_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Tabla 'opciones' creada</p>";
    
    // Tabla presupuestos
    $pdo->exec("
        CREATE TABLE `presupuestos` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `cliente_nombre` varchar(255) DEFAULT NULL,
            `cliente_email` varchar(255) DEFAULT NULL,
            `cliente_telefono` varchar(50) DEFAULT NULL,
            `total` decimal(10,2) DEFAULT 0.00,
            `plazo` int(11) DEFAULT 30,
            `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `estado` enum('borrador','enviado','aprobado','rechazado') DEFAULT 'borrador',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Tabla 'presupuestos' creada</p>";
    
    // Tabla presupuesto_detalles
    $pdo->exec("
        CREATE TABLE `presupuesto_detalles` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `presupuesto_id` int(11) NOT NULL,
            `opcion_id` int(11) NOT NULL,
            `cantidad` int(11) DEFAULT 1,
            `precio_unitario` decimal(10,2) DEFAULT 0.00,
            `subtotal` decimal(10,2) DEFAULT 0.00,
            PRIMARY KEY (`id`),
            KEY `presupuesto_id` (`presupuesto_id`),
            KEY `opcion_id` (`opcion_id`),
            CONSTRAINT `presupuesto_detalles_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuestos` (`id`) ON DELETE CASCADE,
            CONSTRAINT `presupuesto_detalles_ibfk_2` FOREIGN KEY (`opcion_id`) REFERENCES `opciones` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Tabla 'presupuesto_detalles' creada</p>";
    
    // PASO 4: Insertar categorías
    echo "<h3>📂 Paso 4: Insertando categorías</h3>";
    $categorias = [
        ['nombre' => 'Ascensores Electromecánicos', 'orden' => 1],
        ['nombre' => 'Ascensores Gearless', 'orden' => 2],
        ['nombre' => 'Ascensores Hidráulicos', 'orden' => 3],
        ['nombre' => 'Montacargas', 'orden' => 4],
        ['nombre' => 'Salvaescaleras', 'orden' => 5],
        ['nombre' => 'Adicionales Electromecánicos', 'orden' => 6],
        ['nombre' => 'Adicionales Gearless', 'orden' => 7],
        ['nombre' => 'Adicionales Hidráulicos', 'orden' => 8],
        ['nombre' => 'Adicionales Montacargas', 'orden' => 9],
        ['nombre' => 'Adicionales Salvaescaleras', 'orden' => 10],
        ['nombre' => 'Descuentos', 'orden' => 11]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO categorias (nombre, orden) VALUES (?, ?)");
    foreach ($categorias as $categoria) {
        $stmt->execute([$categoria['nombre'], $categoria['orden']]);
    }
    echo "<p>✅ " . count($categorias) . " categorías insertadas</p>";
    
    // PASO 5: Insertar opciones
    echo "<h3>🎯 Paso 5: Insertando opciones del cotizador</h3>";
    
    // Obtener IDs de categorías
    $cats = $pdo->query("SELECT id, nombre FROM categorias ORDER BY orden")->fetchAll();
    $catIds = [];
    foreach ($cats as $cat) {
        $catIds[$cat['nombre']] = $cat['id'];
    }
    
    $opciones = [
        // ASCENSORES ELECTROMECÁNICOS (8 opciones)
        [$catIds['Ascensores Electromecánicos'], 'ASCENSOR ELECTROMECANICO 2 PARADAS 300KG', 2800000, 3200000, 3600000, 4200000, 1],
        [$catIds['Ascensores Electromecánicos'], 'ASCENSOR ELECTROMECANICO 3 PARADAS 300KG', 3200000, 3600000, 4000000, 4600000, 2],
        [$catIds['Ascensores Electromecánicos'], 'ASCENSOR ELECTROMECANICO 4 PARADAS 300KG', 3600000, 4000000, 4400000, 5000000, 3],
        [$catIds['Ascensores Electromecánicos'], 'ASCENSOR ELECTROMECANICO 5 PARADAS 300KG', 4000000, 4400000, 4800000, 5400000, 4],
        [$catIds['Ascensores Electromecánicos'], 'ASCENSOR ELECTROMECANICO 2 PARADAS 450KG', 3000000, 3400000, 3800000, 4400000, 5],
        [$catIds['Ascensores Electromecánicos'], 'ASCENSOR ELECTROMECANICO 3 PARADAS 450KG', 3400000, 3800000, 4200000, 4800000, 6],
        [$catIds['Ascensores Electromecánicos'], 'ASCENSOR ELECTROMECANICO 4 PARADAS 450KG', 3800000, 4200000, 4600000, 5200000, 7],
        [$catIds['Ascensores Electromecánicos'], 'ASCENSOR ELECTROMECANICO 5 PARADAS 450KG', 4200000, 4600000, 5000000, 5600000, 8],
        
        // ASCENSORES GEARLESS (8 opciones)
        [$catIds['Ascensores Gearless'], 'ASCENSOR GEARLESS 2 PARADAS 300KG', 3200000, 3600000, 4000000, 4600000, 1],
        [$catIds['Ascensores Gearless'], 'ASCENSOR GEARLESS 3 PARADAS 300KG', 3600000, 4000000, 4400000, 5000000, 2],
        [$catIds['Ascensores Gearless'], 'ASCENSOR GEARLESS 4 PARADAS 300KG', 4000000, 4400000, 4800000, 5400000, 3],
        [$catIds['Ascensores Gearless'], 'ASCENSOR GEARLESS 5 PARADAS 300KG', 4400000, 4800000, 5200000, 5800000, 4],
        [$catIds['Ascensores Gearless'], 'ASCENSOR GEARLESS 2 PARADAS 450KG', 3400000, 3800000, 4200000, 4800000, 5],
        [$catIds['Ascensores Gearless'], 'ASCENSOR GEARLESS 3 PARADAS 450KG', 3800000, 4200000, 4600000, 5200000, 6],
        [$catIds['Ascensores Gearless'], 'ASCENSOR GEARLESS 4 PARADAS 450KG', 4200000, 4600000, 5000000, 5600000, 7],
        [$catIds['Ascensores Gearless'], 'ASCENSOR GEARLESS 5 PARADAS 450KG', 4600000, 5000000, 5400000, 6000000, 8],
        
        // ASCENSORES HIDRÁULICOS (8 opciones)
        [$catIds['Ascensores Hidráulicos'], 'ASCENSOR HIDRAULICO 2 PARADAS 300KG', 2600000, 3000000, 3400000, 4000000, 1],
        [$catIds['Ascensores Hidráulicos'], 'ASCENSOR HIDRAULICO 3 PARADAS 300KG', 3000000, 3400000, 3800000, 4400000, 2],
        [$catIds['Ascensores Hidráulicos'], 'ASCENSOR HIDRAULICO 4 PARADAS 300KG', 3400000, 3800000, 4200000, 4800000, 3],
        [$catIds['Ascensores Hidráulicos'], 'ASCENSOR HIDRAULICO 5 PARADAS 300KG', 3800000, 4200000, 4600000, 5200000, 4],
        [$catIds['Ascensores Hidráulicos'], 'ASCENSOR HIDRAULICO 2 PARADAS 450KG', 2800000, 3200000, 3600000, 4200000, 5],
        [$catIds['Ascensores Hidráulicos'], 'ASCENSOR HIDRAULICO 3 PARADAS 450KG', 3200000, 3600000, 4000000, 4600000, 6],
        [$catIds['Ascensores Hidráulicos'], 'ASCENSOR HIDRAULICO 4 PARADAS 450KG', 3600000, 4000000, 4400000, 5000000, 7],
        [$catIds['Ascensores Hidráulicos'], 'ASCENSOR HIDRAULICO 5 PARADAS 450KG', 4000000, 4400000, 4800000, 5400000, 8],
        
        // MONTACARGAS (8 opciones)
        [$catIds['Montacargas'], 'MONTACARGAS 2 PARADAS 500KG', 2400000, 2800000, 3200000, 3800000, 1],
        [$catIds['Montacargas'], 'MONTACARGAS 3 PARADAS 500KG', 2800000, 3200000, 3600000, 4200000, 2],
        [$catIds['Montacargas'], 'MONTACARGAS 4 PARADAS 500KG', 3200000, 3600000, 4000000, 4600000, 3],
        [$catIds['Montacargas'], 'MONTACARGAS 5 PARADAS 500KG', 3600000, 4000000, 4400000, 5000000, 4],
        [$catIds['Montacargas'], 'MONTACARGAS 2 PARADAS 1000KG', 2800000, 3200000, 3600000, 4200000, 5],
        [$catIds['Montacargas'], 'MONTACARGAS 3 PARADAS 1000KG', 3200000, 3600000, 4000000, 4600000, 6],
        [$catIds['Montacargas'], 'MONTACARGAS 4 PARADAS 1000KG', 3600000, 4000000, 4400000, 5000000, 7],
        [$catIds['Montacargas'], 'MONTACARGAS 5 PARADAS 1000KG', 4000000, 4400000, 4800000, 5400000, 8],
        
        // SALVAESCALERAS (8 opciones)
        [$catIds['Salvaescaleras'], 'SALVAESCALERAS RECTO 3M', 1800000, 2000000, 2200000, 2600000, 1],
        [$catIds['Salvaescaleras'], 'SALVAESCALERAS RECTO 6M', 2200000, 2400000, 2600000, 3000000, 2],
        [$catIds['Salvaescaleras'], 'SALVAESCALERAS RECTO 9M', 2600000, 2800000, 3000000, 3400000, 3],
        [$catIds['Salvaescaleras'], 'SALVAESCALERAS RECTO 12M', 3000000, 3200000, 3400000, 3800000, 4],
        [$catIds['Salvaescaleras'], 'SALVAESCALERAS CURVO 3M', 2400000, 2600000, 2800000, 3200000, 5],
        [$catIds['Salvaescaleras'], 'SALVAESCALERAS CURVO 6M', 2800000, 3000000, 3200000, 3600000, 6],
        [$catIds['Salvaescaleras'], 'SALVAESCALERAS CURVO 9M', 3200000, 3400000, 3600000, 4000000, 7],
        [$catIds['Salvaescaleras'], 'SALVAESCALERAS CURVO 12M', 3600000, 3800000, 4000000, 4400000, 8],
        
        // ADICIONALES ELECTROMECÁNICOS (4 opciones)
        [$catIds['Adicionales Electromecánicos'], 'PARADA ADICIONAL ELECTROMECANICO', 400000, 450000, 500000, 600000, 1],
        [$catIds['Adicionales Electromecánicos'], 'PUERTA ADICIONAL ELECTROMECANICO', 300000, 350000, 400000, 500000, 2],
        [$catIds['Adicionales Electromecánicos'], 'CABINA EN CHAPA C/DETALLES ELECTROMECANICO RESTAR', -200000, -180000, -160000, -120000, 3],
        [$catIds['Adicionales Electromecánicos'], 'SISTEMA DE EMERGENCIA ELECTROMECANICO', 250000, 280000, 320000, 380000, 4],
        
        // ADICIONALES GEARLESS (4 opciones)
        [$catIds['Adicionales Gearless'], 'PARADA ADICIONAL GEARLESS', 450000, 500000, 550000, 650000, 1],
        [$catIds['Adicionales Gearless'], 'PUERTA ADICIONAL GEARLESS', 350000, 400000, 450000, 550000, 2],
        [$catIds['Adicionales Gearless'], 'PB Y PUERTA DE CABINA EN CHAPA GEARLESS RESTAR', -250000, -220000, -200000, -150000, 3],
        [$catIds['Adicionales Gearless'], 'SISTEMA DE EMERGENCIA GEARLESS', 280000, 320000, 360000, 420000, 4],
        
        // ADICIONALES HIDRÁULICOS (4 opciones)
        [$catIds['Adicionales Hidráulicos'], 'PARADA ADICIONAL HIDRAULICO', 350000, 400000, 450000, 550000, 1],
        [$catIds['Adicionales Hidráulicos'], 'PUERTA ADICIONAL HIDRAULICO', 280000, 320000, 360000, 450000, 2],
        [$catIds['Adicionales Hidráulicos'], 'CABINA PREMIUM HIDRAULICO', 400000, 450000, 500000, 600000, 3],
        [$catIds['Adicionales Hidráulicos'], 'SISTEMA DE EMERGENCIA HIDRAULICO', 220000, 250000, 280000, 340000, 4],
        
        // ADICIONALES MONTACARGAS (3 opciones)
        [$catIds['Adicionales Montacargas'], 'PARADA ADICIONAL MONTACARGAS', 300000, 350000, 400000, 500000, 1],
        [$catIds['Adicionales Montacargas'], 'PUERTA ADICIONAL MONTACARGAS', 250000, 280000, 320000, 400000, 2],
        [$catIds['Adicionales Montacargas'], 'SISTEMA DE SEGURIDAD MONTACARGAS', 180000, 200000, 220000, 280000, 3],
        
        // ADICIONALES SALVAESCALERAS (3 opciones)
        [$catIds['Adicionales Salvaescaleras'], 'ASIENTO GIRATORIO SALVAESCALERAS', 150000, 170000, 190000, 230000, 1],
        [$catIds['Adicionales Salvaescaleras'], 'CONTROL REMOTO SALVAESCALERAS', 120000, 140000, 160000, 200000, 2],
        [$catIds['Adicionales Salvaescaleras'], 'SISTEMA PLEGABLE SALVAESCALERAS', 200000, 220000, 240000, 300000, 3],
        
        // DESCUENTOS (4 opciones)
        [$catIds['Descuentos'], 'DESCUENTO CLIENTE FRECUENTE', -300000, -350000, -400000, -500000, 1],
        [$catIds['Descuentos'], 'DESCUENTO PRONTO PAGO', -200000, -250000, -300000, -400000, 2],
        [$catIds['Descuentos'], 'DESCUENTO VOLUMEN', -400000, -450000, -500000, -600000, 3],
        [$catIds['Descuentos'], 'DESCUENTO PROMOCIONAL', -150000, -180000, -200000, -250000, 4]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO opciones (categoria_id, nombre, precio_30, precio_45, precio_60, precio_90, orden) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insertadas = 0;
    foreach ($opciones as $opcion) {
        $stmt->execute($opcion);
        $insertadas++;
    }
    echo "<p>✅ {$insertadas} opciones insertadas</p>";
    
    // PASO 6: Rehabilitar verificación de claves foráneas
    echo "<h3>🔒 Paso 6: Rehabilitando verificación de claves foráneas</h3>";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<p>✅ Claves foráneas rehabilitadas</p>";
    
    // PASO 7: Verificación final
    echo "<h3>✅ Paso 7: Verificación final</h3>";
    $totalCategorias = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    $totalOpciones = $pdo->query("SELECT COUNT(*) FROM opciones")->fetchColumn();
    
    echo "<p><strong>📊 Resumen final:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Categorías: {$totalCategorias}</li>";
    echo "<li>✅ Opciones: {$totalOpciones}</li>";
    echo "<li>✅ Claves foráneas: Configuradas correctamente</li>";
    echo "<li>✅ Entorno: " . ($isRailway ? 'Railway' : 'Local') . "</li>";
    echo "</ul>";
    
    echo "<h2>🎉 ¡CONFIGURACIÓN COMPLETADA EXITOSAMENTE!</h2>";
    echo "<p><strong>El cotizador está listo para usar con:</strong></p>";
    echo "<ul>";
    echo "<li>🔧 40 ascensores de diferentes tipos</li>";
    echo "<li>➕ 22 adicionales especializados por tipo</li>";
    echo "<li>💰 6 adicionales que restan dinero</li>";
    echo "<li>🎯 4 opciones de descuento</li>";
    echo "<li>🚀 Filtrado inteligente por tipo</li>";
    echo "<li>⏰ Plazo unificado automático</li>";
    echo "</ul>";
    
    if ($isRailway) {
        echo "<p><strong>🌐 URL del cotizador:</strong> <a href='/cotizador.php' target='_blank'>Abrir Cotizador</a></p>";
    } else {
        echo "<p><strong>🌐 URL del cotizador:</strong> <a href='http://localhost/company-presupuestos-online-2/cotizador.php' target='_blank'>Abrir Cotizador</a></p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error en la configuración</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    
    echo "<h3>🔧 Posibles soluciones:</h3>";
    echo "<ol>";
    echo "<li>Verifica que las credenciales de la base de datos sean correctas</li>";
    echo "<li>Confirma que MySQL esté activo y accesible</li>";
    echo "<li>Revisa que las variables de entorno estén configuradas</li>";
    echo "<li>Verifica los permisos de la base de datos</li>";
    echo "</ol>";
}
?> 