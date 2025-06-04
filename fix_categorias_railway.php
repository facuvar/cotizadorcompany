<?php
/**
 * 🎯 FIX CATEGORÍAS RAILWAY
 * 
 * Solución ultra-simplificada que se enfoca SOLO en crear
 * la tabla categorias correctamente en Railway
 */

// Configuración local
$localConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'company_presupuestos',
    'port' => 3306
];

function createCategoriasOnly($config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $output = "-- ========================================\n";
        $output .= "-- FIX CATEGORÍAS RAILWAY - SOLO CATEGORIAS\n";
        $output .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- ========================================\n\n";
        
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n";
        
        // PASO 1: Eliminar tabla categorias si existe
        $output .= "-- PASO 1: Eliminar tabla categorias\n";
        $output .= "DROP TABLE IF EXISTS `categorias`;\n\n";
        
        // PASO 2: Crear estructura de tabla categorias
        $output .= "-- PASO 2: Crear estructura tabla categorias\n";
        $stmt = $pdo->query("SHOW CREATE TABLE `categorias`");
        $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
        $output .= $createTable['Create Table'] . ";\n\n";
        
        // PASO 3: Insertar datos de categorias
        $output .= "-- PASO 3: Insertar datos en categorias\n";
        $stmt = $pdo->query("SELECT * FROM `categorias`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $columnsList = '`' . implode('`, `', $columns) . '`';
            
            $output .= "INSERT INTO `categorias` ($columnsList) VALUES\n";
            
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
        
        $output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $output .= "\n-- ========================================\n";
        $output .= "-- TABLA CATEGORIAS CREADA EXITOSAMENTE\n";
        $output .= "-- Registros: " . count($rows) . "\n";
        $output .= "-- ========================================\n";
        
        return [
            'success' => true,
            'sql' => $output,
            'records' => count($rows),
            'size' => strlen($output)
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Procesar exportación
$export = null;
$downloadReady = false;

if (isset($_POST['export'])) {
    $export = createCategoriasOnly($localConfig);
    
    if ($export['success']) {
        $filename = 'fix_categorias_railway_' . date('Y-m-d_H-i-s') . '.sql';
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
    <title>🎯 Fix Categorías Railway</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #e74c3c;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .success { background: #d4edda; border-left-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover { background: #c0392b; }
        .btn-primary { background: #007bff; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        
        .highlight {
            background: #fff5f5;
            border: 2px solid #e74c3c;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .code-box {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 15px 0;
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
            <h1>🎯 Fix Categorías Railway</h1>
            <p>Solución Ultra-Simplificada - Solo Tabla Categorías</p>
        </div>
        
        <div class="content">
            <?php if (!$export): ?>
                <div class="highlight">
                    <h3>🚨 Error Específico a Resolver</h3>
                    <div class="code-box">
                        ❌ Error: SQLSTATE[42S02]: Base table or view not found: 1146 Table 'railway.categorias' doesn't exist
                    </div>
                    <p><strong>Solución:</strong> Crear SOLO la tabla categorías primero, sin complicaciones.</p>
                </div>
                
                <div class="info-box">
                    <h3>🎯 ¿Qué hace este Fix?</h3>
                    <ol>
                        <li><strong>Elimina</strong> la tabla categorías si existe</li>
                        <li><strong>Crea</strong> la estructura de la tabla categorías</li>
                        <li><strong>Inserta</strong> todos los datos de categorías</li>
                        <li><strong>Nada más</strong> - Solo se enfoca en categorías</li>
                    </ol>
                </div>
                
                <div class="info-box warning">
                    <h3>⚡ Estrategia Ultra-Simple</h3>
                    <p>En lugar de intentar importar toda la base de datos de una vez:</p>
                    <ul>
                        <li>✅ Primero arreglamos SOLO las categorías</li>
                        <li>✅ Verificamos que el cotizador carga las categorías</li>
                        <li>✅ Después importamos el resto de tablas</li>
                    </ul>
                </div>
                
                <form method="POST" style="text-align: center; margin: 30px 0;">
                    <button type="submit" name="export" class="btn btn-success" style="font-size: 18px; padding: 20px 40px;">
                        🎯 CREAR FIX SOLO CATEGORÍAS
                    </button>
                </form>
                
            <?php elseif ($export['success']): ?>
                <div class="info-box success">
                    <h3>✅ Fix de Categorías Generado</h3>
                    <table>
                        <tr><th>Parámetro</th><th>Valor</th></tr>
                        <tr><td>Enfoque</td><td><strong>Solo tabla categorías</strong></td></tr>
                        <tr><td>Registros de categorías</td><td><?php echo $export['records']; ?></td></tr>
                        <tr><td>Tamaño del archivo</td><td><?php echo number_format($export['size'] / 1024, 2); ?> KB</td></tr>
                        <tr><td>Fecha</td><td><?php echo date('Y-m-d H:i:s'); ?></td></tr>
                    </table>
                </div>
                
                <?php if ($downloadReady): ?>
                    <?php $filename = 'fix_categorias_railway_' . date('Y-m-d_H-i-s') . '.sql'; ?>
                    
                    <div class="info-box">
                        <h3>📥 Descargar Fix de Categorías</h3>
                        <p style="text-align: center;">
                            <a href="?download=<?php echo $filename; ?>" class="btn btn-primary" style="font-size: 18px; padding: 15px 30px;">
                                💾 DESCARGAR <?php echo basename($filename); ?>
                            </a>
                        </p>
                    </div>
                    
                    <div class="highlight">
                        <h3>🚀 Pasos Siguientes</h3>
                        <ol>
                            <li><strong>Descargar</strong> el archivo de arriba</li>
                            <li><strong>Ir a Railway:</strong> <a href="https://cotizadorcompany-production.up.railway.app/upload_database_completa_standalone.php" target="_blank" class="btn btn-primary">🚂 Upload Railway</a></li>
                            <li><strong>Subir SOLO este archivo</strong> (que contiene solo categorías)</li>
                            <li><strong>Ejecutar importación</strong></li>
                            <li><strong>Probar cotizador</strong> - debería cargar las categorías</li>
                            <li><strong>Si funciona,</strong> entonces importar el resto de tablas</li>
                        </ol>
                    </div>
                    
                    <div class="info-box">
                        <h3>🔍 Verificar Resultado</h3>
                        <p>Después de subir este fix, ve al cotizador:</p>
                        <p><a href="https://cotizadorcompany-production.up.railway.app/cotizador.php" target="_blank" class="btn">🎯 Probar Cotizador</a></p>
                        <p>Si ya no aparece "Error al cargar las categorías", ¡el fix funcionó!</p>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="info-box error">
                    <h3>❌ Error al Generar Fix</h3>
                    <p><strong>Error:</strong> <?php echo $export['error']; ?></p>
                    <p><a href="?" class="btn">🔄 Intentar de Nuevo</a></p>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <h3>🔗 Enlaces de Diagnóstico</h3>
                <a href="diagnostico_railway.php" class="btn">🔍 Diagnóstico Local</a>
                <a href="https://cotizadorcompany-production.up.railway.app/diagnostico_railway.php" class="btn" target="_blank">🚂 Diagnóstico Railway</a>
            </div>
        </div>
    </div>
</body>
</html> 