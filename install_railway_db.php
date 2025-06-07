<?php
/**
 * Script de Instalación Automática para Railway
 * Configuración específica con tus credenciales
 */

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Instalación Railway DB</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; border-radius: 5px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .progress { background: #e9ecef; border-radius: 5px; overflow: hidden; margin: 10px 0; height: 30px; }
        .progress-bar { background: #28a745; color: white; text-align: center; line-height: 30px; transition: width 0.3s; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🚀 Instalación Automática - Railway DB</h1>";

// Credenciales específicas de tu Railway
$host = 'mysql.railway.internal';
$user = 'root';
$pass = 'dmlTgjGinHTObFPvTZZGrfbxXopMCAmv';
$name = 'railway';
$port = 3306;

echo "<div class='info'>📡 Conectando a tu base de datos Railway...</div>";
echo "<div class='info'>🏠 Host: {$host}:{$port}</div>";
echo "<div class='info'>🗄️ Base de datos: {$name}</div>";

try {
    // Conectar a la base de datos
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", 
        $user, 
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "<div class='success'>✅ Conexión exitosa a Railway MySQL</div>";
    
    // Progreso
    $totalSteps = 6;
    $currentStep = 0;
    
    function showProgress($step, $total, $message) {
        $percent = ($step / $total) * 100;
        echo "<div class='progress'>";
        echo "<div class='progress-bar' style='width: {$percent}%'>{$message} ({$step}/{$total})</div>";
        echo "</div>";
    }
    
    // Paso 1: Crear tabla categorias
    $currentStep++;
    showProgress($currentStep, $totalSteps, "Creando tabla categorías");
    
    $sql1 = "CREATE TABLE IF NOT EXISTS categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        orden INT DEFAULT 0,
        activo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql1);
    echo "<div class='success'>✅ Tabla 'categorias' creada correctamente</div>";
    
    // Paso 2: Crear tabla opciones
    $currentStep++;
    showProgress($currentStep, $totalSteps, "Creando tabla opciones");
    
    $sql2 = "CREATE TABLE IF NOT EXISTS opciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        categoria_id INT NOT NULL,
        nombre VARCHAR(255) NOT NULL,
        descripcion TEXT,
        precio DECIMAL(10,2) DEFAULT 0.00,
        precio_90_dias DECIMAL(10,2) DEFAULT 0.00,
        precio_160_dias DECIMAL(10,2) DEFAULT 0.00,
        precio_270_dias DECIMAL(10,2) DEFAULT 0.00,
        descuento DECIMAL(5,2) DEFAULT 0.00,
        orden INT DEFAULT 0,
        es_titulo TINYINT(1) DEFAULT 0,
        activo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql2);
    echo "<div class='success'>✅ Tabla 'opciones' creada correctamente</div>";
    
    // Paso 3: Crear tabla configuracion
    $currentStep++;
    showProgress($currentStep, $totalSteps, "Creando tabla configuración");
    
    $sql3 = "CREATE TABLE IF NOT EXISTS configuracion (
        id INT AUTO_INCREMENT PRIMARY KEY,
        clave VARCHAR(100) NOT NULL UNIQUE,
        valor TEXT,
        descripcion TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql3);
    echo "<div class='success'>✅ Tabla 'configuracion' creada correctamente</div>";
    
    // Paso 4: Insertar categorías
    $currentStep++;
    showProgress($currentStep, $totalSteps, "Insertando categorías");
    
    $sql4 = "INSERT IGNORE INTO categorias (nombre, descripcion, orden, activo) VALUES
        ('ASCENSORES', 'Equipos de ascensores electromecánicos', 1, 1),
        ('ADICIONALES', 'Opciones adicionales para ascensores', 2, 1),
        ('DESCUENTOS', 'Formas de pago y descuentos', 3, 1)";
    
    $pdo->exec($sql4);
    echo "<div class='success'>✅ Categorías insertadas correctamente</div>";
    
    // Paso 5: Insertar opciones de ejemplo
    $currentStep++;
    showProgress($currentStep, $totalSteps, "Insertando opciones de ejemplo");
    
    $sql5 = "INSERT IGNORE INTO opciones (categoria_id, nombre, precio, precio_90_dias, precio_160_dias, precio_270_dias, orden, activo) VALUES
        (1, 'Ascensor Electromecánico 4 Paradas', 25000.00, 27500.00, 30000.00, 32500.00, 1, 1),
        (1, 'Ascensor Electromecánico 6 Paradas', 35000.00, 38500.00, 42000.00, 45500.00, 2, 1),
        (1, 'Ascensor Electromecánico 8 Paradas', 45000.00, 49500.00, 54000.00, 58500.00, 3, 1),
        (2, 'Puertas Automáticas', 3500.00, 3850.00, 4200.00, 4550.00, 1, 1),
        (2, 'Sistema de Emergencia', 2500.00, 2750.00, 3000.00, 3250.00, 2, 1),
        (2, 'Espejo en Cabina', 800.00, 880.00, 960.00, 1040.00, 3, 1),
        (2, 'RESTAR - Descuento por Instalación Propia', -2000.00, -2200.00, -2400.00, -2600.00, 4, 1),
        (2, 'RESTAR - Sin Garantía Extendida', -1500.00, -1650.00, -1800.00, -1950.00, 5, 1),
        (3, 'Descuento 5% - Pago Contado', 0.00, 0.00, 0.00, 0.00, 1, 1),
        (3, 'Descuento 10% - Pago Anticipado', 0.00, 0.00, 0.00, 0.00, 2, 1)";
    
    $pdo->exec($sql5);
    echo "<div class='success'>✅ Opciones de ejemplo insertadas correctamente</div>";
    
    // Paso 6: Configuración básica
    $currentStep++;
    showProgress($currentStep, $totalSteps, "Configuración final");
    
    $sql6 = "INSERT IGNORE INTO configuracion (clave, valor, descripcion) VALUES
        ('empresa_nombre', 'Tu Empresa de Ascensores', 'Nombre de la empresa'),
        ('empresa_telefono', '+54 11 1234-5678', 'Teléfono de contacto'),
        ('empresa_email', 'info@tuempresa.com', 'Email de contacto'),
        ('moneda_simbolo', '$', 'Símbolo de la moneda'),
        ('iva_porcentaje', '21', 'Porcentaje de IVA'),
        ('descuento_maximo', '15', 'Descuento máximo permitido'),
        ('filtrado_inteligente', '1', 'Activar filtrado inteligente de adicionales'),
        ('plazo_unificado', '1', 'Usar plazo unificado para toda la cotización')";
    
    $pdo->exec($sql6);
    echo "<div class='success'>✅ Configuración básica completada</div>";
    
    // Verificar instalación
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM opciones");
    $result = $stmt->fetch();
    $totalOpciones = $result['total'];
    
    echo "<div class='step'>";
    echo "<h2>🎉 ¡Instalación Completada!</h2>";
    echo "<div class='success'>✅ Base de datos configurada correctamente</div>";
    echo "<div class='info'>📊 Total de opciones instaladas: {$totalOpciones}</div>";
    echo "<div class='info'>🚀 Sistema listo para usar</div>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>🔗 Enlaces Útiles:</h3>";
    echo "<p><a href='cotizador.php' class='btn'>🧮 Ir al Cotizador</a></p>";
    echo "<p><a href='admin/' class='btn'>⚙️ Panel de Administración</a></p>";
    echo "<p><a href='sistema/test_db.php' class='btn'>🔍 Probar Conexión</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error durante la instalación: " . $e->getMessage() . "</div>";
    echo "<div class='warning'>🔧 Verifica que las credenciales de Railway sean correctas</div>";
    echo "<div class='info'>💡 Credenciales utilizadas:</div>";
    echo "<div class='info'>Host: {$host}:{$port}</div>";
    echo "<div class='info'>Usuario: {$user}</div>";
    echo "<div class='info'>Base de datos: {$name}</div>";
}

echo "    </div>
</body>
</html>";
?> 