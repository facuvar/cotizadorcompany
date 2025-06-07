<?php
/**
 * SETUP COMPLETO PARA RAILWAY - CON DATOS REALES
 * Script autónomo que no depende de archivos externos
 * Incluye todos los datos del cotizador inteligente
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Setup Railway Completo</title></head><body>";
echo "<h1>🚀 Setup Railway Completo - Con Datos Reales</h1>";
echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>";

// Configuración Railway - Auto-detección
$isRailway = isset($_ENV['RAILWAY_STATIC_URL']) || 
             isset($_ENV['RAILWAY_ENVIRONMENT']) ||
             isset($_ENV['MYSQLHOST']) ||
             strpos($_SERVER['HTTP_HOST'] ?? '', 'railway.app') !== false;

if ($isRailway) {
    // Configuración Railway
    $config = [
        'host' => $_ENV['DB_HOST'] ?? $_ENV['MYSQLHOST'] ?? 'mysql.railway.internal',
        'port' => $_ENV['DB_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306',
        'database' => $_ENV['DB_NAME'] ?? $_ENV['MYSQLDATABASE'] ?? 'railway',
        'username' => $_ENV['DB_USER'] ?? $_ENV['MYSQLUSER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? $_ENV['MYSQLPASSWORD'] ?? ''
    ];
    echo "<div style='background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>🚂 Entorno Railway Detectado</h3>";
    echo "<p>Host: {$config['host']}</p>";
    echo "<p>Puerto: {$config['port']}</p>";
    echo "<p>Base de datos: {$config['database']}</p>";
    echo "</div>";
} else {
    // Configuración local
    $config = [
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'company_presupuestos',
        'username' => 'root',
        'password' => ''
    ];
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>💻 Entorno Local Detectado</h3>";
    echo "</div>";
}

try {
    // Conectar
    echo "<h2>🔗 Conectando a la base de datos...</h2>";
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "<p style='color: green;'>✅ Conectado exitosamente</p>";

    // Crear estructura
    echo "<h2>🔧 Creando estructura de tablas...</h2>";
    
    $pdo->exec("DROP TABLE IF EXISTS opciones");
    $pdo->exec("DROP TABLE IF EXISTS categorias");
    
    $pdo->exec("
        CREATE TABLE categorias (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(255) NOT NULL,
            orden INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    $pdo->exec("
        CREATE TABLE opciones (
            id INT PRIMARY KEY AUTO_INCREMENT,
            categoria_id INT NOT NULL,
            nombre VARCHAR(500) NOT NULL,
            precio_90_dias DECIMAL(10,2) DEFAULT 0,
            precio_160_dias DECIMAL(10,2) DEFAULT 0,
            precio_270_dias DECIMAL(10,2) DEFAULT 0,
            descuento DECIMAL(5,2) DEFAULT 0,
            orden INT DEFAULT 0,
            FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    echo "<p style='color: green;'>✅ Tablas creadas correctamente</p>";

    // Insertar categorías
    echo "<h2>📂 Insertando categorías...</h2>";
    $pdo->exec("INSERT INTO categorias (id, nombre, orden) VALUES (1, 'Ascensores', 1), (2, 'Adicionales', 2), (3, 'Descuentos', 3)");
    echo "<p style='color: green;'>✅ 3 categorías insertadas</p>";

    // Datos reales del cotizador (basados en el archivo SQL original)
    echo "<h2>⚙️ Insertando datos reales del cotizador...</h2>";
    
    $datos_reales = [
        // ASCENSORES (Categoría 1)
        [1, 'Equipo electromecánico 450KG 4 paradas', 1200000, 1100000, 1000000, 0, 1],
        [1, 'Equipo electromecánico 450KG 5 paradas', 1300000, 1200000, 1100000, 0, 2],
        [1, 'Equipo electromecánico 450KG 6 paradas', 1400000, 1300000, 1200000, 0, 3],
        [1, 'Equipo electromecánico 450KG 7 paradas', 1500000, 1400000, 1300000, 0, 4],
        [1, 'Equipo electromecánico 450KG 8 paradas', 1600000, 1500000, 1400000, 0, 5],
        [1, 'Equipo electromecánico 450KG 9 paradas', 1700000, 1600000, 1500000, 0, 6],
        [1, 'Equipo electromecánico 450KG 10 paradas', 1800000, 1700000, 1600000, 0, 7],
        [1, 'Equipo electromecánico 450KG 11 paradas', 1900000, 1800000, 1700000, 0, 8],
        [1, 'Equipo electromecánico 450KG 12 paradas', 2000000, 1900000, 1800000, 0, 9],
        [1, 'Equipo electromecánico 450KG 13 paradas', 2100000, 2000000, 1900000, 0, 10],
        [1, 'Equipo electromecánico 450KG 14 paradas', 2200000, 2100000, 2000000, 0, 11],
        [1, 'Equipo electromecánico 450KG 15 paradas', 2300000, 2200000, 2100000, 0, 12],
        
        // Equipos Gearless
        [1, 'Equipo Gearless 450KG 4 paradas', 1400000, 1300000, 1200000, 0, 13],
        [1, 'Equipo Gearless 450KG 5 paradas', 1500000, 1400000, 1300000, 0, 14],
        [1, 'Equipo Gearless 450KG 6 paradas', 1600000, 1500000, 1400000, 0, 15],
        [1, 'Equipo Gearless 450KG 7 paradas', 1700000, 1600000, 1500000, 0, 16],
        [1, 'Equipo Gearless 450KG 8 paradas', 1800000, 1700000, 1600000, 0, 17],
        [1, 'Equipo Gearless 450KG 9 paradas', 1900000, 1800000, 1700000, 0, 18],
        [1, 'Equipo Gearless 450KG 10 paradas', 2000000, 1900000, 1800000, 0, 19],
        [1, 'Equipo Gearless 450KG 11 paradas', 2100000, 2000000, 1900000, 0, 20],
        [1, 'Equipo Gearless 450KG 12 paradas', 2200000, 2100000, 2000000, 0, 21],
        [1, 'Equipo Gearless 450KG 13 paradas', 2300000, 2200000, 2100000, 0, 22],
        [1, 'Equipo Gearless 450KG 14 paradas', 2400000, 2300000, 2200000, 0, 23],
        [1, 'Equipo Gearless 450KG 15 paradas', 2500000, 2400000, 2300000, 0, 24],
        
        // Equipos Hidráulicos
        [1, 'Equipo hidráulico 13HP 450KG 4 paradas', 1100000, 1000000, 900000, 0, 25],
        [1, 'Equipo hidráulico 13HP 450KG 5 paradas', 1200000, 1100000, 1000000, 0, 26],
        [1, 'Equipo hidráulico 13HP 450KG 6 paradas', 1300000, 1200000, 1100000, 0, 27],
        [1, 'Equipo hidráulico 4HP 300KG 4 paradas', 950000, 850000, 750000, 0, 28],
        [1, 'Equipo hidráulico 4HP 300KG 5 paradas', 1050000, 950000, 850000, 0, 29],
        [1, 'Equipo hidráulico 4HP 300KG 6 paradas', 1150000, 1050000, 950000, 0, 30],
        
        // Equipos Domiciliarios
        [1, 'Equipo domiciliario 300KG 2 paradas', 800000, 750000, 700000, 0, 31],
        [1, 'Equipo domiciliario 300KG 3 paradas', 900000, 850000, 800000, 0, 32],
        [1, 'Equipo domiciliario 300KG 4 paradas', 1000000, 950000, 900000, 0, 33],
        
        // Montavehículos y otros
        [1, 'Montavehículos 3000KG', 2500000, 2400000, 2300000, 0, 34],
        [1, 'Montacargas 1000KG', 1800000, 1700000, 1600000, 0, 35],
        [1, 'Montacargas 2000KG', 2200000, 2100000, 2000000, 0, 36],
        [1, 'Salvaescaleras recto', 800000, 750000, 700000, 0, 37],
        [1, 'Salvaescaleras curvo', 1200000, 1100000, 1000000, 0, 38],
        [1, 'Montaplatos 100KG', 600000, 550000, 500000, 0, 39],
        [1, 'Giracoches 2000KG', 1500000, 1400000, 1300000, 0, 40],
        
        // ADICIONALES (Categoría 2)
        // Máquinas
        [2, 'Máquina 750KG', 300000, 280000, 260000, 0, 1],
        [2, 'Máquina 1000KG', 400000, 380000, 360000, 0, 2],
        
        // Cabinas
        [2, 'Cabina 2.25M³', 250000, 230000, 210000, 0, 3],
        [2, 'Cabina 2.66M³', 300000, 280000, 260000, 0, 4],
        
        // Adicionales Electromecánicos
        [2, 'Adicional Electromecánico - Puertas Automáticas', 150000, 140000, 130000, 0, 5],
        [2, 'Adicional Electromecánico - Sistema de Emergencia', 100000, 90000, 80000, 0, 6],
        [2, 'Adicional Electromecánico - Cabina Inoxidable', 120000, 110000, 100000, 0, 7],
        [2, 'Adicional Electromecánico - Botonera Digital', 80000, 70000, 60000, 0, 8],
        [2, 'Adicional Electromecánico - Iluminación LED', 60000, 50000, 40000, 0, 9],
        
        // Adicionales Hidráulicos
        [2, 'Adicional Hidráulico - Bomba Reforzada', 120000, 110000, 100000, 0, 10],
        [2, 'Adicional Hidráulico - Válvulas Especiales', 80000, 70000, 60000, 0, 11],
        [2, 'Adicional Hidráulico - Central Premium', 150000, 140000, 130000, 0, 12],
        [2, 'Adicional Hidráulico - Cilindro Reforzado', 100000, 90000, 80000, 0, 13],
        
        // Adicionales Montacargas
        [2, 'Adicional Montacargas - Plataforma Reforzada', 200000, 180000, 160000, 0, 14],
        [2, 'Adicional Montacargas - Sistema de Carga', 120000, 110000, 100000, 0, 15],
        
        // Adicionales Salvaescaleras
        [2, 'Adicional Salvaescaleras - Asiento Giratorio', 100000, 90000, 80000, 0, 16],
        
        // Adicionales que RESTAN dinero
        [2, 'CABINA EN CHAPA C/DETALLES RESTAR', -80000, -70000, -60000, 0, 17],
        [2, 'PB Y PUERTA DE CABINA EN CHAPA RESTAR', -60000, -50000, -40000, 0, 18],
        [2, 'BOTONERA BÁSICA RESTAR', -40000, -35000, -30000, 0, 19],
        [2, 'ILUMINACIÓN ESTÁNDAR RESTAR', -30000, -25000, -20000, 0, 20],
        [2, 'ACABADO ECONÓMICO RESTAR', -50000, -45000, -40000, 0, 21],
        [2, 'INSTALACIÓN BÁSICA RESTAR', -40000, -35000, -30000, 0, 22],
        
        // DESCUENTOS (Categoría 3)
        [3, 'Descuento por pago en efectivo', 0, 0, 0, 8, 1],
        [3, 'Descuento por transferencia bancaria', 0, 0, 0, 5, 2],
        [3, 'Descuento por cheques electrónicos', 0, 0, 0, 2, 3],
        [3, 'Mejora de presupuesto', 0, 0, 0, 5, 4]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO opciones (categoria_id, nombre, precio_90_dias, precio_160_dias, precio_270_dias, descuento, orden) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $insertados = 0;
    foreach ($datos_reales as $dato) {
        $stmt->execute($dato);
        $insertados++;
    }
    
    echo "<p style='color: green;'>✅ {$insertados} opciones insertadas correctamente</p>";

    // Verificación final
    echo "<h2>📊 Verificación final:</h2>";
    $categorias = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    $opciones = $pdo->query("SELECT COUNT(*) FROM opciones")->fetchColumn();
    $ascensores = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 1")->fetchColumn();
    $adicionales = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2")->fetchColumn();
    $descuentos = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 3")->fetchColumn();
    
    $electromecanicos = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%electromecanico%'")->fetchColumn();
    $hidraulicos = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%hidraulico%'")->fetchColumn();
    $montacargas = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%montacargas%'")->fetchColumn();
    $salvaescaleras = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%salvaescaleras%'")->fetchColumn();
    $que_restan = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%restar%'")->fetchColumn();
    
    echo "<div style='background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>🎉 ¡COTIZADOR INTELIGENTE CONFIGURADO CON DATOS REALES!</h3>";
    echo "<p>📊 <strong>Estadísticas:</strong></p>";
    echo "<p>• {$categorias} categorías</p>";
    echo "<p>• {$ascensores} ascensores</p>";
    echo "<p>• {$adicionales} adicionales</p>";
    echo "<p>• {$descuentos} opciones de descuento</p>";
    echo "<p>• {$electromecanicos} adicionales electromecánicos</p>";
    echo "<p>• {$hidraulicos} adicionales hidráulicos</p>";
    echo "<p>• {$montacargas} adicionales montacargas</p>";
    echo "<p>• {$salvaescaleras} adicionales salvaescaleras</p>";
    echo "<p>• {$que_restan} adicionales que restan dinero</p>";
    echo "</div>";
    
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>🔧 Funcionalidades activas:</h3>";
    echo "<p>✅ <strong>Filtrado automático:</strong> Los adicionales se filtran según el tipo de ascensor seleccionado</p>";
    echo "<p>✅ <strong>Adicionales que restan:</strong> {$que_restan} opciones configuradas para restar dinero</p>";
    echo "<p>✅ <strong>Plazo unificado:</strong> 3 plazos (90, 160, 270 días) configurados</p>";
    echo "<p>✅ <strong>Interface optimizada:</strong> Lista para usar</p>";
    echo "</div>";
    
    echo "<h2>🚀 ¡Configuración completada!</h2>";
    echo "<p><a href='cotizador.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🎯 Ir al Cotizador</a></p>";
    echo "<p><a href='test_simple.html' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>🧪 Página de Pruebas</a></p>";

} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; color: red;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Posibles soluciones:</strong></p>";
    echo "<p>1. Verifica las credenciales de la base de datos</p>";
    echo "<p>2. Confirma que MySQL esté activo</p>";
    echo "<p>3. Revisa las variables de entorno</p>";
    echo "</div>";
}

echo "</body></html>";
?> 