<?php
/**
 * 🚀 UPLOAD DATABASE COMPLETA A RAILWAY - STANDALONE
 * 
 * Versión autónoma que incluye la configuración directamente
 * No depende de archivos externos
 */

// ========================================
// CONFIGURACIÓN INTEGRADA
// ========================================

// Detectar si estamos en Railway
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || 
             isset($_SERVER['RAILWAY_ENVIRONMENT']) ||
             strpos($_SERVER['HTTP_HOST'] ?? '', 'railway.app') !== false ||
             strpos($_SERVER['HTTP_HOST'] ?? '', 'up.railway.app') !== false;

if ($isRailway) {
    // CONFIGURACIÓN RAILWAY (PRODUCCIÓN)
    define('DB_HOST', $_ENV['DB_HOST'] ?? 'mysql.railway.internal');
    define('DB_USER', $_ENV['DB_USER'] ?? 'root');
    define('DB_PASS', $_ENV['DB_PASS'] ?? '');
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'railway');
    define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
    define('ENVIRONMENT', 'railway');
    define('DEBUG_MODE', false);
    define('BASE_URL', 'https://' . $_SERVER['HTTP_HOST']);
} else {
    // CONFIGURACIÓN LOCAL (DESARROLLO)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'company_presupuestos');
    define('DB_PORT', 3306);
    define('ENVIRONMENT', 'local');
    define('DEBUG_MODE', true);
    define('BASE_URL', 'http://localhost/company-presupuestos-online-2');
}

// Configuración de errores según entorno
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ========================================
// FUNCIONES DE CONEXIÓN
// ========================================

function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die("Error de conexión: " . $e->getMessage());
        } else {
            die("Error de conexión a la base de datos. Por favor, inténtelo más tarde.");
        }
    }
}

function testConnection() {
    try {
        $pdo = getDBConnection();
        $result = $pdo->query("SELECT 1 as test")->fetch();
        return $result['test'] === 1;
    } catch (Exception $e) {
        return false;
    }
}

function getDatabaseStats() {
    try {
        $pdo = getDBConnection();
        $stats = [];
        
        // Contar categorías
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categorias");
        $stats['categorias'] = $stmt->fetch()['count'];
        
        // Contar opciones
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM opciones");
        $stats['opciones'] = $stmt->fetch()['count'];
        
        // Contar presupuestos
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM presupuestos");
        $stats['presupuestos'] = $stmt->fetch()['count'];
        
        return $stats;
        
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function getEnvironmentInfo() {
    return [
        'environment' => ENVIRONMENT,
        'host' => DB_HOST,
        'database' => DB_NAME,
        'port' => DB_PORT,
        'debug' => DEBUG_MODE,
        'base_url' => BASE_URL,
        'is_railway' => ENVIRONMENT === 'railway'
    ];
}

// ========================================
// LÓGICA DE UPLOAD
// ========================================

$maxFileSize = 50 * 1024 * 1024; // 50MB
$allowedExtensions = ['sql', 'txt'];
$uploadSuccess = false;
$errorMessage = '';
$successMessage = '';
$executionLog = [];

// Función para agregar al log
function addToLog($message) {
    global $executionLog;
    $timestamp = date('H:i:s');
    $executionLog[] = "[$timestamp] $message";
}

// Función para ejecutar SQL paso a paso
function executeSQLFile($filePath) {
    global $executionLog;
    
    try {
        $pdo = getDBConnection();
        addToLog("✅ Conexión a base de datos establecida");
        
        // Leer archivo SQL
        $sqlContent = file_get_contents($filePath);
        if (!$sqlContent) {
            throw new Exception("No se pudo leer el archivo SQL");
        }
        
        addToLog("📄 Archivo SQL leído: " . number_format(strlen($sqlContent)) . " caracteres");
        
        // Deshabilitar foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        addToLog("🔓 Foreign key checks deshabilitados");
        
        // Dividir en statements individuales
        $statements = array_filter(
            array_map('trim', explode(';', $sqlContent)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^(--|\/\*|\s*$)/', $stmt);
            }
        );
        
        addToLog("📊 Total de statements SQL: " . count($statements));
        
        $executed = 0;
        $errors = 0;
        
        foreach ($statements as $statement) {
            try {
                $pdo->exec($statement);
                $executed++;
                
                // Log cada 10 statements
                if ($executed % 10 == 0) {
                    addToLog("⚡ Ejecutados: $executed statements");
                }
                
            } catch (PDOException $e) {
                $errors++;
                addToLog("❌ Error en statement: " . substr($statement, 0, 50) . "... - " . $e->getMessage());
            }
        }
        
        // Rehabilitar foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        addToLog("🔒 Foreign key checks rehabilitados");
        
        addToLog("✅ Proceso completado - Ejecutados: $executed, Errores: $errors");
        
        return ['executed' => $executed, 'errors' => $errors];
        
    } catch (Exception $e) {
        addToLog("💥 Error crítico: " . $e->getMessage());
        throw $e;
    }
}

// Procesar upload si se envió archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sql_file'])) {
    addToLog("🚀 Iniciando proceso de upload");
    
    $file = $_FILES['sql_file'];
    
    // Validaciones
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = "Error en el upload: " . $file['error'];
        addToLog("❌ " . $errorMessage);
    } elseif ($file['size'] > $maxFileSize) {
        $errorMessage = "Archivo demasiado grande. Máximo: " . ($maxFileSize / 1024 / 1024) . "MB";
        addToLog("❌ " . $errorMessage);
    } else {
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            $errorMessage = "Extensión no permitida. Solo: " . implode(', ', $allowedExtensions);
            addToLog("❌ " . $errorMessage);
        } else {
            try {
                addToLog("📁 Archivo recibido: " . $file['name'] . " (" . number_format($file['size']) . " bytes)");
                
                // Ejecutar SQL
                $result = executeSQLFile($file['tmp_name']);
                
                $uploadSuccess = true;
                $successMessage = "✅ Base de datos actualizada exitosamente!<br>";
                $successMessage .= "📊 Statements ejecutados: " . $result['executed'] . "<br>";
                if ($result['errors'] > 0) {
                    $successMessage .= "⚠️ Errores menores: " . $result['errors'];
                }
                
                addToLog("🎉 Upload completado exitosamente");
                
            } catch (Exception $e) {
                $errorMessage = "Error ejecutando SQL: " . $e->getMessage();
                addToLog("💥 " . $errorMessage);
            }
        }
    }
}

// Obtener estadísticas actuales
$currentStats = getDatabaseStats();
$envInfo = getEnvironmentInfo();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Upload Database - STANDALONE - <?php echo ENVIRONMENT; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: <?php echo ENVIRONMENT === 'railway' ? 'linear-gradient(135deg, #2c3e50, #3498db)' : 'linear-gradient(135deg, #27ae60, #2ecc71)'; ?>;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .upload-area {
            border: 3px dashed #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
            background: #f8f9fa;
        }
        
        .upload-area.dragover {
            border-color: #007bff;
            background: #e3f2fd;
        }
        
        .file-input {
            display: none;
        }
        
        .upload-btn {
            background: #007bff;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        
        .upload-btn:hover {
            background: #0056b3;
        }
        
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid <?php echo ENVIRONMENT === 'railway' ? '#3498db' : '#27ae60'; ?>;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: <?php echo ENVIRONMENT === 'railway' ? '#3498db' : '#27ae60'; ?>;
        }
        
        .log-container {
            background: #2d3748;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-height: 400px;
            overflow-y: auto;
            margin: 20px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: <?php echo ENVIRONMENT === 'railway' ? '#3498db' : '#27ae60'; ?>;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            font-weight: 600;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .status-ok {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Upload Database - STANDALONE</h1>
            <p>Entorno: <?php echo ENVIRONMENT === 'railway' ? '🚂 Railway (Producción)' : '🏠 Local (Desarrollo)'; ?></p>
            <p><small>Versión autónoma - No depende de config.php externo</small></p>
        </div>
        
        <div class="content">
            <!-- Información del Entorno -->
            <div class="info-box">
                <h3>📋 Información del Entorno</h3>
                <table>
                    <tr><th>Parámetro</th><th>Valor</th></tr>
                    <tr><td>Entorno</td><td><strong><?php echo $envInfo['environment']; ?></strong></td></tr>
                    <tr><td>Host de BD</td><td><?php echo $envInfo['host']; ?></td></tr>
                    <tr><td>Base de datos</td><td><?php echo $envInfo['database']; ?></td></tr>
                    <tr><td>Puerto</td><td><?php echo $envInfo['port']; ?></td></tr>
                    <tr><td>Conexión</td><td><?php echo testConnection() ? '<span class="status-ok">✅ Activa</span>' : '<span class="status-error">❌ Error</span>'; ?></td></tr>
                    <tr><td>URL base</td><td><?php echo $envInfo['base_url']; ?></td></tr>
                </table>
            </div>
            
            <!-- Estadísticas Actuales -->
            <div class="info-box">
                <h3>📊 Estadísticas Actuales de la Base de Datos</h3>
                <?php if (isset($currentStats['error'])): ?>
                    <div class="error">❌ Error: <?php echo $currentStats['error']; ?></div>
                <?php else: ?>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $currentStats['categorias']; ?></div>
                            <div>Categorías</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $currentStats['opciones']; ?></div>
                            <div>Opciones</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $currentStats['presupuestos']; ?></div>
                            <div>Presupuestos</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Mensajes de Resultado -->
            <?php if ($uploadSuccess): ?>
                <div class="success">
                    <h4>🎉 ¡Upload Exitoso!</h4>
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="error">
                    <h4>❌ Error en el Upload</h4>
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulario de Upload -->
            <div class="info-box">
                <h3>📁 Subir Base de Datos Completa</h3>
                <p><strong>Archivo recomendado:</strong> <code>company_presupuestos_completo_backup.sql</code></p>
                
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="upload-area" id="uploadArea">
                        <h4>📤 Arrastra tu archivo SQL aquí</h4>
                        <p>o haz clic para seleccionar</p>
                        <input type="file" name="sql_file" id="sqlFile" class="file-input" accept=".sql,.txt" required>
                        <button type="button" class="upload-btn" onclick="document.getElementById('sqlFile').click()">
                            📁 Seleccionar Archivo SQL
                        </button>
                        <p><small>Máximo: <?php echo $maxFileSize / 1024 / 1024; ?>MB | Formatos: .sql, .txt</small></p>
                    </div>
                    
                    <div id="fileInfo" style="display: none; margin: 15px 0; padding: 15px; background: #e3f2fd; border-radius: 5px;">
                        <strong>Archivo seleccionado:</strong> <span id="fileName"></span><br>
                        <strong>Tamaño:</strong> <span id="fileSize"></span>
                    </div>
                    
                    <button type="submit" class="upload-btn" style="background: #28a745; width: 100%; padding: 20px;">
                        🚀 SUBIR Y EJECUTAR SQL
                    </button>
                </form>
            </div>
            
            <!-- Log de Ejecución -->
            <?php if (!empty($executionLog)): ?>
            <div class="info-box">
                <h3>📋 Log de Ejecución</h3>
                <div class="log-container">
                    <?php foreach ($executionLog as $logEntry): ?>
                        <div><?php echo htmlspecialchars($logEntry); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Enlaces Rápidos -->
            <div class="info-box">
                <h3>🔗 Enlaces Rápidos</h3>
                <a href="cotizador.php" class="btn">💼 Cotizador</a>
                <a href="admin/" class="btn">⚙️ Admin</a>
                <?php if (ENVIRONMENT === 'local'): ?>
                <a href="http://localhost/phpmyadmin" class="btn" target="_blank">🗄️ phpMyAdmin</a>
                <?php endif; ?>
            </div>
            
            <!-- Instrucciones -->
            <div class="info-box">
                <h3>📝 Instrucciones</h3>
                <ol>
                    <li><strong>Exportar desde Local:</strong> Usa phpMyAdmin o mysqldump para exportar <code>company_presupuestos</code></li>
                    <li><strong>Subir Archivo:</strong> Selecciona el archivo .sql exportado</li>
                    <li><strong>Ejecutar:</strong> El script procesará automáticamente todas las tablas y datos</li>
                    <li><strong>Verificar:</strong> Revisa las estadísticas para confirmar que todo se cargó</li>
                </ol>
                
                <p><strong>💡 Tip:</strong> Esta versión standalone incluye toda la configuración internamente.</p>
            </div>
        </div>
    </div>
    
    <script>
        // Manejo de drag & drop
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('sqlFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                showFileInfo(files[0]);
            }
        });
        
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                showFileInfo(e.target.files[0]);
            }
        });
        
        function showFileInfo(file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.style.display = 'block';
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Confirmación antes de enviar
        document.getElementById('uploadForm').addEventListener('submit', (e) => {
            if (!confirm('¿Estás seguro de que quieres reemplazar la base de datos actual?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html> 