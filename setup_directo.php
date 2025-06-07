<?php
/**
 * SETUP DIRECTO PARA RAILWAY
 * Script completo que no depende de archivos externos
 * Copia y pega este código completo en Railway
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Setup Railway</title></head><body>";
echo "<h1>🚀 Setup Directo Railway</h1>";
echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>";

// Configuración Railway
$config = [
    'host' => $_ENV['DB_HOST'] ?? 'autorack.proxy.rlwy.net',
    'port' => $_ENV['DB_PORT'] ?? '47470',
    'database' => $_ENV['DB_NAME'] ?? 'railway',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? 'LjEWJGgCJHdBcgfGAhGjfEBEhfJjGGjA'
];

try {
    // Conectar
    echo "<h2>🔗 Conectando a Railway...</h2>";
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<p>✅ Conectado exitosamente</p>";

    // Crear estructura
    echo "<h2>🔧 Creando estructura...</h2>";
    
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
            orden INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    echo "<p>✅ Tablas creadas</p>";

    // Insertar categorías
    echo "<h2>📂 Insertando categorías...</h2>";
    $pdo->exec("INSERT INTO categorias (id, nombre, orden) VALUES (1, 'Ascensores', 1), (2, 'Adicionales', 2), (3, 'Otros', 3)");
    echo "<p>✅ 3 categorías insertadas</p>";

    // Insertar datos de ejemplo del cotizador inteligente
    echo "<h2>⚙️ Insertando datos del cotizador...</h2>";
    
    $datos = [
        // Ascensores
        [1, 'Ascensor Electromecánico 4 Personas 300kg', 850000, 750000, 650000, 0, 1],
        [1, 'Ascensor Electromecánico 6 Personas 450kg', 950000, 850000, 750000, 0, 2],
        [1, 'Ascensor Gearless 4 Personas 300kg', 1100000, 1000000, 900000, 0, 3],
        [1, 'Ascensor Gearless 6 Personas 450kg', 1200000, 1100000, 950000, 0, 4],
        [1, 'Ascensor Hidráulico 4 Personas 300kg', 750000, 650000, 550000, 0, 5],
        [1, 'Ascensor Hidráulico 6 Personas 450kg', 850000, 750000, 650000, 0, 6],
        [1, 'Montacargas 500kg', 950000, 850000, 750000, 0, 7],
        [1, 'Montacargas 1000kg', 1150000, 1050000, 950000, 0, 8],
        [1, 'Salvaescaleras Recto', 450000, 400000, 350000, 0, 9],
        [1, 'Salvaescaleras Curvo', 650000, 600000, 550000, 0, 10],
        
        // Adicionales Electromecánicos
        [2, 'Adicional Electromecánico - Puertas Automáticas', 120000, 110000, 95000, 0, 1],
        [2, 'Adicional Electromecánico - Sistema de Emergencia', 85000, 75000, 65000, 0, 2],
        [2, 'Adicional Electromecánico - Cabina Inoxidable', 95000, 85000, 75000, 0, 3],
        [2, 'Adicional Electromecánico - Botonera Digital', 65000, 55000, 45000, 0, 4],
        [2, 'Adicional Electromecánico - Iluminación LED', 45000, 40000, 35000, 0, 5],
        
        // Adicionales Hidráulicos
        [2, 'Adicional Hidráulico - Bomba Reforzada', 95000, 85000, 75000, 0, 6],
        [2, 'Adicional Hidráulico - Válvulas Especiales', 65000, 55000, 45000, 0, 7],
        [2, 'Adicional Hidráulico - Central Hidráulica Premium', 125000, 115000, 105000, 0, 8],
        [2, 'Adicional Hidráulico - Cilindro Reforzado', 85000, 75000, 65000, 0, 9],
        
        // Adicionales Montacargas
        [2, 'Adicional Montacargas - Plataforma Reforzada', 150000, 135000, 120000, 0, 10],
        [2, 'Adicional Montacargas - Sistema de Carga', 95000, 85000, 75000, 0, 11],
        
        // Adicionales Salvaescaleras
        [2, 'Adicional Salvaescaleras - Asiento Giratorio', 75000, 65000, 55000, 0, 12],
        
        // Adicionales que RESTAN dinero
        [2, 'CABINA EN CHAPA C/DETALLES RESTAR', -50000, -45000, -40000, 0, 13],
        [2, 'PB Y PUERTA DE CABINA EN CHAPA RESTAR', -35000, -30000, -25000, 0, 14],
        [2, 'BOTONERA BÁSICA RESTAR', -25000, -20000, -15000, 0, 15],
        [2, 'ILUMINACIÓN ESTÁNDAR RESTAR', -15000, -12000, -10000, 0, 16],
        [2, 'ACABADO ECONÓMICO RESTAR', -40000, -35000, -30000, 0, 17],
        [2, 'INSTALACIÓN BÁSICA RESTAR', -30000, -25000, -20000, 0, 18]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO opciones (categoria_id, nombre, precio_90_dias, precio_160_dias, precio_270_dias, descuento, orden) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($datos as $dato) {
        $stmt->execute($dato);
    }
    
    echo "<p>✅ " . count($datos) . " opciones insertadas</p>";

    // Verificar datos
    echo "<h2>📊 Verificación final:</h2>";
    $categorias = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    $opciones = $pdo->query("SELECT COUNT(*) FROM opciones")->fetchColumn();
    $ascensores = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 1")->fetchColumn();
    $adicionales = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2")->fetchColumn();
    
    $electromecanicos = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%electromecanico%'")->fetchColumn();
    $hidraulicos = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%hidraulico%'")->fetchColumn();
    $montacargas = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%montacargas%'")->fetchColumn();
    $salvaescaleras = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%salvaescaleras%'")->fetchColumn();
    $que_restan = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%restar%'")->fetchColumn();
    
    echo "<div style='background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>🎉 ¡COTIZADOR INTELIGENTE CONFIGURADO!</h3>";
    echo "<p>📊 <strong>Estadísticas:</strong></p>";
    echo "<p>• {$categorias} categorías</p>";
    echo "<p>• {$ascensores} ascensores</p>";
    echo "<p>• {$adicionales} adicionales</p>";
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
    
    echo "<h2>🚀 ¡Listo para usar!</h2>";
    echo "<p><a href='cotizador.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🎯 Ir al Cotizador</a></p>";
    echo "<p><a href='test_simple.html' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>🧪 Página de Pruebas</a></p>";

} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; color: red;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Posibles soluciones:</strong></p>";
    echo "<p>1. Verifica las credenciales de Railway</p>";
    echo "<p>2. Confirma que MySQL esté activo</p>";
    echo "<p>3. Revisa las variables de entorno</p>";
    echo "</div>";
}

echo "</body></html>";
?> 