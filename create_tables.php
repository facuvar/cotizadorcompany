<?php
/**
 * Script para crear las tablas necesarias del sistema
 */

echo "<h1>🏗️ CREAR TABLAS DEL SISTEMA</h1>";
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
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    echo "</div>";
    exit;
}

// SQL para crear las tablas
$tables = [
    'plazos_entrega' => "
        CREATE TABLE IF NOT EXISTS plazos_entrega (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            dias INT NOT NULL,
            orden INT DEFAULT 0,
            activo TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'categorias' => "
        CREATE TABLE IF NOT EXISTS categorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            orden INT DEFAULT 0,
            activo TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'opciones' => "
        CREATE TABLE IF NOT EXISTS opciones (
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
            activo TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'presupuestos' => "
        CREATE TABLE IF NOT EXISTS presupuestos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            numero_presupuesto VARCHAR(50) UNIQUE NOT NULL,
            cliente_nombre VARCHAR(255) NOT NULL,
            cliente_email VARCHAR(255),
            cliente_telefono VARCHAR(50),
            cliente_empresa VARCHAR(255),
            plazo_entrega_id INT,
            subtotal DECIMAL(10,2) DEFAULT 0.00,
            descuento_porcentaje DECIMAL(5,2) DEFAULT 0.00,
            descuento_monto DECIMAL(10,2) DEFAULT 0.00,
            total DECIMAL(10,2) DEFAULT 0.00,
            observaciones TEXT,
            estado ENUM('borrador', 'enviado', 'aprobado', 'rechazado') DEFAULT 'borrador',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (plazo_entrega_id) REFERENCES plazos_entrega(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'presupuesto_items' => "
        CREATE TABLE IF NOT EXISTS presupuesto_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            presupuesto_id INT NOT NULL,
            opcion_id INT NOT NULL,
            cantidad INT DEFAULT 1,
            precio_unitario DECIMAL(10,2) NOT NULL,
            precio_total DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (presupuesto_id) REFERENCES presupuestos(id) ON DELETE CASCADE,
            FOREIGN KEY (opcion_id) REFERENCES opciones(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'configuracion' => "
        CREATE TABLE IF NOT EXISTS configuracion (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL UNIQUE,
            valor TEXT,
            descripcion TEXT,
            tipo ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'usuarios' => "
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            nombre VARCHAR(255),
            rol ENUM('admin', 'usuario') DEFAULT 'usuario',
            activo TINYINT(1) DEFAULT 1,
            ultimo_acceso TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];

// Crear las tablas
echo "<h2>🏗️ Creando tablas</h2>";

foreach ($tables as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        echo "<div class='success'>✅ Tabla '$tableName' creada correctamente</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error creando tabla '$tableName': " . $e->getMessage() . "</div>";
    }
}

// Insertar datos básicos
echo "<h2>📊 Insertando datos básicos</h2>";

// Plazos de entrega
try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO plazos_entrega (nombre, dias, orden) VALUES (?, ?, ?)");
    $plazos = [
        ['90 dias', 90, 1],
        ['160-180 dias', 170, 2],
        ['270 dias', 270, 3]
    ];
    
    foreach ($plazos as $plazo) {
        $stmt->execute($plazo);
    }
    echo "<div class='success'>✅ Plazos de entrega insertados</div>";
} catch (PDOException $e) {
    echo "<div class='warning'>⚠️ Error insertando plazos: " . $e->getMessage() . "</div>";
}

// Categorías básicas
try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO categorias (nombre, descripcion, orden) VALUES (?, ?, ?)");
    $categorias = [
        ['ASCENSORES', 'Equipos electromecánicos de ascensores', 1],
        ['ADICIONALES', 'Opciones adicionales para ascensores', 2],
        ['DESCUENTOS', 'Formas de pago y descuentos', 3]
    ];
    
    foreach ($categorias as $categoria) {
        $stmt->execute($categoria);
    }
    echo "<div class='success'>✅ Categorías básicas insertadas</div>";
} catch (PDOException $e) {
    echo "<div class='warning'>⚠️ Error insertando categorías: " . $e->getMessage() . "</div>";
}

// Configuración básica
try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO configuracion (nombre, valor, descripcion, tipo) VALUES (?, ?, ?, ?)");
    $configs = [
        ['titulo_sistema', 'Sistema de Presupuestos de Ascensores', 'Título del sistema', 'text'],
        ['empresa_nombre', 'Tu Empresa', 'Nombre de la empresa', 'text'],
        ['empresa_telefono', '+54 11 1234-5678', 'Teléfono de la empresa', 'text'],
        ['empresa_email', 'info@tuempresa.com', 'Email de la empresa', 'text'],
        ['empresa_direccion', 'Tu Dirección, Ciudad', 'Dirección de la empresa', 'text'],
        ['moneda', 'ARS', 'Moneda del sistema', 'text'],
        ['iva_porcentaje', '21', 'Porcentaje de IVA', 'number'],
        ['validez_presupuesto', '30', 'Días de validez del presupuesto', 'number']
    ];
    
    foreach ($configs as $config) {
        $stmt->execute($config);
    }
    echo "<div class='success'>✅ Configuración básica insertada</div>";
} catch (PDOException $e) {
    echo "<div class='warning'>⚠️ Error insertando configuración: " . $e->getMessage() . "</div>";
}

// Usuario administrador
try {
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO usuarios (username, password, email, nombre, rol) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', $adminPassword, 'admin@tuempresa.com', 'Administrador', 'admin']);
    echo "<div class='success'>✅ Usuario administrador creado (admin/admin123)</div>";
} catch (PDOException $e) {
    echo "<div class='warning'>⚠️ Error creando usuario admin: " . $e->getMessage() . "</div>";
}

// Verificar resultados
echo "<h2>📊 Verificación final</h2>";

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='info'>✅ Tablas creadas: " . count($tables) . "</div>";
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $table");
        $count = $stmt->fetch()['total'];
        echo "<div class='info'>• $table: $count registros</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error verificando: " . $e->getMessage() . "</div>";
}

echo "<h2>🎉 ¡Tablas creadas exitosamente!</h2>";
echo "<div class='success'>Ahora puedes importar los datos del Excel usando el script de importación.</div>";

echo "</div>";
?> 