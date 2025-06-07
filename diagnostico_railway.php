<?php
/**
 * 🔍 DIAGNÓSTICO COMPLETO RAILWAY vs LOCAL
 * 
 * Compara estructuras de base de datos y datos
 * para identificar diferencias entre entornos
 */

// Configuración integrada (igual que upload_database_completa_standalone.php)
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || 
             isset($_SERVER['RAILWAY_ENVIRONMENT']) ||
             strpos($_SERVER['HTTP_HOST'] ?? '', 'railway.app') !== false ||
             strpos($_SERVER['HTTP_HOST'] ?? '', 'up.railway.app') !== false;

if ($isRailway) {
    // CONFIGURACIÓN RAILWAY
    define('DB_HOST', $_ENV['DB_HOST'] ?? 'mysql.railway.internal');
    define('DB_USER', $_ENV['DB_USER'] ?? 'root');
    define('DB_PASS', $_ENV['DB_PASS'] ?? '');
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'railway');
    define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
    define('ENVIRONMENT', 'railway');
    define('DEBUG_MODE', false);
} else {
    // CONFIGURACIÓN LOCAL
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'company_presupuestos');
    define('DB_PORT', 3306);
    define('ENVIRONMENT', 'local');
    define('DEBUG_MODE', true);
}

// Configuración de errores
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
        
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die("Error de conexión: " . $e->getMessage());
        } else {
            die("Error de conexión a la base de datos.");
        }
    }
}

function getTableStructure($tableName) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("DESCRIBE `$tableName`");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function getTableData($tableName, $limit = 10) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM `$tableName` LIMIT $limit");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function getTableCount($tableName) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$tableName`");
        $result = $stmt->fetch();
        return $result['count'];
    } catch (Exception $e) {
        return 0;
    }
}

function getAllTables() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SHOW TABLES");
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        return $tables;
    } catch (Exception $e) {
        return [];
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

// Obtener información
$connectionStatus = testConnection();
$allTables = getAllTables();

// Tablas críticas para el cotizador
$criticalTables = ['categorias', 'opciones', 'plazos_entrega', 'opcion_precios', 'presupuestos'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Diagnóstico Railway vs Local</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
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
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid <?php echo ENVIRONMENT === 'railway' ? '#3498db' : '#27ae60'; ?>;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .success { background: #d4edda; border-left-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        
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
        
        .btn:hover { opacity: 0.9; }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .card h3 {
            margin-top: 0;
            color: <?php echo ENVIRONMENT === 'railway' ? '#3498db' : '#27ae60'; ?>;
        }
        
        .table-details {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 14px;
        }
        
        .expand-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .expand-btn:hover {
            background: #5a6268;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Diagnóstico Completo de Base de Datos</h1>
            <p>Entorno: <?php echo ENVIRONMENT === 'railway' ? '🚂 Railway (Producción)' : '🏠 Local (Desarrollo)'; ?></p>
            <p><small>Análisis detallado de estructura y datos</small></p>
        </div>
        
        <div class="content">
            <!-- Estado de Conexión -->
            <div class="info-box <?php echo $connectionStatus ? 'success' : 'error'; ?>">
                <h3>📡 Estado de Conexión</h3>
                <table>
                    <tr><th>Parámetro</th><th>Valor</th></tr>
                    <tr><td>Entorno</td><td><strong><?php echo ENVIRONMENT; ?></strong></td></tr>
                    <tr><td>Host</td><td><?php echo DB_HOST; ?></td></tr>
                    <tr><td>Base de datos</td><td><?php echo DB_NAME; ?></td></tr>
                    <tr><td>Puerto</td><td><?php echo DB_PORT; ?></td></tr>
                    <tr><td>Conexión</td><td><?php echo $connectionStatus ? '<span class="status-ok">✅ Activa</span>' : '<span class="status-error">❌ Error</span>'; ?></td></tr>
                </table>
            </div>
            
            <!-- Resumen de Tablas -->
            <div class="info-box">
                <h3>📊 Resumen de Tablas</h3>
                <p><strong>Total de tablas encontradas:</strong> <?php echo count($allTables); ?></p>
                
                <div class="grid">
                    <?php foreach ($criticalTables as $table): ?>
                        <?php 
                        $exists = in_array($table, $allTables);
                        $count = $exists ? getTableCount($table) : 0;
                        ?>
                        <div class="card">
                            <h3><?php echo $table; ?></h3>
                            <p><strong>Estado:</strong> <?php echo $exists ? '<span class="status-ok">✅ Existe</span>' : '<span class="status-error">❌ No existe</span>'; ?></p>
                            <?php if ($exists): ?>
                                <p><strong>Registros:</strong> <?php echo number_format($count); ?></p>
                                <button class="expand-btn" onclick="toggleDetails('<?php echo $table; ?>')">Ver Detalles</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Detalles de Tablas Críticas -->
            <?php foreach ($criticalTables as $table): ?>
                <?php if (in_array($table, $allTables)): ?>
                    <div id="details-<?php echo $table; ?>" class="info-box hidden">
                        <h3>🔍 Detalles de la tabla: <?php echo $table; ?></h3>
                        
                        <!-- Estructura -->
                        <h4>📋 Estructura</h4>
                        <table>
                            <tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>
                            <?php 
                            $structure = getTableStructure($table);
                            if (!isset($structure['error'])):
                                foreach ($structure as $field): ?>
                                    <tr>
                                        <td><strong><?php echo $field['Field']; ?></strong></td>
                                        <td><?php echo $field['Type']; ?></td>
                                        <td><?php echo $field['Null']; ?></td>
                                        <td><?php echo $field['Key']; ?></td>
                                        <td><?php echo $field['Default'] ?? 'NULL'; ?></td>
                                        <td><?php echo $field['Extra']; ?></td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr><td colspan="6" class="status-error">Error: <?php echo $structure['error']; ?></td></tr>
                            <?php endif; ?>
                        </table>
                        
                        <!-- Datos de muestra -->
                        <h4>📄 Datos de muestra (primeros 10 registros)</h4>
                        <?php 
                        $sampleData = getTableData($table, 10);
                        if (!isset($sampleData['error']) && !empty($sampleData)): ?>
                            <table>
                                <tr>
                                    <?php foreach (array_keys($sampleData[0]) as $column): ?>
                                        <th><?php echo $column; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                                <?php foreach ($sampleData as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $value): ?>
                                            <td><?php echo htmlspecialchars(substr($value ?? '', 0, 50)) . (strlen($value ?? '') > 50 ? '...' : ''); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php elseif (isset($sampleData['error'])): ?>
                            <div class="error">Error: <?php echo $sampleData['error']; ?></div>
                        <?php else: ?>
                            <div class="warning">⚠️ No hay datos en esta tabla</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <!-- Todas las Tablas -->
            <div class="info-box">
                <h3>📋 Todas las Tablas en la Base de Datos</h3>
                <div class="grid">
                    <?php foreach ($allTables as $table): ?>
                        <?php $count = getTableCount($table); ?>
                        <div class="card">
                            <h4><?php echo $table; ?></h4>
                            <p><strong>Registros:</strong> <?php echo number_format($count); ?></p>
                            <p><strong>Crítica:</strong> <?php echo in_array($table, $criticalTables) ? '<span class="status-ok">✅ Sí</span>' : '<span class="status-warning">⚠️ No</span>'; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Diagnóstico del Cotizador -->
            <div class="info-box">
                <h3>🔧 Diagnóstico del Cotizador</h3>
                <?php
                $diagnostico = [];
                
                // Verificar tablas necesarias
                $tablasNecesarias = ['categorias', 'opciones', 'plazos_entrega'];
                foreach ($tablasNecesarias as $tabla) {
                    if (!in_array($tabla, $allTables)) {
                        $diagnostico[] = "❌ Falta la tabla: $tabla";
                    } else {
                        $count = getTableCount($tabla);
                        if ($count == 0) {
                            $diagnostico[] = "⚠️ La tabla $tabla está vacía";
                        } else {
                            $diagnostico[] = "✅ Tabla $tabla: $count registros";
                        }
                    }
                }
                
                // Verificar estructura de opciones
                if (in_array('opciones', $allTables)) {
                    $estructura = getTableStructure('opciones');
                    if (!isset($estructura['error'])) {
                        $campos = array_column($estructura, 'Field');
                        $camposPrecios = ['precio', 'precio_90_dias', 'precio_160_dias', 'precio_270_dias'];
                        foreach ($camposPrecios as $campo) {
                            if (in_array($campo, $campos)) {
                                $diagnostico[] = "✅ Campo de precio encontrado: $campo";
                            } else {
                                $diagnostico[] = "❌ Falta campo de precio: $campo";
                            }
                        }
                    }
                }
                
                if (empty($diagnostico)) {
                    $diagnostico[] = "✅ No se encontraron problemas evidentes";
                }
                ?>
                
                <ul>
                    <?php foreach ($diagnostico as $item): ?>
                        <li><?php echo $item; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Enlaces Rápidos -->
            <div class="info-box">
                <h3>🔗 Enlaces Rápidos</h3>
                <a href="upload_database_completa_standalone.php" class="btn">📤 Upload Database</a>
                <a href="cotizador.php" class="btn">💼 Cotizador</a>
                <?php if (ENVIRONMENT === 'railway'): ?>
                    <a href="sistema/cotizador.php" class="btn">🔧 Cotizador Sistema</a>
                <?php else: ?>
                    <a href="http://localhost/phpmyadmin" class="btn" target="_blank">🗄️ phpMyAdmin</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function toggleDetails(tableName) {
            const element = document.getElementById('details-' + tableName);
            if (element.classList.contains('hidden')) {
                element.classList.remove('hidden');
            } else {
                element.classList.add('hidden');
            }
        }
    </script>
</body>
</html> 