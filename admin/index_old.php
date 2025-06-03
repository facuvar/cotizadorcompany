<?php
// Iniciar sesión antes de cualquier output
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Manejar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Verificar si existe el archivo de configuración
$configPath = __DIR__ . '/../sistema/config.php';
if (!file_exists($configPath)) {
    // Mostrar página de error con instrucciones
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Configuración Requerida</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                margin: 0;
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 15px 35px rgba(0,0,0,0.1);
                width: 100%;
                max-width: 600px;
            }
            .error {
                background: #ffebee;
                color: #c62828;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
                text-align: center;
            }
            .info {
                background: #e3f2fd;
                color: #1565c0;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .btn {
                display: inline-block;
                padding: 12px 20px;
                background: #667eea;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin: 5px;
                transition: background 0.3s;
            }
            .btn:hover {
                background: #5a6fd8;
            }
            .btn-success {
                background: #4caf50;
            }
            .btn-success:hover {
                background: #45a049;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>⚠️ Configuración Requerida</h1>
            
            <div class="error">
                <strong>Error:</strong> El archivo de configuración no existe.<br>
                Archivo faltante: <code>sistema/config.php</code>
            </div>
            
            <div class="info">
                <h3>🔧 Solución:</h3>
                <p>Para configurar el sistema automáticamente, haz clic en el botón de abajo:</p>
                <a href="../create_config.php" class="btn btn-success">🚀 Crear Configuración Automáticamente</a>
            </div>
            
            <div class="info">
                <h3>📋 Pasos alternativos:</h3>
                <ol>
                    <li>Ejecuta <a href="../create_config.php" target="_blank">create_config.php</a></li>
                    <li>Luego ejecuta <a href="../verify_config.php" target="_blank">verify_config.php</a> para verificar</li>
                    <li>Finalmente regresa a <a href="./">este panel admin</a></li>
                </ol>
            </div>
            
            <div class="info">
                <h3>🔍 Enlaces de diagnóstico:</h3>
                <a href="../diagnose_blank_pages.php" class="btn">🔍 Diagnóstico Completo</a>
                <a href="test.php" class="btn">🧪 Admin de Prueba</a>
                <a href="../verify_config.php" class="btn">✅ Verificar Config</a>
            </div>
            
            <div class="info">
                <strong>Información técnica:</strong><br>
                • Directorio actual: <?php echo getcwd(); ?><br>
                • Archivo buscado: <?php echo realpath($configPath) ?: $configPath; ?><br>
                • PHP Version: <?php echo PHP_VERSION; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Cargar configuración
try {
    require_once $configPath;
} catch (Exception $e) {
    die("Error cargando configuración: " . $e->getMessage());
}

// Intentar cargar db.php con manejo de errores para Railway
$dbPath = __DIR__ . '/../sistema/includes/db.php';
$dbConnected = false;

try {
    if (file_exists($dbPath)) {
        // En Railway, capturar cualquier output o error de db.php
        ob_start();
        require_once $dbPath;
        $dbOutput = ob_get_clean();
        
        if (!empty($dbOutput) && !defined('IS_RAILWAY')) {
            error_log("DB.php output: " . $dbOutput);
        }
        
        $dbConnected = true;
    }
} catch (Exception $e) {
    if (defined('IS_RAILWAY') && IS_RAILWAY) {
        railway_log("Error loading db.php: " . $e->getMessage());
    } else {
        error_log("Error loading db.php: " . $e->getMessage());
    }
    $dbConnected = false;
} catch (Error $e) {
    if (defined('IS_RAILWAY') && IS_RAILWAY) {
        railway_log("Fatal error in db.php: " . $e->getMessage());
    } else {
        error_log("Fatal error in db.php: " . $e->getMessage());
    }
    $dbConnected = false;
}

// Verificar si el usuario ya está logueado - SI ESTÁ LOGUEADO, MOSTRAR DASHBOARD
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Procesar login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isLoggedIn) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Verificar credenciales
    if (defined('ADMIN_USER') && defined('ADMIN_PASS')) {
        if ($username === ADMIN_USER && password_verify($password, ADMIN_PASS)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $username;
            $isLoggedIn = true;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    } else {
        $error = 'Credenciales de administrador no configuradas';
    }
}

// Si está logueado, mostrar el dashboard
if ($isLoggedIn) {
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
                $totalPresupuestos = $row['total'];
            }

            // Contar opciones
            $query = "SELECT COUNT(*) as total FROM opciones";
            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $totalOpciones = $row['total'];
            }

            // Obtener últimos presupuestos
            $query = "SELECT * FROM presupuestos ORDER BY fecha_creacion DESC LIMIT 5";
            $ultimosPresupuestos = $conn->query($query);

        } catch (Exception $e) {
            if (defined('IS_RAILWAY') && IS_RAILWAY) {
                railway_log("Error getting stats: " . $e->getMessage());
            }
        }
    }
    
    // MOSTRAR DASHBOARD
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
                        <?php if (defined('IS_RAILWAY') && IS_RAILWAY): ?>
                            <div class="text-center">
                                <span class="railway-badge">🚂 Railway</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-4">
                            <h5>Panel Admin</h5>
                            <p class="small">Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_user'] ?? 'admin'); ?></p>
                        </div>
                        
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="index.php" class="active">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="gestionar_datos.php">
                                    <i class="bi bi-database-gear"></i> Gestionar Datos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="presupuestos.php">
                                    <i class="bi bi-file-earmark-text"></i> Presupuestos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="../cotizador.php" target="_blank">
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
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">📊 Dashboard</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <a href="../cotizador.php" class="btn btn-sm btn-outline-secondary" target="_blank">
                                    <i class="bi bi-calculator"></i> Ir al Cotizador
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">📋 Presupuestos</h5>
                                        <h2 class="text-primary"><?php echo $totalPresupuestos; ?></h2>
                                        <p class="text-muted">Total generados</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-file-earmark-text" style="font-size: 2rem; color: #007bff;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">🏗️ Modelos</h5>
                                        <h2 class="text-success"><?php echo $totalOpciones; ?></h2>
                                        <p class="text-muted">Opciones disponibles</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-building" style="font-size: 2rem; color: #28a745;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">🔗 Estado BD</h5>
                                        <h2 class="<?php echo $dbConnected ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo $dbConnected ? '✅' : '❌'; ?>
                                        </h2>
                                        <p class="text-muted"><?php echo $dbConnected ? 'Conectada' : 'Desconectada'; ?></p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-database" style="font-size: 2rem; color: <?php echo $dbConnected ? '#28a745' : '#dc3545'; ?>;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Últimos presupuestos -->
                    <?php if ($ultimosPresupuestos && $ultimosPresupuestos->num_rows > 0): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>📈 Últimos Presupuestos</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Cliente</th>
                                                    <th>Email</th>
                                                    <th>Total</th>
                                                    <th>Fecha</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($presupuesto = $ultimosPresupuestos->fetch_assoc()): ?>
                                                <tr>
                                                    <td>#<?php echo $presupuesto['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($presupuesto['nombre_cliente']); ?></td>
                                                    <td><?php echo htmlspecialchars($presupuesto['email_cliente']); ?></td>
                                                    <td>$<?php echo number_format($presupuesto['total'], 2, ',', '.'); ?></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($presupuesto['fecha_creacion'])); ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Estado del sistema -->
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
                                                <?php echo defined('IS_RAILWAY') && IS_RAILWAY ? '🚂 Railway' : '💻 Local'; ?>
                                            </p>
                                            <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
                                            <p><strong>Base de datos:</strong> 
                                                <?php echo $dbConnected ? '✅ Conectada' : '❌ Desconectada'; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Usuario:</strong> <?php echo $_SESSION['admin_user'] ?? 'admin'; ?></p>
                                            <p><strong>Sesión iniciada:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                                            <?php if (defined('BASE_URL')): ?>
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
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// Si no está logueado, mostrar formulario de login
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Sistema de Presupuestos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #333;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .login-header p {
            color: #666;
            margin: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .warning {
            background: #fff3e0;
            color: #ef6c00;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        .info {
            background: #e3f2fd;
            color: #1565c0;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        .railway-badge {
            background: #0066ff;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <?php if (defined('IS_RAILWAY') && IS_RAILWAY): ?>
            <div class="railway-badge">
                🚂 Ejecutándose en Railway
            </div>
        <?php endif; ?>

        <div class="login-header">
            <h1>🔐 Admin Panel</h1>
            <p>Sistema de Presupuestos de Ascensores</p>
        </div>

        <?php if (!$dbConnected): ?>
            <div class="warning">
                ⚠️ Advertencia: Conexión a base de datos limitada.<br>
                <?php if (defined('IS_RAILWAY') && IS_RAILWAY): ?>
                    Verificando conexión a Railway MySQL...
                <?php else: ?>
                    Verificando conexión local...
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       placeholder="Ingresa tu usuario">
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required
                       placeholder="Ingresa tu contraseña">
            </div>

            <button type="submit" class="btn">🚀 Iniciar Sesión</button>
        </form>

        <div class="info">
            💡 <strong>Credenciales por defecto:</strong><br>
            Usuario: <code>admin</code><br>
            Contraseña: <code>admin123</code>
        </div>

        <?php if (!defined('IS_RAILWAY') || !IS_RAILWAY): ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="../" style="color: #667eea; text-decoration: none;">← Volver al sitio principal</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
