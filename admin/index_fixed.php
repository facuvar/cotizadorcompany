<?php
// Iniciar sesión solo si no está iniciada y no se han enviado headers
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Habilitar reporte de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si existe el archivo de configuración
$configPath = __DIR__ . '/../sistema/config.php';
if (!file_exists($configPath)) {
    die("Error: Archivo de configuración no encontrado en: " . $configPath);
}

// Cargar configuración
try {
    require_once $configPath;
} catch (Exception $e) {
    die("Error cargando configuración: " . $e->getMessage());
}

// Intentar cargar db.php con manejo de errores
$dbPath = __DIR__ . '/../sistema/includes/db.php';
$dbConnected = false;

try {
    if (file_exists($dbPath)) {
        // Capturar cualquier output o error de db.php
        ob_start();
        require_once $dbPath;
        $dbOutput = ob_get_clean();
        
        if (!empty($dbOutput)) {
            error_log("DB.php output: " . $dbOutput);
        }
        
        $dbConnected = true;
    } else {
        error_log("DB file not found: " . $dbPath);
    }
} catch (Exception $e) {
    error_log("Error loading db.php: " . $e->getMessage());
    $dbConnected = false;
} catch (Error $e) {
    error_log("Fatal error in db.php: " . $e->getMessage());
    $dbConnected = false;
}

// Verificar si el usuario ya está logueado
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Procesar login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Verificar credenciales
    if (defined('ADMIN_USER') && defined('ADMIN_PASS')) {
        if ($username === ADMIN_USER && password_verify($password, ADMIN_PASS)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $username;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    } else {
        $error = 'Credenciales de administrador no configuradas';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
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
        .debug {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Admin Panel</h1>
            <p>Sistema de Presupuestos</p>
        </div>

        <?php if (!$dbConnected): ?>
            <div class="warning">
                ⚠️ Advertencia: No se pudo conectar a la base de datos.<br>
                El login funcionará pero algunas funciones pueden estar limitadas.
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
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>

        <div class="info">
            💡 <strong>Credenciales por defecto:</strong><br>
            Usuario: admin<br>
            Contraseña: admin123
        </div>

        <div class="debug">
            <strong>Debug Info:</strong><br>
            • PHP Version: <?php echo PHP_VERSION; ?><br>
            • Config cargado: <?php echo file_exists($configPath) ? '✅' : '❌'; ?><br>
            • DB conectada: <?php echo $dbConnected ? '✅' : '❌'; ?><br>
            • Admin user definido: <?php echo defined('ADMIN_USER') ? '✅ (' . ADMIN_USER . ')' : '❌'; ?><br>
            • Admin pass definido: <?php echo defined('ADMIN_PASS') ? '✅' : '❌'; ?><br>
            • Sesión activa: <?php echo session_status() === PHP_SESSION_ACTIVE ? '✅' : '❌'; ?>
        </div>
    </div>
</body>
</html> 