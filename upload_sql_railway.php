<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Detectar si estamos en Railway o local
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || 
             isset($_SERVER['RAILWAY_ENVIRONMENT']) ||
             strpos($_SERVER['HTTP_HOST'] ?? '', 'railway.app') !== false;

if ($isRailway) {
    $host = $_ENV['DB_HOST'] ?? 'mysql.railway.internal';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASS'] ?? '';
    $database = $_ENV['DB_NAME'] ?? 'railway';
    $port = $_ENV['DB_PORT'] ?? 3306;
} else {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'cotizador_company';
    $port = 3306;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🗄️ Upload SQL - Railway Database</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .content {
            padding: 40px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        
        .upload-form {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .file-input {
            width: 100%;
            padding: 15px;
            border: 2px dashed #3498db;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-input:hover {
            border-color: #2980b9;
            background: #ecf0f1;
        }
        
        .btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .result {
            margin-top: 30px;
            padding: 20px;
            border-radius: 8px;
            white-space: pre-wrap;
            font-family: 'Courier New', monospace;
        }
        
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        
        .progress {
            width: 100%;
            height: 20px;
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #3498db, #2980b9);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .file-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border: 1px solid #ddd;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }
        
        .quick-btn {
            padding: 15px;
            background: white;
            border: 2px solid #3498db;
            color: #3498db;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .quick-btn:hover {
            background: #3498db;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗄️ SQL Upload Manager</h1>
            <p>Sube y ejecuta archivos SQL en <?php echo $isRailway ? 'Railway' : 'Local'; ?></p>
        </div>
        
        <div class="content">
            <div class="info-box">
                <h3>📋 Información de Conexión</h3>
                <p><strong>Entorno:</strong> <?php echo $isRailway ? '🚂 Railway' : '🏠 Local'; ?></p>
                <p><strong>Host:</strong> <?php echo $host; ?></p>
                <p><strong>Base de datos:</strong> <?php echo $database; ?></p>
                <p><strong>Puerto:</strong> <?php echo $port; ?></p>
            </div>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sql_file'])): ?>
                <div class="result">
                    <?php
                    try {
                        // Verificar archivo subido
                        if ($_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
                            throw new Exception('Error al subir el archivo: ' . $_FILES['sql_file']['error']);
                        }
                        
                        $uploadedFile = $_FILES['sql_file']['tmp_name'];
                        $fileName = $_FILES['sql_file']['name'];
                        $fileSize = $_FILES['sql_file']['size'];
                        
                        echo "📁 <strong>Archivo:</strong> {$fileName}\n";
                        echo "📏 <strong>Tamaño:</strong> " . number_format($fileSize / 1024, 2) . " KB\n\n";
                        
                        // Leer contenido del archivo
                        $sqlContent = file_get_contents($uploadedFile);
                        if ($sqlContent === false) {
                            throw new Exception('No se pudo leer el archivo SQL');
                        }
                        
                        echo "✅ <strong>Archivo leído correctamente</strong>\n\n";
                        
                        // Conectar a la base de datos
                        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", 
                                       $username, $password, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                        ]);
                        
                        echo "🔌 <strong>Conexión a la base de datos exitosa</strong>\n\n";
                        
                        // Deshabilitar verificación de claves foráneas
                        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                        echo "🔧 <strong>Claves foráneas deshabilitadas</strong>\n\n";
                        
                        // Dividir el SQL en statements individuales
                        $statements = array_filter(
                            array_map('trim', explode(';', $sqlContent)),
                            function($stmt) { return !empty($stmt) && !preg_match('/^\s*--/', $stmt); }
                        );
                        
                        echo "📝 <strong>Statements encontrados:</strong> " . count($statements) . "\n\n";
                        echo "🚀 <strong>Ejecutando SQL...</strong>\n";
                        echo str_repeat("-", 50) . "\n";
                        
                        $executed = 0;
                        $errors = 0;
                        
                        foreach ($statements as $index => $statement) {
                            try {
                                $pdo->exec($statement);
                                $executed++;
                                echo "✅ Statement " . ($index + 1) . ": OK\n";
                            } catch (Exception $e) {
                                $errors++;
                                echo "❌ Statement " . ($index + 1) . ": " . $e->getMessage() . "\n";
                            }
                        }
                        
                        // Rehabilitar verificación de claves foráneas
                        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                        
                        echo str_repeat("-", 50) . "\n";
                        echo "📊 <strong>Resumen de ejecución:</strong>\n";
                        echo "✅ Ejecutados exitosamente: {$executed}\n";
                        echo "❌ Errores: {$errors}\n";
                        echo "🔒 Claves foráneas rehabilitadas\n\n";
                        
                        if ($errors === 0) {
                            echo "🎉 <strong>¡SQL ejecutado completamente sin errores!</strong>\n";
                            echo '<div class="success">';
                        } else {
                            echo "⚠️ <strong>SQL ejecutado con algunos errores</strong>\n";
                            echo '<div class="warning">';
                        }
                        
                        // Verificar tablas creadas
                        $tables = $pdo->query("SHOW TABLES")->fetchAll();
                        echo "\n📊 <strong>Tablas en la base de datos:</strong>\n";
                        foreach ($tables as $table) {
                            $tableName = array_values($table)[0];
                            $count = $pdo->query("SELECT COUNT(*) as total FROM `{$tableName}`")->fetch();
                            echo "  • {$tableName} ({$count['total']} registros)\n";
                        }
                        
                        echo '</div>';
                        
                    } catch (Exception $e) {
                        echo '<div class="error">';
                        echo "❌ <strong>Error:</strong> " . $e->getMessage() . "\n";
                        echo "📁 <strong>Archivo:</strong> " . $e->getFile() . "\n";
                        echo "📍 <strong>Línea:</strong> " . $e->getLine() . "\n";
                        echo '</div>';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label for="sql_file">📁 Seleccionar archivo SQL:</label>
                    <input type="file" 
                           id="sql_file" 
                           name="sql_file" 
                           accept=".sql,.txt" 
                           class="file-input" 
                           required>
                    <div class="file-info" id="file-info" style="display: none;">
                        <p><strong>Archivo seleccionado:</strong> <span id="file-name"></span></p>
                        <p><strong>Tamaño:</strong> <span id="file-size"></span></p>
                        <p><strong>Tipo:</strong> <span id="file-type"></span></p>
                    </div>
                </div>
                
                <button type="submit" class="btn" id="upload-btn" disabled>
                    🚀 Ejecutar SQL en <?php echo $isRailway ? 'Railway' : 'Local'; ?>
                </button>
            </form>
            
            <div class="quick-actions">
                <a href="diagnostico_conexion.php" class="quick-btn">
                    🔍 Diagnóstico
                </a>
                <a href="setup_railway_completo_v2.php" class="quick-btn">
                    ⚙️ Setup Automático
                </a>
                <a href="cotizador.php" class="quick-btn">
                    💼 Cotizador
                </a>
                <?php if (!$isRailway): ?>
                <a href="http://localhost/phpmyadmin" class="quick-btn" target="_blank">
                    🗄️ phpMyAdmin
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('sql_file');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const fileType = document.getElementById('file-type');
        const uploadBtn = document.getElementById('upload-btn');

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024).toFixed(2) + ' KB';
                fileType.textContent = file.type || 'text/plain';
                
                fileInfo.style.display = 'block';
                uploadBtn.disabled = false;
                
                // Validar extensión
                const validExtensions = ['.sql', '.txt'];
                const fileExtension = file.name.toLowerCase().substring(file.name.lastIndexOf('.'));
                
                if (!validExtensions.includes(fileExtension)) {
                    alert('⚠️ Por favor selecciona un archivo .sql o .txt');
                    fileInput.value = '';
                    fileInfo.style.display = 'none';
                    uploadBtn.disabled = true;
                }
            } else {
                fileInfo.style.display = 'none';
                uploadBtn.disabled = true;
            }
        });

        // Prevenir envío accidental
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!confirm('¿Estás seguro de que quieres ejecutar este archivo SQL? Esta acción puede modificar la base de datos.')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html> 