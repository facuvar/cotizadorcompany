<?php
/**
 * Script de configuración inicial para Modern UI
 * Ejecutar una sola vez para configurar el sistema
 */

// Verificar si se ejecuta desde línea de comandos o navegador
$is_cli = php_sapi_name() === 'cli';

function output($message, $type = 'info') {
    global $is_cli;
    
    $colors = [
        'success' => "\033[32m",
        'error' => "\033[31m",
        'warning' => "\033[33m",
        'info' => "\033[36m"
    ];
    
    if ($is_cli) {
        $color = $colors[$type] ?? '';
        $reset = "\033[0m";
        echo $color . "[" . strtoupper($type) . "] " . $message . $reset . PHP_EOL;
    } else {
        $style = [
            'success' => 'color: #10b981;',
            'error' => 'color: #ef4444;',
            'warning' => 'color: #f59e0b;',
            'info' => 'color: #3b82f6;'
        ];
        echo "<p style='{$style[$type]}'><strong>[" . strtoupper($type) . "]</strong> " . htmlspecialchars($message) . "</p>";
    }
}

// HTML header si no es CLI
if (!$is_cli) {
    echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Modern UI</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #1a1a1a;
            color: #e5e5e5;
            padding: 2rem;
            line-height: 1.6;
        }
        h1 { color: #3b82f6; }
        pre {
            background: #262626;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
        }
        .success { background: rgba(16, 185, 129, 0.1); padding: 1rem; border-radius: 8px; margin: 1rem 0; }
        .error { background: rgba(239, 68, 68, 0.1); padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    </style>
</head>
<body>
    <h1>🚀 Setup Modern UI - Sistema de Cotización</h1>';
}

output("Iniciando configuración del sistema Modern UI...", "info");

// 1. Verificar estructura de directorios
output("Verificando estructura de directorios...", "info");

$required_dirs = [
    'assets',
    'assets/css',
    'assets/js',
    'admin',
    'uploads',
    'sistema',
    'sistema/includes'
];

foreach ($required_dirs as $dir) {
    if (!is_dir($dir)) {
        output("Creando directorio: $dir", "warning");
        if (!mkdir($dir, 0755, true)) {
            output("Error al crear directorio: $dir", "error");
        } else {
            output("Directorio creado: $dir", "success");
        }
    } else {
        output("Directorio existe: $dir", "success");
    }
}

// 2. Verificar archivos críticos
output("\nVerificando archivos críticos...", "info");

$critical_files = [
    'assets/css/modern-dark-theme.css' => 'Sistema de diseño',
    'assets/js/modern-icons.js' => 'Librería de iconos',
    'index_moderno.php' => 'Página principal',
    'cotizador_moderno.php' => 'Cotizador público',
    'admin/index_moderno.php' => 'Dashboard admin',
    'admin/gestionar_datos_moderno.php' => 'Gestión de datos',
    'admin/presupuestos_moderno.php' => 'Lista de presupuestos',
    'admin/ver_presupuesto_moderno.php' => 'Detalle de presupuesto',
    'admin/importar_moderno.php' => 'Importación Excel'
];

$missing_files = [];
foreach ($critical_files as $file => $description) {
    if (file_exists($file)) {
        output("✓ $description: $file", "success");
    } else {
        output("✗ $description: $file", "error");
        $missing_files[] = $file;
    }
}

// 3. Verificar configuración de base de datos
output("\nVerificando configuración de base de datos...", "info");

$config_file = 'sistema/config.php';
if (file_exists($config_file)) {
    output("Archivo de configuración encontrado", "success");
    
    // Intentar incluir y verificar conexión
    try {
        require_once $config_file;
        if (file_exists('sistema/includes/db.php')) {
            require_once 'sistema/includes/db.php';
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            if ($conn) {
                output("Conexión a base de datos exitosa", "success");
                
                // Verificar tablas
                $required_tables = ['categorias', 'opciones', 'presupuestos'];
                foreach ($required_tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($result && $result->num_rows > 0) {
                        output("Tabla '$table' existe", "success");
                    } else {
                        output("Tabla '$table' no encontrada", "error");
                    }
                }
            }
        }
    } catch (Exception $e) {
        output("Error al conectar con la base de datos: " . $e->getMessage(), "error");
    }
} else {
    output("Archivo de configuración no encontrado. Creando plantilla...", "warning");
    
    $config_template = '<?php
// Configuración de la base de datos
define("DB_HOST", "localhost");
define("DB_USER", "tu_usuario");
define("DB_PASS", "tu_contraseña");
define("DB_NAME", "tu_base_de_datos");

// Configuración general
define("SITE_URL", "http://localhost/company-presupuestos-online-2");
define("SITE_NAME", "Sistema de Cotización de Ascensores");

// Zona horaria
date_default_timezone_set("Europe/Madrid");
?>';
    
    if (file_put_contents($config_file, $config_template)) {
        output("Plantilla de configuración creada en: $config_file", "success");
        output("Por favor, edita el archivo con tus credenciales de base de datos", "warning");
    }
}

// 4. Crear archivo .htaccess para URLs amigables (opcional)
output("\nConfigurando .htaccess...", "info");

$htaccess_content = 'RewriteEngine On

# Redirigir a versión moderna por defecto
RewriteRule ^$ index_moderno.php [L]

# Prevenir acceso directo a archivos del sistema
RewriteRule ^sistema/ - [F,L]

# Manejar errores
ErrorDocument 404 /404.html
ErrorDocument 500 /500.html';

if (!file_exists('.htaccess')) {
    if (file_put_contents('.htaccess', $htaccess_content)) {
        output("Archivo .htaccess creado", "success");
    } else {
        output("No se pudo crear .htaccess", "warning");
    }
} else {
    output("Archivo .htaccess ya existe", "info");
}

// 5. Resumen final
output("\n=== RESUMEN DE CONFIGURACIÓN ===", "info");

if (empty($missing_files)) {
    output("✓ Todos los archivos críticos están presentes", "success");
} else {
    output("✗ Faltan " . count($missing_files) . " archivos críticos", "error");
}

output("\n=== PRÓXIMOS PASOS ===", "info");
output("1. Configura la base de datos en sistema/config.php", "warning");
output("2. Importa la estructura de base de datos si es necesario", "warning");
output("3. Accede a index_moderno.php para comenzar", "warning");
output("4. Credenciales admin por defecto: admin / admin123", "warning");

// 6. Crear estructura SQL de ejemplo
output("\n=== ESTRUCTURA SQL DE EJEMPLO ===", "info");

$sql_structure = "
-- Estructura de tablas para el sistema Modern UI

CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `opciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `precio_90_dias` decimal(10,2) DEFAULT 0.00,
  `precio_160_dias` decimal(10,2) DEFAULT 0.00,
  `precio_270_dias` decimal(10,2) DEFAULT 0.00,
  `descuento` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `presupuestos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_cliente` varchar(255) DEFAULT NULL,
  `email_cliente` varchar(255) DEFAULT NULL,
  `telefono_cliente` varchar(50) DEFAULT NULL,
  `opciones_json` text,
  `plazo_entrega` int(11) DEFAULT 90,
  `total` decimal(10,2) DEFAULT 0.00,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos de ejemplo
INSERT INTO `categorias` (`nombre`) VALUES 
('Características Básicas'),
('Características Opcionales'),
('Descuentos');
";

if (!$is_cli) {
    echo '<div class="success"><h3>Estructura SQL de ejemplo:</h3><pre>' . htmlspecialchars($sql_structure) . '</pre></div>';
} else {
    output("Guarda la estructura SQL anterior en un archivo .sql para importar en tu base de datos", "info");
}

// Crear archivo SQL
if (file_put_contents('estructura_db_modern_ui.sql', $sql_structure)) {
    output("Archivo SQL creado: estructura_db_modern_ui.sql", "success");
}

output("\n¡Configuración completada! 🎉", "success");

// HTML footer
if (!$is_cli) {
    echo '</body></html>';
}
?> 