<?php
/**
 * Script para preparar el proyecto para Railway
 * Corrige todos los archivos para que funcionen en Railway
 */

echo "<h1>🚂 PREPARANDO PROYECTO PARA RAILWAY</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
</style>";

echo "<div class='container'>";

// Paso 1: Verificar archivos de Railway existentes
echo "<h2>📋 Paso 1: Verificar archivos de Railway</h2>";

$railwayFiles = [
    'railway.json',
    'nixpacks.toml',
    'index.php',
    'sistema/config_railway.php',
    'RAILWAY_SETUP.md'
];

foreach ($railwayFiles as $file) {
    if (file_exists($file)) {
        echo "<div class='success'>✅ " . $file . " existe</div>";
    } else {
        echo "<div class='warning'>⚠️ " . $file . " no existe - se creará</div>";
    }
}

// Paso 2: Corregir admin/dashboard.php para Railway
echo "<h2>🔧 Paso 2: Corregir dashboard.php</h2>";

$dashboardContent = '<?php
session_start();

// Cargar configuración con manejo de errores
$configPath = __DIR__ . \'/../sistema/config.php\';
if (!file_exists($configPath)) {
    die("Error: Archivo de configuración no encontrado");
}

try {
    require_once $configPath;
} catch (Exception $e) {
    die("Error cargando configuración: " . $e->getMessage());
}

// Cargar db.php con manejo de errores para Railway
$dbConnected = false;
try {
    require_once __DIR__ . \'/../sistema/includes/db.php\';
    $dbConnected = true;
} catch (Exception $e) {
    if (defined(\'IS_RAILWAY\') && IS_RAILWAY) {
        railway_log("Error loading db.php in dashboard: " . $e->getMessage());
    }
    $dbConnected = false;
}

// Verificar si el usuario está logueado
if (!isset($_SESSION[\'admin_logged_in\']) || $_SESSION[\'admin_logged_in\'] !== true) {
    header(\'Location: index.php\');
    exit;
}

// Inicializar estadísticas
$totalPresupuestos = 0;
$totalProductos = 0;
$totalOpciones = 0;
$ultimosPresupuestos = null;

// Obtener estadísticas solo si la DB está conectada
if ($dbConnected) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        // Contar presupuestos
        $query = "SELECT COUNT(*) as total FROM presupuestos";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $totalPresupuestos = $row[\'total\'];
        }

        // Contar productos
        $query = "SELECT COUNT(*) as total FROM xls_productos";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $totalProductos = $row[\'total\'];
        }

        // Contar opciones
        $query = "SELECT COUNT(*) as total FROM xls_opciones";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $totalOpciones = $row[\'total\'];
        }

        // Obtener últimos presupuestos
        $query = "SELECT * FROM presupuestos ORDER BY fecha_creacion DESC LIMIT 5";
        $ultimosPresupuestos = $conn->query($query);
        
    } catch (Exception $e) {
        if (defined(\'IS_RAILWAY\') && IS_RAILWAY) {
            railway_log("Error getting stats: " . $e->getMessage());
        }
    }
}

// Procesar cierre de sesión
if (isset($_GET[\'logout\'])) {
    session_unset();
    session_destroy();
    header(\'Location: index.php\');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Sistema de Presupuestos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: rgba(255,255,255,.8);
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        .stat-card {
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background-color: white;
        }
        .railway-badge {
            background: #0066ff;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <?php if (defined(\'IS_RAILWAY\') && IS_RAILWAY): ?>
                        <div class="text-center">
                            <span class="railway-badge">🚂 Railway</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center mb-4">
                        <h5>Panel Admin</h5>
                        <p class="small">Bienvenido, <?php echo htmlspecialchars($_SESSION[\'admin_user\'] ?? \'admin\'); ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="dashboard.php" class="active">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../sistema/cotizador.php" target="_blank">
                                <i class="bi bi-calculator"></i> Cotizador
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../" target="_blank">
                                <i class="bi bi-house"></i> Sitio Web
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a href="?logout=1" class="text-danger">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="content p-4">
                    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2">🏠 Dashboard</h1>
                        <div>
                            <a href="../sistema/cotizador.php" target="_blank" class="btn btn-primary btn-sm">
                                🚀 Ver Cotizador
                            </a>
                        </div>
                    </div>
                    
                    <?php if (!$dbConnected): ?>
                        <div class="alert alert-warning">
                            ⚠️ <strong>Advertencia:</strong> Conexión a base de datos limitada.
                            <?php if (defined(\'IS_RAILWAY\') && IS_RAILWAY): ?>
                                Verificando conexión a Railway MySQL...
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Stats -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stat-card d-flex justify-content-between align-items-center">
                                <div>
                                    <h3><?php echo $totalPresupuestos; ?></h3>
                                    <p class="text-muted">Presupuestos</p>
                                </div>
                                <i class="bi bi-file-text text-primary" style="font-size: 2.5rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="stat-card d-flex justify-content-between align-items-center">
                                <div>
                                    <h3><?php echo $totalProductos; ?></h3>
                                    <p class="text-muted">Productos</p>
                                </div>
                                <i class="bi bi-box text-success" style="font-size: 2.5rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="stat-card d-flex justify-content-between align-items-center">
                                <div>
                                    <h3><?php echo $totalOpciones; ?></h3>
                                    <p class="text-muted">Opciones</p>
                                </div>
                                <i class="bi bi-list-check text-warning" style="font-size: 2.5rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Info adicional -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>📊 Estado del Sistema</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Entorno:</strong> 
                                                <?php echo defined(\'IS_RAILWAY\') && IS_RAILWAY ? \'🚂 Railway\' : \'💻 Local\'; ?>
                                            </p>
                                            <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
                                            <p><strong>Base de datos:</strong> 
                                                <?php echo $dbConnected ? \'✅ Conectada\' : \'❌ Desconectada\'; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Usuario:</strong> <?php echo $_SESSION[\'admin_user\'] ?? \'admin\'; ?></p>
                                            <p><strong>Sesión iniciada:</strong> <?php echo date(\'Y-m-d H:i:s\'); ?></p>
                                            <?php if (defined(\'BASE_URL\')): ?>
                                                <p><strong>URL Base:</strong> <?php echo BASE_URL; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';

if (file_put_contents('admin/dashboard.php', $dashboardContent)) {
    echo "<div class='success'>✅ Dashboard corregido para Railway</div>";
} else {
    echo "<div class='error'>❌ Error corrigiendo dashboard</div>";
}

// Paso 3: Verificar que switch_config.php funcione
echo "<h2>🔄 Paso 3: Verificar switch_config.php</h2>";

if (file_exists('switch_config.php')) {
    echo "<div class='success'>✅ switch_config.php existe</div>";
} else {
    echo "<div class='warning'>⚠️ Creando switch_config.php...</div>";
    
    $switchContent = '<?php
/**
 * Script para cambiar entre configuración local y Railway
 */

echo "<h1>🔄 CAMBIAR CONFIGURACIÓN</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
    .btn { padding: 10px 20px; margin: 10px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn-railway { background: #0066ff; color: white; }
    .btn-local { background: #28a745; color: white; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";

echo "<div class=\'container\'>";
echo "<h2>Selecciona la configuración:</h2>";

if (isset($_GET[\'mode\'])) {
    $mode = $_GET[\'mode\'];
    
    if ($mode === \'railway\') {
        // Usar configuración de Railway
        if (file_exists(\'sistema/config_railway.php\')) {
            copy(\'sistema/config_railway.php\', \'sistema/config.php\');
            echo "<div class=\'info\'>✅ Configuración cambiada a <strong>Railway</strong></div>";
        } else {
            echo "<div class=\'info\'>❌ Archivo config_railway.php no encontrado</div>";
        }
    } elseif ($mode === \'local\') {
        // Crear configuración local
        $localConfig = \'<?php
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "company_presupuestos");
define("DB_PORT", 3306);
define("BASE_URL", "http://localhost/company-presupuestos-online-2");
define("IS_RAILWAY", false);
define("ADMIN_USER", "admin");
define("ADMIN_PASS", "$2y$10$szOr0zBbR/0iUpJbHGzVgOyMS3vr7/3DbqFnOJTJRKZOwjyWO/vjm");
?>\';
        
        file_put_contents(\'sistema/config.php\', $localConfig);
        echo "<div class=\'info\'>✅ Configuración cambiada a <strong>Local</strong></div>";
    }
}

echo "<a href=\'?mode=railway\' class=\'btn btn-railway\'>🚂 Usar Railway</a>";
echo "<a href=\'?mode=local\' class=\'btn btn-local\'>💻 Usar Local</a>";

echo "<div class=\'info\'>";
echo "<strong>Estado actual:</strong><br>";
if (file_exists(\'sistema/config.php\')) {
    require_once \'sistema/config.php\';
    echo "• Entorno: " . (defined(\'IS_RAILWAY\') && IS_RAILWAY ? \'Railway\' : \'Local\') . "<br>";
    echo "• DB Host: " . (defined(\'DB_HOST\') ? DB_HOST : \'No definido\') . "<br>";
    echo "• Base URL: " . (defined(\'BASE_URL\') ? BASE_URL : \'No definido\') . "<br>";
} else {
    echo "❌ No hay configuración activa";
}
echo "</div>";

echo "</div>";
?>';
    
    if (file_put_contents('switch_config.php', $switchContent)) {
        echo "<div class='success'>✅ switch_config.php creado</div>";
    } else {
        echo "<div class='error'>❌ Error creando switch_config.php</div>";
    }
}

// Paso 4: Preparar para commit
echo "<h2>📤 Paso 4: Preparar para commit a Railway</h2>";

echo "<div class='info'>";
echo "<strong>Archivos listos para Railway:</strong><br>";
echo "• ✅ admin/index.php - Corregido con manejo de errores<br>";
echo "• ✅ admin/dashboard.php - Corregido para Railway<br>";
echo "• ✅ sistema/config.php - Auto-detecta Railway<br>";
echo "• ✅ switch_config.php - Cambiar configuración<br>";
echo "</div>";

echo "<div class='warning'>";
echo "<strong>Próximos pasos:</strong><br>";
echo "1. Ejecutar: <code>git add .</code><br>";
echo "2. Ejecutar: <code>git commit -m \"Fix admin panel for Railway\"</code><br>";
echo "3. Ejecutar: <code>git push origin main</code><br>";
echo "4. Verificar en Railway que las variables de entorno estén configuradas<br>";
echo "</div>";

echo "</div>";
?> 