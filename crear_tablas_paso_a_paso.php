<?php
/**
 * 🔧 CREAR TABLAS PASO A PASO
 * 
 * Sistema que crea las tablas una por una para evitar problemas
 * del upload masivo en Railway
 */

// Configuración local
$localConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'company_presupuestos',
    'port' => 3306
];

function createTableStepByStep($config, $tableName) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $output = "-- ========================================\n";
        $output .= "-- CREAR TABLA: $tableName\n";
        $output .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- ========================================\n\n";
        
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n";
        
        // Eliminar tabla si existe
        $output .= "DROP TABLE IF EXISTS `$tableName`;\n\n";
        
        // Crear estructura de la tabla
        $stmt = $pdo->query("SHOW CREATE TABLE `$tableName`");
        $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
        $output .= $createTable['Create Table'] . ";\n\n";
        
        // Insertar datos
        $stmt = $pdo->query("SELECT COUNT(*) FROM `$tableName`");
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $stmt = $pdo->query("SELECT * FROM `$tableName`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $columnsList = '`' . implode('`, `', $columns) . '`';
                
                $output .= "INSERT INTO `$tableName` ($columnsList) VALUES\n";
                
                $values = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } else {
                            $escaped = addslashes($value);
                            $escaped = str_replace(["\n", "\r", "\t"], ["\\n", "\\r", "\\t"], $escaped);
                            $rowValues[] = "'" . $escaped . "'";
                        }
                    }
                    $values[] = '(' . implode(', ', $rowValues) . ')';
                }
                
                $output .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        $output .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";
        $output .= "-- Verificación\n";
        $output .= "SELECT 'Tabla $tableName creada exitosamente' as resultado;\n";
        $output .= "SELECT COUNT(*) as registros FROM `$tableName`;\n";
        
        return [
            'success' => true,
            'sql' => $output,
            'records' => $count,
            'size' => strlen($output)
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Tablas principales en orden de dependencia
$tables = [
    'categorias' => 'Tabla principal de categorías',
    'plazos_entrega' => 'Plazos de entrega disponibles',
    'opciones' => 'Opciones por categoría',
    'opcion_precios' => 'Precios de opciones',
    'presupuestos' => 'Presupuestos generados'
];

// Procesar creación de tabla específica
$export = null;
$downloadReady = false;
$selectedTable = null;

if (isset($_POST['create_table'])) {
    $selectedTable = $_POST['table_name'];
    $export = createTableStepByStep($localConfig, $selectedTable);
    
    if ($export['success']) {
        $filename = "crear_tabla_{$selectedTable}_" . date('Y-m-d_H-i-s') . '.sql';
        file_put_contents($filename, $export['sql']);
        $downloadReady = true;
    }
}

// Descargar archivo
if (isset($_GET['download']) && file_exists($_GET['download'])) {
    $filename = $_GET['download'];
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Content-Length: ' . filesize($filename));
    readfile($filename);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Crear Tablas Paso a Paso</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
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
            background: linear-gradient(135deg, #6f42c1, #e83e8c);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #6f42c1;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .success { background: #d4edda; border-left-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #6f42c1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 8px 5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn:hover { background: #5a32a3; }
        .btn-primary { background: #007bff; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        
        .table-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .table-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .table-card h4 {
            color: #6f42c1;
            margin-bottom: 10px;
        }
        
        .highlight {
            background: #f3e5f5;
            border: 2px solid #6f42c1;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Crear Tablas Paso a Paso</h1>
            <p>Solución para el problema de upload masivo en Railway</p>
        </div>
        
        <div class="content">
            <?php if (!$export): ?>
                <div class="highlight">
                    <h3>🎯 Problema Identificado</h3>
                    <p>El upload masivo reporta "éxito" pero <strong>no crea las tablas</strong>. Vamos a crear las tablas una por una:</p>
                    <ul>
                        <li>✅ <strong>Una tabla por archivo</strong> - Evita problemas de upload masivo</li>
                        <li>✅ <strong>Verificación incluida</strong> - Cada archivo verifica que se creó</li>
                        <li>✅ <strong>Orden correcto</strong> - Respeta dependencias entre tablas</li>
                    </ul>
                </div>
                
                <div class="info-box">
                    <h3>📋 Estrategia Paso a Paso</h3>
                    <p>Crea las tablas en este orden específico:</p>
                    <ol>
                        <li><strong>categorias</strong> - Tabla principal (sin dependencias)</li>
                        <li><strong>plazos_entrega</strong> - Independiente</li>
                        <li><strong>opciones</strong> - Depende de categorias</li>
                        <li><strong>opcion_precios</strong> - Depende de opciones</li>
                        <li><strong>presupuestos</strong> - Tabla final</li>
                    </ol>
                </div>
                
                <h3>🗂️ Selecciona la tabla a crear:</h3>
                <div class="table-grid">
                    <?php foreach ($tables as $tableName => $description): ?>
                        <div class="table-card">
                            <h4><?php echo $tableName; ?></h4>
                            <p><?php echo $description; ?></p>
                            <form method="POST" style="margin: 10px 0;">
                                <input type="hidden" name="table_name" value="<?php echo $tableName; ?>">
                                <button type="submit" name="create_table" class="btn btn-success">
                                    🔧 Crear <?php echo $tableName; ?>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="info-box warning">
                    <h3>⚠️ Instrucciones</h3>
                    <ol>
                        <li><strong>Empieza por "categorias"</strong> - Es la tabla principal</li>
                        <li><strong>Descarga el archivo SQL</strong> generado</li>
                        <li><strong>Súbelo a Railway</strong> usando el upload</li>
                        <li><strong>Verifica que se creó</strong> antes de continuar</li>
                        <li><strong>Repite</strong> con las demás tablas en orden</li>
                    </ol>
                </div>
                
            <?php elseif ($export['success']): ?>
                <div class="info-box success">
                    <h3>✅ Archivo para tabla "<?php echo $selectedTable; ?>" generado</h3>
                    <table>
                        <tr><th>Parámetro</th><th>Valor</th></tr>
                        <tr><td>Tabla</td><td><strong><?php echo $selectedTable; ?></strong></td></tr>
                        <tr><td>Registros</td><td><?php echo $export['records']; ?></td></tr>
                        <tr><td>Tamaño</td><td><?php echo number_format($export['size'] / 1024, 2); ?> KB</td></tr>
                        <tr><td>Fecha</td><td><?php echo date('Y-m-d H:i:s'); ?></td></tr>
                    </table>
                </div>
                
                <?php if ($downloadReady): ?>
                    <?php $filename = "crear_tabla_{$selectedTable}_" . date('Y-m-d_H-i-s') . '.sql'; ?>
                    
                    <div class="info-box">
                        <h3>📥 Descargar Archivo para "<?php echo $selectedTable; ?>"</h3>
                        <p style="text-align: center;">
                            <a href="?download=<?php echo $filename; ?>" class="btn btn-primary" style="font-size: 16px; padding: 15px 30px;">
                                💾 DESCARGAR <?php echo basename($filename); ?>
                            </a>
                        </p>
                    </div>
                    
                    <div class="highlight">
                        <h3>🚀 Próximos Pasos</h3>
                        <ol>
                            <li><strong>Descargar</strong> el archivo de arriba</li>
                            <li><strong>Ir a Railway</strong>: <a href="https://cotizadorcompany-production.up.railway.app/upload_database_completa_standalone.php" target="_blank" class="btn btn-primary">🚂 Upload Railway</a></li>
                            <li><strong>Subir el archivo</strong> de la tabla "<?php echo $selectedTable; ?>"</li>
                            <li><strong>Verificar</strong> que se creó correctamente</li>
                            <li><strong>Continuar</strong> con la siguiente tabla</li>
                        </ol>
                    </div>
                    
                    <div class="info-box">
                        <h3>🔄 Crear Otra Tabla</h3>
                        <p><a href="?" class="btn">🔧 Volver a Seleccionar Tabla</a></p>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="info-box error">
                    <h3>❌ Error Generando Tabla</h3>
                    <p><strong>Error:</strong> <?php echo $export['error']; ?></p>
                    <p><a href="?" class="btn">🔄 Intentar de Nuevo</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 